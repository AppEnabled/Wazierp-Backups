

<style type="text/css">

    .Link-button
    {
        width: 85px;
        height: 20px;
        background-color: #058633;
        text-decoration: none;
        color:white;
        font-size: 14px;
        border-radius: 4px;
        -moz-border-radius: 4px;
        -webkit-border-radius: 4px;
    }
</style>

<style>
	.ui-autocomplete-loading {
		background: white url('counter_module/images/ui-anim_basic_16x16.gif') right center no-repeat;
	}
</style>
<?php
/* $Id: CounterSales.php 2011-01-27   Douglas */

include('includes/DefineCartClass.php');
include('includes/DefineReceiptClass.php');

// $PageSecurity = 1;

/* Session started in session.inc for password checking and authorisation level check
  config.php is in turn included in session.inc // $PageSecurity now comes from session.inc (and gets read in by GetConfig.php */

include('includes/session.inc');

$title = _('Counter Sales');

include('includes/header.inc');

include('includes/GetPrice.inc');
include('includes/SQL_CounterSales.inc');
include('includes/GetSalesTransGLCodes.inc');

$AlreadyWarnedAboutCredit = false;

if (empty($_GET['identifier'])) {
    $identifier = date('U');
} else {
    $identifier = $_GET['identifier'];
}
if (isset($_SESSION['Items' . $identifier]) AND isset($_POST['CustRef'])) {
    //update the Items object variable with the data posted from the form
    $_SESSION['Items' . $identifier]->CustRef = $_POST['CustRef'];
    $_SESSION['Items' . $identifier]->Comments = $_POST['Comments'];
    $_SESSION['Items' . $identifier]->DeliverTo = $_POST['DeliverTo'];
    $_SESSION['Items' . $identifier]->PhoneNo = $_POST['PhoneNo'];
    $_SESSION['Items' . $identifier]->Email = $_POST['Email'];
    $_SESSION['Items' . $identifier]->CustRefVia = $_POST['Codes'];

    /* Ajax/javascripts posts */
    //if($_POST['CustCodeNum']!=''){
        
    //}
}
if(isset($_POST['CustCodeNum'])){
    
    
         $_SESSION['Items' . $identifier]->Address1 = $_POST['Address1'];
        $_SESSION['Items' . $identifier]->StreetAddress1 = $_POST['StreetAdd1'];
        $_SESSION['Items' . $identifier]->CustCodeNum = $_POST['CustCodeNum'];
}
/* Explode quantiy from ajax-dynamic, Tamelo Douglas */
if (isset($_POST['StockCode'])) {
    $separate = explode("_QTY:_", trim(strtoupper($_POST['StockCode'])));
    if ($_POST['StockCode'] != $separate[0]) {
        $_POST['StockCode'] = $separate[0];
    } else {
        $_POST['StockCode'] = $_POST['StockCode'];
    }
}

if (isset($_POST['QuickEntry'])) {
    unset($_POST['PartSearch']);
}

/*********************************************/
/* Tamelo Douglas*/
if (isset($_POST['BarCode']))
{
  unset($_SESSION['defaultfield']);// = 'BarCode';
  unset($_POST['Search']);
  unset ($NewItemArray);
  unset ($NewBItem_array);
   $ItemInArray = 0;
   $_POST['SelectingOrderItems'] = $_POST['BarCode'];
    	if (!isset($NewBItem_array)) {
		$NewBItem_array = array();
	}
      $_SESSION['defaultfield'] = 'BarCode';
      
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.barcode,
                    stockmaster.mbflag,
                    SUM(locstock.quantity) AS qoh,
                    stockmaster.units,
                    stockmaster.decimalplaces
                FROM stockmaster,
                    locstock
                WHERE stockmaster.stockid=locstock.stockid
                AND (stockmaster.barcode = '" . $_POST['BarCode'] . "'
                OR   stockmaster.barcodetwo = '" . $_POST['BarCode'] . "')
                    
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                ORDER BY stockmaster.stockid";

    $Result = DB_query($SQL,$db,$ErrMsg);
    $myrow = DB_fetch_array($Result);
    $Rowrsult = DB_fetch_array($Result);
    $NewItem =  $Rowrsult['stockid'];
    if (DB_num_rows($Result) == 0) {
            prnMsg (_('There are no products available meeting the criteria specified'), 'info');

        }

   $Units = 1;
   $PackSize = 1;
   $QuantityLine = 1;

       foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
                if($OrderLine->StockID == $myrow['stockid'])
                {

                  $ItemInArray  = 1 ;
                  $DiscountPercentage = filter_number_format($_POST['Discount_' . $OrderLine->LineNumber]);
		  $_POST['Quantity_' . $OrderLine->LineNumber] =  $_POST['Quantity_' . $OrderLine->LineNumber] + 1;
                  $_SESSION['Items'.$identifier]->update_cart_item($OrderLine->LineNumber,
																$_POST['Quantity_' . $OrderLine->LineNumber],
																$OrderLine->Price,
																$DiscountPercentage/100,
																$OrderLine->Narrative,
																'Yes', /*Update DB */
																$OrderLine->ItemDue,
																$OrderLine->POLine ,
																filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]));
                }
       }
     if($ItemInArray == 0)
     {
      $NewBItem_array[] = array( $myrow['stockid'], $Units, $PackSize, $QuantityLine);
              
     }
                unset($myrow['stockid']);
                unset($QuantityLine);
    		unset($Units);
    		unset($PackSize);
                
}

/********************************************/


/* Tamelo Douglas */
if (isset($_POST['SelectingOrderItems'])) {
    //echo "<br>ident is " . print_r($_SESSION['Items' . $identifier]->LineItems) . "<br>";
     unset($_SESSION['defaultfield']);// = 'BarCode';
    $count = 0;
    if (!isset($NewItemArray)) {
        $NewItemArray = array();
    }
    $_SESSION['defaultfield'] = 'StockCode';
    foreach ($_POST as $key => $value) {

        //echo "<br><br>post is<br><br>";
        //	echo "key is " . $key . "<br>";
        //echo "value is " . $value . "<br>";
        if (strstr($key, "itm") && $value > 0) {
            //echo "itm count is " . $count . "<br>";
            $Stockid = substr($key, 3);
            $QuantityLine = trim($value);
        }
        if (strstr($key, "unit")) {
            if ($value > 0) {
                $Units = trim($value);
            }
        }

        if (strstr($key, "pack")) {
            if ($value > 0) {
                $PackSize = trim($value);
                //echo "<br>packsize " . $PackSize . "<br>"; Intransit
                //$NewItem_array[$Stockid]['Packsize'] = $PackSize;
                //$PackSizeArray[substr($key, 4)]['pack'] = trim($value);
                //echo "<br><br>pack value is " . $value . "<br><br>";
            }
            //echo "pack count is " . $count . "<br>";
        }
        if (strstr($key, "Intransit")) {
            if ($value > 0) {
                $Intransit = trim($value);
                $Units = $Units - $Intransit;
            }
        }

        $count = ++$count;
        //unset($itemname);
        if ($QuantityLine > 0) {
            // echo "<br>q line isss " . $QuantityLine . "<br>";
            $NewItemArray[] = array($Stockid, $Units, $PackSize, $QuantityLine);
            unset($Stockid);
            unset($QuantityLine);
            unset($Units);
            unset($PackSize);
        }
    }
}

if (isset($_GET['NewItem'])) {
    $NewItem = trim($_GET['NewItem']);
}

if (isset($_GET['NewOrder'])) {
    /* New order entry - clear any existing order details from the Items object and initiate a newy */
    if (isset($_SESSION['Items' . $identifier])) {
        unset($_SESSION['Items' . $identifier]->LineItems);
        $_SESSION['Items' . $identifier]->ItemsOrdered = 0;
        unset($_SESSION['Items' . $identifier]);
    }
}


