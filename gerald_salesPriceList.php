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

	function createFilterInput($sqlFilter,$column,$value)
	{
		include('PHP Modules/mysqliConnection.php');
		
		$return = "<option value=''>".displayText('L490')." </option>";
		
		$sql = "SELECT DISTINCT ".$column." FROM cadcam_parts ".$sqlFilter." ORDER BY ".$column."";
		if($column=='customerId')
		{
			$materialTypeIdArray = array();
			$sql = "SELECT DISTINCT customerId FROM cadcam_parts ".$sqlFilter."";
			$query = $db->query($sql);
			if($query->num_rows > 0)
			{
				while($result = $query->fetch_array())
				{
					$customerIdArray[] = $result['customerId'];
				}
			}
			
			if(count($customerIdArray) > 0)
			{
				$sql = "SELECT customerId, customerName FROM sales_customer WHERE customerId IN(".implode(",",$customerIdArray).") ORDER BY customerName";
			}
		}
		else if($column=='currency')
		{
			$partIdArray = array();
			$sql = "SELECT partId FROM cadcam_parts ".$sqlFilter."";
			$query = $db->query($sql);
			if($query->num_rows > 0)
			{
				while($result = $query->fetch_array())
				{
					$partIdArray[] = $result['partId'];
				}
			}
			
			if(count($partIdArray) > 0)
			{
				$sql = "SELECT DISTINCT currency FROM sales_pricelist WHERE arkPartId IN(".implode(",",$partIdArray).") AND currency !='' ORDER BY currency";
			}
		}
		//~ echo $sql;
		$query = $db->query($sql);
		if($query->num_rows > 0)
		{
			while($result = $query->fetch_array())
			{
				$valueColumn = $valueCaption = $result[$column];
				
				$selected = ($value==$result[$column]) ? 'selected' : '';
				
				if($column=='customerId')	$valueCaption = $result['customerName'];
				else if($column=='currency')
				{
					if($valueColumn==1)		$valueCaption = 'Dollar';
					else if($valueColumn==2)	$valueCaption = 'Peso';
					else if($valueColumn==3)	$valueCaption = 'Yen';
				}					
				
				$return .= "<option value='".$valueColumn."' ".$selected.">".$valueCaption."</option>";
			}
		}
		return $return;
	}
	
	$partId = (isset($_GET['partId'])) ? $_GET['partId'] : '';
	$customerId = (isset($_POST['customerId'])) ? $_POST['customerId'] : '';
	$customerPartNumber = (isset($_POST['customerPartNumber'])) ? $_POST['customerPartNumber'] : '';
	$partNumber = (isset($_POST['partNumber'])) ? $_POST['partNumber'] : '';
	$partName = (isset($_POST['partName'])) ? $_POST['partName'] : '';
	$currency = (isset($_POST['currency'])) ? $_POST['currency'] : '';
	
	$sqlFilter = "";
	$sqlFilterArray = $sqlFilterMaterialSpecsArray = array();
	
	if($partId!='')				$sqlFilterArray[] = "partId = ".$partId." ";
	if($customerId!='')			$sqlFilterArray[] = "customerId = ".$customerId." ";
	if($customerPartNumber!='')	$sqlFilterArray[] = "customerPartNumber LIKE '%".$customerPartNumber."%' ";
	if($partNumber!='')			$sqlFilterArray[] = "partNumber LIKE '%".$partNumber."%' ";
	if($partName!='')			$sqlFilterArray[] = "partName LIKE '%".$partName."%' ";
	if($currency!='')			$sqlFilterArray[] = "partId IN(SELECT arkPartId FROM sales_pricelist WHERE currency = ".$currency.")";
	
	$sqlFilter = "WHERE status IN(0,2)";
	if(count($sqlFilterArray) > 0)
	{
		$sqlFilter .= " AND ".implode(" AND ",$sqlFilterArray)." ";
	}
	
	//~ $sqlFilter .= " AND partId IN (SELECT arkPartId FROM sales_parts)";
	
	$sql = "SELECT partId FROM cadcam_parts ".$sqlFilter;
	$queryParts = $db->query($sql);
	$totalRecords = ($queryParts AND $queryParts->num_rows > 0) ? $queryParts->num_rows : 0;
	
	//~ echo createFilterInput($sqlFilter,'materialType',$materialType)	
?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo displayText('L1497');//Sales Price List?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="../Common Data/Templates/api.css">
	<script src="../Common Data/Templates/api.js"></script>
