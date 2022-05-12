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
/* Database connection end */

// storing  request (ie, get/post) global array to a variable  
$requestData= $_REQUEST;
$sqlData = isset($requestData['sqlData']) ? $requestData['sqlData'] : "";
$export = isset($requestData['export']) ? $requestData['export'] : "";
$totalRecords = (isset($requestData['totalRecords'])) ? $requestData['totalRecords'] : 0;
$totalFiltered = $totalRecords;
$totalData = $totalFiltered;

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

$data = array();

$sql = $sqlData;
if($export!='')
{
    $counter = 0;
}
else
{
    $sql.=" LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
    $counter = $requestData['start'];
}
$queryParts = $db->query($sql);
if($queryParts AND $queryParts->num_rows > 0)
{
    while($resultParts = $queryParts->fetch_assoc())
    {
        $partId = $resultParts['partId'];
        $customerPartNumber = $resultParts['customerPartNumber'];
        $partNumber = $resultParts['partNumber'];
        $partName = $resultParts['partName'];
        $revisionId = $resultParts['revisionId'];
        $partNote = $resultParts['partNote'];
        $customerId = $resultParts['customerId'];
        $itemGroup = $resultParts['itemGroup'];

        

        $customerDrawing = $_SERVER['DOCUMENT_ROOT']."/Document Management System/Master Folder/MAIN_".$partId.".pdf";
        if(file_exists($customerDrawing))
        {
            // $url = "window.open('/Document Management System/Master Folder/MAIN_".$partId.".pdf', '_blank', 'toolbar=yes, scrollbars=yes, resizable=yes, top=100, left=100, width=1200, height=600')";
            // $buttonDwg = "<i style='cursor:pointer;' class='fa fa-photo w3-medium w3-text-purple' onclick=\"".$url."\"></i>";
            $url = "window.open('/".v."/20 Document Management System/raymond_drawingViewer.php?partId=".$partId."&dwg=1','cc','left=50,screenX=700,screenY=20,resizable,scrollbars,status,width=1000,height=700')";
            $buttonDwg = "<i style='cursor:pointer;' class='fa fa-photo w3-medium w3-text-purple' onclick=\"".$url."\" title='Drawing'></i>";
        }

        $customerName = '';
        $sql = "SELECT customerName FROM sales_customer WHERE customerId = ".$customerId." LIMIT 1";
        $queryCustomer = $db->query($sql);
        if($queryCustomer AND $queryCustomer->num_rows > 0)
        {
            $resultCustomer = $queryCustomer->fetch_array();
            $customerName = $resultCustomer['customerName'];
        }

        $currency = $currencyValue = '';
        $accessoryPrice = $materialPrice = $subconPrice = $productionPrice = $deliveryPrice = $price = '<b>NO PRICE</b>';
        $priceOnClick = "<u class='w3-text-pink' style='cursor:pointer;' onclick=\"modalFunctionEdit('',".$partId.");\"><b>ADD PRICE</b></u>";
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

            $priceOnClick = "<u class='w3-text-blue' style='cursor:pointer;' onclick=\"modalFunction(".$partId.");\"><b>".round($price,2)."</b></u>";

            if($currency==1)		$currencyValue = 'Dollar';
            else if($currency==2)	$currencyValue = 'Peso';
            else if($currency==3)	$currencyValue = 'Yen';	
        }
        else
        {
            if($export=='export2')
            {
                continue;
            }
        }
        
        if($export != '')
        {
            $nestedData = Array ();
            if($export=='export2')
            {
                $nestedData[] = $partId;
                $nestedData[] = $customerName;
                if($_GET['country'] == 1) $nestedData[] = $customerPartNumber;
                $nestedData[] = $partNumber;
                $nestedData[] = $revisionId;
                if($_GET['country'] == 1) $nestedData[] = $partNote;
                $nestedData[] = $partName;
                $nestedData[] = $itemGroup;
                $nestedData[] = $currencyValue;
                $nestedData[] = $price;
            }
            else
            {
                $nestedData[] = $partId;
                $nestedData[] = $customerName;
                if($_GET['country'] == 1) $nestedData[] = $customerPartNumber;
                $nestedData[] = $partNumber;
                $nestedData[] = $revisionId;
                if($_GET['country'] == 1) $nestedData[] = $partNote;
                $nestedData[] = $partName;
                $nestedData[] = $itemGroup;
                $nestedData[] = $currencyValue;
                // $nestedData[] = $accessoryPrice;
                // $nestedData[] = $materialPrice;
                // $nestedData[] = $subconPrice;
                // $nestedData[] = $productionPrice;
                // $nestedData[] = $deliveryPrice;
                $nestedData[] = $priceOnClick;
                $nestedData[] = $buttonDwg;
            }
        }
        else
        {
            $nestedData = Array ();
            $nestedData[] = "<b>".++$counter."</b>";
            $nestedData[] = $partId;
            $nestedData[] = $customerName;
            if($_GET['country'] == 1) $nestedData[] = $customerPartNumber;
            $nestedData[] = $partNumber;
            $nestedData[] = $revisionId;
            if($_GET['country'] == 1) $nestedData[] = $partNote;
            $nestedData[] = $partName;
            $nestedData[] = $itemGroup;
            $nestedData[] = $currencyValue;
            // $nestedData[] = $accessoryPrice;
            // $nestedData[] = $materialPrice;
            // $nestedData[] = $subconPrice;
            // $nestedData[] = $productionPrice;
            // $nestedData[] = $deliveryPrice;
            $nestedData[] = $priceOnClick;
            $nestedData[] = $buttonDwg;
        }

        $data[] = $nestedData;
    }
}

