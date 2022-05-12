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

// $customerIdArray = isset($_POST['customerIdArray']) ? $_POST['customerIdArray'] : '';
$customerData = isset($_POST['customerData']) ? $_POST['customerData'] : '';

// $customerPartNumberArray = isset($_POST['customerPartNumberArray']) ? $_POST['customerPartNumberArray'] : '';
$customerPartNumberFilter = isset($_POST['customerPartNumberFilter']) ? $_POST['customerPartNumberFilter'] : '';

// $partNumberArray = isset($_POST['partNumberArray']) ? $_POST['partNumberArray'] : '';
$partNumberFilter = isset($_POST['partNumberFilter']) ? $_POST['partNumberFilter'] : '';

// $partNameArray = isset($_POST['partNameArray']) ? $_POST['partNameArray'] : '';
$partNameFilter = isset($_POST['partNameFilter']) ? $_POST['partNameFilter'] : '';

// $currencyArray = isset($_POST['currencyArray']) ? $_POST['currencyArray'] : '';
$currencyFilter = isset($_POST['currencyFilter']) ? $_POST['currencyFilter'] : '';


$customerIdArray = $customerPartNumberArray = $partNumberArray = $partNameArray = $currencyArray = Array();
$sqlData = isset($_POST['sqlData']) ? $_POST['sqlData'] : "";
$sql = $sqlData;
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

        $customerIdArray[] = $customerId;
        $customerPartNumberArray[] = $customerPartNumber;
        $partNumberArray[] = $partNumber;
        $partNameArray[] = $partName;

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

            if($currency==1)		$currencyValue = 'Dollar';
            else if($currency==2)	$currencyValue = 'Peso';
            else if($currency==3)	$currencyValue = 'Yen';	

            $currencyArray[] = $currency;
        }
    }
}

