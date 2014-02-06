<?php

/* $Revision: 1.00 CMJ $ */

//// $PageSecurity = 24;
//include('includes/session.inc');
//include('includes/SQL_CommonFunctions.inc');
//$title = _('Comparison of Modules');
// include('includes/header.inc');
/* $db = mysql_connect('YOUR_DB_ADDRESS','YOUR_DB_USER','YOUR_DB_PASS') or die("Database error");
  mysql_select_db('YOUR_DB', $db);
 */

$dbt = date("Y-m-d");

// echo "<br>Complete Module Check UP <br>";

$Conn = mysql_connect("www.wazierp.net", "waziuser", "weberp98") or die("Database error");
$db = mysql_select_db("waziuser_duroplastic", $Conn);
$TotBal = $TotBal / 2;
$Datenow = date("Y-m-d");
$PeriodDD;
$today = strtotime($Datenow);


//	echo "<br>Total Debtors from Debtors Module is R " . $DebtorModule ;
//        echo "<br />Today's date ". $today ." Date now is ".$Datenow ."<br />";
/* get period */
$MonthAfterTransDate = Mktime(0, 0, 0, Date('m', $today) + 1, Date('d', $today), Date('Y', $today));
$GetPrd = "SELECT periodno FROM periods WHERE lastdate_in_period < '" .
        Date('Y/m/d', $MonthAfterTransDate) . "' AND lastdate_in_period >= '" . Date('Y/m/d', $today) . "'";
//        echo "<br>". $GetPrd;
$Result = mysql_query($GetPrd);
if (!$Result) {
    echo "error in retrieving period  results";
} else {
    $rows = mysql_fetch_array($Result);
    $PeriodDD = $rows['periodno'];
    // echo "Period is :".$rows['periodno']; 
}
/* $PeriodDD = GetPeriod($Datenow, $db) ;
  echo "<br>Total Debtors from Debtors Module is R " . $DebtorModule ;
  echo "<br>Debtors Module vs GL as at " . $Datenow . " which is in Period : " . $PeriodDD ;
 */


$from = "crons@duroplastic.com";
$replyto = "support@duroplastic.com";
/*  Send email */
$headers = "From: " . strip_tags($from) . "\r\n";
$headers .= "Reply-To: " . strip_tags($replyto) . "\r\n";
//$headers .= "CC: dylan@duroplastic.com.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

