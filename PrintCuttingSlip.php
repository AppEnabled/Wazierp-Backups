<?php

/* $Revision: 1.49 $ */

// $PageSecurity = 1;
error_reporting (E_ALL);
include('includes/session.inc');
$PaperSize = "A4_Landscape";
include('includes/PDFStarter.php');
include('includes/SQL_CommonFunctions.inc');
    //$title = _('Print Send/Receive document');
   //include('includes/header.inc');
if(isset($_GET['ModifyOrderNumber'])){
   $_SESSION['CutModifyOrderNumber'] = $_GET['ModifyOrderNumber'];
}

//$_SESSION['myArray']  = array();
if (isset($_POST['Print']) )
{
    $CuttingSlipNo = "";
    //Check to see if we have already put cutslip number....
    $LineSQL = "SELECT salesorderdetails.orderlineno,
									salesorderdetails.stkcode,
									salesorderdetails.cutslipno
									FROM salesorderdetails INNER JOIN stockmaster
									ON salesorderdetails.stkcode = stockmaster.stockid
									INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
									WHERE   salesorderdetails.orderno ='" .    $_SESSION['CutModifyOrderNumber']. "'
									ORDER BY salesorderdetails.orderlineno";
    $LineResults = db_query($LineSQL, $db, $ErrMsg);


       $myrow  = DB_fetch_array($LineResults);
    if( $myrow['cutslipno'] !="")
    {
        $CuttingSlipNo = $myrow['cutslipno'];

    }else{

        $TypeNoField = "typeno" . $_SESSION['UserStockLocation'];
        $NextCuttingSlipNo = GetNextTransNoTenderType(53, $db, $TypeNoField);
        $CuttingSlipNo = $_SESSION['UserStockLocation'] . $NextCuttingSlipNo;
    }
$YPos =+20;
$FontSize=12;
$PageNumber=1;
include ('includes/PDFCuttingSlipHeader.inc');
$line_height=10;
$FontSize=8;
$countlines = 0;
$TotalUnits = 0;
$Totalweight = 0;


    //$key = $_POST['RowSelect'];
    //$description = $_POST['description'];
    $countCheck = count($_POST['RowSelect']);
    //$StockOrder = $_POST['OrderNo'];
   // $packdsc = $_POST['packdescript'];
    //$itmQty = $_POST['itemqty'];
    //print_r(myArray);
   for ($i = 0; $i < $countCheck; $i++) {
   // foreach ($_SESSION['myArray'] as $key => $value) {
        $CutSizes = "";
    //$CutSizes = $packdsc[$i];
        //$_SESSION['myArray'][$key]['qty']
	$LeftOvers = $pdf->addTextWrap($Left_Margin+2,$YPos,100,$FontSize,$_SESSION['myArray'][$_POST['RowSelect'][$i]]['stockid'], 'left');
	$LeftOvers = $pdf->addTextWrap(145,$YPos,150,$FontSize-2,$_SESSION['myArray'][$_POST['RowSelect'][$i]]['description'] , 'left');
	$LeftOvers = $pdf->addTextWrap(305,$YPos,60,$FontSize,$_SESSION['myArray'][$_POST['RowSelect'][$i]]['OrderNo'], 'right');
	$LeftOvers = $pdf->addTextWrap(370,$YPos,50,$FontSize,$_SESSION['myArray'][$_POST['RowSelect'][$i]]['itemqty'], 'right');
	//$LeftOvers = $pdf->addTextWrap(385,$YPos,50,$FontSize,'', 'right');
	//$LeftOvers = $pdf->addTextWrap(435,$YPos,50,$FontSize,'', 'right');
    $LeftOvers = $pdf->addTextWrap(385,$YPos,150,$FontSize,$_SESSION['myArray'][$_POST['RowSelect'][$i]]['packdescript'], 'right');
	//$LeftOvers = $pdf->addTextWrap(495,$YPos,150,$FontSize, $CutSizes, 'right');
	$LeftOvers = $pdf->addTextWrap(570,$YPos,150,$FontSize,'', 'left');
	//$LeftOvers = $pdf->addTextWrap(550,$YPos,60,$FontSize,'Lines ' . $countlines, 'right');

    /*Update cutting slip number...*/
    $SQL = "UPDATE salesorderdetails SET cutslipno ='".$CuttingSlipNo."' WHERE orderno = '".$_SESSION['myArray'][$_POST['RowSelect'][$i]]['OrderNo']."'";
    $runResults = db_query($SQL , $db, $ErrMsg);
	$pdf->line($Left_Margin, $YPos-1,$Page_Width-$Right_Margin, $YPos-1);
	//Draw lines between items
	//Start line left
	$pdf->line($Left_Margin, $YPos+20,$Left_Margin, $YPos-1);
	//Line before description
	$pdf->line($Left_Margin+100, $YPos+20,$Left_Margin+100, $YPos-1);
	//Line before order
	$pdf->line($Left_Margin+265, $YPos+20,$Left_Margin+265, $YPos-1);
	//Line before color
	$pdf->line($Left_Margin+340, $YPos+20,$Left_Margin+340, $YPos-1);
	//Line before Required
	$pdf->line($Left_Margin+420, $YPos+20,$Left_Margin+420, $YPos-1);
	//Line before lengthUsed
    $pdf->line($Left_Margin+560, $YPos+20,$Left_Margin+560, $YPos-1);//600
	//$pdf->line($Left_Margin+510, $YPos+20,$Left_Margin+510, $YPos-1);
	//Line before Offcuts
	$pdf->line($Left_Margin+650, $YPos+20,$Left_Margin+650, $YPos-1);//680
	//Line after weight
	//$pdf->line($Left_Margin+650, $YPos+20,$Left_Margin+650, $YPos-1);

	//End line right
	$pdf->line($Left_Margin+772, $YPos+20,$Left_Margin+772, $YPos-1);


	$YPos -= $line_height;
	++$countlines;
	$TotalUnits = $TotalUnits + $TransferRow['total'];
	$Totalweight = $Totalweight + $TransferRow['weight'];

	if ($YPos < $Bottom_Margin + $line_height) {
		$PageNumber++;
		include ('includes/PDFCuttingSlipHeader.inc');
	}
	if ($countlines == $TotalRecords) {
		//Start line left
		$pdf->line($Left_Margin, $YPos+10,$Left_Margin, $YPos-1);
		//Line before total
		$pdf->line($Left_Margin+400, $YPos+10,$Left_Margin+400, $YPos-1);
		//Line before weight
		$pdf->line($Left_Margin+450, $YPos+10,$Left_Margin+450, $YPos-1);
		//Line after weight
		$pdf->line($Left_Margin+525, $YPos+10,$Left_Margin+525, $YPos-1);
		//End line right
		$pdf->line($Left_Margin+772, $YPos+10,$Left_Margin+772, $YPos-1);
		//Bottom line
		$pdf->line($Left_Margin, $YPos-1,$Page_Width-$Right_Margin, $YPos-1);
		

	}

} 

$pdfcode = $pdf->output();
$len = strlen($pdfcode);


if ($len<=20){
	include('includes/header.inc');
	echo '<p>';
	prnMsg( _('There was no stock location transfer to print out'), 'warn');
	echo '<br><a href="' . $rootpath. '/index.php?' . SID . '">'. _('Back to the menu'). '</a>';
	include('includes/footer.inc');
	exit;
} else {
	header('Content-type: application/pdf');
	header('Content-Length: ' . $len);
	header('Content-Disposition: inline; filename=StockSent.pdf');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$pdf->Stream();
}

unset($_SESSION['myArray']);
            
        }
