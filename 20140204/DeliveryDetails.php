<?php
/* $Revision: 1.71 $ */
/* Tamelo Douglas...
  /*
  This is where the delivery details are confirmed/entered/modified and the order committed to the database once the place order/modify order button is hit.
 */

include('includes/DefineCartClass.php');

/* Session started in header.inc for password checking the session will contain the details of the order from the Cart class object. The details of the order come from SelectOrderItems.php 			 */

// $PageSecurity=1;
include('includes/session.inc');
$title = _('Order Delivery Details');
include('includes/header.inc');
include('includes/FreightCalculation.inc');
include('includes/SQL_CommonFunctions.inc');
//print_r($_SESSION['Packsize']);
if (isset($_GET['identifier'])) {
    $identifier = $_GET['identifier'];
}
//print_r($_SESSION['Items' . $identifier]->LineItems);
// This is not required on this page
//echo '<a href="'. $rootpath . '/SelectSalesOrder.php?' . SID . '">'. _('Back to Sales Orders'). '</a><br>';
unset($_SESSION['WarnOnce']);
if (!isset($_SESSION['Items' . $identifier]) OR !isset($_SESSION['Items' . $identifier]->DebtorNo)) {
    prnMsg(_('This page can only be read if an order has been entered') . '. ' . _('To enter an order select customer transactions then sales order entry'), 'error');
    include('includes/footer.inc');
    exit;
}

if ($_SESSION['Items' . $identifier]->ItemsOrdered == 0) {
    prnMsg(_('This page can only be read if an there are items on the order') . '. ' . _('To enter an order select customer transactions, then sales order entry'), 'error');
    include('includes/footer.inc');
    exit;
}
echo $_SESSION['Items' . $identifier]->CashPaymentTerms . "Session name";
/* Calculate the earliest dispacth date in DateFunctions.inc */

$EarliestDispatch = CalcEarliestDispatchDate();

if (isset($_POST['ProcessOrder']) OR isset($_POST['MakeRecurringOrder'])) {

    /* need to check for input errors in any case before order processed */
    $_POST['Update'] = 'Yes rerun the validation checks';

    /* store the old freight cost before it is recalculated to ensure that there has been no change - test for change after freight recalculated and get user to re-confirm if changed */

    $OldFreightCost = round($_POST['FreightCost'], 2);
}
?>
<script>
    $(document).ready(function() {
<?php
if (isset($_SESSION['ExistingOrder'])) {

    $SQL = "SELECT ordernumofdays 
                            FROM salesorders WHERE 
                  orderno='" . $_SESSION['ExistingOrder'] . "'";

    $result = DB_query($SQL, $db);
    $myrow = DB_fetch_array($result);
    if ($myrow['ordernumofdays'] > 0 && $_SESSION['Items' . $identifier]->inputedate == $myrow['ordernumofdays']) {
        ?>
                         document.DeliveryForm.inputedate.value = <? echo $myrow['ordernumofdays']; ?>;
    <? } elseif ($_SESSION['Items' . $identifier]->inputedate > 0) { ?>
                        document.DeliveryForm.inputedate.value = <? echo $_SESSION['Items' . $identifier]->inputedate; ?>;
    <? } else { ?>
                        document.DeliveryForm.inputedate.value = 7;
    <? }
} ?>
                document.DeliveryForm.DeliveryDate.readOnly = true;
                document.DeliveryForm.DeliveryDate.disabled = true;
            });

            var currentValue = 0;
            function handleClick(dateopt) {
  
                currentValue = dateopt.value;
    
   
                if(currentValue == "txtdate"){
       
                    document.DeliveryForm.DeliveryDate.readOnly = true;
                    document.DeliveryForm.DeliveryDate.disabled = true;
                    document.DeliveryForm.inputedate.disabled = false;
                    document.DeliveryForm.inputedate.readOnly = false;
       
<? if ($_SESSION['Items' . $identifier]->inputedate > 0) { ?>
                         document.DeliveryForm.inputedate.value = <? echo $_SESSION['Items' . $identifier]->inputedate; ?>;
<? } else { ?>
                        document.DeliveryForm.inputedate.value = 7;
               
<? } ?>

                }else{
        
                    document.DeliveryForm.DeliveryDate.readOnly = false;
                    document.DeliveryForm.DeliveryDate.disabled = false;
        
                    document.DeliveryForm.inputedate.readOnly = true;
                    document.DeliveryForm.inputedate.value = 0;
                    document.DeliveryForm.inputedate.disabled = true;
                }

            }
</script>

