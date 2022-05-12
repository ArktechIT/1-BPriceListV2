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

$partId = $_GET['partId'];
?>
<html>
	<body>
		<table border = 1>
			<tr>
				<th><?php echo displayText('L1508');//History Id?></th>
				<th><?php echo displayText('L52');//ark Part Id?></th>
				<th><?php echo displayText('L667');//Accessory Price?></th>
				<th><?php echo displayText('L668');//Material Price?></th>
				<th><?php echo displayText('L669');//Subcon Price?></th>
				<th><?php echo displayText('L670');//Production Price?></th>
				<th><?php echo displayText('L1498');//Delivery Price?></th>
				<th><?php echo displayText('L267');//Price?></th>
				<th><?php echo displayText('L112');//Currency?></th>
				<th><?php echo displayText('L1511');//Price Bracket?></th>
				<th><?php echo displayText('L611');//Price Date?></th>
				<th><?php echo displayText('L1504');//Quotation Date?></th>
				<th><?php echo displayText('L1513');//update Incharge?></th>
			</tr>

<?php
$sql = "SELECT * FROM sales_pricelisthistory WHERE arkPartId = '".$partId."'";
$historyQuery = $db->query($sql);
if($historyQuery AND $historyQuery->num_rows > 0)
{
	while($historyResult = $historyQuery->fetch_assoc())	
	{
		$historyId = $historyResult['historyId'];
		$arkPartId = $historyResult['arkPartId'];
		$accessoryPrice = $historyResult['accessoryPrice'];
		$materialPrice = $historyResult['materialPrice'];
		$subconPrice = $historyResult['subconPrice'];
		$productionPrice = $historyResult['productionPrice'];
		$deliveryPrice = $historyResult['deliveryPrice'];
		$price = $historyResult['price'];
		$currency = $historyResult['currency'];
		$curr=""; if($currency==1){$curr="Dollar";}if($currency==2){$curr="Peso";}if($currency==3){$curr="Yen";}
		$priceBracket = $historyResult['priceBracket'];
		$priceDate = $historyResult['priceDate'];
		$quotationDate = $historyResult['quotationDate'];
		$updateInCharge = $historyResult['updateInCharge'];
		
		echo
		"
			<tr>
				<td>".$historyId."</td>
				<td>".$arkPartId."</td>
				<td>".$accessoryPrice."</td>
				<td>".$materialPrice."</td>
				<td>".$subconPrice."</td>
				<td>".$productionPrice."</td>
				<td>".$deliveryPrice."</td>
				<td>".$price."</td>
				<td>".$curr."</td>
				<td>".$priceBracket."</td>
				<td>".$priceDate."</td>
				<td>".$quotationDate."</td>
				<td>".$updateInCharge."</td>
			</tr>
		";
	}
}
?>
		</table>
	</body>
</html>
