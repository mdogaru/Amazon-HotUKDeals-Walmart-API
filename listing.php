<!DOCTYPE html>
<html lang="en">

    <?php
    // MARK: IMPORTS
    include_once 'header.php' ?>


    <?php
    // MARK: AMAZON
    require_once("amazon.php");
    ?>

    <?php
    // MARK: WALMART
    require_once("walmart.php");
    ?>

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Amazon/HUKD/Walmart API Exercise 2016</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

    <!-- Custom CSS -->
    <link href="css/shop-item.css" rel="stylesheet">

</head>

<body>

    <?php

    // GET ARRAY FROM POST - INDEX.PHP
    $product = $_POST['result'];
    $fake = $_POST['fake'];

    // UNSERIALIZE ARRAY TO GET STRINGS
    $array_var = unserialize(base64_decode($product));

    if(!empty($array_var)) {

    	// CREATE AMAZON OBJECT
    	$amazonObject = new AmazonAPI();
    	// GET PRICE FOR productlink - AMAZON
        $amazon_info = $amazonObject->getPrice($array_var["productLink"]);

        if(empty($amazon_info) && $fake)
        {
            $amazon_info = array(
                'price' => '£'.$amazonObject->frand($array_var['price']/2, $array_var['price']*2, 2),
                'percent' => rand(40, 100),
            );
        }

        // CREATE WALMART OBJECT
        $walmartObject = new WalmartAPI();
        // GET PRICE FOR productlink - WALMART
        $walmart_info = $walmartObject->getPrice($array_var["productLink"]);

        if(empty($walmart_info) && $fake)
        {
            $walmart_info = array(
                'price' => $amazonObject->frand($array_var['price']/2, $array_var['price']*2, 2),
                'percent' => rand(40, 100),
            );
        }

        $argosSavings = array();
        $argosVSwalmart[] = array();
        $argosVSamazon[] = array();

    	if( (!empty($array_var['price'])) && (!empty($amazon_info["price"])) )
    	{
    		// Remove Pound sign
    		$amazonPrice = substr($amazon_info["price"],2);

    		// Compare price between ARGOS and AMAZON
    		$argosVSamazon = $amazonObject->comparePrice($amazonPrice, $array_var['price']);
    	}

    	if( (!empty($array_var['price'])) && (!empty($walmart_info['price'])) )
    	{
    		// Compare price between ARGOS and WALMART
    		$argosVSwalmart = $amazonObject->comparePrice($walmart_info['price'], $array_var['price']);
    	}

    	if( (!empty($array_var['price'])) && (!empty($walmart_info['price'])) && (!empty($amazon_info["price"])))
    	{
    		// Compare price between all 3, if they exist
    		$argosSavings = $amazonObject->compareThreePrices($array_var['price'], $amazon_info["price"], $walmart_info['price']);
    	}
    ?>

    <!-- Page Content -->
    <div class="container">

        <div class="row">

            <div class="col-md-3 text-center">
			<h3 style="font-weight:bold"> Glancing at the facts </h3>
				<div class="row">
					<div class="col-lg-12 col-sm-12">
					  <div class="circle-tile ">
						<a href="<?php echo $array_var['productLink']; ?>"><div class="circle-tile-heading dark-argos"><i class="fa fa-font fa-fw fa-3x"></i></div></a>
						<div class="circle-tile-content dark-argos">
						  <div class="circle-tile-description text-faded"> <?php $amazonObject->isEmpty($argosSavings['state']); ?> </div>
						  <div class="circle-tile-number text-white "> <?php $amazonObject->isEmpty($argosSavings['pounds']); ?> </div>
						  <div class="circle-tile-footer text-white">WITH ARGOS</div>
						</div>
					  </div>
					</div>
				</div>

				<div class="row">
					<div class="col-lg-12 col-sm-12">
					  <div class="circle-tile ">
						<a><div class="circle-tile-heading dark-yellow"><i class="fa fa-amazon fa-fw fa-3x"></i></div></a>
						<div class="circle-tile-content dark-yellow">
						  <div class="circle-tile-description text-faded"> Amazon was </div>
						  <div class="circle-tile-number text-white "><?php $amazonObject->isEmpty($argosVSamazon[0]['percent']); ?></div>
						  <div class="circle-tile-footer text-white"><?php $amazonObject->isEmpty($argosVSamazon[0]['state']); ?></div>
						</div>
					  </div>
					</div>
				</div>

				<div class="row">
					<div class="col-lg-12 col-sm-12">
					  <div class="circle-tile ">
						<a><div class="circle-tile-heading dark-walmart"><i class="fa fa-google-wallet fa-fw fa-3x"></i></div></a>
						<div class="circle-tile-content dark-walmart">
						  <div class="circle-tile-description text-faded" style="font-weight:bold"> Walmart was </div>
						  <div class="circle-tile-number text-white "><?php $amazonObject->isEmpty($argosVSwalmart[0]['percent']); ?></div>
						  <div class="circle-tile-footer text-white"><?php $amazonObject->isEmpty($argosVSwalmart[0]['state']); ?></div>
						</div>
					  </div>
					</div>
				</div>
            </div>

            <div class="col-md-9">

                <div class="thumbnail">
                    <img class="img-responsive" src="<?php echo $array_var['image_hi']; ?>" alt="">
                    <div class="caption">
						<div class="row">

		                    <div class="col-sm-10">
		                    	<h4 class = "producttitle"><a href="<?php echo $array_var['dealLink']; ?>"><?php echo $array_var['title']; ?></a>
		                        </h4>
		                    </div>

							<div class="col-sm-2">
		                        <h4 class="pull-right pricetext">£<?php echo $array_var['price']; ?></h4>
		                    </div>

                   		</div>
                        <p class = "productdescription"><?php echo $array_var['description']; ?> </p>

						<div class="btn-group btn-group-justified">
							<a href = "<?php echo $array_var['dealLink']; ?>" class = "btn btn-primary btn-sm buttontext" role = "button">
							View Deal on Hot UK Deal
							</a>

							<a href = "<?php echo $array_var['productLink']; ?>" class = "btn btn-default btn-sm fire buttontext" role = "button"><i class="glyphicon glyphicon-flash"></i>
							View Product on Argos
							</a>
						</div>

						<div class="row">
						  <div class="col-sm-12 text-center pricecomparison">
							<p class="fire temperature"> <span style="color:#404040">Hot UK Deal Temperature  </span><i class="glyphicon glyphicon-fire fire"></i> <?php echo $array_var['temperature']; ?> </p>
						  </div>
						</div>


						<div class="row">
						<div class="col-md-6">
						  <div class="col-sm-8 pull-left pricecomparison">
							<p> <i class="fa fa-amazon amazon"></i> Amazon Price </p>
						  </div>
						  <div class="col-sm-4 pull-right pricecomparison">
							<p style="font-weight:bold"> <?php $amazonObject->isEmpty($amazon_info['price']); ?> </p>
						  </div>
						</div>

						<div class="col-md-6">
						  <div class="col-sm-8 pull-left pricecomparison">
						    <p> <i class="fa fa-google-wallet walmart"></i> Walmart Price</p>
						  </div>
						  <div class="col-sm-4 text-right pricecomparison">
							<p style="font-weight:bold"> £<?php $amazonObject->isEmpty($walmart_info['price']); ?> </p>
						  </div>
						</div>
					    </div>

						<div class="row">
						<div class="col-md-6 text-center pricecomparison">
							<p>Match: <?php $amazonObject->isEmpty($amazon_info['percent']); ?> <span> <i class="fa fa-percent amazon"></i> </span></p>
						</div>

						<div class="col-md-6 text-center pricecomparison">
							<p>Match: <?php $amazonObject->isEmpty($walmart_info['percent']); ?> <span> <i class="fa fa-percent walmart"></i> </span> </p>
						</div>
						</div>
                    </div>

                </div>

			</div>

        </div>
        <?php
        if((empty($amazon_info))||(empty($walmart_info)))
        {
        	$fake = true;
        	echo '
                    <div class="col-sm-12 col-lg-12 col-md-12">
                        <form method="post" action="listing.php">
	                        <h4><a>Bad news.</a>
	                        </h4>
	                        <p>If you are seeing this message, it means that Walmart or Amazon failed to give a result. But I can help with that. Press the button below to add some fake, random data to have the page at it\'s full potential.
	                        </p>
	                        <input type="hidden" name="fake" value="'.$fake.'">
	                        <input type="hidden" name="result" value="'.$product.'">
                            <button type="submit" class = "btn btn-default btn-sm fire buttontext">
                                <i class="fa fa-thumbs-o-up"></i> Fake it
                            </button>

                    	</form>
                    </div>
        		 ';
        }

    }

        else {
        	echo '<div class="row text-center">
        				<p style="font-size:32px"> Oops. An error occured.</p>
        		  </div>

        		  <div class="row text-center">
        		  		<span> <i class="fa fa-frown-o fa-4x"></i>
        		  </div>

        		  <div style="padding-top:10px" class="row text-center">
        		 		 <a href = "index.php" class = "btn btn-default btn-lg" role = "button"><i class="fa fa-arrow-left"></i>
        		 		 Go back
                         </a>
        		  </div>
        		 ';
        }?>
    </div>
    <!-- /.container -->

    <div class="container">

        <hr>

        <!-- Footer -->
        <footer>
            <div class="row">
                <div class="col-lg-12">
                    <p>Amazon/HUKD/Walmart API Exercise 2016</p>
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
