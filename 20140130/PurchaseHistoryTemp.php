<?php

// $PageSecurity = 24;
$ViewAllBranches = 24;
//Tamelo Douglas

include('includes/session.inc');
///global $SupplierID;

function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(",", ".", $str);
}


/****************************Search only bought/purchased items*************************************************/

if (!isset($_POST['alllocations'])) {
    $SQL = "SELECT stockmaster.categoryid,
					locstock.stockid,
					stockmaster.description,
					stockcategory.categorydescription,
					locstock.quantity  AS qoh
				FROM locstock,
					stockmaster,
					stockcategory
				WHERE locstock.stockid=stockmaster.stockid ";

    if($_POST['chckretail']){
        $SQL .= "   AND stockmaster.retailitem  = 'Y' ";
    }
    $SQL .= " AND stockmaster.categoryid >= '" . $_POST['FromCriteria'] . "'
				AND stockmaster.categoryid=stockcategory.categoryid
				AND stockmaster.categoryid <= '" . $_POST['ToCriteria'] . "'
                AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
				AND locstock.loccode = '" . $_SESSION['UserStockLocation'] . "'
				ORDER BY stockmaster.categoryid,

					stockmaster.stockid";
    //AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
} else {

    $SQL = "SELECT stockmaster.categoryid,
				stockmaster.description,
				stockcategory.categorydescription,
				locstock.stockid,

				SUM(locstock.quantity) AS qoh
			FROM locstock,
				stockmaster,
				stockcategory
			WHERE locstock.stockid=stockmaster.stockid";

    if($_POST['chckretail']){
        $SQL .= "   AND stockmaster.retailitem  = 'Y' ";
    }

    $SQL .= "  AND stockmaster.categoryid=stockcategory.categoryid
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
    //AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
}