if (!isset($_SESSION['Items' . $identifier])) {
    /* It must be a new order being created $_SESSION['Items'.$identifier] would be set up from the order
      modification code above if a modification to an existing order. Also $ExistingOrder would be
      set to 1. The delivery check screen is where the details of the order are either updated or
      inserted depending on the value of ExistingOrder */

    $_SESSION['ExistingOrder'] = 0;
    $_SESSION['Items' . $identifier] = new cart;
    $_SESSION['PrintedPackingSlip'] = 0; /* Of course 'cos the order ain't even started !! */

 
   if($_SESSION['Salesmanrepcashcode'] != "" && $_SESSION['Salesmanrepcashlocation'] == $_SESSION['UserStockLocation']){
        $_POST['CustCode'] = $_SESSION['Salesmanrepcashcode'];
        
                $_POST['CustCode'] = strtoupper(trim($_POST['CustCode']));

                $SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.area,
					custbranch.branchcode,
					custbranch.debtorno
				FROM custbranch
				WHERE (custbranch.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%' OR custbranch.branchcode " . LIKE . " '%" . $_POST['CustCode'] . "%')";
                $SQL .= " AND custbranch.area='" . $_SESSION['UserStockLocation'] . "'";
                if ($_SESSION['SalesmanLogin'] != '') {
                    $SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
                }
                $SQL .= ' AND custbranch.disabletrans=0
						ORDER BY custbranch.debtorno';
                
            $ErrMsg = _('The searched customer records requested cannot be retrieved because');
            
            $result_CustSelect = DB_query($SQL, $db, $ErrMsg);

            if (DB_num_rows($result_CustSelect) == 1) {
                $myrow = DB_fetch_array($result_CustSelect);
                $_POST['Select'] = $myrow['debtorno'] . ' - ' . $myrow['branchcode'];
                $_SESSION['Items' . $identifier]->DebtorNo = $_POST['Select'];
                $_SESSION['Items' . $identifier]->DebtorNo = $myrow['debtorno'];
                $_SESSION['Items' . $identifier]->Branch = $myrow['branchcode'];
            } elseif (DB_num_rows($result_CustSelect) == 0) {
               // prnMsg(_('No Customer Branch records contain the search criteria') . ' - ' . _('please try again') . ' - ' . _('Note a Customer Branch Name may be different to the Customer Name'), 'info');
                prnMsg(_('Could not autoload your cash code') . ' - ' . $_POST['CustCode'].' , '. _('please try again') . ' - ' . _('Contact Admin/IT to assist. SalesPeople'), 'info');
            }
                
    }
    
    if (isset($_POST['SearchCust'])) {
        if (($_POST['CustKeywords'] != '') AND (($_POST['CustCode'] != '') OR ($_POST['CustPhone'] != ''))) {
            prnMsg(_('Customer Branch Name keywords have been used in preference to the Customer Branch Code or Branch Phone Number entered'), 'warn');
        }
        if (($_POST['CustCode'] != '') AND ($_POST['CustPhone'] != '')) {
            prnMsg(_('Customer Branch Code has been used in preference to the Customer Branch Phone Number entered'), 'warn');
        }
        if (($_POST['CustKeywords'] == '') AND ($_POST['CustCode'] == '') AND ($_POST['CustPhone'] == '')) {
            prnMsg(_('At least one Customer Branch Name keyword OR an extract of a Customer Branch Code or Branch Phone Number must be entered for the search'), 'warn');
        } else {
            if (strlen($_POST['CustKeywords']) > 0) {
                // insert wildcard characters in spaces
                $_POST['CustKeywords'] = strtoupper(trim($_POST['CustKeywords']));
                $i = 0;
                $SearchString = '%';
                while (strpos($_POST['CustKeywords'], ' ', $i)) {
                    $wrdlen = strpos($_POST['CustKeywords'], ' ', $i) - $i;
                    $SearchString = $SearchString . substr($_POST['CustKeywords'], $i, $wrdlen) . '%';
                    $i = strpos($_POST['CustKeywords'], ' ', $i) + 1;
                }
                $SearchString = $SearchString . substr($_POST['CustKeywords'], $i) . '%';
                /* Add to sql to only select customers for that location PDT */
                $SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.area,
					custbranch.branchcode,
					custbranch.debtorno
				FROM custbranch
				WHERE custbranch.brname " . LIKE . " '$SearchString'";
                $SQL .= " AND custbranch.area='" . $_SESSION['UserStockLocation'] . "'";
                if ($_SESSION['SalesmanLogin'] != '') {
                    $SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
                }
                $SQL .= ' AND custbranch.disabletrans=0
						ORDER BY custbranch.debtorno, custbranch.branchcode';
            } elseif (strlen($_POST['CustCode']) > 0) {

                $_POST['CustCode'] = strtoupper(trim($_POST['CustCode']));

                $SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.area,
					custbranch.branchcode,
					custbranch.debtorno
				FROM custbranch
				WHERE (custbranch.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%' OR custbranch.branchcode " . LIKE . " '%" . $_POST['CustCode'] . "%')";
                $SQL .= " AND custbranch.area='" . $_SESSION['UserStockLocation'] . "'";
                if ($_SESSION['SalesmanLogin'] != '') {
                    $SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
                }
                $SQL .= ' AND custbranch.disabletrans=0
						ORDER BY custbranch.debtorno';
            } elseif (strlen($_POST['CustPhone']) > 0) {
                $SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.area,
					custbranch.branchcode,
					custbranch.debtorno
				FROM custbranch
				WHERE custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'";

                if ($_SESSION['SalesmanLogin'] != '') {
                    $SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
                }

                $SQL .= ' AND custbranch.disabletrans=0
						ORDER BY custbranch.debtorno';
            }


            //echo "<br>SQl is " . $SQL . "<br>";
            $ErrMsg = _('The searched customer records requested cannot be retrieved because');
            $result_CustSelect = DB_query($SQL, $db, $ErrMsg);

            if (DB_num_rows($result_CustSelect) == 1) {
                $myrow = DB_fetch_array($result_CustSelect);
                $_POST['Select'] = $myrow['debtorno'] . ' - ' . $myrow['branchcode'];
                $_SESSION['Items' . $identifier]->DebtorNo = $_POST['Select'];
                $_SESSION['Items' . $identifier]->DebtorNo = $myrow['debtorno'];
                $_SESSION['Items' . $identifier]->Branch = $myrow['branchcode'];
            } elseif (DB_num_rows($result_CustSelect) == 0) {
                prnMsg(_('No Customer Branch records contain the search criteria') . ' - ' . _('please try again') . ' - ' . _('Note a Customer Branch Name may be different to the Customer Name'), 'info');
            }
        }
        /* one of keywords or custcode was more than a zero length string */
    }
  
    /* end of if search for customer codes/names */
// will only be true if page called from customer selection form or set because only one customer
// record returned from a search so parse the $Select string into customer code and branch code */
    if (isset($_POST['Select']) AND $_POST['Select'] != '') {


        $_SESSION['Items' . $identifier]->Branch = substr($_POST['Select'], strpos($_POST['Select'], ' - ') + 3);

        $_POST['Select'] = substr($_POST['Select'], 0, strpos($_POST['Select'], ' - '));
        // Now check to ensure this account is not on hold */
        $sql = "SELECT debtorsmaster.name,
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
			AND debtorsmaster.debtorno = '" . $_POST['Select'] . "'";

        $ErrMsg = _('The details of the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
        $DbgMsg = _('The SQL used to retrieve the customer details and failed was') . ':';
        $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

        $myrow = DB_fetch_row($result);
        if ($myrow[1] != 1) {
            if ($myrow[1] == 2) {
                prnMsg(_('The') . ' ' . $myrow[0] . ' ' . _('account is currently flagged as an account that needs to be watched. Please contact the credit control personnel to discuss'), 'warn');
            }

            $_SESSION['Items' . $identifier]->DebtorNo = $_POST['Select'];
            $_SESSION['RequireCustomerSelection'] = 0;
            $_SESSION['Items' . $identifier]->CustomerName = $myrow[0];
            // the sales type determines the price list to be used by default the customer of the user is
            // defaulted from the entry of the userid and password.
            $_SESSION['Items' . $identifier]->DefaultSalesType = $myrow[2];
            $_SESSION['Items' . $identifier]->SalesTypeName = $myrow[3];
            $_SESSION['Items' . $identifier]->DefaultCurrency = $myrow[4];
            $_SESSION['Items' . $identifier]->DefaultPOLine = $myrow[5];
            $_SESSION['Items' . $identifier]->PaymentTerms = $myrow[6];
            $_SESSION['Items' . $identifier]->CurrDecimalPlaces = 2;
            // the branch was also selected from the customer selection so default the delivery details from the customer branches table CustBranch. The order process will ask for branch details later anyway
            $sql = "SELECT custbranch.brname,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.braddress6,
				custbranch.phoneno,
				custbranch.email,
				custbranch.defaultlocation,
				custbranch.defaultshipvia,
				custbranch.deliverblind,
                custbranch.specialinstructions,
                custbranch.estdeliverydays,
                locations.locationname,
				custbranch.salesman,
				custbranch.taxgroupid
			FROM custbranch
			INNER JOIN locations
			ON custbranch.defaultlocation=locations.loccode
			WHERE custbranch.branchcode='" . $_SESSION['Items' . $identifier]->Branch . "'
			AND custbranch.debtorno = '" . $_POST['Select'] . "'";
//echo "<br>sql line 522 is " . $sql . "<br>";
            $ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
            $DbgMsg = _('SQL used to retrieve the branch details was') . ':';
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

            if (DB_num_rows($result) == 0) {
                prnMsg(_('The branch details for branch code') . ': ' . $_SESSION['Items' . $identifier]->Branch . ' ' . _('against customer code') . ': ' . $_POST['Select'] . ' ' . _('could not be retrieved') . '. ' . _('Check the set up of the customer and branch'), 'error');

                if ($debug == 1) {
                    echo '<br>' . _('The SQL that failed to get the branch details was') . ':<br>' . $sql;
                }
                include('includes/footer.inc');
                exit;
            }
            // add echo
            echo '<br>';
            $myrow = DB_fetch_row($result);
            if ($_SESSION['SalesmanLogin'] != '' AND $_SESSION['SalesmanLogin'] != $myrow[15]) {
                prnMsg(_('Your login is only set up for a particular salesperson. This customer has a different salesperson.'), 'error');
                include('includes/footer.inc');
                exit;
            }

            $_SESSION['Items' . $identifier]->DeliverTo = $myrow[0];
            $_SESSION['Items' . $identifier]->DelAdd1 = $myrow[1];
            $_SESSION['Items' . $identifier]->DelAdd2 = $myrow[2];
            $_SESSION['Items' . $identifier]->DelAdd3 = $myrow[3];
            $_SESSION['Items' . $identifier]->DelAdd4 = $myrow[4];
            $_SESSION['Items' . $identifier]->DelAdd5 = $myrow[5];
            $_SESSION['Items' . $identifier]->DelAdd6 = $myrow[6];
            $_SESSION['Items' . $identifier]->PhoneNo = $myrow[7];
            $_SESSION['Items' . $identifier]->Email = $myrow[8];
            $_SESSION['Items' . $identifier]->Location = $_SESSION['UserStockLocation'];
            $_SESSION['Items' . $identifier]->ShipVia = $myrow[10];
            $_SESSION['Items' . $identifier]->DeliverBlind = $myrow[11];
            $_SESSION['Items' . $identifier]->SpecialInstructions = $myrow[12];
            $_SESSION['Items' . $identifier]->DeliveryDays = $myrow[13];
            $_SESSION['Items' . $identifier]->LocationName = $myrow[14];
            $_SESSION['Items' . $identifier]->TaxGroup = $myrow[16];
            $_SESSION['Items' . $identifier]->DispatchTaxProvince = 1;

            if ($_SESSION['Items' . $identifier]->SpecialInstructions)
                prnMsg($_SESSION['Items' . $identifier]->SpecialInstructions, 'warn');

            if ($_SESSION['CheckCreditLimits'] > 0) {
                /* Check credit limits is 1 for warn and 2 for prohibit sales */
                $_SESSION['Items' . $identifier]->CreditAvailable = GetCreditAvailable($_POST['Select'], $db);

                if ($_SESSION['CheckCreditLimits'] == 1 AND $_SESSION['Items' . $identifier]->CreditAvailable <= 0) {
                    prnMsg(_('The') . ' ' . $myrow[0] . ' ' . _('account is currently at or over their credit limit'), 'warn');
                } elseif ($_SESSION['CheckCreditLimits'] == 2 AND $_SESSION['Items' . $identifier]->CreditAvailable <= 0) {
                    prnMsg(_('No more orders can be placed by') . ' ' . $myrow[0] . ' ' . _(' their account is currently at or over their credit limit'), 'warn');
                    include('includes/footer.inc');
                    exit;
                }
            }
        } else {
            prnMsg(_('The') . ' ' . $myrow[0] . ' ' . _('account is currently on hold please contact the credit control personnel to discuss'), 'warn');
        }
    } elseif (!$_SESSION['Items' . $identifier]->DefaultSalesType OR $_SESSION['Items' . $identifier]->DefaultSalesType == '') {

        // Possible that the check to ensure this account is not on hold has not been done
        // if the customer is placing own order, if this is the case then
        // DefaultSalesType will not have been set as above
        $sql = "SELECT debtorsmaster.name,
					holdreasons.dissallowinvoices,
					debtorsmaster.salestype,
					debtorsmaster.currcode,
					debtorsmaster.customerpoline
			FROM debtorsmaster, holdreasons
			WHERE debtorsmaster.holdreason=holdreasons.reasoncode
			AND debtorsmaster.debtorno = '" . $_SESSION['Items' . $identifier]->DebtorNo . "'";

        if (isset($_POST['Select'])) {
            $ErrMsg = _('The details for the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
        } else {
            $ErrMsg = '';
        }
        $DbgMsg = _('SQL used to retrieve the customer details was') . ':<br>' . $sql;
        $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

        $myrow = DB_fetch_row($result);
        if ($myrow[1] == 0) {
            $_SESSION['Items' . $identifier]->CustomerName = $myrow[0];
            // the sales type determines the price list to be used by default the customer of the user is
            // defaulted from the entry of the userid and password.
            $_SESSION['Items' . $identifier]->DefaultSalesType = $myrow[2];
            $_SESSION['Items' . $identifier]->DefaultCurrency = $myrow[3];
            $_SESSION['Items' . $identifier]->Branch = $_SESSION['UserBranch'];
            $_SESSION['Items' . $identifier]->DefaultPOLine = $myrow[4];
            // the branch would be set in the user data so default delivery details as necessary. However,
            // the order process will ask for branch details later anyway
            $sql = "SELECT custbranch.brname,
						custbranch.braddress1,
						custbranch.braddress2,
						custbranch.braddress3,
						custbranch.braddress4,
						custbranch.braddress5,
						custbranch.braddress6,
						custbranch.phoneno,
						custbranch.email,
						custbranch.defaultlocation,
						custbranch.deliverblind,
						custbranch.estdeliverydays,
						locations.locationname,
						custbranch.taxgroupid
				FROM custbranch INNER JOIN locations
				ON custbranch.defaultlocation=locations.loccode
				WHERE custbranch.branchcode='" . $_SESSION['Items' . $identifier]->Branch . "'
				AND custbranch.debtorno = '" . $_SESSION['Items' . $identifier]->DebtorNo . "'";
//echo "<br>sql line 629 is " . $sql . "<br>";
            if (isset($_POST['Select'])) {
                $ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
            } else {
                $ErrMsg = '';
            }
            $DbgMsg = _('SQL used to retrieve the branch details was');
            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

            $myrow = DB_fetch_row($result);
            $_SESSION['Items' . $identifier]->DeliverTo = $myrow[0];
            $_SESSION['Items' . $identifier]->DelAdd1 = $myrow[1];
            $_SESSION['Items' . $identifier]->DelAdd2 = $myrow[2];
            $_SESSION['Items' . $identifier]->DelAdd3 = $myrow[3];
            $_SESSION['Items' . $identifier]->DelAdd4 = $myrow[4];
            $_SESSION['Items' . $identifier]->DelAdd5 = $myrow[5];
            $_SESSION['Items' . $identifier]->DelAdd6 = $myrow[6];
            $_SESSION['Items' . $identifier]->PhoneNo = $myrow[7];
            $_SESSION['Items' . $identifier]->Email = $myrow[8];
            $_SESSION['Items' . $identifier]->Location = $myrow[9];
            $_SESSION['Items' . $identifier]->DeliverBlind = $myrow[10];
            $_SESSION['Items' . $identifier]->DeliveryDays = $myrow[11];
            $_SESSION['Items' . $identifier]->LocationName = $myrow[12];

            //$_SESSION['Items'.$identifier]->DeliverTo = '';
            $_SESSION['Items' . $identifier]->DelAdd1 = $myrow[1];
            $_SESSION['Items' . $identifier]->ShipVia = 1;

            $_SESSION['Items' . $identifier]->SpecialInstructions = '';


            $_SESSION['Items' . $identifier]->TaxGroup = $myrow[13];
            echo $_SESSION['Items' . $identifier]->TaxGroup;
            $_SESSION['Items' . $identifier]->DispatchTaxProvince = 1;
        } else {
            prnMsg(_('Sorry, your account has been put on hold for some reason, please contact the credit control personnel.'), 'warn');
            include('includes/footer.inc');
            exit;
        }
    }

    // END OPERATION SELECT USER ------------------------------------------------------------------
    //        select user ---------------------------------------------------------------------
    if ($_SESSION['RequireCustomerSelection'] == 1
            OR !isset($_SESSION['Items' . $identifier]->DebtorNo)
            OR $_SESSION['Items' . $identifier]->DebtorNo == '') {
        //if(!isset($_SESSION['Items'.$identifier]->CustomerName) OR $_SESSION['Items'.$identifier]->CustomerName ==''){

        echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="">' .
        ' ' . _('CASH SALE  ') . ' : ' . _(' POINT OF SALE ') . '</p>';
        echo '<div class="page_help_text">' . _('This module is for Cash Customers only. No account customers will reflect . Payment must be tendered. Look up Branch  Code ') . '</div>';
        ?>

        <form action="<?php echo $_SERVER['PHP_SELF'] . '?' . SID . 'identifier'; ?>" name="SelectCustomer" method=post>
            <b><?php echo '<p>' . $msg; ?></p>
                <table cellpadding=3 colspan=4>
                    <tr>
                        <td><h5><?php echo _('Part of the Customer Branch Code'); ?>:</h5></td>
                        <td><input tabindex=1 onfocus="setBKColor(event);" onblur="reSetBKColor(event);" type="Text" onkeyup="ajax_showOptions(this,'getCustomer',event)" name="CustCode" size=15	maxlength=18 autocomplete=off></td>
                        <td><h3><b><?php echo _('OR'); ?></b></h3></td>
                        <td><h5><?php echo _('Part of the Customer Name'); ?>:</h5></td>
                        <td><input tabindex=2 onfocus="setBKColor(event);" onblur="reSetBKColor(event);" type="Text" name="CustKeywords" size=20	maxlength=25></td>

                    </tr>
                </table>
                <br><div class="centre"><input tabindex=4 type=submit  name="SearchCust" value="<?php echo _('Search Now'); ?>">
                    <input tabindex=5 type=submit action=reset value="<?php echo _('Reset'); ?>"></div>
        <?php
        include('includes/footer.inc');
        exit();
    }
} // end if its a new sale to be set up ...

if (isset($_POST['CancelOrder'])) {


    unset($_SESSION['Items' . $identifier]->LineItems);
    $_SESSION['Items' . $identifier]->ItemsOrdered = 0;
    unset($_SESSION['Items' . $identifier]);
    $_SESSION['Items' . $identifier] = new cart;

    echo '<br /><br />';
    prnMsg(_('This sale has been cancelled as requested'), 'success');
    echo '<br /><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">' . _('Start a new Counter Sale') . '</a>';
    include('includes/footer.inc');
    exit;
} else { /* Not cancelling the order */


    echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/inventory.png" title="' . _('Counter Sales') . '" alt="" />' . ' ';
    echo $_SESSION['Items' . $identifier]->CustomerName . ' ' . _(' : Cash Customer using Location :') . $_SESSION['Items' . $identifier]->LocationName . ' (' . _('with Currency ') . ' ' . $_SESSION['Items' . $identifier]->DefaultCurrency . ')' . _(' Price List used : ') . $_SESSION['Items' . $identifier]->DefaultSalesType;
    echo '</p>';
}

if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev']) || $_POST['StockCode']!='') {

    if ($_POST['Keywords'] != '' AND $_POST['StockCode'] == '') {
        $msg = '<div class="page_help_text">' . _('Item description has been used in search') . '.</div>';
    } else if ($_POST['StockCode'] != '' AND $_POST['Keywords'] == '') {
        $msg = '<div class="page_help_text">' . _('Item Code has been used in search') . '.</div>';
    } else if ($_POST['Keywords'] == '' AND $_POST['StockCode'] == '') {
        $msg = '<div class="page_help_text">' . _('Stock Category has been used in search') . '.</div>';
    }
    if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords']) > 0) {
        //insert wildcard characters in spaces
        $_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
        $SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

        if ($_POST['StockCat'] == 'All') {
            $SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						stockmaster.decimalplaces   
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.mbflag <>'G'
                                         AND stockmaster.retailitem  = 'Y'
					AND stockmaster.controlled <> 1
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.discontinued=0
					ORDER BY stockmaster.stockid";
        } else {
            $SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						stockmaster.decimalplaces 
					FROM stockmaster INNER JOIN stockcategory
					ON  stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.mbflag <>'G'
                                        AND stockmaster.retailitem  = 'Y'
					AND stockmaster.controlled <> 1
					AND stockmaster.discontinued=0
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
        }
    } else if (mb_strlen($_POST['StockCode']) > 0) {

        $_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
        $SearchString = '%' . $_POST['StockCode'] . '%';

        if ($_POST['StockCat'] == 'All') {
            $SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN stockcategory
					  ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
					AND stockmaster.mbflag <>'G'
                                        AND stockmaster.retailitem  = 'Y'
					AND stockmaster.controlled <> 1
					AND stockmaster.discontinued=0
					ORDER BY stockmaster.stockid";
        } else {
            $SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
					AND stockmaster.mbflag <>'G'
                                         AND stockmaster.retailitem  = 'Y'
					AND stockmaster.controlled <> 1
					AND stockmaster.discontinued=0
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
        }
    } else {
        if ($_POST['StockCat'] == 'All') {
            $SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster INNER JOIN stockcategory
					ON  stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.mbflag <>'G'
                                        AND stockmaster.retailitem  = 'Y'
					AND stockmaster.controlled <> 1
					AND stockmaster.discontinued=0
					ORDER BY stockmaster.stockid";
        } else {
            $SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						stockmaster.decimalplaces           
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.mbflag <>'G'
                                        AND stockmaster.retailitem  = 'Y'
					AND stockmaster.controlled <> 1
					AND stockmaster.discontinued=0
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
        }
    }

    if (isset($_POST['Next'])) {
        $Offset = $_POST['NextList'];
    }
    if (isset($_POST['Prev'])) {
        $Offset = $_POST['previous'];
    }
    if (!isset($Offset) or $Offset < 0) {
        $Offset = 0;
    }
    $SQL = $SQL . ' LIMIT ' . $_SESSION['DefaultDisplayRecordsMax'] . ' OFFSET ' . strval($_SESSION['DefaultDisplayRecordsMax'] * $Offset);

    $ErrMsg = _('There is a problem selecting the part records to display because');
    $DbgMsg = _('The SQL used to get the part selection was');
    $SearchResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

    if (DB_num_rows($SearchResult) == 0) {
        prnMsg(_('There are no products available meeting the criteria specified'), 'info');
    }
    if (DB_num_rows($SearchResult) == 1) {
        $myrow = DB_fetch_array($SearchResult);
        $NewItem = $myrow['stockid'];
        DB_data_seek($SearchResult, 0);
    }
    if (DB_num_rows($SearchResult) < $_SESSION['DisplayRecordsMax']) {
        $Offset = 0;
    }
     $_POST['StockCode']='';
} //end of if search
?>
              
<!--        <link rel="stylesheet" href="counter_module/css/jquery.ui.all.css">
        
        <link rel="stylesheet" href="counter_module/css/demos.css">-->
<!--        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.0/themes/base/jquery-ui.css" />-->

        <link rel="stylesheet" href="counter_module/css/jquery-ui.css">
     	<script src="counter_module/jquery-1.9.0.js"></script>
	<script src="counter_module/js/jquery.ui.core.js"></script>
	<script src="counter_module/js/jquery.ui.widget.js"></script>
	<script src="counter_module/js/jquery.ui.position.js"></script>
	<script src="counter_module/js/jquery.ui.menu.js"></script>
     
	<script src="counter_module/js/jquery.ui.autocomplete.js"></script>
               
<!--<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.0/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.8.3.js"></script>
<script src="counter_module/jquery-1.9.0.js"></script>
<script src="http://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>
<link rel="stylesheet" href="/resources/demos/style.css" />-->
      <script>
	$(function() {
//		function log( message ) {
//			$( "<div>" ).text( message ).prependTo( "#log" );
//			$( "#log" ).scrollTop( 0 );
//		}

		$( "#loyalCustomer" ).autocomplete({
			source: "SearchLoyalCustomer.php",
			minLength: 3,
			select: function( event, ui ) {
                            $.ajax({
                                    dataType: "json",
                                    url: 'loyalCustomerInfo.php',
                                
                                    data: "customercode="+ui.item.id,
                                    success: function(data) {
                                        
                                      
                                        //alert(data[0]);
                                       // alert(data.Phoneno);
                                       // alert(data['Phoneno'] + point[0] + point[0][1] );
                                        //document.getElementById("DeliverTo").value =  data.Name +" "+ data.Surname;
                                        document.getElementById("DeliverTo").value =  data.Company;
                                        document.getElementById("PhoneNo").value = data.Vatnum;
                                        document.getElementById("Address1").value = data.Address ;
                                        document.getElementById("StreetAdd1").value = data.Streetad ;
                                        document.getElementById("Email").value =  data.Phoneno ;
                                        document.getElementById("CustCodeNum").value = data.CustCode;
                                        //
                                        //document.getElementById("link").href = "LoyalCustomerContacts.php?loyalcustomername="+data.CustCode;
                                        document.getElementById("recalc").click();
                                        // call it again after one second
                                        //setTimeout(requestData, 1000);    
                                    }
                                    //cache: false
                                });  
                                document.getElementById("CustCodeNum").value = ui.item.id;
                                document.getElementById("CustCodeNuma").readOnly = true;
                                         //document.SelectParts.CustCodeNum.value  = stringVariable[7] ;
                                document.getElementById("link").href = "LoyalCustomerContacts.php?loyalcustomername="+ui.item.id;
//				log( ui.item ?
//					"Selected: " + ui.item.Customer + " aka " + ui.item.Customer :
//					"Nothing selected, input was " + this.Customer );
                            //alert("Selected: " + ui.item.Customer + " aka " + ui.item.Customer );
                            //alert("Selected: " +  ui.item.value + " aka " + ui.item.id);
			}
		});
	});
	</script>
<?php

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?identifier=' . $identifier . '" name="SelectParts" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


//      function setCustomerName()
//            {
//                  
//                       var loyal = document.getElementById("loyalCustomer").value;
//                       var strn = loyal.search("NotFound");
//                       if(loyal.length > 10 && strn == -1){
//                       var str = loyal.split(/#/);
//                      
//                       document.SelectParts.DeliverTo.value =   str[2]+" "+str[1];
//                      // if(str[1] != ""){
//                           document.getElementById("link").href = "LoyalCustomerContacts.php?loyalcustomername="+str[0];
//                       //}
//                       if (window.XMLHttpRequest)
//                       {// code for IE7+, Firefox, Chrome, Opera, Safari
//                                xmlhttp=new XMLHttpRequest();
//                        }else{// code for IE6, IE5
//                                 xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
//                        }
//                        //Get results
//                         xmlhttp.onreadystatechange=function()
//                         {
//                              if (xmlhttp.readyState==4 && xmlhttp.status==200)
//                                {
//                                        var stringVariable = xmlhttp.responseText.split("#"); 
//                                       
//                                         if(stringVariable[3] == ""){
//                                             document.SelectParts.PhoneNo.value = "" ;
//                                         }else{
//                                             
//                                             document.getElementById("PhoneNo").value = stringVariable[3] ;
//                                         }
//                                         if(stringVariable[4] == ""){
//                                             document.SelectParts.Address1.value  = "" ;
//                                         }else{
//                                              document.getElementById("Address1").value = stringVariable[4] ;
//                                         }
//                                         
//                                         if(stringVariable[5] == ""){
//                                             document.SelectParts.StreetAdd1.value  = "";
//                                         }else{
//
//                                                document.getElementById("StreetAdd1").value = stringVariable[5] ;
//                                             
//                                         }
//                                      
//                                         if(stringVariable[6] == ""){
//                                             document.SelectParts.Email.value  =  "" ;
//                                         }else{
//                                             //document.SelectParts.Email.value  =  stringVariable[6] ;
//                                              document.getElementById("Email").value = stringVariable[6] ;
//                                         }
//                                         
//                                         document.getElementById("CustCodeNum").value = stringVariable[7] ;
//                                         //document.SelectParts.CustCodeNum.value  = stringVariable[7] ;
//                                         document.getElementById("link").href = "LoyalCustomerContacts.php?loyalcustomername="+stringVariable[7];
//                                         document.getElementById("recalc").click();
//                                }
//                           }
//                            if(str[0]!= "NotFound")
//                            {
//                                xmlhttp.open("GET","loyalCustomerInfo.php?Code="+str[0],true);
//                                xmlhttp.send();
//                            }
//                 }
//           
//           }
echo '<script  type="text/javascript">

            function recalculates()
            {
                   document.getElementById("StockCodes").value = "";
                   document.SelectParts.Recalculate.click();
                  
            }
      
            function clearFields()
            {
                  document.getElementById("CustCodeNum").value = "";
                  document.getElementById("loyalCustomer").value = "";
                  document.SelectParts.DeliverTo.value = "CASH SALES";
                  document.SelectParts.PhoneNo.value = "" ;
                  document.SelectParts.Email.value  = "" ;
                  document.SelectParts.CustRef.value  ="";
                  document.SelectParts.Address1.value  ="";
                  document.SelectParts.StreetAdd1.value  ="";
                  document.getElementById("CustCodeNuma").value = "";
                  document.getElementById("CustCodeNuma").readOnly = false;
                  //document.SelectParts.CustCodeNum.value = "" ;
                  document.SelectParts.Recalculate.click();
                

           }
  
 </script>';
//  echo '<input type="hidden" name="Address1" value="'.$_SESSION['Items'.$identifier]->Address1.'" />';
//                echo '<input type="hidden" name="StreetAdd1" value="'.$_SESSION['Items'.$identifier]->StreetAddress1.'" />';
//Get The exchange rate used for GPPercent calculations on adding or amending items
if ($_SESSION['Items' . $identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']) {
    $ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items' . $identifier]->DefaultCurrency . "'", $db);
    if (DB_num_rows($ExRateResult) > 0) {
        $ExRateRow = DB_fetch_row($ExRateResult);
        $ExRate = $ExRateRow[0];
    } else {
        $ExRate = 1;
    }
} else {
    $ExRate = 1;
}

/* Process Quick Entry */
/* If enter is pressed on the quick entry screen, the default button may be Recalculate */
if (isset($_POST['SelectingOrderItems'])
        OR isset($_POST['QuickEntry'])
        OR isset($_POST['Recalculate'])) {
//echo "<b>Hello </b>";
    /* get the item details from the database and hold them in the cart object */

    /* Discount can only be set later on  -- after quick entry -- so default discount to 0 in the first place */
    $Discount = 0;
    $AlreadyWarnedAboutCredit = false;
    $i = 1;
    while ($i <= $_SESSION['QuickEntries']
    AND isset($_POST['part_' . $i])
    AND $_POST['part_' . $i] != '') {

        $QuickEntryCode = 'part_' . $i;
        $QuickEntryQty = 'qty_' . $i;
        $QuickEntryPOLine = 'poline_' . $i;
        $QuickEntryItemDue = 'ItemDue_' . $i;

        $i++;

        if (isset($_POST[$QuickEntryCode])) {
            $NewItem = mb_strtoupper($_POST[$QuickEntryCode]);
        }
        if (isset($_POST[$QuickEntryQty])) {
            $NewItemQty = filter_number_format($_POST[$QuickEntryQty]);
        }
        if (isset($_POST[$QuickEntryItemDue])) {
            $NewItemDue = $_POST[$QuickEntryItemDue];
        } else {
            $NewItemDue = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $_SESSION['Items' . $identifier]->DeliveryDays);
        }
        if (isset($_POST[$QuickEntryPOLine])) {
            $NewPOLine = $_POST[$QuickEntryPOLine];
        } else {
            $NewPOLine = 0;
        }

        if (!isset($NewItem)) {
            unset($NewItem);
            break; /* break out of the loop if nothing in the quick entry fields */
        }

        if (!Is_Date($NewItemDue)) {
            prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $NewItemDue . ' ' . _('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'warn');
            //Attempt to default the due date to something sensible?
            $NewItemDue = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $_SESSION['Items' . $identifier]->DeliveryDays);
        }
        /* Now figure out if the item is a kit set - the field MBFlag='K' */
        $sql = "SELECT stockmaster.mbflag, 
						stockmaster.controlled
				FROM stockmaster
				WHERE stockmaster.stockid='" . $NewItem . "'";

        $ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
        $DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
        $KitResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);


        if (DB_num_rows($KitResult) == 0) {
            prnMsg(_('The item code') . ' ' . $NewItem . ' ' . _('could not be retrieved from the database and has not been added to the order'), 'warn');
        } elseif ($myrow = DB_fetch_array($KitResult)) {
            if ($myrow['mbflag'] == 'K') { /* It is a kit set item */
                $sql = "SELECT bom.component,
							bom.quantity
						FROM bom
						WHERE bom.parent='" . $NewItem . "'
						AND bom.effectiveto > '" . Date('Y-m-d') . "'
						AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

                $ErrMsg = _('Could not retrieve kitset components from the database because') . ' ';
                $KitResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

                $ParentQty = $NewItemQty;
                while ($KitParts = DB_fetch_array($KitResult, $db)) {
                    $NewItem = $KitParts['component'];
                    $NewItemQty = $KitParts['quantity'] * $ParentQty;
                    $NewPOLine = 0;
                    include('includes/SelectOrderItems_CounterSales.inc');
                    $_SESSION['Items' . $identifier]->GetTaxes(($_SESSION['Items' . $identifier]->LineCounter - 1));
                }
            } else if ($myrow['mbflag'] == 'G') {
                prnMsg(_('Phantom assemblies cannot be sold, these items exist only as bills of materials used in other manufactured items. The following item has not been added to the order:') . ' ' . $NewItem, 'warn');
            } else if ($myrow['controlled'] == 1) {
                prnMsg(_('The system does not currently cater for counter sales of lot controlled or serialised items'), 'warn');
            } else if ($NewItemQty <= 0) {
                prnMsg(_('Only items entered with a positive quantity can be added to the sale'), 'warn');
            } else { /* Its not a kit set item */
                include('includes/SelectOrderItems_CounterSalesNew.inc');
                $_SESSION['Items' . $identifier]->GetTaxes(($_SESSION['Items' . $identifier]->LineCounter - 1));
            }
        }
    }
    unset($NewItem);
} /* end of if quick entry */

/* Now do non-quick entry delete/edits/adds */

if ((isset($_SESSION['Items' . $identifier])) OR isset($NewItem)) {

    if (isset($_GET['Delete'])) {
        $_SESSION['Items' . $identifier]->remove_from_cart($_GET['Delete']);  /* Don't do any DB updates */
    }
    $AlreadyWarnedAboutCredit = false;
    foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {

        if (isset($_POST['Quantity_' . $OrderLine->LineNumber])) {
            $Quantity = round(filter_number_format($_POST['Quantity_' . $OrderLine->LineNumber]), 4);
            //$Quantity = round(filter_number_format($_POST['Quantity_' . $OrderLine->LineNumber]),$OrderLine->DecimalPlaces);

            if (ABS($OrderLine->Price - number_format($_POST['Price_' . $OrderLine->LineNumber]), 2, '.', '') > 0.01) {
                /* There is a new price being input for the line item */

                $Price = number_format($_POST['Price_' . $OrderLine->LineNumber], 2, '.', '');
                $_POST['GPPercent_' . $OrderLine->LineNumber] = (($Price * (1 - (filter_number_format($_POST['Discount_' . $OrderLine->LineNumber]) / 100))) - $OrderLine->StandardCost * $ExRate) / ($Price * (1 - filter_number_format($_POST['Discount_' . $OrderLine->LineNumber])) / 100);
            } elseif (ABS($OrderLine->GPPercent - filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber])) >= 0.01) {
                /* A GP % has been input so need to do a recalculation of the price at this new GP Percentage */


                prnMsg(_('Recalculated the price from the GP % entered - the GP % was') . ' ' . $OrderLine->GPPercent . '  the new GP % is ' . filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]), 'info');


                $Price = ($OrderLine->StandardCost * $ExRate) / (1 - ((filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]) + filter_number_format($_POST['Discount_' . $OrderLine->LineNumber])) / 100));
            } else {
                $Price = number_format($_POST['Price_' . $OrderLine->LineNumber], 2, '.', '');
            }
            $DiscountPercentage = filter_number_format($_POST['Discount_' . $OrderLine->LineNumber]);
            if ($_SESSION['AllowOrderLineItemNarrative'] == 1) {
                $Narrative = $_POST['Narrative_' . $OrderLine->LineNumber];
            } else {
                $Narrative = '';
            }

            if (!isset($OrderLine->DiscountPercent)) {
                $OrderLine->DiscountPercent = 0;
            }

            if ($Quantity < 0 or $Price < 0 or $DiscountPercentage > 100 or $DiscountPercentage < 0) {
                prnMsg(_('The item could not be updated because you are attempting to set the quantity ordered to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'), 'warn');
            } else if ($OrderLine->Quantity != $Quantity
                    OR $OrderLine->Price != $Price
                    OR abs($OrderLine->DiscountPercent - $DiscountPercentage / 100) > 0.001
                    OR $OrderLine->Narrative != $Narrative
                    OR $OrderLine->ItemDue != $_POST['ItemDue_' . $OrderLine->LineNumber]
                    OR $OrderLine->POLine != $_POST['POLine_' . $OrderLine->LineNumber]) {

                $_SESSION['Items' . $identifier]->update_cart_item($OrderLine->LineNumber, $Quantity, $Price, $DiscountPercentage / 100, $Narrative, 'Yes', /* Update DB */ $_POST['ItemDue_' . $OrderLine->LineNumber], $_POST['POLine_' . $OrderLine->LineNumber], filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]));
            }
        } //page not called from itself - POST variables not set
    }
}

