<?php

class AmazonAPI{

    /*The API Key & URL will be used in functions to get data from Amazon.*/
    private $private_key = 'PRIVATE_KEY';

    // Associate Tag
    private $associate_tag = 'ASSOCIATE_TAG';

    // AccessKeyID
    private $AWSAccessKeyId = 'ACCESSKEYID';

    private $method = "GET";

    private $host = "webservices.amazon.co.uk";

    private $uri = "/onca/xml";

    function aws_query($extraparams, $choose) {

        $params = array(
            "AssociateTag" => $this->associate_tag,
            "Service" => "AWSECommerceService",
            "AWSAccessKeyId" => $this->AWSAccessKeyId,
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "SignatureMethod" => "HmacSHA256",
            "SignatureVersion" => "2",
            "Version" => "2013-08-01"
        );

        foreach ($extraparams as $param => $value) {
            $params[$param] = $value;
        }

        ksort($params);

        // Sort the parameters
        // Create the canonicalized query
        $canonicalized_query = array();
        foreach ($params as $param => $value) {
            $param = str_replace("%7E", "~", rawurlencode($param));
            $value = str_replace("%7E", "~", rawurlencode($value));
            $canonicalized_query[] = $param . "=" . $value;
        }
        $canonicalized_query = implode("&", $canonicalized_query);

        // Create the string to sign
        $string_to_sign =
            $this->method . "\n" .
            $this->host . "\n" .
            $this->uri . "\n" .
            $canonicalized_query;

        // Calculate HMAC with SHA256 and base64-encoding
        $signature = base64_encode(
            hash_hmac("sha256", $string_to_sign, $this->private_key, True));

        // Encode the signature for the equest
        $signature = str_replace("%7E", "~", rawurlencode($signature));

        // Put the signature into the parameters
        $params["Signature"] = $signature;
        uksort($params, "strnatcasecmp");

        $query = urldecode(http_build_query($params));
        $query = str_replace(' ', '%20', $query);

        $string_to_send = "https://" . $this->host . $this->uri . "?" . $query;

        // Preparing Query...
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $string_to_send);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        // Will print back the response from the call
        // Used for troubleshooting/debugging
        if(!$response){
            return false;
        }

        /*Return the data in JSON format*/
        $xml = simplexml_load_string($response);

        $json = json_encode($xml);

        $array = json_decode($json,TRUE);