$message = '<html>';
$message.='<head><style type="text/css">
   
    .outer {background: #FAFAFA; font-family: Arial, Helvetica, sans-serif; font-size: 12px;}
    p {margin: 0px 0 10px;margin-left:10px;}
    h2{font-weight: bold; font-size: 14px; margin-bottom: 5px;margin-left:5px;  display: block }
     .text {border-radius: 5px;font-size: 1.5em; padding: 5px;}
    </style>
                </head>';
$message .= '<h1>Sales with Low Gross Profit /Selling below Cost</h1>';
$message.= '<body>';


$message.= ' <h2>Low Gross Profit</h2>
        <p class ="text">
            <small style="color:#666">';
/*$message.= 'Debtors Module R <b><font color="green">' . $DebtorModule . '</b></font> /  GL Debtors Ledger R <b><font color="green">' . $GLDebtor . '</b></font><br />
              variance = R 
              </small><font style="color:#F00">
               ' . number_format($DiffDebtorTotal, 2, '.', ' ') . '
            </font><br /><br />';
*/
$SQL = "SELECT * FROM sendLowGPcron";
$results = mysql_query($SQL);

$myrow  = mysql_fetch_array($results);
$_POST['GPMin'] = $myrow['margin'];

$SQL = "SELECT stockmaster.categoryid,
                       stockmaster.stockid,
                       stockmoves.transno,
					   stockmoves.type,
                       stockmoves.trandate,
                       stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost as unitcost,
                       stockmoves.qty,
                       stockmoves.debtorno,
                       stockmoves.branchcode,
                       stockmoves.price*(1-stockmoves.discountpercent) as sellingprice,
                       (stockmoves.price*(1-stockmoves.discountpercent)) - (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS gp
                FROM stockmaster,
                       stockmoves
                WHERE stockmoves.type=10
                AND stockmaster.stockid=stockmoves.stockid
                AND stockmoves.trandate >= '" . $Datenow . "'
                AND stockmoves.trandate <= '" . $Datenow. "'

                AND ((stockmoves.price*(1-stockmoves.discountpercent)) - (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost))/(stockmoves.price*(1-stockmoves.discountpercent)) <=" . ($_POST['GPMin']/100) . "

                AND ((stockmoves.price*(1-stockmoves.discountpercent)) - (stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost))/(stockmoves.price*(1-stockmoves.discountpercent)) <=" . ($_POST['GPMin']/100) . "
                ORDER BY stockmoves.trandate";

$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
$Result = mysql_query($SQL);
//$ChartDetailRow = mysql_fetch_array($Result);
// --------------------
while ($LowGPItems = mysql_fetch_array($Result)) {


   /* $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,$LowGPItems['trandate']);
    $LeftOvers = $pdf->addTextWrap(100,$YPos,50,$FontSize,$LowGPItems['transno']);
    $LeftOvers = $pdf->addTextWrap(160,$YPos,100,$FontSize,$LowGPItems['stockid']);
    $LeftOvers = $pdf->addTextWrap(270,$YPos,50,$FontSize,$LowGPItems['debtorno']);
    $LeftOvers = $pdf->addTextWrap(320,$YPos,50,$FontSize,$LowGPItems['branchcode']);
   */
    $sqlsalesman = "SELECT debtortrans.id,
			   		debtortrans.trandate,
					debtortrans.ovamount,
					debtortrans.ovdiscount,
					debtortrans.ovfreight,
					debtortrans.ovgst,
					debtortrans.rate,
					debtortrans.invtext,
					debtortrans.consignment,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
					debtorsmaster.currcode,
					salesorders.deliverto,
					salesorders.deladd1,
					salesorders.deladd2,
					salesorders.deladd3,
					salesorders.deladd4,
					salesorders.deladd5,
					salesorders.deladd6,
					salesorders.customerref,
					salesorders.orderno,
					salesorders.orddate,
					shippers.shippername,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					salesman.salesmanname,
					debtortrans.debtorno,
					debtortrans.user
				FROM debtortrans,
					debtorsmaster,
					custbranch,
					salesorders,
					shippers,
					salesman
				WHERE debtortrans.order_ = salesorders.orderno
				AND debtortrans.type=10
				AND debtortrans.transno='".$LowGPItems['transno']."'
				AND debtortrans.shipvia=shippers.shipper_id
				AND debtortrans.debtorno=debtorsmaster.debtorno
				AND debtortrans.debtorno=custbranch.debtorno
				AND debtortrans.branchcode=custbranch.branchcode
				AND custbranch.salesman=salesman.salesmancode";
    $saleresults = mysql_query($sqlsalesman);

    $myrowsales  = mysql_fetch_array( $saleresults);
    $DisplayUnitCost = number_format($LowGPItems['unitcost'],2);
    $DisplaySellingPrice = number_format($LowGPItems['sellingprice'],2);
    $DisplayGP = number_format($LowGPItems['gp'],2);
    $DisplayGPPercent = number_format(($LowGPItems['gp']*100)/$LowGPItems['sellingprice'],1);
    $sql = "SELECT counterindex,
    	typeno,
   		trandate,
		account,
		amount,
		invnumber,
		type,
		user
     FROM gltrans
	WHERE typeno = '" . $LowGPItems['transno']."'";

    $results = mysql_query( $sql);

    $myrow  = mysql_fetch_array($results);
    $link = "http://www.wazierp.net/wazi/PrintCustTrans.php?FromTransNo=".$LowGPItems['transno']."&InvOrCredit=Invoice";
    $message.= '<small style="color:#666"><b>SalesMan : </b></small>' . $myrowsales['salesmanname']. '
            <br />';
    $message.= '<small style="color:#666"><b>Entry Cleck : </b><font style="color:#F00">' . $myrow ['user'] . '</font>
            Invoice Number : <a  href="'.$link.'"><font color="green">' .$LowGPItems['transno'] . '</b></font></a>
             </small>
           <br />';
   // Rep :<b><font color="green">' .$LowGPItems['debtorno'] . '</b></font>
    $message.= '<small style="color:#666">Item Code: <b><font color="green">' . $LowGPItems['stockid'] . '</font></b> Debtor <b><font color="green">' .$LowGPItems['debtorno'] . '</font></b> UnitCost <b><font color="green">' .$DisplayUnitCost. '</font></b>
               Selling Price <b><font color="green">' .$DisplaySellingPrice. '</font></b> GP <b><font color="green">' .$DisplayGP. '</font></b> GP Percentage <b><font color="green">' .$DisplayGPPercent . '%</font></b>
             </small>
           <br /><br/>';

}
$message.= '</p>';


$message .= '</body></html>';

$SQLSet = "SELECT * FROM sendLowGPcronemails";
$resulSet = mysql_query($SQLSet);

while ($myrowset = mysql_fetch_array($resulSet)) {
if (!mail($myrowset['email'], 'Low GP Cron: ' . $dbt, $message
                , $headers)) {
    echo "Mail not send";
}

}

mysql_close($Conn);
?>