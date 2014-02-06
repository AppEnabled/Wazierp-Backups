<?php

/*
 * @author : Tamelo Douglas
 * Be able to understand before attempt to change the code below
 * Comments have been provided, if they make sense
 */
// $PageSecurity = 2;

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

echo "<form action='" . $_SERVER['PHP_SELF'] . "' method=post>";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_POST['process'])) {

    $xml = simplexml_load_file($_POST['filepath']);

    $temp_num = 0;
    $salestype = "";
    $ErroMsg = array();
    $orderlineno = 0;
    $Result = DB_Txn_Begin($db);
    $Msg = array();
    $Ordertotals = array();

    foreach ($xml as $item) {
        $StockID = '';
        $StockIDCHECK = '';
        $unitprice = 0;
        $ItemPrice = 0;
        $Quantity = 0;
        $OrderExist = 0;
        $PO_NumTemp = '';

        /* Check Prices */
        $sqla = "SELECT stockid FROM stockmaster
                                WHERE barcode = '" . trim($item->Barcode) . "'";
        $results = DB_query($sqla, $db, $ErrMsg);

        if (DB_num_rows($results) > 0) {
            $myrow = DB_fetch_array($results);
            $StockIDCHECK = $myrow['stockid'];
        } else {
            $sqlbarcode = "SELECT stockid FROM stockmaster
                                WHERE barcodetwo ='" . trim($item->Barcode) . "'";

            $myresults = DB_query($sqlbarcode, $db, $ErrMsg);
            $myrowstock = DB_fetch_array($myresults);

            $StockIDCHECK = $myrowstock['stockid'];
        }
        /*Lets check if we do have an item selected*/
        if($StockIDCHECK == ""){
            continue;// avoid mistakes
        }

        if ($_POST['avoidchecker'] == "New") {
            $SQL = "SELECT salesorders.orderno,salesorders.customerref,salesorderdetails.stkcode
                               FROM salesorders,salesorderdetails
                               WHERE salesorders.orderno = salesorderdetails.orderno
                               AND (salesorders.customerref='" . $item->PO_Number . "' OR UPPER(salesorders.customerref)= '" . trim($item->Site . '/' . $item->PO_Number) . "'
                               OR UPPER(salesorders.customerref)= '" . trim($item->Site . '-' . $item->PO_Number) . "'
                               OR UPPER(salesorders.customerref) LIKE '%" . trim($item->Site . '-' . $item->PO_Number) . "'
                               OR salesorders.customerref LIKE  '%" . trim($item->PO_Number) . "%'
                               OR UPPER(salesorders.customerref) LIKE '%" . trim($item->PO_Number) . "%')
                               AND salesorderdetails.stkcode='" . $StockIDCHECK . "'";

            $salesResults = DB_query($SQL, $db, $ErrMsg);
            $myrow = DB_fetch_array($salesResults);
            if (DB_num_rows($salesResults) > 0) {// check if the item does exist on the tables/ else update to put the item for order, but check if its been invoiced first
                continue;
            } else {
                /* Does this order already exist?,  If yes have it been invoiced? */
                $SQL = "SELECT salesorders.orderno,salesorders.customerref,salesorderdetails.stkcode
                               FROM salesorders,salesorderdetails
                               WHERE salesorders.orderno = salesorderdetails.orderno
                               AND (salesorders.customerref='" . $item->PO_Number . "' OR UPPER(salesorders.customerref)= '" . trim($item->Site . '/' . $item->PO_Number) . "'
                               OR UPPER(salesorders.customerref)= '" . trim($item->Site . '-' . $item->PO_Number) . "'
                               OR salesorders.customerref LIKE  '%" . trim($item->PO_Number) . "%'
                               OR UPPER(salesorders.customerref) LIKE '%" . trim($item->PO_Number) . "%')";

                $salesResults = DB_query($SQL, $db, $ErrMsg);
                if (DB_num_rows($salesResults) > 0) {
                    $myrow = DB_fetch_array($salesResults);
                    $SQL = "SELECT debtortrans.transno,debtortrans.order_
                                FROM debtortrans
                                WHERE debtortrans.order_ = '" . $myrow['orderno'] . "'";
                    $ErrMsg = _('No transactions were returned by the SQL because');
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    if (DB_num_rows($TransResult) > 0) {
                        /* Alreay invoiced skip */
                        continue;
                    } else {
                        /* Exist, not yet invoiced, update the order number.. */
                        $OrderExist = 1;
                    }
                }
            }
        }elseif($_POST['avoidchecker'] == "Overide"){//Here we want to create a new salesorder-number but same cust ref/customer number
            // all in all we duplicating customer order number but different salesorder with different items
            //1. check if the item is already in the table
            $SQL = "SELECT salesorders.orderno,salesorders.customerref,salesorderdetails.stkcode
                               FROM salesorders,salesorderdetails
                               WHERE salesorders.orderno = salesorderdetails.orderno
                               AND (salesorders.customerref='" . $item->PO_Number . "' OR UPPER(salesorders.customerref)= '" . trim($item->Site . '/' . $item->PO_Number) . "'
                               OR UPPER(salesorders.customerref)= '" . trim($item->Site . '-' . $item->PO_Number) . "'
                               OR UPPER(salesorders.customerref) LIKE '%" . trim($item->Site . '-' . $item->PO_Number) . "'
                               OR salesorders.customerref LIKE  '%" . trim($item->PO_Number) . "%'
                               OR UPPER(salesorders.customerref) LIKE '%" . trim($item->PO_Number) . "%')
                               AND salesorderdetails.stkcode='" . $StockIDCHECK . "'";

            $salesResults = DB_query($SQL, $db, $ErrMsg);
            $myrow = DB_fetch_array($salesResults);
            if (DB_num_rows($salesResults) > 0) {
                //does exist so is the item then skip
                continue;
            }
        }//end check


        /*if OrderExist == 1 then we just need to insert into salesoderdetails since we already have the header(salesorder)*/
        if ($temp_num != (int) $item->PO_Number && $OrderExist == 0) {//can be overide/new sales order, since we want to create new order number i.e.CPTSO122111
            $temp_num = (int) $item->PO_Number;
            /* Get The Debtor */
            $OrderNo = "";
            $orderlineno = 0;
            $sql = "SELECT custbranch.brname,
                                custbranch.debtorno,
                                custbranch.branchcode,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.braddress6,
				custbranch.phoneno,
				custbranch.email,
                                custbranch.area,
				custbranch.defaultlocation,
				custbranch.defaultshipvia,
				custbranch.deliverblind,
                                custbranch.specialinstructions,
                                custbranch.estdeliverydays,
                                locations.locationname,
				custbranch.salesman
			FROM custbranch
			INNER JOIN locations
			ON custbranch.defaultlocation=locations.loccode
			WHERE custbranch.branch_num = '" . trim($item->Site) . "'
                        AND custbranch.disabletrans = 0";

            $DebtorResults = DB_query($sql, $db, $ErrMsg);
            $myrow = DB_fetch_array($DebtorResults);
            $debrtonum = $myrow['debtorno'];
            $branchcode = $myrow['branchcode'];
            $brname = $myrow['brname'];
            $braddress1 = $myrow['braddress1'];
            $braddress2 = $myrow['braddress2'];
            $braddress3 = $myrow['braddress3'];
            $braddress4 = $myrow['braddress4'];
            $braddress5 = $myrow['braddress5'];
            $braddress6 = $myrow['braddress6'];
            $phoneno = $myrow['phoneno'];
            $email = $myrow['email'];
            $area = $myrow['area'];

            /* Now have to generate duroplastic SO number.. */
            $TypeNoField = "typeno" . $area;
            $OrderNo = $area . GetNextTransNoTenderType(30, $db, $TypeNoField);

            $sqldebt = "SELECT debtorsmaster.name,
					holdreasons.dissallowinvoices,
					debtorsmaster.salestype,
					salestypes.sales_type,
					debtorsmaster.currcode,
					debtorsmaster.customerpoline,
					paymentterms.terms
			FROM debtorsmaster,
				holdreasons,
				salestypes,
				paymentterms
			WHERE debtorsmaster.salestype=salestypes.typeabbrev
			AND debtorsmaster.holdreason=holdreasons.reasoncode
			AND debtorsmaster.paymentterms=paymentterms.termsindicator
			AND debtorsmaster.debtorno = '" . $debrtonum . "'";

            $resultdebt = DB_query($sqldebt, $db, $ErrMsg);
            $myrowdebt = DB_fetch_array($resultdebt);

            $DelDate = str_replace('.', '/', $item->Est_Del_Date);
            $custContact = "Repeat Customer";
            $comments = "";
            $Quotation = 0;
            $DeliverBlind = 1;
            $packSize = "";
            $FreightCost = 0;
            $salestype = $myrowdebt['salestype'];
            $QuotDate = $DelDate;
            $ConfDate = $DelDate;
            $shipValue = 1;
            $HeaderSQL = 'INSERT INTO salesorders (orderno,
								debtorno,
								branchcode,
								customerref,
                                                                custContact,
								comments,
								orddate,
								ordertype,
								shipvia,
								deliverto,
								deladd1,
								deladd2,
								deladd3,
								deladd4,
								deladd5,
								deladd6,
								contactphone,
								contactemail,
								freightcost,
								fromstkloc,
								deliverydate,
								quotedate,
								confirmeddate,
								quotation,
								deliverblind,
								packsize)
							VALUES (
								' . "'" . $OrderNo . "'" . ',
								' . "'" . $debrtonum . "'" . ',
								' . "'" . $branchcode . "'" . ',';
            if ($_POST['OrderType'] == 1) {
                $PO_NumTemp = trim($item->Site . '-' . $item->PO_Number . '-' . 'RPL');
                $HeaderSQL .= '' . "'" . $PO_NumTemp . "'" . ',';
            } else {
                $PO_NumTemp = trim($item->Site . '-' . $item->PO_Number);
                $HeaderSQL .= '' . "'" . $PO_NumTemp . "'" . ',';
            }

            $HeaderSQL .= '' . "'" . $custContact . "'" . ',
								' . "'" . $comments . "'" . ',
								' . "'" . FormatDateForSQL($item->Order_Date) . "'" . ',
								' . "'" . $myrowdebt['salestype'] . "'" . ',
								' . $shipValue . ',
								' . "'" . $brname . "'" . ',
								' . "'" . $braddress1 . "'" . ',
								' . "'" . $braddress2 . "'" . ',
								' . "'" . $braddress3 . "'" . ',
								' . "'" . $braddress4 . "'" . ',
								' . "'" . $braddress5 . "'" . ',
								' . "'" . $braddress6 . "'" . ',
								' . "'" . $phoneno . "'" . ',
								' . "'" . $email . "'" . ',
								' . $FreightCost . ',
								' . "'" . $area . "'" . ',
								' . "'" . FormatDateForSQL($DelDate) . "'" . ',
								' . "'" . FormatDateForSQL($QuotDate) . "'" . ',
								' . "'" . FormatDateForSQL($ConfDate) . "'" . ',
								' . $Quotation . ',
								' . $DeliverBlind . ',
								' . "'" . $packSize . "'" . '
								)';

            $ErrMsg = _('The order cannot be added because');
            $InsertQryResult = DB_query($HeaderSQL, $db, $ErrMsg);
            $Msg[$OrderNo]['message'] = $item->PO_Number . ", order  " . $OrderNo . " store " . $item->Site;
            //$myArray[$OrderLine->StockID]['qty']
            //JHBSO12053 order 3000457329 store B08 value 1,284.
            echo $HeaderSQL . "<br />";
            $PO_NumTemp = '';
        }//end creting a new sales order number..

        /* Check Prices */
        $sql = "SELECT stockid FROM stockmaster
                                WHERE barcode = '" . trim($item->Barcode) . "'";
        $results = DB_query($sql, $db, $ErrMsg);

        if (DB_num_rows($results) > 0) {
            $myrow = DB_fetch_array($results);
            $StockID = $myrow['stockid'];
        } else {
            $sqlbarcode = "SELECT stockid FROM stockmaster
                                WHERE barcodetwo ='" . trim($item->Barcode) . "'";

            $myresults = DB_query($sqlbarcode, $db, $ErrMsg);
            $myrowstock = DB_fetch_array($myresults);

            $StockID = $myrowstock['stockid'];
        }
        /* Now insert values into tables, every thing is ok... */
        $ItemPrice = (double) $item->Est_Open_Value;
        $Quantity = (int) $item->Open_Quantity;
        $unitprice = $ItemPrice / $Quantity;
        $DiscAmount = 0;
        $narrative = '';
        $poline = '';
        $packdesc = '';

        if ($OrderExist == 0) {


            $Temp_LineItemsSQL = "INSERT INTO temp_salesorderdetails (
											orderlineno,
											orderno,
											stkcode,
											unitprice,
											quantity,
											discountpercent,
											narrative,
											poline,
											itemdue,
											packdesc)
										VALUES (" . $orderlineno . ",
                                                                                        '" . $OrderNo . "',
                                                                                        '" . $StockID . "',
                                                                                        " . number_format($unitprice, 2, '.', '') . ",
                                                                                         " . $Quantity . ",
                                                                                        " . $DiscAmount . ",
                                                                                        '" . $narrative . "',
                                                                                        '" . $poline . "',
                                                                                        '" . FormatDateForSQL($item->Est_Del_Date) . "', 
                                                                                        '" . $packdesc . "')";
            $temp_insert_run = DB_query($Temp_LineItemsSQL, $db, $ErrMsg, $DbgMsg);
            DB_free_result($temp_insert_run);
            //'.', ''
            /***************************************************************************************************************************** */
            $StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (
											orderlineno,
											orderno,
											stkcode,
											unitprice,
											quantity,
											discountpercent,
											narrative,
											poline,
											itemdue,
											packdesc)
										VALUES (" . $orderlineno . ",
                                                                                        '" . $OrderNo . "',
                                                                                        '" . $StockID . "',
                                                                                        " . number_format($unitprice, 2, '.', '') . ",
                                                                                         " . $Quantity . ",
                                                                                        " . $DiscAmount . ",
                                                                                        '" . $narrative . "',
                                                                                        '" . $poline . "',
                                                                                        '" . FormatDateForSQL($item->Est_Del_Date) . "', 
                                                                                        '" . $packdesc . "')";
            $Ordertotals[$OrderNo]['total'] += $unitprice * $Quantity;
            echo "1.." . $StartOf_LineItemsSQL . "<br />";
        } else {

            $SQL = "SELECT salesorders.orderno,salesorders.customerref,salesorders.orderno,salesorderdetails.orderlineno
                               FROM salesorders,salesorderdetails
                               WHERE salesorders.orderno = salesorderdetails.orderno
                               AND (salesorders.customerref='" . $item->PO_Number . "' OR UPPER(salesorders.customerref)= '" . trim($item->Site . '/' . $item->PO_Number) . "'
                               OR UPPER(salesorders.customerref)= '" . trim($item->Site . '-' . $item->PO_Number) . "'
                               OR UPPER(salesorders.customerref) LIKE '%" . trim($item->PO_Number) . "%')
                               ORDER BY salesorderdetails.orderlineno DESC
                               LIMIT 1";
            $ItemResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            $myrowitem = DB_fetch_array($ItemResult);
            $lineno = $myrowitem['orderlineno'] + 1;
            $StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (
											orderlineno,
											orderno,
											stkcode,
											unitprice,
											quantity,
											discountpercent,
											narrative,
											poline,
											itemdue,
											packdesc)
										VALUES (" . $lineno . ",
                                                                                        '" . $myrowitem['orderno'] . "',
                                                                                        '" . $StockID . "',
                                                                                        " . number_format($unitprice, 2) . ",
                                                                                        " . $Quantity . ",
                                                                                        " . $DiscAmount . ",
                                                                                        '" . $narrative . "',
                                                                                        '" . $poline . "',
                                                                                        '" . FormatDateForSQL($item->Est_Del_Date) . "', 
                                                                                        '" . $packdesc . "')";
            echo "2.." . $StartOf_LineItemsSQL . "<br />";
            $Ordertotals[$OrderNo]['total'] += $unitprice * $Quantity;
        }
        $ErrMsg = _('Unable to add the sales order line');
        $DbgMsg = _('SQL Query used was...');

        $Ins_LineItemResult = DB_query($StartOf_LineItemsSQL, $db, $ErrMsg, $DbgMsg);

        if ($OrderExist == 0) {
            $orderlineno++;
        }

        $OrderExist = 0;
    }
    $result = DB_Txn_Commit($db);

    if (!empty($Msg)) {
        $OveralTotal = 0;
        foreach ($Msg as $key => $value) {
            $OveralTotal += $Ordertotals[$key]['total'];
            echo "<div class='centre'><font color='green'>" . $Msg[$key]['message'] . " value  R " . number_format($Ordertotals[$key]['total'], 2, '.', ' ') . "</font></div>";
        }
        echo "<div class='centre'><font color='green'>Batch total for RPLs is  <b> R " . number_format($OveralTotal, 2, '.', ' ') . "</b></font></div>";
    }

    unset($_POST['avoidchecker']);
    unset($_POST['process']);
    unset($_POST['filepath']);
}