// print_r($_SESSION);
//		echo'Currency Decimal Places<br>';
//		echo $_SESSION['Items'.$identifier]->CurrDecimalPlaces;
//		echo'Local number <br>';
//	echo locale_number_format($TenderTypeTotals,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
//		echo'<br>';
//	echo filter_number_format($TenderTypeTotals); 

if (isset($_POST['Recalculate'])) {



    /* Check that tender type has at least one value and check against total for sale */
    $TenderTypeTotals = 0;
    $TenderTypeArray = array();
    foreach ($_POST['tenderid'] as $key => $val) {
        //echo "<br>Key rot is " . $key . "<br>";
        if (is_numeric($val) && $val != "") {
            // Check that only one tendertype was selected PDT */
            $FoundTender = $key;
            //echo "found key is " . $FoundTender . "<br>";
            //echo "val is " . $val . "<br>";
            $_POST['TenderSet'] = TRUE;
            $_POST['TenderTypeCode'] = $key;
            $_POST['TenderValue'] = $val;
            //$_POST['TenderTypeCode'] = $_POST[$key];
            $_POST['GrandTotal'] = $_POST['TenderValue'] + $TenderTypeTotals;
            $TenderTypeTotals = $_POST['GrandTotal'];
            $TenderTypeArray[$key] = array(
                'TenderTypeGlCode' => $_POST['GlAccount'][$key],
                'TenderValue' => $_POST['TenderValue'],
                'TenderTypeCode' => $_POST['TenderTypeCode']
            );
        } else {
            //echo "<br>Key under else is" . $key . "<br>";
            if (!is_numeric($val) && $val != "") {
                prnMsg(_('Only numeric values can be entered'), 'error');
                echo '<div class="centre">
					<INPUT type="button" value="Click here to go back" onClick="history.back()"></div>';
                include('includes/footer.inc');
                exit;
            }
        }


        //echo "<br>Key is " . $key . "<br>";
    } //END FOREACH
    /*
      echo "gl code is " . $val['TenderTypeGlCode'] . "<br>";
      echo "Tender Type Code is " . $_POST['TenderTypeCode'] . "<br>";
      echo "Tender Value " . $_POST['TenderValue'] . "<br>";
      echo "tendertotal " . $TenderTypeTotals . "<br>";
      echo "session total " . $_SESSION['TenderTotal'] . "<br>";
      echo "grant total " . $_POST['GrandTotal']  . "<br>";
      //filter_number_format($_POST['TaxTotal']

      echo'diderson test<br>';
      echo "tendertotal " . $TenderTypeTotals . "<br>";
      echo "receive " . $_POST['AmountReceved'] . "<br>";
      echo "receive filtre " . filter_number_format($_POST['AmountReceved']) . "<br>";
      echo locale_number_format($TenderTypeTotals,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
      echo'diderson test<br>';
      echo filter_number_format($TenderTypeTotals); */

    foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {
        $NewItem = $OrderLine->StockID;
        $sql = "SELECT stockmaster.mbflag, 
						stockmaster.controlled
				FROM stockmaster
				WHERE stockmaster.stockid='" . $OrderLine->StockID . "'";

        $ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
        $DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
        $KitResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
        if ($myrow = DB_fetch_array($KitResult)) {
            if ($myrow['mbflag'] == 'K') { /* It is a kit set item */
                $sql = "SELECT bom.component,
								bom.quantity
							FROM bom
							WHERE bom.parent='" . $OrderLine->StockID . "'
							AND bom.effectiveto > '" . Date('Y-m-d') . "'
							AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

                $ErrMsg = _('Could not retrieve kitset components from the database because');
                $KitResult = DB_query($sql, $db, $ErrMsg);

                $ParentQty = $NewItemQty;
                while ($KitParts = DB_fetch_array($KitResult, $db)) {
                    $NewItem = $KitParts['component'];
                    $NewItemQty = $KitParts['quantity'] * $ParentQty;
                    $NewPOLine = 0;
                    $NewItemDue = date($_SESSION['DefaultDateFormat']);
                    $_SESSION['Items' . $identifier]->GetTaxes($OrderLine->LineNumber);
                }
            } else { /* Its not a kit set item */
                $NewItemDue = date($_SESSION['DefaultDateFormat']);
                $NewPOLine = 0;
                $_SESSION['Items' . $identifier]->GetTaxes($OrderLine->LineNumber);
            }
        }
        unset($NewItem);
    } /* end of if its a new item */
}

//Avoid duplicates
if (isset($NewItemArray) AND isset($_POST['SelectingOrderItems']) && !$_POST['BarCode'] ) {
    /* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
    /* Now figure out if the item is a kit set - the field MBFlag='K' */
    $AlreadyWarnedAboutCredit = false;
    //print_r($NewItemArray);
    foreach ($NewItemArray as $NewItem => $NewItemQty) {
        if ($NewItemQty['3'] > 0) {
            $sql = "SELECT stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='" . $NewItemQty['0'] . "'";

            $ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');

            $KitResult = DB_query($sql, $db, $ErrMsg);

            //$NewItemQty = 1; /*By Default */
            $Discount = 0; /* By default - can change later or discount category override */

            if ($myrow = DB_fetch_array($KitResult)) {
                if ($myrow['mbflag'] == 'K') { /* It is a kit set item */
                    $sql = "SELECT bom.component,
	        					bom.quantity
		          			FROM bom
							WHERE bom.parent='" . $NewItemQty['0'] . "'
							AND bom.effectiveto > '" . Date('Y-m-d') . "'
							AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

                    $ErrMsg = _('Could not retrieve kitset components from the database because');
                    $KitResult = DB_query($sql, $db, $ErrMsg);

                    $ParentQty = $NewItemQty['3'];
                    while ($KitParts = DB_fetch_array($KitResult, $db)) {
                        $NewItemQty['0'] = $KitParts['component'];
                        $NewItemQty['3'] = $KitParts['quantity'] * $ParentQty;
                        $NewItemDue = date($_SESSION['DefaultDateFormat']);
                        $NewPOLine = 0;
                        include('includes/SelectOrderItems_CounterSalesNew.inc');
                        $_SESSION['Items' . $identifier]->GetTaxes(($_SESSION['Items' . $identifier]->LineCounter - 1));
                    }
                } else { /* Its not a kit set item */
                    $NewItemDue = date($_SESSION['DefaultDateFormat']);
                    $NewPOLine = 0;
                    include('includes/SelectOrderItems_CounterSalesNew.inc');
                    $_SESSION['Items' . $identifier]->GetTaxes(($_SESSION['Items' . $identifier]->LineCounter - 1));
                }
            } /* end of if its a new item */
        } /* end of if its a new item */
    }
}//////////////Now perform barcode tasks....
 elseif (isset($NewBItem_array)&& isset($_POST['SelectingOrderItems']) && isset($_POST['BarCode'])) {
      
          unset($_POST['BarCode']);
          unset($NewItemArray);
          unset($_POST['SelectingOrderItems']);

        	foreach($NewBItem_array as $NewItem => $NewItemQty){
		if($NewItemQty['3'] > 0)	{
			$sql = "SELECT stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='". $NewItemQty['0'] ."'";

			$ErrMsg =  _('Could not determine if the part being ordered was a kitset or not because');

			$KitResult = DB_query($sql, $db,$ErrMsg);

			//$NewItemQty = 1; /*By Default */
			$Discount = 0; /*By default - can change later or discount category override */

			if ($myrow=DB_fetch_array($KitResult)){
				if ($myrow['mbflag']=='K'){	/*It is a kit set item */
					$sql = "SELECT bom.component,
	        					bom.quantity
		          			FROM bom
							WHERE bom.parent='" . $NewItemQty['0']. "'
							AND bom.effectiveto > '" . Date('Y-m-d') . "'
							AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

					$ErrMsg = _('Could not retrieve kitset components from the database because');
					$KitResult = DB_query($sql,$db,$ErrMsg);

					$ParentQty = $NewItemQty['3'];
					while ($KitParts = DB_fetch_array($KitResult,$db)){
						$NewItemQty['0']= $KitParts['component'];
						$NewItemQty['3'] = $KitParts['quantity'] * $ParentQty;
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = 0;
						include('includes/SelectOrderItems_CounterSalesNew.inc');
						$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
					}

				} else { /*Its not a kit set item*/
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					$NewPOLine = 0;
					include('includes/SelectOrderItems_CounterSalesNew.inc');
					$_SESSION['Items'.$identifier]->GetTaxes(($_SESSION['Items'.$identifier]->LineCounter - 1));
				}
			} /* end of if its a new item */
		} /*end of if its a new item */
	}
   

    }
/* Now Run through each line of the order again to work out the appropriate discount from the discount matrix */
$DiscCatsDone = array();
foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {

    if ($OrderLine->DiscCat != '' AND !in_array($OrderLine->DiscCat, $DiscCatsDone)) {
        $DiscCatsDone[] = $OrderLine->DiscCat;
        $QuantityOfDiscCat = 0;

        foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine_2) {
            /* add up total quantity of all lines of this DiscCat */
            if ($OrderLine_2->DiscCat == $OrderLine->DiscCat) {
                $QuantityOfDiscCat += $OrderLine_2->Quantity;
            }
        }
        $result = DB_query("SELECT MAX(discountrate) AS discount
							FROM discountmatrix
							WHERE salestype='" . $_SESSION['Items' . $identifier]->DefaultSalesType . "'
							AND discountcategory ='" . $OrderLine->DiscCat . "'
							AND quantitybreak <= '" . $QuantityOfDiscCat . "'", $db);
        $myrow = DB_fetch_row($result);
        if ($myrow[0] == NULL) {
            $DiscountMatrixRate = 0;
        } else {
            $DiscountMatrixRate = $myrow[0];
        }
        if ($myrow[0] != 0) { /* need to update the lines affected */
            foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine_2) {
                if ($OrderLine_2->DiscCat == $OrderLine->DiscCat) {
                    $_SESSION['Items' . $identifier]->LineItems[$OrderLine_2->LineNumber]->DiscountPercent = $DiscountMatrixRate;
                    $_SESSION['Items' . $identifier]->LineItems[$OrderLine_2->LineNumber]->GPPercent = (($_SESSION['Items' . $identifier]->LineItems[$OrderLine_2->LineNumber]->Price * (1 - $DiscountMatrixRate)) - $_SESSION['Items' . $identifier]->LineItems[$OrderLine_2->LineNumber]->StandardCost * $ExRate) / ($_SESSION['Items' . $identifier]->LineItems[$OrderLine_2->LineNumber]->Price * (1 - $DiscountMatrixRate) / 100);
                }
            }
        }
    }
} /* end of discount matrix lookup code */