echo "<form id='formFilter' method='POST' action=''></form>";
echo "<div class='w3-padding-top'></div>";
echo "<div class='row'>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".(displayText("L24", 'utf8', 0, 0, 1))."</label>"; # CUSTOMER
        echo "<select class='w3-input w3-border' id='customerName' name='customerName[]' multiple='multiple' form='formFilter'>";
            $customerIdArray = array_unique($customerIdArray);
            $sql = "SELECT customerId, customerName FROM sales_customer WHERE customerId IN (".implode(", ",$customerIdArray).") ORDER BY customerName";
            $queryCustomer = $db->query($sql);
            if($queryCustomer AND $queryCustomer->num_rows > 0)
            {
                while($resultCustomer = $queryCustomer->fetch_array())
                {
                    $customerId = $resultCustomer['customerId'];
                    $customerName = $resultCustomer['customerName'];

                    $selectedCustomer = "";
                    if($customerData != "")
                    {       
                        $customerDataExploded = explode(",",$customerData);
                        if(in_array($customerId, $customerDataExploded)) $selectedCustomer =  "selected";
                    }

                    echo "<option ".$selectedCustomer." value='".$customerId."'>".$customerName."</option>";
                }
            }
        echo "</select>";
    echo "</div>";
    if($_GET['country'] == 1)
    {
        echo "<div class='col-md-2'>";
            echo "<label class='w3-tiny'>".(displayText("L3764", 'utf8', 0, 0, 1))."</label>"; # CUSTOMER PART NUMBER
            echo "<input list='customerPartNum' class='w3-input w3-border w3-pale-red' name='customerPartNumber' form='formFilter' value='".$customerPartNumberFilter."'>";
            echo "<datalist id='customerPartNum'>";
                if($customerPartNumberArray != NULL)
                {
                    $customerPartNumberArray = array_unique($customerPartNumberArray);
                    foreach($customerPartNumberArray AS $key)
                    {       
                        echo "<option>".$key."</option>";
                    }
                }
            echo "<datalist>";
        echo "</div>";
    }
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".(displayText("L28", 'utf8', 0, 0, 1))."</label>"; # PART NUMBER
        echo "<input list='partNum' class='w3-input w3-border w3-pale-red' name='partNumber' form='formFilter' value='".$partNumberFilter."'>";
        echo "<datalist id='partNum'>";
            if($partNumberArray != NULL)
            {   
                $partNumberArray = array_unique($partNumberArray);
                sort($partNumberArray);
                foreach($partNumberArray AS $key)
                {       
                    echo "<option>".$key."</option>";
                }
            }
        echo "<datalist>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".(displayText("L30", 'utf8', 0, 0, 1))."</label>"; # PART NAME
        /* echo "<select class='w3-input w3-border' id='partName' name='partName[]' multiple='multiple' form='formFilter'>";
            $partNameArray = array_unique($partNameArray);
            sort($partNameArray);
            foreach($partNameArray AS $key)
            {
                if(trim($key) != "")
                {
                    $selectedPartName = "";
                    if($partNameFilter != "")
                    {
                        $partNameDataExploded = explode(",",$partNameFilter);
                        if(in_array($key, $partNameDataExploded)) $selectedPartName =  "selected";
                    }

                    echo "<option ".$selectedPartName.">".$key."</option>";
                }
            }
        echo "</select>"; */
        echo "<input list='partN' class='w3-input w3-border w3-pale-red' name='partName' form='formFilter' value='".$partNameFilter."' autocomplete='off'>";
        echo "<datalist id='partN'>";
            $partNameArray = array_unique($partNameArray);
            sort($partNameArray);
            foreach($partNameArray AS $key)
            {
                if(trim($key) != "")
                {
                    $selectedPartName = "";
                    if($partNameFilter != "")
                    {
                        $partNameDataExploded = explode(",",$partNameFilter);
                        if(in_array($key, $partNameDataExploded)) $selectedPartName =  "selected";
                    }

                    echo "<option ".$selectedPartName.">".$key."</option>";
                }
            }
        echo "<datalist>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".(displayText("L112", 'utf8', 0, 0, 1))."</label>"; # CURRENCY
        echo "<select class='w3-input w3-border' id='currency' name='currency' form='formFilter'>";
            $currencyArray = array_unique($currencyArray);
            echo "<option value=''></option>";
            foreach($currencyArray AS $key)
            {
                if(trim($key) != "")
                {
                    $selectedCurrency = ($key == $currencyFilter) ? 'selected' : '';

                    if($key==1)		$currencyValue = 'Dollar';
                    else if($key==2)	$currencyValue = 'Peso';
                    else if($key==3)	$currencyValue = 'Yen';	

                    echo "<option ".$selectedCurrency." value='".$key."'>".$currencyValue."</option>";
                }
            }
        echo "</select>";
    echo "</div>";
echo "</div>";
echo "<div class='w3-padding-top'></div>";
echo "<div class='row w3-padding'>";
    echo "<div class='col-md-12 w3-center'>";
        echo "<button class='w3-btn w3-round w3-small w3-indigo' form='formFilter'><i class='fa fa-search'></i>&emsp;<b>".(displayText("B5", 'utf8', 0, 0, 1))."</b></button>";
    echo "</div>";
echo "</div>";
?>
<link rel="stylesheet" href="/<?php echo v; ?>/Common Data/Libraries/Javascript/Bootstrap Multi-Select JS/dist/css/bootstrap-multiselect.css" type="text/css" media="all" />
<script src="/<?php echo v; ?>/Common Data/Libraries/Javascript/Bootstrap Multi-Select JS/dist/js/bootstrap-multiselect.js"></script>
<script>
$(document).ready(function(){
    $('#customerName, #partName, #typeData, #currentProcess').multiselect({
        maxHeight: 300,
        includeSelectAllOption: true,
        buttonClass:'w3-input w3-border w3-pale-yellow',
        buttonWidth: '100%',
        nonSelectedText : 'Select',
        numberDisplayed: 0,
        onSelectAll: function(event) {
            event.preventDefault();
        },
        onDeselectAll: function(event) {
            event.preventDefault();
        },
        onChange: function(event) {
            event.preventDefault();
        }
    });

    $('#customerAlias, #partName, #typeData').click(function(event){
        event.preventDefault();
    });
});
</script>