/*********************************************************************************************************************************************************************************************/
/*--------------------------------------------------Below if the initial check, before inserts and updates */
/********************************************************************************************************************************************************************************************/



if (isset($_POST['continue'])) {
    $title = _('Customer Purchase Orders ');
    include('includes/header.inc');

    if($_POST['avoidchecker'] == "selection")
    {
        echo "<br /><br />";
        echo "<div class='centre'><font color='green'>Please select one of the options...</div>";
        exit;
    }

    $PathToOrders = "purchaseOrders/";
    $Filename = $_POST['purchaseorder'];
    $completepath = $PathToOrders . $Filename;
    $xml = simplexml_load_file($PathToOrders . $Filename);

    $temp_num = 0;
    $salestype = "";
    $ErroMsg = array();
    $checkItems = 0;
    $msgNotFoundSite = array();
    $ItemsInOrder = array();
    $SelectedDebtor = "";
    //echo "<pre>";
    //  print_r($xml);
    //echo "</pre>";
    //exit;

    echo "<table>";

    foreach ($xml as $item) {
        $xmlPriceCon = 0;
        $ItemPrice = 0;
        $xmlPrice = 0;
        $Rresult = 0;
        $StockID = '';
        $OrderExist = 0;
        if ($temp_num != (int) $item->PO_Number) {
            $temp_num = (int) $item->PO_Number;
            /* Get The Debtor  Info */
            $sql = "SELECT custbranch.brname,
                                custbranch.debtorno,
                                custbranch.branchcode,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.braddress6,
				custbranch.phoneno,
				custbranch.email,
                                custbranch.area,
				custbranch.defaultlocation,
				custbranch.defaultshipvia,
				custbranch.deliverblind,
                                custbranch.specialinstructions,
                                custbranch.estdeliverydays,
                                locations.locationname,
				custbranch.salesman
			FROM custbranch
			INNER JOIN locations
			ON custbranch.defaultlocation=locations.loccode
			WHERE custbranch.branch_num = '" . trim($item->Site) . "'
                        AND custbranch.disabletrans = 0";

            $DebtorResults = DB_query($sql, $db, $ErrMsg);
            $myrow = DB_fetch_array($DebtorResults);
            if (DB_num_rows($DebtorResults) == 0) {
                $msgNotFoundSite[] = "<div class='centre'><font color='green'>$item->Site Not found</font></div>";
            }

            $displayrow = "<tr  style=\" display:block;margin-bottom: 15px;\"></tr>";
            $displayrow .= "<tr style=\"background-color:#99CCFF;\"><td colspan= 5>DEBTOR NO: " . $myrow['debtorno'] . "</td><td colspan=5>Branch Code :" . $myrow['branchcode'] . " @ :" . $myrow['area'] . "</td></tr>";
            $displayrow .="<tr><th>Site/Branch code</th><th>Article</th><th>Item Description</th><th>PO Number</th><th>Barcode</th><th>Quantity</th><th>Value</th><th>Order Date</th><th>EST Delivery Date</th><th>Messages</th></tr>";
            /* Need Debtor sales Info.. */
            $sqldebt = "SELECT debtorsmaster.name,
					holdreasons.dissallowinvoices,
					debtorsmaster.salestype,
					salestypes.sales_type,
					debtorsmaster.currcode,
					debtorsmaster.customerpoline,
					paymentterms.terms
			FROM debtorsmaster,
				holdreasons,
				salestypes,
				paymentterms
			WHERE debtorsmaster.salestype=salestypes.typeabbrev
			AND debtorsmaster.holdreason=holdreasons.reasoncode
			AND debtorsmaster.paymentterms=paymentterms.termsindicator
			AND debtorsmaster.debtorno = '" . $myrow['debtorno'] . "'";

            $resultdebt = DB_query($sqldebt, $db, $ErrMsg);
            $myrowdebt = DB_fetch_array($resultdebt);

            $DelDate = FormatDateforSQL($item->Est_Open_Value);
            $custContact = "Repeat Customer";
            $comments = "";
            $Quotation = 0;
            $DeliverBlind = 1;
            $packSize = "";
            $FreightCost = 0;
            $SelectedDebtor = $myrow['debtorno'];
            $salestype = $myrowdebt['salestype'];
            echo $displayrow;
        }
        /* Check Prices, to compare with xml data */
        $sql = "SELECT stockid FROM stockmaster
                                WHERE barcode = '" . trim($item->Barcode) . "'";
        $results = DB_query($sql, $db, $ErrMsg);
        $myrow = DB_fetch_array($results);

        //$salestype = "EL";
        $sqlPrice = "SELECT stockid,typeabbrev,currabrev,debtorno,price
                    FROM prices
                    WHERE stockid ='" . $myrow['stockid'] . "'
                    AND typeabbrev = '" . $salestype . "'";

        $priceresults = DB_query($sqlPrice, $db, $ErrMsg);
        $pricerow = DB_fetch_array($priceresults);
        //echo '<pre>'.$sqlPrice.'</pre>';
        $msg = "";
        $StockID = $myrow['stockid'];
        $ItemPrice = (float) $pricerow['price'] * floatval($item->Open_Quantity);
        $xmlPrice = floatval($item->Est_Open_Value);
        $Rresult = number_format($ItemPrice, 2) - number_format($xmlPrice, 2);


        if ($myrow['stockid'] != '' && (double) $Rresult == 0) {
            $msg = "Price OK";
        } elseif ($myrow['stockid'] == '') {

            $sqlbarcode = "SELECT stockid FROM stockmaster
                                    WHERE barcodetwo ='" . trim($item->Barcode) . "'";

            $myresults = DB_query($sqlbarcode, $db, $ErrMsg);
            $myrowstock = DB_fetch_array($myresults);

            $StockID = $myrowstock['stockid'];
            $sqlPrice = "SELECT stockid,typeabbrev,currabrev,debtorno,price
                        FROM prices
                        WHERE stockid ='" . $myrowstock['stockid'] . "'
                        AND typeabbrev = '" . $salestype . "'";

            $priceresults = DB_query($sqlPrice, $db, $ErrMsg);
            $pricerow = DB_fetch_array($priceresults);
            $ItemPrice = (float) $pricerow['price'] * floatval($item->Open_Quantity);
            $xmlPrice = floatval($item->Est_Open_Value);
            $Rresult = number_format($ItemPrice, 2) - number_format($xmlPrice, 2);

            if ((double) $Rresult == 0) {
                $msg = "Price OK";
            } else {
                $msg = "NO matching price" . number_format($ItemPrice, 2) . "--" . number_format($xmlPrice, 2);
                $ErroMsg[] = "NO matching price" . number_format($ItemPrice, 2) . "--" . number_format($xmlPrice, 2);
            }
            if ($StockID == '') {
                $msg = "Item not found, barcode does not exist";
                $ErroMsg[] = "Item not found, barcode does not exist";
            }
        } else {
            $msg = "NO matching price $salestype -" . $SelectedDebtor . "" . $pricerow['price'] . "**" . number_format($ItemPrice, 2) . "-2-" . number_format($xmlPrice, 2);
            $ErroMsg[] = "NO matching price $salestype -" . $SelectedDebtor . "" . $pricerow['price'] . "**" . number_format($ItemPrice, 2) . "-2-" . number_format($xmlPrice, 2);
        }

//exit;
        if ($msg == "Price OK") {
            $erroHighlighter = '<tr style="background-color:#3F0;">';
        } elseif ($msg == "NO matching price") {
            $erroHighlighter = '<tr style="background-color:#F36;">';
        } else {
            $erroHighlighter = '<tr style="background-color:#FC3;">';
        }
        if ($_POST['avoidchecker'] == 'New') {
            $SQL = "SELECT salesorders.orderno,salesorders.customerref,salesorderdetails.stkcode
                               FROM salesorders,salesorderdetails
                               WHERE salesorders.orderno = salesorderdetails.orderno
                               AND (salesorders.customerref='" . trim($item->PO_Number) . "' OR UPPER(salesorders.customerref)= '" . trim($item->Site . '/' . $item->PO_Number) . "'
                               OR UPPER(salesorders.customerref)= '" . trim($item->Site . '-' . $item->PO_Number) . "'
                               OR UPPER(salesorders.customerref) LIKE '%" . trim($item->PO_Number) . "%')
                               AND salesorderdetails.stkcode='" . $StockID . "'";

            $salesResults = DB_query($SQL, $db, $ErrMsg);
            $myrow = DB_fetch_array($salesResults);
            if (DB_num_rows($salesResults) > 0) {
                //$displayrow = "";
                $ItemsInOrder[] = "$item->Site : Order Number : $item->PO_Number for Items $StockID Order number :" . $myrow['orderno'] . " , exist/aready in the database";
                continue;
            } else {
                /* Does this order already exist? If yes, is it invoiced yet? If no, add items to it, that are not it the order */
                $SQL = "SELECT salesorders.orderno,salesorders.customerref,salesorders.orderno,salesorderdetails.stkcode
                               FROM salesorders,salesorderdetails
                               WHERE salesorders.orderno = salesorderdetails.orderno
                               AND (salesorders.customerref='" . $item->PO_Number . "' OR UPPER(salesorders.customerref)= '" . trim($item->Site . '/' . $item->PO_Number) . "'
                               OR UPPER(salesorders.customerref)= '" . trim($item->Site . '-' . $item->PO_Number) . "'
                               OR UPPER(salesorders.customerref) LIKE '%" . trim($item->PO_Number) . "%')";

                $salesResults = DB_query($SQL, $db, $ErrMsg);
                if (DB_num_rows($salesResults) > 0) {
                    /* This order does exist, is it invoiced yet??? */
                    $myrow = DB_fetch_array($salesResults);
                    $OrderExist = 1;
                    $msg = "Item($StockID) not found in this order " . $myrow['orderno'];
                    $SQL = "SELECT debtortrans.transno,debtortrans.order_
                                FROM debtortrans
                                WHERE debtortrans.order_ = '" . $myrow['orderno'] . "'";
                    $ErrMsg = _('No transactions were returned by the SQL because');
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    if (DB_num_rows($TransResult) > 0) {
                        $myrow = DB_fetch_array($TransResult);

                        $msg .= " Invoiced(" . $myrow['transno'] . ")";
                        $ErroMsg[] = " Invoiced(" . $myrow['transno'] . ")";
                    }
                }
            }
        }//end overide check

        echo $erroHighlighter;
        echo "<td>" . $item->Site . "</td><td> " . $item->Article . "</td><td>" .
            $item->Article_Description . "</td><td>" .
            $item->PO_Number . "</td><td>" .
            $item->Barcode . "</td><td>" .
            $item->Open_Quantity . "</td><td>" .
            $item->Est_Open_Value . "</td><td>" .
            FormatDateForSQL($item->Order_Date) . "</td><td>" .
            FormatDateForSQL($item->Est_Del_Date) . "</td><td>" . $msg . " " . $SelectedDebtor . "</td></tr>";

        $checkItems++;
    }
    echo "</table><br />";
    if (empty($ErroMsg) && $checkItems != 0 && empty($msgNotFoundSite)) {
        echo '<input type=hidden name=filepath value ="' . $completepath . '">';
        echo '<input type=hidden name=avoidchecker value ="' . $_POST['avoidchecker'] . '">';
        echo "<div class='centre'><b>" . _('Order Type') . ':</b><select name=OrderType>';
        echo '<option value="1">RPLs</option>
              <option value="2">Sales Order</option>';
        echo '</select></div>';
        echo "<br />";
        echo "<div class='centre'><input type=Submit Name='process' Value='" . _('Process Purchase') . "'onclick=\"return confirm('" . _('Are you sure you want to process this order(s)?') . '\');"/></div>';
    } elseif ($checkItems == 0) {
        echo "<div class='centre'><font color='green'>No xml data found/Orders Already Processed..</font></div>";
    } elseif (!empty($msgNotFoundSite)) {
        for ($i = 0; $i < count($msgNotFoundSite); $i++) {
            //echo "<div class='centre'><font color='green'>".$ItemsInOrder[$i]."</div>";
            echo "<div class='centre'><font color='green'>" . $msgNotFoundSite[$i] . "</div>";
            //$msgNotFoundSite[$i];
        }
    }
} else {
    $title = _('Customer Purchase Orders ');
    include('includes/header.inc');

    echo "<br />";
    echo '<table>';
    echo '<tr><td>' . _('Select Purchase Order') . ':</td><td><select name=purchaseorder>';

    $opendir = "purchaseOrders/";

    if ($handle = opendir($opendir)) {
        while (false !== ($file = readdir($handle))) {
            //first see if this file is required in the listing
            if ($file == "." || $file == "..")
                continue;
            echo "<option value='" . trim($file) . "'>" . $file;
        }
        closedir($handle);
    }
    echo '</select></td></tr>';
    if (in_array(8, $_SESSION['AllowedPageSecurityTokens'])) {
        echo '<tr><td>' . _('Upload to New Order') . ':</td><td><select name=avoidchecker>';
        echo "<option value='selection'>" . _('- Please make selection- ');
        echo "<option value='New'>" . _('Order to New Order sales order');
        echo "<option value='Overide'>" . _('Overide checks');
        // echo "<option value='Old'>" . _('Order to  existing sales order');
        echo '</select></td></tr>';
    }
    echo '</table>';
    echo "<div class='centre'><input type=Submit Name='continue' Value='" . _('Continue>>>') . "'></div>";
}
echo "</form>";
include('includes/footer.inc');
?>