else{
/************************************-FORM-****************************************************************/
        $title = _('Cutting-Slip');
        include('includes/header.inc');
        /*if FromTransNo is not set then show a form to allow input of either a single invoice number or a range of invoices to be printed. Also get the last invoice number created to show the user where the current range is up to */
        echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST' name='cuttingslips'><table class='table1'>";
        echo '<div class="centre"><p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt="">' . ' ' . _('Cutting - Slip ') . '</div>';

        /*********************************************************************************************/
         $identifier =   $_SESSION['CutModifyOrderNumber'];
         $_SESSION['CutModifyOrderNumber'] = $_GET['ModifyOrderNumber'];

         $valuechecked  = "all";
         $OrderHeaderSQL = 'SELECT  salesorders.debtorno,
								debtorsmaster.name,
								salesorders.branchcode,
								salesorders.customerref,
                                                                salesorders.custContact,
								salesorders.comments,
								salesorders.orddate,
								salesorders.ordertype,
								salestypes.sales_type,
								salesorders.shipvia,
								salesorders.deliverto,
								salesorders.deladd1,
								salesorders.deladd2,
								salesorders.deladd3,
								salesorders.deladd4,
								salesorders.deladd5,
								salesorders.deladd6,
								salesorders.contactphone,
								salesorders.contactemail,
								salesorders.freightcost,
								salesorders.deliverydate,
								debtorsmaster.currcode,
								paymentterms.terms,
								salesorders.fromstkloc,
								salesorders.printedpackingslip,
								salesorders.datepackingslipprinted,
								salesorders.quotation,
								salesorders.deliverblind,
								debtorsmaster.customerpoline,
								locations.locationname,
								custbranch.estdeliverydays,
								custbranch.salesman
							FROM salesorders,
								debtorsmaster,
								salestypes,
								custbranch,
								paymentterms,
								locations
							WHERE salesorders.ordertype=salestypes.typeabbrev
							AND salesorders.debtorno = debtorsmaster.debtorno
							AND salesorders.debtorno = custbranch.debtorno
							AND salesorders.branchcode = custbranch.branchcode
							AND debtorsmaster.paymentterms=paymentterms.termsindicator
							AND locations.loccode=salesorders.fromstkloc
							AND salesorders.orderno = "' . $_GET['ModifyOrderNumber'] . '"';
         
        $GetOrdHdrResult = DB_query($OrderHeaderSQL, $db, $ErrMsg);
        $myrow = DB_fetch_array($GetOrdHdrResult);
        /*******************************************************************************************/
         echo '<input  type=hidden name="CustomerOrder" value= "'.$myrow['customerref'].'"/>';
         echo '<input  type=hidden name="CustomerCode" value= "'.$myrow['debtorno']." - ".$myrow['branchcode'].'"/>';
         echo '<input  type=hidden name="DeliveryDate" value= "'.$myrow['deliverydate'].'"/>';
        echo '<input  type=hidden name="ToDeliverTo" value= "'.$myrow['deladd1'].", ".$myrow['deladd2'].'"/>';
         $LineItemsSQL = "SELECT salesorderdetails.orderlineno,
									salesorderdetails.stkcode,
									salesorderdetails.cutslipno,
									stockmaster.description,
									stockmaster.volume,
									stockmaster.kgs,
									stockmaster.units,
									stockmaster.serialised,
									stockmaster.nextserialno,
									stockmaster.eoq,
                                     salesorderdetails.orderno,
									salesorderdetails.unitprice,
									salesorderdetails.quantity,
									salesorderdetails.discountpercent,
									salesorderdetails.actualdispatchdate,
									salesorderdetails.qtyinvoiced,
									salesorderdetails.narrative,
									salesorderdetails.itemdue,
									salesorderdetails.poline,
									salesorderdetails.packdesc,
									locstock.quantity as qohatloc,
									stockmaster.mbflag,
									stockmaster.discountcategory,
									stockmaster.decimalplaces,
									stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
									salesorderdetails.completed
									FROM salesorderdetails INNER JOIN stockmaster
									ON salesorderdetails.stkcode = stockmaster.stockid
                                                                      
									INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
									WHERE   salesorderdetails.orderno ='" . $_GET['ModifyOrderNumber'] . "'
                                                                        AND locstock.loccode = '" . $myrow['fromstkloc'] . "'
									ORDER BY salesorderdetails.orderlineno";

        $ErrMsg = _('The line items of the order cannot be retrieved because');
        $LineItemsResult = db_query($LineItemsSQL, $db, $ErrMsg);


        echo '<table>';
        $tableheader = '<tr>
                                        <th>' . _("Stock ID") . '</th>
                                        <th>' . _("Discription") . '</th>
                                        <th>' . _("Unit") . '</th>
                                        <th>' . _("Quantity") . '</th>


                                        <th><input type="checkbox"  class="chk_boxes" value="Check All"/></th></tr> ';




// onChange="ReloadForm(cuttingslips.checkallc)"

        echo $tableheader;

        $FormatedTotalOrder = 0;
        $FormatedTotalAcc = 0;
//<th>" . _('Show On Cutting Slip') . "</th>
        $j = 1;
        $k = 0; //row colour counter

    $_SESSION['myArray'] = array();
        while ($myrow = DB_fetch_array($LineItemsResult)) {


            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k++;
            }


                                      echo "<td>".$myrow['stkcode']."</td>
                                        <td>".$myrow['description']."</td>
                                        <td>".$myrow['quantity']."</td>
                                        <td>".$myrow['units']."</td>

                                        <td><input type='checkbox' id='checkbox[]' class='chk_boxes1' name='RowSelect[]'  value=".$myrow['orderlineno']."   ></td></tr>";
                                        $_SESSION['myArray'][$myrow['orderlineno']]['stockid'] = $myrow['stkcode'];
                                        $_SESSION['myArray'][$myrow['orderlineno']]['description'] = $myrow['description'];
                                        $_SESSION['myArray'][$myrow['orderlineno']]['itemqty'] = $myrow['quantity'];
                                        $_SESSION['myArray'][$myrow['orderlineno']]['packdescript'] = $myrow['packdesc'];
                                        $_SESSION['myArray'][$myrow['orderlineno']]['cutslipno'] = $myrow['cutslipno'];
                                        $_SESSION['myArray'][$myrow['orderlineno']]['OrderNo'] = $myrow['orderno'];



            $j++;
        }
    //$_SESSION['myArray'][$OrderLine->StockID]['qty'] += $OrderLine->Quantity ;
        //  <td><input type='checkbox' id='checkbox2[]' name='OrderNo[]' value='".$myrow['orderno']."' $RowSelect style='display:none;' ></td>";
        //end of while loop
      
        echo '</table>';
        /*****************************************************************************************/
       echo "<div class='centre'><input type=Submit Name='Print' Value='" . _('Print PDF') . "'></div>";

    //echo '<input type="submit" name="checkallc" style="display: none;">';
    echo "</form>";
 //include ('includes/footer.inc');
}

 include ('includes/footer.inc');

?>
<script type="text/javascript">

    $(document).ready(function(){
      //  alert("hellooooo123");
        var checked = false;
        $('.chk_boxes').click(function() {
            $('.chk_boxes1').attr('checked', true);
           // if (checked) $(this).text('Uncheck All');
           // else $(this).text('Check All);
        });

    });

</script>

