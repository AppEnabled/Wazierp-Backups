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

//echo "<br>Sql line 371 is " . $SQL . "<br>";
/* Make a MySQL Connection
  $query = "SELECT * FROM example";

  $result = mysql_query($query) or die(mysql_error());


  while($row = mysql_fetch_array($result)){
  echo $row['name']. " - ". $row['age'];
  echo "<br />";
  }
 */

$CustomerResult = mysql_query($SQL, $Conn);

$TotBal = 0;
while ($row = mysql_fetch_array($CustomerResult)) {
    $TotBal += $row['balance'];
}

/* 	While ($AgedAnalysis = mysql_fetch_array($CustomerResult)){
  $TotBal += $AgedAnalysis['balance'];
  }
 */
$TotBal = $TotBal / 2;
$Datenow = date("Y-m-d");
$PeriodDD;
$today = strtotime($Datenow);
$DebtorModule = number_format($TotBal, 2, '.', '');

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
//	echo "<br>Total Debtors according to GL Debtors 3000.000 is R " . $GLDebtor ;
$DiffDebtorTotal = number_format($TotBal, 2, '.', '') - number_format($RunningTotal, 2, '.', '');

//	echo "<br>Variance between GL and Debtors module is R " . number_format($DiffDebtorTotal,2,'.','') . "<br>";
$TotBal = 0;

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
$UnformatedCreditorModule  = 0;

While ($AgedAnalysis = mysql_fetch_array($CreditorsResult)) {

    $TotBal += $AgedAnalysis['balance'];
}
//$Datenow = date("d/m/Y");
//$PeriodDD = GetPeriod($Datenow, $db) ;
$UnformatedCreditorModule = $TotBal;
$CreditorModule = number_format($TotBal, 2, '.', ' ');
//	echo "<br>Creditors Module vs GL as at " . $Datenow . " in Period : " . $PeriodDD ;
//	echo "<br>Total Creditors from Creditors Module is R " . $CreditorModule ;

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
//	echo "<br>Total Debtors according to GL Debtors 3000.000 is R " . $GLCreditor ;
$DiffCreditorTotal = number_format($TotBal, 2, '.', '') + number_format($RunningTotal, 2, '.', '');

//	echo "<br>Variance between GL and Creditors module is R " . number_format($DiffCreditorTotal,2,'.','') . "<br>";

/*  Stock Module */


