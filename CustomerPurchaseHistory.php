<?php

// $PageSecurity = 24;
/*Tamelo Douglas
 * 
 * 
 */
include('includes/session.inc');
//echo "<form action='" . $_SERVER['PHP_SELF'] . '' .  "' method='POST'>";
if (isset($_POST['PrintPDF']) && $_POST['PurchaseOrRec'] == 0) {
    include ('includes/class.pdf.php');

    /* A4_Landscape */

    $Page_Width = 842;
    $Page_Height = 595;
    $Top_Margin = 20;
    $Bottom_Margin = 20;
    $Left_Margin = 25;
    $Right_Margin = 22;

    $PageSize = array(0, 0, $Page_Width, $Page_Height);
    $pdf = & new Cpdf($PageSize);

    $PageNumber = 0;

    $pdf->selectFont('./fonts/Helvetica.afm');

    /* Standard PDF file creation header stuff */

    $pdf->addinfo('Author', 'webERP ' . $Version);
    $pdf->addinfo('Creator', 'webERP http://www.weberp.org');
    $pdf->addinfo('Title', _('Inventory Sales - Cape Gate') . ' ' . Date($_SESSION['DefaultDateFormat']));

    $line_height = 12;

    $pdf->addinfo('Subject', _('Inventory CapeGate'));

    $PageNumber = 1;
    $line_height = 12;

    if (!isset($_POST['ToDate'])) {
        $_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
    }


    $date = GetPeriod(Date($_POST['ToDate']), $db);
    $SQLGETPER0 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$date}";
    $CurrentResult0 = DB_query($SQLGETPER0, $db);
    $CurrentPeriod_0_Name = DB_fetch_row($CurrentResult0);


    $Period_0_Name = substr($CurrentPeriod_0_Name[0], 0, 3);
    /*     * ********************************* */
    $month1 = $date - 1;
    $SQLGETPER1 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month1}";
    $CurrentResult1 = DB_query($SQLGETPER1, $db);
    $CurrentPeriod_1_Name = DB_fetch_row($CurrentResult1);
    //echo $Period_0_Name ."<br />";
    $Period_1_Name = substr($CurrentPeriod_1_Name[0], 0, 3);
    //date('Y-m',strtotime('- 2 month',$t)),
    $month2 = $date - 2;
    $SQLGETPER2 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month2}";
    $CurrentResult2 = DB_query($SQLGETPER2, $db);
    $CurrentPeriod_2_Name = DB_fetch_row($CurrentResult2);
    $Period_2_Name = substr($CurrentPeriod_2_Name[0], 0, 3);
    /*     * ********************************* */
    $month3 = $date - 3;
    $SQLGETPER3 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month3}";
    $CurrentResult3 = DB_query($SQLGETPER3, $db);
    $CurrentPeriod_3_Name = DB_fetch_row($CurrentResult3);
    $Period_3_Name = substr($CurrentPeriod_3_Name[0], 0, 3);
    /*     * ********************************* */
    $month4 = $date - 4;
    $SQLGETPER4 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month4}";
    $CurrentResult4 = DB_query($SQLGETPER4, $db);
    $CurrentPeriod_4_Name = DB_fetch_row($CurrentResult4);
    $Period_4_Name = substr($CurrentPeriod_4_Name[0], 0, 3);
    /*     * ********************************* */
    $month5 = $date - 5;
    $SQLGETPER5 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month5}";
    $CurrentResult5 = DB_query($SQLGETPER5, $db);
    $CurrentPeriod_5_Name = DB_fetch_row($CurrentResult5);
    $Period_5_Name = substr($CurrentPeriod_5_Name[0], 0, 3);
    /*     * ********************************* */
    $month6 = $date - 6;
    $SQLGETPER6 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month6}";
    $CurrentResult6 = DB_query($SQLGETPER6, $db);
    $CurrentPeriod_6_Name = DB_fetch_row($CurrentResult6);
    $Period_6_Name = substr($CurrentPeriod_6_Name[0], 0, 3);
    /*     * ********************************* */
    $month7 = $date - 7;
    $SQLGETPER7 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month7}";
    $CurrentResult7 = DB_query($SQLGETPER7, $db);
    $CurrentPeriod_7_Name = DB_fetch_row($CurrentResult7);
    $Period_7_Name = substr($CurrentPeriod_7_Name[0], 0, 3);
    /*     * ********************************* */
    $month8 = $date - 8;
    $SQLGETPER8 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month8}";
    $CurrentResult8 = DB_query($SQLGETPER8, $db);
    $CurrentPeriod_8_Name = DB_fetch_row($CurrentResult8);
    $Period_8_Name = substr($CurrentPeriod_8_Name[0], 0, 3);
    /*     * ********************************* */
    $month9 = $date - 9;
    $SQLGETPER9 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month9}";
    $CurrentResult9 = DB_query($SQLGETPER9, $db);
    $CurrentPeriod_9_Name = DB_fetch_row($CurrentResult9);
    $Period_9_Name = substr($CurrentPeriod_9_Name[0], 0, 3);
    /*     * ********************************* */
    $month10 = $date - 10;
    $SQLGETPER10 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month10}";
    $CurrentResult10 = DB_query($SQLGETPER10, $db);
    $CurrentPeriod_10_Name = DB_fetch_row($CurrentResult10);
    $Period_10_Name = substr($CurrentPeriod_10_Name[0], 0, 3);
    /*     * ********************************* */
    $month11 = $date - 11;
    $SQLGETPER11 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month11}";
    $CurrentResult11 = DB_query($SQLGETPER11, $db);
    $CurrentPeriod_11_Name = DB_fetch_row($CurrentResult11);
    $Period_11_Name = substr($CurrentPeriod_11_Name[0], 0, 3);
    /*     * ********************************* */
    $month12 = $date - 12;
    $SQLGETPER12 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month12}";
    $CurrentResult12 = DB_query($SQLGETPER12, $db);
    $CurrentPeriod_12_Name = DB_fetch_row($CurrentResult12);
    $Period_12_Name = substr($CurrentPeriod_12_Name[0], 0, 3);

    $Category = '';

    //$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);
    $CurrentPeriod = GetPeriod(Date($_POST['ToDate']), $db);
    $Period_1 = $CurrentPeriod - 1;
    $Period_2 = $CurrentPeriod - 2;
    $Period_3 = $CurrentPeriod - 3;
    $Period_4 = $CurrentPeriod - 4;
    $Period_5 = $CurrentPeriod - 5;
    $Period_6 = $CurrentPeriod - 6;
    $Period_7 = $CurrentPeriod - 7;
    $Period_8 = $CurrentPeriod - 8;
    $Period_9 = $CurrentPeriod - 9;
    $Period_10 = $CurrentPeriod - 10;
    $Period_11 = $CurrentPeriod - 11;
    $Period_12 = $CurrentPeriod - 12;

    $SQL = "SELECT stockmaster.categoryid,
				stockmaster.description,
				stockcategory.categorydescription,
				stockmaster.stockid,
				SUM(locstock.quantity) AS qoh
			FROM locstock,
				stockmaster,
				stockcategory
			WHERE locstock.stockid=stockmaster.stockid
			AND stockmaster.categoryid=stockcategory.categoryid
			AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
			GROUP BY stockmaster.categoryid,
				stockmaster.description,
				stockcategory.categorydescription,
				locstock.stockid,
				stockmaster.stockid
			ORDER BY stockmaster.categoryid,
				stockmaster.stockid";

    $DbgMsg = _('The SQL that was used to retrive stockid is ');
    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
    include('includes/PDFCustomerPuchaseHistoryPageHeader.inc');
    while ($myrow = DB_fetch_array($result)) {

        $SQL = "SELECT stockid, SUM(CASE WHEN prd=" . $CurrentPeriod . " THEN -qty ELSE 0 END) AS prd0,
		   		SUM(CASE WHEN prd=" . $Period_1 . " THEN -qty ELSE 0 END) AS prd1,
				SUM(CASE WHEN prd=" . $Period_2 . " THEN -qty ELSE 0  END) AS prd2,
				SUM(CASE WHEN prd=" . $Period_3 . " THEN -qty  ELSE 0 END) AS prd3,
				SUM(CASE WHEN prd=" . $Period_4 . " THEN -qty  ELSE 0 END) AS prd4,
                                SUM(CASE WHEN prd=" . $Period_5 . " THEN -qty  ELSE 0 END) AS prd5,
                                SUM(CASE WHEN prd=" . $Period_6 . " THEN -qty  ELSE 0 END) AS prd6,
                                SUM(CASE WHEN prd=" . $Period_7 . " THEN -qty  ELSE 0 END) AS prd7,
                                SUM(CASE WHEN prd=" . $Period_8 . " THEN -qty  ELSE 0 END) AS prd8 ";

        $SQL .= " ,SUM(CASE WHEN prd=" . $Period_9 . " THEN -qty  ELSE 0 END) AS prd9,
                                       SUM(CASE WHEN prd=" . $Period_10 . " THEN -qty  ELSE 0 END) AS prd10,
                                       SUM(CASE WHEN prd=" . $Period_11 . " THEN -qty  ELSE 0 END) AS prd11,
                                       SUM(CASE WHEN prd=" . $Period_12 . " THEN -qty  ELSE 0 END) AS prd12
                                    FROM stockmoves
                                    WHERE debtorno ='" . $_POST['customerID'] . "'
                                    AND stockid = '" . $myrow['stockid'] . "'
                                    AND (stockmoves.type=10 OR stockmoves.type=11) ";

        $ErrMsg = _('Could not retrieve bom because');
        $DbgMsg = _('The SQL used to retrieve the data was');
        $SalesResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg, FALSE, FALSE);


        $SalesRow = DB_fetch_array($SalesResult);

        $FontSize = 6;
        if ($SalesRow['stockid'] != "") {
           // echo $SalesRow['stockid']. "<br />";
            $YPos -=$line_height;
            $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $SalesRow['stockid'], 'left');
            $LeftOvers = $pdf->addTextWrap(100, $YPos, 150, $FontSize, $myrow['itemcode'], 'left');
            $LeftOvers = $pdf->addTextWrap(220, $YPos, 40, $FontSize, number_format($SalesRow['prd12'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(251, $YPos, 40, $FontSize, number_format($SalesRow['prd11'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(292, $YPos, 40, $FontSize, number_format($SalesRow['prd10'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(333, $YPos, 40, $FontSize, number_format($SalesRow['prd9'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(374, $YPos, 40, $FontSize, number_format($SalesRow['prd8'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(415, $YPos, 40, $FontSize, number_format($SalesRow['prd7'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(456, $YPos, 40, $FontSize, number_format($SalesRow['prd6'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(495, $YPos, 40, $FontSize, number_format($SalesRow['prd5'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(530, $YPos, 40, $FontSize, number_format($SalesRow['prd4'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(580, $YPos, 40, $FontSize, number_format($SalesRow['prd3'], 0), 'right');
            $MthSalesa = ($SalesRow['prd0'] + $SalesRow['prd1'] + $SalesRow['prd2'] + $SalesRow['prd3'] + $SalesRow['prd4'] + $SalesRow['prd5']
                    + $SalesRow['prd6'] + $SalesRow['prd7'] + $SalesRow['prd8'] + $SalesRow['prd9'] + $SalesRow['prd10'] + $SalesRow['prd11'] + $SalesRow['prd12']);

            $LeftOvers = $pdf->addTextWrap(620, $YPos, 40, $FontSize, number_format($SalesRow['prd2'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(670, $YPos, 40, $FontSize, number_format($SalesRow['prd1'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(710, $YPos, 40, $FontSize, number_format($SalesRow['prd0'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(750, $YPos, 40, $FontSize, number_format($MthSalesa, 0), 'right');


            if ($YPos < $Bottom_Margin + $line_height) {
                $PageNumber++;
                include('includes/PDFCustomerPuchaseHistoryPageHeader.inc');
            }
        }
    }
    $YPos -= ( 2 * $line_height);

    $pdf->line($Left_Margin, $YPos + $line_height, $Page_Width - $Right_Margin, $YPos + $line_height);

    $pdfcode = $pdf->output();
    $len = strlen($pdfcode);

    if ($len <= 20) {
        $title = _('Print Inventory Planning Report Empty');
        include('includes/header.inc');
        prnMsg(_('There were no items in the range and location specified'), 'error');
        echo "<br><a href='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
        include('includes/footer.inc');
        exit;
    } else {
        header('Content-type: application/pdf');
        header('Content-Length: ' . $len);
        header('Content-Disposition: inline; filename=InventoryPlanning.pdf');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        $pdf->Stream();
    }
} elseif (isset($_POST['PrintPDF']) && $_POST['PurchaseOrRec'] == 1) {

    $title = _('Customer Sales');
    include('includes/header.inc');
    //echo '...........Working on it';

    echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/supplier.png" title="' . _('Customer Sales ') . '" alt="">' . ' ' . _('Customer Sales') . '';


    if (!isset($_POST['ToDate'])) {
       $_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
    }


    $date = GetPeriod(Date($_POST['ToDate']), $db);
    $SQLGETPER0 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$date}";
    $CurrentResult0 = DB_query($SQLGETPER0, $db);
    $CurrentPeriod_0_Name = DB_fetch_row($CurrentResult0);


    $Period_0_Name = substr($CurrentPeriod_0_Name[0], 0, 3);
    /*     * ********************************* */
    $month1 = $date - 1;
    $SQLGETPER1 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month1}";
    $CurrentResult1 = DB_query($SQLGETPER1, $db);
    $CurrentPeriod_1_Name = DB_fetch_row($CurrentResult1);
    //echo $Period_0_Name ."<br />";
    $Period_1_Name = substr($CurrentPeriod_1_Name[0], 0, 3);
    //date('Y-m',strtotime('- 2 month',$t)),
    $month2 = $date - 2;
    $SQLGETPER2 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month2}";
    $CurrentResult2 = DB_query($SQLGETPER2, $db);
    $CurrentPeriod_2_Name = DB_fetch_row($CurrentResult2);
    $Period_2_Name = substr($CurrentPeriod_2_Name[0], 0, 3);
    /*     * ********************************* */
    $month3 = $date - 3;
    $SQLGETPER3 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month3}";
    $CurrentResult3 = DB_query($SQLGETPER3, $db);
    $CurrentPeriod_3_Name = DB_fetch_row($CurrentResult3);
    $Period_3_Name = substr($CurrentPeriod_3_Name[0], 0, 3);
    /*     * ********************************* */
    $month4 = $date - 4;
    $SQLGETPER4 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month4}";
    $CurrentResult4 = DB_query($SQLGETPER4, $db);
    $CurrentPeriod_4_Name = DB_fetch_row($CurrentResult4);
    $Period_4_Name = substr($CurrentPeriod_4_Name[0], 0, 3);
    /*     * ********************************* */
    $month5 = $date - 5;
    $SQLGETPER5 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month5}";
    $CurrentResult5 = DB_query($SQLGETPER5, $db);
    $CurrentPeriod_5_Name = DB_fetch_row($CurrentResult5);
    $Period_5_Name = substr($CurrentPeriod_5_Name[0], 0, 3);
    /*     * ********************************* */
    $month6 = $date - 6;
    $SQLGETPER6 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month6}";
    $CurrentResult6 = DB_query($SQLGETPER6, $db);
    $CurrentPeriod_6_Name = DB_fetch_row($CurrentResult6);
    $Period_6_Name = substr($CurrentPeriod_6_Name[0], 0, 3);
    /*     * ********************************* */
    $month7 = $date - 7;
    $SQLGETPER7 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month7}";
    $CurrentResult7 = DB_query($SQLGETPER7, $db);
    $CurrentPeriod_7_Name = DB_fetch_row($CurrentResult7);
    $Period_7_Name = substr($CurrentPeriod_7_Name[0], 0, 3);
    /*     * ********************************* */
    $month8 = $date - 8;
    $SQLGETPER8 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month8}";
    $CurrentResult8 = DB_query($SQLGETPER8, $db);
    $CurrentPeriod_8_Name = DB_fetch_row($CurrentResult8);
    $Period_8_Name = substr($CurrentPeriod_8_Name[0], 0, 3);
    /*     * ********************************* */
    $month9 = $date - 9;
    $SQLGETPER9 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month9}";
    $CurrentResult9 = DB_query($SQLGETPER9, $db);
    $CurrentPeriod_9_Name = DB_fetch_row($CurrentResult9);
    $Period_9_Name = substr($CurrentPeriod_9_Name[0], 0, 3);
    /*     * ********************************* */
    $month10 = $date - 10;
    $SQLGETPER10 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month10}";
    $CurrentResult10 = DB_query($SQLGETPER10, $db);
    $CurrentPeriod_10_Name = DB_fetch_row($CurrentResult10);
    $Period_10_Name = substr($CurrentPeriod_10_Name[0], 0, 3);
    /*     * ********************************* */
    $month11 = $date - 11;
    $SQLGETPER11 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month11}";
    $CurrentResult11 = DB_query($SQLGETPER11, $db);
    $CurrentPeriod_11_Name = DB_fetch_row($CurrentResult11);
    $Period_11_Name = substr($CurrentPeriod_11_Name[0], 0, 3);
    /*     * ********************************* */
    $month12 = $date - 12;
    $SQLGETPER12 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month12}";
    $CurrentResult12 = DB_query($SQLGETPER12, $db);
    $CurrentPeriod_12_Name = DB_fetch_row($CurrentResult12);
    $Period_12_Name = substr($CurrentPeriod_12_Name[0], 0, 3);

    $Category = '';

    //$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);
    $CurrentPeriod = GetPeriod(Date($_POST['ToDate']), $db);
    $Period_1 = $CurrentPeriod - 1;
    $Period_2 = $CurrentPeriod - 2;
    $Period_3 = $CurrentPeriod - 3;
    $Period_4 = $CurrentPeriod - 4;
    $Period_5 = $CurrentPeriod - 5;
    $Period_6 = $CurrentPeriod - 6;
    $Period_7 = $CurrentPeriod - 7;
    $Period_8 = $CurrentPeriod - 8;
    $Period_9 = $CurrentPeriod - 9;
    $Period_10 = $CurrentPeriod - 10;
    $Period_11 = $CurrentPeriod - 11;
    $Period_12 = $CurrentPeriod - 12;
    
    
    
    $SQL = "SELECT SUM(CASE WHEN (prd=" . $CurrentPeriod . ")THEN ovamount+ovfreight+ovdiscount  ELSE 0 END) AS prd0,
		   		SUM(CASE WHEN (prd=" . $Period_1 . ") THEN ovamount+ovfreight+ovdiscount  ELSE 0 END) AS prd1,
				SUM(CASE WHEN (prd=" . $Period_2 . ") THEN ovamount+ovfreight+ovdiscount  ELSE 0  END) AS prd2,
				SUM(CASE WHEN (prd=" . $Period_3 . ") THEN ovamount+ovfreight+ovdiscount  ELSE 0 END) AS prd3,
				SUM(CASE WHEN (prd=" . $Period_4 . ") THEN ovamount+ovfreight+ovdiscount  ELSE 0 END) AS prd4,
                                SUM(CASE WHEN (prd=" . $Period_5 . ") THEN ovamount+ovfreight+ovdiscount  ELSE 0 END) AS prd5,
                                SUM(CASE WHEN (prd=" . $Period_6 . ") THEN ovamount+ovfreight+ovdiscount  ELSE 0 END) AS prd6,
                                SUM(CASE WHEN (prd=" . $Period_7 . ") THEN ovamount+ovfreight+ovdiscount  ELSE 0 END) AS prd7,
                                SUM(CASE WHEN (prd=" . $Period_8 . ") THEN ovamount+ovfreight+ovdiscount  ELSE 0 END) AS prd8 ";

    $SQL .= " ,SUM(CASE WHEN (prd=" . $Period_9 . ") THEN ovamount+ovfreight+ovdiscount   ELSE 0 END) AS prd9,
                                       SUM(CASE WHEN (prd=" . $Period_10 . ") THEN ovamount+ovfreight+ovdiscount   ELSE 0 END) AS prd10,
                                       SUM(CASE WHEN (prd=" . $Period_11 . ") THEN ovamount+ovfreight+ovdiscount   ELSE 0 END) AS prd11,
                                       SUM(CASE WHEN (prd=" . $Period_12 . ") THEN ovamount+ovfreight+ovdiscount   ELSE 0 END) AS prd12
                                 ";

    $SQL .= " FROM debtortrans
                                    WHERE debtorno='" . $_POST['customerID'] . "'";
    $SQL .= " AND (debtortrans.type=10 OR debtortrans.type=11) ";

    $ErrMsg = _('Could not retrieve bom because');
    $DbgMsg = _('The SQL used to retrieve the data was');
    $transResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg, FALSE, FALSE);
    $SalesRow = DB_fetch_array($transResult);

    echo '<table border=1 >';
    echo '<tr><th>Debtor</th><th>Period_' . $Period_12_Name . '</th><th>Period_' . $Period_11_Name . '</th><th>Period_' . $Period_10_Name . '</th><th>Period_' . $Period_9_Name . '</th><th>Period_' . $Period_8_Name .
    '</th><th>Period_' . $Period_7_Name . '</th><th>Period_' . $Period_6_Name . '</th><th>Period_' . $Period_5_Name .
    '</th><th>Period_' . $Period_4_Name . '</th><th>Period_' . $Period_3_Name . '</th><th>Period_' . $Period_2_Name .
    '</th><th>Period_' . $Period_1_Name . '</th><th>Period_' . $Period_0_Name . '</th></tr>';

    echo '<tr><td>' . $_POST['customerID'] . '</td><td>' . number_format($SalesRow['prd12'], 2) . '</td><td>' . number_format($SalesRow['prd11'], 2) . '</td><td>' . number_format($SalesRow['prd10'], 2) . '</td><td>' . number_format($SalesRow['prd9'], 2) . '</td><td>' . number_format($SalesRow['prd8'], 2) .
    '</td><td>' . number_format($SalesRow['prd7'], 2) . '</td><td>' . number_format($SalesRow['prd6'], 2) . '</td><td>' . number_format($SalesRow['prd5'], 2) . '</td><td>' . number_format($SalesRow['prd4'], 2) . '</td><td>' . number_format($SalesRow['prd3'], 2) .
    '</td><td>' . number_format($SalesRow['prd2'], 2) . '</td><td>' . number_format($SalesRow['prd1'], 2) . '</td><td>' . number_format($SalesRow['prd0'], 2) . '</td></tr>';
    // prnMsg(_('Busy with this module......'), 'info');
    echo '<table>';


  echo "<div class='centre'>";
    echo "<input type='hidden' name='customerID' value ='".$_POST['customerID']."' >";
  echo "<input type='hidden' name='PurchaseOrRec' value =".$_POST['PurchaseOrRec']." >";
  echo 'Select trasactions before: <input tabindex="3" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="TEXT" name="ToDate" maxlength="10" size="11" VALUE="' . $_POST['ToDate'] . '"><br />';
  echo "<input type=Submit Name='PrintPDF' Value='" . _('Search ') . "'>
  </div></form>";


    /***********************************now draw the graph*******************/
    include('includes/phplot/phplot.php');
    $graph = new PHPlot(950,450);
    $GraphTitle = _('Sales Value');
    $SelectClause ='';
    $_POST['GraphValue']='Net';
    
    $_POST['GraphType'] = 'linepoints';
    $WhereClause ='';
    $GroupBYOption ='';
    $SelectClause = 'amt';
    $WhereClause = "   UPPER(cust)>='". $_POST['customerID'] . "' AND  UPPER(cust)<='" . $_POST['customerID'] . "' AND ";
    $WhereClause = 'WHERE ' . $WhereClause . ' salesanalysis.periodno>=' . $Period_12 . ' AND salesanalysis.periodno <= ' . $CurrentPeriod;
    
    
    $SQLGraph = 'SELECT salesanalysis.periodno,
				periods.lastdate_in_period,
				SUM(CASE WHEN budgetoractual=1 THEN ' . $SelectClause . ' ELSE 0 END) AS sales,
				SUM(CASE WHEN  budgetoractual=0 THEN ' . $SelectClause . ' ELSE 0 END) AS budget
		FROM salesanalysis  INNER JOIN periods ON salesanalysis.periodno=periods.periodno '.$WhereClause . '
		GROUP BY '.$GroupBYOption.' salesanalysis.periodno,
			periods.lastdate_in_period
		ORDER BY salesanalysis.periodno';
    
 
        $graph->SetTitle($GraphTitle);
	$graph->SetTitleColor('blue');
	$graph->SetOutputFile('companies/' .$_SESSION['DatabaseName'] .  '/reports/salesgraph.png');
	$graph->SetXTitle(_('Month'));
	if ($_POST['GraphValue']=='Net'){
		$graph->SetYTitle(_('Sales Value'));
	} elseif ($_POST['GraphValue']=='GP'){
		$graph->SetYTitle(_('Gross Profit'));
	} else {
		$graph->SetYTitle(_('Quantity'));
	}
	$graph->SetXTickPos('none');
	$graph->SetXTickLabelPos('none');
	$graph->SetBackgroundColor("white");
	$graph->SetTitleColor("blue");
	$graph->SetFileFormat("png");
	$graph->SetPlotType($_POST['GraphType']);
	$graph->SetIsInline("1");
	$graph->SetShading(5);
	$graph->SetDrawYGrid(TRUE);
	$graph->SetDataType('text-data');

	$SalesResult = DB_query($SQLGraph, $db);
	if (DB_error_no($db) !=0) {

		prnMsg(_('The sales graph data for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg($db),'error');
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($SalesResult)==0){
		prnMsg(_('There is not sales data for the criteria entered to graph'),'info');
		include('includes/footer.inc');
		exit;
	}

	$GraphArray = array();
	$i = 0;
	while ($myrow = DB_fetch_array($SalesResult)){
		$GraphArray[$i] = array(MonthAndYearFromSQLDate($myrow['lastdate_in_period']),$myrow['sales'],$myrow['budget']);
		$i++;
	}

	$graph->SetDataValues($GraphArray);
	$graph->SetDataColors(
		array('navy','magenta'),  //Data Colors
		array('black')	//Border Colors
	);
	$graph->SetLegend(array(_('Actual'),_('Budget')));

	//Draw it
	$graph->DrawGraph();
        echo '<table class=selection><tr><td>';
	echo '<p><img src="companies/' .$_SESSION['DatabaseName'] .  '/reports/salesgraph.png" alt="Sales Report Graph"></img></p>';
	echo '</td></tr></table>';
    
    
    include('includes/footer.inc');
} else { /* The option to print PDF was not hit */

    $title = _('Purchase History');
    include('includes/header.inc');
    if (isset($_GET['CustomerID'])) {
        $CustomerID = $_GET['CustomerID'];
    } else {
        $CustomerID = "";
    }

    if ($CustomerID != "") {

       echo "<form action='" . $_SERVER['PHP_SELF'] . '' . "' method='POST'><table>";
        echo "<BR>";
        echo '<tr><th colspan =2>' . $CustomerID . '</th></tr>';
        echo '<tr><input type=hidden name=customerID value ="' . $CustomerID . '"></tr>';
        echo '<tr><td>' . _('Filter By Units/Sales') . ":</td><td><select name='PurchaseOrRec'>";
        echo '<option selected Value=0>' . _('Show By Units ');
        echo '<option Value=1>' . _('Show By Sales');
        echo '</select></td></tr>';
        echo "</table><div class='centre'><input type=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></div>";
    }
    
    include('includes/footer.inc');
} /* end of else not PrintPDF */
?>