if($export != '')
{
    ?>
    <table border = 1>
        <thead class='w3-indigo' style='text-transform: uppercase;'>
            <?php
            if($export=='export2')
            {
            ?>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L52')); # PART ID ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L24')); # CUSTOMER ?></th>
                <?php
                if($_GET['country'] == 1)
                {
                ?>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L3764')); # CUSTOMER PART NUMBER ?></th>
                <?php
                }
                ?>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L28')); # PART NUMBER ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L226')); # REV ?></th>
                <?php
                if($_GET['country'] == 1)
                {
                ?>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L3446')); # PART NOTE ?></th>
                <?php
                }
                ?>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L30')); # PART NAME ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L1303')); # ITEM GROUP ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L112')); # CURRENCY ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L267')); # PRICE ?></th>
            <?php
            }
            else
            {
            ?>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L52')); # PART ID ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L24')); # CUSTOMER ?></th>
                <?php
                if($_GET['country'] == 1)
                {
                ?>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L3764')); # CUSTOMER PART NUMBER ?></th>
                <?php
                }
                ?>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L28')); # PART NUMBER ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L226')); # REV ?></th>
                <?php
                if($_GET['country'] == 1)
                {
                ?>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L3446')); # PART NOTE ?></th>
                <?php
                }
                ?>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L30')); # PART NAME ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L1303')); # ITEM GROUP ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L112')); # CURRENCY ?></th>
                <!-- <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L667')); # ACCESSORY PRICE ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L668')); # MATERIAL PRICE ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L669')); # SUBCON PRICE ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L670')); # PRODUCTION PRICE ?></th>
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L1498')); # DELIVERY PRICE ?></th> -->
                <th class='w3-center' style='vertical-align:middle;'><?php echo strtoupper(displayText('L267')); # PRICE ?></th>
            <?php
             }
            ?>
        </thead>
        <tbody>
        <?php
        foreach($data AS $key)
        {
            echo "<tr>";
            foreach($key AS $value)
            {
                echo "<td>".$value."</td>";
            }   
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
    <?php
}
else
{
    $json_data = array(
                "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                "recordsTotal"    => intval( $totalData ),  // total number of records
                "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
                "data"            => $data   // total data array
        );

    echo json_encode($json_data);  // send data as json format
}
?>