if (!isset($_POST['ProcessSale'])) {
//if (count($_SESSION['Items'.$identifier]->LineItems)>0 
    //AND !isset($_POST['ProcessSale'])){ /*only show order lines if there are any */
    /*
      // *************************************************************************
      //   T H I S   W H E R E   T H E   S A L E  I S   D I S P L A Y E D
      // *************************************************************************
     */

$SQLgp = "SELECT * FROM sendLowGPcron";
$resultsgp = mysql_query($SQLgp);

$myrowgp  = mysql_fetch_array($resultsgp);
$_POST['GPMin'] = $myrowgp['margin'];
$Messages = "";
foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {
    $GPPercent = 0;
    $GP  = 0;
    $DisplayDiscount = locale_number_format(($OrderLine->DiscountPercent * 100), 2);
    $GP = ($OrderLine->Price*(1-$DisplayDiscount)) - ($OrderLine->StandardCost);
    $GPPercent = (int)(( $OrderLine->Price-$OrderLine->StandardCost)*100/ $OrderLine->Price) ;
    if($GPPercent <= $_POST['GPMin']){
        //echo $myrow['lowgp'] ."----------".$_POST['GPMin'] ."<br />";
        $DisplayGPPercent = number_format(($GP *100)/ $OrderLine->Price,1);
        $DisplayGP = number_format($GP ,2);
        //echo "<tr><td>".$myrow['stkcode']." is selling at a low gp of ".$DisplayGPPercent."%</td></tr>";
        $Messages .= "<tr><td class=tdc><font color=red><u>".$OrderLine->StockID. ", of  GP: ".$DisplayGP. " is selling at a low GP percentage of..".$DisplayGPPercent ." %</u></font></td></tr>";
        //continue;
    }
}
if( $Messages != ""){

    echo '<link href="' . $rootpath . '/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
    echo '<script src="' . $rootpath . '/facebox/src/facebox.js" type="text/javascript"></script>';
    echo "<script>jQuery.facebox('$Messages');</script>";
    // echo "<table>";
    // echo
    // echo "</table>";
}
    echo '<br />
		<table width="90%" cellpadding="2" colspan="7">
		<tr bgcolor="#800000">';
    echo '<th>' . _('Item Code') . '</th>
   	      <th>' . _('Item Description') . '</th>
              <th>' . _('Units') . '</th>
	      <th>' . _('Of') . '</th>
	      <th>' . _('Packsize') . '</th>
	      <th>' . _('Quantity') . '</th>
	      <th>' . _('QOH') . '</th>
	      <th>' . _('Unit') . '</th>
	      <th>' . _('Price') . '</th>';
    echo '<th>' . _('Discount') . '</th>';
    //echo '<th>' . _('GP %') . '</th>';
    echo ' <th>' . _('Net') . '</th>
	      <th>' . _('Tax') . '</th>
	      <th>' . _('Total') . '<br />' . _('Incl Tax') . '</th>
	      </tr>';

    $_SESSION['Items' . $identifier]->total = 0;
    $_SESSION['Items' . $identifier]->totalVolume = 0;
    $_SESSION['Items' . $identifier]->totalWeight = 0;
    $TaxTotals = array();
    $TaxGLCodes = array();
    $TaxTotal = 0;
    $k = 0;  //row colour counter
    foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {

        $SubTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
        $DisplayDiscount = locale_number_format(($OrderLine->DiscountPercent * 100), 2);
        $QtyOrdered = $OrderLine->Quantity;
        $QtyRemain = $QtyOrdered - $OrderLine->QtyInv;

        if ($OrderLine->QOHatLoc < $OrderLine->Quantity AND ($OrderLine->MBflag == 'B' OR $OrderLine->MBflag == 'M') OR $OrderLine->Price < $OrderLine->StandardCost) {

            // Sell Price is lower than cost price. Gives error message.
            if ($OrderLine->Price < $OrderLine->StandardCost) {
                $Cost = number_format($OrderLine->StandardCost);
                $PriceDisplay = number_format($OrderLine->Price);
                echo '<link href="' . $rootpath . '/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
                echo '<script src="' . $rootpath . '/facebox/src/facebox.js" type="text/javascript"></script>';
                echo "<script>jQuery.facebox('Sell price for $OrderLine->StockID is below cost price.');</script>";
            }

            // There is a stock deficiency in the stock location selected
            $RowStarter = '<tr bgcolor="#EEAABB">';
        } elseif ($k == 1) {
            $RowStarter = '<tr class="OddTableRows">';
            $k = 0;
        } else {
            $RowStarter = '<tr class="EvenTableRows">';
            $k = 1;
        }

        echo $RowStarter;
        echo '<input type="hidden" name="POLine_' . $OrderLine->LineNumber . '" value="" />';
        echo '<input type="hidden" name="ItemDue_' . $OrderLine->LineNumber . '" value="' . $OrderLine->ItemDue . '" />';

        echo '<td><a target="_blank" href="' . $rootpath . '/StockStatus.php?identifier=' . $identifier . '&StockID=' . $OrderLine->StockID . '&DebtorNo=' . $_SESSION['Items' . $identifier]->DebtorNo . '">' . $OrderLine->StockID . '</a></td>
			<td>' . $OrderLine->ItemDescription . '</td>';
        echo ' <td>' . $OrderLine->PackUnits . '</td>';
        echo ' <td> X </td>';
        echo ' <td>' . $OrderLine->Packsize . '</td>';
        echo '<td><input class="number" tabindex="2" type="text" id ="QuantityAm" name="Quantity_' . $OrderLine->LineNumber . '" size="8" maxlength="8" onFocus="this.select(); setBKColor(event);" onChange="recalculates()" value="' . locale_number_format($OrderLine->Quantity, 4) . '" />'; //$OrderLine->DecimalPlaces

        echo '</td>
			<td class="number">' . locale_number_format($OrderLine->QOHatLoc, $OrderLine->DecimalPlaces) . '</td>
			<td>' . $OrderLine->Units . '</td>';

        echo '<td><input class="number" type="text" name="Price_' . $OrderLine->LineNumber . '" size="16" maxlength="16"  onFocus="this.select(); setBKColor(event);" onChange="recalculates()" value="' . number_format($OrderLine->Price, $_SESSION['Items' . $identifier]->CurrDecimalPlaces, '.', '') . '" /></td>
				<td><input class="number" type="text" name="Discount_' . $OrderLine->LineNumber . '" size="5" maxlength="4" value="' . locale_number_format(($OrderLine->DiscountPercent * 100), 2) . '" /><input class="number" type="hidden" name="GPPercent_' . $OrderLine->LineNumber . '" value="' . locale_number_format($OrderLine->GPPercent, 2) . '" /></td>';
        //<td><input class="number" type="text" name="GPPercent_' . $OrderLine->LineNumber . '" size="3" maxlength="40" value="' . locale_number_format($OrderLine->GPPercent,2) . '" /></td>';
        echo '<td class="number">' . locale_number_format($SubTotal, $_SESSION['Items' . $identifier]->CurrDecimalPlaces) . '</td>';
        $LineDueDate = $OrderLine->ItemDue;
        if (!Is_Date($OrderLine->ItemDue)) {
            $LineDueDate = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $_SESSION['Items' . $identifier]->DeliveryDays);
            $_SESSION['Items' . $identifier]->LineItems[$OrderLine->LineNumber]->ItemDue = $LineDueDate;
        }
        $i = 0; // initialise the number of taxes iterated through
        $TaxLineTotal = 0; //initialise tax total for the line

        foreach ($OrderLine->Taxes AS $Tax) {
            if (empty($TaxTotals[$Tax->TaxAuthID])) {
                $TaxTotals[$Tax->TaxAuthID] = 0;
            }
            if ($Tax->TaxOnTax == 1) {
                $TaxTotals[$Tax->TaxAuthID] += ( $Tax->TaxRate * ($SubTotal + $TaxLineTotal));
                $TaxLineTotal += ( $Tax->TaxRate * ($SubTotal + $TaxLineTotal));
            } else {
                $TaxTotals[$Tax->TaxAuthID] += ( $Tax->TaxRate * $SubTotal);
                $TaxLineTotal += ( $Tax->TaxRate * $SubTotal);
            }
            $TaxGLCodes[$Tax->TaxAuthID] = $Tax->TaxGLCode;
        }

        $TaxTotal += $TaxLineTotal;
        $_SESSION['Items' . $identifier]->TaxTotals = $TaxTotals;
        $_SESSION['Items' . $identifier]->TaxGLCodes = $TaxGLCodes;
        echo '<td class="number">' . locale_number_format($TaxLineTotal, $_SESSION['Items' . $identifier]->CurrDecimalPlaces) . '</td>';
        echo '<td class="number">' . locale_number_format($SubTotal + $TaxLineTotal, $_SESSION['Items' . $identifier]->CurrDecimalPlaces) . '</td>';
        echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?identifier=' . $identifier . '&Delete=' . $OrderLine->LineNumber . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">' . _('Delete') . '</a></td></tr>';

        if ($_SESSION['AllowOrderLineItemNarrative'] == 1) {
            echo $RowStarter;
            echo '<td valign="top" colspan="11">' . _('Narrative') . ':<textarea name="Narrative_' . $OrderLine->LineNumber . '" cols="100" rows="1">' . stripslashes(AddCarriageReturns($OrderLine->Narrative)) . '</textarea><br /></td></tr>';
        } else {
            echo '<input type="hidden" name="Narrative" value="" />';
        }

        $_SESSION['Items' . $identifier]->total = $_SESSION['Items' . $identifier]->total + $SubTotal;
        $_SESSION['Items' . $identifier]->totalVolume = $_SESSION['Items' . $identifier]->totalVolume + $OrderLine->Quantity * $OrderLine->Volume;
        $_SESSION['Items' . $identifier]->totalWeight = $_SESSION['Items' . $identifier]->totalWeight + $OrderLine->Quantity * $OrderLine->Weight;
    } /* end of loop around items */


    echo '<tr class="EvenTableRows"><td colspan="7" class="number"><b>' . _('Total') . '</b></td>
                                <td></td>
                                <td></td>
                                  <td></td>
				<td class="number">' . locale_number_format(($_SESSION['Items' . $identifier]->total), $_SESSION['Items' . $identifier]->CurrDecimalPlaces) . '</td>
				<td class="number">' . locale_number_format($TaxTotal, $_SESSION['Items' . $identifier]->CurrDecimalPlaces) . '</td>
				<td class="number">' . locale_number_format(($_SESSION['Items' . $identifier]->total + $TaxTotal), $_SESSION['Items' . $identifier]->CurrDecimalPlaces) . '</td>
						</tr>
		</table>';
    echo '<input type="hidden" name="TaxTotal" value="' . $TaxTotal . '" />';

    echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ';
    echo _('Search for Items') . '</p>';
    echo '<div class="page_help_text">' . _('Search for Items') . _(', Searches the database for items, you can narrow the results by selecting a stock category, or just enter a partial item description or partial item code') . '.</div><br />';
    echo '<table class="selection"><tr><td><b>' . _('Select a Stock Category') . ': </b><select tabindex="1" name="StockCat">';

    if (!isset($_POST['StockCat'])) {
        echo '<option selected="true" value="All">' . _('All') . '</option>';
        $_POST['StockCat'] = 'All';
    } else {
        echo '<option value="All">' . _('All') . '</option>';
    }
    $SQL = "SELECT categoryid,
					categorydescription
				FROM stockcategory
				WHERE stocktype='F' OR stocktype='D'
				ORDER BY categorydescription";
    $result1 = DB_query($SQL, $db);
    while ($myrow1 = DB_fetch_array($result1)) {
        if ($_POST['StockCat'] == $myrow1['categoryid']) {
            echo '<option selected="true" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
        } else {
            echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
        }
    }
    ?>


            </select></td>
            <td><b><?php echo _('Enter partial Description'); ?>:</b>
                <input tabindex="2" type="text" name="Keywords" size="20" maxlength="25" value="<?php if (isset($_POST['Keywords']))
            echo $_POST['Keywords']; ?>" />
            </td>

            <td align="right"><b><?php echo _('OR'); ?> </b><b><?php echo _('Enter extract of the Stock Code'); ?>:</b>
                <input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" onkeyup="ajax_showOptions(this,'getItem',event)" tabindex=3 type="Text" id ="StockCodes" name="StockCode" size=15 maxlength=18 value="<?php if (isset($_POST['StockCode']))
            echo $_POST['StockCode']; ?>" autocomplete=off>
            </td>

            </tr>
              <tr>
                 <td></td><td  align="right"><b><?php echo _('OR'); ?> </b><b><?php echo _('Enter '); ?> <?php echo _('Item BarCode '); ?>:</b>
                   &nbsp;<input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);"  tabindex=4 type="Text" id ="BarCode" name="BarCode" size=20 maxlength=18 value="<?php ?>" >
                 </td>
		</tr>
            </table>
            <br><div class="centre"><input tabindex="4" type="submit"  name="Search" value="<?php echo _('Search Now'); ?>" />
                <input tabindex="5" type="submit" name="QuickEntry"  value="<?php echo _('Use Quick Entry'); ?>" /></div>
            <?php
            if (!isset($_POST['Recalculate'])) {
                
//                if( $_SESSION['defaultfield'] == 'BarCode'){
//                   echo '<script  type="text/javascript">defaultControl(document.SelectParts.BarCode);</script>';
//                }elseif($_SESSION['defaultfield'] == 'StockCode'){
//                   echo '<script  type="text/javascript">defaultControl(document.SelectParts.StockCode);</script>';
//                }else{
                    echo '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.BarCode);}</script>';
                   //echo  '<script type="text/javascript"> document.SelectPartsStockCode.value  = ""; </script>';
               // }
            }

            //echo '</tr></table><br />';
            // Add some useful help as the order progresses
            if (isset($SearchResult)) {
                unset($_POST['Recalculate']);
                echo '<br />';
                echo '<div class="page_help_text">' . _('Select an item by entering the quantity required.  Click Order when ready.') . '</div>';
                echo '<br />';
            }
            if (isset($msg)) {
                echo '<p><div class="centre"><b>' . $msg . '</b></div></p>';
            }

            if (isset($SearchResult) AND !isset($_POST['Recalculate'])&& !$_POST['BarCode']) {
                echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . 'identifier=' . $identifier . ' method=post name="orderform"><table class="table1">';
                echo '<tr><td><input type="hidden" name="previous" value=' . number_format($Offset - 1) . '><input tabindex=' . number_format($j + 7) . ' type="submit" name="Prev" value="' . _('Prev') . '"></td>';
                echo '<td style="text-align:center" colspan=6><input type="hidden" name="SelectingOrderItems" value=1><input tabindex=' . number_format($j + 8) . ' name="OrderAnItem "type="submit" value="' . _('Add to Sale') . '"></td>';
                echo '<td><input type="hidden" name="nextlist" value=' . number_format($Offset + 1) . '><input tabindex=' . number_format($j + 9) . ' type="submit" name="Next" value="' . _('Next') . '"></td></tr>';
                echo '<td><type="hidden" id="Intransit' . $count . '" name="Intransit' . $myrow['stockid'] . '" value="' . $StyQ . '">';
                $TableHeader = '<tr><th>' . _('Code') . '</th>
                          			<th>' . _('Description') . '</th>
                          			<th>' . _('Units') . '</th>
                          			<th>' . _('On Hand') . '</th>
                          			
                          			<th>' . _('Units') . '</th>
						<th>' . _('X') . '</th>
                          			<th>' . _('Packsize') . '</th>
                          			<th>' . _('Quantity') . '</th>
                          			</tr>';
                echo $TableHeader;
                $j = 1;
                $k = 0; //row colour counter
                $count = 0;
                while ($myrow = DB_fetch_array($SearchResult)) {
                    // This code needs sorting out, but until then :
                    $ImageSource = _('No Image');
                    //echo "<br>Location is " . $_SESSION['Items' . $identifier]->Location . "<br>";
                    /*
                      if (function_exists('imagecreatefrompng') ){
                      $ImageSource = '<IMG SRC="GetStockImage.php?SID&automake=1&textcolor=FFFFFF&bgcolor=CCCCCC&StockID=' . urlencode($myrow['stockid']). '&text=&width=64&height=64">';
                      } else {
                      if(file_exists($_SERVER['DOCUMENT_ROOT'] . $rootpath. '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.jpg')) {
                      $ImageSource = '<IMG SRC="' .$_SERVER['DOCUMENT_ROOT'] . $rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.jpg">';
                      } else {
                      $ImageSource = _('No Image');
                      }
                      }

                     */
                    // Find the quantity in stock at location
                    $qohsql = "SELECT sum(quantity)
						   FROM locstock
						   WHERE stockid='" . $myrow['stockid'] . "' AND
						   loccode = '" . $_SESSION['Items' . $identifier]->Location . "'";
                    $qohresult = DB_query($qohsql, $db);
                    $qohrow = DB_fetch_row($qohresult);
                    $qoh = $qohrow[0];
                    //Get the stock on request
                    $qohsql = "SELECT SUM(quantity) AS qty
                FROM stockintransit
                WHERE  stockintransit.stockid='" . $myrow['stockid'] . "'
                AND  stockintransit.loccode='" . $_SESSION['UserStockLocation'] . "'
                ";

                    $stresult = DB_query($qohsql, $db);
                    $strow = DB_fetch_row($stresult);
                    $StyQ = $strow[0];

                    // Find the quantity on outstanding sales orders
                    $sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
            			     FROM salesorderdetails,
                      			salesorders
			                 WHERE salesorders.orderno = salesorderdetails.orderno AND
            			     salesorders.fromstkloc='" . $_SESSION['Items' . $identifier]->Location . "' AND
 			                salesorderdetails.completed=0 AND
		 					salesorders.quotation=0 AND
                 			salesorderdetails.stkcode='" . $myrow['stockid'] . "'";

                    $ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Items' . $identifier]->Location . ' ' .
                            _('cannot be retrieved because');
                    $DemandResult = DB_query($sql, $db, $ErrMsg);

                    $DemandRow = DB_fetch_row($DemandResult);
                    if ($DemandRow[0] != null) {
                        $DemandQty = $DemandRow[0];
                    } else {
                        $DemandQty = 0;
                    }
                    // Find the quantity on purchase orders
                    $sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS dem
            			     FROM purchorderdetails
			                 WHERE purchorderdetails.completed=0 AND
                			purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

                    $ErrMsg = _('The order details for this product cannot be retrieved because');
                    $PurchResult = db_query($sql, $db, $ErrMsg);

                    $PurchRow = db_fetch_row($PurchResult);
                    if ($PurchRow[0] != null) {
                        $PurchQty = $PurchRow[0];
                    } else {
                        $PurchQty = 0;
                    }
                    // Find the quantity on works orders
                    $sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
				       FROM woitems
				       WHERE stockid='" . $myrow['stockid'] . "'";
                    $ErrMsg = _('The order details for this product cannot be retrieved because');
                    $WoResult = db_query($sql, $db, $ErrMsg);

                    $WoRow = db_fetch_row($WoResult);
                    if ($WoRow[0] != null) {
                        $WoQty = $WoRow[0];
                    } else {
                        $WoQty = 0;
                    }

                    if ($k == 1) {
                        echo '<tr class="EvenTableRows">';
                        $k = 0;
                    } else {
                        echo '<tr class="OddTableRows">';
                        $k = 1;
                    }
                    $OnOrder = $PurchQty + $WoQty;

                    $Available = $qoh - $DemandQty + $OnOrder;
                    $qoh = $qoh - $StyQ;

                    printf('<td>%s</font></td>
					<td>%s</td>
					<td>%s</td>
					<td style="text-align:center">%s</td>
					
					<td><font size=1><input class="number"  tabindex=' . number_format($j + 7) . ' type="textbox" size=6 id="unit' . $count . '" name="unit' . $myrow['stockid'] . '" onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);">
					<td align=center>X</td>
					<td><font size=1><input class="number"  tabindex=' . number_format($j + 7) . ' type="textbox" size=6 id="pack' . $count . '" name="pack' . $myrow['stockid'] . '" onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);">
					<td><font size=1><input class="number"  tabindex=' . number_format($j + 7, 2) . ' type="textbox" size=6 id="packresult' . $count . '" name="itm' . $myrow['stockid'] . '" onFocus="CalcUnits(' . $count . ');setBKColor(event);this.select();" onblur="reSetBKColor(event);">
					</td>
					</tr>', $myrow['stockid'], $myrow['description'], $myrow['units'], $qoh, $ImageSource, $rootpath, SID, $myrow['stockid']);

                    if ($j == 1)
                        $jsCall = '<script type="text/javascript">defaultControl(document.SelectParts.itm' . $myrow['stockid'] . ');</script>';
                    echo '<td><type="hidden" id="Intransit' . $count . '" name="Intransit' . $myrow['stockid'] . '" value="' . number_format($StyQ) . '">';
                    $j++;
                    // end of page full new headings if
                    $count = ++$count;
                }
                // end of while loop
                echo '<input type="hidden" name="CustRef" id="CustRef" value="' . $_SESSION['Items' . $identifier]->CustRef . '" />';
                echo '<input type="hidden" name="Comments" id="Comments" value="' . $_SESSION['Items' . $identifier]->Comments . '" />';
                echo '<input type="hidden" name="DeliverTo" id="DeliverTo" value="' . $_SESSION['Items' . $identifier]->DeliverTo . '" />';
                echo '<input type="hidden" name="PhoneNo" id="PhoneNo" value="' . $_SESSION['Items' . $identifier]->PhoneNo . '" />';
                echo '<input type="hidden" name="Email" id = "Email" value="' . $_SESSION['Items' . $identifier]->Email . '" />';
                echo '<input type="hidden" name="Codes" id = "Codes" value="' . $_SESSION['Items' . $identifier]->CustRefVia . '" />';
                
//                echo '<input type="hidden" name="Address1" value="' . $_SESSION['Items' . $identifier]->Address1 . '" />';
//                echo '<input type="hidden" name="StreetAdd1" value="' . $_SESSION['Items' . $identifier]->StreetAddress1 . '" />';
//                echo '<input type="hidden" name="CustCodeNum" value="' . $_SESSION['Items' . $identifier]->CustCodeNum . '" />';
                /* echo '<tr><td><input type="hidden" name="previous" value="'.strval($Offset-1).'" /><input tabindex="'.strval($j+7).'" type="submit" name="Prev" value="'._('Prev').'" /></td>';
                  echo '<td style="text-align:center" colspan="6"><input type="hidden" name="SelectingOrderItems" value="1" /><input tabindex="'.strval($j+8).'" type="submit" value="'._('Add to Sale').'" /></td>';
                  echo '<td><input type="hidden" name="NextList" value="'.strval($Offset+1).'" /><input tabindex="'.strval($j+9).'" type="submit" name="Next" value="'._('Next').'" /></td></tr>';
                  echo '</table></form>'; */
                //////
                echo '<tr><td><input type="hidden" name="previous" value=' . number_format($Offset - 1) . '><input tabindex=' . number_format($j + 7) . ' type="submit" name="Prev" value="' . _('Prev') . '"></td>';
                echo '<td style="text-align:center" colspan=6><input type="hidden" name="SelectingOrderItems" value=1><input tabindex=' . number_format($j + 8) . ' type="submit" value="' . _('Add to Sale') . '"></td>';
                echo '<td><input type="hidden" name="nextlist" value=' . number_format($Offset + 1) . '><input tabindex=' . number_format($j + 9) . ' type="submit" name="Next" value="' . _('Next') . '"></td></tr>';
                echo '</table></form>';
                echo $jsCall;
                //echo $jsCall;
            }#end if SearchResults to show
            //nested table
            // }
            echo '<hr /><table><tr><td>';

            echo '<table><tr>';
            ?>
            <!--onkeyup="ajax_showOptions(this,'getLoyalCustomer',event)" onClick="setCustomerName()"-->
            <td align="right"><?php echo _('Search Customer(Name/Surname/Code)'); ?>:</td>
            <td>
            <div class="ui-widget">
            <input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);"  type="Text" id ="loyalCustomer" name="loyalCustomer" size=25 maxlength=25 value="<?php if (isset($_POST['loyalCustomer']))
            echo $_POST['loyalCustomer']; ?>" autocomplete=off>
            
          
            <?php
            // print_r($_SESSION['Items'.$identifier]);
            // echo "<br />";
            //Street Address
           echo '<input type="hidden" name="Address1"     id= "Address1" value="' . $_SESSION['Items' . $identifier]->Address1 . '" />';
           echo '<input type="hidden" name="StreetAdd1"   id= "StreetAdd1"  value="' . $_SESSION['Items' . $identifier]->StreetAddress1 . '" />';
           echo '<input type="hidden" name="CustCodeNum"  id= "CustCodeNum" value="' . $_SESSION['Items' . $identifier]->CustCodeNum . '" />';
           
       
//            echo '<input type="hidden" name="Address1" />';
//            echo '<input type="hidden" name="StreetAdd1"  />';
//            echo '<input type="hidden" name="CustCodeNum" />';
//            
//         $_SESSION['Items' . $identifier]->Address1 = $_POST['Address1'];
//         $_SESSION['Items' . $identifier]->StreetAddress1 = $_POST['StreetAdd1'];
//         $_SESSION['Items' . $identifier]->CustCodeNum = $_POST['CustCodeNum'];
            echo '<a class="Link-button" target="new" href="LoyalCustomerContacts.php?loyalcustomername='.$_SESSION['Items' . $identifier]->CustCodeNum.'" id="link">Edit</a>' . "/" . '<a class="Link-button" target="new" href="LoyalCustomerContacts.php? add=add" >Add</a>' . '</td></tr>';
           ?>
            
          
          </div>
            <?php
          if($_SESSION['Items' . $identifier]->CustCodeNum != ""){
            echo '<tr><td>' . _('Customer Number') . ':</td>
		<td><input type="text" size="25" maxlength="25" name="CustCodeNuma"  id= "CustCodeNuma"  value="' .stripslashes( $_SESSION['Items' . $identifier]->CustCodeNum). '" /></td>
	        </tr>';
          }
            echo '<tr><td>' . _('Sale To ') . ':</td>
		<td><input type="text" size="25" maxlength="25" name="DeliverTo" id = "DeliverTo" value="' . stripslashes($_SESSION['Items' . $identifier]->DeliverTo) . '" /></td>
	</tr>';
            echo '<tr>
		<td>' . _('VAT number say VAT No423423234') . ':</td>
		<td><input type="text" size="25" maxlength="25" name="PhoneNo" id = "PhoneNo" value="' . stripslashes($_SESSION['Items' . $identifier]->PhoneNo) . '" /></td>
	</tr>';
            echo '<tr><td>' . _('Initial Contact via') . ':*</td>
	<td><select name="Codes">';
//custContact =  '."'". $_SESSION['Items'.$identifier]->CustRefVia  ."'".',
            //echo "" .$_SESSION['Items'.$identifier]->CustRefVia;
            $sql = 'SELECT  code,Description FROM custReferences';
            $ErrMsg = _('Could not retrieve the values beacause: ');
            $result = Db_query($sql, $db, $ErrMsg);
            echo "<option value='none'>" . _('--Select Contact Via--');
            while ($myrow = DB_fetch_array($result)) {
                if (isset($_POST['Codes']) AND $_POST['Codes'] == $myrow["code"]) {
                    echo "<option selected value='" . $myrow["Descriptione"] . "'>" . $myrow["Description"];
                    //echo "<select name='Codesb'><option selected value='" . $myrow["AdvrtOn"] . "'>"  ;
                } else {
                    echo "<option value='" . $myrow["Description"] . "'>" . $myrow["Description"];
                    //$_POST['CustRef']= $myrow["code"] ;
                }
            }

            echo '</select></td></tr>';
            echo '<tr><td>' . _('Customer Phone No') . ':</td><td><input type="text" size="25" maxlength="30" name="Email" id="Email" value="' . stripslashes($_SESSION['Items' . $identifier]->Email) . '" /></td></tr>';

            echo '<tr><td>' . _('Customer Reference/Order Number') . ':</td>
		<td><input type="text" size="25" maxlength="25" name="CustRef" id="CustRef" value="' . stripcslashes($_SESSION['Items' . $identifier]->CustRef) . '" /></td>
	</tr>';

            echo '<tr>
		<td>' . _('Comments , EFT number , etc') . ':</td>
		<td><textarea name="Comments" cols="23" rows="5">' . stripcslashes($_SESSION['Items' . $identifier]->Comments) . '</textarea></td>
	</tr>';
            echo '</table>'; //end the sub table in the first column of master table
            echo '</td><th valign="bottom">'; //for the master table

            echo '<table class="selection">'; // a new nested table in the second column of master table
            //now the payment stuff in this column

            $PaymentMethodsResult = DB_query("SELECT paymentid, paymentname FROM paymentmethods WHERE location='" . $_SESSION['UserStockLocation'] . "'", $db);

            //tender types
            //get tender types from paymentmethods table
            $TenderTypeSQL = "SELECT paymentid,
					paymentname,
					paymentcode,
					glaccount,
					isbankaccount,
					location
					FROM paymentmethods
					ORDER BY paymentname";
            $ErrMsg = _('Tender types cannot be retrieved because');
            $DbgMsg = _('The SQL that failed was');
            $TenderTypeResult = DB_query($TenderTypeSQL, $db, $ErrMsg, $DbgMsg);
            $j = 1;
            if (db_num_rows($TenderTypeResult) > 0) {
                $tendercount = 0;
                $DefaultDispatchDate = Date($_SESSION['DefaultDateFormat'], CalcEarliestDispatchDate());
                echo '<tr><td>Payment date:</td>
					<td><input class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" onfocus="setBKColor(event);" onblur="reSetBKColor(event);" type="Text" tabindex=\"$j\" size=15 maxlength=14 name="PamentDate" value="' . $DefaultDispatchDate . '"></td>
					<td rowspan="6" valign="top"><table border="1" align="right">
									<tr><th colspan="2">TENDER SELECTION HELP</th></tr>
									<tr><th>Tender</th><th>Case</th></tr>
									<tr><td>Cash</td><td>CT- Cash Tendered</td></tr>
									<tr><td>Cheque</td><td>CN- Cheque Tendered</td></tr>
									<tr><td>Credit Card</td><td>CC- Credit Card</td></tr>									
									<tr><td>Debit Card</td><td>CD- Debit Card</td></tr>
									<tr><td>EFT</td><td>CF- EFT/Bank Tfr </td></tr>
									</table>
									</td>
									</tr>';
                while ($myrow = db_fetch_array($TenderTypeResult)) {

                    $TenderCode = $myrow['paymentcode'];
                    $tendername = $myrow['paymentname'];
                    if ($myrow['isbankaccount'] == "Y" && $myrow['location'] == $_SESSION['UserStockLocation']) {
                        echo "<tr>
			<td>$myrow[paymentname]:</td>
			<td><input type=text onfocus=\"setBKColor(event);\" onblur=\"reSetBKColor(event);\" tabindex=\"$j\" name=tenderid[$TenderCode] id=\"$j\">
				<input type=\"hidden\" name=\"$myrow[paymentid]\" value=\"$myrow[paymentname]\">
				<input type=\"hidden\" name=\"GlAccount[$TenderCode]\" value=\"$myrow[glaccount]\">
				<input type=\"hidden\" name=\"TenderTotal\" value=\"$_GET[TenderTotal]\"></td>

					</tr>";
                    } else {
                        if ($myrow['isbankaccount'] == "N") {
                            echo "<tr>
			<td>$myrow[paymentname]:</td>
			<td><input type=text onfocus=\"setBKColor(event);\" onblur=\"reSetBKColor(event);\" tabindex=\"$j\" name=tenderid[$TenderCode] id=\"$j\">
					<input type=\"hidden\" name=\"$myrow[paymentid]\" value=\"$myrow[paymentname]\">
					<input type=\"hidden\" name=\"GlAccount[$TenderCode]\" value=\"$myrow[glaccount]\">
					<input type=\"hidden\" name=\"TenderTotal\" value=\"$_GET[TenderTotal]\"></td>

						</tr>";
                        }
                    }

                    $j++;
                    $tendercount++;
                } //end while

                echo "<tr><td>Tendered Total:</td><td><input type='text' onfocus='setBKColor(event);' onblur='reSetBKColor(event);' readonly='readonly' tabindex='$j' name='AmountReceveds' value='" . locale_number_format(($_SESSION['Items' . $identifier]->total + $TaxTotal), $_SESSION['Items' . $identifier]->CurrDecimalPlaces) . "'><input type='hidden' name='AmountReceved' value='" . filter_number_format($_SESSION['Items' . $identifier]->total + $TaxTotal) . "'>";

                //$_GET[TenderTotal]AmountReceved
                //. locale_number_format(($_SESSION['Items'.$identifier]->total+$TaxTotal),$_SESSION['Items'.$identifier]->CurrDecimalPlaces) .
            } //end if
            /*
              echo '<tr><td>' . _('Payment Type') . ':</td><td><select name="PaymentMethod">';
              while ($PaymentMethodRow = DB_fetch_array($PaymentMethodsResult)){
              if (isset($_POST['PaymentMethod']) AND $_POST['PaymentMethod'] == $PaymentMethodRow['paymentid']){
              echo '<option selected="True" value="' . $PaymentMethodRow['paymentid'] . '">' . $PaymentMethodRow['paymentname'] . '</option>';
              } else {
              echo '<option value="' . $PaymentMethodRow['paymentid'] . '">' . $PaymentMethodRow['paymentname'] . '</option>';
              }
              }
              echo '</select></td></tr>';
             */

            $BankAccountsResult = DB_query("SELECT bankaccountname, accountcode FROM bankaccounts", $db);

            echo'<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
            /*
              echo '<tr>
              <td>' . _('Banked to') . ':</td>
              <td><select name="BankAccount">';
              while ($BankAccountsRow = DB_fetch_array($BankAccountsResult)){
              if (isset($_POST['BankAccount']) AND $_POST['BankAccount']	== $BankAccountsRow['accountcode']){
              echo '<option selected="True" value="' . $BankAccountsRow['accountcode'] . '">' . $BankAccountsRow['bankaccountname'] . '</option>';
              } else {
              echo '<option value="' . $BankAccountsRow['accountcode'] . '">' . $BankAccountsRow['bankaccountname'] . '</option>';
              }
              }
              echo '</select></td>
              </tr>';
             */

            if (!isset($_POST['AmountPaid'])) {
                $_POST['AmountPaid'] = 0;
            }
            //echo '<tr><td>' . _('Amount Paid') . ':</td><td><input type="text" class="number" name="AmountPaid" maxlength="12" size="12" value="' . $_POST['AmountPaid'] . '" /></td></tr>';

            echo '</table>'; //end the sub table in the second column of master table
            echo '</th></tr></table>'; //end of column/row/master table
            echo '<br /><div class="centre"><input type="submit" name="Recalculate" id = "recalc" value="' . _('Re-Calculate') . '" />
                <input type="submit" name="ProcessSale" value="' . _('Process The Sale') . '" /></div>';
            echo '<br /><div class="centre"><input type="submit" name="btnclear" onClick="clearFields()" value="' . _('Clear-Customer') . '" /></div>';
        } # end of if lines

        /* An error occured */
        if (isset($_POST['SendRequest'])) {
            $checkedbox = $_POST['sendAllowRequest'];
            $check = count($_POST['sendAllowRequest']);
            for ($i = 0; $i < $check; $i++) {
                /* 	loccode	stockid */

                $SQL = "UPDATE locstock set request = 1
                                WHERE stockid = '" . $checkedbox[$i] . "'
                                AND  loccode ='" . $_SESSION['Items' . $identifier]->Location . "'";
                $ErroMsg = _('Could not send the request sorry... Sql used was :');

                //echo  $SQL."<br />";
                $runRequest = DB_query($SQL, $db, $ErroMsg);
            }
        }

        echo "<hr />";
        /*         * *********************************
         * Invoice Processing Here
         * **********************************
         * */
