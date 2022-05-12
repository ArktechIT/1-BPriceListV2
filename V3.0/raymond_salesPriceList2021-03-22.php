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

$customerNameFilter = isset($_POST['customerName']) ? $_POST['customerName'] : '';
$customerPartNumberFilter = isset($_POST['customerPartNumber']) ? $_POST['customerPartNumber'] : '';
$partNumberFilter = isset($_POST['partNumber']) ? $_POST['partNumber'] : '';
$partNameFilter = isset($_POST['partName']) ? $_POST['partName'] : '';

$addQuery = "";
$customerData = "";
if($customerNameFilter != '')
{
    $addQuery .= " AND customerId IN (".implode(", ",$customerNameFilter).")";
    $customerData = implode(",", $customerNameFilter);
}

if($customerPartNumberFilter != '')
{
    $addQuery .= " AND customerPartNumber = '".$customerPartNumberFilter."'";
}

if($partNumberFilter != '')
{
    $addQuery .= " AND partNumber = '".$partNumberFilter."'";
}

if($partNameFilter != '')
{
    $addQuery .= " AND partName = '".$db->real_escape_string($partNameFilter)."'";
}

// if($partNameFilter != '')
// {
//     $addQuery .= " AND partName IN ('".implode("', '",$partNameFilter)."')";
//     $partNameDataFilter = implode(",", $partNameFilter);
// }

// if($_SESSION['idNumber'] == '0412') $addQuery .= " AND partId >= 12000";

$sqlFilter = "WHERE status IN(0,2) ".$addQuery;
$sql = "SELECT * FROM cadcam_parts ".$sqlFilter." ORDER BY partId DESC";
$sqlData = $sql;
$queryParts = $db->query($sql);
$totalRecords = $queryParts->num_rows;

?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo (displayText('L680')); # Sales Price List ?> v3.0</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/<?php echo v; ?>/Common Data/Templates/Bootstrap/w3css/w3.css">
    <link rel="stylesheet" type="text/css" href="/<?php echo v; ?>/Common Data/Libraries/Javascript/Super Quick Table/datatables.min.css">
	<link rel="stylesheet" href="/<?php echo v; ?>/Common Data/Templates/Bootstrap/Bootstrap 3.3.7/css/bootstrap.css">
	<link rel="stylesheet" href="/<?php echo v; ?>/Common Data/Templates/Bootstrap/Font Awesome/css/font-awesome.css">
	<link rel="stylesheet" href="/<?php echo v; ?>/Common Data/Templates/Bootstrap/Bootstrap 3.3.7/Roboto Font/roboto.css">
	<style>
        .dataTables_wrapper .dataTables_filter {
			position: absolute;
			text-align: right;
			visibility: hidden;
		}
        
        body
		{
			font-size: 11px;
			font-family: Roboto;
			margin:0px;
			padding:0px;
			background-color:whitesmoke;
		}
	</style>
