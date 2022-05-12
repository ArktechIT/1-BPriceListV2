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

# TYPE : 0 - select; 1 - Datalist;
function filterForm($sqlFilter,$column,$value,$type=0,$multiple=0)
{   
    include('PHP Modules/mysqliConnection.php');
    if($column == "customerId")
    {
        $sql = "SELECT DISTINCT customerId FROM cadcam_parts ".$sqlFilter."";
        $query = $db->query($sql);
        if($query->num_rows > 0)
        {
            while($result = $query->fetch_array())
            {
                $customerIdArray[] = $result['customerId'];
            }
        }
        
        $sql = "SELECT customerId, customerName FROM sales_customer WHERE customerId IN (".implode(", ",$customerIdArray).") ORDER BY customerName";
        $queryCustomer = $db->query($sql);
        if($queryCustomer AND $queryCustomer->num_rows > 0)
        {
            while($resultCustomer = $queryCustomer->fetch_array())
            {
                $customerId = $resultCustomer['customerId'];
                $customerName = $resultCustomer['customerName'];
    
                $selectedCustomer = "";
                if($value != "")
                {       
                    $customerDataExploded = explode(",",$value);
                    if(in_array($customerId, $customerDataExploded)) $selectedCustomer =  "selected";
                }
    
                $return .= "<option ".$selectedCustomer." value='".$customerId."'>".$customerName."</option>";
            }
        }
    }
    else if($column == "currency")
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

        $return = "<option></option>";
        if(count($partIdArray) > 0)
        {
            $sql = "SELECT DISTINCT currency FROM sales_pricelist WHERE arkPartId IN(".implode(",",$partIdArray).") AND currency !='' ORDER BY currency";
            $queryFilter = $db->query($sql);
            if($queryFilter AND $queryFilter->num_rows > 0)
            {
                while ($resultFilter = $queryFilter->fetch_assoc()) 
                {
                    $currency = $resultFilter['currency'];
                    if($currency==1)		$currencyValue = 'Dollar';
                    else if($currency==2)	$currencyValue = 'Peso';
                    else if($currency==3)	$currencyValue = 'Yen';	

                    $selectedCurrency = ($currency == $value) ? 'selected' : '';

                    $return .= "<option ".$selectedCurrency." value='".$currency."'>".$currencyValue."</option>";
                }
            }
        }
    }
    else
    {
        $sql = "SELECT DISTINCT ".$column." FROM cadcam_parts ".$sqlFilter." ORDER BY ".$column."";
        $queryFilter = $db->query($sql);
        if($queryFilter AND $queryFilter->num_rows > 0)
        {
            while ($resultFilter = $queryFilter->fetch_assoc()) 
            {
                $valueData = $resultFilter[$column];
                $return .= "<option>".$valueData."</option>";
            }
        }
    }

    return $return;
}

$customerData = isset($_POST['customerData']) ? $_POST['customerData'] : '';
$customerPartNumberFilter = isset($_POST['customerPartNumberFilter']) ? $_POST['customerPartNumberFilter'] : '';
$partNumberFilter = isset($_POST['partNumberFilter']) ? $_POST['partNumberFilter'] : '';
$partNameFilter = isset($_POST['partNameFilter']) ? $_POST['partNameFilter'] : '';
$currencyFilter = isset($_POST['currencyFilter']) ? $_POST['currencyFilter'] : '';


$customerIdArray = $customerPartNumberArray = $partNumberArray = $partNameArray = $currencyArray = Array();
$sqlData = isset($_POST['sqlData']) ? $_POST['sqlData'] : "";
$sqlFilter = isset($_POST['sqlFilter']) ? $_POST['sqlFilter'] : "";

echo "<form id='formFilter' method='POST' action=''></form>";
echo "<div class='w3-padding-top'></div>";
echo "<div class='row'>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".(displayText("L24", 'utf8', 0, 0, 1))."</label>"; # CUSTOMER
        echo "<select class='w3-input w3-border' id='customerName' name='customerName[]' multiple='multiple' form='formFilter'>";
            echo filterForm($sqlFilter,'customerId',$customerData);
        echo "</select>";
    echo "</div>";
    if($_GET['country'] == 1)
    {
        echo "<div class='col-md-2'>";
            echo "<label class='w3-tiny'>".(displayText("L3764", 'utf8', 0, 0, 1))."</label>"; # CUSTOMER PART NUMBER
            echo "<input list='customerPartNum' class='w3-input w3-border w3-pale-red' name='customerPartNumber' form='formFilter' value='".$customerPartNumberFilter."' autocomplete='off'>";
            echo "<datalist id='customerPartNum'>";
                echo filterForm($sqlFilter,'customerPartNumber',$customerPartNumberFilter);
            echo "<datalist>";
        echo "</div>";
    }
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".(displayText("L28", 'utf8', 0, 0, 1))."</label>"; # PART NUMBER
        echo "<input list='partNum' class='w3-input w3-border w3-pale-red' name='partNumber' form='formFilter' value='".$partNumberFilter."' autocomplete='off'>";
        echo "<datalist id='partNum'>";
            echo filterForm($sqlFilter,'partNumber',$partNumberFilter);
        echo "<datalist>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".(displayText("L30", 'utf8', 0, 0, 1))."</label>"; # PART NAME
        // echo "<select class='w3-input w3-border' id='partName' name='partName[]' multiple='multiple' form='formFilter'>";
        //     echo filterForm($sqlFilter,'partName',$partNameFilter);
        // echo "</select>";
        echo "<input list='partN' class='w3-input w3-border w3-pale-red' name='partName' form='formFilter' value='".$partNameFilter."' autocomplete='off'>";
        echo "<datalist id='partN'>";
            echo filterForm($sqlFilter,'partName',$partNameFilter);
        echo "<datalist>";
    echo "</div>";
    echo "<div class='col-md-2'>";
        echo "<label class='w3-tiny'>".(displayText("L112", 'utf8', 0, 0, 1))."</label>"; # CURRENCY
        echo "<select class='w3-input w3-border' id='currency' name='currency' form='formFilter'>";
            echo filterForm($sqlFilter,'currency',$currencyFilter);
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