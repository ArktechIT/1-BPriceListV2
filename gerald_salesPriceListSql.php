<?php
	include $_SERVER['DOCUMENT_ROOT']."/version.php";
	$path = $_SERVER['DOCUMENT_ROOT']."/".v."/Common Data/";
	$javascriptLib = "/".v."/Common Data/Libraries/Javascript/";
	$templates = "/".v."/Common Data/Libraries/Javascript/";
	set_include_path($path);    
	include('PHP Modules/mysqliConnection.php');
	include('PHP Modules/anthony_wholeNumber.php');
	include('PHP Modules/anthony_retrieveText.php');
	include('PHP Modules/gerald_functions.php');
	ini_set("display_errors", "on");

	if(isset($_GET['sqlType']))
	{
		if($_GET['sqlType']=='multiplePriceSql')
		{
			$partId = $_POST['partId'];
			$priceLowerRangeArray = $_POST['priceLowerRangeArray'];
			$priceUpperRangeArray = $_POST['priceUpperRangeArray'];
			$currencyArray = $_POST['currencyArray'];
			$accessoryPriceArray = $_POST['accessoryPriceArray'];
			$materialPriceArray = $_POST['materialPriceArray'];
			$subconPriceArray = $_POST['subconPriceArray'];
			$productionPriceArray = $_POST['productionPriceArray'];
			$deliveryPriceArray = $_POST['deliveryPriceArray'];
			$priceArray = $_POST['priceArray'];
			
			$priceIdArray = (isset($_POST['priceIdArray'])) ? $_POST['priceIdArray'] : array();
			
			foreach($priceIdArray as $key => $priceId)
			{
				$priceLowerRange = $priceLowerRangeArray[$key];
				$priceUpperRange = $priceUpperRangeArray[$key];
				$currency = $currencyArray[$key];
				$accessoryPrice = $accessoryPriceArray[$key];
				$materialPrice = $materialPriceArray[$key];
				$subconPrice = $subconPriceArray[$key];
				$productionPrice = $productionPriceArray[$key];
				$deliveryPrice = $deliveryPriceArray[$key];
				$price = $priceArray[$key];
				
				if($priceUpperRange > 0 AND $priceLowerRange == 0)
				{
					$sql = "SELECT SUBSTRING_INDEX(priceBracket,'-',-1) as `priceUpperRange` FROM sales_pricelist WHERE arkPartId = ".$partId." AND priceId != ".$priceId." ORDER BY priceId DESC LIMIT 1";
					$queryRange = $db->query($sql);
					if($queryRange->num_rows > 0)
					{
						$resultRange = $queryRange->fetch_array();
						$priceLowerRange = $resultRange['priceUpperRange'];
					}
					$priceLowerRange++;
				}
				
				$priceBracket = $priceLowerRange."-".$priceUpperRange;
				if($priceBracket=='0-0')	$priceBracket = '-';
				
				if($priceId > 0)
				{
					$sql = "
						INSERT INTO `sales_pricelisthistory`
								(	`arkPartId`, `currency`, `accessoryPrice`, `materialPrice`, `subconPrice`, `productionPrice`,`deliveryPrice`, `price`, `priceBracket`, `priceDate`,`quotationDate`, `updateInCharge`)
						SELECT 		`arkPartId`, `currency`, `accessoryPrice`, `materialPrice`, `subconPrice`, `productionPrice`,`deliveryPrice`, `price`, `priceBracket`, `priceDate`,`quotationDate`, `updateInCharge`
						FROM		`sales_pricelist`
						WHERE		`priceId` = ".$priceId." LIMIT 1
					";
					$queryInsert = $db->query($sql);
					
					$sql = "
						UPDATE	`sales_pricelist`
						SET		`currency` = '".$currency."',
								`accessoryPrice` = '".$accessoryPrice."',
								`materialPrice` = '".$materialPrice."',
								`subconPrice` = '".$subconPrice."',
								`productionPrice` = '".$productionPrice."',
								`deliveryPrice` = '".$deliveryPrice."',
								`price` = '".$price."',
								`priceBracket` = '".$priceBracket."',
								`priceDate` = NOW(),
								`updateInCharge` = '".$_SESSION['idNumber']."'
						WHERE	`priceId` = ".$priceId." LIMIT 1";
					$queryUpdate = $db->query($sql);
				}
			}
			
			header('location:gerald_salesPriceList.php?partId='.$partId);
		}
		else if($_GET['sqlType']=='priceSql')
		{
			$partId = $_POST['partId'];
			$priceLowerRange = (isset($_POST['priceLowerRange'])) ? $_POST['priceLowerRange'] : 0;
			$priceUpperRange = $_POST['priceUpperRange'];
			$currency = $_POST['currency'];
			$accessoryPrice = $_POST['accessoryPrice'];
			$materialPrice = $_POST['materialPrice'];
			$subconPrice = $_POST['subconPrice'];
			$productionPrice = $_POST['productionPrice'];
			$deliveryPrice = $_POST['deliveryPrice'];
			$price = $_POST['price'];
			$quotationDate = $_POST['quotationDate'];
			
			$priceId = (isset($_POST['priceId'])) ? $_POST['priceId'] : 0;
			
			if($priceUpperRange > 0 AND $priceLowerRange == 0)
			{
				$sql = "SELECT SUBSTRING_INDEX(priceBracket,'-',-1) as `priceUpperRange` FROM sales_pricelist WHERE arkPartId = ".$partId." AND priceId != ".$priceId." ORDER BY priceId DESC LIMIT 1";
				$queryRange = $db->query($sql);
				if($queryRange->num_rows > 0)
				{
					$resultRange = $queryRange->fetch_array();
					$priceLowerRange = $resultRange['priceUpperRange'];
				}
				$priceLowerRange++;
			}
			
			$priceBracket = $priceLowerRange."-".$priceUpperRange;
			if($priceBracket=='0-0')	$priceBracket = '-';
			
			if($priceId > 0)
			{
				$sql = "
					INSERT INTO `sales_pricelisthistory`
							(	`arkPartId`, `currency`, `accessoryPrice`, `materialPrice`, `subconPrice`, `productionPrice`,`deliveryPrice`, `price`, `priceBracket`, `priceDate`,`quotationDate`, `updateInCharge`)
					SELECT 		`arkPartId`, `currency`, `accessoryPrice`, `materialPrice`, `subconPrice`, `productionPrice`,`deliveryPrice`, `price`, `priceBracket`, `priceDate`,`quotationDate`, `updateInCharge`
					FROM		`sales_pricelist`
					WHERE		`priceId` = ".$priceId." LIMIT 1
				";
				$queryInsert = $db->query($sql);
				
				$sql = "
					UPDATE	`sales_pricelist`
					SET		`currency` = '".$currency."',
							`accessoryPrice` = '".$accessoryPrice."',
							`materialPrice` = '".$materialPrice."',
							`subconPrice` = '".$subconPrice."',
							`productionPrice` = '".$productionPrice."',
							`deliveryPrice` = '".$deliveryPrice."',
							`price` = '".$price."',
							`priceBracket` = '".$priceBracket."',
							`priceDate` = NOW(),
							`quotationDate` = '".$quotationDate."',
							`updateInCharge` = '".$_SESSION['idNumber']."'
					WHERE	`priceId` = ".$priceId." LIMIT 1";
				$queryUpdate = $db->query($sql);
			}
			else
			{
				 $sql = "
					INSERT INTO	`sales_pricelist`
							(	`arkPartId`,			`currency`,			`accessoryPrice`,		`materialPrice`,		`subconPrice`, 
								`productionPrice`,		`deliveryPrice`,		`price`,			`priceBracket`,			`priceDate`,
								`quotationDate`,			`updateInCharge`)
					VALUES	(	'".$partId."',			'".$currency."',		'".$accessoryPrice."',	'".$materialPrice."',	'".$subconPrice."',
								'".$productionPrice."',	'".$deliveryPrice."',	'".$price."',			'".$priceBracket."',	
								NOW(), 					'".$quotationDate."',	'".$_SESSION['idNumber']."')";
				$queryInsert = $db->query($sql);
			}
			
			header('location:gerald_salesPriceList.php?partId='.$partId);
		}
		else if($_GET['sqlType']=='deletePriceSql')
		{
			$partId = $_GET['partId'];
			$priceId = $_GET['priceId'];
			
			$sql = "
				INSERT INTO `sales_pricelisthistory`
						(	`historyId`, `arkPartId`, `currency`, `accessoryPrice`, `materialPrice`, `subconPrice`, `productionPrice`, `deliveryPrice`,`price`, `priceBracket`, `priceDate`,`quotationDate`, `updateInCharge`)
				SELECT 		`priceId`, 	 `arkPartId`, `currency`, `accessoryPrice`, `materialPrice`, `subconPrice`, `productionPrice`,	`deliveryPrice`, `price`, `priceBracket`, `priceDate`,`quotationDate`, `updateInCharge`
				FROM		`sales_pricelist`
				WHERE		`priceId` = ".$priceId." LIMIT 1
			";
			$queryInsert = $db->query($sql);			
			
			$sql = "DELETE FROM `sales_pricelist` WHERE priceId = ".$priceId." LIMIT 1";
			$queryDelete = $db->query($sql);
			
			header('location:gerald_salesPriceList.php?partId='.$partId);
		}
		else if($_GET['sqlType']=='addPartsSql')
		{
			$customerId = $_POST['customerId'];
			$partNumber = $_POST['partNumber'];
			$revisionId = $_POST['revisionId'];
			$partName = $_POST['partName'];
			$itemGroup = $_POST['itemGroup'];
			
			$sql = "SELECT partId FROM cadcam_parts WHERE partNumber LIKE '".trim($partNumber)."' AND revisionId LIKE '".trim($revisionId)."' LIMIT 1";
			$queryParts = $db->query($sql);
			if($queryParts AND $queryParts->num_rows > 0)
			{
				?>
				<script>
					alert('Parts already exist!');
					location.href='gerald_salesPriceList.php';
				</script>
				<?php
				exit(0);
			}
			
			$sql = "INSERT INTO	cadcam_parts 
							(	partNumber,					partName,		itemGroup,		revisionId,					customerId,			status)
					VALUES	(	'".trim($partNumber)."',	'".trim($partName)."',	'".trim($itemGroup)."',	'".trim($revisionId)."',	".$customerId.",	2) ";
			$queryInsert = $db->query($sql);
			
			$sql = "SELECT MAX(partId) AS maxPartId FROM cadcam_parts where partNumber = '".trim($partNumber)."'";
			$getMaxPartId = $db->query($sql);
			$getMaxPartIdResult = $getMaxPartId->fetch_array();
			$maxPartId = $getMaxPartIdResult['maxPartId'];
			
			$Users_IP_address = $_SERVER['REMOTE_ADDR'];
			$sesLOG = isset($_SESSION['userID']) ? $_SESSION['userID'] : "";
			if(trim($revisionId)!=''){ $logval=trim($partNumber)."-".trim($revisionId); } else{ $logval=trim($partNumber); }
			$sql = "INSERT INTO	system_partlog
							(	partId,			date,	query,	field,	oldValue,	newValue,		ip,							user,				details)
					VALUES	(	".$maxPartId.",	now(),	1,		1,		'',			'".$logval."',	'".$Users_IP_address."',	'".$sesLOG."-Manual Input',	'') ";
			$queryInsert = $db->query($sql);
			
			
			$sql = "SELECT arkPartId FROM sales_parts WHERE arkPartId = ".$maxPartId." LIMIT 1";
			$querySalesParts = $db->query($sql);
			if($querySalesParts->num_rows == 0)
			{
				$sql = "INSERT INTO	sales_parts
								(	arkPartId,		customerPartNumber,			customerPartName)
						VALUES	(	".$maxPartId.", '".trim($partNumber)."',	'".trim($partName)."') ";
				$queryInsert = $db->query($sql);
			}			
			
			header('location:gerald_salesPriceList.php?partId='.$maxPartId);
		}
	}	
?>