</head>
<body id="loading" class=''>
    <form action='raymond_salesPriceListAJAX.php' method='post' id='exportFormId'></form>
	<input type='hidden' name='sqlData' value="<?php echo $sqlData;?>" form='exportFormId'>	
    <?php 
    $displayId = "1-B"; # Price List
    $version = "v3.0";
    createHeader($displayId, $version);
    ?>
	<div class="container-fluid"> 
        <div class="w3-padding-top"></div>
        <div class="row w3-padding-top">
			<div class="col-lg-7 col-md-7 col-sm-12 col-xs-12">
				<a href='/<?php echo v; ?>/1-B Price List V2/V3.0/raymond_salesPriceList.php'><button class="w3-btn w3-round w3-pink"><i class='fa fa-send'></i>&emsp;<b><?php echo (displayText('L792', 'utf8', 0, 0, 1));?></b></button></a>
				<a href='/<?php echo v; ?>/4-D Purchasing Price List/gerald_priceV2.php'><button class="w3-btn w3-round w3-blue"><i class='fa fa-send'></i>&emsp;<b><?php echo (displayText('L679', 'utf8', 0, 0, 1));?></b></button></a>
            </div>
			<div class="hidden-lg hidden-md hidden-sm col-xs-4">
                <div class="w3-padding-top"></div>
                <div class="w3-padding-top"></div>
			</div>
			<div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                <div class='w3-right'>
                    <button class="w3-btn w3-round w3-blue" id='addData'><i class='fa fa-plus'></i>&emsp;<b><?php echo (displayText('L482', 'utf8', 0, 0, 1));?></b></button>
                    <button class="w3-btn w3-round w3-pink" id='filterData'><i class='fa fa-list'></i>&emsp;<b><?php echo (displayText('L437', 'utf8', 0, 0, 1));?></b></button>
                    <button class="w3-btn w3-round w3-purple" name='export' id='exportData1' value='export1' form='exportFormId'><i class='fa fa-file-excel-o'></i>&emsp;<b><?php echo (displayText("L487", 'utf8', 0, 0, 1)); // Export ?> 1</b></button>
                    <button class="w3-btn w3-round w3-deep-purple" name='export' id='exportData2' value='export2' form='exportFormId'><i class='fa fa-file-excel-o'></i>&emsp;<b><?php echo (displayText("L487", 'utf8', 0, 0, 1)); // Export ?> 2</b></button>
                    <button class='w3-btn w3-round w3-green' onclick="location.href='';"><i class='fa fa-refresh'></i>&emsp;<b><?php echo (displayText('L436', 'utf8', 0, 0, 1));?></b></button>
                </div>
            </div>
        </div>
		<div class="row w3-padding"></div>
		<div class="row">
			<div class="col-lg-12"> 
                <label><?php echo (displayText("L41", 'utf8', 0, 0, 1)); # Records ?> : <?php echo $totalRecords; ?></label>
                <table id='mainTableId' style='' class="table table-bordered table-striped table-condensed">
                    <thead class='w3-indigo' style='text-transform: uppercase;'>
                        <th class='w3-center' style='vertical-align:middle;'>#</th>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L52')); # PART ID ?></th>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L24')); # CUSTOMER ?></th>
                        <?php
                        if($_GET['country'] == 1)
                        {
                        ?>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L3764')); # CUSTOMER PART NUMBER ?></th>
                        <?php
                        }
                        ?>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L28')); # PART NUMBER ?></th>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L226')); # REV ?></th>
                        <?php
                        if($_GET['country'] == 1)
                        {
                        ?>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L3446')); # PART NOTE ?></th>
                        <?php
                        }
                        ?>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L30')); # PART NAME ?></th>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L1303')); # ITEM GROUP ?></th>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L112')); # CURRENCY ?></th>
                        <!-- <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L667')); # ACCESSORY PRICE ?></th>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L668')); # MATERIAL PRICE ?></th>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L669')); # SUBCON PRICE ?></th>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L670')); # PRODUCTION PRICE ?></th>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L1498')); # DELIVERY PRICE ?></th> -->
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L267')); # PRICE ?></th>
                        <th class='w3-center' style='vertical-align:middle;'><?php echo (displayText('L121')); # DTL ?></th>
                    </thead>
                    <tbody class='w3-center'>
                    </tbody>
                    <tfoot class='w3-indigo' style='text-transform: uppercase;'>
                        <tr>
                            <th class='w3-center' style='vertical-align:middle;'></th>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # PART ID ?></th>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # CUSTOMER ?></th>
                            <?php
                            if($_GET['country'] == 1)
                            {
                            ?>
                            <th class='w3-center' style='vertical-align:middle;'><?php # CUSTOMER PART NUMBER ?></th>
                            <?php
                            }
                            ?>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # PART NUMBER ?></th>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # REV ?></th>
                            <?php
                            if($_GET['country'] == 1)
                            {
                            ?>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # PART NOTE ?></th>
                            <?php
                            }
                            ?>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # PART NAME ?></th>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # ITEM GROUP ?></th>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # CURRENCY ?></th>
                            <!-- <th class='w3-center' style='vertical-align:middle;'><?php  # ACCESSORY PRICE ?></th>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # MATERIAL PRICE ?></th>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # SUBCON PRICE ?></th>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # PRODUCTION PRICE ?></th>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # DELIVERY PRICE ?></th> -->
                            <th class='w3-center' style='vertical-align:middle;'><?php  # PRICE ?></th>
                            <th class='w3-center' style='vertical-align:middle;'><?php  # DTL ?></th>
                        </tr>
                    </tfoot>
                </table>
			</div>
		</div>
	</div>
    <div id='modal-izi'><span class='izimodal-content'></span></div>
    <div id='modal-izi-edit'><span class='izimodal-content-edit'></span></div>
    <div id='modal-izi-update'><span class='izimodal-content-update'></span></div>
