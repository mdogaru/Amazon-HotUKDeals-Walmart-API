<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Amazon/HUKD/Walmart API Example 2016</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <!-- Custom CSS -->
    <link href="css/argos.css" rel="stylesheet">

</head>

<body>

    <?php
    // MARK: IMPORTS
    include_once 'header.php' ?>


    <?php
    // MARK: HUKD
    require_once("hukd.php");
    ?>

    <!-- Page Content -->
    <div class="container">

        <div class="row">

                <div class="row carousel-holder">

                    <?php

                    // MARK: MAIN BODY

                    // Getting HUKD deals
                    $prodObject = new HotUKDealsAPI();

                    // Getting list of products - LIVE Updates
                    $products = $prodObject->getDeals();

                    $fake = false;

                    $data = serialize(($products));
                    if(!empty($products)) {

                        foreach ($products as $product) {

                           echo '<div class="col-md-15 col-sm-3">
                                     <div class="thumbnail">
                                      <img style="height: 200px; width: 100%; display: block;" src="'.$product["image"].'" data-holder-rendered="true">
                                      <div class="caption">

                                        <div class = "producttitle"> <a href = "'.$product["dealLink"].'"> <h5 id="thumbnail-label">'.$product["title"].' </a> </h5></div>
                                        <p class = "productdescription">'.$prodObject->adjustLength($product["description"], 150).'</p>
                                        <div class="productprice pricetext">

                                                <div class="pull-right">
                                                    <p class="glyphicon glyphicon-fire fire" role="button"> '.$product["temperature"].' </p>
                                                </div>

                                               <div>£'.$product["price"].'</div>
                                        </div>


                                        <div class="btn-group btn-group-justified buttonlinks">
                                            <a href = "'.$product["dealLink"].'" class = "btn btn-primary btn-sm buttontext" role = "button">
                                            View Deal
                                            </a>

                                            <a href = "'.$product["productLink"].'" class = "btn btn-default btn-sm fire buttontext" role = "button"><i class="glyphicon glyphicon-flash"></i>
                                            Product
                                            </a>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12 amazonprice pricetext text-center">
                                            <form method="post" action="listing.php">
                                                <input type="hidden" name="fake" value="'.$fake.'">
                                                <input type="hidden" name="result" value="'.base64_encode(serialize($product)).'">
                                                <button type="submit" class = "amazon btn btn-default btn-sm fire ">
                                                    <i class="icon-circle-arrow-right icon-large"></i> View More Details
                                                </button>
                                            </form>
                                            </div>
                                        </div>


                                      </div>
                                    </div>
                                </div>';
                        }

                    }
                    else
                    {
                            for ($i = 1; $i <= 10; $i++) {
                                    echo '<div class="col-md-15 col-sm-3">
                                          <div class="thumbnail">
                                            <img src="images/nocontent.jpg" alt="">
                                            <div style="height:55px" class="caption">
                                                <h4><a href="#">No Content</a>
                                                </h4>
                                            </div>
                                        </div>
                                        </div>';
                                }
                    }
                    ?>

                </div>

        </div>

    </div>
    <!-- /.container -->

    <div class="container">

        <hr>

        <!-- Footer -->
        <footer>
            <div class="row">
                <div class="col-lg-12">
                    <p>Amazon/HUKD/Walmart API Example 2016</p>
                </div>
            </div>
        </footer>

    </div>
    <!-- /.container -->

    <!-- jQuery -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

</body>

</html>