$SqlStock = "SELECT stockmaster.categoryid,
				stockcategory.categorydescription,
				stockmaster.stockid,
				stockmaster.description,
				stockmaster.units,
				locstock.quantity AS qtyonhand,
				stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost AS unitcost,
				locstock.quantity *(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS itemtotal
			FROM stockmaster,
				stockcategory,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.categoryid=stockcategory.categoryid
			ORDER BY stockmaster.categoryid,
				stockmaster.stockid";

//echo "<br>Sql line 371 is " . $SQL . "<br>";

$StockResult = mysql_query($SqlStock);

if (!$StockResult) {
    echo 'The Stock Module details could not be retrieved by the SQL because ' . mysql_error();
    exit;
}

$Tot_Val = 0;

$StockUnformated = 0;
While ($AgedAnalysis = mysql_fetch_array($StockResult)) {

    $Tot_Val += $AgedAnalysis['itemtotal'];
}

//$Datenow = date("d/m/Y");
//$PeriodDD = GetPeriod($Datenow, $db) ;
$StockUnformated = $Tot_Val;
$StockModule = number_format($Tot_Val, 2, '.', ' ');
//	echo "<br>Stock Module vs GL as at " . $Datenow . " in Period : " . $PeriodDD ;
//	echo "<br>Total Stock from Stock Module is R " . $StockModule  ;

$RunningTotal = 0;
$SqlStockCharter = "SELECT bfwd,
						actual,
						period
					FROM chartdetails
					WHERE chartdetails.accountcode= '3700.000'
					AND chartdetails.period=" . $PeriodDD;

$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
$StockChartDetailsResult = mysql_query($SqlStockCharter);
$StockChartDetailRow = mysql_fetch_array($StockChartDetailsResult);
// --------------------

$RunningTotal = $StockChartDetailRow['bfwd'] + $StockChartDetailRow['actual'];

$GLStock = number_format($RunningTotal, 2, '.', ' ');
//	echo "<br>Total Stock according to GL Stock 3700.000 is R " . number_format($RunningTotal,2,'.','') ;

$DiffStockTotal = number_format($Tot_Val, 2, '.', '') - number_format($RunningTotal, 2, '.', '');

//	echo "<br>Variance between GL and Stock module is R " . number_format($DiffStockTotal,2,'.','') . "<br>";
/* =============================================================================================================== */


$GRNRunningTotal = 0;
//$DiffStockTotal = 0;
$SqlGRNStockCharter = "SELECT SUM(bfwd + actual) AS runningtotal
					FROM chartdetails
					WHERE chartdetails.accountcode = '3700.GRN'
					AND chartdetails.period = " . $PeriodDD;

$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
$StockGRNChartDetailsResult = mysql_query($SqlGRNStockCharter);
$StockGRNChartDetailRow = mysql_fetch_array($StockGRNChartDetailsResult);

$GRNRunningTotal = $StockGRNChartDetailRow['runningtotal'];
/* ================================================================================================================ */

$SQLgrn = "SELECT typeno from gltrans 
                             WHERE account = '3700.GRN'
                             AND type = 25
                             AND posted = 1
                             AND periodno = " . $PeriodDD . "
                           
                            ";
$CustomerGrnResults = mysql_query($SQLgrn);
$ChckGRNTaxBal = 0;
while ($row = mysql_fetch_array($CustomerGrnResults)) {
    $SQLch = "SELECT amount from gltrans 
                             WHERE account = '3700.000'
                             AND typeno = '" . $row['typeno'] . "'";
    $CustomercResults = mysql_query($SQLch);
    $rowc = mysql_fetch_array($CustomercResults);
    $ChckGRNTaxBal += $rowc['amount'];
}
/* ================================================================================================================ */
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

/* ================================================================================================================ */
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
$message .= '<h1>Chartdetails Analysis</h1>';
$message.= '<body>';


$message.= ' <h2>Analysis:</h2> 
        <p class ="text">
            <small style="color:#666">';
$message.= 'Debtors Module R <b><font color="green">' . $DebtorModule . '</b></font> /  GL Debtors Ledger R <b><font color="green">' . $GLDebtor . '</b></font><br />
              variance = R 
              </small><font style="color:#F00">
               ' . number_format($DiffDebtorTotal, 2, '.', ' ') . '
            </font><br /><br />';
$message.= '<small style="color:#666">Creditors Module R <b><font color="green">' . $CreditorModule . '</b></font> / GL Creditors Ledger R <b><font color="green">' . $GLCreditor . '</b></font><br />
            Creditor Module Difference R
             </small><font style="color:#F00">
               ' . number_format($DiffCreditorTotal, 2, '.', ' ') . '
            </font><br /><br />';

$message.= '<small style="color:#666">Stock Module R <b><font color="green">' . $StockModule . '</b></font> / GL Stock Ledger R <b><font color="green">' . $GLStock . '</b></font><br />
               Stock Module Difference R
                </small><font style="color:#F00">
               ' . number_format($DiffStockTotal, 2, '.', ' ') . '
            </font><br /><br />';

$message.= '<small style="color:#666"> Stock in system , however awaiting supplier invoices GL 3700.GRN
R <b><font color="green">' . number_format($GRNRunningTotal, 2, '.', ' ') . '</b></font>
             </small>
           <br />';
		   
		   

/*CPT (R11111) + GRG(33333) + KZN(100) +    +  = Bank Rxxxx*/
$SQL = "SELECT SUM(bfwd+actual) AS balance
				FROM chartdetails WHERE period=" . $PeriodDD . " AND accountcode IN ('3500.000','3500.F3S','3500.GRG','3500.KZN','3500.GTN','3500.PAR')";
//echo "<br>Period sql is " . $SQL . "<br>";
	//$ErrMsg = _('The bank account balance could not be returned by the SQL because');
	$BalanceResult = mysql_query($SQL);
	//echo "<br>Balance sql is " . $SQL . "<br>";
	$myrow =  mysql_fetch_row($BalanceResult);
	$Balance = $myrow[0];
	echo $Balance .'<br />';

	
	$SQL = "SELECT amount/exrate AS amt,
					amountcleared,
					(amount/exrate)-amountcleared as outstanding,
					ref,
					transdate,
					systypes.typename,
					transno
				FROM banktrans,
					systypes
				WHERE banktrans.type = systypes.typeid
				AND banktrans.bankact IN ('3500.000','3500.F3S','3500.GRG','3500.KZN','3500.GTN','3500.PAR')
				AND banktrans.transdate<='" . $Datenow ."'
				AND amount < 0
				AND ABS((amount/exrate)-amountcleared)>0.009 ORDER BY transdate";
				
				echo $SQL."<br />";
    $UPChequesResult = mysql_query($SQL);
    $TotalUnpresentedCheques =0;
	while ($myrow=mysql_fetch_array($UPChequesResult)) {
	
		$TotalUnpresentedCheques +=$myrow['outstanding'];
	}			
	echo $TotalUnpresentedCheques . "<br />";

	$SQL = "SELECT amount/exrate AS amt,
				amountcleared,
				(amount/exrate)-amountcleared as outstanding,
				ref,
				transdate,
				systypes.typename,
				transno
			FROM banktrans,
				systypes
			WHERE banktrans.type = systypes.typeid
			AND banktrans.bankact IN ('3500.000','3500.F3S','3500.GRG','3500.KZN','3500.GTN','3500.PAR')
			AND banktrans.transdate<='" . $Datenow . "'
			AND amount > 0
			AND ABS((amount/exrate)-amountcleared)>0.009 ORDER BY transdate";
			echo $SQL."<br />";
	//echo "<br>Exate sql is " . $SQL . "<br>";
	echo '<tr></tr>'; /*Bang in a blank line */

	//$ErrMsg = _('The uncleared deposits could not be retrieved by the SQL because');

	$UPChequesResult = mysql_query($SQL);

	//echo '<tr><td colspan=6><b>' . _('Less deposits not cleared') . ':</b></td></tr>';


	$TotalUnclearedDeposits =0;

	while ($myrow=mysql_fetch_array($UPChequesResult)) {

		$TotalUnclearedDeposits +=$myrow['outstanding'];

	}

	/*Brought forward */
$message.= '<small style="color:#666">Total Bank Bfwd ( all of them added ) R <b><font color="green">' . number_format(($Balance/1), 2, '.', ' ') . '</b></font>
             </small>
           <br />';
$FXStatementBalance = ($Balance/1) - $TotalUnpresentedCheques -$TotalUnclearedDeposits;
/*CPT (R11111) + GRG(33333) + KZN(100) +    +  = Bank Rxxxx*/
$message.= '<small style="color:#666">Total Bank account ( all of them added ) R <b><font color="green">' . number_format($FXStatementBalance, 2, '.', ' ') . '</b></font>
             </small>
           <br />';

	echo $TotalUnclearedDeposits ."<br />";
//$FXStatementBalance = ($Balance/1) - $TotalUnpresentedCheques -$TotalUnclearedDeposits;
/*Quick Asset  Ratio : Bank Rxxxx + Stock  + Debtors - Creditors*/
echo $FXStatementBalance ." +  ". $StockModule." +  ".$DebtorModule ." +  ". $CreditorModule." Unformated ".$UnformatedCreditorModule."<br />";
$message.= '<small style="color:#666">Quick Asset  Ratio:  R <b><font color="green">' . number_format(($FXStatementBalance + $StockUnformated + $DebtorModule )- $UnformatedCreditorModule, 2, '.', ' ') . '</b></font>
             </small>
           <br />';
/*Liquidity  Ratio : Bank Rxxxx  + Debtors – Creditors*/
$message.= '<small style="color:#666">Liquidity  Ratio :  R <b><font color="green">' . number_format(($FXStatementBalance + $DebtorModule )- $UnformatedCreditorModule, 2, '.', ' ') . '</b></font>
             </small>
           <br />';
/*Debtors Creditor Ratio : Debtors – Creditors */
$message.= '<small style="color:#666">Debtors Creditor Ratio: R <b><font color="green">' . number_format(($DebtorModule- $UnformatedCreditorModule), 2, '.', ' ') . '</b></font>
             </small>
           <br />';

/*$message.= '<small style="color:#666">GL 3700.GRN AND 3700.000(gltrans)  R <b><font color="green">' . number_format($ChckGRNTaxBal, 2, '.', ' ') . '</b></font>
             </small>
           <br />';
$message.= '<small style="color:#666">Next period(' . ($PeriodDD + 1) . ') BFW  =  R <b><font color="green">' . number_format($NxtRunningTotal, 2, '.', ' ') . '</b></font>
             </small>
           <br />';*/

$message.= '</p>';


$message .= '</body></html>';


//if (!mail("Chantelle@duroplastic.com", 'Module Variance: ' . $dbt, "Debtors Module R" . $DebtorModule . " /  GL Debtors Ledger R" . $GLDebtor . " = Debtor Module Difference R" . number_format($DiffDebtorTotal, 2, '.', ' ') . "\n"
//                . "Creditors Module R" . $CreditorModule . " / GL Creditors Ledger R" . $GLCreditor . " = Creditor Module Difference R" . number_format($DiffCreditorTotal, 2, '.', ' ') . "\n"
//                . "Stock Module R" . $StockModule . " / GL Stock Ledger R" . $GLStock . " = Stock Module Difference R" . number_format($DiffStockTotal, 2, '.', ' ') . "\n"
//                , "From: $from")) {
//    echo "Mail not send";
//}

/*if (!mail("clinton@duroplastic.com", 'Module Variance: ' . $dbt, $message
                , $headers)) {
    echo "Mail not send";
}
*/
if (!mail("tamelo@duroplastic.com", 'Module Variance: ' . $dbt, $message
                , $headers)) {
    echo "Mail not send";
}
/*if (!mail("corrine@duroplastic.com", 'Module Variance: ' . $dbt, $message
                , $headers)) {
    echo "Mail not send";
}*/

mysql_close($Conn);
?>