</body>
<script src="/<?php echo v; ?>/Common Data/Libraries/Javascript/jQuery 3.1.1/jquery-3.1.1.js"></script>
<script src="/<?php echo v; ?>/Common Data/Libraries/Javascript/jQuery 3.1.1/jquery-ui.js"></script>
<script src="/<?php echo v; ?>/Common Data/Libraries/Javascript/jQuery 3.1.1/bootstrap.min.js"></script>
<script src="/<?php echo v; ?>/Common Data/Libraries/Javascript/Super Quick Table/datatables.min.js"></script>
<link rel="stylesheet" href="/<?php echo v; ?>/Common Data/Libraries/Javascript/Bootstrap Multi-Select JS/dist/css/bootstrap-multiselect.css" type="text/css" media="all" />
<script src="/<?php echo v; ?>/Common Data/Libraries/Javascript/Bootstrap Multi-Select JS/dist/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="/<?php echo v; ?>/Common Data/Libraries/Javascript/iziModal-master/css/iziModal.css" />
<script src="/<?php echo v; ?>/Common Data/Libraries/Javascript/iziModal-master/js/iziModal.js"></script>
<link rel="stylesheet" href="/<?php echo v; ?>/Common Data/Libraries/Javascript/iziToast-master/dist/css/iziToast.css" />
<script src="/<?php echo v; ?>/Common Data/Libraries/Javascript/iziToast-master/dist/js/iziToast.js"></script>
<script>
function modalFunction(partId)
{
    $("#modal-izi").iziModal({
        title 					: '<i class="fa fa-list w3-large"></i>&emsp;<label class="w3-large">DETAILS</label>',
        headerColor 			: '#1F4788',
        subtitle 				: '',
        width 					: 1500,
        fullscreen 				: false,
        // openFullscreen			: true,
        transitionIn 			: 'comingIn',
        transitionOut 			: 'comingOut',
        padding 				: 20,
        radius 					: 0,
        restoreDefaultContent 	: true,
        closeOnEscape           : true,
        closeButton             : true,
        overlayClose            : false,
        onOpening   			: function(modal){
                                    modal.startLoading();
                                    setTimeout(function(){
                                        $.post( "gerald_salesPriceListModalBox.php?modalBoxType=2", { partId, partId }, function( data ) {
                                            $( ".izimodal-content" ).html(data);
                                            modal.stopLoading();
                                        });
                                    }, 100);
                                },
        onClosed                : function(modal){
                                        $("#modal-izi").iziModal("destroy");
                            } 
    });

    $("#modal-izi").iziModal("open");
}

function modalFunctionUpdate(partId)
{
    $("#modal-izi").iziModal("close");
    $("#modal-izi-update").iziModal({
        title 					: '<i class="fa fa-list w3-large"></i>&emsp;<label class="w3-large">PRICE RANGE</label>',
        headerColor 			: '#1F4788',
        subtitle 				: '',
        width 					: 1500,
        fullscreen 				: false,
        // openFullscreen			: true,
        transitionIn 			: 'comingIn',
        transitionOut 			: 'comingOut',
        padding 				: 20,
        radius 					: 0,
        restoreDefaultContent 	: true,
        closeOnEscape           : true,
        closeButton             : true,
        overlayClose            : false,
        onOpening   			: function(modal){
                                    modal.startLoading();
                                    setTimeout(function(){
                                        $.post( "gerald_salesPriceListModalBox.php?modalBoxType=4", { partId, partId }, function( data ) {
                                            $( ".izimodal-content-update" ).html(data);
                                            modal.stopLoading();
                                        });
                                    }, 100);
                                },
        onClosed                : function(modal){
                                        $("#modal-izi-update").iziModal("destroy");
                            } 
    });

    $("#modal-izi-update").iziModal("open");
}

function modalFunctionEdit(priceId='', partId='')
{
    $("#modal-izi").iziModal("close");
    $("#modal-izi-edit").iziModal({
        title 					: '<i class="fa fa-list w3-large"></i>&emsp;<label class="w3-large">SALES PRICE INPUT FORM</label>',
        headerColor 			: '#1F4788',
        subtitle 				: '',
        width 					: 500,
        fullscreen 				: false,
        // openFullscreen			: true,
        transitionIn 			: 'comingIn',
        transitionOut 			: 'comingOut',
        padding 				: 20,
        radius 					: 0,
        restoreDefaultContent 	: true,
        closeOnEscape           : true,
        closeButton             : true,
        overlayClose            : false,
        onOpening   			: function(modal){
                                    modal.startLoading();
                                    setTimeout(function(){
                                        $.post( "gerald_salesPriceListModalBox.php?modalBoxType=1", { priceId: priceId, partId : partId }, function( data ) {
                                            $( ".izimodal-content-edit" ).html(data);
                                            modal.stopLoading();
                                        });
                                    }, 100);
                                },
        onClosed                : function(modal){
                                        $("#modal-izi-edit").iziModal("destroy");
                            } 
    });

    $("#modal-izi-edit").iziModal("open");
}