<?php
if (isset($_POST['Update'])
        or isset($_POST['BackToLineDetails'])
        or isset($_POST['MakeRecurringOrder'])) {
    $InputErrors = 0;
    if ($_POST['CustOrderNum'] == "" || strlen($_POST['CustOrderNum']) <= 1 || $_SESSION['Items' . $identifier]->CustRef == " ") {
        echo '<link href="' . $rootpath . '/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
        echo '<script src="' . $rootpath . '/facebox/src/facebox.js" type="text/javascript"></script>';
        echo "<script>jQuery.facebox('PLEASE..Insert customer order number.');</script>";
        // exit();
        $InputErrors = 1;
    }
//    if (!isset($_POST['DeliveryDate']) && $_POST['dateopt'] == "seldate") {
//
//        echo '<link href="' . $rootpath . '/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
//        echo '<script src="' . $rootpath . '/facebox/src/facebox.js" type="text/javascript"></script>';
//        echo "<script>jQuery.facebox('Please select estimated delivery date. A must.');</script>";
//        // exit();
//        $InputErrors = 1;
//    }
    if (!isset($_POST['ConfirmedDate'])) {
        echo '<link href="' . $rootpath . '/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
        echo '<script src="' . $rootpath . '/facebox/src/facebox.js" type="text/javascript"></script>';
        echo "<script>jQuery.facebox('Please select confirm delivery date. A must.');</script>";
        // exit();
        $InputErrors = 1;
    }


    if ($_POST['Codes'] == "" || $_POST['Codes'] == "none" || strlen($_POST['Codes']) <= 1 || $_SESSION['Items' . $identifier]->CustRefVia == " ") {
        echo '<link href="' . $rootpath . '/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
        echo '<script src="' . $rootpath . '/facebox/src/facebox.js" type="text/javascript"></script>';
        echo "<script>jQuery.facebox('How did the customer hear about us? Please correct.');</script>";
        // exit();
        $InputErrors = 1;
    }
    if (strlen($_POST['DeliverTo']) <= 1) {
        $InputErrors = 1;
        prnMsg(_('You must enter the person or company to whom delivery should be made'), 'error');
    }
    if (strlen($_POST['BrAdd1']) <= 1) {
        $InputErrors = 1;
        prnMsg(_('You should enter the street address in the box provided') . '. ' . _('Orders cannot be accepted without a valid street address'), 'error');
    }
//	if (strpos($_POST['BrAdd1'],_('Box'))>0){
//		prnMsg(_('You have entered the word') . ' "' . _('Box') . '" ' . _('in the street address') . '. ' . _('Items cannot be delivered to') . ' ' ._('box') . ' ' . _('addresses'),'warn');
//	}
    if (!is_numeric($_POST['FreightCost'])) {
        $InputErrors = 1;
        prnMsg(_('The freight cost entered is expected to be numeric'), 'error');
    }
    if (isset($_POST['MakeRecurringOrder']) AND $_POST['Quotation'] == 1) {
        $InputErrors = 1;
        prnMsg(_('A recurring order cannot be made from a quotation'), 'error');
    }
    if (($_POST['DeliverBlind']) <= 0) {
        $InputErrors = 1;
        prnMsg(_('You must select the type of packlist to print'), 'error');
    }

    /* 	If (strlen($_POST['BrAdd3'])==0 OR !isset($_POST['BrAdd3'])){
      $InputErrors =1;
      echo "<br>A region or city must be entered.<br>";
      }

      Maybe appropriate in some installations but not here
      If (strlen($_POST['BrAdd2'])<=1){
      $InputErrors =1;
      echo "<br>You should enter the suburb in the box provided. Orders cannot be accepted without a valid suburb being entered.<br>";
      }

     */
// Check the date is OK
    if (isset($_POST['DeliveryDate']) and !Is_Date($_POST['DeliveryDate'])) {
        $InputErrors = 1;
        prnMsg(_('An invalid date entry was made') . '. ' . _('The date entry must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'warn');
    }
// Check the date is OK
    if (isset($_POST['QuoteDate']) and !Is_Date($_POST['QuoteDate'])) {
        $InputErrors = 1;
        prnMsg(_('An invalid date entry was made') . '. ' . _('The date entry must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'warn');
    }
// Check the date is OK
    if (isset($_POST['ConfirmedDate']) and !Is_Date($_POST['ConfirmedDate'])) {
        $InputErrors = 1;
        prnMsg(_('An invalid date entry was made') . '. ' . _('The date entry must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'warn');
    }

    /* This check is not appropriate where orders need to be entered in retrospectively in some cases this check will be appropriate and this should be uncommented

      elseif (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat'],$EarliestDispatch), $_POST['DeliveryDate'])){
      $InputErrors =1;
      echo '<br><b>' . _('The delivery details cannot be updated because you are attempting to set the date the order is to be dispatched earlier than is possible. No dispatches are made on Saturday and Sunday. Also, the dispatch cut off time is') .  $_SESSION['DispatchCutOffTime']  . _(':00 hrs. Orders placed after this time will be dispatched the following working day.');
      }

     */

    if ($InputErrors == 0) {

        if ($_SESSION['DoFreightCalc'] == True) {
            list ($_POST['FreightCost'], $BestShipper) = CalcFreightCost($_SESSION['Items' . $identifier]->total, $_POST['BrAdd2'], $_POST['BrAdd3'], $_SESSION['Items' . $identifier]->totalVolume, $_SESSION['Items' . $identifier]->totalWeight, $_SESSION['Items' . $identifier]->Location, $db);
            if (!empty($BestShipper)) {
                $_POST['FreightCost'] = round($_POST['FreightCost'], 2);
                $_POST['ShipVia'] = $BestShipper;
            } else {
                prnMsg(_($_POST['FreightCost']), 'warn');
            }
        }
        $sql = 'SELECT custbranch.brname,
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
                                debtorsmaster.paymentterms
			FROM custbranch,debtorsmaster
			WHERE debtorsmaster.debtorno = custbranch.debtorno
                        AND custbranch.branchcode=' . "'" . $_SESSION['Items' . $identifier]->Branch . "'" .
                ' AND custbranch.debtorno = ' . "'" . $_SESSION['Items' . $identifier]->DebtorNo . "'";

        $ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_SESSION['Items' . $identifier]->CustomerName . ' ' . _('cannot be retrieved because');
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
        if (!isset($_POST['SpecialInstructions'])) {
            $_POST['SpecialInstructions'] = '';
        }
        if (!isset($_POST['DeliveryDays'])) {
            $_POST['DeliveryDays'] = 0;
        }
        if (!isset($_SESSION['Items' . $identifier])) {
            echo "From the database:";
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
            $_SESSION['Items' . $identifier]->ShipVia = $myrow[10];
            $_SESSION['Items' . $identifier]->DeliverBlind = $myrow[11];
            $_SESSION['Items' . $identifier]->SpecialInstructions = $myrow[12];
            $_SESSION['Items' . $identifier]->DeliveryDays = $myrow[13];
            $_SESSION['Items' . $identifier]->DeliveryDate = $_POST['DeliveryDate'];
            $_SESSION['Items' . $identifier]->QuoteDate = $_POST['QuoteDate'];
            $_SESSION['Items' . $identifier]->ConfirmedDate = $_POST['ConfirmedDate'];
            $_SESSION['Items' . $identifier]->CustRef = $_POST['CustOrderNum'];
            //$_SESSION['Items'.$identifier]->custrefAdon
            $_SESSION['Items' . $identifier]->Comments = $_POST['CommentsText'];
            $_SESSION['Items' . $identifier]->FreightCost = round($_POST['FreightCost'], 2);
            $_SESSION['Items' . $identifier]->Quotation = $_POST['Quotation'];
            $_SESSION['Items' . $identifier]->CustRefVia = $_POST['Codes'];
            //$_SESSION['Items' .$identifier]->CashPaymentTerms  = $myrow[14];
            $OrderExist = 1;
        } /* else if($_SESSION['ExistingOrder']!= "0")
          {


          } */ elseif ($_SESSION['ExistingOrder'] == "0") {
            echo "else//////";
            $_SESSION['Items' . $identifier]->DeliverTo = $_POST['DeliverTo'];
            $_SESSION['Items' . $identifier]->DelAdd1 = $_POST['BrAdd1'];
            $_SESSION['Items' . $identifier]->DelAdd2 = $_POST['BrAdd2'];
            $_SESSION['Items' . $identifier]->DelAdd3 = $_POST['BrAdd3'];
            $_SESSION['Items' . $identifier]->DelAdd4 = $_POST['BrAdd4'];
            $_SESSION['Items' . $identifier]->DelAdd5 = $_POST['BrAdd5'];
            $_SESSION['Items' . $identifier]->DelAdd6 = $_POST['BrAdd6'];
            $_SESSION['Items' . $identifier]->PhoneNo = $_POST['PhoneNo'];
            $_SESSION['Items' . $identifier]->Email = $_POST['Email'];
            $_SESSION['Items' . $identifier]->Location = $_POST['Location'];
            $_SESSION['Items' . $identifier]->ShipVia = $_POST['ShipVia'];
            $_SESSION['Items' . $identifier]->DeliverBlind = $_POST['DeliverBlind'];
            $_SESSION['Items' . $identifier]->SpecialInstructions = $_POST['SpecialInstructions'];
            $_SESSION['Items' . $identifier]->DeliveryDays = $_POST['DeliveryDays'];
            $_SESSION['Items' . $identifier]->DeliveryDate = $_POST['DeliveryDate'];
            $_SESSION['Items' . $identifier]->QuoteDate = $_POST['QuoteDate'];
            $_SESSION['Items' . $identifier]->inputedate = $_POST['inputedate'];
            $_SESSION['Items' . $identifier]->ConfirmedDate = $_POST['ConfirmedDate'];
            $_SESSION['Items' . $identifier]->CustRef = $_POST['CustOrderNum'];
            $_SESSION['Items' . $identifier]->Comments = $_POST['CommentsText'];
            $_SESSION['Items' . $identifier]->FreightCost = round($_POST['FreightCost'], 2);
            $_SESSION['Items' . $identifier]->Quotation = $_POST['Quotation'];
            $_SESSION['Items' . $identifier]->CustRefVia = $_POST['Codes'];
            $OrderExist = 0;
        }
        if (isset($_POST['inputedate']) || $_POST['inputedate'] != '') {
            $_SESSION['Items' . $identifier]->inputedate = $_POST['inputedate'];
        }
        if (isset($_POST['Codes'])) {
            $_SESSION['Items' . $identifier]->CustRefVia = $_POST['Codes'];
        }
        if (isset($_POST['DeliverTo'])) {
            $_SESSION['Items' . $identifier]->DeliverTo = $_POST['DeliverTo'];
        }
        if (isset($_POST['BrAdd1'])) {
            $_SESSION['Items' . $identifier]->DelAdd1 = $_POST['BrAdd1'];
        }
        if (isset($_POST['BrAdd2'])) {
            $_SESSION['Items' . $identifier]->DelAdd2 = $_POST['BrAdd2'];
        }
        if (isset($_POST['BrAdd3'])) {
            $_SESSION['Items' . $identifier]->DelAdd3 = $_POST['BrAdd3'];
        }
        if (isset($_POST['BrAdd4'])) {
            $_SESSION['Items' . $identifier]->DelAdd4 = $_POST['BrAdd4'];
        }
        if (isset($_POST['BrAdd5'])) {
            $_SESSION['Items' . $identifier]->DelAdd5 = $_POST['BrAdd5'];
        }
        if (isset($_POST['BrAdd6'])) {
            $_SESSION['Items' . $identifier]->DelAdd6 = $_POST['BrAdd6'];
        }
        if (isset($_POST['PhoneNo'])) {
            $_SESSION['Items' . $identifier]->PhoneNo = $_POST['PhoneNo'];
        }

        if ($_POST['DeliveryDate']=="" || !isset($_POST['DeliveryDate']) || $_SESSION['Items' . $identifier]->DeliveryDate == "")  {
            
             echo '<link href="'.$rootpath.'/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
             echo '<script src="'.$rootpath.'/facebox/src/facebox.js" type="text/javascript"></script>';
             echo "<script>jQuery.facebox('Please select estimated delivery date. A must, same as in the purchase order.');</script>"; 
            // exit();
             $InputErrors =1;
        }
        /* $_SESSION['DoFreightCalc'] is a setting in the config.php file that the user can set to false to turn off freight calculations if necessary */


        /* What to do if the shipper is not calculated using the system
          - first check that the default shipper defined in config.php is in the database
          if so use this
          - then check to see if any shippers are defined at all if not report the error
          and show a link to set them up
          - if shippers defined but the default shipper is bogus then use the first shipper defined
         */
        if ((isset($BestShipper) and $BestShipper == '') AND ($_POST['ShipVia'] == '' || !isset($_POST['ShipVia']))) {
            $sql = 'SELECT shipper_id
						FROM shippers
						WHERE shipper_id=' . $_SESSION['Default_Shipper'];
            $ErrMsg = _('There was a problem testing for the default shipper');
            $DbgMsg = _('SQL used to test for the default shipper') . ':';
            $TestShipperExists = DB_query($sql, $db, $ErrMsg, $DbgMsg);

            if (DB_num_rows($TestShipperExists) == 1) {

                $BestShipper = $_SESSION['Default_Shipper'];
            } else {

                $sql = 'SELECT shipper_id
							FROM shippers';
                $TestShipperExists = DB_query($sql, $db, $ErrMsg, $DbgMsg);

                if (DB_num_rows($TestShipperExists) >= 1) {
                    $ShipperReturned = DB_fetch_row($TestShipperExists);
                    $BestShipper = $ShipperReturned[0];
                } else {
                    prnMsg(_('We have a problem') . ' - ' . _('there are no shippers defined') . '. ' . _('Please use the link below to set up shipping or freight companies') . ', ' . _('the system expects the shipping company to be selected or a default freight company to be used'), 'error');
                    echo '<a href="' . $rootpath . 'Shippers.php">' . _('Enter') . '/' . _('Amend Freight Companies') . '</a>';
                }
            }
            if (isset($_SESSION['Items' . $identifier]->ShipVia) AND $_SESSION['Items' . $identifier]->ShipVia != '') {
                $_POST['ShipVia'] = $_SESSION['Items' . $identifier]->ShipVia;
            } else {
                $_POST['ShipVia'] = $BestShipper;
            }
        }
    }
}

if (isset($_POST['MakeRecurringOrder']) AND !$InputErrors) {

    echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/RecurringSalesOrders.php?' . SID . 'identifier=' . $identifier . '&NewRecurringOrder=Yes">';
    prnMsg(_('You should automatically be forwarded to the entry of recurring order details page') . '. ' . _('If this does not happen') . '(' . _('if the browser does not support META Refresh') . ') ' . "<a href='" . $rootpath . '/RecurringOrders.php?' . SID . 'identifier=' . $identifier . "&NewRecurringOrder=Yes'>" . _('click here') . '</a> ' . _('to continue'), 'info');
    include('includes/footer.inc');
    exit;
}


if (isset($_POST['BackToLineDetails']) and $_POST['BackToLineDetails'] == _('Modify Order Lines')) {

    echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/SelectOrderItems.php?' . SID . 'identifier=' . $identifier . '">';
    prnMsg(_('You should automatically be forwarded to the entry of the order line details page') . '. ' . _('If this does not happen') . '(' . _('if the browser does not support META Refresh') . ') ' . "<a href='" . $rootpath . '/SelectOrderItems.php?' . SID . 'identifier=' . $identifier . "'>" . _('click here') . '</a> ' . _('to continue'), 'info');
    include('includes/footer.inc');
    exit;
}

If (isset($_POST['ProcessOrder'])) {
    /* Default OK_to_PROCESS to 1 change to 0 later if hit a snag */
    if ($InputErrors == 0) {
        $OK_to_PROCESS = 1;
    }
    If ($_POST['FreightCost'] != $OldFreightCost && $_SESSION['DoFreightCalc'] == True) {
        $OK_to_PROCESS = 0;
        prnMsg(_('The freight charge has been updated') . '. ' . _('Please reconfirm that the order and the freight charges are acceptable and then confirm the order again if OK') . ' <br> ' . _('The new freight cost is') . ' ' . $_POST['FreightCost'] . ' ' . _('and the previously calculated freight cost was') . ' ' . $OldFreightCost, 'warn');
    } else {

        /* check the customer's payment terms */
        $sql = 'SELECT daysbeforedue,
				dayinfollowingmonth
			FROM debtorsmaster,
				paymentterms
			WHERE debtorsmaster.paymentterms=paymentterms.termsindicator
			AND debtorsmaster.debtorno = ' . "'" . $_SESSION['Items' . $identifier]->DebtorNo . "'";

        $ErrMsg = _('The customer terms cannot be determined') . '. ' . _('This order cannot be processed because');
        $DbgMsg = _('SQL used to find the customer terms') . ':';
        $TermsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);


        $myrow = DB_fetch_array($TermsResult);
        if ($myrow['daysbeforedue'] == 0 && $myrow['dayinfollowingmonth'] == 0) {

            /* THIS IS A CASH SALE NEED TO GO OFF TO 3RD PARTY SITE SENDING MERCHANT ACCOUNT DETAILS AND CHECK FOR APPROVAL FROM 3RD PARTY SITE BEFORE CONTINUING TO PROCESS THE ORDER

              UNTIL ONLINE CREDIT CARD PROCESSING IS PERFORMED ASSUME OK TO PROCESS

              NOT YET CODED */

            $OK_to_PROCESS = 1;
        } #end if cash sale detected
    } #end if else freight charge not altered
} #end if process order


if (isset($OK_to_PROCESS) and $OK_to_PROCESS == 1 && $_SESSION['ExistingOrder'] == "0") {
    echo "Not existing order" . $_SESSION['ExistingOrder'];
    echo " " . $_SESSION['Items' . $identifier]->Quotation;
    /* finally write the order header to the database and then the order line details */


    $DelDate = FormatDateforSQL($_SESSION['Items' . $identifier]->DeliveryDate);
    $QuotDate = FormatDateforSQL($_SESSION['Items' . $identifier]->QuoteDate);
    $ConfDate = FormatDateforSQL($_SESSION['Items' . $identifier]->ConfirmedDate);

    $Result = DB_Txn_Begin($db);
    /* Check if tendertypes is selected in company preferences */
    $SQL = "SELECT tender_type FROM companies";
    $ErrMsg = _('Could not open company table because');
    $result = DB_query($SQL, $db, $ErrMsg);
    $myrow = DB_fetch_array($result);
    $_SESSION['ActiveTenderType'] = $myrow['tender_type'];
    //if ($_SESSION['Items'.$identifier]->Quotation==1){
    if ($_SESSION['ActiveTenderType'] == "Y" && $_SESSION['Items' . $identifier]->Quotation != 1) {
        echo "Under yes... Order" . $_SESSION['ActiveTenderType'];
        $TypeNoField = "typeno" . $_SESSION['UserStockLocation'];
        $OrderNo = $_SESSION['UserStockLocation'] . GetNextTransNoTenderType(30, $db, $TypeNoField);
    } else {
        $TypeNoField = "typeno" . $_SESSION['UserStockLocation'];
        echo "<br>This is a qotation- > prfeix is QT ";
        $OrderNo = $_SESSION['UserStockLocation'] . GetNextTransQuotation(30, $db, $TypeNoField);
    }


    $HeaderSQL = 'INSERT INTO salesorders (
								orderno,
								debtorno,
								branchcode,
								customerref,
                                                                custContact,
								comments,
								orddate,
								ordertype,
								shipvia,';
    if ($_POST['dateopt'] == "txtdate" && $_POST['inputedate'] != '') {
        $HeaderSQL .= 'ordernumofdays,';
    } else {
        $HeaderSQL .= 'deliverydate,';
    }
    $HeaderSQL .= '	deliverto,
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
                                                                quotedate,
								confirmeddate,
								quotation,
								deliverblind,
								packsize)
							VALUES (
								' . "'" . $OrderNo . "'" . ',
								' . "'" . $_SESSION['Items' . $identifier]->DebtorNo . "'" . ',
								' . "'" . $_SESSION['Items' . $identifier]->Branch . "'" . ',
								' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->CustRef) . "'" . ',
                                                                ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->CustRefVia) . "'" . ',
								' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->Comments) . "'" . ',
								' . "'" . Date("Y-m-d H:i") . "'" . ',
								' . "'" . $_SESSION['Items' . $identifier]->DefaultSalesType . "'" . ',
								' . $_POST['ShipVia'] . ',';
    if ($_POST['dateopt'] == "txtdate" && $_POST['inputedate'] != '') {
        $HeaderSQL .= $_POST['inputedate'] . ',';
    } else {
        $HeaderSQL .= "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->DeliveryDate)) . "'" . ',';
        //deliverydate = '."'" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->DeliveryDate)) . "'".',
        // $HeaderSQL .=  '  deliverydate = '."'" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->DeliveryDate)) . "'".',';
    }
    $HeaderSQL .= "'" . DB_escape_string($_SESSION['Items' . $identifier]->DeliverTo) . "'" . ',
                                                                ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd1) . "'" . ',
								' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd2) . "'" . ',
								' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd3) . "'" . ',
								' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd4) . "'" . ',
								' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd5) . "'" . ',
								' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd6) . "'" . ',
								' . "'" . $_SESSION['Items' . $identifier]->PhoneNo . "'" . ',
								' . "'" . $_SESSION['Items' . $identifier]->Email . "'" . ',
								' . $_SESSION['Items' . $identifier]->FreightCost . ',
								' . "'" . $_SESSION['Items' . $identifier]->Location . "'" . ',
								' . "'" . $QuotDate . "'" . ',
								' . "'" . $ConfDate . "'" . ',
								' . $_SESSION['Items' . $identifier]->Quotation . ',
								' . $_SESSION['Items' . $identifier]->DeliverBlind . ',
								' . "'" . $_POST['Pack'] . "'" . '
								)';
    //echo "<br>sql is " . $HeaderSQL . "<br>";
    //'."'" . $DelDate . "'".',
    $ErrMsg = _('The order cannot be added because');
    $InsertQryResult = DB_query($HeaderSQL, $db, $ErrMsg);

    if ($_SESSION['ActiveTenderType'] == "Y" && $_SESSION['Items' . $identifier]->Quotation != 1) {

        $SQL_temp = "CREATE TABLE IF NOT EXISTS `temp_salesorderdetails` (
                          `orderlineno` int(11) NOT NULL default '0',
                          `orderno` varchar(11) NOT NULL default '0',
                          `stkcode` varchar(20) NOT NULL default '',
                          `qtyinvoiced` double NOT NULL default '0',
                          `unitprice` double NOT NULL default '0',
                          `quantity` double NOT NULL default '0',
                          `estimate` tinyint(4) NOT NULL default '0',
                          `discountpercent` double NOT NULL default '0',
                          `actualdispatchdate` datetime NOT NULL default '0000-00-00 00:00:00',
                          `completed` tinyint(1) NOT NULL default '0',
                          `narrative` mediumtext,
                          `itemdue` date default NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
                          `poline` varchar(10) default NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
                          `packdesc` varchar(45) NOT NULL,
                          `commissionrate` double NOT NULL default '0',
                          `commissionearned` double NOT NULL default '0',
                          `negative` varchar(1) NOT NULL default 'N',
                          PRIMARY KEY  (`orderlineno`,`orderno`),
                          KEY `OrderNo` (`orderno`),
                          KEY `StkCode` (`stkcode`),
                          KEY `Completed` (`completed`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $run = DB_query($SQL_temp, $db, $ErrMsg);

        $TempStartOf_LineItemsSQL = 'INSERT INTO temp_salesorderdetails (
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
										VALUES (';
        $DbgMsg = _('The SQL that failed was');
        foreach ($_SESSION['Items' . $identifier]->LineItems as $StockItem) {
            //echo "line item " . $StockItem->LineNumber . "<br>";
            /* Get the packdescription for the order line */

            $StockItem->Packdescription = $_POST[$StockItem->StockID];
            $TempLineItemsSQL = $TempStartOf_LineItemsSQL .
                    $StockItem->LineNumber . ',
					' . "'" . $OrderNo . "'" . ',
					' . "'" . $StockItem->StockID . "'" . ',
					' . $StockItem->Price . ',
					' . $StockItem->Quantity . ',
					' . floatval($StockItem->DiscountPercent) . ',
					' . "'" . DB_escape_string($StockItem->Narrative) . "'" . ',
					' . "'" . $StockItem->POLine . "'" . ',
					' . "'" . FormatDateForSQL($StockItem->ItemDue) . "'" . ',
					' . "'" . $_POST['Pack' . $StockItem->LineNumber] . "'" . '
				)';
            //echo "<br>sql line 433 " . $LineItemsSQL . "<br>";
            $ErrMsg = _('Unable to add the sales order line');
            $run_temp = DB_query($TempLineItemsSQL, $db, $ErrMsg, $DbgMsg, true);
        }
    }



    $StartOf_LineItemsSQL = 'INSERT INTO salesorderdetails (
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
										VALUES (';
    $DbgMsg = _('The SQL that failed was');
    foreach ($_SESSION['Items' . $identifier]->LineItems as $StockItem) {
        //echo "line item " . $StockItem->LineNumber . "<br>";
        /* Get the packdescription for the order line */

        $StockItem->Packdescription = $_POST[$StockItem->StockID];


        $LineItemsSQL = $StartOf_LineItemsSQL .
                $StockItem->LineNumber . ',
					' . "'" . $OrderNo . "'" . ',
					' . "'" . $StockItem->StockID . "'" . ',
					' . $StockItem->Price . ',
					' . $StockItem->Quantity . ',
					' . floatval($StockItem->DiscountPercent) . ',
					' . "'" . DB_escape_string($StockItem->Narrative) . "'" . ',
					' . "'" . $StockItem->POLine . "'" . ',
					' . "'" . FormatDateForSQL($StockItem->ItemDue) . "'" . ',
					' . "'" . $_POST['Pack' . $StockItem->LineNumber] . "'" . '
				)';
        //echo "<br>sql line 433 " . $LineItemsSQL . "<br>";
        $ErrMsg = _('Unable to add the sales order line');
        $Ins_LineItemResult = DB_query($LineItemsSQL, $db, $ErrMsg, $DbgMsg, true);
        //echo "<br>sql is " . $LineItemsSQL . "<br>";
        /* Now check to see if the item is manufactured
         * 			and AutoCreateWOs is on
         * 			and it is a real order (not just a quotation) */

        if ($StockItem->MBflag == 'M'
                AND $_SESSION['AutoCreateWOs'] == 1
                AND $_SESSION['Items' . $identifier]->Quotation != 1) { //oh yeah its all on!
            echo '<br>';

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
					AND stockmaster.mbflag='A'
					AND salesorderdetails.completed=0";
            $AssemblyDemandResult = DB_query($SQL, $db);
            $AssemblyDemandRow = DB_fetch_row($AssemblyDemandResult);
            $QuantityAssemblyDemand = $AssemblyDemandRow[0];

            $SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as qtyonorder
					FROM purchorderdetails,
						purchorders
					WHERE purchorderdetails.orderno = purchorders.orderno
					AND purchorderdetails.itemcode = '" . $StockItem->StockID . "'
					AND purchorderdetails.completed = 0";
            $PurchOrdersResult = DB_query($SQL, $db);
            $PurchOrdersRow = DB_fetch_row($PurchOrdersResult);
            $QuantityPurchOrders = $PurchOrdersRow[0];

            $SQL = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) as qtyonorder
					FROM woitems INNER JOIN workorders
					ON woitems.wo=workorders.wo
					WHERE woitems.stockid = '" . $StockItem->StockID . "'
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
								 VALUES (" . $WONo . ",
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
								 VALUES ( " . $WONo . ",
										 '" . $StockItem->StockID . "',
										 " . $WOQuantity . ",
										  " . $Cost . ")";
                $ErrMsg = _('The work order item could not be added');
                $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);

                //Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
                WoRealRequirements($db, $WONo, $_SESSION['DefaultFactoryLocation'], $StockItem->StockID);

                $FactoryManagerEmail = _('A new work order has been created for') .
                        ":\n" . $StockItem->StockID . ' - ' . $StockItem->Descr . ' x ' . $WOQuantity . ' ' . $StockItem->UOM .
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
                            $sql = 'INSERT INTO woserialnos (wo,
																stockid,
																serialno)
													VALUES (' . $WONo . ",
															'" . $StockItem->StockID . "',
															" . ($StockItem->NextSerialNo + $i) . ')';
                            $ErrMsg = _('The serial number for the work order item could not be added');
                            $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
                            $FactoryManagerEmail .= "\n" . ($StockItem->NextSerialNo + $i);
                        }
                    } //end loop around creation of woserialnos
                    $NewNextSerialNo = ($StockItem->NextSerialNo + $WOQuantity + 1);
                    $ErrMsg = _('Could not update the new next serial number for the item');
                    $UpdateNextSerialNoResult = DB_query('UPDATE stockmaster SET nextserialno=' . $NewNextSerialNo . " WHERE stockid='" . $StockItem->StockID . "'", $db, $ErrMsg, $DbgMsg, true);
                } // end if the item is serialised and nextserialno is set

                $EmailSubject = _('New Work Order Number') . ' ' . $WONo . ' ' . _('for') . ' ' . $StockItem->StockID . ' x ' . $WOQuantity;
                //Send email to the Factory Manager
                mail($_SESSION['FactoryManagerEmail'], $EmailSubject, $FactoryManagerEmail);
            } //end if with this sales order there is a shortfall of stock - need to create the WO
        }//end if auto create WOs in on
    } /* end inserted line items into sales order details */

    $result = DB_Txn_Commit($db);
    echo '<br>';
    if ($_SESSION['Items' . $identifier]->Quotation == 1) {
        prnMsg(_('Quotation Number') . ' ' . $OrderNo . ' ' . _('has been entered'), 'success');
    } else {
        prnMsg(_('Order Number') . ' ' . $OrderNo . ' ' . _('has been entered'), 'success');
    }

    if (count($_SESSION['AllowedPageSecurityTokens']) > 1) {
        /* Only allow print of packing slip for internal staff - customer logon's cannot go here */

        if ($_POST['Quotation'] == 0 && !$_SESSION['Items' . $identifier]->CashPaymentTerms == 'CA') { /* then its not a quotation its a real order */

            echo '<div style="text-align:center"><p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt="">' . ' ' . '<a target="_blank" href="' . $rootpath . '/PrintCustOrder.php?' . SID . 'identifier=' . $identifier . '&TransNo=' . $OrderNo . '">' . _('Print packing slip') . ' (' . _('Preprinted stationery') . ')' . '</a>';
            echo '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PrintCustOrder_generic.php?' . SID . 'identifier=' . $identifier . '&TransNo=' . $OrderNo . '">' . _('Print packing slip And Delivery Note') . ' (' . _('Laser') . ')' . '</a>';

            echo '<p><img src="' . $rootpath . '/css/' . $theme . '/images/reports.png" title="' . _('Invoice') . '" alt="">' . ' ' . '<a href="' . $rootpath . '/ConfirmDispatch_Invoice.php?' . SID . 'identifier=' . $identifier . '&OrderNumber=' . $OrderNo . '">' . _('Confirm Dispatch and Produce Invoice') . '</a></div>';
        } elseif ($_SESSION['Items' . $identifier]->CashPaymentTerms == 'CA' && $_POST['Quotation'] == 0) {
            //CASH14&InvNumber=CPTIN111167&TenderTotal=794.35&NewReceipt=Yes&OrderNumber=CPTSO111333
            echo '<div style="text-align:center"><p><img src="' . $rootpath . '/css/' . $theme . '/images/reports.png" title="' . _('Invoice') . '" alt="">' . ' ' . '<a href="' . $rootpath . '/CustomerReceiptTenderType.php?' . SID . 'Debtor=' . $_SESSION['Items' . $identifier]->DebtorNo . '&OrderNumber=' . $OrderNo . '">' . _('Confirm Dispatch and Produce Invoice') . '</a>';
            echo '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PrintCustOrder_generic.php?' . SID . 'identifier=' . $identifier . '&TransNo=' . $OrderNo . '">' . _('Print packing slip And Delivery Note') . ' (' . _('Laser') . ')' . '</a></div>';
        } else {
            /* link to print the quotation */
            echo '<div style="text-align:center"><p><img src="' . $rootpath . '/css/' . $theme . '/images/reports.png" title="' . _('Order') . '" alt="">' . ' ' . '<a href="' . $rootpath . '/PDFQuotation.php?' . SID . 'identifier=' . $identifier . '&QuotationNo=' . $OrderNo . '">' . _('Print Quotation') . '</a></div>';
        }
        echo '<div class="centre"><p><img src="' . $rootpath . '/css/' . $theme . '/images/sales.png" title="' . _('Order') . '" alt="">' . ' ' . '<a href="' . $rootpath . '/SelectOrderItems.php?' . SID . 'identifier=' . $identifier . '&NewOrder=Yes">' . _('Add Sales Order') . '</a></div>';
    } else {
        /* its a customer logon so thank them */
        prnMsg(_('Thank you for your business'), 'success');
    }

    unset($_SESSION['Items' . $identifier]->LineItems);
    unset($_SESSION['Items' . $identifier]);
    //unset($_SESSION['ExistingOrder']);
    include('includes/footer.inc');
    exit;
} elseif (isset($OK_to_PROCESS) and $OK_to_PROCESS == 1 && $_SESSION['ExistingOrder'] != "0") {

    /* echo "Check....... ".$_SESSION['Items'.$identifier]->Quotation;
      /* update the order header then update the old order line details and insert the new lines
      echo "Existing Quotation "; */
    if (isset($_POST['CommentsText'])) {/* Global If update.... */
        $_SESSION['Items' . $identifier]->Comments = $_POST['CommentsText'];
    }

    echo $_SESSION['Items' . $identifier]->Quotation;
    $DelDate = FormatDateforSQL($_SESSION['Items' . $identifier]->DeliveryDate);
    $QuotDate = FormatDateforSQL($_SESSION['Items' . $identifier]->QuoteDate);
    $ConfDate = FormatDateforSQL($_SESSION['Items' . $identifier]->ConfirmedDate);

    $Result = DB_Txn_Begin($db);
    if ($_SESSION['Items' . $identifier]->Quotation == 0 && $_POST['ConvertOrder'] == 1)/* if to convert sales  order - to quotation.... */ {
        echo "line 654. " . $_SESSION['Items' . $identifier]->Quotation . '<br>';
        //$TypeNoField = "typeno" . $_SESSION['UserStockLocation'];
        //$OrderNs = $_SESSION['UserStockLocation'] . GetNextTransQuotation(30, $db, $TypeNoField);
        $OrderNs = str_replace("SO", "QT", $_SESSION['ExistingOrder']);
        $Qtation = 1;
        $HeaderSQL = 'UPDATE salesorders
			SET debtorno = ' . "'" . $_SESSION['Items' . $identifier]->DebtorNo . "'" . ',
                                orderno=' . "'" . $OrderNs . "'" . ',
				branchcode = ' . "'" . $_SESSION['Items' . $identifier]->Branch . "'" . ',
				customerref = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->CustRef) . "'" . ',
                                custContact =  ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->CustRefVia) . "'" . ',
				comments = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->Comments) . "'" . ',
				ordertype = ' . "'" . $_SESSION['Items' . $identifier]->DefaultSalesType . "'" . ',
				shipvia = ' . $_POST['ShipVia'] . ',
				
				quotedate = ' . "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->QuoteDate)) . "'" . ',
				confirmeddate = ' . "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->ConfirmedDate)) . "'" . ',
				deliverto = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DeliverTo) . "'" . ',
				deladd1 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd1) . "'" . ',
				deladd2 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd2) . "'" . ',
				deladd3 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd3) . "'" . ',
				deladd4 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd4) . "'" . ',
				deladd5 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd5) . "'" . ',
				deladd6 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd6) . "'" . ',';
        if ($_POST['dateopt'] == "txtdate" && $_SESSION['Items' . $identifier]->inputedate != '') {
            $HeaderSQL .= ' ordernumofdays =' . $_SESSION['Items' . $identifier]->inputedate . ',';
        } else {
            //deliverydate = '."'" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->DeliveryDate)) . "'".',
            $HeaderSQL .= '  deliverydate = ' . "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->DeliveryDate)) . "'" . ',';
        }
        $HeaderSQL .= ' contactphone = ' . "'" . $_SESSION['Items' . $identifier]->PhoneNo . "'" . ',
				contactemail = ' . "'" . $_SESSION['Items' . $identifier]->Email . "'" . ',
				freightcost = ' . $_SESSION['Items' . $identifier]->FreightCost . ',
				fromstkloc = ' . "'" . $_SESSION['Items' . $identifier]->Location . "'" . ',
				deliverydate = ' . "'" . $DelDate . "'" . ',
				quotedate = ' . "'" . $QuotDate . "'" . ',
				confirmeddate = ' . "'" . $ConfDate . "'" . ',
				printedpackingslip = ' . $_POST['ReprintPackingSlip'] . ',
				quotation = ' . $Qtation . ',
				deliverblind = ' . $_SESSION['Items' . $identifier]->DeliverBlind . '
			WHERE salesorders.orderno=' . "'" . $_SESSION['ExistingOrder'] . "'";

        $DbgMsg = _('The SQL that was used to update the order and failed was');
        $ErrMsg = _('The order cannot be updated because');
        $InsertQryResult = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);


        foreach ($_SESSION['Items' . $identifier]->LineItems as $StockItem) {
//echo 1;
            /* Check to see if the quantity reduced to the same quantity
              as already invoiced - so should set the line to completed */
            if ($StockItem->Quantity == $StockItem->QtyInv) {
                $Completed = 1;
            } else { /* order line is not complete */
                $Completed = 0;
            }


            $Sql = "SELECT orderno, stkcode,orderlineno
                    FROM salesorderdetails
                    WHERE orderno='" . $_SESSION['ExistingOrder'] . "'
                    AND stkcode = '" . $StockItem->StockID . "'
                    AND orderlineno=" . $StockItem->LineNumber;

            $ErrMsg = "Cannot update";
            $resul = DB_query($Sql, $db, $ErrMsg);
            $myrow = DB_fetch_array($resul);
            echo $StockItem->StockID;
            if ($myrow['stkcode'] == $StockItem->StockID && $myrow['orderlineno'] == $StockItem->LineNumber) {
                echo $myrow['stkcode'];
                $LineItemsSQL = 'UPDATE salesorderdetails SET
                                                                orderno =' . "'" . $OrderNs . "'" . ',
                                                                unitprice=' . $StockItem->Price . ',
								quantity=' . $StockItem->Quantity . ',
								discountpercent=' . floatval($StockItem->DiscountPercent) . ',
								completed=' . $Completed . ',
								poline=' . "'" . $StockItem->POLine . "'" . ',
								itemdue=' . "'" . FormatDateForSQL($StockItem->ItemDue) . "'" . ',
                                                                packdesc =' . "'" . $_POST['Pack' . $StockItem->LineNumber] . "'" . '
					WHERE salesorderdetails.orderno=' . "'" . $_SESSION['ExistingOrder'] . "'" . '
					AND salesorderdetails.orderlineno=' . "'" . $StockItem->LineNumber . "'";
                $DbgMsg = _('The SQL that was used to modify the order line and failed was');
                $ErrMsg = _('The updated order line cannot be modified because');

                $Upd_LineItemResult = DB_query($LineItemsSQL, $db, $ErrMsg, $DbgMsg, true);
                DB_free_result($Upd_LineItemResult);
            } else {

                $LineItems2 = 'INSERT INTO salesorderdetails (orderlineno,orderno,
											stkcode,
											unitprice,
											quantity,
											discountpercent,
											narrative,
											poline,
											itemdue,
											packdesc)
										VALUES ('
                        . $StockItem->LineNumber . ',
					' . "'" . $_SESSION['ExistingOrder'] . "'" . ',
					' . "'" . $StockItem->StockID . "'" . ',
					' . $StockItem->Price . ',
					' . $StockItem->Quantity . ',
					' . floatval($StockItem->DiscountPercent) . ',
					' . "'" . DB_escape_string($StockItem->Narrative) . "'" . ',
					' . "'" . $StockItem->POLine . "'" . ',
					' . "'" . FormatDateForSQL($StockItem->ItemDue) . "'" . ',
					' . "'" . $_POST['Pack' . $StockItem->LineNumber] . "'" . '
				)';
                $DbgMsg = _('The SQL that was used to modify the order line and failed was');
                $ErrMsg = _('The updated order line cannot be modified because');
                $INS_LineItemResult = DB_query($LineItems2, $db, $ErrMsg, $DbgMsg, true);
                DB_free_result($INS_LineItemResult);
            }
        } /* updated line items into sales order details */

        $Result = DB_Txn_Commit($db);


        prnMsg(_('Order number ' . $_SESSION['ExistingOrder']) . ' Coverted to ' . $OrderNs . ' ' . _('Updated'), 'success');


        echo '<br><div style="text-align:center"><p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt=""><a href="' . $rootpath . '/PrintCustOrder.php?' . SID . 'identifier=' . $identifier . '&TransNo=' . $OrderNs . '">' . _('Print packing slip - pre-printed stationery') . '</a>';
        if ($_SESSION['Items' . $identifier]->CashPaymentTerms == 'CA') {
            echo '<p><img src="' . $rootpath . '/css/' . $theme . '/images/reports.png" title="' . _('Invoice') . '" alt="">' . ' ' . '<a href="' . $rootpath . '/CustomerReceiptTenderType.php?' . SID . 'Debtor=' . $_SESSION['Items' . $identifier]->DebtorNo . '&OrderNumber=' . $OrderNo . '">' . _('Confirm Dispatch and Produce Invoice') . '</a>';
        } else {
            echo '<p><img src="' . $rootpath . '/css/' . $theme . '/images/reports.png" title="' . _('Invoice') . '" alt=""><a href="' . $rootpath . '/ConfirmDispatch_Invoice.php?' . SID . 'identifier=' . $identifier . '&OrderNumber=' . $OrderNs . '">' . _('Confirm Order Delivery Quantities and Produce Invoice') . '</a>';
        }
        echo '<br><a  target="_blank" href="' . $rootpath . '/PrintCustOrder_generic.php?' . SID . 'identifier=' . $identifier . '&TransNo=' . $OrderNs . '">' . _('Print packing slip and delivery note') . ' (' . _('Laser') . ')' . '</a>';
        echo '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt=""><a href="' . $rootpath . '/SelectSalesOrder.php?' . SID . 'identifier=' . $identifier . '">' . _('Select A Different Order') . '</a></div>';
        include('includes/footer.inc');
        unset($_SESSION['Items' . $identifier]->LineItems);
        unset($_SESSION['Items' . $identifier]);
        exit;
    } elseif ($_POST['ConvertOrder'] == 0 && $_SESSION['Items' . $identifier]->Quotation == 0) {//Update sales orders without convert

        if (isset($_POST['CustOrderNum'])) {
            $_SESSION['Items' . $identifier]->CustRef = $_POST['CustOrderNum'];
        }

        // $OrderNs = str_replace("SO","QT",$_SESSION['ExistingOrder']);
        $HeaderSQL = 'UPDATE salesorders
			SET debtorno = ' . "'" . $_SESSION['Items' . $identifier]->DebtorNo . "'" . ',
                              
				branchcode = ' . "'" . $_SESSION['Items' . $identifier]->Branch . "'" . ',
				customerref = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->CustRef) . "'" . ',
                                custContact =  ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->CustRefVia) . "'" . ',
				comments = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->Comments) . "'" . ',
				ordertype = ' . "'" . $_SESSION['Items' . $identifier]->DefaultSalesType . "'" . ',
				shipvia = ' . $_POST['ShipVia'] . ',
				
				quotedate = ' . "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->QuoteDate)) . "'" . ',
				confirmeddate = ' . "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->ConfirmedDate)) . "'" . ',
				deliverto = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DeliverTo) . "'" . ',
				deladd1 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd1) . "'" . ',
				deladd2 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd2) . "'" . ',
				deladd3 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd3) . "'" . ',
				deladd4 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd4) . "'" . ',
				deladd5 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd5) . "'" . ',
				deladd6 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd6) . "'" . ',';
        if ($_POST['dateopt'] == "txtdate" && $_SESSION['Items' . $identifier]->inputedate != '') {
            $HeaderSQL .= ' ordernumofdays =' . $_SESSION['Items' . $identifier]->inputedate . ',';
            //echo "here 1048".$HeaderSQL;
        } else {
            //deliverydate = '."'" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->DeliveryDate)) . "'".',
            $HeaderSQL .= '  deliverydate = ' . "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->DeliveryDate)) . "'" . ',';
        }
        $HeaderSQL .= ' contactphone = ' . "'" . $_SESSION['Items' . $identifier]->PhoneNo . "'" . ',
				contactemail = ' . "'" . $_SESSION['Items' . $identifier]->Email . "'" . ',
				freightcost = ' . $_SESSION['Items' . $identifier]->FreightCost . ',
				fromstkloc = ' . "'" . $_SESSION['Items' . $identifier]->Location . "'" . ',
				deliverydate = ' . "'" . $DelDate . "'" . ',
				quotedate = ' . "'" . $QuotDate . "'" . ',
				confirmeddate = ' . "'" . $ConfDate . "'" . ',
				printedpackingslip = ' . $_POST['ReprintPackingSlip'] . ',
				quotation = ' . $_SESSION['Items' . $identifier]->Quotation . ',
				deliverblind = ' . $_SESSION['Items' . $identifier]->DeliverBlind . '
			WHERE salesorders.orderno=' . "'" . $_SESSION['ExistingOrder'] . "'";
        //echo $HeaderSQL."<br />";
        $DbgMsg = _('The SQL that was used to update the order and failed was');
        $ErrMsg = _('The order cannot be updated because');
        $InsertQryResult = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);






        foreach ($_SESSION['Items' . $identifier]->LineItems as $StockItem) {
            echo 3;
            /* Check to see if the quantity reduced to the same quantity
              as already invoiced - so should set the line to completed */
            if ($StockItem->Quantity == $StockItem->QtyInv) {
                $Completed = 1;
            } else { /* order line is not complete */
                $Completed = 0;
            }


            $Sql = "SELECT orderno, stkcode,orderlineno
                    FROM salesorderdetails
                    WHERE orderno='" . $_SESSION['ExistingOrder'] . "'
                    AND stkcode = '" . $StockItem->StockID . "'
                    AND orderlineno=" . $StockItem->LineNumber;

            $resul = DB_query($Sql, $db);
            $myrow = DB_fetch_array($resul);
            //echo "___".$_SESSION['ExistingOrder']."______".$StockItem->StockID."<br>";
            //echo "***".$myrow['stkcode'];
            if ($myrow['stkcode'] == $StockItem->StockID && $myrow['orderlineno'] == $StockItem->LineNumber) {
                //echo "<br>Inside__".$myrow[' stkcode']. "_<br>";
                $LineItemsSQL = 'UPDATE salesorderdetails SET

                                                                unitprice=' . $StockItem->Price . ',
								quantity=' . $StockItem->Quantity . ',
								discountpercent=' . floatval($StockItem->DiscountPercent) . ',
								completed=' . $Completed . ',
								poline=' . "'" . $StockItem->POLine . "'" . ',
								itemdue=' . "'" . FormatDateForSQL($StockItem->ItemDue) . "'" . ',
                                                                packdesc =' . "'" . $_POST['Pack' . $StockItem->LineNumber] . "'" . '
					WHERE salesorderdetails.orderno=' . "'" . $_SESSION['ExistingOrder'] . "'" . '
					AND salesorderdetails.orderlineno=' . "'" . $StockItem->LineNumber . "'";

                $DbgMsg = _('The SQL that was used to modify the order line and failed was');
                $ErrMsg = _('The updated order line cannot be modified because');
                $Upd_LineItemResult = DB_query($LineItemsSQL, $db, $ErrMsg, $DbgMsg, true);
            } else {
                $LineItems2 = 'INSERT INTO salesorderdetails (orderlineno,orderno,
											stkcode,
											unitprice,
											quantity,
											discountpercent,
											narrative,
											poline,
											itemdue,
											packdesc)
										VALUES ('
                        . $StockItem->LineNumber . ',
					' . "'" . $_SESSION['ExistingOrder'] . "'" . ',
					' . "'" . $StockItem->StockID . "'" . ',
					' . $StockItem->Price . ',
					' . $StockItem->Quantity . ',
					' . floatval($StockItem->DiscountPercent) . ',
					' . "'" . DB_escape_string($StockItem->Narrative) . "'" . ',
					' . "'" . $StockItem->POLine . "'" . ',
					' . "'" . FormatDateForSQL($StockItem->ItemDue) . "'" . ',
					' . "'" . $_POST['Pack' . $StockItem->LineNumber] . "'" . '
				)';
                $DbgMsg = _('The SQL that was used to modify the order line and failed was');
                $ErrMsg = _('The updated order line cannot be modified because');
                $INS_LineItemResult = DB_query($LineItems2, $db, $ErrMsg, $DbgMsg, true);
            }
        } /* updated line items into sales order details */

        $Result = DB_Txn_Commit($db);


        prnMsg(_('Order number ') . ' ' . $_SESSION['ExistingOrder'] . ' ' . _('has been Updated'), 'success');

        //replacing all the $OrderNs, with $_SESSION['ExistingOrder'] 
        echo "Session is " . $_SESSION['Items' . $identifier]->CashPaymentTerms;
        echo '<br><div style="text-align:center"><p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt=""><a href="' . $rootpath . '/PrintCustOrder.php?' . SID . 'identifier=' . $identifier . '&TransNo=' . $_SESSION['ExistingOrder'] . '">' . _('Print packing slip - pre-printed stationery') . '</a>';
        if ($_SESSION['Items' . $identifier]->CashPaymentTerms == 'CA') {
            echo '<p><img src="' . $rootpath . '/css/' . $theme . '/images/reports.png" title="' . _('Invoice') . '" alt="">' . ' ' . '<a href="' . $rootpath . '/CustomerReceiptTenderType.php?' . SID . 'Debtor=' . $_SESSION['Items' . $identifier]->DebtorNo . '&OrderNumber=' . $_SESSION['ExistingOrder'] . '">' . _('Confirm Dispatch and Produce Invoice') . '</a>';
        } else {
            echo '<p><img src="' . $rootpath . '/css/' . $theme . '/images/reports.png" title="' . _('Invoice') . '" alt=""><a href="' . $rootpath . '/ConfirmDispatch_Invoice.php?' . SID . 'identifier=' . $identifier . '&OrderNumber=' . $_SESSION['ExistingOrder'] . '">' . _('Confirm Order Delivery Quantities and Produce Invoice') . '</a>';
        }
        echo '<br><a  target="_blank" href="' . $rootpath . '/PrintCustOrder_generic.php?' . SID . 'identifier=' . $identifier . '&TransNo=' . $_SESSION['ExistingOrder'] . '">' . _('Print packing slip and delivery note') . ' (' . _('Laser') . ')' . '</a>';
        echo '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt=""><a href="' . $rootpath . '/SelectQuotations.php?' . SID . 'identifier=' . $identifier . '">' . _('Select A Different Quotation') . '</a></div>';
        include('includes/footer.inc');
        unset($_SESSION['Items' . $identifier]->LineItems);
        unset($_SESSION['Items' . $identifier]);
        exit;
    }

    if ($_POST['ConvertOrder'] == 1 && $_SESSION['Items' . $identifier]->Quotation == 1) {// Convert quotation to sales orders


        /* Now if customer */
        //$TypeNoField = "typeno" . $_SESSION['UserStockLocation'];
        //$OrderNs = $_SESSION['UserStockLocation'] . GetNextTransNoTenderType(30, $db, $TypeNoField);
        $OrderNs = str_replace("QT", "SO", $_SESSION['ExistingOrder']);
        $Qtation = 0;
        $HeaderSQL = 'UPDATE salesorders
			  SET debtorno = ' . "'" . $_SESSION['Items' . $identifier]->DebtorNo . "'" . ',
                                orderno=' . "'" . $OrderNs . "'" . ',
				branchcode = ' . "'" . $_SESSION['Items' . $identifier]->Branch . "'" . ',
				customerref = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->CustRef) . "'" . ',
                                custContact =  ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->CustRefVia) . "'" . ',
				comments = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->Comments) . "'" . ',
				ordertype = ' . "'" . $_SESSION['Items' . $identifier]->DefaultSalesType . "'" . ',
				shipvia = ' . $_POST['ShipVia'] . ',
				
				quotedate = ' . "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->QuoteDate)) . "'" . ',
				confirmeddate = ' . "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->ConfirmedDate)) . "'" . ',
				deliverto = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DeliverTo) . "'" . ',
				deladd1 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd1) . "'" . ',
				deladd2 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd2) . "'" . ',
				deladd3 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd3) . "'" . ',
				deladd4 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd4) . "'" . ',
				deladd5 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd5) . "'" . ',
				deladd6 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd6) . "'" . ',';
        if ($_POST['dateopt'] == "txtdate" && $_SESSION['Items' . $identifier]->inputedate != '') {
            $HeaderSQL .= ' ordernumofdays =' . $_SESSION['Items' . $identifier]->inputedate . ',';
        } else {
            //deliverydate = '."'" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->DeliveryDate)) . "'".',
            $HeaderSQL .= '  deliverydate = ' . "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->DeliveryDate)) . "'" . ',';
        }
        $HeaderSQL .=' contactphone = ' . "'" . $_SESSION['Items' . $identifier]->PhoneNo . "'" . ',
				contactemail = ' . "'" . $_SESSION['Items' . $identifier]->Email . "'" . ',
				freightcost = ' . $_SESSION['Items' . $identifier]->FreightCost . ',
				fromstkloc = ' . "'" . $_SESSION['Items' . $identifier]->Location . "'" . ',
				deliverydate = ' . "'" . $DelDate . "'" . ',
				quotedate = ' . "'" . $QuotDate . "'" . ',
				confirmeddate = ' . "'" . $ConfDate . "'" . ',
				printedpackingslip = ' . $_POST['ReprintPackingSlip'] . ',
				quotation = ' . $Qtation . ',
				deliverblind = ' . $_SESSION['Items' . $identifier]->DeliverBlind . '
			WHERE salesorders.orderno=' . "'" . $_SESSION['ExistingOrder'] . "'";

        $DbgMsg = _('The SQL that was used to update the order and failed was');
        $ErrMsg = _('The order cannot be updated because');
        $InsertQryResult = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);


        foreach ($_SESSION['Items' . $identifier]->LineItems as $StockItem) {

            /* Check to see if the quantity reduced to the same quantity
              as already invoiced - so should set the line to completed */
            if ($StockItem->Quantity == $StockItem->QtyInv) {
                $Completed = 1;
            } else { /* order line is not complete */
                $Completed = 0;
            }


            $Sql = "SELECT orderno, stkcode,orderlineno
                    FROM salesorderdetails
                    WHERE orderno='" . $_SESSION['ExistingOrder'] . "'
                    AND stkcode = '" . $StockItem->StockID . "'
                    AND orderlineno=" . $StockItem->LineNumber;

            $resul = DB_query($Sql, $db);
            $myrow = DB_fetch_array($resul);
            if ($myrow['stkcode'] == $StockItem->StockID && $myrow['orderlineno'] == $StockItem->LineNumber) {
                //echo $myrow[' stkcode'];
                $LineItemsSQL = 'UPDATE salesorderdetails SET
                                                                orderno =' . "'" . $OrderNs . "'" . ',
                                                                unitprice=' . $StockItem->Price . ',
								quantity=' . $StockItem->Quantity . ',
								discountpercent=' . floatval($StockItem->DiscountPercent) . ',
								completed=' . $Completed . ',
								poline=' . "'" . $StockItem->POLine . "'" . ',
								itemdue=' . "'" . FormatDateForSQL($StockItem->ItemDue) . "'" . ',
                                                                packdesc =' . "'" . $_POST['Pack' . $StockItem->LineNumber] . "'" . '
					WHERE salesorderdetails.orderno=' . "'" . $_SESSION['ExistingOrder'] . "'" . '
					AND salesorderdetails.orderlineno=' . "'" . $StockItem->LineNumber . "'";


                $DbgMsg = _('The SQL that was used to modify the order line and failed was');
                $ErrMsg = _('The updated order line cannot be modified because');
                $Upd_LineItemResult = DB_query($LineItemsSQL, $db, $ErrMsg, $DbgMsg, true);
                DB_free_result($Upd_LineItemResult);
            } else {
                $LineItems2 = 'INSERT INTO salesorderdetails (orderlineno,orderno,
											stkcode,
											unitprice,
											quantity,
											discountpercent,
											narrative,
											poline,
											itemdue,
											packdesc)
										VALUES ('
                        . $StockItem->LineNumber . ',
					' . "'" . $_SESSION['ExistingOrder'] . "'" . ',
					' . "'" . $StockItem->StockID . "'" . ',
					' . $StockItem->Price . ',
					' . $StockItem->Quantity . ',
					' . floatval($StockItem->DiscountPercent) . ',
					' . "'" . DB_escape_string($StockItem->Narrative) . "'" . ',
					' . "'" . $StockItem->POLine . "'" . ',
					' . "'" . FormatDateForSQL($StockItem->ItemDue) . "'" . ',
					' . "'" . $_POST['Pack' . $StockItem->LineNumber] . "'" . '
				)';

                $DbgMsg = _('The SQL that was used to modify the order line and failed was');
                $ErrMsg = _('The updated order line cannot be modified because');
                $INS_LineItemResult = DB_query($LineItems2, $db, $ErrMsg, $DbgMsg, true);
                DB_free_result($INS_LineItemResult);
            }
        } /* updated line items into sales order details */
        /* Conversion Counter..... */

