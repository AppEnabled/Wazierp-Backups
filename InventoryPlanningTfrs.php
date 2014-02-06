<?php

// $PageSecurity = 2;
include('includes/session.inc');

/* 	Prepare data for csv */

function stripcomma($str) { //because we're using comma as a delimiter
    return str_replace(",", ".", $str);
}

If (isset($_POST['PrintPDF'])
        AND isset($_POST['FromCriteria'])
        AND strlen($_POST['FromCriteria']) >= 1
        AND isset($_POST['ToCriteria'])
        AND strlen($_POST['ToCriteria']) >= 1) {

    include ('includes/class.pdf.php');
    if (!file_exists($_SESSION['reports_dir'])) {
        $Result = mkdir('./' . $_SESSION['reports_dir']);
    }

    $filename = $_SESSION['reports_dir'] . '/InventoryPlanningTfrs.csv';

    $fp = fopen($filename, "w");
    if ($fp == FALSE) {

        prnMsg(_('Could not open or create the file under') . ' ' . $_SESSION['reports_dir'] . '/InventoryPlanningTfrs.csv', 'error');
        include('includes/footer.inc');
        exit;
    }
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
    $pdf->addinfo('Title', _('Inventory Movement Report - Not Sales') . ' ' . Date($_SESSION['DefaultDateFormat']));

    $line_height = 12;

    $pdf->addinfo('Subject', _('Inventory Planning'));

    $PageNumber = 1;
    $line_height = 12;

    /* Now figure out the inventory data to report for the category range under review
      need Debtors Branch , Sales Mth -1, Sales Mth -2, Sales Mth -3, Sales Mth -4 */
    if (isset($_POST['Location'])) {
         	$SQL = "SELECT stockmaster.categoryid,
				stockmaster.description,
				stockcategory.categorydescription,
				locstock.stockid,
				SUM(locstock.quantity) AS qoh
			FROM locstock,
				stockmaster,
				stockcategory
			WHERE locstock.stockid=stockmaster.stockid

			AND stockmaster.categoryid=stockcategory.categoryid
			AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
			AND stockmaster.categoryid >= '" . $_POST['FromCriteria'] . "'
			AND stockmaster.categoryid <= '" . $_POST['ToCriteria'] . "'
			GROUP BY stockmaster.categoryid,
				stockmaster.description,
				stockcategory.categorydescription,
				locstock.stockid,
				stockmaster.stockid
			ORDER BY stockmaster.categoryid,
				stockmaster.stockid";
    } else {
        $SQL = "SELECT stockmaster.categoryid,
					locstock.stockid,
					stockmaster.description,
					stockcategory.categorydescription,
					locstock.quantity  AS qoh
				FROM locstock,
					stockmaster,
					stockcategory
				WHERE locstock.stockid=stockmaster.stockid
				AND locstock.quantity <> 0
				AND stockmaster.categoryid >= '" . $_POST['FromCriteria'] . "'
				AND stockmaster.categoryid=stockcategory.categoryid
				AND stockmaster.categoryid <= '" . $_POST['ToCriteria'] . "'
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
				AND locstock.loccode = '" . $_POST['Location'] . "'
				ORDER BY stockmaster.categoryid,
					stockmaster.stockid";
    }
    $InventoryResult = DB_query($SQL, $db, '', '', false, false);

    if (DB_error_no($db) != 0) {
        $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
        include('includes/header.inc');
        prnMsg(_('The inventory quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db), 'error');
        echo "<br><a href='" . $rootpath . '/index.php?' . SID . "'>" . _('Back to the menu') . '</a>';
        if ($debug == 1) {
            echo "<br>$SQL";
        }
        include('includes/footer.inc');
        exit;
    }
    if (!isset($_POST['ToDate'])) {
        $_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
    }

    $date = GetPeriod(Date($_POST['ToDate']), $db);
    $SQLGETPER0 = "SELECT MONTHNAME(lastdate_in_period)
            FROM periods
            WHERE periodno = {$date}";
    $CurrentResult0 = DB_query($SQLGETPER0, $db);
    $CurrentPeriod_0_Name = DB_fetch_row($CurrentResult0);
    //$date  = strtotime($_POST['ToDate']);

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

    if (!isset($_POST['nozero'])) {
        include ('includes/PDFInventoryPlanPageHeaderStock.inc');
        $HeaderHeading = "";
        While ($InventoryPlan = DB_fetch_array($InventoryResult, $db)) {
            $Checkres = 0;
            if ($Category != $InventoryPlan['categoryid']) {
                $FontSize = 10;
                if ($Category != '') { /* Then it's NOT the first time round */
                    /* draw a line under the CATEGORY TOTAL */
                    $YPos -=$line_height;
                    $pdf->line($Left_Margin, $YPos, $Page_Width - $Right_Margin, $YPos);
                    $YPos -= ( 2 * $line_height);
                }
                $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 260 - $Left_Margin, $FontSize, $InventoryPlan['categoryid'] . ' - ' . $InventoryPlan['categorydescription'], 'left');
                $Category = $InventoryPlan['categoryid'];
                $FontSize = 8;
            }




            if ($_POST['Location'] == 'All') {
                $SQL = "SELECT SUM(CASE WHEN prd=" . $CurrentPeriod . " THEN -qty ELSE 0 END) AS prd0,
		   		SUM(CASE WHEN prd=" . $Period_1 . " THEN -qty ELSE 0 END) AS prd1,
				SUM(CASE WHEN prd=" . $Period_2 . " THEN -qty ELSE 0 END) AS prd2,
				SUM(CASE WHEN prd=" . $Period_3 . " THEN -qty ELSE 0 END) AS prd3,
				SUM(CASE WHEN prd=" . $Period_4 . " THEN -qty ELSE 0 END) AS prd4,
                                SUM(CASE WHEN prd=" . $Period_5 . " THEN -qty  ELSE 0 END) AS prd5,
                                SUM(CASE WHEN prd=" . $Period_6 . " THEN -qty  ELSE 0 END) AS prd6,
                                SUM(CASE WHEN prd=" . $Period_7 . " THEN -qty  ELSE 0 END) AS prd7,
                                SUM(CASE WHEN prd=" . $Period_8 . " THEN -qty  ELSE 0 END) AS prd8,
                                SUM(CASE WHEN prd=" . $Period_9 . " THEN qty  ELSE 0 END) AS prd9,
                                SUM(CASE WHEN prd=" . $Period_10 . " THEN qty  ELSE 0 END) AS prd10,
                                SUM(CASE WHEN prd=" . $Period_11 . " THEN qty  ELSE 0 END) AS prd11,
                                SUM(CASE WHEN prd=" . $Period_12 . " THEN qty  ELSE 0 END) AS prd12
			FROM stockmoves
			WHERE stockid='" . $InventoryPlan['stockid'] . "'
			AND (type=16 OR type=19)
			AND stockmoves.hidemovt=0";
            } else {
                $SQL = "SELECT SUM(CASE WHEN prd=" . $CurrentPeriod . " THEN -qty ELSE 0 END) AS prd0,
		   		SUM(CASE WHEN prd=" . $Period_1 . " THEN -qty ELSE 0 END) AS prd1,
				SUM(CASE WHEN prd=" . $Period_2 . " THEN -qty ELSE 0 END) AS prd2,
				SUM(CASE WHEN prd=" . $Period_3 . " THEN -qty ELSE 0 END) AS prd3,
				SUM(CASE WHEN prd=" . $Period_4 . " THEN -qty ELSE 0 END) AS prd4,
                                SUM(CASE WHEN prd=" . $Period_5 . " THEN -qty  ELSE 0 END) AS prd5,
                                SUM(CASE WHEN prd=" . $Period_6 . " THEN -qty  ELSE 0 END) AS prd6,
                                SUM(CASE WHEN prd=" . $Period_7 . " THEN -qty  ELSE 0 END) AS prd7,
                                SUM(CASE WHEN prd=" . $Period_8 . " THEN -qty  ELSE 0 END) AS prd8,
                                SUM(CASE WHEN prd=" . $Period_9 . " THEN qty  ELSE 0 END) AS prd9,
                                SUM(CASE WHEN prd=" . $Period_10 . " THEN qty  ELSE 0 END) AS prd10,
                                SUM(CASE WHEN prd=" . $Period_11 . " THEN qty  ELSE 0 END) AS prd11,
                                SUM(CASE WHEN prd=" . $Period_12 . " THEN qty  ELSE 0 END) AS prd12
			FROM stockmoves
			WHERE stockid='" . $InventoryPlan['stockid'] . "'
			AND stockmoves.loccode ='" . $_POST['Location'] . "'
			AND (stockmoves.type=16 OR stockmoves.type=19)
			AND stockmoves.hidemovt=0";
            }

            $SalesResult = DB_query($SQL, $db, '', '', FALSE, FALSE);

            if (DB_error_no($db) != 0) {
                $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
                include('includes/header.inc');
                prnMsg(_('The sales quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db), 'error');
                echo "<br><a href='" . $rootpath . '/index.php?' . SID . "'>" . _('Back to the menu') . '</a>';
                if ($debug == 1) {
                    echo "<br>$SQL";
                }
                include('includes/footer.inc');
                exit;
            }

            $SalesRow = DB_fetch_array($SalesResult);
            $Checkres = ($SalesRow['prd0'] + $SalesRow['prd1'] + $SalesRow['prd2'] + $SalesRow['prd3'] + $SalesRow['prd4']
                    + $SalesRow['prd5'] + $SalesRow['prd6'] + $SalesRow['prd7'] + $SalesRow['prd8']);
            if ($Checkres > 0) {
                $YPos -=$line_height;
                if ($_POST['Location'] == 'All') {
                    $SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
				FROM salesorderdetails,
					salesorders
				WHERE salesorderdetails.orderno=salesorders.orderno
				AND salesorderdetails.stkcode = '" . $InventoryPlan['stockid'] . "'
				AND salesorderdetails.completed = 0";
                } else {
                    $SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
				FROM salesorderdetails,
					salesorders
				WHERE salesorderdetails.orderno=salesorders.orderno
				AND salesorders.fromstkloc ='" . $_POST['Location'] . "'
				AND salesorderdetails.stkcode = '" . $InventoryPlan['stockid'] . "'
				AND salesorderdetails.completed = 0";
                }

                $DemandResult = DB_query($SQL, $db, '', '', FALSE, FALSE);

                if (DB_error_no($db) != 0) {
                    $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
                    include('includes/header.inc');
                    prnMsg(_('The sales order demand quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db), 'error');
                    echo "<br><a href='" . $rootpath . "/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
                    if ($debug == 1) {
                        echo "<br>$SQL";
                    }
                    include('includes/footer.inc');
                    exit;
                }

//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.

                if ($_POST['Location'] == 'All') {
                    $SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
				FROM salesorderdetails,
					bom,
					stockmaster
				WHERE salesorderdetails.stkcode=bom.parent
				AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
				AND bom.component='" . $InventoryPlan['stockid'] . "'
				AND stockmaster.stockid=bom.parent
				AND stockmaster.mbflag='A'
				AND salesorderdetails.completed=0";
                } else {
                    $SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
				FROM salesorderdetails,
					salesorders,
					bom,
					stockmaster
				WHERE salesorderdetails.orderno=salesorders.orderno
				AND salesorderdetails.stkcode=bom.parent
				AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
				AND bom.component='" . $InventoryPlan['stockid'] . "'
				AND stockmaster.stockid=bom.parent
				AND salesorders.fromstkloc ='" . $_POST['Location'] . "'
				AND stockmaster.mbflag='A'
				AND salesorderdetails.completed=0";
                }

                $BOMDemandResult = DB_query($SQL, $db, '', '', false, false);

                if (DB_error_no($db) != 0) {
                    $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
                    include('includes/header.inc');
                    prnMsg(_('The sales order demand quantities from parent assemblies could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db), 'error');
                    echo "<br><a href='" . $rootpath . "/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
                    if ($debug == 1) {
                        echo "<br>$SQL";
                    }
                    include('includes/footer.inc');
                    exit;
                }

                if ($_POST['Location'] == 'All') {
                    $SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as qtyonorder
				FROM purchorderdetails,
					purchorders
				WHERE purchorderdetails.orderno = purchorders.orderno
				AND purchorderdetails.itemcode = '" . $InventoryPlan['stockid'] . "'
				AND purchorderdetails.completed = 0";
                } else {
                    $SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS qtyonorder
				FROM purchorderdetails,
					purchorders
				WHERE purchorderdetails.orderno = purchorders.orderno
				AND purchorderdetails.itemcode = '" . $InventoryPlan['stockid'] . "'
				AND purchorderdetails.completed = 0
				AND purchorders.intostocklocation=  '" . $_POST['Location'] . "'";
                }

                $DemandRow = DB_fetch_array($DemandResult);
                $BOMDemandRow = DB_fetch_array($BOMDemandResult);
                $TotalDemand = $DemandRow['qtydemand'] + $BOMDemandRow['dem'];

                $OnOrdResult = DB_query($SQL, $db, '', '', false, false);
                if (DB_error_no($db) != 0) {
                    $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
                    include('includes/header.inc');
                    prnMsg(_('The purchase order quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db), 'error');
                    echo "<br><a href='" . $rootpath . "/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
                    if ($debug == 1) {
                        echo "<br>$SQL";
                    }
                    include('includes/footer.inc');
                    exit;
                }

                $OnOrdRow = DB_fetch_array($OnOrdResult);
                $FontSize = 6;
                $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $InventoryPlan['stockid'], 'left');
                $LeftOvers = $pdf->addTextWrap(100, $YPos, 150, $FontSize, $InventoryPlan['description'], 'left');
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
                $MaxMthSales = $MthSalesa / 12;
                //$IdealStockHolding = $MaxMthSales * $_POST['NumberMonthsHolding'];
                $Maxstock = $SalesRow['maxstock'];
                $AvgStockHolding = $InventoryPlan['qoh']/ $MaxMthSales;
                $LeftOvers = $pdf->addTextWrap(620, $YPos, 40, $FontSize, number_format($SalesRow['prd2'], 0), 'right');
                $LeftOvers = $pdf->addTextWrap(660, $YPos, 40, $FontSize, number_format($SalesRow['prd1'], 0), 'right');
                $LeftOvers = $pdf->addTextWrap(700, $YPos, 40, $FontSize, number_format($SalesRow['prd0'], 0), 'right');
                $LeftOvers = $pdf->addTextWrap(740, $YPos, 40, $FontSize, number_format($MthSalesa, 0), 'right');
                $LeftOvers = $pdf->addTextWrap(770, $YPos, 40,$FontSize,number_format($AvgStockHolding,2),'right');
                if ($HeaderHeading == "") {
                    $HeaderHeading = 'Stockid, Description,' . $Period_8_Name . ',' . $Period_7_Name . ',' . $Period_6_Name . ',' . $Period_5_Name . ',' . $Period_4_Name . ',' . $Period_3_Name . ',' . $Period_2_Name . ',' . $Period_1_Name . ',' . $Period_0_Name . ',Avg,Total Movd,QOH,Splr,OrdsSugg Ord';
                    fputs($fp, $HeaderHeading . "\n");
                }
                $line = stripcomma($InventoryPlan['stockid']) . ', ' . stripcomma($InventoryPlan['description']) . ', ' . stripcomma(number_format($SalesRow['prd8'])) . ', ' . stripcomma(number_format($SalesRow['prd7'], 0)) . ', ' .
                        stripcomma(number_format($SalesRow['prd6'], 0)) . ', ' . stripcomma(number_format($SalesRow['prd5'], 0)) . ', ' . stripcomma(number_format($SalesRow['prd4'], 0)) . ', ' . stripcomma(number_format($SalesRow['prd3'], 0))
                        . ', ' . stripcomma(number_format($SalesRow['prd2'], 0)) . ', ' . stripcomma(number_format($SalesRow['prd1'], 0)) . ', ' . stripcomma(number_format($SalesRow['prd0'], 0))
                        . ', ' . stripcomma(number_format($IdealStockHolding, 0)) . ', ' . stripcomma(number_format($Maxstock, 0)) . ', ' . stripcomma(number_format($InventoryPlan['qoh'], 0)) . ', ' . stripcomma(number_format($OnOrdRow['qtyonorder'], 0))
                ;
                fputs($fp, $line . "\n");


                if ($YPos < $Bottom_Margin + $line_height) {
                    $PageNumber++;
                    include('includes/PDFInventoryPlanPageHeaderStock.inc');
                }
                $Checkres = 0;
            }
        } /* end inventory valn while loop */
        fclose($fp);
        $HeaderHeading = "";
    } elseif (isset($_POST['nozero'])) {
        include ('includes/PDFInventoryPlanPageHeaderStock.inc');
        $HeaderHeading = " ";
        /* now display eight period with zero values */
        While ($InventoryPlan = DB_fetch_array($InventoryResult, $db)) {

            if ($Category != $InventoryPlan['categoryid']) {
                $FontSize = 10;
                if ($Category != '') { /* Then it's NOT the first time round */
                    /* draw a line under the CATEGORY TOTAL */
                    $YPos -=$line_height;
                    $pdf->line($Left_Margin, $YPos, $Page_Width - $Right_Margin, $YPos);
                    $YPos -= ( 2 * $line_height);
                }
                $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 260 - $Left_Margin, $FontSize, $InventoryPlan['categoryid'] . ' - ' . $InventoryPlan['categorydescription'], 'left');
                $Category = $InventoryPlan['categoryid'];
                $FontSize = 8;
            }


            $YPos -=$line_height;

            if ($_POST['Location']) {
                $SQL = "SELECT SUM(CASE WHEN prd=" . $CurrentPeriod . " THEN -qty ELSE 0 END) AS prd0,
		   		SUM(CASE WHEN prd=" . $Period_1 . " THEN -qty ELSE 0 END) AS prd1,
				SUM(CASE WHEN prd=" . $Period_2 . " THEN -qty ELSE 0 END) AS prd2,
				SUM(CASE WHEN prd=" . $Period_3 . " THEN -qty ELSE 0 END) AS prd3,
				SUM(CASE WHEN prd=" . $Period_4 . " THEN -qty ELSE 0 END) AS prd4,
                                SUM(CASE WHEN prd=" . $Period_5 . " THEN -qty  ELSE 0 END) AS prd5,
                                SUM(CASE WHEN prd=" . $Period_6 . " THEN -qty  ELSE 0 END) AS prd6,
                                SUM(CASE WHEN prd=" . $Period_7 . " THEN -qty  ELSE 0 END) AS prd7,
                                 SUM(CASE WHEN prd=" . $Period_8 . " THEN -qty  ELSE 0 END) AS prd8,
                                SUM(CASE WHEN prd=" . $Period_9 . " THEN qty  ELSE 0 END) AS prd9,
                                SUM(CASE WHEN prd=" . $Period_10 . " THEN qty  ELSE 0 END) AS prd10,
                                SUM(CASE WHEN prd=" . $Period_11 . " THEN qty  ELSE 0 END) AS prd11,
                                SUM(CASE WHEN prd=" . $Period_12 . " THEN qty  ELSE 0 END) AS prd12
			FROM stockmoves
			WHERE stockid='" . $InventoryPlan['stockid'] . "'
			AND (type=16 OR type=19)
			AND stockmoves.hidemovt=0";
            } else {
                $SQL = "SELECT SUM(CASE WHEN prd=" . $CurrentPeriod . " THEN -qty ELSE 0 END) AS prd0,
		   		SUM(CASE WHEN prd=" . $Period_1 . " THEN -qty ELSE 0 END) AS prd1,
				SUM(CASE WHEN prd=" . $Period_2 . " THEN -qty ELSE 0 END) AS prd2,
				SUM(CASE WHEN prd=" . $Period_3 . " THEN -qty ELSE 0 END) AS prd3,
				SUM(CASE WHEN prd=" . $Period_4 . " THEN -qty ELSE 0 END) AS prd4,
                                SUM(CASE WHEN prd=" . $Period_5 . " THEN -qty  ELSE 0 END) AS prd5,
                                SUM(CASE WHEN prd=" . $Period_6 . " THEN -qty  ELSE 0 END) AS prd6,
                                SUM(CASE WHEN prd=" . $Period_7 . " THEN -qty  ELSE 0 END) AS prd7,
                                SUM(CASE WHEN prd=" . $Period_8 . " THEN -qty  ELSE 0 END) AS prd8,
                                SUM(CASE WHEN prd=" . $Period_9 . " THEN qty  ELSE 0 END) AS prd9,
                                SUM(CASE WHEN prd=" . $Period_10 . " THEN qty  ELSE 0 END) AS prd10,
                                SUM(CASE WHEN prd=" . $Period_11 . " THEN qty  ELSE 0 END) AS prd11,
                                SUM(CASE WHEN prd=" . $Period_12 . " THEN qty  ELSE 0 END) AS prd12
			FROM stockmoves
			WHERE stockid='" . $InventoryPlan['stockid'] . "'
			AND stockmoves.loccode ='" . $_POST['Location'] . "'
			AND (stockmoves.type=16 OR stockmoves.type=19)
			AND stockmoves.hidemovt=0";
            }

            $SalesResult = DB_query($SQL, $db, '', '', FALSE, FALSE);

            if (DB_error_no($db) != 0) {
                $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
                include('includes/header.inc');
                prnMsg(_('The sales quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db), 'error');
                echo "<br><a href='" . $rootpath . '/index.php?' . SID . "'>" . _('Back to the menu') . '</a>';
                if ($debug == 1) {
                    echo "<br>$SQL";
                }
                include('includes/footer.inc');
                exit;
            }

            $SalesRow = DB_fetch_array($SalesResult);


            if ($_POST['Location'] == 'All') {
                $SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
				FROM salesorderdetails,
					salesorders
				WHERE salesorderdetails.orderno=salesorders.orderno
				AND salesorderdetails.stkcode = '" . $InventoryPlan['stockid'] . "'
				AND salesorderdetails.completed = 0";
            } else {
                $SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
				FROM salesorderdetails,
					salesorders
				WHERE salesorderdetails.orderno=salesorders.orderno
				AND salesorders.fromstkloc ='" . $_POST['Location'] . "'
				AND salesorderdetails.stkcode = '" . $InventoryPlan['stockid'] . "'
				AND salesorderdetails.completed = 0";
            }

            $DemandResult = DB_query($SQL, $db, '', '', FALSE, FALSE);

            if (DB_error_no($db) != 0) {
                $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
                include('includes/header.inc');
                prnMsg(_('The sales order demand quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db), 'error');
                echo "<br><a href='" . $rootpath . "/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
                if ($debug == 1) {
                    echo "<br>$SQL";
                }
                include('includes/footer.inc');
                exit;
            }

//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.

            if ($_POST['Location'] == 'All') {
                $SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
				FROM salesorderdetails,
					bom,
					stockmaster
				WHERE salesorderdetails.stkcode=bom.parent
				AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
				AND bom.component='" . $InventoryPlan['stockid'] . "'
				AND stockmaster.stockid=bom.parent
				AND stockmaster.mbflag='A'
				AND salesorderdetails.completed=0";
            } else {
                $SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
				FROM salesorderdetails,
					salesorders,
					bom,
					stockmaster
				WHERE salesorderdetails.orderno=salesorders.orderno
				AND salesorderdetails.stkcode=bom.parent
				AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
				AND bom.component='" . $InventoryPlan['stockid'] . "'
				AND stockmaster.stockid=bom.parent
				AND salesorders.fromstkloc ='" . $_POST['Location'] . "'
				AND stockmaster.mbflag='A'
				AND salesorderdetails.completed=0";
            }

            $BOMDemandResult = DB_query($SQL, $db, '', '', false, false);

            if (DB_error_no($db) != 0) {
                $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
                include('includes/header.inc');
                prnMsg(_('The sales order demand quantities from parent assemblies could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db), 'error');
                echo "<br><a href='" . $rootpath . "/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
                if ($debug == 1) {
                    echo "<br>$SQL";
                }
                include('includes/footer.inc');
                exit;
            }

            if ($_POST['Location'] == 'All') {
                $SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as qtyonorder
				FROM purchorderdetails,
					purchorders
				WHERE purchorderdetails.orderno = purchorders.orderno
				AND purchorderdetails.itemcode = '" . $InventoryPlan['stockid'] . "'
				AND purchorderdetails.completed = 0";
            } else {
                $SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS qtyonorder
				FROM purchorderdetails,
					purchorders
				WHERE purchorderdetails.orderno = purchorders.orderno
				AND purchorderdetails.itemcode = '" . $InventoryPlan['stockid'] . "'
				AND purchorderdetails.completed = 0
				AND purchorders.intostocklocation=  '" . $_POST['Location'] . "'";
            }

            $DemandRow = DB_fetch_array($DemandResult);
            $BOMDemandRow = DB_fetch_array($BOMDemandResult);
            $TotalDemand = $DemandRow['qtydemand'] + $BOMDemandRow['dem'];

            $OnOrdResult = DB_query($SQL, $db, '', '', false, false);
            if (DB_error_no($db) != 0) {
                $title = _('Inventory Planning') . ' - ' . _('Problem Report') . '....';
                include('includes/header.inc');
                prnMsg(_('The purchase order quantities could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db), 'error');
                echo "<br><a href='" . $rootpath . "/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
                if ($debug == 1) {
                    echo "<br>$SQL";
                }
                include('includes/footer.inc');
                exit;
            }

            $OnOrdRow = DB_fetch_array($OnOrdResult);
            $FontSize = 6;
            $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $InventoryPlan['stockid'], 'left');
            $LeftOvers = $pdf->addTextWrap(100, $YPos, 150, $FontSize, $InventoryPlan['description'], 'left');
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
            $MaxMthSales = $MthSalesa / 12;
           // $IdealStockHolding = $MaxMthSales * $_POST['NumberMonthsHolding'];
            $Maxstock = $SalesRow['maxstock'];
            $AvgStockHolding = $InventoryPlan['qoh']/ $MaxMthSales ;
            $LeftOvers = $pdf->addTextWrap(620, $YPos, 40, $FontSize, number_format($SalesRow['prd2'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(670, $YPos, 40, $FontSize, number_format($SalesRow['prd1'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(710, $YPos, 40, $FontSize, number_format($SalesRow['prd0'], 0), 'right');
            $LeftOvers = $pdf->addTextWrap(750, $YPos, 40, $FontSize, number_format($MthSalesa, 0), 'right');
            $LeftOvers = $pdf->addTextWrap(770, $YPos, 40,$FontSize,number_format($AvgStockHolding,2),'right');

            if ($HeaderHeading == "") {
                $HeaderHeading = 'Stockid, Description,' . $Period_8_Name . ',' . $Period_7_Name . ',' . $Period_6_Name . ',' . $Period_5_Name . ',' . $Period_4_Name . ',' . $Period_3_Name . ',' . $Period_2_Name . ',' . $Period_1_Name . ',' . $Period_0_Name . ',Avg,Total Movd,QOH,Splr,OrdsSugg Ord';
                fputs($fp, $HeaderHeading . "\n");
            }
            $line = stripcomma($InventoryPlan['stockid']) . ', ' . stripcomma($InventoryPlan['description']) . ', ' . stripcomma(number_format($SalesRow['prd8'])) . ', ' . stripcomma(number_format($SalesRow['prd7'], 0)) . ', ' .
                    stripcomma(number_format($SalesRow['prd6'], 0)) . ', ' . stripcomma(number_format($SalesRow['prd5'], 0)) . ', ' . stripcomma(number_format($SalesRow['prd4'], 0)) . ', ' . stripcomma(number_format($SalesRow['prd3'], 0))
                    . ', ' . stripcomma(number_format($SalesRow['prd2'], 0)) . ', ' . stripcomma(number_format($SalesRow['prd1'], 0)) . ', ' . stripcomma(number_format($SalesRow['prd0'], 0))
                    . ', ' . stripcomma(number_format($IdealStockHolding, 0)) . ', ' . stripcomma(number_format($Maxstock, 0)) . ', ' . stripcomma(number_format($InventoryPlan['qoh'], 0)) . ', ' . stripcomma(number_format($OnOrdRow['qtyonorder'], 0))
            ;
            fputs($fp, $line . "\n");

            if ($YPos < $Bottom_Margin + $line_height) {
                $PageNumber++;
                include('includes/PDFInventoryPlanPageHeaderStock.inc');
            }
        } /* end inventory valn while loop */
        fclose($fp);
        $HeaderHeading = " ";
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
} else { /* The option to print PDF was not hit */

    $title = _('Inventory Planning Reporting');
    include('includes/header.inc');


    if (strlen($_POST['FromCriteria']) < 1 || strlen($_POST['ToCriteria']) < 1) {

        /* if $FromCriteria is not set then show a form to allow input	 */

        echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";

        echo "<BR>";
        echo '<tr><td><font color=BLUE size=4>' . _('Inventory Movement Planning NOT Sales') . '</font></td><td>';
        echo '<br /><br /><br />';

        echo '<tr><td>' . _('From Inventory Category Code') . ':</font></td><td><select name=FromCriteria>';

        $sql = 'SELECT categoryid, categorydescription FROM stockcategory ORDER BY categoryid';
        $CatResult = DB_query($sql, $db);
        While ($myrow = DB_fetch_array($CatResult)) {
            echo "<option VALUE='" . $myrow['categoryid'] . "'>" . $myrow['categoryid'] . " - " . $myrow['categorydescription'];
        }
        echo "</select></td></tr>";

        echo '<tr><td>' . _('To Inventory Category Code') . ':</td><td><select name=ToCriteria>';

        /* Set the index for the categories result set back to 0 */
        DB_data_seek($CatResult, 0);
        if (!isset($_POST['ToDate'])) {
            $_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
        }
        While ($myrow = DB_fetch_array($CatResult)) {
            echo "<option VALUE='" . $myrow['categoryid'] . "'>" . $myrow['categoryid'] . " - " . $myrow['categorydescription'];
        }
        echo '</select></td></tr>';

        echo '<tr><td>' . _('For Inventory in Location') . ":</td><td><select name='Location'>";
        $sql = 'SELECT loccode, locationname FROM locations';
        $LocnResult = DB_query($sql, $db);

        echo "<option Value='All'>" . _('All Locations');

        while ($myrow = DB_fetch_array($LocnResult)) {
            echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
        }
        echo '</select></td></tr>';
        echo '<tr><td>' . _('Before Period') . ':</td><td><input tabindex="3" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" type="TEXT" name="ToDate" maxlength="10" size="11" VALUE="' . $_POST['ToDate'] . '"></td></tr>';
        echo '<tr><td>' . _('Maximum No Months Holding') . ":</td><td><select name='NumberMonthsHolding'>";
        echo '<option selected Value=1>' . _('One Month');
        echo '<option Value=1.5>' . _('One Month and a half');
        echo '<option Value=2>' . _('Two Months');
        echo '<option Value=3>' . _('Three Months');
        echo '<option Value=4>' . _('Four Months');
        echo '<option Value=8>' . _('Eight Months');
        echo '</select></td></tr>';
        echo '<tr><td>' . _('Tick to show Zero Transfers') . '<td><input type="checkbox" name="nozero"></tr>';

        echo "</table><div class='centre'><input type=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></div>";
        echo "<div class='centre'><a href='" . $rootpath . '/' . $_SESSION['reports_dir'] . "/InventoryPlanningTfrs.csv'>" . _('click here') . '</a> ' . _('to export all to csv') . '</div>';
    }
    include('includes/footer.inc');
} /* end of else not PrintPDF */
?>