//        if($_POST['btnclear']){
//           //  $_SESSION['Items' . $identifier]->Address1 = $_POST['Address1'];
//          //$_SESSION['Items' . $identifier]->StreetAddress1 = $_POST['StreetAdd1'];
//                $_SESSION['Items' . $identifier]->CustCodeNum = "";
//        }
        if (isset($_POST['ProcessSale']) AND $_POST['ProcessSale'] != '') {

            // ------------------------------- Checks if contact via is filled in -------------------------------
            if (!isset($_POST['Codes']) || $_POST['Codes'] == "none") {
                echo '<link href="' . $rootpath . '/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
                echo '<script src="' . $rootpath . '/facebox/src/facebox.js" type="text/javascript"></script>';
                echo "<script>jQuery.facebox('Please select Contact Via - No process took place.');</script>";
                // Refreshes
                echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/CounterSales.php?' . SID . 'identifier=' . $identifier . '">';
                prnMsg(_('You should automatically forwarded back to main counter sales page') . '. ' . _('if this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' .
                        '<a href="' . $rootpath . '/CounterSales.php?' . SID . 'identifier=' . $identifier . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
                exit();
            }
            //---------------------------------------------------------------------------------------------------
            // ------------------------------- Checks if items are below cost price -------------------------------


            foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {

                $SubTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
                $DisplayDiscount = locale_number_format(($OrderLine->DiscountPercent * 100), 2);
                $QtyOrdered = $OrderLine->Quantity;
                $QtyRemain = $QtyOrdered - $OrderLine->QtyInv;

                if ($OrderLine->QOHatLoc < $OrderLine->Quantity AND ($OrderLine->MBflag == 'B' OR $OrderLine->MBflag == 'M') OR $OrderLine->Price < $OrderLine->StandardCost) {

                    // Sell Price is lower than cost price. Gives error message.
                    if ($OrderLine->Price < $OrderLine->StandardCost) {
                        $Cost = number_format($OrderLine->StandardCost);
                        $PriceDisplay = number_format($OrderLine->Price);
                        echo '<link href="' . $rootpath . '/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
                        echo '<script src="' . $rootpath . '/facebox/src/facebox.js" type="text/javascript"></script>';
                        echo "<script>jQuery.facebox('Sell price for $OrderLine->StockID is below cost price.');</script>";

                        // Refreshes
                        echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/CounterSales.php?' . SID . 'identifier=' . $identifier . '">';
                        prnMsg(_('You should automatically forwarded back to main counter sales page') . '. ' . _('if this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' .
                                '<a href="' . $rootpath . '/CounterSales.php?' . SID . 'identifier=' . $identifier . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
                        exit();
                    }
                }
            }


            //---------------------------------------------------------------------------------------------------



            $TenderTypeTotals = 0;
            $TenderTypeArray = array();


            foreach ($_POST['tenderid'] as $key => $val) {
                //echo "<br>Key rot is " . $key . "<br>";
                if (is_numeric($val) && $val != "") {
                    // Check that only one tendertype was selected PDT */
                    $FoundTender = $key;
                    //echo "found key is " . $FoundTender . "<br>";
                    //echo "val is " . $val . "<br>";
                    $_POST['TenderSet'] = TRUE;
                    $_POST['TenderTypeCode'] = $key;
                    $_POST['TenderValue'] = $val;
                    //$_POST['TenderTypeCode'] = $_POST[$key];
                    $_POST['GrandTotal'] = $_POST['TenderValue'] + $TenderTypeTotals;
                    $TenderTypeTotals = $_POST['GrandTotal'];
                    $TenderTypeArray[$key] = array(
                        'TenderTypeGlCode' => $_POST['GlAccount'][$key],
                        'TenderValue' => $_POST['TenderValue'],
                        'TenderTypeCode' => $_POST['TenderTypeCode']
                    );
                } else {
                    //echo "<br>Key under else is" . $key . "<br>";
                    if (!is_numeric($val) && $val != "") {
                        prnMsg(_('Only numeric values can be entered'), 'error');
                        echo '<div class="centre">
					<INPUT type="button" value="Click here to go back" onClick="history.back()"></div>';
                        include('includes/footer.inc');
                        exit;
                    }
                }


                //echo "<br>Key is " . $key . "<br>";
            }
            //--------------------------------------------------------------------------------------
//  LEARN ABOUT SOME CHANGE IF THERE IS ANY CASH -------------------------------------------
            foreach ($TenderTypeArray as $key => $val) {
                $GlCode = $val['TenderTypeGlCode'];
                $TenderTypeCode = $val['TenderTypeCode'];
                $TenderValue = $val['TenderValue'];
                //echo "tender value is " . $TenderValue . "<br>";
                //echo "TenderTypeCode is " . $TenderTypeCode . "<br>";
                //----------------------------------
                if ($TenderTypeCode == 'CT') {
                    $CTct = TRUE;
                    $amount_cash = $TenderValue;
                }

                if ($TenderTypeCode == 'CN') {
                    $CNcn = TRUE;
                }

                if ($TenderTypeCode == 'CC') {
                    $CCcc = TRUE;
                }

                if ($TenderTypeCode == 'CD') {
                    $CDcd = TRUE;
                }
                if ($TenderTypeCode == 'CF') {
                    $CFcf = TRUE;
                }

                //----------------------------------------
            }
            $_POST['AmountPaid'] = round($TenderTypeTotals, 2);
//echo  $_POST['AmountPaid'] ;
            if (isset($CNcn) OR isset($CCcc) OR isset($CDcd) OR isset($CFcf)) {
                
            } else {
                //echo'Start CHANGE HERE';
                //--------CHANGE -----amountP is money tendered  amountD is amount Due ie the total invoice value -----------------------------
                $amountP = filter_number_format($_POST['AmountPaid'], 2);
                $amountD = round($_POST['AmountReceved'], 2);
                if ($amountP > $amountD) {
                    $_POST['AmountPaid'] = $_POST['AmountReceved'];
                    $amountChange = $amountP - $amountD;
                    $Change = "Amount Paid : R";
                    $Change .= $amountP;
                    $Change .= "<br>" . "Invoice Total : R";
                    $Change .= $amountD;
                    $Changea = "<br>" . " R";
                    $Changea .= $amountChange;
                }
            }


//      END OF LEARNING ABOUT SOME CASH -------------------------------------------------------
            //--------DIDERSON-------------------------------------------
            //echo $_POST['AmountReceved'];
            //locale_number_format($SubTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces)
            //filter_number_format($_POST['TaxTotal']
//	$_POST['AmountPaid'] = $TenderTypeTotals ;
            //---------------------------------------------------------------------

            $InputError = false; //always assume the best
            //but check for the worst
            if ($_SESSION['Items' . $identifier]->LineCounter == 0) {
                prnMsg(_('There are no lines on this sale. Please enter lines to invoice first'), 'error');
                $InputError = true;
            }
            if (abs(filter_number_format($_POST['AmountPaid']) - ($_SESSION['Items' . $identifier]->total + filter_number_format($_POST['TaxTotal']))) >= 0.01) {
                prnMsg(_('The amount entered as payment does not equal the amount of the invoice. Please ensure the customer has paid the correct amount and re-enter'), 'error');
                $InputError = true;
                //<a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?identifier='.$identifier .'" 
                //echo"<br><br>sosososooso baddddddddddddddddd";
                //-------DIDERSON ----------------------------------
                $linkees = $_SERVER['PHP_SELF'];
                ?>
                <center>  <a href="<?php echo "$linkees?identifier=$identifier"; ?>">CLICK HERE TO BACK</a></center>
                <script language="Javascript" type="text/javascript">
                    setTimeout("window.location='<?php echo "$linkees?identifier=$identifier"; ?>';", 2000)
                </script>

                    <?php
                    //---------------------------------------------------------
                }

                if ($_SESSION['ProhibitNegativeStock'] == 1) { // checks for negative stock after processing invoice
                    //sadly this check does not combine quantities occuring twice on and order and each line is considered individually :-(
                    $NegativesFound = false;
                    $ErrorMsgArray = array();
                    foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {
                        $SQL = "SELECT stockmaster.description,
					   		locstock.quantity,
                                                        locstock.allownegative,
					   		stockmaster.mbflag,
                                                        stockmaster.retailitem
		 			FROM locstock
		 			INNER JOIN stockmaster
					ON stockmaster.stockid=locstock.stockid
					WHERE stockmaster.stockid='" . $OrderLine->StockID . "'
					AND locstock.loccode='" . $_SESSION['Items' . $identifier]->Location . "'";

                        $ErrMsg = _('Could not retrieve the quantity left at the location once this order is invoiced (for the purposes of checking that stock will not go negative because)');
                        $Result = DB_query($SQL, $db, $ErrMsg);
                        $CheckNegRow = DB_fetch_array($Result);
                        if ($CheckNegRow['allownegative'] != 1) {
                            if ($CheckNegRow['mbflag'] == 'B' OR $CheckNegRow['mbflag'] == 'M') {
                                if ($CheckNegRow['quantity'] < $OrderLine->Quantity) {
                                    prnMsg(_('Invoicing the selected order would result in negative stock. The system parameters are set to prohibit negative stocks from occurring. This invoice cannot be created until the stock on hand is corrected.'), 'error', $OrderLine->StockID . ' ' . $CheckNegRow['description'] . ' - ' . _('Negative Stock Prohibited'));
                                    $ErrorMsgArray[] = $OrderLine->StockID;
                                    $NegativesFound = true;
                                }
                            } else if ($CheckNegRow['mbflag'] == 'A') {

                                /* Now look for assembly components that would go negative */
                                $SQL = "SELECT bom.component,
							   stockmaster.description,
							   locstock.quantity-(" . $OrderLine->Quantity . "*bom.quantity) AS qtyleft
						FROM bom
						INNER JOIN locstock
						ON bom.component=locstock.stockid
						INNER JOIN stockmaster
						ON stockmaster.stockid=bom.component
						WHERE bom.parent='" . $OrderLine->StockID . "'
						AND locstock.loccode='" . $_SESSION['Items' . $identifier]->Location . "'
						AND effectiveafter <'" . Date('Y-m-d') . "'
						AND effectiveto >='" . Date('Y-m-d') . "'";

                                $ErrMsg = _('Could not retrieve the component quantity left at the location once the assembly item on this order is invoiced (for the purposes of checking that stock will not go negative because)');
                                $Result = DB_query($SQL, $db, $ErrMsg);
                                while ($NegRow = DB_fetch_array($Result)) {
                                    if ($NegRow['qtyleft'] < 0) {
                                        prnMsg(_('Invoicing the selected order would result in negative stock for a component of an assembly item on the order. The system parameters are set to prohibit negative stocks from occurring. This invoice cannot be created until the stock on hand is corrected.'), 'error', $NegRow['component'] . ' ' . $NegRow['description'] . ' - ' . _('Negative Stock Prohibited'));
                                        $ErrorMsgArray[] = $OrderLine->StockID;
                                        $NegativesFound = true;
                                    } // end if negative would result
                                } //loop around the components of an assembly item
                            }//end if its an assembly item - check component stock
                        }//Should work...
                    } //end of loop around items on the order for negative check
//		if ($NegativesFound){
//			prnMsg(_('The parameter to prohibit negative stock is set and invoicing this sale would result in negative stock. No futher processing can be performed. Alter the sale first changing quantities or deleting lines which do not have sufficient stock.'),'error');
//			$InputError = true;
//		}
                    
                   
                /**************************************************************************************************
                 * ********** CHECK NEGATIVES AGAIN--Tamelo Douglas
                 * ************************************************************************************************
                 */   
                     $myArray  = array();

                    foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) 
                     {

                          if(array_key_exists($OrderLine->StockID,$myArray)){
                             $myArray[$OrderLine->StockID]['qty'] += $OrderLine->Quantity ;
                          }else{
                             $myArray[$OrderLine->StockID]['itm'] = $OrderLine->StockID;
                             $myArray[$OrderLine->StockID]['qty'] = $OrderLine->Quantity ;
                          }

                     }//end loop

                      foreach ($myArray as $key => $value) {

                            $SEC_SQL = "SELECT stockmaster.description,
                                                                    locstock.quantity,
                                                                    locstock.allownegative,
                                                                    stockmaster.mbflag,
                                                                    stockmaster.retailitem
                                                    FROM locstock
                                                    INNER JOIN stockmaster
                                                    ON stockmaster.stockid=locstock.stockid

                                                    WHERE stockmaster.stockid='" .$key . "'
                                                    AND locstock.loccode='" . $_SESSION['Items'.$identifier]->Location . "'";

                            $Neg_Result = DB_query($SEC_SQL,$db,$ErrMsg);
                            $CheckNegRow = DB_fetch_array($Neg_Result);

                            if($CheckNegRow['allownegative'] != 1 ){

                            if ($CheckNegRow['quantity'] < $myArray[$key]['qty']){

                                     prnMsg( _('Invoicing the selected order would result in negative stock. The system parameters are set to prohibit negative stocks from occurring. This invoice cannot be created until the stock on hand is corrected.'),'error',$OrderLine->StockID . ' ' . $CheckNegRow['description'] . ' - ' . _('Negative Stock Prohibited'));
                                                $ErrorMsgArray[] = $key ." quantity of ".$myArray[$key]['qty'];
                                                $NegativesFound = true;
                                }
                            }
                    }
                    
                    /**************************************************************************************************
                     * ***************************************END HERE
                     * ************************************************************************************************
                     */
                   
                    if ($NegativesFound) {
                        echo '<br />';
                        /*                         * Display the error messsages... See this as a better way of doing things* */
                        echo '<div class="centre">' . _('The parameter to prohibit negative stock is set and invoicing this sale would result in negative stock') . '</div>';

                        echo '<table><th>' . _('Send Request') . '</th><th>' . _('Item Code') . '</th>';
                        for ($i = 0; $i < count($ErrorMsgArray); $i++) {
                            echo "<tr><td><input type=checkbox name=sendAllowRequest[] value='" . $ErrorMsgArray[$i] . "' /></td>";
                            echo "<td>" . $ErrorMsgArray[$i] . "</td></tr>";
                        }
                        echo '</table>';
                        echo '<div class="centre">
					<input type=submit name=SendRequest Value=' . _('SendRequest') . '></div>';
                        include('includes/footer.inc');
                        exit;
                    }
                    
                    
        }//end of testing for negative stocks

                /*                 * */
                $BelowCost = false;
                foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {
//$price = $OrderLine->Price /$OrderLine->StandardCost;
                    $sql = "SELECT stkcode,negative
					FROM
					salesorderdetails
                                        WHERE stkcode='" . $OrderLine->StockID . "'
                                        AND orderno ='" . $_SESSION['Items']->OrderNo . "'";
                    $result = DB_query($sql, $db);
                    $myrow = DB_fetch_array($result);
                    if (($OrderLine->Price / $OrderLine->StandardCost) < 1.0 && $myrow['negative'] != "Y") {
                        $Cost = number_format($OrderLine->StandardCost);
                        $PriceDisplay = number_format($OrderLine->Price);
                        //echo '<script type="text/javascript" src="javascripts/jquery.js"></script>';
                        //Load facebox files for popups like lightbox
                        echo '<link href="' . $rootpath . '/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
                        echo '<script src="' . $rootpath . '/facebox/src/facebox.js" type="text/javascript"></script>';
                        echo "<script>jQuery.facebox('Sell price for $OrderLine->StockID at $PriceDisplay is below cost price of $Cost. Only prices higher than cost allowed. Please contact Salome/Corrine/Clinton to correct this.');</script>";
                        $BelowCost = true;
                    } elseif (($OrderLine->Price / $OrderLine->StandardCost) < 1.0 && $myrow['negative'] == "Y") {
                        $BelowCost = false;
                    }


                    if ($BelowCost) {
                        echo "Allo:" . $myrow['negative'];
                        echo '<div class="centre">
		    <p>' . prnMsg(_('Click menu')) . '</p></div>';
                        include('includes/footer.inc');
                        unset($_SESSION['Items']->LineItems);
                        exit;
                    }
                }


                if ($InputError == false) { //all good so let's get on with the processing

                    /* Now Get the area where the sale is to from the branches table */

                    $SQL = "SELECT 	area,
						defaultshipvia
				FROM custbranch
				WHERE custbranch.debtorno ='" . $_SESSION['Items' . $identifier]->DebtorNo . "'
				AND custbranch.branchcode = '" . $_SESSION['Items' . $identifier]->Branch . "'";

                    $ErrMsg = _('We were unable to load the area where the sale is to from the custbranch table');
                    $Result = DB_query($SQL, $db, $ErrMsg);
                    $myrow = DB_fetch_row($Result);
                    $Area = $myrow[0];
                    $DefaultShipVia = $myrow[1];
                    DB_free_result($Result);

                    /* company record read in on login with info on GL Links and debtors GL account */

                    if ($_SESSION['CompanyRecord'] == 0) {
                        /* The company data and preferences could not be retrieved for some reason */
                        prnMsg(_('The company information and preferences could not be retrieved. See your system administrator'), 'error');
                        include('includes/footer.inc');
                        exit;
                    }
//print_r($_SESSION['Items'.$identifier]->LineItems );
                    // *************************************************************************
                    //   S T A R T   O F   I N V O I C E   S Q L   P R O C E S S I N G
                    // *************************************************************************

                    /* First add the order to the database - it only exists in the session currently! */
                    DB_Txn_Begin($db);
                    //$OrderNo = GetNextTransNo(30, $db);
                    $TypeNoField = "typeno".$_SESSION['UserStockLocation'];
                    $OrderNo = $_SESSION['UserStockLocation'] .GetNextTransNoTenderType(30, $db, $TypeNoField);
                    $OrderNumber = $OrderNo;
                    $_SESSION['OrderNumber'] = $OrderNumber;

                    if ($_SESSION['Items' . $identifier]->CustCodeNum != "") {
                        $HeaderSQL = "INSERT INTO salesorders (	orderno,
												debtorno,
												branchcode,
												customerref,
                                                                                                custContact,
												comments,
												orddate,
												ordertype,
												shipvia,
												deliverto,
                                                                                                CustCode,
												deladd1,
                                                                                                deladd2,
                                                                                                deladd3,
												contactphone,
												contactemail,
												fromstkloc,
												deliverydate,
												confirmeddate,
												deliverblind)
											VALUES (
												'" . $OrderNo . "',
												'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
												'" . $_SESSION['Items' . $identifier]->Branch . "',
												'" . $_SESSION['Items' . $identifier]->CustRef . "',
                                                                                                '" . $_SESSION['Items' . $identifier]->CustRefVia . "',
												'" . $_SESSION['Items' . $identifier]->Comments . "',
												'" . Date('Y-m-d H:i') . "',
												'" . $_SESSION['Items' . $identifier]->DefaultSalesType . "',
												'" . $_SESSION['Items' . $identifier]->ShipVia . "',
												'" . $_SESSION['Items' . $identifier]->DeliverTo . "',
                                                                                                " .  $_SESSION['Items' . $identifier]->CustCodeNum . ",
                                                                                                '" . $_SESSION['Items' . $identifier]->Address1 . "',
                                                                                                '" . $_SESSION['Items' . $identifier]->StreetAddress1 . "',
												'" . $_SESSION['Items' . $identifier]->PhoneNo . "',
												'" . $_SESSION['Items' . $identifier]->Email . "',
												'" . _('no email') . "',
												'" . $_SESSION['Items' . $identifier]->Location . "',
												'" . Date('Y-m-d') . "',
												'" . Date('Y-m-d') . "',
												0)";
                    } else {
                        $HeaderSQL = "INSERT INTO salesorders (	orderno,
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
												contactphone,
												contactemail,
												fromstkloc,
												deliverydate,
												confirmeddate,
												deliverblind)
											VALUES (
												'" . $OrderNo . "',
												'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
												'" . $_SESSION['Items' . $identifier]->Branch . "',
												'" . $_SESSION['Items' . $identifier]->CustRef . "',
                                                                                                '" . $_SESSION['Items' . $identifier]->CustRefVia . "',
												'" . $_SESSION['Items' . $identifier]->Comments . "',
												'" . Date('Y-m-d H:i') . "',
												'" . $_SESSION['Items' . $identifier]->DefaultSalesType . "',
												'" . $_SESSION['Items' . $identifier]->ShipVia . "',
												'" . $_SESSION['Items' . $identifier]->DeliverTo . "',
												'" . $_SESSION['Items' . $identifier]->PhoneNo . "',
												'" . $_SESSION['Items' . $identifier]->Email . "',
												'" . _('no email') . "',
												'" . $_SESSION['Items' . $identifier]->Location . "',
												'" . Date('Y-m-d') . "',
												'" . Date('Y-m-d') . "',
												0)";
                    }


                    $ErrMsg = _('The order cannot be added because');
                    $InsertQryResult = DB_query($HeaderSQL, $db, $ErrMsg);
                    //echo $HeaderSQL ;
                    /* $StartOf_LineItemsSQL = "INSERT INTO salesorderdetails  (orderlineno,
                      orderno,
                      stkcode,
                      unitprice,
                      quantity,
                      discountpercent,
                      narrative,
                      itemdue,
                      actualdispatchdate,
                      qtyinvoiced,
                      completed,
                      packdesc)
                      VALUES(
                      "; */

                    $DbgMsg = _('Trouble inserting a line of a sales order. The SQL that failed was');
                    foreach ($_SESSION['Items' . $identifier]->LineItems as $StockItem) {

                        if ($StockItem->PackUnits > 0 || $StockItem->Packsize > 0) {
                            $pakDescription = $StockItem->PackUnits . ' X ' . $StockItem->Packsize . ' ' . $StockItem->Units;
                        } else {
                            $pakDescription = "";
                        }
                        $StartOf_LineItemsSQL = "INSERT INTO salesorderdetails  (orderlineno,
																orderno,
																stkcode,
																unitprice,
																quantity,
																discountpercent,
																narrative,
																itemdue,
																actualdispatchdate,
																qtyinvoiced,
																completed,
                                                                                                                                packdesc)
                                                                                                                                VALUES('" . $StockItem->LineNumber . "',
					'" . $OrderNo . "',
					'" . $StockItem->StockID . "',
					'" . $StockItem->Price . "',
					'" . $StockItem->Quantity . "',
					'" . floatval($StockItem->DiscountPercent) . "',
					'" . $StockItem->Narrative . "',
					'" . Date('Y-m-d') . "',
					'" . Date('Y-m-d') . "',
					'" . $StockItem->Quantity . "',
					1,
                                        '" . $pakDescription . "')";

                        $ErrMsg = _('Unable to add the sales order line');
                        // echo "<br> ".$StartOf_LineItemsSQL ;
                        $Ins_LineItemResult = DB_query($StartOf_LineItemsSQL, $db, $ErrMsg, true, true);
                        //}
                        //unset($_SESSION['Items'.$identifier]->LineItems);
                        /* Now check to see if the item is manufactured
                         * 			and AutoCreateWOs is on
                         * 			and it is a real order (not just a quotation) */

                        if ($StockItem->MBflag == 'M'
                                and $_SESSION['AutoCreateWOs'] == 1) { //oh yeah its all on!
                            //now get the data required to test to see if we need to make a new WO
                            $QOHResult = DB_query("SELECT SUM(quantity) FROM locstock WHERE stockid='" . $StockItem->StockID . "'", $db);
                            $QOHRow = DB_fetch_row($QOHResult);
                            $QOH = $QOHRow[0];

                            $SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
									FROM salesorderdetails
									WHERE salesorderdetails.stkcode = '" . $StockItem->StockID . "'
									AND salesorderdetails.completed = 0";
                            $DemandResult = DB_query($SQL, $db);
                            $DemandRow = DB_fetch_row($DemandResult);
                            $QuantityDemand = $DemandRow[0];

                            $SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
								FROM salesorderdetails,
									bom,
									stockmaster
								WHERE salesorderdetails.stkcode=bom.parent
								AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
								AND bom.component='" . $StockItem->StockID . "'
								AND stockmaster.stockid=bom.parent
								AND salesorderdetails.completed=0";
                            $AssemblyDemandResult = DB_query($SQL, $db);
                            $AssemblyDemandRow = DB_fetch_row($AssemblyDemandResult);
                            $QuantityAssemblyDemand = $AssemblyDemandRow[0];

                            $SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as qtyonorder
								FROM purchorderdetails INNER JOIN purchorders
								ON purchorderdetails.orderno = purchorders.orderno
								WHERE purchorderdetails.itemcode = '" . $StockItem->StockID . "'
								AND purchorderdetails.completed = 0
								AND purchorders.status<>'Rejected'
								AND purchorders.status<>'Pending'
								AND purchorders.status<>'Completed'";
                            $PurchOrdersResult = DB_query($SQL, $db);
                            $PurchOrdersRow = DB_fetch_row($PurchOrdersResult);
                            $QuantityPurchOrders = $PurchOrdersRow[0];

                            $SQL = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) as qtyonorder
								FROM woitems INNER JOIN workorders
								ON woitems.wo=workorders.wo
								WHERE woitems.stockid = '" . $StockItem->StockID . "'
								AND woitems.qtyreqd > woitems.qtyrecd
								AND workorders.closed = 0";
                            $WorkOrdersResult = DB_query($SQL, $db);
                            $WorkOrdersRow = DB_fetch_row($WorkOrdersResult);
                            $QuantityWorkOrders = $WorkOrdersRow[0];

                            //Now we have the data - do we need to make any more?
                            $ShortfallQuantity = $QOH - $QuantityDemand - $QuantityAssemblyDemand + $QuantityPurchOrders + $QuantityWorkOrders;

                            if ($ShortfallQuantity < 0) { //then we need to make a work order
                                //How many should the work order be for??
                                if ($ShortfallQuantity + $StockItem->EOQ < 0) {
                                    $WOQuantity = -$ShortfallQuantity;
                                } else {
                                    $WOQuantity = $StockItem->EOQ;
                                }

                                $WONo = GetNextTransNo(40, $db);
                                $ErrMsg = _('Unable to insert a new work order for the sales order item');
                                $InsWOResult = DB_query("INSERT INTO workorders (wo,
													 loccode,
													 requiredby,
													 startdate)
									 VALUES ('" . $WONo . "',
											'" . $_SESSION['DefaultFactoryLocation'] . "',
											'" . Date('Y-m-d') . "',
											'" . Date('Y-m-d') . "')", $db, $ErrMsg, $DbgMsg, true);
                                //Need to get the latest BOM to roll up cost
                                $CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*bom.quantity) AS cost
																	FROM stockmaster INNER JOIN bom
																	ON stockmaster.stockid=bom.component
																	WHERE bom.parent='" . $StockItem->StockID . "'
																	AND bom.loccode='" . $_SESSION['DefaultFactoryLocation'] . "'", $db);
                                $CostRow = DB_fetch_row($CostResult);
                                if (is_null($CostRow[0]) OR $CostRow[0] == 0) {
                                    $Cost = 0;
                                    prnMsg(_('In automatically creating a work order for') . ' ' . $StockItem->StockID . ' ' . _('an item on this sales order, the cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'), 'warn');
                                } else {
                                    $Cost = $CostRow[0];
                                }

                                // insert parent item info
                                $sql = "INSERT INTO woitems (wo,
												 stockid,
												 qtyreqd,
												 stdcost)
									 VALUES ('" . $WONo . "',
											 '" . $StockItem->StockID . "',
											 '" . $WOQuantity . "',
											 '" . $Cost . "')";
                                $ErrMsg = _('The work order item could not be added');
                                $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);

                                //Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
                                WoRealRequirements($db, $WONo, $_SESSION['DefaultFactoryLocation'], $StockItem->StockID);

                                $FactoryManagerEmail = _('A new work order has been created for') .
                                        ":\n" . $StockItem->StockID . ' - ' . $StockItem->ItemDescription . ' x ' . $WOQuantity . ' ' . $StockItem->Units .
                                        "\n" . _('These are for') . ' ' . $_SESSION['Items' . $identifier]->CustomerName . ' ' . _('there order ref') . ': ' . $_SESSION['Items' . $identifier]->CustRef . ' ' . _('our order number') . ': ' . $OrderNo;

                                if ($StockItem->Serialised AND $StockItem->NextSerialNo > 0) {
                                    //then we must create the serial numbers for the new WO also
                                    $FactoryManagerEmail .= "\n" . _('The following serial numbers have been reserved for this work order') . ':';

                                    for ($i = 0; $i < $WOQuantity; $i++) {

                                        $result = DB_query("SELECT serialno FROM stockserialitems
													WHERE serialno='" . ($StockItem->NextSerialNo + $i) . "'
													AND stockid='" . $StockItem->StockID . "'", $db);
                                        if (DB_num_rows($result) != 0) {
                                            $WOQuantity++;
                                            prnMsg(($StockItem->NextSerialNo + $i) . ': ' . _('This automatically generated serial number already exists - it cannot be added to the work order'), 'error');
                                        } else {
                                            $sql = "INSERT INTO woserialnos (wo,
																	stockid,
																	serialno)
														VALUES ('" . $WONo . "',
																'" . $StockItem->StockID . "',
																'" . ($StockItem->NextSerialNo + $i) . "')";
                                            $ErrMsg = _('The serial number for the work order item could not be added');
                                            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                                            $FactoryManagerEmail .= "\n" . ($StockItem->NextSerialNo + $i);
                                        }
                                    } //end loop around creation of woserialnos
                                    $NewNextSerialNo = ($StockItem->NextSerialNo + $WOQuantity + 1);
                                    $ErrMsg = _('Could not update the new next serial number for the item');
                                    $UpdateSQL = "UPDATE stockmaster SET nextserialno='" . $NewNextSerialNo . "' WHERE stockid='" . $StockItem->StockID . "'";
                                    $UpdateNextSerialNoResult = DB_query($UpdateSQL, $db, $ErrMsg, $DbgMsg, true);
                                } // end if the item is serialised and nextserialno is set

                                $EmailSubject = _('New Work Order Number') . ' ' . $WONo . ' ' . _('for') . ' ' . $StockItem->StockID . ' x ' . $WOQuantity;
                                //Send email to the Factory Manager
                                mail($_SESSION['FactoryManagerEmail'], $EmailSubject, $FactoryManagerEmail);
                            } //end if with this sales order there is a shortfall of stock - need to create the WO
                        }//end if auto create WOs in on
                    } /* end inserted line items into sales order details */
                    // echo "Outside this";
                    //$result = DB_Txn_Commit($db);

                    prnMsg(_('Order Number') . ' ' . $OrderNo . ' ' . _('has been entered'), 'success');

                    /* End of insertion of new sales order */

                    /* Now Get the next invoice number - GetNextTransNo() function in SQL_CommonFunctions
                     * GetPeriod() in includes/DateFunctions.inc */
                     /* Start an SQL transaction */

                   // DB_Txn_Begin($db);
                    //$InvoiceNo = GetNextTransNo(10, $db);
                    $TypeNoField = "typeno".$_SESSION['UserStockLocation'];
                    $InvoiceNo = $_SESSION['UserStockLocation'] .GetNextTransNoTenderType(10, $db, $TypeNoField);
                    $_SESSION['InvNumber'] = $InvoiceNo;
                    $PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

                   

                    $DefaultDispatchDate = Date('Y-m-d');

                    /* Update order header for invoice charged on */
                    $SQL = "UPDATE salesorders SET comments = CONCAT(comments,'" . ' ' . _('Invoice') . ': ' . "','" . $InvoiceNo . "') WHERE orderno= '" . $OrderNo . "'";

                    $ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order header could not be updated with the invoice number');
                    $DbgMsg = _('The following SQL to update the sales order was used');
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                    /* Now insert the DebtorTrans */

                    $SQL = "INSERT INTO debtortrans (transno,
										type,
										debtorno,
										branchcode,
										trandate,
										inputdate,
										prd,
										reference,
										tpe,
										order_,
										ovamount,
										ovgst,
                                                                                invprofit,
										rate,
										invtext,
										shipvia,
										user,
										loccode,
										alloc )
			VALUES (
				'" . $InvoiceNo . "',
				10,
				'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
				'" . $_SESSION['Items' . $identifier]->Branch . "',
				'" . $DefaultDispatchDate . "',
				'" . date('Y-m-d H-i-s') . "',
				'" . $PeriodNo . "',
				'" . $_SESSION['Items' . $identifier]->CustRef . "',
				'" . $_SESSION['Items' . $identifier]->DefaultSalesType . "',
				'" . $OrderNo . "',
				'" . number_format($_SESSION['Items' . $identifier]->total, 2, '.', '') . "',
				'" . number_format($_POST['TaxTotal'], 2, '.', '') . "',
                                '" . number_format($_SESSION['Items' . $identifier]->total, 2, '.', '') . "',
				'" . $ExRate . "',
				'" . $_SESSION['Items' . $identifier]->Comments . "',
				'" . $_SESSION['Items' . $identifier]->ShipVia . "',
				'" . $_SESSION['UserID'] . "',
				'" . $_SESSION['UserStockLocation'] . "',
				'" . number_format(($_SESSION['Items' . $identifier]->total + filter_number_format($_POST['TaxTotal'])), 2, '.', '') . "')";