//        $SQLCRE = " CREATE TABLE IF NOT EXISTS `quotationcoversion` (
//                      `conindex` int(11) NOT NULL AUTO_INCREMENT,
//                      `salesodersno` varchar(11) NOT NULL,
//                      `quotno` varchar(11) NOT NULL,
//                      `periodnum` smallint(6) default 0,
//                      `user` varchar(40) NOT NULL,
//                      `transdate` date NOT NULL default '0000-00-00',
//                      `transdatetime` datetime default NULL,
//          PRIMARY KEY  (`conindex`)
//        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
//
//        $Run = db_query($SQLCRE, $db, $ErrMsg);
        $PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
        $SQLIN = "INSERT INTO quotationcoversion(salesodersno,quotno,periodnum,transdate,transdatetime,user)
                                                           VALUES('" . $OrderNs . "',
                                                                  '" . $_SESSION['ExistingOrder'] . "',
                                                                  " . $PeriodNo . ",
                                                                  '" . Date('Y-m-d') . "',
                                                                  NOW(),
                                                                  '" . $_SESSION['UserID'] . "'
                                                                  )";


        $ErrMsg = _('Quotation  could not be converted because :');
        $QuotResult = db_query($SQLIN, $db, $ErrMsg);
        $Result = DB_Txn_Commit($db);

        prnMsg(_('Quotation  number ' . $_SESSION['ExistingOrder']) . ' Converted to: ' . $OrderNs . ' ' . _('updated'), 'success');

        echo '<br><div style="text-align:center"><a href="' . $rootpath . '/PrintCustOrder.php?' . SID . 'identifier=' . $identifier . '&TransNo=' . $OrderNs . '">' . _('Print packing slip - pre-printed stationery') . '</a>';
        if ($_SESSION['Items' . $identifier]->CashPaymentTerms == 'CA') {
            //$OrderNo
            echo '<p><img src="' . $rootpath . '/css/' . $theme . '/images/reports.png" title="' . _('Invoice') . '" alt="">' . ' ' . '<a href="' . $rootpath . '/CustomerReceiptTenderType.php?' . SID . 'Debtor=' . $_SESSION['Items' . $identifier]->DebtorNo . '&OrderNumber=' . $OrderNs . '">' . _('Confirm Dispatch and Produce Invoice') . '</a>';
        } else {
            echo '<p><img src="' . $rootpath . '/css/' . $theme . '/images/reports.png" title="' . _('Invoice') . '" alt=""><a href="' . $rootpath . '/ConfirmDispatch_Invoice.php?' . SID . 'identifier=' . $identifier . '&OrderNumber=' . $OrderNs . '">' . _('Confirm Order Delivery Quantities and Produce Invoice') . '</a>';
        }
        echo '<br><a  target="_blank" href="' . $rootpath . '/PrintCuttingSlip.php?' . SID . 'identifier=' . $identifier . '&ModifyOrderNumber=' . $OrderNs . '">'. _('Print cutting slip...') . '</a>';
        echo '<br><a  target="_blank" href="' . $rootpath . '/PrintCustOrder_generic.php?' . SID . 'identifier=' . $identifier . '&TransNo=' . $OrderNs . '">' . _('Print packing slip and delivery note') . ' (' . _('Laser') . ')' . '</a>';
        echo '<p><a href="' . $rootpath . '/SelectSalesOrder.php?' . SID . 'identifier=' . $identifier . '">' . _('Select A Different Order') . '</a></div>';
        include('includes/footer.inc');
        unset($_SESSION['Items' . $identifier]->LineItems);
        unset($_SESSION['Items' . $identifier]);
        exit;
    } elseif ($_POST['ConvertOrder'] == 0 && $_SESSION['Items' . $identifier]->Quotation == 1) {

        
        
         /*Avoid Error Messages/ Seems like our Quotation losses value*/
        if(empty($_SESSION['Items' . $identifier]->Quotation)){
            if(preg_match('/SO/', $_SESSION['ExistingOrder'])){
                $_SESSION['Items' . $identifier]->Quotation = 0;
            }elseif(preg_match('/QT/', $_SESSION['ExistingOrder'])){
                $_SESSION['Items' . $identifier]->Quotation= 1;


            }
        }//Okay...
        if (isset($_POST['CustOrderNum'])) {
           $_SESSION['Items' . $identifier]->CustRef = $_POST['CustOrderNum'];
           // echo "am here >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 1304..";
        }
        $HeaderSQL = 'UPDATE salesorders
			SET debtorno = ' . "'" . $_SESSION['Items' . $identifier]->DebtorNo . "'" . ',
				branchcode = ' . "'" . $_SESSION['Items' . $identifier]->Branch . "'" . ',
				customerref = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->CustRef) . "'" . ',
                                custContact =  ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->CustRefVia) . "'" . ',
				comments = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->Comments) . "'" . ',
				ordertype = ' . "'" . $_SESSION['Items' . $identifier]->DefaultSalesType . "'" . ',
				shipvia = ' . $_POST['ShipVia'] . ',
				
				quotedate = ' . "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->QuoteDate)) . "'" . ',
				confirmeddate = ' . "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->ConfirmedDate)) . "'" . ',
				deliverto = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DeliverTo) . "'" . ',
				deladd1 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd1) . "'" . ',
				deladd2 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd2) . "'" . ',
				deladd3 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd3) . "'" . ',
				deladd4 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd4) . "'" . ',
				deladd5 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd5) . "'" . ',
				deladd6 = ' . "'" . DB_escape_string($_SESSION['Items' . $identifier]->DelAdd6) . "'" . ',';

        if ($_POST['dateopt'] == "txtdate" && $_SESSION['Items' . $identifier]->inputedate != '') {
            $HeaderSQL .= ' ordernumofdays =' . $_SESSION['Items' . $identifier]->inputedate . ',';
        } else {
            $HeaderSQL .= '  deliverydate = ' . "'" . FormatDateForSQL(DB_escape_string($_SESSION['Items' . $identifier]->DeliveryDate)) . "'" . ',';
        }

        $HeaderSQL .= 'contactphone = ' . "'" . $_SESSION['Items' . $identifier]->PhoneNo . "'" . ',
				contactemail = ' . "'" . $_SESSION['Items' . $identifier]->Email . "'" . ',
				freightcost = ' . $_SESSION['Items' . $identifier]->FreightCost . ',
				fromstkloc = ' . "'" . $_SESSION['Items' . $identifier]->Location . "'" . ',
				deliverydate = ' . "'" . $DelDate . "'" . ',
				quotedate = ' . "'" . $QuotDate . "'" . ',
				confirmeddate = ' . "'" . $ConfDate . "'" . ',
				printedpackingslip = ' . $_POST['ReprintPackingSlip'] . ',
				quotation = ' . $_SESSION['Items' . $identifier]->Quotation . ',
				deliverblind = ' . $_SESSION['Items' . $identifier]->DeliverBlind . '
			WHERE salesorders.orderno=' . "'" . $_SESSION['ExistingOrder'] . "'";

        $DbgMsg = _('The SQL that was used to update the order and failed was');
        $ErrMsg = _('The order cannot be updated because');
        $InsertQryResult = DB_query($HeaderSQL, $db, $ErrMsg, $DbgMsg, true);






        foreach ($_SESSION['Items' . $identifier]->LineItems as $StockItem) {
            echo 2;
            /* Check to see if the quantity reduced to the same quantity
              as already invoiced - so should set the line to completed */
            if ($StockItem->Quantity == $StockItem->QtyInv) {
                $Completed = 1;
            } else { /* order line is not complete */
                $Completed = 0;
            }


            $Sql = "SELECT orderno, stkcode,orderlineno
                    FROM salesorderdetails
                    WHERE orderno='" . $_SESSION['ExistingOrder'] . "'
                    AND stkcode = '" . $StockItem->StockID . "'
                    AND orderlineno=" . $StockItem->LineNumber;

            $resul = DB_query($Sql, $db);
            $myrow = DB_fetch_array($resul);
            //echo "___".$_SESSION['ExistingOrder']."______".$StockItem->StockID."<br>";
            //echo "***".$myrow['stkcode'];
            if ($myrow['stkcode'] == $StockItem->StockID && $myrow['orderlineno'] == $StockItem->LineNumber) {
                //echo "<br>Inside__".$myrow[' stkcode']. "_<br>";
                $LineItemsSQL = 'UPDATE salesorderdetails SET

                                                                unitprice=' . $StockItem->Price . ',
								quantity=' . $StockItem->Quantity . ',
								discountpercent=' . floatval($StockItem->DiscountPercent) . ',
								completed=' . $Completed . ',
								poline=' . "'" . $StockItem->POLine . "'" . ',
								itemdue=' . "'" . FormatDateForSQL($StockItem->ItemDue) . "'" . ',
                                                                packdesc =' . "'" . $_POST['Pack' . $StockItem->LineNumber] . "'" . '
					WHERE salesorderdetails.orderno=' . "'" . $_SESSION['ExistingOrder'] . "'" . '
					AND salesorderdetails.orderlineno=' . "'" . $StockItem->LineNumber . "'";

                $DbgMsg = _('The SQL that was used to modify the order line and failed was');
                $ErrMsg = _('The updated order line cannot be modified because');
                $Upd_LineItemResult = DB_query($LineItemsSQL, $db, $ErrMsg, $DbgMsg, true);
            } else {
                $LineItems2 = 'INSERT INTO salesorderdetails (orderlineno,orderno,
											stkcode,
											unitprice,
											quantity,
											discountpercent,
											narrative,
											poline,
											itemdue,
											packdesc)
										VALUES ('
                        . $StockItem->LineNumber . ',
					' . "'" . $_SESSION['ExistingOrder'] . "'" . ',
					' . "'" . $StockItem->StockID . "'" . ',
					' . $StockItem->Price . ',
					' . $StockItem->Quantity . ',
					' . floatval($StockItem->DiscountPercent) . ',
					' . "'" . DB_escape_string($StockItem->Narrative) . "'" . ',
					' . "'" . $StockItem->POLine . "'" . ',
					' . "'" . FormatDateForSQL($StockItem->ItemDue) . "'" . ',
					' . "'" . $_POST['Pack' . $StockItem->LineNumber] . "'" . '
				)';
                $DbgMsg = _('The SQL that was used to modify the order line and failed was');
                $ErrMsg = _('The updated order line cannot be modified because');
                $INS_LineItemResult = DB_query($LineItems2, $db, $ErrMsg, $DbgMsg, true);
            }
        } /* updated line items into sales order details */

        $Result = DB_Txn_Commit($db);


        prnMsg(_('Ouotation number  ') . ' ' . $_SESSION['ExistingOrder'] . ' ' . _('has been Updated'), 'success');



        echo '<br><div style="text-align:center"><p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt=""><a href="' . $rootpath . '/PrintCustOrder.php?' . SID . 'identifier=' . $identifier . '&TransNo=' . $_SESSION['ExistingOrder'] . '">' . _('Print packing slip - pre-printed stationery') . '</a>';