</head>
<body>
	<form action='gerald_salesPriceListAjax.php' method='post' id='exportFormId'></form>
	<input type='hidden' name='sqlFilter' value="<?php echo $sqlFilter;?>" form='exportFormId'>	
	<div class="api-row">
		<div class="api-top api-col api-left-buttons" style='width:30%'>
			<button class='api-btn api-btn-home' onclick="location.href='../dashboard.php';" data-api-title='<?php echo displayText('L434');?>'></button>
			<?php echo helpMenu(1); ?>
			<button class='api-btn api-btn-view' onclick= "window.open('../1-B Price List/paul_partsLog.php','BBC','left=50,screenX=20,screenY=60,resizable,scrollbars,status,width=1100,height=500'); return false;" style='width:33%' data-api-title='<?php echo displayText('L666');?>'></button>
		</div>
		
		<div class="api-top api-col api-title" style='width:40%;'>
			<h2><?php echo displayText('L1497');//Sales Price List?></h2>
		</div>
		
		<div class="api-top api-col api-right-buttons" style='width:30%'>
			<button class='api-btn api-btn-add' onclick= "openModalBox('gerald_salesPriceListModalBox.php?modalBoxType=3',jsFunctions,'','')" style='width:33%' data-api-title='<?php echo displayText('B4');?>'></button>
			<button type='submit' name='export' value='export' class='api-btn api-btn-excel' style='width:33%' data-api-title='<?php echo displayText('L487'); ?>' form='exportFormId'></button>			
			<button type='submit' name='export' value='export2' class='api-btn api-btn-excel' style='width:33%' data-api-title='<?php echo displayText('L487'); ?>2' title='With Price Only' form='exportFormId'></button>			
			<button class='api-btn api-btn-refresh' onclick="location.href='gerald_salesPriceList.php';" style='width:33%' data-api-title='<?php echo displayText('L436');?>'></button>
		</div>
		
		<div class="api-col" style='width:100%;height:88vh;'>
			<!-------------------- Filters -------------------->
			<form action='' method='post' id='formFilter' autocomplete="off"></form>	
			<table cellpadding="0" cellspacing="0" border="0" style='width:100%;'>
				<tr style='font-size:12px;'>
					<td align='center' ><?php echo displayText('L24'); ?></td>
					<td align='center' ><?php echo displayText('L24')." ".displayText('L28'); ?></td>
					<td align='center' ><?php echo displayText('L28'); ?></td>
					<td align='center' ><?php echo displayText('L30'); ?></td>
					<td align='center' ><?php echo displayText('L112'); ?></td>
					<td rowspan='2' align='left' style=''>
						<button type='submit' class='api-btn' onclick="location.href='';" style='font-size:1.2em;' data-api-title='<?php echo displayText('B7');?>' form='formFilter'></button>
					</td>
				</tr>
				<tr>
					<td><select name='customerId' class='api-form' value='<?php echo $customerId;?>' form='formFilter'><?php echo createFilterInput($sqlFilter,'customerId',$customerId);?></select></td>
					<td><input list='customerPartNumber' name='customerPartNumber' class='api-form' value='<?php echo $customerPartNumber;?>' form='formFilter'><datalist id='customerPartNumber' class='classDataList'><?php echo createFilterInput($sqlFilter,'customerPartNumber',$customerPartNumber);?></datalist></td>
					<td><input list='partNumber' name='partNumber' class='api-form' value='<?php echo $partNumber;?>' form='formFilter'><datalist id='partNumber' class='classDataList'><?php echo createFilterInput($sqlFilter,'partNumber',$partNumber);?></datalist></td>
					<td>
						<?php
						if($_GET['country'] == 2 ) // japan select
						{
							?>
							<select name='partName' class='api-form' value='<?php echo $partName;?>' form='formFilter'>
								<?php echo createFilterInput($sqlFilter,'partName',$partName);?>
							</select>
							<?php
						}
						else // ph datalis
						{
							?>
							<input list='partName' name='partName' class='api-form' value='<?php echo $partName;?>' form='formFilter'><datalist id='partName' class='classDataList'><?php echo createFilterInput($sqlFilter,'partName',$partName);?></datalist>
							<?php
						}
						?>
					</td>
					<td><select name='currency' class='api-form' value='<?php echo $currency;?>' form='formFilter'><?php echo createFilterInput($sqlFilter,'currency',$currency);?></select></td>
				</tr>
			</table>
			<!------------------ End Filters ------------------>
			
			<!-------------------- Contents -------------------->
			<?php echo displayText('L41');?> : <span><?php echo $totalRecords; ?></span>
			<div style='height: 89%;'><!-- Adjust height if browser had a vertical scroll -->
				<table class="api-table-design1" id="myTable02" cellpadding="0" cellspacing="0">
				<?php
					// --------------------------------------- Table Header ------------------------------------------
					echo $thead = "
						<thead>
							<tr>
								<th style='width:3vw;'>".displayText('L843')."</th>
								<th style='width:3vw;'>".displayText('L52')."</th>
								<th style='width:15vw;'>".displayText('L24')."</th>
								<th style='width:15vw;'>".displayText('L24')." ".displayText('L28')."</th>                    
								<th style='width:17vw;'>".displayText('L28')."</th>
								<th style='width:4vw;'>".displayText('L226')."</th>
								<th style='width:4vw;'>".displayText('L3446')."</th>
								<th style='width:15vw;'>".displayText('L30')."</th>
								<th style='width:15vw;'>".displayText('L1303')."</th>
								<th style='width:4vw;'>".displayText('L112')."</th>
								<th style='width:5vw;'>".displayText('L667')."</th>
								<th style='width:5vw;'>".displayText('L668')."</th>
								<th style='width:5vw;'>".displayText('L669')."</th>
								<th style='width:5vw;'>".displayText('L670')."</th>
								<th style='width:4vw;'>".displayText('L1498')."</th>
								<th style='width:5vw;'>".displayText('L267')."</th>
								<th style='width:5vw;'>".displayText('L121')."</th>
							</tr>
						</thead>
					";
					//~ echo "<input type='hidden' name='thead' value=\" ".$thead." \" form='exportFormId'>";
					// ------------------------------------- End of Table Header ------------------------------				
				?>
				<tbody id='results' data-group-no='0'>
					
				</tbody>
				<tfoot>
					<tr><th colspan='15'></th></tr>
				</tfoot>
				</table>
			</div>
			<!------------------ End Contents ------------------>			
			
		</div>
	</div>