        // CHOOSE = 1 -> AWS_ITEMSEARCH
        if ($choose == 1)
        {
            $amazon_products = array();

            for($i=0;$i<count($array['Items']['Item']);$i++){

                $asin = $array['Items']['Item'][$i]['ASIN'];


                $title = $array['Items']['Item'][$i]['ItemAttributes']['Title'];


                $manufacturer = $array['Items']['Item'][$i]['ItemAttributes']['Manufacturer'];

                // Creating array that will be returned with the Amazon products
                $amazon_products[] = array(
                    'ASIN' => $asin,
                    'title' => $title,
                    'percent' => '1',
                );

            }
        }
        // CHOOSE = 2 -> AWS_ITEMLOOKUP
        else if ($choose == 2)
        {
            $checkamazon = $array['Items']['Item']['OfferSummary'];

            $amazon_products = array();

                $price = $array['Items']['Item']['OfferSummary']['LowestNewPrice']['FormattedPrice'];

                $currency = $array['Items']['Item']['OfferSummary']['LowestNewPrice']['CurrencyCode'];

                // Creating array that will be returned with the Amazon products
                $amazon_products = array(
                    'price' => $price,
                    'currency' => $currency,
                );
        }
        return $amazon_products;
    }

    // Search for an item by keywords
    function aws_itemsearch($keywords) {
        return $this->aws_query(array (
            "Operation" => "ItemSearch",
            "SearchIndex" => "All",
            "ItemPage" => "1",
            "Keywords" => $keywords,
        ), 1);
    }

    //Search for an item by ASIN
    function aws_itemlookup($ASIN) {
        return $this->aws_query(array (
            "Operation" => "ItemLookup",
            "ResponseGroup" => "Offers",
            "IdType" => "ASIN",
            "ItemId" => $ASIN,
        ), 2);
    }

    // Argos - Get title from HTML
    function getTitleArgos($link) {
        $html = file_get_contents($link);
        if(!empty($html))
        {
            $matchCount = preg_match('/<h1 class="fn">(.*?)<\/h1>/s', $html, $matches);

            if($matchCount != 0)
            {
                return $matches[1];
            }
        }
    }

    // Sort ARRAY by PERCENT
    function sortByOrder($a, $b) {
        return $b['percent'] - $a['percent'];
    }

    // Main Function to get Amazon product
    function getPrice($link)
    {
        $argosTitle = $this->getTitleArgos($link);

        //Splitting title received from Argos in middle
        $firstFive = explode(' ', $argosTitle);

        $first_part = trim(implode(" ", array_splice($firstFive, 0, 5)));

        $products = $this->aws_itemsearch($first_part);

        if(!empty($products)) {

            // Update product's percentage based on greatest
            foreach ($products as &$product) {
                similar_text($product['title'], $argosTitle, $percent);
                $product['percent'] = round($percent);
            }

            // Sort array based on percentage
            usort($products, array($this, "sortByOrder"));

            $productz = $this->aws_itemlookup($products[0]['ASIN']);

            $amazon_information = array(
                'price' => $productz['price'],
                'currency' => $productz['currency'],
                'percent' => $products[0]['percent']
            );

            return $amazon_information;
        }
    }

    // Used to compare the prices between the products
    function comparePrice($string1, $string2)
    {
        if((!empty($string1))&&(!empty($string2)))
        $x = (($string1/$string2)-1)*100;
        if ($x <0 )
        {
            $x *= -1;
            $comparePrice[] = array(
                'state' => 'Cheaper',
                'percent' => round($x).'%',
             );
            return $comparePrice;
        }
        else
        {
            $comparePrice[] = array(
                'state' => 'More Expensive',
                'percent' => round($x).'%',
             );
            return $comparePrice;
        }
    }

    // Compare the three prices to find out if Argos was more expensive or not than competitors
    // Use the following order to aviod confusion.
    // ARGOS , AMAZON , WALMART
    function compareThreePrices($argos, $amazon, $walmart)
    {
        $amazonPrice = substr($amazon,2);
        $comparePrice = array();

        // MINIMUM PRICE BETWEEN THE THREE
        $minPrice = min($argos, $amazonPrice, $walmart);

        // If Argos has min price
        if ($minPrice == $argos)
        {
            // We find minimum between the rest
            $minPrice = min($amazonPrice, $walmart);
            // If it is Amazon
            if ($minPrice == $amazonPrice)
            {
                // Argos "SAVED YOU" Amazon price - Argos price
                $youSave = $amazonPrice - $argos;
                $comparePrice = array(
                'state' => 'You save',
                'pounds' => '£'.$youSave,
                );
            return $comparePrice;
            }
            // If it is Walmart
            else if ($minPrice == $walmart)
            {
                // Argos "SAVED YOU" Walmart price - Argos price
                $youSave = $walmart - $argos;
                $comparePrice = array(
                'state' => 'You save',
                'pounds' => '£'.$youSave,
                );
            return $comparePrice;
            }
        }
        // If Amazon has min price
        else if($minPrice == $amazonPrice)
        {
                // Argos is more expensive by (Amazon price - Argos price)
                // * (-1) as it would be negative
                $youSave = $amazonPrice - $argos;
                $comparePrice = array(
                'state' => 'More Expensive by',
                'pounds' => '£'.$youSave*(-1),
                );
            return $comparePrice;
        }
        else if($minPrice == $walmart)
        {
                // Argos is more expensive by (Walmart price - Argos price)
                // * (-1) as it would be negative
                $youSave = $walmart - $argos;
                $comparePrice = array(
                'state' => 'More Expensive by',
                'pounds' => '£'.$youSave*(-1),
                );
            // Return array
            return $comparePrice;
        }
    }

    // Show '-' if string is empty.
    function isEmpty($string){
        if(empty($string))
        {
            echo 'N/A';
        }
        else
        {
            echo $string;
        }
    }

    // Generate a random FLOAT number
    // MIN, MAX, DECIMALS
    function frand($min, $max, $decimals = 0) {
      $scale = pow(10, $decimals);
      return mt_rand($min * $scale, $max * $scale) / $scale;
    }
}
?>