//	if($_SESSION['Items' .$identifier]->CashPaymentTerms == 'CA')
//        {
//              echo '<p><img src="'.$rootpath.'/css/'.$theme.'/images/reports.png" title="' . _('Invoice') . '" alt="">' . ' ' . '<a href="' . $rootpath . '/CustomerReceiptTenderType.php?' . SID .'Debtor='. $_SESSION['Items'.$identifier]->DebtorNo. '&OrderNumber=' . $OrderNo .'">'. _('Confirm Dispatch and Produce Invoice') .'</a>';
//        }
//        else
//         {
//             echo '<p><img src="'.$rootpath.'/css/'.$theme.'/images/reports.png" title="' . _('Invoice') . '" alt=""><a href="' . $rootpath .'/ConfirmDispatch_Invoice.php?' . SID.'identifier='.$identifier  . '&OrderNumber=' . $OrderNs . '">'. _('Confirm Order Delivery Quantities and Produce Invoice') .'</a>';
//         }
        //echo '<br><a  target="_blank" href="' . $rootpath . '/PrintCustOrder_generic.php?' . SID . 'identifier=' . $identifier . '&TransNo=' . $_SESSION['ExistingOrder']  . '">' . _('Print packing slip and delivery note') . ' (' . _('Laser') . ')' . '</a>';
        echo '<div style="text-align:center"><p><img src="' . $rootpath . '/css/' . $theme . '/images/reports.png" title="' . _('Order') . '" alt="">' . ' ' . '<a href="' . $rootpath . '/PDFQuotation.php?' . SID . 'identifier=' . $identifier . '&QuotationNo=' . $_SESSION['ExistingOrder'] . '">' . _('Print Quotation') . '</a></div>';
        echo '<p><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Print') . '" alt=""><a href="' . $rootpath . '/SelectQuotations.php?' . SID . 'identifier=' . $identifier . '">' . _('Select A Different Quotation') . '</a></div>';
        include('includes/footer.inc');
        unset($_SESSION['Items' . $identifier]->LineItems);
        unset($_SESSION['Items' . $identifier]);
        exit;
    }
}

