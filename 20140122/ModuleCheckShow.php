<style>
    * {margin: 0; padding: 0;}
    .outer {background: #FAFAFA; font-family: Arial, Helvetica, sans-serif; font-size: 12px;}
    p {margin: 0px 0 10px;margin-left:10px;}
    label {font-weight: bold; font-size: 14px; margin-bottom: 5px;margin-left:5px;  display: block }


    .text {border-radius: 5px; -webkit-border-radius: 5px; border: 1px solid #CCC; font-size: 1.5em; padding: 5px; width: 500px}

</style>


<?php
/* $Revision: 1.00 CMJ $ */

// $PageSecurity = 24;
include('includes/session.inc');
include('includes/GLPostings.inc');
$title = _('Comparison of Modules');
include('includes/header.inc');
/* $db = mysql_connect('YOUR_DB_ADDRESS','YOUR_DB_USER','YOUR_DB_PASS') or die("Database error");
  mysql_select_db('YOUR_DB', $db);
 */

$dbt = date("Y-m-d");

//echo "<br>Complete Module Check UP <br />";
echo "<br /><br /><br />";
echo '<p class="page_title_text">Complete Module Check UP</p>';
$Conn = mysql_connect("www.wazierp.net", "waziuser", "weberp98") or die("Database error");
$db = mysql_select_db("waziuser_duroplastic", $Conn);


/*=======================================================================================================*/
/*  Debtors Module */
$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.holdreason,					
					currencies.currency,
					paymentterms.terms,
					debtorsmaster.creditlimit,
					holdreasons.dissallowinvoices,
					holdreasons.reasondescription,
					debtortrans.loccode,
			sum(debtortrans.ovamount +
				debtortrans.ovgst +
				debtortrans.ovfreight +
				debtortrans.ovdiscount -
				debtortrans.alloc) AS balance
	
		FROM debtorsmaster,
		paymentterms,
		holdreasons,
		currencies,
		debtortrans
		WHERE debtorsmaster.paymentterms = paymentterms.termsindicator
		AND debtorsmaster.currcode = currencies.currabrev
		AND debtorsmaster.debtorno = debtortrans.debtorno
		AND holdreasons.dissallowinvoices=1
				GROUP BY debtorsmaster.debtorno,
					debtorsmaster.name
						HAVING
Sum(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc) <>0";

$CustomerResult = mysql_query($SQL, $Conn);

$TotBal = 0;
while ($row = mysql_fetch_array($CustomerResult)) {
    $TotBal += $row['balance'];
}


$TotBal = $TotBal / 2;
$Datenow = date("Y-m-d");
$PeriodDD;
$today = strtotime($Datenow);
$DebtorModule = number_format($TotBal, 2, '.', ' ');

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



$sql = "SELECT bfwd,
						actual,
						period
					FROM chartdetails
					WHERE chartdetails.accountcode= '3000.000'
					AND chartdetails.period=" . $PeriodDD;

$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
$ChartDetailsResult = mysql_query($sql);
$ChartDetailRow = mysql_fetch_array($ChartDetailsResult);
// --------------------

$RunningTotal = $ChartDetailRow['bfwd'] + $ChartDetailRow['actual'];
$GLDebtor = number_format($RunningTotal, 2, '.', ' ');
//echo "<br>Total Debtors according to GL Debtors 3000.000 is R " . $GLDebtor ;
$DiffDebtorTotal = number_format($TotBal, 2, '.', '') - number_format($RunningTotal, 2, '.', '');

//echo "<br>Variance between GL and Debtors module is R " . number_format($DiffDebtorTotal,2,'.',' ') . "<br>";
$TotBal = 0;


/*=======================================================================================================*/
/*  Creditors Module 	 */

$SqlCreditors = "SELECT suppliers.supplierid,
	      		suppliers.suppname,
			currencies.currency,
			paymentterms.terms,
			SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance
			FROM suppliers,
				paymentterms,
				currencies,
				supptrans
			WHERE suppliers.paymentterms = paymentterms.termsindicator
			AND suppliers.currcode = currencies.currabrev
			and suppliers.supplierid = supptrans.supplierno
                      
			GROUP BY suppliers.supplierid,
				suppliers.suppname,
				currencies.currency,
				paymentterms.terms ";

//echo "<br>Sql line 371 is " . $SQL . "<br>";
$CreditorsResult = mysql_query($SqlCreditors);

if (!$CreditorsResult) {
    echo 'The Stock Module details could not be retrieved by the SQL because ' . mysql_error();
    exit;
}

$TotBal = 0;


While ($AgedAnalysis = mysql_fetch_array($CreditorsResult)) {

    $TotBal += $AgedAnalysis['balance'];
}
//$Datenow = date("d/m/Y");
//$PeriodDD = GetPeriod($Datenow, $db) ;
$CreditorModule = number_format($TotBal, 2, '.', ' ');
//echo "<br>Creditors Module vs GL as at " . $Datenow . " in Period : " . $PeriodDD ;
//echo "<br>Total Creditors from Creditors Module is R " . $CreditorModule ;

$SqlCreditorsCharter = "SELECT bfwd,
						actual,
						period
					FROM chartdetails
					WHERE chartdetails.accountcode= '4000.000'
					AND chartdetails.period=" . $PeriodDD;

$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
$ChartCreditorsResult = mysql_query($SqlCreditorsCharter);
$CreditorsChartDetailRow = mysql_fetch_array($ChartCreditorsResult);
// --------------------

$RunningTotal = $CreditorsChartDetailRow ['bfwd'] + $CreditorsChartDetailRow ['actual'];

$GLCreditor = number_format($RunningTotal, 2, '.', ' ');
//echo "<br>Total Debtors according to GL Debtors 3000.000 is R " . $GLCreditor ;
$DiffCreditorTotal = number_format($TotBal, 2, '.', '') + number_format($RunningTotal, 2, '.', '');



/*=======================================================================================================*/
/* Sales Analysis */
/*****************/

$SalesItemAmount = 0;
$SalesItemCosts = 0;
$PeriodDDSales = $PeriodDD;
$SqlAnalysis = "SELECT SUM(amt) AS itemsAmount,
                                       SUM(cost) AS itemCosts
			FROM salesanalysis,stockmaster
                        WHERE stockmaster.stockid = salesanalysis.stockid
                        
                        AND salesanalysis.periodno = {$PeriodDDSales}
                        GROUP BY salesanalysis.stockid";
//AND salesanalysis.stockid NOT LIKE '%VBM%'
$SalesAnalysisResult = mysql_query($SqlAnalysis);

if (!$SalesAnalysisResult) {
    echo 'The Sale Analysis  Module details could not be retrieved by the SQL because ' . mysql_error();
    exit;
}
$AnalysisRow = mysql_fetch_array($SalesAnalysisResult);
$SalesItemAmount = number_format($AnalysisRow['itemsAmount'], 2, '.', ' ');
$SalesItemCosts = number_format($AnalysisRow['itemCosts'], 2, '.', ' ');



/*=======================================================================================================*/
/*  Stock Module */


$SqlStock = "SELECT stockmaster.categoryid,
				stockcategory.categorydescription,
				stockmaster.stockid,
				stockmaster.description,
				stockmaster.units,
				locstock.quantity AS qtyonhand,
				stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost AS unitcost,
				abs(locstock.quantity) *(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS itemtotal,
                                locstock.quantity *(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS itemstotals,
                                CASE WHEN locstock.quantity >= 0.09 THEN  locstock.quantity *(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) END AS itemstotalsnoneg 
			FROM stockmaster,
				stockcategory,
				locstock,
                                locations
			WHERE stockmaster.stockid = locstock.stockid
                        AND locstock.loccode = locations.loccode 
                        AND stockmaster.materialcost <> 0
                        AND stockmaster.categoryid = stockcategory.categoryid
                        ORDER BY stockmaster.categoryid,
                            stockmaster.stockid
			";

$StockResult = mysql_query($SqlStock);

if (!$StockResult) {
    echo 'The Stock Module details could not be retrieved by the SQL because ' . mysql_error();
    exit;
}

$Tot_Val = 0;
$Items_totals = 0;
$NegItems_totals = 0;

While ($AgedAnalysisStock = mysql_fetch_array($StockResult)) {

    $Tot_Val += $AgedAnalysisStock['itemtotal'];
    $Items_totals += $AgedAnalysisStock['itemstotals'];
    $NegItems_totals  += $AgedAnalysisStock['itemstotalsnoneg'];
}


$StockModule = number_format($Tot_Val, 2, '.', ' ');




$RunningTotal = 0;
$DiffStockTotal = 0;
$actualStockvalue = 0;
$SqlStockCharter = "SELECT SUM(bfwd + actual) AS runningtotal,
                                        actual
					FROM chartdetails
					WHERE chartdetails.accountcode = '3700.000'
					AND chartdetails.period = " . $PeriodDD;

$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
$StockChartDetailsResult = mysql_query($SqlStockCharter);
$StockChartDetailRow = mysql_fetch_array($StockChartDetailsResult);
$actualStockvalue = $StockChartDetailRow['actual'];
$RunningTotal = $StockChartDetailRow['runningtotal'];
$DiffStockTotal = (double)$Tot_Val - (double)$RunningTotal;
//$DiffStockTotal = $Tot_Val - $RunningTotal;
/*=====================================================================================*/
$SqlStockLoss= "SELECT SUM(bfwd + actual) AS runningtotal
					FROM chartdetails
					WHERE chartdetails.accountcode = '1950.000'
					AND chartdetails.period = " . $PeriodDD;

$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
$StockChartlossResult = mysql_query($SqlStockLoss);
$StockChartlossRow = mysql_fetch_array($StockChartlossResult);

$RunninglossTotal = $StockChartlossRow['runningtotal'];
/*=======================================================================================================*/

$NxtRunningTotal = 0;
$NxtDiffStockTotal = 0;
$SqlNxtStockCharter = "SELECT SUM(bfwd + actual) AS runningtotal
					FROM chartdetails
					WHERE chartdetails.accountcode = '3700.000'
					AND chartdetails.period > " . $PeriodDD;

$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
$StockNxtChartDetailsResult = mysql_query($SqlNxtStockCharter);
$StockNxtChartDetailRow = mysql_fetch_array($StockNxtChartDetailsResult);

$NxtRunningTotal = $StockNxtChartDetailRow['runningtotal'];
$NxtDiffStockTotal = (double)$Tot_Val - (double)$NxtRunningTotal;
/*=======================================================================================================*/
$GRNRunningTotal = 0;
//$DiffStockTotal = 0;
$SqlGRNStockCharter = "SELECT SUM(bfwd + actual) AS runningtotal
					FROM chartdetails
					WHERE chartdetails.accountcode = '3700.GRN'
					AND chartdetails.period = " . $PeriodDD;

$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
$StockGRNChartDetailsResult = mysql_query($SqlGRNStockCharter);
$StockGRNChartDetailRow = mysql_fetch_array($StockGRNChartDetailsResult );

$GRNRunningTotal= $StockGRNChartDetailRow['runningtotal'];
//$DiffStockTotal = (double)$Tot_Val - (double)$RunningTotal;

/*=======================================================================================================*/

$SqlOverPeriod = "SELECT count(*) as OverCount FROM gltrans WHERE periodno > " . $PeriodDD;
$ErrMsg = _('The sql could not be retrieved');
$StockChartDetailsResult = mysql_query($SqlOverPeriod);
$StockChartDetailRow = mysql_fetch_array($StockChartDetailsResult);


/*=======================================================================================================*/
	$SQLgrn = "SELECT grnno,
			orderno,
			grns.supplierid,
			suppliers.suppname,
			grns.itemcode,
			grns.itemdescription,
			qtyrecd,
			quantityinv,
			grns.stdcostunit,
			actprice,
			unitprice,
			locgrnno
		FROM grns,
			purchorderdetails,
			suppliers
		WHERE grns.supplierid=suppliers.supplierid
		AND grns.podetailitem = purchorderdetails.podetailitem
		AND qtyrecd-quantityinv <>0
		ORDER BY supplierid,
			grnno";

	$GRNsResult = mysql_query($SQLgrn);



	$Tot_Val=0;
	$Supplier = '';
	
	While ($GRNs = mysql_fetch_array($GRNsResult)){

		$LineValue = ($GRNs['qtyrecd']- $GRNs['quantityinv'])*$GRNs['stdcostunit'];
		$Tot_Val += $LineValue;
		
	} /*end while loop */
        
/*=======================================================================================================*/
/*  Debtors Module check*/
//$SQLch = "SELECT debtorsmaster.debtorno,
//				debtorsmaster.name,
//				debtorsmaster.holdreason,					
//					currencies.currency,
//					paymentterms.terms,
//					debtorsmaster.creditlimit,
//					holdreasons.dissallowinvoices,
//					holdreasons.reasondescription,
//					debtortrans.loccode,
//			sum(debtortrans.ovamount +
//				debtortrans.ovgst +
//				debtortrans.ovfreight +
//				debtortrans.ovdiscount -
//				debtortrans.alloc) AS balance
//	
//		FROM debtorsmaster,
//		paymentterms,
//		holdreasons,
//		currencies,
//		debtortrans
//		WHERE debtorsmaster.paymentterms = paymentterms.termsindicator
//		AND debtorsmaster.currcode = currencies.currabrev
//		AND debtorsmaster.debtorno = debtortrans.debtorno
//		AND holdreasons.dissallowinvoices=1
//                AND  debtortrans.prd  = ".$PeriodDD."
//				GROUP BY debtorsmaster.debtorno,
//					debtorsmaster.name
//						HAVING
//Sum(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount - debtortrans.alloc) <> 0";
//
//$CustomerResultchck = mysql_query($SQLch);
//
//$ChecTotBal = 0;
//while ($row = mysql_fetch_array($CustomerResultchck)) {
//    $ChecTotBal  += $row['balance'];
//}
$SqlCreditorssetled = "SELECT SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance
			FROM suppliers,
				paymentterms,
				currencies,
				supptrans
			WHERE suppliers.paymentterms = paymentterms.termsindicator
			AND suppliers.currcode = currencies.currabrev
			and suppliers.supplierid = supptrans.supplierno
                        and supptrans.settled = 0
			GROUP BY suppliers.supplierid,
				suppliers.suppname,
				currencies.currency,
				paymentterms.terms ";

//echo "<br>Sql line 371 is " . $SQL . "<br>";
$ChecsetTotBal  = 0;
$CreditorssetResult = mysql_query($SqlCreditorssetled);
while($rowset = mysql_fetch_array($CreditorssetResult)){
 $ChecsetTotBal  += $rowset['balance'];
}
//$SqlCreditorOut = "SELECT SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance
//			FROM suppliers,
//				paymentterms,
//				currencies,
//				supptrans
//			WHERE suppliers.paymentterms = paymentterms.termsindicator
//			AND suppliers.currcode = currencies.currabrev
//			and suppliers.supplierid = supptrans.supplierno
//                        and supptrans.type = 20
//			GROUP BY suppliers.supplierid,
//				suppliers.suppname,
//				currencies.currency,
//				paymentterms.terms ";
//
////echo "<br>Sql line 371 is " . $SQL . "<br>";
//$ChecoutTotBal  = 0;
//$CreditorsoutResult = mysql_query($SqlCreditorOut );
//while($rowset = mysql_fetch_array($CreditorsoutResult)){
// $ChecoutTotBal  += $rowset['balance'];
//}

$SqlCreditorDeb = "SELECT SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance
			FROM suppliers,
				paymentterms,
				currencies,
				supptrans
			WHERE suppliers.paymentterms = paymentterms.termsindicator
			AND suppliers.currcode = currencies.currabrev
			and suppliers.supplierid = supptrans.supplierno
                        and supptrans.type = 22
			GROUP BY suppliers.supplierid,
				suppliers.suppname,
				currencies.currency,
				paymentterms.terms ";

//echo "<br>Sql line 371 is " . $SQL . "<br>";
$ChecdebTotBal  = 0;
$CreditorsdebResult = mysql_query($SqlCreditorDeb);
while($rowset = mysql_fetch_array($CreditorsdebResult)){
 $ChecdebTotBal  += $rowset['balance'];
}
$SQLch = "SELECT sum(amount) AS balance from gltrans 
                             WHERE account = '3700.IMP'
                            ";
$CustomerResultchck = mysql_query($SQLch);
$row = mysql_fetch_array($CustomerResultchck);
$ChecTotBal  += $row['balance'];

$SQLs = "SELECT typeno from gltrans 
                             WHERE account = '3700.IMP'
                            ";
$CustomerResults = mysql_query($SQLs);
$ChecTaxBal = 0;
while ($row = mysql_fetch_array($CustomerResults))
{
     $SQLch = "SELECT amount from gltrans 
                             WHERE account = '4300.000'
                             AND typeno = '".$row['typeno']."'";
       $CustomercResults = mysql_query($SQLch);
       $rowc = mysql_fetch_array($CustomercResults);
       $ChecTaxBal  += $rowc['amount'];
}
$SQLgrn = "SELECT typeno from gltrans 
                             WHERE account = '3700.GRN'
                             AND type = 25
                             AND posted = 1
                             AND periodno = ".$PeriodDD."
                           
                            ";
$CustomerGrnResults = mysql_query($SQLgrn);
$ChckGRNTaxBal = 0;
while ($row = mysql_fetch_array($CustomerGrnResults))
{
     $SQLch = "SELECT amount from gltrans 
                             WHERE account = '3700.000'
                             AND typeno = '".$row['typeno']."'";
       $CustomercResults = mysql_query($SQLch);
       $rowc = mysql_fetch_array($CustomercResults);
       $ChckGRNTaxBal  += $rowc['amount'];
}
/*=======================================================================================================*/

//42 = bom manufacturing
//10 = invoices
//11 = credit notes
//20 = Purchase invoice
//AND type IN (10,11)
$SqlMStock = "SELECT   abs(stockmoves.qty) *(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS itemstotals
			FROM stockmaster,
				stockcategory,
				stockmoves
                              
			WHERE stockmaster.stockid = stockmoves.stockid
                       
                        AND stockmaster.materialcost <> 0
                        AND stockmaster.categoryid = stockcategory.categoryid
                        
                        AND prd = ".$PeriodDD."
                        ORDER BY stockmaster.categoryid,
                            stockmaster.stockid
			";

$StockMResult = mysql_query($SqlMStock);

if (!$StockMResult) {
    echo 'The Stock Module details could not be retrieved by the SQL because ' . mysql_error();
    exit;
}

$MTot_Val = 0;
$MItems_totals = 0;


While ($AgedAnalysisStock = mysql_fetch_array($StockMResult)) {

    //$Tot_Val += $AgedAnalysisStock['itemtotal'];
    $MItems_totals += $AgedAnalysisStock['itemstotals'];
}
/*=======================================================================================================*/
//echo "Invoice   is R <b><font color='green'>" . number_format($ChecoutTotBal, 2, '.', ' '); 

/*=======================================================================================================*/
$stockcontroltotal = 0;
$actualcontrl = 0;
//$DiffStockTotal = 0;
$SqlCrlStockCharter = "SELECT SUM(bfwd + actual) AS runningtotal,
                                        actual
					FROM chartdetails
					WHERE chartdetails.accountcode = '1500.000'
					AND chartdetails.period = " . $PeriodDD;

$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
$StockCtrlDetailsResult = mysql_query($SqlCrlStockCharter);
$StockCrlDetailRow = mysql_fetch_array($StockCtrlDetailsResult);

$stockcontroltotal = $StockCrlDetailRow['runningtotal'];
$actualcontrl = $StockCrlDetailRow['actual'];
//$DiffStockTotal = (double)$Tot_Val - (double)$RunningTotal;

/*=======================================================================================================*/
?>
<div class="outer">
    <div style="width: 620px; margin: 40px auto; background: #F0F0F0; border: 1px solid #CCC; border-radius: 5px; -webkit-border-radius: 5px; box-shadow: 5px 5px 5px #888888">
        <label>DEBTORS/GL MODULE:</label> 
        <p class ="text">
            <small style="color:#666">
                <?php echo "Debtors Module vs GL as at " . $Datenow . " in Period : " . $PeriodDD; ?> <br />
                <?php echo "Total Debtors from Debtors Module is R  <b><font color='green'>" . $DebtorModule; ?> </font></b> <br />
                <?php echo "Total Debtors according to GL Debtors 3000.000 is R  <b><font color='green'>" . $GLDebtor; ?></font></b> <br />
                
                <?php echo "Variance between GL and Debtors module is "; ?>
            </small>
            <font style="color:#F00">
            R <?php echo number_format($DiffDebtorTotal, 2, '.', ' '); ?>
            </font>
        </p>
        <label>CREDITORS Module vs GL:</label> 
        <p class ="text">
            <small style="color:#666">
                <?php echo "Creditors Module vs GL as at " . $Datenow . " in Period : " . $PeriodDD; ?> <br />
                <?php echo "Total Creditors from Creditors Module is R <b> <font color='green'>" . $CreditorModule; ?> </font></b> <br />
                <?php echo "Total Creditors according to GL Debtors 4000.000 is R <b><font color='green'>" . $GLCreditor; ?></font></b> <br />
                <?php echo "Total outstanding GRNs is R <b><font color='green'>" . number_format($Tot_Val, 2, '.', ' '); ?></font></b> <br />
                <?php echo "Total according to gltrans(3700.IMP) is R <b><font color='green'>" . number_format($ChecTotBal, 2, '.', ' '); ?></font></b> <br />
                <?php echo "Unsettled  is R <b><font color='green'>" . number_format($ChecsetTotBal, 2, '.', ' '); ?></font></b> <br />
                
                <?php echo "Tax  is R <b><font color='green'>" . number_format($ChecTaxBal, 2, '.', ' '); ?></font></b> <br />
                <?php echo "Variance between GL and Creditors module is "; ?>
            </small>
            <font style="color:#F00">
            R <?php echo number_format($DiffCreditorTotal, 2, '.', ','); ?>
            </font>
        </p>
        <label>Sales Analysis Module:</label> 
        <p class ="text">
            <small style="color:#666">
                <?php echo "Stock Module vs GL as at " . $Datenow . " in Period : " . $PeriodDD; ?> <br />
                <?php echo "****Total Sales Analysis amount Module is <b><font color='green'>R " . $SalesItemAmount; ?></font></b> <br />
                <?php echo "****Total Sales Analysis Costs  Module is <b><font color='green'>R " . $SalesItemCosts; ?></font></b> <br />
                <?php echo "****Total Stockmoves Costs  Module is <b><font color='green'>R " . number_format($MItems_totals, 2, '.', ' '); ?></font></b> <br />

            </small>

        </p>
        
        <label>Stock Module:</label> 
        <p class ="text">

            <small style="color:#666">
                <?php echo "Stock Module vs GL as at " . $Datenow . " in Period : " . $PeriodDD; ?> <br />
                <?php echo "Total Stock from Stock Module with - is R  <b><font color='green'>" .number_format($Items_totals, 2, '.', ' '); ?></font></b>  <br />
               <?php echo "Total Stock from Stock Module with + is R  <b><font color='green'>".number_format($NegItems_totals, 2, '.', ' '); ?></font></b><br /><br />
                <?php echo "Total Stock from Stock Module is R  <b><font color='green'>" . $StockModule; ?></font></b>  <br />
            
                <?php echo "Total Stock according to GL Stock 3700.000 is R  <b><font color='green'>" . number_format($RunningTotal, 2, '.', ' ') ?></font></b> <br />
                 <?php echo "Actual  37000.000 stock value is R  <b><font color='green'>" . number_format($actualStockvalue, 2, '.', ' ') ?></font></b> <br />
                <?php echo "Total Stock according to  1500.000 is R  <b><font color='green'>" . number_format($stockcontroltotal, 2, '.', ' ') ?></font></b> <br />
                 <?php echo "Actual 1500.000 is R  <b><font color='green'>" . number_format($actualcontrl , 2, '.', ' ') ?></font></b> <br />
                 <?php echo "Total Stock according to Stock Adjustments 1950.000 is R  <b><font color='green'>" . number_format($RunninglossTotal, 2, '.', ' ') ?></font></b> <br />
                 <?php echo "Total Stock according to GL 3700.GRN(chartdetail) is R  <b><font color='green'>" . number_format($GRNRunningTotal, 2, '.', ' ') ?></font></b> <br />
                 <?php echo "GL 3700.GRN AND 3700.000(gltrans) is R  <b><font color='green'>" . number_format($ChckGRNTaxBal, 2, '.', ' ') ?></font></b> <br />
                 <?php echo "BFW nxt period <b><font color='green'>" . number_format($NxtRunningTotal, 2, '.', ' ') ?></font></b> <br />
                <?php echo "Variance between GL and Stock module is"; ?>
            </small>
            <font style="color:#F00">
            R <?php echo number_format($DiffStockTotal,2,'.',','); ?>
            </font>
             <font style="color:#F00"><br/>
            R <?php echo number_format(($Items_totals-$RunningTotal),2,'.',','); ?>
            </font>

        </p>
        <label>GL entries allocated into future Periods :</label> 
        <p class ="text">
                <small style="color:#666">
                         Total number of GL entries allocated into future Periods
                </small>
                   =  
                   <font color='green'>
                            <?php echo $StockChartDetailRow['OverCount']; ?>
                  </font>
        </p>

    </div>
</div>
<?php
include('includes/footer.inc');
mysql_close($Conn);
?>