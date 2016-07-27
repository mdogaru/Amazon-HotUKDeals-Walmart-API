<?php

class WalmartAPI{

/*The API Key WALMART*/
private $api_key = 'API_KEY';

// API URL
private $api_url = 'http://api.walmartlabs.com/v1/search?apiKey=';

// ORDER ASCENDING
private $order_by = 'asc';

/*Function to send HTTP POST Requests*/
/*Used by every function below to make HTTP POST call*/
function sendRequest($calledFunction){

    /*Creates the endpoint URL and adds the Key to the passed array and the function's parameters'*/
    $request_url = $this->api_url.$this->api_key.$calledFunction;

    /*Preparing Query...*/
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    /*Will print back the response from the call*/
    /*Used for troubleshooting/debugging        */
    if(!$response){
        return false;
    }

    /*Return the data in JSON format*/
    $walmartDeals = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);

    // Array holding products
    $walmart_products = array();

    foreach ($walmartDeals->items[0] as $api_item)
      {
          $title = htmlspecialchars($api_item->name);
          $price = $api_item->salePrice;
          $productLink = $api_item->productUrl;

          // Creating array that will be returned with the Walmart products
          $walmart_products[] = array(
            'title' => $title,
            'productLink' => $productLink,
            'price' => $price,
            'percent' => '1',

          );
      }

      return $walmart_products;
}


    //function for showing '..' if string is bigger than
    //a maximum set value (will be set to 150)
    function adjustLength($string, $max)
    {
        if (strlen($string) <=$max)
        {
            return $string;
        }
        else
        {
            return substr($string, 0, $max) . '..';
        }
    }

    // Query information for Walmart - Order by, Query (search keywords), XML format
    function getDeals($query){
        $hot = '&ord='.$this->order_by;
        $query = '&query='.$query;
        $format = '&format=xml';
        return $this->sendRequest($query.$hot.$format);
    }

    // Sorting HUKD deals by temperature DESC
    function sortByOrder($a, $b) {
        return $b['percent'] - $a['percent'];
    }

    // Argos - Get title from HTML which is between
    // <h1 class="fn"> ... </h1>
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

    // Main function that retrieves array with the product
    function getPrice($link)
    {
        $argosTitle = $this->getTitleArgos($link);

        $products = $this->getDeals(urlencode($argosTitle));

        if(!empty($products)) {

            // Update product's percentage based on greatest
            foreach ($products as &$product) {
                similar_text($product['title'], $argosTitle, $percent);
                $product['percent'] = round($percent);
            }

            // Sort array based on percentage
            usort($products, array($this, "sortByOrder"));

            $walmart_information = array(
                'price' => $products[0]['price'],
                'percent' => $products[0]['percent'],
            );

            return $walmart_information;
        }
    }
}

?>
