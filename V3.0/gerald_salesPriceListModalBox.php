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
	
	if(isset($_POST['ajaxType']))
	{
		if($_POST['ajaxType']=='checkPrice')
		{
			echo "\n".$price = $_POST['price'];
			echo "\n".$accessoryPrice = $_POST['accessoryPrice'];
			echo "\n".$materialPrice = $_POST['materialPrice'];
			echo "\n".$subconPrice = $_POST['subconPrice'];
			echo "\n".$productionPrice = $_POST['productionPrice'];
			echo "\n".$deliveryPrice = $_POST['deliveryPrice'];
			
			echo "\n".$totalPrice = $accessoryPrice + $materialPrice + $subconPrice + $productionPrice + $deliveryPrice;
			
			//~ $price = (float) $price;
			//~ $totalPrice = (float) $totalPrice;
			
			echo "\n".$dif = $price - $totalPrice;
			
			if($totalPrice <= 0)
			{
				echo 'Total amount of sub price above must be greater than zero';
			}
			else if($dif!=0)
			{
				echo 'Price mismatch vs total sub price!';
			}			
		}
		exit(0);
	}

	if(isset($_GET['modalBoxType']))
	{
		if($_GET['modalBoxType']=='1')
		{
			$partId = (isset($_POST['partId'])) ? $_POST['partId'] : '';
			$priceId = (isset($_POST['priceId'])) ? $_POST['priceId'] : '';
			
			$buttonCaption = 'SUBMIT';
			$priceLowerRange = $priceUpperRange = $accessoryPrice = $materialPrice = $subconPrice = $productionPrice = $deliveryPrice = $price = 0;
			$currency = '';
			if($partId!='')
			{
				$sql = "SELECT SUBSTRING_INDEX(priceBracket,'-',-1) as priceUpperRange, currency FROM sales_pricelist WHERE arkPartId = ".$partId." ORDER BY priceId DESC LIMIT 1";
				$queryRange = $db->query($sql);
				if($queryRange->num_rows > 0)
				{
					$resultRange = $queryRange->fetch_array();
					$currency = $resultRange['currency'];
					$priceLowerRange = $priceUpperRange = $min = $resultRange['priceUpperRange'] + 1;
				}
			}
			else
			{
				$sql = "SELECT `arkPartId`, SUBSTRING_INDEX(priceBracket,'-',1) as `priceLowerRange`, SUBSTRING_INDEX(priceBracket,'-',-1) as `priceUpperRange`, `currency`, `accessoryPrice`, `materialPrice`, `subconPrice`, `productionPrice`,`deliveryPrice`, `price`,`quotationDate` FROM `sales_pricelist` WHERE `priceId` = ".$priceId." LIMIT 1";
				$queryPrice = $db->query($sql);
				if($queryPrice AND $queryPrice->num_rows > 0)
				{
					$resultPrice = $queryPrice->fetch_assoc();
					$partId = $resultPrice['arkPartId'];
					$priceLowerRange = $resultPrice['priceLowerRange'];
					$priceUpperRange = $resultPrice['priceUpperRange'];
					$currency = $resultPrice['currency'];
					$accessoryPrice = $resultPrice['accessoryPrice'];
					$materialPrice = $resultPrice['materialPrice'];
					$subconPrice = $resultPrice['subconPrice'];
					$productionPrice = $resultPrice['productionPrice'];
					$deliveryPrice = $resultPrice['deliveryPrice'];
					$price = $resultPrice['price'];
					$quotationDate = $resultPrice['quotationDate'];
					
					$lastPriceId = '';
					$sql = "SELECT priceId FROM sales_pricelist WHERE arkPartId = ".$partId." ORDER BY priceId DESC LIMIT 1";
					$queryRange = $db->query($sql);
					if($queryRange->num_rows > 0)
					{
						$resultRange = $queryRange->fetch_array();
						$lastPriceId = $resultRange['priceId'];
					}
					
					$sql = "SELECT SUBSTRING_INDEX(priceBracket,'-',-1) as `priceUpperRange` FROM sales_pricelist WHERE arkPartId = ".$partId." AND priceId != ".$priceId." AND SUBSTRING_INDEX(priceBracket,'-',1) < ".$priceLowerRange." ORDER BY priceId DESC LIMIT 1";
					$queryRange = $db->query($sql);
					if($queryRange->num_rows > 0)
					{
						$resultRange = $queryRange->fetch_array();
						$min = $resultRange['priceUpperRange'] + 1;
					}
					
					$sql = "SELECT SUBSTRING_INDEX(priceBracket,'-',-1) as `priceUpperRange` FROM sales_pricelist WHERE arkPartId = ".$partId." AND priceId > ".$priceId." ORDER BY priceId LIMIT 1";
					$queryRange = $db->query($sql);
					if($queryRange->num_rows > 0)
					{
						$resultRange = $queryRange->fetch_array();
						$max = $resultRange['priceUpperRange'] - 1;
					}
					
					$buttonCaption = 'UPDATE';
					echo "<input type='hidden' class='api-form' name='priceId' value='".$priceId."' required form='formId'>";
					//~ if($_GET['country']==1)
					//~ {
						echo "<input type='hidden' class='api-form' name='priceLowerRange' value='".$priceLowerRange."' required form='formId'>";
					//~ }
				}
			}
			
			$customerId = '';
			$sql = "SELECT customerId FROM cadcam_parts WHERE partId = ".$partId." LIMIT 1";
			$queryParts = $db->query($sql);
			if($queryParts AND $queryParts->num_rows > 0)
			{
				$resultParts = $queryParts->fetch_assoc();
				$customerId = $resultParts['customerId'];
			}
			
			if($priceUpperRange=='')	$priceUpperRange = 0;
			
			//~ echo getcwd();
			//~ echo "<br>".__DIR__;
			
			echo "<input type='hidden' class='api-form' name='partId' value='".$partId."' required form='formId'>";
			?>
			<form action='gerald_salesPriceListSql.php?sqlType=priceSql' method='post' enctype="multipart/form-data" id='formId'></form>
			<div class='row'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L1116', 'utf8', 0, 0, 1));?></label>
					<input type="number" name="priceUpperRange" min="<?php echo $min;?>" max="<?php echo $max;?>" value='<?php echo $priceUpperRange;?>' <?php if($lastPriceId!=$priceId) echo 'readOnly';?> class='w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L112', 'utf8', 0, 0, 1));?> : </label>&emsp;
					<label><input class='w3-radio' type="radio" name="currency" min="1" value='2' <?php if($currency == '2'){echo "checked";}?> required form='formId'>&nbsp;<?php echo displayText('L787');?> (Php)</label>&emsp;
					<label><input class='w3-radio' type="radio" name="currency" min="1" value='1' <?php if($currency == '1'){echo "checked";}?> required form='formId'>&nbsp;<?php echo displayText('L786');?> ($)</label>&emsp;
					<label><input class='w3-radio' type="radio" name="currency" min="1" value='3' <?php if($currency == '3'){echo "checked";}?> required form='formId'>&nbsp;<?php echo displayText('L788');?> (¥)</label>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L667', 'utf8', 0, 0, 1));?></label>
					<input type="number" name="accessoryPrice" min="0" step="any" value='<?php echo $accessoryPrice;?>' class='priceClass w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L668', 'utf8', 0, 0, 1));?></label>
					<input type="number" name="materialPrice" min="0" step="any" value='<?php echo $materialPrice;?>' class='priceClass w3-input w3-border w3-small w3-pale-yellow' required form='formId'>

				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L669', 'utf8', 0, 0, 1));?></label>
					<input type="number" name="subconPrice" min="0" step="any" value='<?php echo $subconPrice;?>' class='priceClass w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L670', 'utf8', 0, 0, 1));?></label>
					<input type="number" name="productionPrice" min="0" step="any" value='<?php echo $productionPrice;?>' class='priceClass w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L1498', 'utf8', 0, 0, 1));?></label>
					<input type="number" name="deliveryPrice" min="0" step="any" value='<?php echo $deliveryPrice;?>' class='priceClass w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L267', 'utf8', 0, 0, 1));?></label>
					<input type="number" name="price" min="0.0001" step="any" value='<?php echo $price;?>' class='w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L1504', 'utf8', 0, 0, 1));?></label>
					<input type="date" name="quotationDate" value = '<?php echo $quotationDate;?>' class='priceClass w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L4480', 'utf8', 0, 0, 1));?></label>
					<input type="file" name="quotationPdf" class='w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12 w3-center'>
					<button type='submit' id='button1' class='w3-btn w3-medium w3-indigo w3-round' form='formId'><i class='fa fa-send-o'></i>&emsp;<b><?php echo (displayText('L1052', 'utf8', 0, 0, 1)); # SAVE?></b></button>
				</div>
			</div>
			<script>
			function roundToFour(num) {    
				return +(Math.round(num + "e+4")  + "e-4");
			}
			$(document).ready(function(){
				$("input.priceClass").change(function(){
					$("input[name=price]").val(0);
				});
				
				<?php
					if($customerId==1 AND $_GET['country']==1)//2021-10-04 mam jane
					{
						?>
						$("input[name=currency]").change(function(){
							var currency = $(this).val();
							if(currency!=3)
							{
								alert('Yen is only allowed for this customer');
								$("input[name=currency]").focus().prop('checked',false);
							}
						});
						<?php
					}
				?>
				
				
				$("input[name=price]").change(function(){
					var price = parseFloat($(this).val()),
						accessoryPrice = parseFloat($("input[name=accessoryPrice]").val()),
						materialPrice = parseFloat($("input[name=materialPrice]").val()),
						subconPrice = parseFloat($("input[name=subconPrice]").val()),
						productionPrice = parseFloat($("input[name=productionPrice]").val()),
						deliveryPrice = parseFloat($("input[name=deliveryPrice]").val()),
						//~ totalPrice = accessoryPrice + materialPrice + subconPrice + productionPrice + deliveryPrice;
						totalPrice = roundToFour(accessoryPrice) + roundToFour(materialPrice) + roundToFour(subconPrice) + roundToFour(productionPrice) + roundToFour(deliveryPrice);
						
					//~ var price = $(this).val(),
						//~ accessoryPrice = $("input[name=accessoryPrice]").val(),
						//~ materialPrice = $("input[name=materialPrice]").val(),
						//~ subconPrice = $("input[name=subconPrice]").val(),
						//~ productionPrice = $("input[name=productionPrice]").val(),
						//~ deliveryPrice = $("input[name=deliveryPrice]").val();
					
					/*
					$.ajax({
						url:"<?php echo $_SERVER['PHP_SELF'];?>",
						type:'post',
						data:{
							ajaxType:'checkPrice',
							price:price,
							accessoryPrice:accessoryPrice,
							materialPrice:materialPrice,
							subconPrice:subconPrice,
							productionPrice:productionPrice,
							deliveryPrice:deliveryPrice
						},
						success:function(data){
							if(data.trim()!='')
							{
								console.log(data.trim());
								alert(data.trim());
								$("input[name=price]").val(0).focus();
							}
						}
					});*/
					
					<?php
						if($customerId!=1)//2021-09-04 exclude arkjapan by jessica co sir ace 
						{
							?>
							if(totalPrice <= 0)
							{
								alert('Total amount of sub price above must be greater than zero');
								$("input[name=price]").val(0).focus();
							}
							else if(roundToFour(price) != roundToFour(totalPrice))
							{
								console.log(roundToFour(price));
								console.log(roundToFour(totalPrice));
								console.log(accessoryPrice+" "+materialPrice+" "+subconPrice+" "+productionPrice+" "+deliveryPrice);
								alert('Price mismatch vs total sub price!');
								$("input[name=price]").val(0).focus();
							}
							<?php
						}
						else
						{
							?>
							$("input[name='quotationPdf']").attr('required',false);
							<?php
						}
					?>
					
					//~ if(price < totalPrice)
					//~ {
						//~ alert('Price should be greater than or equal to the total price above!');
						//~ $("input[name=price]").val(0).focus();
					//~ }
				});
			});
			</script>
			<?php
			exit(0);
		}
		else if($_GET['modalBoxType']=='2')
		{
			$partId = $_POST['partId'];
			?>
			<div class='w3-right'>
				<button class='w3-btn w3-indigo w3-round w3-medium' onclick= "window.open('jansen_salesHistory.php?partId=<?php echo $partId;?>','checkDel','left=50,screenX=20,screenY=60,resizable,scrollbars,status,width=850,height=500'); return false;"><i class='fa fa-history'></i>&emsp;<b>VIEW HISTORY</b></button>
			</div>
			<br><br><br>
			<?php
			echo "
				<table class='table table-bordered table-condensed table-striped'>
					<thead class='w3-indigo' style='text-transform: uppercase;'>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L1117')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L1118')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L1116')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L112')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L667')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L668')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L669')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L670')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L1498')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L267')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L292')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L1504')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L497')."</th>
						<th class='w3-center' style='vertical-align:middle;' colspan='4'>".displayText('L1120')."</th>
					</thead>
			";
			$count = 0;
			$sql = "SELECT `priceId`, SUBSTRING_INDEX(priceBracket,'-',1) as `priceLowerRange`, SUBSTRING_INDEX(priceBracket,'-',-1) as `priceUpperRange`, `currency`, `accessoryPrice`, `materialPrice`, `subconPrice`, `productionPrice`,`deliveryPrice`, `price`, `priceDate`, `quotationDate`, `updateInCharge`  FROM `sales_pricelist` WHERE `arkPartId` = ".$partId."";
			$queryPrice = $db->query($sql);
			if($queryPrice AND $queryPrice->num_rows > 0)
			{
				while($resultPrice = $queryPrice->fetch_assoc())
				{
					$count++;
					$priceId = $resultPrice['priceId'];
					$priceLowerRange = $resultPrice['priceLowerRange'];
					$priceUpperRange = $resultPrice['priceUpperRange'];
					$currency = $resultPrice['currency'];
					$accessoryPrice = $resultPrice['accessoryPrice'];
					$materialPrice = $resultPrice['materialPrice'];
					$subconPrice = $resultPrice['subconPrice'];
					$productionPrice = $resultPrice['productionPrice'];					
					$deliveryPrice = $resultPrice['deliveryPrice'];					
					$price = $resultPrice['price'];
					$priceDate = $resultPrice['priceDate'];
					$quotationDate = $resultPrice['quotationDate'];
					$updateInCharge = $resultPrice['updateInCharge'];
					
					$deleteButton = "<td class='w3-center' style='vertical-align:middle;'></td>";
					if($queryPrice->num_rows == $count)
					{
						$deleteButton = "<td class='w3-center' style='vertical-align:middle;'><i class='fa fa-remove w3-large w3-text-pink' onclick=\"location.href='gerald_salesPriceListSql.php?sqlType=deletePriceSql&priceId=".$priceId."&partId=".$partId."'\" style='cursor:pointer;' alt='delete' title='Delete'></i></td>";
					}					
					
					if($currency==2)		$sign = 'Peso';
					else if($currency==1)	$sign = 'Dollar';
					else if($currency==3)	$sign = 'Yen';
					
					$pdfIconColor = "w3-text-gray";
					$onClick = "";
					$quotationPdf = __DIR__ ."/Quotation Files/".$priceId.".pdf";
					if(file_exists($quotationPdf))
					{
						$onClick = "window.open ('Quotation Files/".$priceId.".pdf', 'newwindow', config='height=600,width=800, toolbar=no, menubar=no, scrollbars=no, resizable=no,location=no, directories=no, status=no');";
						$pdfIconColor = "w3-text-red";
					}
					
					echo "
						<tr>
							<td class='w3-center' style='vertical-align:middle;'>".$priceId."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$priceLowerRange."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$priceUpperRange."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$sign."</td>
							<td class='w3-center' style='vertical-align:middle;' align='right'>".$accessoryPrice."</td>
							<td class='w3-center' style='vertical-align:middle;' align='right'>".$materialPrice."</td>
							<td class='w3-center' style='vertical-align:middle;' align='right'>".$subconPrice."</td>
							<td class='w3-center' style='vertical-align:middle;' align='right'>".$productionPrice."</td>
							<td class='w3-center' style='vertical-align:middle;' align='right'>".$deliveryPrice."</td>
							<td class='w3-center' style='vertical-align:middle;' align='right'>".$price."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$priceDate."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$quotationDate."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$updateInCharge."</td>
							<td class='w3-center' style='vertical-align:middle;'><i class='fa fa-file-pdf-o w3-large ".$pdfIconColor."' onclick=\" ".$onClick." \" data-price-id='".$priceId."' style='cursor:pointer;' alt='View' title='View PDF'></i></td>
							<td class='w3-center' style='vertical-align:middle;'><i class='fa fa-edit w3-large w3-text-green' onclick=\"modalFunctionEdit(".$priceId.");\" data-price-id='".$priceId."' style='cursor:pointer;' alt='Edit' title='Edit'></i></td>
							".$deleteButton."
						</tr>
					";
				}
			}
			echo "</table>";

			if($priceLowerRange!=0 AND $priceUpperRange!=0)
			{
				$editPriceRangeButton = "";
				//~ if($_SESSION['idNumber']=='0346')
				//~ {
					$editPriceRangeButton = "<button class='w3-btn w3-medium w3-indigo w3-round' type='button' onclick=\"modalFunctionUpdate(".$partId.");\"><i class='fa fa-edit'></i>&emsp;<b>EDIT PRICE RANGE</b></button>";
				//~ }       
				echo "<div class='w3-padding-top w3-center'>";
					echo "<button class='w3-btn w3-medium w3-indigo w3-round' onclick=\"modalFunctionEdit('', ".$partId.");\" type='button' data-part-id='".$partId."'><i class='fa fa-plus'></i>&emsp;<b>ADD PRICE</b></button>&nbsp;".$editPriceRangeButton."";
				echo "</div>";
			}
		}
		else if($_GET['modalBoxType']=='3')
		{
			?>
			<form action='gerald_salesPriceListSql.php?sqlType=addPartsSql' method='post' id='formId'></form>
			<div class='row'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L24', 'utf8', 0, 0, 1));?></label>
					<select name='customerId' class='w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
						<option value=''>Select Customer</option>
						<?php
							$sql = "SELECT customerId, customerName FROM sales_customer WHERE status = 1 ORDER BY customerName";
							$queryCustomer = $db->query($sql);
							if($queryCustomer AND $queryCustomer->num_rows > 0)
							{
								while($resultCustomer = $queryCustomer->fetch_assoc())
								{
									$customerId = $resultCustomer['customerId'];
									$customerName = $resultCustomer['customerName'];
									
									echo "<option value='".$customerId."'>".$customerName."</option>";
								}
							}
						?>
					</select>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L28', 'utf8', 0, 0, 1));?></label>
					<input type="text" name="partNumber" class='w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L1934', 'utf8', 0, 0, 1));?></label>
					<input type="text" name="revisionId" class='w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L30', 'utf8', 0, 0, 1));?></label>
					<input type="text" name="partName" class='w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12'>
					<label><?php echo (displayText('L1303', 'utf8', 0, 0, 1));?></label>
					<input type="text" name="itemGroup" class='w3-input w3-border w3-small w3-pale-yellow' required form='formId'>
				</div>
			</div>
			<div class='row w3-padding-top'>
				<div class='col-md-12 w3-center'>
					<button type='submit' id='button1' class='w3-btn w3-medium w3-indigo w3-round' form='formId'><i class='fa fa-send-o'></i>&emsp;<b><?php echo (displayText('L1052', 'utf8', 0, 0, 1)); # SAVE?></b></button>
				</div>
			</div>
			<?php
			exit(0);			
		}
		else if($_GET['modalBoxType']=='4')
		{
			$partId = $_POST['partId'];
			?>
			<div class='w3-right'>
				<button class='w3-btn w3-indigo w3-round w3-medium' onclick= "window.open('jansen_salesHistory.php?partId=<?php echo $partId;?>','checkDel','left=50,screenX=20,screenY=60,resizable,scrollbars,status,width=850,height=500'); return false;"><i class='fa fa-history'></i>&emsp;<b><?php echo displayText('L4040', 'utf8', 0, 0, 1);?></b></button>
			</div>
			<br><br><br>
			<?php
			echo "<input type='hidden' class='api-form' name='partId' value='".$partId."' required form='priceFormId'>";
			echo "
				<form action='gerald_salesPriceListSql.php?sqlType=multiplePriceSql' method='post' id='priceFormId'></form>
				<table class='table table-bordered table-condensed table-striped'>
					<thead class='w3-indigo' style='text-transform: uppercase;'>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L1117')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L1118')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L1116')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L112')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L667')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L668')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L669')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L670')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L1498')."</th>
						<th class='w3-center' style='vertical-align:middle;'>".displayText('L267')."</th>
					</thead>
			";
			
			$count = $counter = $errorFlag = $rangeNumber = 0;
			$sql = "SELECT `priceId`, SUBSTRING_INDEX(priceBracket,'-',1) as `priceLowerRange`, SUBSTRING_INDEX(priceBracket,'-',-1) as `priceUpperRange`, `currency`, `accessoryPrice`, `materialPrice`, `subconPrice`, `productionPrice`,`deliveryPrice`, `price`, `priceDate`, `quotationDate`, `updateInCharge`  FROM `sales_pricelist` WHERE `arkPartId` = ".$partId."";
			$queryPrice = $db->query($sql);
			if($queryPrice AND $queryPrice->num_rows > 0)
			{
				while($resultPrice = $queryPrice->fetch_assoc())
				{
					$count++;
					$priceId = $resultPrice['priceId'];
					$priceLowerRange = $resultPrice['priceLowerRange'];
					$priceUpperRange = $resultPrice['priceUpperRange'];
					$currency = $resultPrice['currency'];
					$accessoryPrice = $resultPrice['accessoryPrice'];
					$materialPrice = $resultPrice['materialPrice'];
					$subconPrice = $resultPrice['subconPrice'];
					$productionPrice = $resultPrice['productionPrice'];					
					$deliveryPrice = $resultPrice['deliveryPrice'];					
					$price = $resultPrice['price'];
					$priceDate = $resultPrice['priceDate'];
					$quotationDate = $resultPrice['quotationDate'];
					$updateInCharge = $resultPrice['updateInCharge'];
					
					if($currency==2)		$sign = 'Peso';
					else if($currency==1)	$sign = 'Dollar';
					else if($currency==3)	$sign = 'Yen';
					
					$colorLowerRange = '';
					if($priceLowerRange >= $rangeNumber)
					{
						$rangeNumber = $priceLowerRange;
					}
					else
					{
						$errorFlag = 1;
						$colorLowerRange = 'background-color:red;';
					}
					
					$colorUpperRange = '';
					if($priceUpperRange >= $rangeNumber)
					{
						$rangeNumber = $priceUpperRange;
					}
					else
					{
						$errorFlag = 1;
						$colorUpperRange = 'background-color:red;';
					}
					
					$priceLowerRangeInput = "<input type='number' name='priceLowerRangeArray[]' value='".$priceLowerRange."' class='w3-input w3-border w3-small w3-pale-yellow editPriceRange rangeClass' data-range-type='lower' data-price-id='".$priceId."' data-counter='".$counter++."' style='text-align:right;".$colorLowerRange."' form='priceFormId'>";
					$priceUpperRangeInput = "<input type='number' name='priceUpperRangeArray[]' value='".$priceUpperRange."' class='w3-input w3-border w3-small w3-pale-yellow editPriceRange rangeClass' data-range-type='upper' data-price-id='".$priceId."' data-counter='".$counter++."' style='text-align:right;".$colorUpperRange."' form='priceFormId'>";
					
					$currencySelect = "
					<select class='w3-input w3-border w3-small w3-pale-yellow' name='currencyArray[]' form='priceFormId'>
						<option value='2'>".displayText('L787')."</option>
						<option value='1'>".displayText('L786')."</option>
						<option value='3'>".displayText('L788')."</option>
					</select>
					";
					
					$accessoryPriceInput = "<input type='number' name='accessoryPriceArray[]' value='".$accessoryPrice."' step='any' class='w3-input w3-border w3-small w3-pale-yellow editPriceRange' style='text-align:right;' form='priceFormId'>";
					$materialPriceInput = "<input type='number' name='materialPriceArray[]' value='".$materialPrice."' step='any' class='w3-input w3-border w3-small w3-pale-yellow editPriceRange' style='text-align:right;' form='priceFormId'>";
					$subconPriceInput = "<input type='number' name='subconPriceArray[]' value='".$subconPrice."' step='any' class='w3-input w3-border w3-small w3-pale-yellow editPriceRange' style='text-align:right;' form='priceFormId'>";
					$productionPriceInput = "<input type='number' name='productionPriceArray[]' value='".$productionPrice."' step='any' class='w3-input w3-border w3-small w3-pale-yellow editPriceRange' style='text-align:right;' form='priceFormId'>";
					$deliveryPriceInput = "<input type='number' name='deliveryPriceArray[]' value='".$deliveryPrice."' step='any' class='w3-input w3-border w3-small w3-pale-yellow editPriceRange' style='text-align:right;' form='priceFormId'>";
					$priceInput = "<input type='number' name='priceArray[]' value='".$price."' step='any' class='w3-input w3-border w3-small w3-pale-yellow editPriceRange' style='text-align:right;' form='priceFormId'>";
					
					echo "
						<tr>
							<td class='w3-center' style='vertical-align:middle;'><input type='hidden' name='priceIdArray[]' value='".$priceId."' form='priceFormId'><b>".$priceId."</b></td>
							<td class='w3-center' style='vertical-align:middle;'>".$priceLowerRangeInput."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$priceUpperRangeInput."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$currencySelect."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$accessoryPriceInput."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$materialPriceInput."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$subconPriceInput."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$productionPriceInput."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$deliveryPriceInput."</td>
							<td class='w3-center' style='vertical-align:middle;'>".$priceInput."</td>
						</tr>
					";
				}
			}
			
			$hidden = ($errorFlag==1) ? 'display:none;' : '';
			echo "</table>";

			echo "
				<div class='w3-padding-top w3-center'>
					<button class='w3-btn w3-medium w3-indigo w3-round' type='submit' value='Update' form='priceFormId' id='buttonUpdate' style='".$hidden."'><i class='fa fa-check'></i>&emsp;<b>UPDATE</b></button>
				</div>
			";
		}    
		?>
		<script>
		$(document).ready(function(){
			$("input.rangeClass").change(function(){
				var range = $(this).val();
				var rangeType = $(this).data('rangeType');
				var priceId = $(this).data('priceId');
				var counter = $(this).data('counter');
				
				var number = errorFlag = 0;
				$("input.rangeClass").each(function(i){
					var currentRangeType = $(this).data('rangeType');
					var currentPriceId = $(this).data('priceId');
					
					//~ if(priceId==currentPriceId)
					//~ {
						//~ if(rangeType)
					//~ }
					
					if(rangeType=='lower' && (parseFloat(i)+1) == parseFloat(counter))
					{
						$(this).val(--range);
					}
					
					if(rangeType=='upper' && (parseFloat(i)-1) == parseFloat(counter))
					{
						$(this).val(++range);
					}
					
					if(parseFloat($(this).val()) >= parseFloat(number))
					{
						number = $(this).val();
						$(this).removeClass('w3-pink');
						$(this).addClass('w3-pale-yellow');
					}
					else
					{
						//~ alert($(this).val());
						$(this).addClass('w3-pink');
						$(this).removeClass('w3-pale-yellow');
						errorFlag = 1;
					}
				});
				
				if(errorFlag==1)
				{
					$('#buttonUpdate').hide();
				}
				else
				{
					$('#buttonUpdate').show();
				}
			});

			$(".editPriceRange").keypress(function(e){
				if(e.which==13)
				{
					return false;
				}
			}); 
		});
		</script>
		<?php
	}
?>