$InventoryResult = DB_query($SQL, $db, '', '', false, false);
/***************************************************************************************************/
//if (isset($_POST['PrintPDF']) && $_POST['PurchaseOrRec'] == 0) {
if (isset($_POST['PrintPDF']) && $_POST['SearchValue'] == "quantities"){
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

  if (!file_exists($_SESSION['reports_dir'])){
	$Result = mkdir('./' . $_SESSION['reports_dir']);
}

$filename = $_SESSION['reports_dir'] . '/CrdSupPurchaseHistory.csv';

$fp = fopen($filename,"w");


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
    include('includes/PDFPuchaseHistoryPageHeader.inc');
    $TempStock = "";
While ($InventoryPlan = DB_fetch_array($InventoryResult, $db)) {
    $SQL = "SELECT  grnbatch,podetailitem,itemcode,itemdescription,deliverydate,qtyrecd,
		supplierid,stdcostunit,locgrnno,user,loccode,ponumber
        FROM  grns 
        WHERE itemcode ='" . $InventoryPlan['stockid'] . "'
        GROUP BY itemcode";
   // WHERE supplierid = '" . $_POST['supplierID'] . "'
    /* Errors */
    //echo $SQL . "<br />";
   $HeaderHeading = "Item,".$Period_12_Name."-Qty, ".$Period_11_Name."-Qty,". $Period_10_Name." -Qty,". $Period_9_Name." -Qty,". $Period_8_Name." -Qty,". $Period_7_Name." -Qty,". $Period_6_Name." -Qty,". $Period_5_Name." -Qty,". $Period_4_Name." -Qty,". $Period_3_Name ." -Qty,". $Period_2_Name ." -Qty,". $Period_1_Name ." -Qty,". $Period_0_Name." -Qty ";//MTD, Total, bght";
    // $HeaderHeading = 'Type,Number,Date,Customer,Branch,Quantity,Reference,Price(Each),Discount,New Qty';
      fputs($fp, $HeaderHeading . "\n");
    $ErrMsg = _('Could not retrive received goods ');
    $DbgMsg = _('The SQL that was used to retrive good received is ');
    $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);


    while ($myrow = DB_fetch_array($result)) {

        $SQL = "SELECT SUM(CASE WHEN prd=" . $CurrentPeriod . " THEN qty ELSE 0 END) AS prd0,
		   		SUM(CASE WHEN prd=" . $Period_1 . " THEN qty ELSE 0 END) AS prd1,
				SUM(CASE WHEN prd=" . $Period_2 . " THEN qty ELSE 0  END) AS prd2,
				SUM(CASE WHEN prd=" . $Period_3 . " THEN qty  ELSE 0 END) AS prd3,
				SUM(CASE WHEN prd=" . $Period_4 . " THEN qty  ELSE 0 END) AS prd4,
                                SUM(CASE WHEN prd=" . $Period_5 . " THEN qty  ELSE 0 END) AS prd5,
                                SUM(CASE WHEN prd=" . $Period_6 . " THEN qty  ELSE 0 END) AS prd6,
                                SUM(CASE WHEN prd=" . $Period_7 . " THEN qty  ELSE 0 END) AS prd7,
                                SUM(CASE WHEN prd=" . $Period_8 . " THEN qty  ELSE 0 END) AS prd8 ";

        $SQL .= " ,SUM(CASE WHEN prd=" . $Period_9 . " THEN qty  ELSE 0 END) AS prd9,
                                       SUM(CASE WHEN prd=" . $Period_10 . " THEN qty  ELSE 0 END) AS prd10,
                                       SUM(CASE WHEN prd=" . $Period_11 . " THEN qty  ELSE 0 END) AS prd11,
                                       SUM(CASE WHEN prd=" . $Period_12 . " THEN qty  ELSE 0 END) AS prd12
                                 ";

        $SQL .= " FROM stockmoves
                                    WHERE stockid='" . $myrow['itemcode'] . "'";
                                     /*   AND (transno='" . $myrow['grnbatch'] . "'
                                            OR CONVERT(`reference` USING utf8) LIKE '%" . $myrow['supplierid'] . "%'
                                         OR CONVERT(`reference` USING utf8) LIKE '%" . $myrow['ponumber'] . "%')";*/
        $SQL .= " AND stockmoves.type = 25


        ";

        $ErrMsg = _('Could not retrieve bom because');
        $DbgMsg = _('The SQL used to retrieve the data was');
        $SalesResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg, FALSE, FALSE);



        $SalesRow = DB_fetch_array($SalesResult);


        $MthSalesa = ($SalesRow['prd0'] + $SalesRow['prd1'] + $SalesRow['prd2'] + $SalesRow['prd3'] + $SalesRow['prd4'] + $SalesRow['prd5']
            + $SalesRow['prd6'] + $SalesRow['prd7'] + $SalesRow['prd8'] + $SalesRow['prd9'] + $SalesRow['prd10'] + $SalesRow['prd11'] + $SalesRow['prd12']);
        if( $MthSalesa == 0)continue;
//echo $myrow['itemcode']. "<br />";
        $YPos -=$line_height;

      //  if($TempStock <> $SalesRow['reference']){

            //if($TempStock <> $SalesRow['reference'])
           // $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 150, $FontSize,$SalesRow['reference'], 'left');

       // }

        /***********************/
       // if($TempStock== ""){
        //    $TempStock = $SalesRow['reference'];
            // $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 40, $FontSize, number_format($SalesRow['prd12'], 0), 'right');
            // $YPos -=$line_height;
     //   }

     /* if ($SalesRow['reference'] != $TempStock){
          $FontSize =10;
            // $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 40, $SalesRow['suppname'], 'right');
           // $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos, 150, $FontSize, $SalesRow['suppname'], 'left');
          //  $YPos -=$line_height;

           // echo $TempStock ." <> ". $SalesRow['reference']. "<br />";
           // $TempStock = $SalesRow['reference'];
            $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos-10, 150, $FontSize,$SalesRow['reference'], 'left');
            $TempStock = $SalesRow['reference'];
            $YPos -=$line_height;
        }//else{
        $FontSize = 6;
         // $TempStock = $SalesRow['reference'];
     // }
     */
     //  $YPos -=$line_height;
       // $YPos -= ( 2 * $line_height);
       $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos-10, 85, $FontSize, $myrow['itemcode'], 'left');
        $LeftOvers = $pdf->addTextWrap(120, $YPos-10, 150, $FontSize, $myrow['itemcode'], 'left');
        $LeftOvers = $pdf->addTextWrap(220, $YPos-10, 40, $FontSize, number_format($SalesRow['prd12'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(251, $YPos-10, 40, $FontSize, number_format($SalesRow['prd11'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(292, $YPos-10, 40, $FontSize, number_format($SalesRow['prd10'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(333, $YPos-10, 40, $FontSize, number_format($SalesRow['prd9'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(374, $YPos-10, 40, $FontSize, number_format($SalesRow['prd8'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(415, $YPos-10, 40, $FontSize, number_format($SalesRow['prd7'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(456, $YPos-10, 40, $FontSize, number_format($SalesRow['prd6'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(495, $YPos-10, 40, $FontSize, number_format($SalesRow['prd5'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(530, $YPos-10, 40, $FontSize, number_format($SalesRow['prd4'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(580, $YPos-10, 40, $FontSize, number_format($SalesRow['prd3'], 0), 'right');

        $MaxMthSales = $MthSalesa / 8;
        $IdealStockHolding = $MaxMthSales * $_POST['NumberMonthsHolding'];
        $Maxstock = $SalesRow['maxstock'];
        $LeftOvers = $pdf->addTextWrap(620, $YPos-10, 40, $FontSize, number_format($SalesRow['prd2'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(670, $YPos-10, 40, $FontSize, number_format($SalesRow['prd1'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(710, $YPos-10, 40, $FontSize, number_format($SalesRow['prd0'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(750, $YPos-10, 40, $FontSize, number_format($MthSalesa, 0), 'right');

   $line = stripcomma($myrow['itemcode'])  . ', ' . stripcomma(number_format($SalesRow['prd12'],0,'.', '')) . ', ' . stripcomma(number_format($SalesRow['prd11'],0,'.', '')) . ', ' .stripcomma(number_format($SalesRow['prd10'],0,'.', '')) . ', ' .stripcomma(number_format($SalesRow['prd9'],0,'.', '')) . ', ' .stripcomma(number_format($SalesRow['prd8'],0,'.', '')) . ', ' .stripcomma(number_format($SalesRow['prd7'],0,'.', '')) . ', ' .
                stripcomma(number_format($SalesRow['prd6'],0,'.', '')) . ', ' . stripcomma(number_format($SalesRow['prd5'],0,'.', '')) . ', ' . stripcomma(number_format($SalesRow['prd4'],0,'.', '')). ', ' . stripcomma(number_format($SalesRow['prd3'],0,'.', ''))
                . ', ' . stripcomma(number_format($SalesRow['prd2'],0,'.', '')). ', ' . stripcomma(number_format($SalesRow['prd1'],0,'.', '')). ', ' . stripcomma(number_format($SalesRow['prd0'],0,'.', '')) ;
                 //. ', ' . stripcomma($IdealStockHolding). ', ' . stripcomma(number_format($Maxstock,0)) . ', ' . stripcomma(number_format($SalesRow['qoh'],0)). ', ' . stripcomma(number_format($AvgStockHolding,2));
            fputs($fp, $line . "\n");
        if ($YPos < $Bottom_Margin + $line_height) {
            $PageNumber++;
            include('includes/PDFPuchaseHistoryPageHeader.inc');
        }

        fclose($fp);


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
   // $YPos -= ( 2 * $line_height);




}elseif (isset($_POST['PrintPDF']) && $_POST['PurchaseOrRec'] == 2) {
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
    $SQLGETPER0 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$date}";
    $CurrentResult0 = DB_query($SQLGETPER0, $db);
    $CurrentPeriod_0_Name = DB_fetch_row($CurrentResult0);


    $Period_0_Name = substr($CurrentPeriod_0_Name[0], 0, 3);
    $Period_0_Name_month = $CurrentPeriod_0_Name[0];
    $Period_0_Name_year = $CurrentPeriod_0_Name[1];
    /*     * ********************************* */
    $month1 = $date - 1;
    $SQLGETPER1 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month1}";
    $CurrentResult1 = DB_query($SQLGETPER1, $db);
    $CurrentPeriod_1_Name = DB_fetch_row($CurrentResult1);
    //echo $Period_0_Name ."<br />";
    $Period_1_Name = substr($CurrentPeriod_1_Name[0], 0, 3);
    $Period_1_Name_month = $CurrentPeriod_1_Name[0];
    $Period_1_Name_year = $CurrentPeriod_1_Name[1];
    //date('Y-m',strtotime('- 2 month',$t)),
    $month2 = $date - 2;
    $SQLGETPER2 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month2}";
    $CurrentResult2 = DB_query($SQLGETPER2, $db);
    $CurrentPeriod_2_Name = DB_fetch_row($CurrentResult2);
    $Period_2_Name = substr($CurrentPeriod_2_Name[0], 0, 3);
    $Period_2_Name_month = $CurrentPeriod_2_Name[0];
    $Period_2_Name_year = $CurrentPeriod_2_Name[1];
    /*     * ********************************* */
    $month3 = $date - 3;
    $SQLGETPER3 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month3}";
    $CurrentResult3 = DB_query($SQLGETPER3, $db);
    $CurrentPeriod_3_Name = DB_fetch_row($CurrentResult3);
    $Period_3_Name = substr($CurrentPeriod_3_Name[0], 0, 3);
    $Period_3_Name_month = $CurrentPeriod_3_Name[0];
    $Period_3_Name_year = $CurrentPeriod_3_Name[1];
    /*     * ********************************* */
    $month4 = $date - 4;
    $SQLGETPER4 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month4}";
    $CurrentResult4 = DB_query($SQLGETPER4, $db);
    $CurrentPeriod_4_Name = DB_fetch_row($CurrentResult4);
    $Period_4_Name = substr($CurrentPeriod_4_Name[0], 0, 3);
    $Period_4_Name_month = $CurrentPeriod_4_Name[0];
    $Period_4_Name_year = $CurrentPeriod_4_Name[1];
    /*     * ********************************* */
    $month5 = $date - 5;
    $SQLGETPER5 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month5}";
    $CurrentResult5 = DB_query($SQLGETPER5, $db);
    $CurrentPeriod_5_Name = DB_fetch_row($CurrentResult5);
    $Period_5_Name = substr($CurrentPeriod_5_Name[0], 0, 3);
    $Period_5_Name_month = $CurrentPeriod_5_Name[0];
    $Period_5_Name_year = $CurrentPeriod_5_Name[1];
    /*     * ********************************* */
    $month6 = $date - 6;
    $SQLGETPER6 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month6}";
    $CurrentResult6 = DB_query($SQLGETPER6, $db);
    $CurrentPeriod_6_Name = DB_fetch_row($CurrentResult6);
    $Period_6_Name = substr($CurrentPeriod_6_Name[0], 0, 3);
    $Period_6_Name_month = $CurrentPeriod_6_Name[0];
    $Period_6_Name_year = $CurrentPeriod_6_Name[1];
    /*     * ********************************* */
    $month7 = $date - 7;
    $SQLGETPER7 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month7}";
    $CurrentResult7 = DB_query($SQLGETPER7, $db);
    $CurrentPeriod_7_Name = DB_fetch_row($CurrentResult7);
    $Period_7_Name = substr($CurrentPeriod_7_Name[0], 0, 3);
    $Period_7_Name_month = $CurrentPeriod_7_Name[0];
    $Period_7_Name_year = $CurrentPeriod_7_Name[1];
    /*     * ********************************* */
    $month8 = $date - 8;
    $SQLGETPER8 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month8}";
    $CurrentResult8 = DB_query($SQLGETPER8, $db);
    $CurrentPeriod_8_Name = DB_fetch_row($CurrentResult8);
    $Period_8_Name = substr($CurrentPeriod_8_Name[0], 0, 3);
    $Period_8_Name_month = $CurrentPeriod_8_Name[0];
    $Period_8_Name_year = $CurrentPeriod_8_Name[1];
    /*     * ********************************* */
    $month9 = $date - 9;
    $SQLGETPER9 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month9}";
    $CurrentResult9 = DB_query($SQLGETPER9, $db);
    $CurrentPeriod_9_Name = DB_fetch_row($CurrentResult9);
    $Period_9_Name = substr($CurrentPeriod_9_Name[0], 0, 3);
    $Period_9_Name_month = $CurrentPeriod_9_Name[0];
    $Period_9_Name_year = $CurrentPeriod_9_Name[1];
    /*     * ********************************* */
    $month10 = $date - 10;
    $SQLGETPER10 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month10}";
    $CurrentResult10 = DB_query($SQLGETPER10, $db);
    $CurrentPeriod_10_Name = DB_fetch_row($CurrentResult10);
    $Period_10_Name = substr($CurrentPeriod_10_Name[0], 0, 3);
    $Period_10_Name_month = $CurrentPeriod_10_Name[0];
    $Period_10_Name_year = $CurrentPeriod_10_Name[1];
    /*     * ********************************* */
    $month11 = $date - 11;
    $SQLGETPER11 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month11}";
    $CurrentResult11 = DB_query($SQLGETPER11, $db);
    $CurrentPeriod_11_Name = DB_fetch_row($CurrentResult11);
    $Period_11_Name = substr($CurrentPeriod_11_Name[0], 0, 3);
    $Period_11_Name_month = $CurrentPeriod_11_Name[0];
    $Period_11_Name_year = $CurrentPeriod_11_Name[1];
    /*     * ********************************* */
    $month12 = $date - 12;
    $SQLGETPER12 = "SELECT MONTHNAME(lastdate_in_period),YEAR(lastdate_in_period)
            FROM periods
            WHERE periodno = {$month12}";
    $CurrentResult12 = DB_query($SQLGETPER12, $db);
    $CurrentPeriod_12_Name = DB_fetch_row($CurrentResult12);
    $Period_12_Name = substr($CurrentPeriod_12_Name[0], 0, 3);
    $Period_12_Name_month = $CurrentPeriod_12_Name[0];
    $Period_12_Name_year = $CurrentPeriod_12_Name[1];

    $Category = '';
     $tempSupplierID = "";
    include('includes/PDFPuchaseHistoryPageHeaderAmounts.inc');
   // $myrow = DB_fetch_array($result);//{
// WHERE supptrans.supplierno ='" . $_POST['supplierID']. "' ";
        $SQL = "SELECT  suppliers.suppname, SUM(CASE WHEN MONTHNAME(trandate)='" . $Period_0_Name_month  . "' AND YEAR(trandate) =".$Period_0_Name_year." THEN supptrans.ovamount + supptrans.ovgst  ELSE 0 END) AS prd0,
		   		SUM(CASE WHEN MONTHNAME(trandate)='" . $Period_1_Name_month  . "' AND YEAR(trandate) =".$Period_1_Name_year."  THEN supptrans.ovamount + supptrans.ovgst ELSE 0 END) AS prd1,
				SUM(CASE WHEN MONTHNAME(trandate)='" . $Period_2_Name_month  . "' AND YEAR(trandate) =".$Period_2_Name_year." THEN supptrans.ovamount + supptrans.ovgst  ELSE 0  END) AS prd2,
				SUM(CASE WHEN MONTHNAME(trandate)='" . $Period_3_Name_month  . "' AND YEAR(trandate) =".$Period_3_Name_year."  THEN supptrans.ovamount + supptrans.ovgst  ELSE 0 END) AS prd3,
				SUM(CASE WHEN MONTHNAME(trandate)='" . $Period_4_Name_month  . "' AND YEAR(trandate) =".$Period_4_Name_year." THEN supptrans.ovamount + supptrans.ovgst  ELSE 0 END) AS prd4,
                                SUM(CASE WHEN MONTHNAME(trandate)='" . $Period_5_Name_month  . "' AND YEAR(trandate) =".$Period_5_Name_year." THEN supptrans.ovamount + supptrans.ovgst  ELSE 0 END) AS prd5,
                                SUM(CASE WHEN MONTHNAME(trandate)='" . $Period_6_Name_month . "' AND YEAR(trandate) =".$Period_6_Name_year." THEN supptrans.ovamount + supptrans.ovgst  ELSE 0 END) AS prd6,
                                SUM(CASE WHEN MONTHNAME(trandate)='" . $Period_7_Name_month  . "' AND YEAR(trandate) =".$Period_7_Name_year." THEN supptrans.ovamount + supptrans.ovgst  ELSE 0 END) AS prd7,
                                SUM(CASE WHEN MONTHNAME(trandate)='" . $Period_8_Name_month  . "' AND YEAR(trandate) =".$Period_8_Name_year." THEN supptrans.ovamount + supptrans.ovgst  ELSE 0 END) AS prd8 ";

        $SQL .= " ,SUM(CASE WHEN MONTHNAME(trandate)='" . $Period_9_Name_month  . "' AND YEAR(trandate) =".$Period_9_Name_year." THEN supptrans.ovamount + supptrans.ovgst ELSE 0 END) AS prd9,
                                       SUM(CASE WHEN MONTHNAME(trandate)='" .$Period_10_Name_month . "' AND YEAR(trandate) =".$Period_10_Name_year." THEN supptrans.ovamount + supptrans.ovgst  ELSE 0 END) AS prd10,
                                       SUM(CASE WHEN MONTHNAME(trandate)='" . $Period_11_Name_month  . "' AND YEAR(trandate) =".$Period_11_Name_year." THEN supptrans.ovamount + supptrans.ovgst  ELSE 0 END) AS prd11,
                                       SUM(CASE WHEN MONTHNAME(trandate)='" . $Period_12_Name_month  . "' AND YEAR(trandate) =".$Period_12_Name_year." THEN supptrans.ovamount + supptrans.ovgst  ELSE 0 END) AS prd12
                                 ";

        $SQL .= " FROM supptrans,suppliers
                     WHERE supptrans.supplierno = suppliers.supplierid";
       $SQL .= " AND supptrans.type = 20
                  GROUP BY supptrans.supplierno";
      // echo  $SQL;
        $ErrMsg = _('Could not retrieve bom because');
        $DbgMsg = _('The SQL used to retrieve the data was');
        $SalesResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg, FALSE, FALSE);



        while($SalesRow = DB_fetch_array($SalesResult)){
          //  print_r($SalesRow);
           // exit;
        $FontSize = 6;
         if($tempSupplierID == ""){
             $tempSupplierID = $SalesRow['suppname'];
            // $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 40, $FontSize, number_format($SalesRow['prd12'], 0), 'right');
            // $YPos -=$line_height;
          }
        if ($tempSupplierID == $SalesRow['suppname']){
           // $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 40, $SalesRow['suppname'], 'right');
            $LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos, 150, $FontSize, $SalesRow['suppname'], 'left');
            $YPos -=$line_height;
        }else{
            $tempSupplierID = "";
        }
        $YPos -=$line_height;
        //$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $myrow['itemcode'], 'left');
        //$LeftOvers = $pdf->addTextWrap(100, $YPos, 150, $FontSize, $myrow['itemcode'], 'left');
       // $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 40, $FontSize, number_format($SalesRow['prd12'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(90, $YPos, 40, $FontSize, number_format($SalesRow['prd11'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(140, $YPos, 40, $FontSize, number_format($SalesRow['prd10'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(230, $YPos, 40, $FontSize, number_format($SalesRow['prd9'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(292, $YPos, 40, $FontSize, number_format($SalesRow['prd8'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(360, $YPos, 40, $FontSize, number_format($SalesRow['prd7'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(390, $YPos, 40, $FontSize, number_format($SalesRow['prd6'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(455, $YPos, 40, $FontSize, number_format($SalesRow['prd5'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(510, $YPos, 40, $FontSize, number_format($SalesRow['prd4'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(550, $YPos, 40, $FontSize, number_format($SalesRow['prd3'], 0), 'right');
        $MthSalesa = ($SalesRow['prd0'] + $SalesRow['prd1'] + $SalesRow['prd2'] + $SalesRow['prd3'] + $SalesRow['prd4'] + $SalesRow['prd5']
                + $SalesRow['prd6'] + $SalesRow['prd7'] + $SalesRow['prd8'] + $SalesRow['prd9'] + $SalesRow['prd10'] + $SalesRow['prd11'] + $SalesRow['prd12']);
        $MaxMthSales = $MthSalesa / 8;
       // $IdealStockHolding = $MaxMthSales * $_POST['NumberMonthsHolding'];
        //$Maxstock = $SalesRow['maxstock'];
        $LeftOvers = $pdf->addTextWrap(580, $YPos, 40, $FontSize, number_format($SalesRow['prd2'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(640, $YPos, 40, $FontSize, number_format($SalesRow['prd1'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(670, $YPos, 40, $FontSize, number_format($SalesRow['prd0'], 0), 'right');
        $LeftOvers = $pdf->addTextWrap(760, $YPos, 40, $FontSize, number_format($MthSalesa, 0), 'right');


        if ($YPos < $Bottom_Margin + $line_height) {
            $PageNumber++;
            include('includes/DFPuchaseHistoryPageHeaderAmounts.inc');
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



}elseif (isset($_POST['PrintPDF']) && $_POST['PurchaseOrRec'] == 1) {
    include('includes/header.inc');
    //echo '...........Working on it';
    echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/supplier.png" title="' . _('Outstanding Purchase Orders') . '" alt="">' . ' ' . _('Outstanding Purchase Order') . '';
    $SQL = "SELECT itemcode,supplierno,SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS qtyonorder
				FROM purchorderdetails,
					purchorders
				WHERE purchorderdetails.orderno = purchorders.orderno
				AND purchorders.supplierno = '" . $_POST['supplierID'] . "'
				AND purchorderdetails.completed = 0
				";

    $OnOrdResult = DB_query($SQL, $db, '', '', false, false);
    echo '<table>';
    echo '<tr><th>Item </th><th>Quantities</th><th>Supplier ID</th></tr>';
    while ($myrows = DB_fetch_array($OnOrdResult)) {
        echo '<tr><td>' . $myrows['itemcode'] . '</td><td>' . $myrows['qtyonorder'] . '</td><td>' . $myrows['supplierno'] . '</td></tr>';
    }
    echo '</table>';
    include('includes/footer.inc');
} else { /* The option to print PDF was not hit */

    $title = _('Purchase History');
    include('includes/header.inc');
    if (isset($_GET['SupplierID'])) {
        $SupplierID = $_GET['SupplierID'];
    } else {
        $SupplierID = "";
    }

   // if ($SupplierID != "") {

      /*  echo "<form action='" . $_SERVER['PHP_SELF'] . '' . "' method='POST'><table>";
        echo "<BR>";
        echo '<tr><input type=hidden name=supplierID value ="' . $SupplierID . '"></tr>';
        echo '<tr><td>' . _('Filter By Received/Outstanding Purchases') . ":</td><td><select name='PurchaseOrRec'>";
        echo '<option selected Value=0>' . _('Show Received Items');
        echo '<option Value=1>' . _('Items In Purchase Orders');
        echo '<option Value=2>' . _('Purchase Amounts');
        echo '</select></td></tr>';
        echo "</table><div class='centre'><input type=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></div>";
         echo "<div class='centre'><a href='" . $rootpath . '/' . $_SESSION['reports_dir'] . "/CrdPurchaseHistory.csv'>" . _('click here') .  '</a> ' . _('to export all to csv') . '</td></div>';*/

    if (strlen($_POST['FromCriteria']) < 1 || strlen($_POST['ToCriteria']) < 1) {

        /* if $FromCriteria is not set then show a form to allow input	 */

        echo "<form action='" . $_SERVER['PHP_SELF'] . '' . "' method='POST'><table>";

        echo "<BR>";
        echo '<tr><td><font color=BLUE size=4>' . _('Suppliers Purchase- Order') . '</font></td><td>';
        echo '<br /><br /><br />';
        echo '<tr><td>' . _('Filter BY ITEM CODE..') . ':</td><td><input type=text name="stockcode" size=21 value="' . $StockID . '" maxlength=20 onfocus="setBKColor(event);" onblur="reSetBKColor(event);" type="Text" onkeyup="ajax_showOptions(this,\'getItem\',event)" autocomplete=off></td></tr>';
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

        echo '<tr><td>' . _('For Inventory in Location') . ":</td><td><b>" . _($_SESSION['LocationRecord']['locationname']) . '</b>';
        if (in_array($ViewAllBranches, $_SESSION['AllowedPageSecurityTokens'])) {
            echo '<tr><td>' . _('Tick To Show Inventory In All Locations ') . ':<td><input type="checkbox" name="alllocations"></tr>';
        }

        echo '</td></tr>';
        echo '<tr><td>' . _('Before Period') . ':</td><td><input tabindex="3" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" type="TEXT" name="ToDate" maxlength="10" size="11" VALUE="' . $_POST['ToDate'] . '"></td></tr>';
       /* echo '<tr><td>' . _('Maximum No Months Holding') . ":</td><td><select name='NumberMonthsHolding'>";
        echo '<option selected Value=1>' . _('One Month');
        echo '<option Value=1.5>' . _('One Month and a half');
        echo '<option Value=2>' . _('Two Months');
        echo '<option Value=3>' . _('Three Months');
        echo '<option Value=4>' . _('Four Months');
        echo '<option Value=5>' . _('Five Months');
        echo '<option Value=6>' . _('Six Months');
        echo '<option Value=7>' . _('Seven Months');
        echo '<option Value=8>' . _('Eight Months');
        echo '</select></td></tr>';

        echo '<tr><td>' . _('Tick to show  Retail Items ONLY') . '<td><input type="checkbox" name="chckretail"></tr>';
        echo '<tr><td>' . _('Show Zero Movements/Quantities') . '<td><input type="checkbox" name="zeromovements"></tr>';
*/
        echo '<tr><td>' . _('Search BY :') . '</td><td>
		  <input type="RADIO" name="SearchValue" VALUE="quantities" CHECKED>' . _('Item Quantities') . '<br>
		  <input type="RADIO" name="SearchValue" VALUE="value">' . _('Item Value') . ' </td></tr>';
               /*   <input type="RADIO" name="SearchValue" VALUE="transfers">' . _('Transfers(Warehouse/Internal)') . '<br>
		  <input type="RADIO" name="SearchValue" VALUE="manufacturing">' . _('Manufacturing') . '</td></tr>';*/
        echo "</table><div class='centre'><input type=Submit Name='PrintPDF' Value='" . _('Print PDF') . "'></div>";
         echo "<div class='centre'><a href='" . $rootpath . '/' . $_SESSION['reports_dir'] . "/CrdSupPurchaseHistory.csv'>" . _('click here') . '</a> ' . _('to export all to csv') . '</td></div>';
    }
    //}
    include('includes/footer.inc');
} /* end of else not PrintPDF */
?>