</body>

<script src="../Common Data/Templates/jquery.js"></script>
<link href="../Common Data/Libraries/Javascript/Table with Fixed Header/css/defaultTheme.css" rel="stylesheet" media="screen" />
<script src="../Common Data/Libraries/Javascript/Table with Fixed Header/jquery.fixedheadertable.js"></script>
<script>
	function loadData(url) {
		var groupNo = parseFloat($("#results").attr("data-group-no"));
		$.post(url,{'groupNo': groupNo,'sqlFilter':"<?php echo $sqlFilter; ?>"}, function(data){
			$("#results").append(data);
			$("#results").attr("data-group-no",groupNo+1);
			loading = false;
		}).fail(function(xhr, ajaxOptions, thrownError) { //any errors?
			loading = false;
		});		
	}
	
	$(function(){
		$('#myTable02').fixedHeaderTable({footer: true});
		
		loading=false;
		loadData('gerald_salesPriceListAjax.php');
		
		$('.fht-tbody').scroll(function(){
			var thisObj = $(this);
			if(thisObj.scrollTop() + thisObj.innerHeight() >= (thisObj[0].scrollHeight/1.2))
			{
				if(parseFloat($("#results").attr("data-group-no")) <= parseFloat('<?php echo $totalRecords/50;?>') && loading==false)
				{
					loading = true;
					loadData('gerald_salesPriceListAjax.php');
				}
			}
		});
	});
	
	//  -------------------------------------------------- For Modal Box Javascript Code -------------------------------------------------- //
	function jsFunctions(){
		$(window).contextmenu(function(){
			TINY.box.hide();
			return false;
		});
		
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
					$(this).css('background-color','');
				}
				else
				{
					//~ alert($(this).val());
					$(this).css('background-color','red');
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
		
		$("img.editPrice").click(function(){
			var priceId = $(this).attr("data-price-id");
			openModalBox('gerald_salesPriceListModalBox.php?modalBoxType=1',jsFunctions,'','priceId='+priceId);
		});
		
		$("#addPrice").click(function(){
			var partId = $(this).attr("data-part-id");
			openModalBox('gerald_salesPriceListModalBox.php?modalBoxType=1',jsFunctions,'','partId='+partId);
		});
		
		$("input.priceClass").change(function(){
			$("input[name=price]").val(0);
		});
		
		$("input[name=price]").change(function(){
			var price = parseFloat($(this).val()),
				accessoryPrice = parseFloat($("input[name=accessoryPrice]").val()),
				materialPrice = parseFloat($("input[name=materialPrice]").val()),
				subconPrice = parseFloat($("input[name=subconPrice]").val()),
				productionPrice = parseFloat($("input[name=productionPrice]").val()),
				deliveryPrice = parseFloat($("input[name=deliveryPrice]").val()),
				totalPrice = accessoryPrice + materialPrice + subconPrice + productionPrice + deliveryPrice;
			
			if(price < totalPrice)
			{
				alert('Price should be greater than or equal to the total price above!');
				$("input[name=price]").val(0).focus();
			}
		});
	}	
	//  ------------------------------------------------ END For Modal Box Javascript Code ------------------------------------------------ //
</script>
<script type="text/javascript" src="../Common Data/Libraries/Javascript/Tiny Box/tinybox.js"></script>
<link rel="stylesheet" href="../Common Data/Libraries/Javascript/Tiny Box/stylebox.css" />
</html>