//nnumber_format($number, 2, '.', '')				
//n				'" . locale_number_format($_SESSION['Items'.$identifier]->total,2) . "',
//n				'" . filter_number_format($_POST['TaxTotal']) . "',
//n				'" . locale_number_format(($_SESSION['Items'.$identifier]->total + filter_number_format($_POST['TaxTotal'])),2) . "')";

                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
                    $DbgMsg = _('The following SQL to insert the debtor transaction record was used');
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                    $DebtorTransID = DB_Last_Insert_ID($db, 'debtortrans', 'id');

                    /* Insert the tax totals for each tax authority where tax was charged on the invoice */
                    foreach ($_SESSION['Items' . $identifier]->TaxTotals AS $TaxAuthID => $TaxAmount) {

                        $SQL = "INSERT INTO debtortranstaxes (debtortransid,
													taxauthid,
													taxamount)
										VALUES ('" . $DebtorTransID . "',
											'" . $TaxAuthID . "',
										'" . locale_number_format($TaxAmount / $ExRate, 2) . "')";
//n											'" . $TaxAmount/$ExRate . "')";

                        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction taxes records could not be inserted because');
                        $DbgMsg = _('The following SQL to insert the debtor transaction taxes record was used');
                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                    }

                    //Loop around each item on the sale and process each in turn
                    foreach ($_SESSION['Items' . $identifier]->LineItems as $OrderLine) {
                        /* Update location stock records if not a dummy stock item
                          need the MBFlag later too so save it to $MBFlag */
                        $Result = DB_query("SELECT mbflag FROM stockmaster WHERE stockid = '" . $OrderLine->StockID . "'", $db);
                        $myrow = DB_fetch_row($Result);
                        $MBFlag = $myrow[0];
                        if ($MBFlag == 'B' OR $MBFlag == 'M') {
                            $Assembly = False;

                            /* Need to get the current location quantity
                              will need it later for the stock movement */
                            $SQL = "SELECT locstock.quantity
								FROM locstock
								WHERE locstock.stockid='" . $OrderLine->StockID . "'
								AND loccode= '" . $_SESSION['Items' . $identifier]->Location . "'";
                            $ErrMsg = _('WARNING') . ': ' . _('Could not retrieve current location stock');
                            $Result = DB_query($SQL, $db, $ErrMsg);

                            if (DB_num_rows($Result) == 1) {
                                $LocQtyRow = DB_fetch_row($Result);
                                $QtyOnHandPrior = $LocQtyRow[0];
                            } else {
                                /* There must be some error this should never happen */
                                $QtyOnHandPrior = 0;
                            }

                            $SQL = "UPDATE locstock
							SET quantity = locstock.quantity - " . $OrderLine->Quantity . "
							WHERE locstock.stockid = '" . $OrderLine->StockID . "'
							AND loccode = '" . $_SESSION['Items' . $identifier]->Location . "'";

                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated because');
                            $DbgMsg = _('The following SQL to update the location stock record was used');
                            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                        } else if ($MBFlag == 'A') { /* its an assembly */
                            /* Need to get the BOM for this part and make
                              stock moves for the components then update the Location stock balances */
                            $Assembly = True;
                            $StandardCost = 0; /* To start with - accumulate the cost of the comoponents for use in journals later on */
                            $SQL = "SELECT bom.component,
						bom.quantity,
						stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standard
						FROM bom,
							stockmaster
						WHERE bom.component=stockmaster.stockid
						AND bom.parent='" . $OrderLine->StockID . "'
						AND bom.effectiveto > '" . Date('Y-m-d') . "'
						AND bom.effectiveafter < '" . Date('Y-m-d') . "'";
                            

                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not retrieve assembly components from the database for') . ' ' . $OrderLine->StockID . _('because') . ' ';
                            $DbgMsg = _('The SQL that failed was');
                            $AssResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                            while ($AssParts = DB_fetch_array($AssResult, $db)) {

                                $StandardCost += ( $AssParts['standard'] * $AssParts['quantity']);
                                /* Need to get the current location quantity
                                  will need it later for the stock movement */
                                $SQL = "SELECT locstock.quantity
									FROM locstock
									WHERE locstock.stockid='" . $AssParts['component'] . "'
									AND loccode= '" . $_SESSION['Items' . $identifier]->Location . "'";

                                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Can not retrieve assembly components location stock quantities because ');
                                $DbgMsg = _('The SQL that failed was');
                                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                if (DB_num_rows($Result) == 1) {
                                    $LocQtyRow = DB_fetch_row($Result);
                                    $QtyOnHandPrior = $LocQtyRow[0];
                                } else {
                                    /* There must be some error this should never happen */
                                    $QtyOnHandPrior = 0;
                                }
                                if (empty($AssParts['standard'])) {
                                    $AssParts['standard'] = 0;
                                }
                                $SQL = "INSERT INTO stockmoves (stockid,
													type,
													transno,
													loccode,
													trandate,
													debtorno,
													branchcode,
													prd,
													reference,
													qty,
													standardcost,
													show_on_inv_crds,
													newqoh,
                                                                                                        packdesc
						) VALUES (
													'" . $AssParts['component'] . "',
													 10,
													'" . $InvoiceNo . "',
													'" . $_SESSION['Items' . $identifier]->Location . "',
													'" . $DefaultDispatchDate . "',
													'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
													'" . $_SESSION['Items' . $identifier]->Branch . "',
													'" . $PeriodNo . "',
													'" . _('Assembly') . ': ' . $OrderLine->StockID . ' ' . _('Order') . ': ' . $OrderNo . "',
													'" . -$AssParts['quantity'] * $OrderLine->Quantity . "',
													'" . $AssParts['standard'] . "',
													0,
													newqoh-" . ($AssParts['quantity'] * $OrderLine->Quantity) . " ,
                                                                                                            
                                                                                                         '" . $OrderLine->PackUnits . ' X ' . $OrderLine->Packsize . "' )";

                                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for the assembly components of') . ' ' . $OrderLine->StockID . ' ' . _('could not be inserted because');
                                $DbgMsg = _('The following SQL to insert the assembly components stock movement records was used');
                                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


                                $SQL = "UPDATE locstock
							SET quantity = locstock.quantity - " . $AssParts['quantity'] * $OrderLine->Quantity . "
							WHERE locstock.stockid = '" . $AssParts['component'] . "'
							AND loccode = '" . $_SESSION['Items' . $identifier]->Location . "'";

                                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated for an assembly component because');
                                $DbgMsg = _('The following SQL to update the locations stock record for the component was used');
                                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                            } /* end of assembly explosion and updates */

                            /* Update the cart with the recalculated standard cost from the explosion of the assembly's components */
                            $_SESSION['Items' . $identifier]->LineItems[$OrderLine->LineNumber]->StandardCost = $StandardCost;
                            $OrderLine->StandardCost = $StandardCost;
                        } /* end of its an assembly */

                        // Insert stock movements - with unit cost
                        $LocalCurrencyPrice = ($OrderLine->Price / $ExRate);

                        if (empty($OrderLine->StandardCost)) {
                            $OrderLine->StandardCost = 0;
                        }
                        if ($MBFlag == 'B' OR $MBFlag == 'M') {
                            $SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												debtorno,
												branchcode,
												price,
												prd,
												reference,
												qty,
												discountpercent,
												standardcost,
												newqoh,
												narrative )
						VALUES ('" . $OrderLine->StockID . "',
								10,
								'" . $InvoiceNo . "',
								'" . $_SESSION['Items' . $identifier]->Location . "',
								'" . $DefaultDispatchDate . "',
								'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
								'" . $_SESSION['Items' . $identifier]->Branch . "',
								'" . $LocalCurrencyPrice . "',
								'" . $PeriodNo . "',
								'" . $OrderNo . "',
								'" . -number_format($OrderLine->Quantity, 2, '.', '') . "',
								'" . $OrderLine->DiscountPercent . "',
								'" . $OrderLine->StandardCost . "',
								'" . ($QtyOnHandPrior - $OrderLine->Quantity) . "',
								'" . $OrderLine->Narrative . "' )";
                        } else {
                            // its an assembly or dummy and assemblies/dummies always have nil stock (by definition they are made up at the time of dispatch  so new qty on hand will be nil
                            if (empty($OrderLine->StandardCost)) {
                                $OrderLine->StandardCost = 0;
                            }
                            $SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												debtorno,
												branchcode,
												price,
												prd,
												reference,
												qty,
												discountpercent,
												standardcost,
												narrative )
						VALUES ('" . $OrderLine->StockID . "',
										10,
										'" . $InvoiceNo . "',
										'" . $_SESSION['Items' . $identifier]->Location . "',
										'" . $DefaultDispatchDate . "',
										'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
										'" . $_SESSION['Items' . $identifier]->Branch . "',
										'" . $LocalCurrencyPrice . "',
										'" . $PeriodNo . "',
										'" . $OrderNo . "',
										'" . -$OrderLine->Quantity . "',
										'" . $OrderLine->DiscountPercent . "',
										'" . $OrderLine->StandardCost . "',
										'" . $OrderLine->Narrative . "')";
                        }

                        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
                        $DbgMsg = _('The following SQL to insert the stock movement records was used');
                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                        /* Get the ID of the StockMove... */
                        $StkMoveNo = DB_Last_Insert_ID($db, 'stockmoves', 'stkmoveno');

                        /* Insert the taxes that applied to this line */
                        foreach ($OrderLine->Taxes as $Tax) {

                            $SQL = "INSERT INTO stockmovestaxes (stkmoveno,
									taxauthid,
									taxrate,
									taxcalculationorder,
									taxontax)
						VALUES ('" . $StkMoveNo . "',
							'" . $Tax->TaxAuthID . "',
							'" . $Tax->TaxRate . "',
							'" . $Tax->TaxCalculationOrder . "',
							'" . $Tax->TaxOnTax . "')";

                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Taxes and rates applicable to this invoice line item could not be inserted because');
                            $DbgMsg = _('The following SQL to insert the stock movement tax detail records was used');
                            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                        } //end for each tax for the line




                        /* Insert Sales Analysis records */

                        $SQL = "SELECT COUNT(*),
					salesanalysis.stockid,
					salesanalysis.stkcategory,
					salesanalysis.cust,
					salesanalysis.custbranch,
					salesanalysis.area,
					salesanalysis.periodno,
					salesanalysis.typeabbrev,
					salesanalysis.salesperson
				FROM salesanalysis,
					custbranch,
					stockmaster
				WHERE salesanalysis.stkcategory=stockmaster.categoryid
				AND salesanalysis.stockid=stockmaster.stockid
				AND salesanalysis.cust=custbranch.debtorno
				AND salesanalysis.custbranch=custbranch.branchcode
				AND salesanalysis.area=custbranch.area
				AND salesanalysis.salesperson=custbranch.salesman
				AND salesanalysis.typeabbrev ='" . $_SESSION['Items' . $identifier]->DefaultSalesType . "'
				AND salesanalysis.periodno='" . $PeriodNo . "'
				AND salesanalysis.cust " . LIKE . " '" . $_SESSION['Items' . $identifier]->DebtorNo . "'
				AND salesanalysis.custbranch " . LIKE . " '" . $_SESSION['Items' . $identifier]->Branch . "'
				AND salesanalysis.stockid " . LIKE . " '" . $OrderLine->StockID . "'
				AND salesanalysis.budgetoractual=1
				GROUP BY salesanalysis.stockid,
					salesanalysis.stkcategory,
					salesanalysis.cust,
					salesanalysis.custbranch,
					salesanalysis.area,
					salesanalysis.periodno,
					salesanalysis.typeabbrev,
					salesanalysis.salesperson";

                        $ErrMsg = _('The count of existing Sales analysis records could not run because');
                        $DbgMsg = _('SQL to count the no of sales analysis records');
                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                        $myrow = DB_fetch_row($Result);