if (isset($_SESSION['Items' . $identifier]->SpecialInstructions) and strlen($_SESSION['Items' . $identifier]->SpecialInstructions) > 0) {
    prnMsg($_SESSION['Items' . $identifier]->SpecialInstructions, 'info');
}
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/inventory.png" title="' . _('Delivery') . '" alt="">' . ' ' . _('Delivery Details');
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="' . _('Customer') . '" alt="">' . ' ' . _('Customer Code') . ' :<b> ' . $_SESSION['Items' . $identifier]->DebtorNo;
echo '</b>&nbsp;' . _('Customer Name') . ' :<b> ' . $_SESSION['Items' . $identifier]->CustomerName . '</p>';
//echo '<font size=4><b>'. _('Customer') .' : ' . $_SESSION['Items'.$identifier]->CustomerName . '</b></font>';
echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . 'identifier=' . $identifier . '" method=post name="DeliveryForm" >';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


/* Display the order with or without discount depending on access level */
if (in_array(2, $_SESSION['AllowedPageSecurityTokens'])) {

    echo '<div class="centre"><b>';

    if ($_SESSION['Items' . $identifier]->Quotation == 1) {
        echo _('Quotation Summary');
    } else {
        echo _('Order Summary');
    }
    echo "<b>Is it a quotation or an order... </b>: " . $_SESSION['Items' . $identifier]->Quotation;
    echo '</b></div>
	<table cellpading=2 colspan=7 border=1>
	<Tr>
		<th>' . _('Item Code') . '</th>
		<th>' . _('Item Description') . '</th>
		<th>' . _('Quantity') . '</th>
		<th>' . _('Unit') . '</th>
		<th>' . _('Price') . '</th>
		<th>' . _('Discount') . ' %</th>
		<th>' . _('Total') . '</th>
		<th>' . _('Packing List') . '</th>
	</tr>';

    $_SESSION['Items' . $identifier]->total = 0;
    $_SESSION['Items' . $identifier]->totalVolume = 0;
    $_SESSION['Items' . $identifier]->totalWeight = 0;
    $k = 0; //row colour counter
    //print_r($_SESSION['Items'.$identifier]->LineItems);
    foreach ($_SESSION['Items' . $identifier]->LineItems as $StockItem) {

        $sql = 'SELECT packdesc FROM salesorderdetails 
                                        WHERE salesorderdetails.stkcode=' . "'" . $StockItem->StockID . "'" . '
					AND salesorderdetails.orderno=' . "'" . $_SESSION['ExistingOrder'] . "'";
        $Result = DB_query($sql, $db);
        $myrow = DB_fetch_array($Result);
        //$StockItem->Packdesc = $myrow['packdesc'];

        $LineTotal = $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
        $DisplayLineTotal = number_format($LineTotal, 2);
        $DisplayPrice = number_format($StockItem->Price, 2);
        $DisplayQuantity = $StockItem->Quantity;
        $DisplayDiscount = number_format(($StockItem->DiscountPercent * 100), 2);
        //echo "<br>Post pack is " . $_POST['Pack' . $StockItem->LineNumber] . "<br>";
        if ($StockItem->PackUnits != "" && $StockItem->Packsize != "" && !isset($_POST['Pack' . $StockItem->LineNumber])) {
            $StockItem->Packdesc = $StockItem->PackUnits . ' X ' . $StockItem->Packsize . 'm';
        } else if (isset($_POST['Pack' . $StockItem->LineNumber])) {
            $StockItem->Packdesc = $_POST['Pack' . $StockItem->LineNumber];
        } else {
            $StockItem->Packdesc = "";
        }




        if ($k == 1) {
            echo '<tr class="EvenTableRows">';
            $k = 0;
        } else {
            echo '<tr class="OddTableRows">';
            $k = 1;
        }
       // echo "<br>Pack desc is " . $StockItem->Packdesc. "<br>";
        //$_SESSION['Items'.$identifier]->PackDesc = 'Pack' . $StockItem->LineNumber;
        //<td>Packdescription:<input onfocus="setBKColor(event);" onblur="reSetBKColor(event);" type="text" name="Pack' . $StockItem->LineNumber . '" value = "' . $myrow['packdesc'] . '"></td>
        echo '<td>' . $StockItem->StockID . '</td>
			<td>' . $StockItem->ItemDescription . '</td>
			<td class=number>' . $DisplayQuantity . '</td>
			<td>' . $StockItem->Units . '</td>
			<td class=number>' . $DisplayPrice . '</td>
			<td class=number>' . $DisplayDiscount . '</td>
			<td class=number>' . $DisplayLineTotal . '</td>
			<td>Packdescription:<input onfocus="setBKColor(event);" onblur="reSetBKColor(event);" type="text" name="Pack' . $StockItem->LineNumber . '" value = "' .$StockItem->Packdesc . '"></td>
		</tr>';

        $_SESSION['Items' . $identifier]->total = $_SESSION['Items' . $identifier]->total + $LineTotal;
        $_SESSION['Items' . $identifier]->totalVolume = $_SESSION['Items' . $identifier]->totalVolume + ($StockItem->Quantity * $StockItem->Volume);
        $_SESSION['Items' . $identifier]->totalWeight = $_SESSION['Items' . $identifier]->totalWeight + ($StockItem->Quantity * $StockItem->Weight);
    }

    $DisplayTotal = number_format($_SESSION['Items' . $identifier]->total, 2);
    echo '<tr>
		<td colspan=6 align=right><b>' . _('TOTAL Excl Tax/Freight') . '</b></td>
		<td class=number>' . $DisplayTotal . '</td>
	</tr></table>';

    $DisplayVolume = number_format($_SESSION['Items' . $identifier]->totalVolume, 2);
    $DisplayWeight = number_format($_SESSION['Items' . $identifier]->totalWeight, 2);
    echo '<table border=1><tr>
		<td>' . _('Total Weight') . ':</td>
		<td>' . $DisplayWeight . '</td>
		<td>' . _('Total Volume') . ':</td>
		<td>' . $DisplayVolume . '</td>
	</tr></table>';
} else {

    /* Display the order without discount */

    echo '<div class="centre"><b>' . _('Order Summary') . '</b></div>
	<table cellpadding=2 colspan=7 border=1><tr>
		<th>' . _('Item Description') . '</th>
		<th>' . _('Quantity') . '</th>
		<th>' . _('Unit') . '</th>
		<th>' . _('Price') . '</th>
		<th>' . _('Total') . '</th>
	</tr>';

    $_SESSION['Items' . $identifier]->total = 0;
    $_SESSION['Items' . $identifier]->totalVolume = 0;
    $_SESSION['Items' . $identifier]->totalWeight = 0;
    $k = 0; // row colour counter
    foreach ($_SESSION['Items' . $identifier]->LineItems as $StockItem) {

        $LineTotal = $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
        $DisplayLineTotal = number_format($LineTotal, 2);
        $DisplayPrice = number_format($StockItem->Price, 2);
        $DisplayQuantity = number_format($StockItem->Quantity, $StockItem->DecimalPlaces);

        if ($k == 1) {
            echo '<tr class="OddTableRows">';
            $k = 0;
        } else {
            echo '<tr class="EvenTableRows">';
            $k = 1;
        }
        echo '<td>' . $StockItem->ItemDescription . '</td>
			<td class=number>' . $DisplayQuantity . '</td>
			<td>' . $StockItem->Units . '</td>
			<td class=number>' . $DisplayPrice . '</td>
			<td class=number>' . $DisplayLineTotal . '</font></td>
		</tr>';

        $_SESSION['Items' . $identifier]->total = $_SESSION['Items' . $identifier]->total + $LineTotal;
        $_SESSION['Items' . $identifier]->totalVolume = $_SESSION['Items' . $identifier]->totalVolume + $StockItem->Quantity * $StockItem->Volume;
        $_SESSION['Items' . $identifier]->totalWeight = $_SESSION['Items' . $identifier]->totalWeight + $StockItem->Quantity * $StockItem->Weight;
    }

    $DisplayTotal = number_format($_SESSION['Items' . $identifier]->total, 2);
    echo '<table><tr>
		<td>' . _('Total Weight') . ':</td>
		<td>' . $DisplayWeight . '</td>
		<td>' . _('Total Volume') . ':</td>
		<td>' . $DisplayVolume . '</td>
	</tr></table>';

    $DisplayVolume = number_format($_SESSION['Items' . $identifier]->totalVolume, 2);
    $DisplayWeight = number_format($_SESSION['Items' . $identifier]->totalWeight, 2);
    echo '<table border=1><tr>
		<td>' . _('Total Weight') . ':</td>
		<td>' . $DisplayWeight . '</td>
		<td>' . _('Total Volume') . ':</td>
		<td>' . $DisplayVolume . '</td>
	</tr></table>';
}
/* echo "line  1044".$_SESSION['ExistingOrder'];

  echo "...". $OrderNs; */
