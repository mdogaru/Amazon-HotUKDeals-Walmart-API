 
## Prerequisites

What things you need to run the software

```
You will need server environment (local or public) that supports PHP. For the testing of the web 
application I used MAMP with all default settings.
```

Browser Compatibility
```
Tested with Safari 9 in El Capitan and Chrome, Macbook OS X 10.11 - should work fine in other 
browsers/operating systems too.
```
## Instructions

1. Download the repository as a zip.
2. Extract to a folder
```
The below instructions are for MAMP but you are welcome to use any other local or public server environment
```
3. Download MAMP http://www.mamp.info
4. An existing ‘htdocs’ folder should be moved to /Applications/MAMP
5. Copy contents for the extracted folder to ‘htdocs’ folder from step 4
6. Modify 'hukd.php', 'amazon.php', 'walmart.php' with you API Keys
7. Run MAMP and it’s server and go to localhost on your browser which should show the ‘index.php’ page.

## Functionalities

### Extract data from HUKD

* Done using 'hukd.php' - gets data from HUKD in JSON format which is 'cleaned up' and returns it

![Alt text](/Screenshots/index_page2.png)

### Comparing Prices

##### Amazon API


* Used the API with the ItemSearch
	** By using SearchIndex:All for categories and searching using keywords, the API will display 10 results and their corresponding ASIN (unique code)


**For relevant search results**, The title for the product is retrieved from the HTML of the product link. After getting 10 results, a comparison between the HTML-retrieved title and the titles from Amazon is made a "matching" processing is ran which will result in an ordered array sorted by the match percentage (>75 GOOD). I choose the product with highest and get the ASIN for it.


* Using ASIN, a query is ran in the ItemLookup API which requires an IDType (eg. ASIN) and retrieve the price from there
* Code is in 'amazon.php'
* It uses Amazon UK, not US.

##### Walmart API

*  Walmart is also added. I will offer the same matching percentage of the item as for Amazon although the use of the API is simpler.

### Display of information

#### Index.php
* 'index.php' is the first page - contains 10 products REsorted as HUKD temperature sorting does not work
*  displays Item Title, Description, Price, Temperature, URL for product and deal, Image
*  description is limited to 150 characters, expanded in detail page

#### Listing.php
* Page that users are directed to if user clicks on view more details
* Contains all the information from index.php + hi-quality image of product, along with the Price Comparison for the Amazon and Walmart API

* Displays NO-STATE if there is nothing retrieved by HUKD or if "listing.php" detail page went wrong
* Allow user to populate listing page with :Fake: data

![Alt text](/Screenshots/failed_retrieval.png)

* "Listing.php" - Column that contains following

It calculates the percentage of which both Amazon and Argos were Cheaper/More Expensive and displays data accordingly in two separate boxes (Displays information of type “ Amazon/Walmart was % (percent) Cheaper / More expensive)


## Authors

**Mihai Dogaru**

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