//mz
                        if ($myrow[0] > 0) { /* Update the existing record that already exists */

                            $SQL = "UPDATE salesanalysis
							SET amt=amt+" . ($OrderLine->Price * $OrderLine->Quantity / $ExRate) . ",
								cost=cost+" . ($OrderLine->StandardCost * $OrderLine->Quantity) . ",
								qty=qty +" . $OrderLine->Quantity . ",
								disc=disc+" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->Quantity / $ExRate) . "
							WHERE salesanalysis.area='" . $myrow[5] . "'
								AND salesanalysis.salesperson='" . $myrow[8] . "'
								AND typeabbrev ='" . $_SESSION['Items' . $identifier]->DefaultSalesType . "'
								AND periodno = '" . $PeriodNo . "'
								AND cust " . LIKE . " '" . $_SESSION['Items' . $identifier]->DebtorNo . "'
								AND custbranch " . LIKE . " '" . $_SESSION['Items' . $identifier]->Branch . "'
								AND stockid " . LIKE . " '" . $OrderLine->StockID . "'
								AND salesanalysis.stkcategory ='" . $myrow[2] . "'
								AND budgetoractual=1";
                        } else { /* insert a new sales analysis record */
                            $InvoicingTransType = "POS-LOYAL-INVOICE";
                            $SQL = "INSERT INTO salesanalysis (	typeabbrev,
													periodno,
													amt,
													cost,
													cust,
													custbranch,
													qty,
													disc,
													stockid,
													area,
													budgetoractual,
													salesperson,
													stkcategory,
                                                                                                        transdate,
                                                user,
                                                transno,
                                                transtype)
					SELECT '" . $_SESSION['Items' . $identifier]->DefaultSalesType . "',
						'" . $PeriodNo . "',
						'" . ($OrderLine->Price * $OrderLine->Quantity / $ExRate) . "',
						'" . ($OrderLine->StandardCost * $OrderLine->Quantity) . "',
						'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
						'" . $_SESSION['Items' . $identifier]->Branch . "',
						'" . number_format($OrderLine->Quantity, 2, '.', '') . "', 
						'" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->Quantity / $ExRate) . "',
						'" . $OrderLine->StockID . "',
						custbranch.area,
						1,
						custbranch.salesman,
						stockmaster.categoryid,
                                                '" . $DefaultDispatchDate . "',
                                                '".$_SESSION['UserID']."',
                                                '".$InvoiceNo."',
                                                '".$InvoicingTransType."'
					FROM stockmaster,
						custbranch
					WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'
					AND custbranch.debtorno = '" . $_SESSION['Items' . $identifier]->DebtorNo . "'
					AND custbranch.branchcode='" . $_SESSION['Items' . $identifier]->Branch . "'";
                        }
//m						'" . locale_number_format($OrderLine->Price * $OrderLine->Quantity / $ExRate,2) . "',
//m						'" . locale_number_format($OrderLine->StandardCost * $OrderLine->Quantity,2) . "',

                        $ErrMsg = _('Sales analysis record could not be added or updated because');
                        $DbgMsg = _('The following SQL to insert the sales analysis record was used');
                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                        /* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost */

                        if ($_SESSION['CompanyRecord']['gllink_stock'] == 1 AND $OrderLine->StandardCost != 0) {

                            /* first the cost of sales entry 1500 */

                            $SQL = "INSERT INTO gltrans (	type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
						user,
						loccode,
												amount)
										VALUES ( 10,
												'" . $InvoiceNo . "',
												'" . $DefaultDispatchDate . "',
												'" . $PeriodNo . "',
												'" . GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items' . $identifier]->DefaultSalesType, $db) . "',
												'" . $_SESSION['Items' . $identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . "',
						'" . $_SESSION['UserID'] . "',
						'" . $_SESSION['UserStockLocation'] . "',														

												'" . number_format($OrderLine->StandardCost * $OrderLine->Quantity, 2, '.', '') . "')";

                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of sales GL posting could not be inserted because');
                            $DbgMsg = _('The following SQL to insert the GLTrans record was used');
                            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                            /* now the stock entry 3700 */
                            $StockGLCode = GetStockGLCode($OrderLine->StockID, $db);

                            $SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
						user,
						loccode,
											amount )
										VALUES ( 10,
											'" . $InvoiceNo . "',
											'" . $DefaultDispatchDate . "',
											'" . $PeriodNo . "',
											'" . $StockGLCode['stockact'] . "',
											'" . $_SESSION['Items' . $identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . "',
						'" . $_SESSION['UserID'] . "',
						'" . $_SESSION['UserStockLocation'] . "',														
											'" . number_format((-$OrderLine->StandardCost * $OrderLine->Quantity), 2, '.', '') . "')";

                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the cost of sales GL posting could not be inserted because');
                            $DbgMsg = _('The following SQL to insert the GLTrans record was used');
                            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                        } /* end of if GL and stock integrated and standard cost !=0 */

                        if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 AND $OrderLine->Price != 0) {

                            //Post sales transaction to GL credit sales 2200
                            $SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items' . $identifier]->DefaultSalesType, $db);

                            $SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
						user,
						loccode,
											amount )
										VALUES ( 10,
											'" . $InvoiceNo . "',
											'" . $DefaultDispatchDate . "',
											'" . $PeriodNo . "',
											'" . $SalesGLAccounts['salesglcode'] . "',
											'" . $_SESSION['Items' . $identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->Quantity . " @ " . $OrderLine->Price . "',
						'" . $_SESSION['UserID'] . "',
						'" . $_SESSION['UserStockLocation'] . "',														
											'" . number_format((-$OrderLine->Price * $OrderLine->Quantity / $ExRate), 2, '.', '') . "')";

                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales GL posting could not be inserted because');
                            $DbgMsg = '<br />' . _('The following SQL to insert the GLTrans record was used');
                            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                            //Post sales transaction to discount

                            if ($OrderLine->DiscountPercent != 0) {

                                $SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
						user,
						loccode,																						
												amount )
												VALUES ( 10,
													'" . $InvoiceNo . "',
													'" . $DefaultDispatchDate . "',
													'" . $PeriodNo . "',
													'" . $SalesGLAccounts['discountglcode'] . "',
													'" . $_SESSION['Items' . $identifier]->DebtorNo . " - " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%',
					'" . $_SESSION['UserID'] . "',
						'" . $_SESSION['UserStockLocation'] . "',														
													'" . ($OrderLine->Price * $OrderLine->Quantity * $OrderLine->DiscountPercent / $ExRate) . "')";

//													'" . locale_number_format(($OrderLine->Price * $OrderLine->Quantity * $OrderLine->DiscountPercent/$ExRate),2) . "')";

                                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales discount GL posting could not be inserted because');
                                $DbgMsg = _('The following SQL to insert the GLTrans record was used');
                                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                            } /* end of if discount !=0 */
                        } /* end of if sales integrated with debtors */
                    } /* end of OrderLine loop */

                    if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1) {

                        /* Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales */
                        if (($_SESSION['Items' . $identifier]->total + filter_number_format($_POST['TaxTotal'])) != 0) {
//$DrAmount = locale_number_format(((filter_number_format($_SESSION['Items'.$identifier]->total) + filter_number_format($_POST['TaxTotal']))/$ExRate),2) ;
                            $DrAmount = round(($_SESSION['Items' . $identifier]->total + $_POST['TaxTotal']) / $ExRate * 100) / 100;
                            $SQL = "INSERT INTO gltrans (	type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
						user,
						loccode,																						
												amount	)
											VALUES ( 10,
												'" . $InvoiceNo . "',
												'" . $DefaultDispatchDate . "',
												'" . $PeriodNo . "',
												'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
												'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
						'" . $_SESSION['UserID'] . "',
						'" . $_SESSION['UserStockLocation'] . "',													
												'" . $DrAmount . "')";
//m												'" . (($_SESSION['Items'.$identifier]->total + filter_number_format($_POST['TaxTotal']))/$ExRate) . "')";												
                            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The total debtor GL posting could not be inserted because');
                            $DbgMsg = _('The following SQL to insert the total debtors control GLTrans record was used');
                            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                        }
                        foreach ($_SESSION['Items' . $identifier]->TaxTotals as $TaxAuthID => $TaxAmount) {
                            if ($TaxAmount != 0) {
                                $TxSbmtAmount = number_format(($TaxAmount / $ExRate), 2, '.', '');
                                $SQL = "INSERT INTO gltrans (	type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
						user,
						loccode,
													amount	)
												VALUES ( 10,
													'" . $InvoiceNo . "',
													'" . $DefaultDispatchDate . "',
													'" . $PeriodNo . "',
													'" . $_SESSION['Items' . $identifier]->TaxGLCodes[$TaxAuthID] . "',
													'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
						'" . $_SESSION['UserID'] . "',
						'" . $_SESSION['UserStockLocation'] . "',													
													'" . -$TxSbmtAmount . "')";

                                $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The tax GL posting could not be inserted because');
                                $DbgMsg = _('The following SQL to insert the GLTrans record was used');
                                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                            }
                        }

                        EnsureGLEntriesBalance(10, $InvoiceNo, $db);

//n EnsureGL balances has weird rounding figure needs to be adjusted to if more than 0.001 round away 

                        /* Also if GL is linked to debtors need to process the debit to bank and credit to debtors for the payment */
                        /* Need to figure out the cross rate between customer currency and bank account currency */
//------------------------------------------------------------------------------------------			
//                   DIDERSON RECEIPT START
//---------------------------------------------------------------------------------------------
                        if ($_POST['AmountPaid'] != 0) {
                           // $ReceiptNumber = GetNextTransNo(12, $db);
                            $TypeNoField = "typeno".$_SESSION['UserStockLocation'];
                            $ReceiptNumber = $_SESSION['UserStockLocation'] .GetNextTransNoTenderType(12, $db, $TypeNoField);
                            $NextCreditNumber = $ReceiptNumber;
                            $_SESSION['CreditNumber'] = $NextCreditNumber;

                            /* Check that tender type has at least one value and check against total for sale */
                            $TenderTypeTotals = 0;
                            $TenderTypeArray = array();
                            foreach ($_POST['tenderid'] as $key => $val) {
                                //echo "<br>Key rot is " . $key . "<br>";
                                if (is_numeric($val) && $val != "") {
                                    // Check that only one tendertype was selected PDT */
                                    $FoundTender = $key;
                                    //echo "found key is " . $FoundTender . "<br>";
                                    //echo "val is " . $val . "<br>";
                                    $_POST['TenderSet'] = TRUE;
                                    $_POST['TenderTypeCode'] = $key;
                                    $_POST['TenderValue'] = $val;
                                    //$_POST['TenderTypeCode'] = $_POST[$key];
                                    $_POST['GrandTotal'] = $_POST['TenderValue'] + $TenderTypeTotals;
//			$TenderTypeTotals 		= locale_number_format($_POST['GrandTotal'],2);
                                    $TenderTypeTotals = $_POST['GrandTotal'];
                                    $TenderTypeArray[$key] = array(
                                        'TenderTypeGlCode' => $_POST['GlAccount'][$key],
                                        'TenderValue' => $_POST['TenderValue'],
                                        'TenderTypeCode' => $_POST['TenderTypeCode']
                                    );
                                } else {
                                    //echo "<br>Key under else is" . $key . "<br>";
                                    if (!is_numeric($val) && $val != "") {
                                        prnMsg(_('Only numeric values can be entered'), 'error');
                                        echo '<div class="centre">
					<INPUT type="button" value="Click here to go back" onClick="history.back()"></div>';
                                        include('includes/footer.inc');
                                        exit;
                                    }
                                }

                                //echo "<br>Key is " . $key . "<br>";
                            } //END FOREACH	
// $Price = filter_number_format($_POST['Price_' . $OrderLine->LineNumber])
//$_SESSION['TenderTotal'] = locale_number_format(($TenderTypeTotals),2);
                            $_SESSION['TenderTotal'] = $TenderTypeTotals;
                            $_POST['TenderTypeArray'] = $TenderTypeArray;
//----------------------DEUXIEME PARTI ------------------------------------
//-------------------------------------------------------------------------------



                            /* This is the credit received area */
                            $Reference = ""; //concact the tendertypes for now
                            foreach ($TenderTypeArray as $key => $val) {
                                $GlCode = $val['TenderTypeGlCode'];
                                $TenderTypeCode = $val['TenderTypeCode'];
                                $TenderValue = $val['TenderValue'];
                                //echo "tender value is " . $TenderValue . "<br>";
                                //echo "TenderTypeCode is " . $TenderTypeCode . "<br>";
                                if (isset($CNcn) OR isset($CCcc) OR isset($CDcd) OR isset($CFcf)) {
                                    
                                } else {
//$TenderValue =  locale_number_format($_POST['AmountReceved'],2);
//$_SESSION['TenderTotal'] = locale_number_format($_POST['AmountReceved'],2) ;
                                    $TenderValue = $_POST['AmountReceved'];
                                    $_SESSION['TenderTotal'] = $_POST['AmountReceved'];
                                }
//$TenderValue = number_format($TenderValue,2);
                                $ReceiptItem->tag = 0;
                                if ($Reference != "") {
                                    $Reference .= "/";
                                }
                                //echo "period is " . $PeriodNo . "<br>";
                                $Reference .= $TenderTypeCode . "-" . $TenderValue;
                                //echo "receipt number is " . $_SESSION['CreditNumber'] . "<br>";
                                /* Debit the Debtors Control Account as Credit Payment */
//$TenderSubmitValue =		locale_number_format(filter_number_format($TenderValue),2);
                                $TenderSubmitValue = round($TenderValue * 100) / 100;
//$TnderAmnt =                round($_SESSION['TenderTotal']*100)/100 ;
                                $SQL = 'INSERT INTO gltrans (type,
			 			typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						user,
						loccode,
						' . $TenderTypeCode . ',
						tag,
						invnumber
						)
					VALUES (12,
						' . "'" . $_SESSION['CreditNumber'] . "',
						'" . FormatDateForSQL($_POST['PamentDate']) . "',
						" . $PeriodNo . ",
						'" . $GlCode . "',
						'" . $TenderTypeCode . "-" . $TenderValue . "',
						" . $TenderSubmitValue . ",
						'" . $_SESSION['UserID'] . "',
						'" . $_SESSION['UserStockLocation'] . "',
						" . $TenderSubmitValue . ",
						'" . $ReceiptItem->tag . "',
						'" . $_SESSION['InvNumber'] . "'
						" . ')';
//m						" . locale_number_format($TenderValue,2) . ",
//m						" . locale_number_format($TenderValue,2) . ",

                                $ErrMsg = _('Cannot insert a gltranssss entry because');
                                $DbgMsg = _('The SQL that failed to insert the receipt GL entry was');
                                $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                                /* Check if the GL Account is a bank acount PDT */
                                $SQL = 'SELECT accountcode FROM bankaccounts where accountcode="' . $GlCode . '"';
                                $DbgMsg = _('The sql that was used to retrieve the information was');
                                $ErrMsg = _('Could not check whether bank Gl Account exist because');
                                $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
                                //echo "<br>CN is " . $_SESSION['CreditNumber'] . "<br>";
                                $TransNo = str_replace($_SESSION['UserStockLocation'], "", $_SESSION['CreditNumber']);
                                if (mysql_num_rows($result) != 0) { //Bank account gl found
                                    /* Get the next GL Trans Number */
                                    //$TransNo = GetNextTransNo(12, $db);
                                    $RefBankTRans = 'EFT Payment - Invoice ' . $_SESSION['InvNumber'] . ' User ' . $_SESSION['UserID'];
                                    /* Now nsert detail into banktrans for reconciliation purposes */
                                    $TransType = 'EFT';
                                    /* 						$SQL = 'INSERT INTO banktrans (type,
                                      transno,
                                      bankact,
                                      ref,
                                      transdate,
                                      banktranstype,
                                      amount,
                                      currcode)
                                      VALUES (12,
                                      ' . "'" . $TransNo . "',
                                      '" . $GlCode . "',
                                      '" . $RefBankTRans . "',
                                      '" . FormatDateForSQL($_POST['PamentDate']) . "',
                                      '" . $TransType . "',
                                      " . $TenderValue . ",
                                      '" . $_SESSION['CompanyRecord']['currencydefault'] . "'
                                      " . ')';
                                      $ErrMsg = _('Cannot insert a gltranssss entry because');
                                      $DbgMsg = _('The SQL that failed to insert the receipt GL entry was');
                                      $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                                     */
                                }
                            } //END FOREACH
                            /* Insert the credit received */

                            /* Get debtors Gl account from company preferences */
                            $SQL = "SELECT debtorsact FROM companies where coycode=1";
                            $DbgMsg = _('The sql that was used to retrieve the information was');
                            $ErrMsg = _('Could not check whether the group is recursive because');
                            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
                            $myrow = DB_fetch_array($result);
                            $GlCode = $myrow['debtorsact'];
                            DB_free_result($Result);

//$TnderAmnt = locale_number_format($_SESSION['TenderTotal'],2);
//n $TnderAmnt = round($_SESSION['TenderTotal']*100)/100 ;
                            $TnderAmnt = number_format($_SESSION['TenderTotal'], 2, '.', '');
                            /* Insert the Debtors Transaction */
                            $SQL = 'INSERT INTO gltrans (type,
			 			typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						user,
						loccode,
						' . $TenderTypeCode . ',
						tag,
						invnumber
						)
					VALUES (12,
						' . "'" . $_SESSION['CreditNumber'] . "',
						'" . FormatDateForSQL($_POST['PamentDate']) . "',
						" . $PeriodNo . ',
						' . $GlCode . ",
						'" . $TenderTypeCode . "',
						" . -$TnderAmnt . ",
						'" . $_SESSION['UserID'] . "',
						'" . $_SESSION['UserStockLocation'] . "',
						" . -$TnderAmnt . ",
						'" . $ReceiptItem->tag . "',
						'" . $_SESSION['InvNumber'] . "'
						" . ')';
                            $ErrMsg = _('Cannot insert a GL entry for the receipt because');
                            $DbgMsg = _('The SQL that failed to insert the receipt GL entry was');
                            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);



// PUT IN CJ MOD BELOW FOR PAYMENT

                            $SQL = 'INSERT INTO debtortrans (transno,
							type,
							debtorno,
							branchcode,
							trandate,
							prd,
							reference,
							tpe,
							order_,
							rate,
							ovamount,
							ovdiscount,
							invtext,
							user,
							loccode
							)
					VALUES (' . "'" . $_SESSION['CreditNumber'] . "',
						12,
'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
'" . $_SESSION['Items' . $identifier]->DebtorNo . "',
'" . $DefaultDispatchDate . "',
'" . $PeriodNo . "',
'" . $Reference . "',
						'',
						'" . $_SESSION['OrderNumber'] . "',
						1,
						" . -$TnderAmnt . ",
						" . -$ReceiptItem->Discount . ",			
'" . $InvoiceNo . "',
						'" . $_SESSION['UserID'] . "',
						'" . $_SESSION['UserStockLocation'] . "'
					)";

                            //echo "SQL query is " . $SQL . "<br>";
                            $DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
                            $ErrMsg = _('Cannot insert a receipt transaction against the customer because');
                            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                            /* Get id for debtortrans insert */
                            $SQL = "SELECT id, transno FROM debtortrans WHERE transno='$_SESSION[CreditNumber]'
			AND type=12";
                            $DbgMsg = _('The sql that was used to retrieve the information was');
                            $ErrMsg = _('Could not get id because');
                            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
                            $myrow = DB_fetch_array($result);
                            $InsertId = $myrow['id'];
                            /* Insert tender amounts */
                            foreach ($TenderTypeArray as $key => $val) {

                                $TenderTypeCode = $val['TenderTypeCode'];
//$TenderValue =locale_number_format($_POST['AmountPaid'],2) ;
//n number_format($number, 2, '.', '')
                                $TenderValue = number_format($_POST['AmountPaid'], 2, '.', '');
//$TenderValue = $val['TenderValue'];
                                $TenderSQL = "UPDATE debtortrans set $TenderTypeCode=$TenderValue
		WHERE id=$InsertId";
                                $DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
                                $ErrMsg = _('Cannot insert a receipt transaction against the customer because');
                                $result = DB_query($TenderSQL, $db, $ErrMsg, $DbgMsg, true);
                            }

                            /* End of credit received */

                            /* Now do the Allocation */
                            /* Update the Invoice Number Alloc field Debit to Gl Code 3005 */
                            /* First debit to invoice number */
