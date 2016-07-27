
<?php

	class HotUKDealsAPI{

	// The API Key & URL will be used in functions to get data from HUKD
	private $api_key = 'API_KEY';

	// API URL
	private $api_url = 'http://api.hotukdeals.com/rest_api/v2/?key=';

	private $order_by = 'hot';

	private $type_of  = 'deals';

	private $merchant = 'argos';

	private $results_per_page = '10';

	// Function to send HTTP POST Requests
	// Used by every function below to make HTTP POST call
	function sendRequest($calledFunction){

		// Creates the endpoint URL and adds the Key to the passed array and the function's parameters'
		$request_url = $this->api_url.$this->api_key.$calledFunction;

		// Preparing Query...
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		curl_close($ch);

		// Will print back the response from the call
		// Used for troubleshooting/debugging
		if(!$response){
			return false;
		}

		// Return the data in JSON format
		$argosDeals = simplexml_load_string($response);

	    $deals = $argosDeals->deals;

	    $argos_products = array();

	    // Go through all the "deals"
	    foreach ($deals->api_item as $api_item)
	      {
	      	  // Saving deals (Title, Deal Link, Description, Image, Product Link, Temp, Price, Hi Res Image)
	          $title = htmlspecialchars($api_item->title);

	          $dealLink = $api_item->deal_link;

	          $description = $api_item->description;

	          $image = $api_item->deal_image;

	          $linkAdd = substr(strrchr($image, "/"), 1, strpos(strrchr($image, "/"), ".")-1);

	          $productLink = "http://www.hotukdeals.com/visit?m=5&q=".$linkAdd;

	          $temperature = $api_item->temperature;

	          $matchCount = preg_match('/\£?([0-9]+[\.]*[0-9]*)/', $title, $match);

	          if ($matchCount != 0)
	          {
		          $price = $match[1];
		      }
		      else
		      {
		      	  $price = $this->getPriceArgos($productLink);
		      }
		          $image_high_res = $api_item->deal_image_highres;

		          // Creating array that will be returned with the argos products
		          $argos_products[] = array(
					'title' => (string)$title,
		    		'dealLink' => (string)$dealLink,
		    		'description' => (string)$description,
		    		'image' => (string)$image,
		    		'image_hi' => (string)$image_high_res,
		    		'productLink' => (string)$productLink,
		    		'temperature' => (string)$temperature,
		    		'price' => (string)$price,

				  );

	      }

	      // Sort Products by Temperature
	      usort($argos_products, array($this, "sortByOrder"));

		  return $argos_products;
	}


		//function for showing '..' if string is bigger than a maximum set value
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

		// Get Deals from HUKD
		function getDeals(){
			$hot = '&order='.$this->order_by;
			$type_of = '&forum='.$this->type_of;
			$merchant = '&merchant='.$this->merchant;
			$results_per_page = '&results_per_page='.$this->results_per_page;

			return $this->sendRequest($hot.$type_of.$merchant.$results_per_page);
		}

		// Sorting HUKD deals by temperature DESC
		function sortByOrder($a, $b) {
	    	return $b['temperature'] - $a['temperature'];
		}

	    // Argos - Get price from HTML
	    function getPriceArgos($link) {
	        $html = file_get_contents($link);
	        if(!empty($html))
	        {
	            $matchCount = preg_match('/<span class="price"><abbr lang="en" class="currency" title="GBP">&pound;<\/abbr>(.*?)<\/span>/s', $html, $matches);

	            if($matchCount != 0)
	            {
	                return $matches[1];
	            }
	        }
	    }

	}
?>
