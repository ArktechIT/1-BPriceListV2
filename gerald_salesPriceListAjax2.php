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
	include('PHP Modules/rose_prodfunctions.php');
	ini_set("display_errors", "on");

	$export = (isset($_POST['export'])) ? $_POST['export'] : '';
	$groupNo = $_POST['groupNo'];
	$sqlFilter = $_POST['sqlFilter'];
	$queryLimit = 50;
	$queryPosition = ($groupNo * $queryLimit);
	
	if($export!='')
	{
		$thead = $_POST['thead'];
		$titleName = 'Price List('.date('ymdhis').')';
		$filename = $titleName.'.xls';
		header('Content-type: application/ms-excel');
		header('Content-Disposition: attachment; filename='.$filename);
		
		if($export=='exportSelected')
		{
			$partIdArray = $_POST['partIdArray'];
			$sqlFilter = "WHERE partId IN('".implode("','",$partIdArray)."')";
		}
	}
	
	$sqlLimit = ($export=='') ? "LIMIT ".$queryPosition.", ".$queryLimit : "";
	
    $count = $queryPosition;
    $sqlFilter = " WHERE partId IN (SELECT arkPartId FROM sales_parts)";
	echo $sql = "SELECT partId, customerPartNumber, partNumber, partName, revisionId, partNote, customerId, itemGroup FROM cadcam_parts ".$sqlFilter." ORDER BY partId DESC";
	$sqlMain = $sql;
	$query = $db->query($sql);
	if($query->num_rows > 0)
	{
		//~ $tableContent = "<tr><td colspan='14'>".$sqlMain."</td></tr>";
		while($result = $query->fetch_array())
		{
			$partId = $result['partId'];
			$customerPartNumber = $result['customerPartNumber'];
			$partNumber = $result['partNumber'];
			$partName = $result['partName'];
			$revisionId = $result['revisionId'];
			$partNote = $result['partNote'];
			$customerId = $result['customerId'];
			$itemGroup = $result['itemGroup'];
			
			$custDrawing = "<td></td>";
			$customerDrawing = "../../Document Management System/Master Folder/MAIN_".$partId.".pdf";
			if(file_exists($customerDrawing))
			{
				$custDrawing = "<td align='center'>
					<input type='image' onmouseover=\"TINY.box.show({boxid:'box',openjs:function(){jsFunctions();},iframe:'".$customerDrawing."#zoom=60',post:'',width:'1200',height:'550',opacity:10,topsplit:6,animate:false,close:true,mask:false,left:-10})\" src='../Common Data/Templates/images/view1.png' width=15 height=15 title='Details'>
				</td>";				
			}
			
			$customerName = '';
			$sql = "SELECT customerName FROM sales_customer WHERE customerId = ".$customerId." LIMIT 1";
			$queryCustomer = $db->query($sql);
			if($queryCustomer AND $queryCustomer->num_rows > 0)
			{
				$resultCustomer = $queryCustomer->fetch_array();
				$customerName = $resultCustomer['customerName'];
			}
			
			$nameLength = strlen($partName);
			$partName = ($nameLength > 50) ? trim(substr($partName,0,49))."..." : trim($partName);
			
			$priceFlag = 0;
			$priceTd = "<td colspan='7' align='center'><span onclick=\" openModalBox('gerald_salesPriceListModalBox.php?modalBoxType=1',jsFunctions,'','partId=".$partId."') \" style='cursor:pointer;color:blue;text-decoration:underline;'>NO PRICE</span></td>";
			$priceOnClick = $accessoryPrice = $materialPrice = $subconPrice = $productionPrice = $deliveryPrice = $price = $currency = $currencyValue = '';
			$sql = "SELECT accessoryPrice, materialPrice, subconPrice, productionPrice,deliveryPrice,price,currency FROM sales_pricelist WHERE arkPartId = ".$partId." LIMIT 1";
			$queryPriceList = $db->query($sql);
			if($queryPriceList AND $queryPriceList->num_rows > 0)
			{
				$resultPriceList = $queryPriceList->fetch_assoc();
				$accessoryPrice = $resultPriceList['accessoryPrice'];
				$materialPrice = $resultPriceList['materialPrice'];
				$subconPrice = $resultPriceList['subconPrice'];
				$productionPrice = $resultPriceList['productionPrice'];
				$deliveryPrice = $resultPriceList['deliveryPrice'];
				$price = $resultPriceList['price'];
				$currency = $resultPriceList['currency'];
				$SubColor="";
				if($price<$subconPrice){$SubColor="< bgcolor=red>";}
				$subconPrice=getSubconPrice($partId);
				$priceFlag = 1;
				$priceOnClick = "openModalBox('gerald_salesPriceListModalBox.php?modalBoxType=2',jsFunctions,'','partId=".$partId."')";
				
				if($currency==1)		$currencyValue = 'Dollar';
				else if($currency==2)	$currencyValue = 'Peso';
				else if($currency==3)	$currencyValue = 'Yen';				
				
				$priceSpan = "<span onclick=\" ".$priceOnClick." \" style='cursor:pointer;color:blue;text-decoration:underline;'>".$price."</span>";
				
				if($export=='export2')
				{
					$priceTd = "
						<td align='center'>".$currencyValue."</td>
						<td align='right'>".$priceSpan."</td>
					";
				}
				else
				{
					$priceTd = "
						<td align='center'>".$currencyValue."</td>
						<td align='right'>".$accessoryPrice."</td>
						<td align='right'>".$materialPrice."</td>
						<td align='right'".$SubColor.">".$subconPrice."</td>
						<td align='right'>".$productionPrice."</td>
						<td align='right'>".$deliveryPrice."</td>
						<td align='right'>".$priceSpan."</td>
					";
				}
			}
			else
			{
				if($export=='export2')
				{
					continue;
				}
			}
			
			$clickData = $cursorStyle = "";
			if(trim($itemGroup) != "")
			{
				$cursorStyle = "style = 'cursor:pointer;'";
				$clickData = "onclick=\"TINY.box.show({url:'raymond_editItemGroup.php?partId=".$partId."',width:'480',height:'',opacity:10,topsplit:6,animate:false,close:false,openjs:function(){openCustomJS()}});\"";
			}

			if($_SESSION['idNumber'] == "0412")
			{
				if($priceFlag == 0)
				{
					$tableContent .= "
					<tr>
						<td align = 'center'>".++$count."</td>
						<td align = 'center'>".$partId."</td>
						<td>".$customerName."</td>
						<td>".$customerPartNumber."</td>
						<td>".trim($partNumber)."</td>
						<td>".$revisionId."</td>
						<td>".$partNote."</td>
						<td>".$partName."</td>
						<td align = 'center' ".$cursorStyle." ".$clickData.">".$itemGroup."</td>
						".$priceTd."
						".$custDrawing."
					</tr>";
				}
			}
			else
			{
				$tableContent .= "
				<tr>
					<td align = 'center'>".++$count."</td>
					<td align = 'center'>".$partId."</td>
					<td>".$customerName."</td>
					<td>".$customerPartNumber."</td>
					<td>".trim($partNumber)."</td>
					<td>".$revisionId."</td>
					<td>".$partNote."</td>
					<td>".$partName."</td>
					<td align = 'center' ".$cursorStyle." ".$clickData.">".$itemGroup."</td>
					".$priceTd."
					".$custDrawing."
				</tr>";
			}
			
        }
        
		echo "<table border='1'>".$thead.$tableContent."</table>";
	}
					
?>