//n number_format($_SESSION[TenderTotal],2, '.', '') ;
                            $SetlAmnt = number_format($_SESSION[TenderTotal], 2, '.', '');
                            $SQL = "UPDATE debtortrans SET alloc = $SetlAmnt, settled=1
									WHERE debtortrans.transno='$_SESSION[InvNumber]' and type=10 and loccode='$_SESSION[UserStockLocation]'";
                            //echo "sql debtortrans" . $SQL . "<br>";
                            $DbgMsg = _('The SQL that failed to update the date of the last payment received was');
                            $ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
                            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                            /* Now credit to credit number */

                            $SQL = "UPDATE debtortrans SET alloc = -$SetlAmnt, settled=1
									WHERE debtortrans.transno='$_SESSION[CreditNumber]' and type=12 and loccode='$_SESSION[UserStockLocation]'";
                            //echo "sql for credit line is " . $SQL . "<br>";
                            $DbgMsg = _('The SQL that failed to update the date of the last payment received was');
                            $ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
                            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                            DB_free_result($Result);
                            /* Get data for the two rows, need the row id's */
                            /* From Credit Row */
                            $sql = "SELECT id
			FROM debtortrans
			WHERE debtortrans.transno='$_SESSION[CreditNumber]'
			AND debtortrans.type='12' and loccode='$_SESSION[UserStockLocation]'";
                            //echo "Credit sql is " . $sql . "<br>";
                            $DbgMsg = _('The sql that was used to retrieve the information was');
                            $ErrMsg = _('Could not check whether the group is recursive because');
                            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                            $CreditRow = DB_fetch_array($result);
                            DB_free_result($Result);
                            /* From Invoice Row */
                            $sql = "SELECT id
			FROM debtortrans
			WHERE debtortrans.transno='$_SESSION[CurrentInvoice]' and loccode='$_SESSION[UserStockLocation]'";
                            //echo "Invoice sql is " . $sql . "<br>";
                            $DbgMsg = _('The sql that was used to retrieve the information was');
                            $ErrMsg = _('Could not check whether the group is recursive because');
                            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                            $InvoiceRow = DB_fetch_array($result);
                            DB_free_result($Result);
                            /* Write transaction to custallocns table */
                            $SQL = "INSERT INTO	custallocns (
							datealloc,
							amt,
							transid_allocfrom,
							transid_allocto,
							loccode
							) VALUES ('" . date('Y-m-d') . "',
							'" . $TnderAmnt . "',
							'" . $CreditRow['id'] . "',
							'" . $InvoiceRow['id'] . "',
							'" . $_SESSION['UserStockLocation'] . "')";
//							'" . locale_number_format($_SESSION['TenderTotal'],2) . "',
                            if (!$Result = DB_query($SQL, $db)) {
                                $error = 'Could not insert invoice record';
                            }


//	unset($_SESSION['ButtonClicked']);
                            unset($_SESSION['CreditNumber']);
                            unset($_SESSION['InvNumber']);
                            unset($_SESSION['TenderTotal']);
                            unset($_SESSION['OrderNumber']);


                            //exit;
                            //END IF
                            //END COMMITBATCH
//-----------------------FIN DEUXIEME PARTI ----------------------------------------
//--------------------------------------------------------------------------------------	
                        }// END BIG $_POST[AmountPaid]
//-------------------------------------------------------------------------
//-                DIDERSON END RECIPT ----------------------------------------------
//-----------------------------------------------------------------------------------------------
//-------START TO BUILD RECEIPT --------------------------------------------------------------
//-------------RECEIPT -------------------------------------------------------------
                        if ($_POST['AmountPaidsss'] != 0) {
                            //$ReceiptNumber = GetNextTransNo(12, $db);
                             $TypeNoField = "typeno".$_SESSION['UserStockLocation'];
                            $ReceiptNumber = $_SESSION['UserStockLocation'] .GetNextTransNoTenderType(12, $db, $TypeNoField);
                            $SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
						VALUES (12,
							'" . $ReceiptNumber . "',
							'" . $DefaultDispatchDate . "',
							'" . $PeriodNo . "',
							'" . $_POST['BankAccount'] . "',
							'" . $_SESSION['Items' . $identifier]->LocationName . ' ' . _('Counter Sale') . ' ' . $InvoiceNo . "',
							'" . (filter_number_format($_POST['AmountPaid']) / $ExRate) . "')";
                            $DbgMsg = _('The SQL that failed to insert the GL transaction for the bank account debit was');
                            $ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
                            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                            /* Now Credit Debtors account with receipt */
                            $SQL = "INSERT INTO gltrans ( type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount)
				VALUES (12,
					'" . $ReceiptNumber . "',
					'" . $DefaultDispatchDate . "',
					'" . $PeriodNo . "',
					'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
					'" . $_SESSION['Items' . $identifier]->LocationName . ' ' . _('Counter Sale') . ' ' . $InvoiceNo . "',
					'" . -(filter_number_format($_POST['AmountPaid']) / $ExRate) . "')";
//					'" . -locale_number_format($_POST['AmountPaid']/$ExRate,2) . "')";
                            $DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
                            $ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
                            $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                        }//amount paid we not zero

                        EnsureGLEntriesBalance(12, $ReceiptNumber, $db);
                    } /* end of if Sales and GL integrated */
                    if ($_POST['AmountPaid'] != 0) {
                        if (!isset($ReceiptNumber)) {
                           // $ReceiptNumber = GetNextTransNo(12, $db);
                            $TypeNoField = "typeno".$_SESSION['UserStockLocation'];
                            $ReceiptNumber = $_SESSION['UserStockLocation'] .GetNextTransNoTenderType(12, $db, $TypeNoField);
                        }
                        //Now need to add the receipt banktrans record
                        //First get the account currency that it has been banked into
                        $result = DB_query("SELECT rate FROM currencies
								INNER JOIN bankaccounts 
								ON currencies.currabrev=bankaccounts.currcode
								WHERE bankaccounts.accountcode='" . $_POST['BankAccount'] . "'", $db);
                        $myrow = DB_fetch_row($result);
                        $BankAccountExRate = $myrow[0];

                        /*
                         * Some interesting exchange rate conversion going on here
                         * Say :
                         * The business's functional currency is NZD
                         * Customer location counter sales are in AUD - 1 NZD = 0.80 AUD
                         * Banking money into a USD account - 1 NZD = 0.68 USD
                         *
                         * Customer sale is for $100 AUD
                         * GL entries  conver the AUD 100 to NZD  - 100 AUD / 0.80 = $125 NZD
                         * Banktrans entries convert the AUD 100 to USD using 100/0.8 * 0.68
                         */

                        //insert the banktrans record in the currency of the bank account

                        /* 		Modified of Duroplastic - using Control accounts instead
                          $SQL="INSERT INTO banktrans (type,
                          transno,
                          bankact,
                          ref,
                          exrate,
                          functionalexrate,
                          transdate,
                          banktranstype,
                          amount,
                          currcode)
                          VALUES (12,
                          '" . $ReceiptNumber . "',
                          '" . $_POST['BankAccount'] . "',
                          '" . $_SESSION['Items'.$identifier]->LocationName . ' ' . _('Counter Sale') . ' ' . $InvoiceNo . "',
                          '" . $ExRate . "',
                          '" . $BankAccountExRate . "',
                          '" . $DefaultDispatchDate . "',
                          '" . $_POST['PaymentMethod'] . "',
                          '" . filter_number_format($_POST['AmountPaid']) * $BankAccountExRate . "',
                          '" . $_SESSION['Items'.$identifier]->DefaultCurrency . "')";

                          $DbgMsg = _('The SQL that failed to insert the bank account transaction was');
                          $ErrMsg = _('Cannot insert a bank transaction');
                          $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                          //insert a new debtortrans for the receipt

                          $SQL = "INSERT INTO debtortrans (transno,
                          type,
                          debtorno,
                          trandate,
                          inputdate,
                          prd,
                          reference,
                          rate,
                          ovamount,
                          alloc,
                          user,
                          loccode,
                          invtext)
                          VALUES ('" . $ReceiptNumber . "',
                          12,
                          '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
                          '" . $DefaultDispatchDate . "',
                          '" . date('Y-m-d H-i-s') . "',
                          '" . $PeriodNo . "',
                          '" . $InvoiceNo . "',
                          '" . $ExRate . "',
                          '" . -filter_number_format($_POST['AmountPaid']) . "',
                          '" . -filter_number_format($_POST['AmountPaid']) . "',
                          '" . $_SESSION['UserID'] . "',
                          '" . $_SESSION['UserStockLocation'] . "',
                          '" . $_SESSION['Items'.$identifier]->LocationName . ' ' . _('Counter Sale') ."')";

                          $DbgMsg = _('The SQL that failed to insert the customer receipt transaction was');
                          $ErrMsg = _('Cannot insert a receipt transaction against the customer because') ;
                          $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                          CJ REMOVE 2ND DRTRANS ENTRY */

                        $ReceiptDebtorTransID = DB_Last_Insert_ID($db, 'debtortrans', 'id');

                        $SQL = "UPDATE debtorsmaster SET lastpaiddate = '" . $DefaultDispatchDate . "',
											lastpaid='" . number_format($_POST['AmountPaid'], 2, '.', '') . "'
									WHERE debtorsmaster.debtorno='" . $_SESSION['Items' . $identifier]->DebtorNo . "'";

                        $DbgMsg = _('The SQL that failed to update the date of the last payment received was');
                        $ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
                        $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                        //and finally add the allocation record between receipt and invoice

                        $SQL = "INSERT INTO custallocns (	amt,
												datealloc,
												transid_allocfrom,
												transid_allocto )
									VALUES  ('" . number_format($_POST['AmountPaid'], 2, '.', '') . "',
									'" . $DefaultDispatchDate . "',
											 '" . $ReceiptDebtorTransID . "',
											 '" . $DebtorTransID . "')";
//m									VALUES  ('" . locale_number_format($_POST['AmountPaid'],2) . "',
                        $DbgMsg = _('The SQL that failed to insert the allocation of the receipt to the invoice was');
                        $ErrMsg = _('Cannot insert the customer allocation of the receipt to the invoice because');
                        $result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                    } //end if 1 $_POST['AmountPaid']!= 0

                    DB_Txn_Commit($db);
                    // *************************************************************************
                    //   E N D   O F   I N V O I C E   S Q L   P R O C E S S I N G
                    // *************************************************************************

                   

                    echo'<br>';
                    echo prnMsg(_('Invoice number') . ' ' . $InvoiceNo . ' ' . _('processed'), 'success');

                    echo '<br />';
                    if (isset($Change)) {//verification change existe
                        echo'<div style="border:1px #006600 solid; font: Arial, font-size:10px , Helvetica, sans-serif; margin:auto; text-align:center; padding-top:15px; padding-bottom:10px;">';
                        echo "<font color=blue>" . "$Change</font>";
                        echo "<BR><font size='1'>Change to Client </font>";
                        echo "<font size='5' color=red>" . "$Changea</font>";
                        echo'</div>';
                    }//end verification if change existe
                    echo'<div class="centre">';
                    /************************/
                     if ($_SESSION['Items' . $identifier]->CustCodeNum != "") {
            /*This prints out for loyal Customers, put fields in the right columns...*/
                        echo "Test---Test!!! <br />";
                        if ($_SESSION['InvoicePortraitFormat'] == 0) {
                            echo '<img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . '<a target="_blank" href="' . $rootpath . '/PrintLoyalPOSTrans.php?FromTransNo=' . $InvoiceNo . '&Code='.$_SESSION['Items' . $identifier]->CustCodeNum. '&InvOrCredit=Invoice&PrintPDF=True">' . _('Print this invoice(Loyal Customers)') . ' (' . _('Landscape') . ')</a><br /><br />';
                            echo '<img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . '<a target="_blank" href="' . $rootpath . '/PrintCustDelivery_generic.php?' . SID . '&TransNo=' . $OrderNo . '">' . _('Print Delivery Note');
                        } else {
                            echo '<img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . '<a target="_blank" href="' . $rootpath . '/PrintLoyalCustTransPortrait.php?FromTransNo=' . $InvoiceNo . '&Code='.$_SESSION['Items' . $identifier]->CustCodeNum.'&InvOrCredit=Invoice&PrintPDF=True">' . _('Print this invoice') . ' (' . _('Portrait') . ')</a><br /><br />';
                        }
                      }
                    if ($_SESSION['InvoicePortraitFormat'] == 0) {
                        echo '<img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . '<a target="_blank" href="' . $rootpath . '/PrintPOSTrans.php?FromTransNo=' . $InvoiceNo . '&InvOrCredit=Invoice&PrintPDF=True">' . _('Print this invoice') . ' (' . _('Landscape') . ')</a><br /><br />';
                        echo '<img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . '<a target="_blank" href="' . $rootpath . '/PrintCustDelivery_generic.php?' . SID . '&TransNo=' . $OrderNo . '">' . _('Print Delivery Note');
                    } else {
                        echo '<img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . '<a target="_blank" href="' . $rootpath . '/PrintCustTransPortrait.php?FromTransNo=' . $InvoiceNo . '&InvOrCredit=Invoice&PrintPDF=True">' . _('Print this invoice') . ' (' . _('Portrait') . ')</a><br /><br />';
                    }
                    echo '<br /><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">' . _('Start a new Counter Sale') . '</a></div>';
                    
                    unset($_SESSION['Items' . $identifier]->LineItems);
                    unset($_SESSION['Items' . $identifier]);
                }
                // There were input errors so don't process nuffin
            } else {
                //pretend the user never tried to commit the sale
                unset($_POST['ProcessSale']);
            }
            /*             * *****************************
             * end of Invoice Processing
             * *****************************
             */

            /* Now show the stock item selection search stuff below */
            if (!isset($_POST['ProcessSale'])) {
                //if (isset($_POST['PartSearch']) and $_POST['PartSearch']!=''){

                echo '<input type="hidden" name="PartSearch" value="' . _('Yes Please') . '" />';

                if ($_SESSION['FrequentlyOrderedItems'] > 0) { //show the Frequently Order Items selection where configured to do so
                    // Select the most recently ordered items for quick select
                    $SixMonthsAgo = DateAdd(Date($_SESSION['DefaultDateFormat']), 'm', -6);

                    $SQL = "SELECT stockmaster.units,
						stockmaster.description,
						stockmaster.stockid,
						salesorderdetails.stkcode,
						SUM(qtyinvoiced) Sales
				  FROM salesorderdetails INNER JOIN stockmaster
				  ON salesorderdetails.stkcode = stockmaster.stockid
				  WHERE ActualDispatchDate >= '" . FormatDateForSQL($SixMonthsAgo) . "'
				  AND stockmaster.controlled=0
				  GROUP BY stkcode
				  ORDER BY sales DESC
				  LIMIT " . $_SESSION['FrequentlyOrderedItems'];
                    $result2 = DB_query($SQL, $db);
                    echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ';
                    echo _('Frequently Ordered Items') . '</p><br />';
                    echo '<div class="page_help_text">' . _('Frequently Ordered Items') . _(', shows the most frequently ordered items in the last 6 months.  You can choose from this list, or search further for other items') . '.</div><br />';
                    echo '<table class="table1">';
                    $TableHeader = '<tr><th>' . _('Code') . '</th>
								<th>' . _('Description') . '</th>
								<th>' . _('Units') . '</th>
								<th>' . _('On Hand') . '</th>
								<th>' . _('On Demand') . '</th>
								<th>' . _('On Order') . '</th>
								<th>' . _('Available') . '</th>
								<th>' . _('Quantity') . '</th></tr>';
                    echo $TableHeader;
                    $i = 0;
                    $j = 1;
                    $k = 0; //row colour counter

                    while ($myrow = DB_fetch_array($result2)) {
                        // This code needs sorting out, but until then :
                        $ImageSource = _('No Image');
                        // Find the quantity in stock at location
                        $QohSql = "SELECT sum(quantity)
									   FROM locstock
									   WHERE stockid='" . $myrow['stockid'] . "' AND
									   loccode = '" . $_SESSION['Items' . $identifier]->Location . "'";
                        $QohResult = DB_query($QohSql, $db);
                        $QohRow = DB_fetch_row($QohResult);
                        $QOH = $QohRow[0];

                        // Find the quantity on outstanding sales orders
                        $sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
									FROM salesorderdetails,
										 salesorders
									WHERE salesorders.orderno = salesorderdetails.orderno AND
										 salesorders.fromstkloc='" . $_SESSION['Items' . $identifier]->Location . "' AND
										 salesorderdetails.completed=0 AND
										 salesorders.quotation=0 AND
										 salesorderdetails.stkcode='" . $myrow['stockid'] . "'";

                        $ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Items' . $identifier]->Location . ' ' .
                                _('cannot be retrieved because');
                        $DemandResult = DB_query($sql, $db, $ErrMsg);

                        $DemandRow = DB_fetch_row($DemandResult);
                        if ($DemandRow[0] != null) {
                            $DemandQty = $DemandRow[0];
                        } else {
                            $DemandQty = 0;
                        }
                        // Find the quantity on purchase orders
                        $sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS QOO
								FROM purchorderdetails INNER JOIN purchorders
								WHERE purchorderdetails.completed=0
								AND purchorders.status<>'Cancelled'
								AND purchorders.status<>'Rejected'
								AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

                        $ErrMsg = _('The order details for this product cannot be retrieved because');
                        $PurchResult = db_query($sql, $db, $ErrMsg);

                        $PurchRow = DB_fetch_row($PurchResult);
                        if ($PurchRow[0] != null) {
                            $PurchQty = $PurchRow[0];
                        } else {
                            $PurchQty = 0;
                        }

                        // Find the quantity on works orders
                        $sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
							   FROM woitems
							   WHERE stockid='" . $myrow['stockid'] . "'";
                        $ErrMsg = _('The order details for this product cannot be retrieved because');
                        $WoResult = db_query($sql, $db, $ErrMsg);
                        $WoRow = db_fetch_row($WoResult);
                        if ($WoRow[0] != null) {
                            $WoQty = $WoRow[0];
                        } else {
                            $WoQty = 0;
                        }

                        if ($k == 1) {
                            echo '<tr class="EvenTableRows">';
                            $k = 0;
                        } else {
                            echo '<tr class="OddTableRows">';
                            $k = 1;
                        }
                        $OnOrder = $PurchQty + $WoQty;

                        $Available = $QOH - $DemandQty + $OnOrder;

                        printf('<td>%s</font></td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td><input class="number" tabindex="' . strval($j + 7) . '" type="text" size="6" name="OrderQty%s" value="0" />
							<input type="hidden" name="StockID%s" value="%s" />
						</td>
						</tr>', $myrow['stockid'], $myrow['description'], $myrow['units'], $QOH, $DemandQty, $OnOrder, $Available, $i, $i, $myrow['stockid']);
                        if ($j == 1) {
                            $jsCall = '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.OrderQty' . $i . ');}</script>';
                        }
                        $j++; //counter for paging
                        $i++; //index for controls
                        #end of page full new headings if
                    }
                    #end of while loop for Frequently Ordered Items
                    echo '<td style="text-align:center" colspan="8"><input type="hidden" name="SelectingOrderItems" value="1" /><input tabindex=' . strval($j + 8) . ' type="submit" value="' . _('Add to Sale') . '" /></td>';
                    echo '</table>';
                } //end of if Frequently Ordered Items > 0
                //} /*end of PartSearch options to be displayed */
                /* else { /* show the quick entry form variable 

                  echo '<div class="page_help_text" style="text-align:center;"><b>' . _('Use this form to add items quickly if the item codes are already known') . '</b></div><br />
                  <table border="1">
                  <tr>';
                  /*do not display colum unless customer requires po line number by sales order line
                  echo '<th>' . _('Item Code') . '</th>
                  <th>' . _('Quantity') . '</th>
                  </tr>';
                  $DefaultDeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',$_SESSION['Items'.$identifier]->DeliveryDays);
                  if (count($_SESSION['Items'.$identifier]->LineItems)==0) {
                  echo '<input type="hidden" name="CustRef" value="'.$_SESSION['Items'.$identifier]->CustRef.'" />';
                  echo '<input type="hidden" name="Comments" value="'.$_SESSION['Items'.$identifier]->Comments.'" />';
                  echo '<input type="hidden" name="DeliverTo" value="'.$_SESSION['Items'.$identifier]->DeliverTo.'" />';
                  echo '<input type="hidden" name="PhoneNo" value="'.$_SESSION['Items'.$identifier]->PhoneNo.'" />';
                  echo '<input type="hidden" name="Email" value="'.$_SESSION['Items'.$identifier]->Email.'" />';
                  }

                  //	 	echo '</table><br /><div class="centre"><input type="submit" name="QuickEntry" value="' . _('Quick Entry') . '" />
                  //		<input type="submit" name="PartSearch" value="' . _('Search Parts') . '" /></div>';

                  for ($i=1;$i<=$_SESSION['QuickEntries'];$i++){

                  echo '<tr class="OddTableRow">';
                  /* Do not display colum unless customer requires po line number by sales order line
                  ?>
                  <td><input type="text" name="<?php echo "part_$i" ;?>" size="21" maxlength="20" onkeyup="ajax_showOptions(this,'getItem',event)" /></td>
                  <?php
                  echo '<td><input type="text" class="number" name="qty_' . $i . '" size="6" maxlength="6" /></td>
                  <input type="hidden" class="date" name="ItemDue_' . $i . '" value="' . $DefaultDeliveryDate . '" /></tr>';
                  }
                  echo '<script  type="text/javascript">if (document.SelectParts) {defaultControl(document.SelectParts.part_1);}</script>';

                  echo '</table><br /><div class="centre"><input type="submit" name="QuickEntry" value="' . _('Quick Entry') . '" />
                  <input type="submit" name="PartSearch" value="' . _('Search Parts') . '" /></div>';

                  } */ // ending else
                if ($_SESSION['Items' . $identifier]->ItemsOrdered >= 1) {
                    echo '<br /><div class="centre"><input type="submit" name="CancelOrder" value="' . _('Cancel Sale') . '" onclick="return confirm(\'' . _('Are you sure you wish to cancel this sale?') . '\');" /></div>';
                }
            }

            echo '</form>';

            include('includes/footer.inc');
            ?>