echo '<br><table><tr>
	<td>' . _('Deliver To') . ':</td>
	<td><input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type=text size=42 maxlength=40 name="DeliverTo" value="' . html_entity_decode($_SESSION['Items' . $identifier]->DeliverTo) . '"></td>
</tr>';

echo '<tr>
	<td>' . _('Deliver from the warehouse at') . ':</td>
	<td><select name="Location">';

if ($_SESSION['Items' . $identifier]->Location == '' OR !isset($_SESSION['Items' . $identifier]->Location)) {
    $_SESSION['Items' . $identifier]->Location = $DefaultStockLocation;
}

$ErrMsg = _('The stock locations could not be retrieved');
$DbgMsg = _('SQL used to retrieve the stock locations was') . ':';
$StkLocsResult = DB_query('SELECT locationname,loccode
					FROM locations', $db, $ErrMsg, $DbgMsg);

while ($myrow = DB_fetch_row($StkLocsResult)) {
    if ($_SESSION['Items' . $identifier]->Location == $myrow[1]) {
        echo '<option selected value="' . $myrow[1] . '">' . $myrow[0];
    } else {
        echo '<option value="' . $myrow[1] . '">' . $myrow[0];
    }
}

echo '</select></td></tr>';
if($_SESSION['ExistingOrder'] && $_SESSION['ExistingOrder'] != "" ){
// Set the default date to earliest possible date if not set already
if (!isset($_SESSION['Items' . $identifier]->DeliveryDate) && $_POST['dateopt'] == "seldate") {
//             echo '<link href="'.$rootpath.'/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
//             echo '<script src="'.$rootpath.'/facebox/src/facebox.js" type="text/javascript"></script>';
//             echo "<script>jQuery.facebox('Please Select estimated date.');</script>"; 
    // exit();
    // $InputErrors =1;
    $_SESSION['Items' . $identifier]->DeliveryDate = Date($_SESSION['DefaultDateFormat'], $EarliestDispatch);

    if ($_SESSION['Items' . $identifier]->DeliveryDate) {
        echo '<link href="' . $rootpath . '/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
        echo '<script src="' . $rootpath . '/facebox/src/facebox.js" type="text/javascript"></script>';
        echo "<script>jQuery.facebox('Are you sure about this date?');</script>";
    }
}
}
if (!isset($_POST['inputedate']) && $_POST['dateopt'] == "txtdate") {
    $_POST['inputedate'] = 7;
}
if (!isset($_SESSION['Items' . $identifier]->QuoteDate)) {
    $_SESSION['Items' . $identifier]->QuoteDate = Date($_SESSION['DefaultDateFormat'], $EarliestDispatch);
}
if (!isset($_SESSION['Items' . $identifier]->ConfirmedDate)) {
//             echo '<link href="'.$rootpath.'/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
//             echo '<script src="'.$rootpath.'/facebox/src/facebox.js" type="text/javascript"></script>';
//             echo "<script>jQuery.facebox('Please, enter Confirmed Order Date.');</script>"; 
    $_SESSION['Items' . $identifier]->ConfirmedDate = Date($_SESSION['DefaultDateFormat'], $EarliestDispatch);
}

/* No idea what this code does, but as it needs updating for other date formats
 * anyway I am commenting it out - Tim

  if($_SESSION['DefaultDateFormat']=='d/m/Y'){
  $jdf=0;
  } else {
  $jdf=1;
  }
 */
// The estimated Dispatch date or Delivery date for this order
echo '<tr>
	<td>' . _('Estimated Delivery Date') . ':</td>
	<td><input type="RADIO" name="dateopt" VALUE="txtdate" onclick="handleClick(this); " CHECKED>' . _('input no. of days ') . '<input type="text" size=5 maxlength=5 style="border:1px solid blue;"  name="inputedate" value="' . $_SESSION['Items' . $identifier]->inputedate . '"><input type="RADIO" name="dateopt" VALUE="seldate" onclick="handleClick(this);" >' . _('use date ') . '<input class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type="Text" size=15 maxlength=14 name="DeliveryDate" value="' . $_SESSION['Items' . $identifier]->DeliveryDate . '"><font color=red>*</font></td>
	</tr>';
// The date when a quote was issued to the customer
echo '<tr>
	<td>' . _('Quote Date') . ':</td>
	<td><input class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type="Text" size=15 maxlength=14 name="QuoteDate" value="' . $_SESSION['Items' . $identifier]->QuoteDate . '"></td>
	</tr>';
// The date when the customer confirmed their order
echo '<tr>
	<td>' . _('Confirmed Order Date') . ':</td>
	<td><input class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type="Text" size=15 maxlength=14 name="ConfirmedDate" value="' . $_SESSION['Items' . $identifier]->ConfirmedDate . '"></td>
	</tr>';

echo '<tr>
	<td>' . _('Delivery Address 1') . ':</td>
	<td><input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type=text size=42 maxlength=40 name="BrAdd1" value="' . $_SESSION['Items' . $identifier]->DelAdd1 . '"></td>
</tr>';

echo '<tr>
	<td>' . _('Delivery Address 2') . ':</td>
	<td><input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type=text size=42 maxlength=40 name="BrAdd2" value="' . $_SESSION['Items' . $identifier]->DelAdd2 . '"></td>
</tr>';

echo '<tr>
	<td>' . _('Delivery Address 3') . ':</td>
	<td><input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type=text size=42 maxlength=40 name="BrAdd3" value="' . $_SESSION['Items' . $identifier]->DelAdd3 . '"></td>
</tr>';

echo '<tr>
	<td>' . _('Delivery Address 4') . ':</td>
	<td><input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type=text size=42 maxlength=40 name="BrAdd4" value="' . $_SESSION['Items' . $identifier]->DelAdd4 . '"></td>
</tr>';

echo '<tr>
	<td>' . _('Delivery Address 5') . ':</td>
	<td><input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type=text size=42 maxlength=40 name="BrAdd5" value="' . $_SESSION['Items' . $identifier]->DelAdd5 . '"></td>
</tr>';

echo '<tr>
	<td>' . _('Delivery Address 6') . ':</td>
	<td><input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type=text size=42 maxlength=40 name="BrAdd6" value="' . $_SESSION['Items' . $identifier]->DelAdd6 . '"></td>
</tr>';

echo '<tr>
	<td>' . _('Contact Phone Number') . ':</td>
	<td><input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type=text size=25 maxlength=25 name="PhoneNo" value="' . $_SESSION['Items' . $identifier]->PhoneNo . '"></td>
</tr>';

echo '<tr><td>' . _('Contact Email') . ':</td><td><input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type=text size=40 maxlength=38 name="Email" value="' . $_SESSION['Items' . $identifier]->Email . '"></td></tr>';
echo '<tr>
	<td>' . _('Order Number') . ':</td>
	<td><input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type=text size=25 maxlength=30 name="CustOrderNum" value="' . $_SESSION['Items' . $identifier]->CustRef . '"><font color=red>*</td>
</tr>';
echo '<tr><td>' . _('Initial Contact via') . ':</td>
	<td><select name="Codes">';
//custContact =  '."'". $_SESSION['Items'.$identifier]->CustRefVia  ."'".',
echo "cust ref" . $_SESSION['Items' . $identifier]->CustRefVia;

$sql = 'SELECT  code,Description FROM custReferences';
$ErrMsg = _('Could not retrieve the values beacause: ');
$result = Db_query($sql, $db, $ErrMsg);
// echo "<option value='".$_SESSION['Items'.$identifier]->CustRefVia."'>" .$_SESSION['Items'.$identifier]->CustRefVia;

if (isset($_SESSION['Items' . $identifier]->CustRefVia) && $_SESSION['Items' . $identifier]->CustRefVia !== "") {
    echo '<option selected value="' . $_SESSION['Items' . $identifier]->CustRefVia . '">' . $_SESSION['Items' . $identifier]->CustRefVia;
} else {
    echo '<option value="none">' . _('--Select Contact Via--');
}
$SQLPAY = " SELECT paymentterms FROM  debtorsmaster
			WHERE debtorno ='" . $_SESSION['Items' . $identifier]->DebtorNo . "'";
$payresult = Db_query($SQLPAY, $db, $ErrMsg);
$payrow = DB_fetch_array($payresult);
if ($payrow['paymentterms'] == 'CA') {
    while ($myrow = DB_fetch_array($result)) {
//	 if(isset($_POST['Codes']) AND $_POST['Codes']== $myrow["code"]){
//		 echo "<option selected value='" . $myrow["Descriptione"]. "'>" .$myrow["Description"];
//                 //echo "<select name='Codesb'><option selected value='" . $myrow["AdvrtOn"] . "'>"  ;
//	} else {
//		echo "<option value='" . $myrow["Description"]."'>" . $myrow["Description"];
//                //$_POST['CustRef']= $myrow["code"] ;
//	}

        if (isset($_POST['Codes']) AND $_POST['Codes'] == $myrow["code"]) {
            echo "<option selected value='" . $myrow["Description"] . "'>" . $myrow["Description"];
            //echo "<select name='Codesb'><option selected value='" . $myrow["AdvrtOn"] . "'>"  ;
        } else {
            echo "<option value='" . $myrow["Description"] . "'>" . $myrow["Description"];
            //$_POST['CustRef']= $myrow["code"] ;
        }
    }
} else {

    echo "<option selected value='Repeat Customer'>Repeat Customer";
}
echo '</select></td></tr>';
//print_r($_SESSION['Items' .$identifier]) ;
echo '<tr><td>' . _('Comments ') . ':</td>
	<td><select name="comments" onChange="getCurrencyCode(this.value)">';
echo '<option value=0>' . _('--Comments--');
$sql = 'SELECT comment, cTitle FROM  Invoicedisplays';
$ErrMsg = _('Could not retrieve the values beacause: ');
$result = Db_query($sql, $db, $ErrMsg);
while ($myrow = DB_fetch_array($result)) {
    if (isset($_POST['comments']) AND $_POST['comments'] == $myrow["cTitle"]) {
        echo "<option selected value='" . $myrow["cTitlet"] . "'>" . $myrow["cTitle"];
    } else {
        echo "<option value='" . $myrow["cTitle"] . "'>" . $myrow["cTitle"];
    }
}

echo '</select></td></tr>';

/* echo'<tr><td><td><input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type=text size=25 maxlength=25 name="CustRef" value="' . $_SESSION['Items'.$identifier]->CustRef . '"></td>
  </td></td></tr>'; */

echo '<tr>
	<td></td>
	<td><textarea name="CommentsText" id ="Comments" cols=31 rows=5>' . html_entity_decode($_SESSION['Items' . $identifier]->Comments) . '</textarea></td>
</tr>';

/* This field will control whether or not to display the company logo and
  address on the packlist */

echo '<tr><td>' . _('Packlist Type') . ':</td><td><select name="DeliverBlind">';
for ($p = 1; $p <= 2; $p++) {
    echo '<option value=' . $p;
    if ($p == $_SESSION['Items' . $identifier]->DeliverBlind) {
        echo ' selected>';
    } else {
        echo '>';
    }
    switch ($p) {
        case 2:
            echo _('Hide Company Details/Logo');
            break;
        default:
            echo _('Show Company Details/Logo');
            break;
    }
}
echo '</select></td></tr>';

if (isset($_SESSION['PrintedPackingSlip']) and $_SESSION['PrintedPackingSlip'] == 1) {

    echo '<tr>
		<td>' . _('Reprint packing slip') . ':</td>
	<td><select name="ReprintPackingSlip">';
    echo '<option value=0>' . _('Yes');
    echo '<option selected value=1>' . _('No');
    echo '</select>	' . _('Last printed') . ': ' . ConvertSQLDate($_SESSION['DatePackingSlipPrinted']) . '</td></tr>';
} else {

    echo '<input type=hidden name="ReprintPackingSlip" value=0>';
}

echo '<tr><td>' . _('Charge Freight Cost inc tax') . ':</td>';
echo '<td><input onFocus="this.select();setBKColor(event);" onblur="reSetBKColor(event);" type=text class=number size=10 maxlength=12 name="FreightCost" VALUE=' . $_SESSION['Items' . $identifier]->FreightCost . '></td>';

if ($_SESSION['DoFreightCalc'] == true) {
    echo '<td><input type=submit name="Update" VALUE="' . _('Recalc Freight Cost') . '"></td></tr>';
}

if ((!isset($_POST['ShipVia']) OR $_POST['ShipVia'] == '') AND isset($_SESSION['Items' . $identifier]->ShipVia)) {
    $_POST['ShipVia'] = $_SESSION['Items' . $identifier]->ShipVia;
}

echo '<tr><td>' . _('Freight/Shipper Method') . ':</td><td><select name="ShipVia">';
$ErrMsg = _('The shipper details could not be retrieved');
$DbgMsg = _('SQL used to retrieve the shipper details was') . ':';
$sql = 'SELECT shipper_id, shippername
		FROM shippers';
$ShipperResults = DB_query($sql, $db, $ErrMsg, $DbgMsg);
while ($myrow = DB_fetch_array($ShipperResults)) {
    if ($myrow['shipper_id'] == $_POST['ShipVia']) {
        echo '<option selected value=' . $myrow['shipper_id'] . '>' . $myrow['shippername'];
    } else {
        echo '<option value=' . $myrow['shipper_id'] . '>' . $myrow['shippername'];
    }
}

echo '</select></td></tr>';

if ($_SESSION['ExistingOrder'] != "0") {
    if ($_SESSION['Items' . $identifier]->Quotation == 1) {
        $Convertname = 'Sales Orders';
    } else {
        $Convertname = 'Quotation';
    }
    echo '<tr><td>' . _('Convert order to ' . $Convertname) . ':</td><td><select name="ConvertOrder">';
    echo '<option  value=1>' . _('Yes');
    echo '<option selected value=0 >' . _('No');
    $naeme = $_POST["ConvertOrder"];
} else {

    echo '<tr><td>' . _('Quotation Only') . ':</td><td><select name="Quotation">';
    if ($_SESSION['Items' . $identifier]->Quotation == 1) {
        echo '<option selected value=1>' . _('Yes');
        echo '<option value=0>' . _('No');
        //$_SESSION['Items'.$identifier]->Quotation = 1;
    } else {
        echo '<option VALUE=1>' . _('Yes');
        echo '<option selected VALUE=0>' . _('No');
        $_SESSION['Items' . $identifier]->Quotation = 0;
    }
}


echo '</select></td></tr>';


echo '</table>';

echo '<br><div class="centre"><input type=submit name="BackToLineDetails" value="' . _('Modify Order Lines') . '">';

if ($_SESSION['ExistingOrder'] == "0") {
    //new one
    echo '<br><br><input type=submit name="ProcessOrder" value="' . _('Process Order') . '">';
    echo '<br><br><input type=submit name="MakeRecurringOrder" VALUE="' . _('Create Recurring Order') . '">';
} else if ($_SESSION['ExistingOrder'] != "0" && $_SESSION['Items' . $identifier]->Quotation == 0) {

    echo '<br><br><input type=submit name="ProcessOrder" value="' . _('Update/Proceed ') . '" onclick="return confirm(\'' . _('Are you sure you wish to continue ?') . '\');">';
    echo '<br><br><input type=submit name="MakeRecurringOrder" VALUE="' . _('Create Recurring Order') . '">';
} else {

    echo '<br><br><input type=submit name="ProcessOrder" VALUE="' . _('Update/Proceed') . '"onclick="return confirm(\'' . _('Are you sure you wish to continue ?') . '\');">';
    echo '<br><br><input type=submit name="MakeRecurringOrder" VALUE="' . _('Create Recurring Order') . '">';
}

echo '</div></form>';
//echo '<script type="text/javascript">defaultControl(document.DeliveryForm.CustCode);</script>';
echo '<script type="text/javascript" language="JavaScript">document.DeliveryForm.elements[0].focus();</script>';
include('includes/footer.inc');
?>