$(document).ready(function(){
    var sqlData = "<?php echo $sqlData; ?>";
    var country = "<?php echo $_GET['country']; ?>";
    var totalRecords = "<?php echo $totalRecords; ?>";
    var hiddenColumn = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16 ];
    var dataTable = $('#mainTableId').DataTable( {
        "processing"    : true,
        "ordering"      : false,
        "serverSide"    : true,
        "bInfo" 		: false,
        "ajax":{
            url     :"raymond_salesPriceListAJAX.php", // json datasource
            type    : "post",  // method  , by default get
            data    : {
                        "totalRecords"   	: totalRecords,
                        "sqlData"     	    : sqlData
                        },
            error: function(data){  // error handling
                
                $(".mainTableId-error").html("");
                $("#mainTableId").append('<tbody class="mainTableId-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                $("#mainTableId_processing").css("display","none");
                
            }
        },
        "createdRow": function( row, data, index ) {
            $(row).addClass("w3-hover-orange");
            $(row).addClass("rowColors");

            if(country == 1)
            {
                $('td:eq(10), td:eq(11)', row).click(function(){
                    $('.rowColors').each(function(){
                        $(this).removeClass("w3-orange");
                    });

                    $(row).addClass("w3-orange");
                });
            }
            else
            {
                $('td:eq(9), td:eq(10)', row).click(function(){
                    $('.rowColors').each(function(){
                        $(this).removeClass("w3-orange");
                    });

                    $(row).addClass("w3-orange");
                });
            }
            
        },
        "columnDefs": [
                        {
                            "targets" 		: [ ] ,
                            "visible"		: false,
                            "searchable" 	: true
                        }
        ],
        language	: {
                    processing	: "<span class='loader'></span>"
        },
        // fixedColumns:   {
        //         leftColumns: 0
        // },
        // responsive		: true,
        deferRender     : true,
        scrollY     	: 505,
        scrollX     	: false,
        scrollCollapse	: false,
        scroller    	: {
            loadingIndicator    : true
        },
        stateSave   	: false
    });
    
    $("#addData").click(function(){
        $("#modal-izi").iziModal({
            title 					: '<i class="fa fa-plus w3-large"></i>&emsp;<label class="w3-large">ADD</label>',
            headerColor 			: '#1F4788',
            subtitle 				: '',
            width 					: 400,
            fullscreen 				: false,
            // openFullscreen			: true,
            transitionIn 			: 'comingIn',
            transitionOut 			: 'comingOut',
            padding 				: 20,
            radius 					: 0,
            restoreDefaultContent 	: true,
            closeOnEscape           : true,
            closeButton             : true,
            overlayClose            : false,
            onOpening   			: function(modal){
                                        modal.startLoading();
                                        setTimeout(function(){
                                            $.post( "gerald_salesPriceListModalBox.php?modalBoxType=3", function( data ) {
                                                $( ".izimodal-content" ).html(data);
                                                modal.stopLoading();
                                            });
                                        }, 100);
                                    },
            onClosed                : function(modal){
                                            $("#modal-izi").iziModal("destroy");
                                } 
        });

        $("#modal-izi").iziModal("open");
    });

    $("#filterData").click(function(){
        var customerData = "<?php echo $customerData; ?>";
        var customerPartNumberFilter = "<?php echo $customerPartNumberFilter; ?>";
        var partNumberFilter = "<?php echo $partNumberFilter; ?>";
        var partNameFilter = "<?php echo $partNameDataFilter; ?>";
        var currencyFilter = "<?php echo $currencyFilter; ?>";
        var sqlFilter = "<?php echo $sqlFilter; ?>";
        var session = "<?php echo $_SESSION['idNumber']; ?>";

        var linkFilter = "raymond_filterData2.php";
        // if(session == "0412") var linkFilter = "raymond_filterData.php";

        $("#modal-izi").iziModal({
            title                   : '<i class="fa fa-flash"></i> <?php echo (displayText("L437", "utf8", 0, 1, 1)); ?>',
            headerColor             : '#1F4788',
            subtitle                : '<b style="text-transform:uppercase;"><?php echo (date('F d, Y'));?></b>',
            width                   : 1200,
            fullscreen              : false,
            transitionIn            : 'comingIn',
            transitionOut           : 'comingOut',
            padding                 : 20,
            radius                  : 0,
            top                     : 10,
            restoreDefaultContent   : true,
            closeOnEscape           : true,
            closeButton             : true,
            overlayClose            : false,
            onOpening               : function(modal){
                                        modal.startLoading();
                                        $.post( linkFilter, {
                                            sqlData                     : sqlData,
                                            sqlFilter                   : sqlFilter,
                                            customerData                : customerData,
                                            customerPartNumberFilter    : customerPartNumberFilter,
                                            partNumberFilter            : partNumberFilter,
                                            partNameFilter              : partNameFilter,
                                            currencyFilter              : currencyFilter
                                        }, function( data ) {
                                            $( ".izimodal-content" ).html(data);
                                            modal.stopLoading();
                                        });
                                    },
                onClosed            : function(modal){
                                        $("#modal-izi").iziModal("destroy");
                        }
        });

        $("#modal-izi").iziModal("open");
    });
});
</script>
