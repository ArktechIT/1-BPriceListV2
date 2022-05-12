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

if(isset($_POST['updateItemG']))
{
    $partId = isset($_POST['partId']) ? $_POST['partId'] : '';
    $itemGroup = isset($_POST['itemGroup']) ? $_POST['itemGroup'] : '';
    $sql = "UPDATE cadcam_parts SET itemGroup = '".$itemGroup."' WHERE partId = ".$partId;
    $queryUpdate = $db->query($sql);
    header("location:gerald_salesPriceList.php");
    exit(0);
}

$partId = isset($_GET['partId']) ? $_GET['partId'] : '';
$sql = "SELECT itemGroup FROM cadcam_parts WHERE partId = ".$partId;
$queryItemGroup = $db->query($sql);
if($queryItemGroup AND $queryItemGroup->num_rows > 0)
{
    $resultItemGroup = $queryItemGroup->fetch_assoc();
    $itemGroup = $resultItemGroup['itemGroup'];
    echo "<form id='updateForm' method='POST' action='".$_SERVER['PHP_SELF']."'>";
    echo "<b>ITEM GROUP :<b>";
    echo "<input form='updateForm' type='hidden' name='partId' value=".$partId.">";
    echo "<input form='updateForm' type='text' name='itemGroup' value='".$itemGroup."'>";
    echo "<button type='submit' form='updateForm' name='updateItemG'>UPDATE</button>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
}
?>