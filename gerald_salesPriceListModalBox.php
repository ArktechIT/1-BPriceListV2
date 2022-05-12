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
	
	//~ if($_SESSION['idNumber']=='0346')	$_GET['country'] = 2;
	
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
			if($priceUpperRange=='')	$priceUpperRange = 0;
			
			echo "<input type='hidden' class='api-form' name='partId' value='".$partId."' required form='formId'>";
			?>
			<div class="api-top api-title">
				<h2><?php echo displayText('L1115');?></h2>
			</div>
			<div>
				<form action='gerald_salesPriceListSql.php?sqlType=priceSql' method='post' id='formId'></form>
				<table border='1' style='width:100%;' class='api-table'>
					<tbody>
						<?php
							//~ if($_GET['country']==1)
							//~ {
								?>
								<tr>	
									<td><?php echo displayText('L1116');?></td>
									<td><input type="number" name="priceUpperRange" min="<?php echo $min;?>" max="<?php echo $max;?>" value='<?php echo $priceUpperRange;?>' <?php if($lastPriceId!=$priceId) echo 'readOnly';?> class='api-form' required form='formId'></td>
								</tr>
								<?php
							/*}
							else
							{
								?>
								<tr>	
									<td><?php echo displayText('L1118');?></td>
									<td><input type="number" name="priceLowerRange" min="<?php echo $min;?>" max="<?php echo $max;?>" value='<?php echo $priceLowerRange;?>' class='api-form' required form='formId'></td>
								</tr>
								<tr>	
									<td><?php echo displayText('L1116');?></td>
									<td><input type="number" name="priceUpperRange" min="<?php echo $min;?>" max="<?php echo $max;?>" value='<?php echo $priceUpperRange;?>' class='api-form' required form='formId'></td>
								</tr>
								<?php
							}*/
						?>
						<tr>
							<td><?php echo displayText('L112');?></td>
							<td>
								<label><input type="radio" name="currency" min="1" value='2' <?php if($currency == '2'){echo "checked";}?> required form='formId'><?php echo displayText('L787');?> (Php)</label>
								<label><input type="radio" name="currency" min="1" value='1' <?php if($currency == '1'){echo "checked";}?> required form='formId'><?php echo displayText('L786');?> ($)</label>
								<label><input type="radio" name="currency" min="1" value='3' <?php if($currency == '3'){echo "checked";}?> required form='formId'><?php echo displayText('L788');?> (¥)</label>
							</td>
						</tr>
						<tr>
							<td><?php echo displayText('L667');?></td>
							<td><input type="number" name="accessoryPrice" min="0" step="any" value='<?php echo $accessoryPrice;?>' class='priceClass api-form' required form='formId'></td>
						</tr>
						<tr>
							<td><?php echo displayText('L668');?></td>
							<td><input type="number" name="materialPrice" min="0" step="any" value='<?php echo $materialPrice;?>' class='priceClass api-form' required form='formId'></td>
						</tr>
						<tr>
							<td><?php echo displayText('L669');?></td>
							<td><input type="number" name="subconPrice" min="0" step="any" value='<?php echo $subconPrice;?>' class='priceClass api-form' required form='formId'></td>
						</tr>
						<tr>
							<td><?php echo displayText('L670');?></td>
							<td><input type="number" name="productionPrice" min="0" step="any" value='<?php echo $productionPrice;?>' class='priceClass api-form' required form='formId'></td>
						</tr>
						<tr>
							<td><?php echo displayText('L1498');//Delivery Price?></td>
							<td><input type="number" name="deliveryPrice" min="0" step="any" value='<?php echo $deliveryPrice;?>' class='priceClass api-form' required form='formId'></td>
						</tr>
						<tr>
							<td><?php echo displayText('L267');?></td>
							<td><input type="number" name="price" min="0.0001" step="any" value='<?php echo $price;?>' class='api-form' required form='formId'></td>
						</tr>
						<tr>
							<td><?php echo displayText('L1504');//Quotation Date?></td>
							<td><input type="date" name="quotationDate" value = '<?php echo $quotationDate;?>' class='priceClass api-form' required form='formId'></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan='4' align='center'>
								<button type='submit' id='button1' class='api-btn' style='font-size:1.2em;' data-api-title='<?php echo displayText('L1052');//SAVE?>' form='formId'></button>
							</th>
						</tr>
					</tfoot>	
				</table>
			</div>
			<?php
			exit(0);
		}
		else if($_GET['modalBoxType']=='2')
		{
			$partId = $_POST['partId'];
			?>
	<a style = "float:right;" href="#" onclick= "window.open('jansen_salesHistory.php?partId=<?php echo $partId;?>','checkDel','left=50,screenX=20,screenY=60,resizable,scrollbars,status,width=850,height=500'); return false;"> <font style = 'color: blue; font-size: 30px;'><?php echo displayText('L4040');?></font></a><!-- View History -->
			<?php
			echo "
				<table border='1'>
					<tr>
						<th>".displayText('L1117')."</th>
						<th>".displayText('L1118')."</th>
						<th>".displayText('L1116')."</th>
						<th>".displayText('L112')."</th>
						<th>".displayText('L667')."</th>
						<th>".displayText('L668')."</th>
						<th>".displayText('L669')."</th>
						<th>".displayText('L670')."</th>
						<th>".displayText('L1498')."</th>
						<th>".displayText('L267')."</th>
						<th>".displayText('L292')."</th>
						<th>".displayText('L1504')."</th>
						<th>".displayText('L497')."</th>
						<th colspan='3'>".displayText('L1120')."</th>
					</tr>
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
					
					$deleteButton = "";
					if($queryPrice->num_rows == $count)
					{
						$deleteButton = "<td><a href='gerald_salesPriceListSql.php?sqlType=deletePriceSql&priceId=".$priceId."&partId=".$partId."'><img class='deletePrice' style='cursor:pointer;' src='../Common Data/Templates/images/close1.png' align='right' alt='delete' title='Delete' width='20' height='20'/></a></td>";
					}					
					
					if($currency==2)		$sign = 'Peso';
					else if($currency==1)	$sign = 'Dollar';
					else if($currency==3)	$sign = 'Yen';
					
					echo "
						<tr>
							<td>".$priceId."</td>
							<td>".$priceLowerRange."</td>
							<td>".$priceUpperRange."</td>
							<td>".$sign."</td>
							<td align='right'>".$accessoryPrice."</td>
							<td align='right'>".$materialPrice."</td>
							<td align='right'>".$subconPrice."</td>
							<td align='right'>".$productionPrice."</td>
							<td align='right'>".$deliveryPrice."</td>
							<td align='right'>".$price."</td>
							<td>".$priceDate."</td>
							<td>".$quotationDate."</td>
							<td>".$updateInCharge."</td>
							<td><img class='editPrice' data-price-id='".$priceId."' style='cursor:pointer;' src='../Common Data/Templates/images/edit1.png' alt='view' width='20' height='20'/></td>
							".$deleteButton."
						</tr>
					";
				}
			}
			if($priceLowerRange!=0 AND $priceUpperRange!=0)
			{
				$editPriceRangeButton = "";
				//~ if($_SESSION['idNumber']=='0346')
				//~ {
					$editPriceRangeButton = "<input type='button' onclick = \"openModalBox('gerald_salesPriceListModalBox.php?modalBoxType=4',jsFunctions,'','partId=".$partId."')\" value='Edit Price Range'>";
				//~ }
				
				echo "
					<tr>
						<th colspan='13'>
							<input id='addPrice' type='button' data-part-id='".$partId."' value='Add Price'>".$editPriceRangeButton."</th>
					</tr>
				";
			}
			echo "</table>";
		}
		else if($_GET['modalBoxType']=='3')
		{
			?>
			<!--div class="api-top api-title">
				<h2><?php //echo displayText('L1115');?></h2>
			</div-->
			<div>
				<form action='gerald_salesPriceListSql.php?sqlType=addPartsSql' method='post' id='formId'></form>
				<table border='1' style='width:100%;' class='api-table'>
					<tbody>
						<tr>	
							<td><?php echo displayText('L24');?></td>
							<td>
								<select name='customerId' class='api-form' required form='formId'>
									<option value=''><?php echo displayText('L1381');?></option>
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
							</td>
						</tr>
						<tr>
							<td><?php echo displayText('L28');?></td>
							<td><input type="input" name="partNumber" class='api-form' required form='formId'></td>
						</tr>
						<tr>
							<td><?php echo displayText('L1934');?></td>
							<td><input type="input" name="revisionId" class='api-form' required form='formId'></td>
						</tr>
						<tr>
							<td><?php echo displayText('L30');?></td>
							<td><input type="input" name="partName" class='api-form' required form='formId'></td>
						</tr>
						<tr>
							<td><?php echo displayText('L1303');//Item Group?></td>
							<td><input type="input" name="itemGroup" class='api-form' required form='formId'></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<th colspan='4' align='center'>
								<button type='submit' id='button1' class='api-btn' style='font-size:1.2em;' data-api-title='<?php echo displayText('L1052');//SAVE?>' form='formId'></button>
							</th>
						</tr>
					</tfoot>	
				</table>
			</div>
			<?php
			exit(0);			
		}
		else if($_GET['modalBoxType']=='4')
		{
			$partId = $_POST['partId'];
			?>
	<a style = "float:right;" href="#" onclick= "window.open('jansen_salesHistory.php?partId=<?php echo $partId;?>','checkDel','left=50,screenX=20,screenY=60,resizable,scrollbars,status,width=850,height=500'); return false;"> <font style = 'color: blue; font-size: 30px;'><?php echo displayText('L4040');?></font></a><!-- View History -->
			<?php
			echo "
				<form action='gerald_salesPriceListSql.php?sqlType=multiplePriceSql' method='post' id='formId'></form>
				<table border='1'>
					<tr>
						<th>".displayText('L1117')."</th>
						<th>".displayText('L1118')."</th>
						<th>".displayText('L1116')."</th>
						<th>".displayText('L112')."</th>
						<th>".displayText('L667')."</th>
						<th>".displayText('L668')."</th>
						<th>".displayText('L669')."</th>
						<th>".displayText('L670')."</th>
						<th>".displayText('L1498')."</th>
						<th>".displayText('L267')."</th>
					</tr>
			";
			echo "<input type='hidden' class='api-form' name='partId' value='".$partId."' required form='formId'>";
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
					
					echo "<input type='hidden' name='priceIdArray[]' value='".$priceId."' form='formId'>";
					
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
					
					$priceLowerRangeInput = "<input type='number' name='priceLowerRangeArray[]' value='".$priceLowerRange."' class='api-form editPriceRange rangeClass' data-range-type='lower' data-price-id='".$priceId."' data-counter='".$counter++."' style='width:50px;text-align:right;".$colorLowerRange."' form='formId'>";
					$priceUpperRangeInput = "<input type='number' name='priceUpperRangeArray[]' value='".$priceUpperRange."' class='api-form editPriceRange rangeClass' data-range-type='upper' data-price-id='".$priceId."' data-counter='".$counter++."' style='width:50px;text-align:right;".$colorUpperRange."' form='formId'>";
					
					$currencySelect = "
					<select class='api-form' name='currencyArray[]' form='formId'>
						<option value='2'>".displayText('L787')."</option>
						<option value='1'>".displayText('L786')."</option>
						<option value='3'>".displayText('L788')."</option>
					</select>
					";
					
					$accessoryPriceInput = "<input type='number' name='accessoryPriceArray[]' value='".$accessoryPrice."' step='any' class='api-form editPriceRange' style='width:100px;text-align:right;' form='formId'>";
					$materialPriceInput = "<input type='number' name='materialPriceArray[]' value='".$materialPrice."' step='any' class='api-form editPriceRange' style='width:100px;text-align:right;' form='formId'>";
					$subconPriceInput = "<input type='number' name='subconPriceArray[]' value='".$subconPrice."' step='any' class='api-form editPriceRange' style='width:100px;text-align:right;' form='formId'>";
					$productionPriceInput = "<input type='number' name='productionPriceArray[]' value='".$productionPrice."' step='any' class='api-form editPriceRange' style='width:100px;text-align:right;' form='formId'>";
					$deliveryPriceInput = "<input type='number' name='deliveryPriceArray[]' value='".$deliveryPrice."' step='any' class='api-form editPriceRange' style='width:100px;text-align:right;' form='formId'>";
					$priceInput = "<input type='number' name='priceArray[]' value='".$price."' step='any' class='api-form editPriceRange' style='width:100px;text-align:right;' form='formId'>";
					
					echo "
						<tr>
							<td>".$priceId."</td>
							<td>".$priceLowerRangeInput."</td>
							<td>".$priceUpperRangeInput."</td>
							<td>".$currencySelect."</td>
							<td align='right'>".$accessoryPriceInput."</td>
							<td align='right'>".$materialPriceInput."</td>
							<td align='right'>".$subconPriceInput."</td>
							<td align='right'>".$productionPriceInput."</td>
							<td align='right'>".$deliveryPriceInput."</td>
							<td align='right'>".$priceInput."</td>
						</tr>
					";
				}
			}
			
			$hidden = ($errorFlag==1) ? 'display:none;' : '';
			
			echo "
				<tr>
					<th colspan='13'><input type='submit' value='Update' form='formId' id='buttonUpdate' style='".$hidden."'></th>
				</tr>
			";
			echo "</table>";
		}		
	}
?>
