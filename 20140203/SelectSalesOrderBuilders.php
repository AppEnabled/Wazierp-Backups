<style>
.closeto
{
    width:220px;
    padding:10px;
    border:1px solid gray;
    margin:0px;
    background-color:#3399FF;
}

.pastdelivery
{
    width:220px;
    padding:10px;
    border:1px solid gray;
    margin:0px;
    background-color:#FF9900;
}
.waypastdelivery
{
    width:220px;
    padding:10px;
    border:1px solid gray;
    margin:0px;
    background-color:#FF0000;
}

</style>

<?php

/* $Revision: 1.19 $ */

// $PageSecurity = 2;

include('includes/session.inc');
$title = _('Search Outstanding Sales Orders');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/sales.png" title="' . _('Sales') . '" alt="">' . ' ' . _('Outstanding Sales Orders') . '</p> ';


?>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

<style>

input.text { margin-bottom:12px; width:95%; padding: .4em; }
fieldset { padding:0; border:0; margin-top:25px; }
h1 { font-size: 1.2em; margin: .6em 0; }
div#users-contain { width: 350px; margin: 20px 0; }
div#users-contain table { margin: 1em 0; border-collapse: collapse; width: 100%; }
div#users-contain table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
.ui-dialog .ui-state-error { padding: .3em; }
.validateTips { border: 1px solid transparent; padding: 0.3em; }
</style>

<script>
    $(function() {
        var name = $( "#name" ),
            email = $( "#email" ),
            password = $( "#password" ),
            allFields = $( [] ).add( name ).add( email ).add( password ),
            tips = $( ".validateTips" );

        function updateTips( t ) {
            tips
                .text( t )
                .addClass( "ui-state-highlight" );
            setTimeout(function() {
                tips.removeClass( "ui-state-highlight", 1500 );
            }, 500 );
        }

        function checkLength( o, n, min, max ) {
            if ( o.val().length > max || o.val().length < min ) {
                o.addClass( "ui-state-error" );
                updateTips( "Not workings yet  " +
                    min + " and " + max + "." );
                return false;
            } else {
                return true;
            }
        }

        function checkRegexp( o, regexp, n ) {
            if ( !( regexp.test( o.val() ) ) ) {
                o.addClass( "ui-state-error" );
                updateTips( n );
                return false;
            } else {
                return true;
            }
        }

        $( "#dialog-form" ).dialog({
            autoOpen: false,
            height:400,
            width: 850,
            modal: true,
            buttons: {
                "Add Reason": function() {
                    var bValid = true;
                    allFields.removeClass( "ui-state-error" );

                    bValid = bValid && checkLength( name, "username", 3, 16 );
                    bValid = bValid && checkLength( email, "email", 6, 80 );
                    bValid = bValid && checkLength( password, "password", 5, 16 );

                    bValid = bValid && checkRegexp( name, /^[a-z]([0-9a-z_])+$/i, "Username may consist of a-z, 0-9, underscores, begin with a letter." );
                    // From jquery.validate.js (by joern), contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
                    bValid = bValid && checkRegexp( email, /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "eg. ui@jquery.com" );
                    bValid = bValid && checkRegexp( password, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );

                    if ( bValid ) {
                        $( "#users tbody" ).append( "<tr>" +
                            "<td>" + name.val() + "</td>" +
                            "<td>" + email.val() + "</td>" +
                            "<td>" + password.val() + "</td>" +
                            "</tr>" );
                        $( this ).dialog( "close" );
                    }
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            },
            close: function() {
                allFields.val( "" ).removeClass( "ui-state-error" );
            }
        });


             $("#create-user")

            .change(function() {
                     var value1 = ($('option:selected', this).val());
                     // alert(value1);
                     if ((value1 === "4")) {
                         // alert(value1);
                       //  $('.popup').show();
                         $( "#dialog-form" ).dialog( "open" );
                     }
                     //alert(value1);

            });

    });
</script>
<?php
//echo '<form action=' . $_SERVER['PHP_SELF'] .'?' .SID . ' method=post>';

if(isset($_POST['SaveChanges'])){

    /*
     * $SQL = " CREATE TABLE IF NOT EXISTS `OpenOrdersReasons` (
                      `salesindex` int(11) NOT NULL AUTO_INCREMENT,
                      `salesnotransno` varchar(11) NOT NULL,
                      `intransno` varchar(11) NOT NULL,
                      `reason` varchar(25) default '',
                      `comments` longblob,
                       `user` varchar(40) NOT NULL,
                      `transdate` date NOT NULL default '0000-00-00',
                      `transdatetime` datetime default NULL,

          PRIMARY KEY  (`salesindex`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
     */
  //  echo " am herre ";
  //  $SQL = "INSERT  INTO OpenOrdersReasons VALUES()"
//print_r($_POST['Commments']);

    $countCheck = count($_POST['Commments']);

    for ($i = 0; $i <= $countCheck; $i++) {
       // echo
         if(!empty($_POST['Commments'][$i])){
             $SQL = "SELECT * FROM OpenOrdersReasons WHERE salesnotransno ='".$_POST['SalesOrderNumber'][$i]."'
                                                         AND intransno='".$_POST['InvoiceNumber'][$i]."'";
             $ExistResult = DB_query($SQL, $db, $ErrMsg);
             if(DB_num_rows( $ExistResult)> 0){
                 //Update
                 $SQLTransferDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
                 $SQLUP = "UPDATE OpenOrdersReasons SET  reason=".$_POST['status'][$i].",
                                                         salesnotransno ='".$_POST['SalesOrderNumber'][$i]."',
                                                         intransno = '". $_POST['InvoiceNumber'][$i]."',
                                                         comments = '".$_POST['Commments'][$i]."',
                                                         user = '".$_SESSION['UserID']."',
                                                         transdate ='".$SQLTransferDate ."',
                                                        transdatetime =  NOW()
                            WHERE salesnotransno = '".$_POST['SalesOrderNumber'][$i]."'
                            AND  intransno = '". $_POST['InvoiceNumber'][$i]."'";
                 $Run = db_query($SQLUP, $db, $ErrMsg);
             }else{
                 //Its an insert

                 $SQLINS = "INSERT INTO OpenOrdersReasons(salesnotransno,intransno,reason,comments,user,transdate,transdatetime)VALUES('".$_POST['SalesOrderNumber'][$i]."',
                                                        '". $_POST['InvoiceNumber'][$i]."',
                                                          ".$_POST['status'][$i].",
                                                          '".$_POST['Commments'][$i]."',
                                                          '".$_SESSION['UserID']."',
                                                         '".$SQLTransferDate ."',
                                                           NOW())";
                          $Run = db_query($SQLINS , $db, $ErrMsg);
                // echo   $_POST['InvoiceNumber'][$i]  ."----".$_POST['SalesOrderNumber'][$i]." -Status- ".$_POST['status'][$i]."<br />";
             }

         }
    }
     //   $_SESSION['Transfers']->Add_list($_SESSION['Transfers']->LinesOnOrder, trim(strtoupper($_POST['StockIDN'][$i])), trim(strtoupper($_POST['description'][$i])), $_POST['Quantities'][$i], $_POST['units'][$i], $_POST['controlled'][$i], $_POST['serialised'][$i], $_POST['decimalplaces'][$i], $_POST['standardcost'][$i]);


    }
?>

<div id="dialog-form" title="Order Reason">
    <p class="validateTips">All form fields are required.</p>

    <form>
        <fieldset>
            <label for="name">Reason</label>

            <textarea  name="editreason" id="name" cols="100" rows='4' class="text ui-widget-content ui-corner-all"></textarea>
        </fieldset>
    </form>
</div>


<?php

//If (isset($_POST['ResetPart'])){
//     unset($SelectedStockItem );
//}Don't know about this.... Tamelo
$SQL = " CREATE TABLE IF NOT EXISTS `OpenOrdersReasons` (
                      `salesindex` int(11) NOT NULL AUTO_INCREMENT,
                      `salesnotransno` varchar(11) NOT NULL,
                      `intransno` varchar(11) NOT NULL,
                      `reason` varchar(25) default '',
                      `comments` longblob,
                       `user` varchar(40) NOT NULL,
                      `transdate` date NOT NULL default '0000-00-00',
                      `transdatetime` datetime default NULL,

          PRIMARY KEY  (`salesindex`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

$Run = db_query($SQL, $db, $ErrMsg);


if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem = $_POST['SelectedStockItem'];
} else {
	unset($SelectedStockItem);
}

if(isset($_GET['OrderNumber']))
{
    $SelectedOrderNumber = $_GET['OrderNumber'];
}elseif(isset($_POST['OrderNumber'])){
    
    $SelectedOrderNumber = $_POST['OrderNumber'];
}

if (isset($_GET['SelectedCustomer'])) {
	$SelectedCustomer = $_GET['SelectedCustomer'];
} elseif (isset($_POST['SelectedCustomer'])){
	$SelectedCustomer = $_POST['SelectedCustomer'];
} else {
	unset($SelectedCustomer);
}
echo '<p><div class="centre">';

If (isset($SelectedOrderNumber) AND $SelectedOrderNumber!='') {
	$SelectedOrderNumber = trim($SelectedOrderNumber);
	if (!is_numeric($SelectedOrderNumber)){
		  echo '<br><b>' . _('The Order Number entered MUST be numeric') . '</b><br>';
		  unset ($SelectedOrderNumber);
		  include('includes/footer.inc');
		  exit;
	} else {
		echo _('Order Number') . ' - ' . $SelectedOrderNumber;
	}
} else {
	If (isset($SelectedCustomer)) {
		echo _('For customer') . ': ' . $SelectedCustomer . ' ' . _('and') . ' ';
		echo "<input type=hidden name='SelectedCustomer' value=" . $SelectedCustomer . '>';
	}
	If (isset($SelectedStockItem )) {
		 echo _('for the part') . ': ' . $SelectedStockItem  . ' ' . _('and') . " <input type=hidden name='SelectedStockItem' value='" . $SelectedStockItem  . "'>";
	}
}

if (isset($_POST['SearchParts'])){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$i=0;
		$SearchString = '%';
		while (strpos($_POST['Keywords'], ' ', $i)) {
			$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
			$i=strpos($_POST['Keywords'],' ',$i) +1;
		}
		$SearchString = $SearchString . substr($_POST['Keywords'],$i).'%';

		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.description " . LIKE . " '" . $SearchString . "'
			AND stockmaster.categoryid='" . $_POST['StockCat']. "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif (isset($_POST['StockCode'])){
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				sum(locstock.quantity) as qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				sum(locstock.quantity) as qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.categoryid='" . $_POST['StockCat'] ."'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	 }

	$ErrMsg =  _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

}

if (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
}

if (!isset($_POST['TransAfterDate'])) {
    $_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m')-6,Date('d'),Date('Y')));
}


if (!isset($StockID)) {

     /* Not appropriate really to restrict search by date since may miss older
     ouststanding orders
	$OrdersAfterDate = Date('d/m/Y',Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
     */

	if (!isset($SelectedOrderNumber) or $SelectedOrderNumber==''){

		echo _('Order number') . ": <input type=text name='OrderNumber' maxlength=8 size=9>&nbsp " . _('From Stock Location') . ":<select name='StockLocation'> ";

		$sql = 'SELECT loccode, locationname FROM locations';

		$resultStkLocs = DB_query($sql,$db);

		while ($myrow=DB_fetch_array($resultStkLocs)){
			if (isset($_POST['StockLocation'])){
				if ($myrow['loccode'] == $_POST['StockLocation']){
				     echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
				} else {
				     echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
				}
			} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
				 echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			} else {
				 echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			}
	
		/*echo '</select> &nbsp&nbsp';
		echo '<select name="Quotations">';

		if ($_GET['Quotations']=='Quotes_Only'){
			$_POST['Quotations']='Quotes_Only';
		}

		if ($_POST['Quotations']=='Quotes_Only'){
			echo '<option selected VALUE="Quotes_Only">' . _('Quotations Only');
			echo '<option VALUE="Orders_Only">' . _('Orders Only');
		} else {
			echo '<option selected VALUE="Orders_Only">' . _('Orders Only');
			echo '<option VALUE="Quotes_Only">' . _('Quotations Only');
		}
*/
		
		
   // echo '&nbsp;&nbsp;<a href="' . $rootpath . '/SelectOrderItems.php?' . SID . '&NewOrder=Yes">' . _('Add Sales Order') . '</a>';
	}

        echo '</select> &nbsp&nbsp';
        echo "<input type=submit name='SearchOrders' VALUE='" . _('Search') . "'>";
echo '141<br>';
	$SQL='SELECT categoryid,
			categorydescription
		FROM stockcategory
		ORDER BY categorydescription';

	$result1 = DB_query($SQL,$db);

	echo '<hr>
		<font size=1>' . _('To search for sales orders for a specific part use the part selection facilities below') . "</font>     <input type=submit name='SearchParts' VALUE='" . _('Search Parts Now') . "'><input type=submit name='ResetPart' VALUE='" . _('Show All') . "'>
      </div><table>
      	<tr>
      		<td><font size=1>" . _('Select a stock category') . ":</font>
      			<select name='StockCat'>";

	while ($myrow1 = DB_fetch_array($result1)) {
		echo "<option VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	}

      echo '</select>
      		<td><font size=1>' . _('Enter text extract(s) in the description') . ":</font></td>
      		<td><input type='Text' name='Keywords' size=20 maxlength=25></td>
	</tr>
      	<tr><td></td>
      		<td><font SIZE 3><b>" . _('OR') . ' </b></font><font size=1>' . _('Enter extract of the Stock Code') . "</b>:</font></td>
      		<td><input type='Text' name='StockCode' size=15 maxlength=18></td>
      	</tr>
      </table>
      <hr>";
        }
    echo "<br><div class='centre'><form action='" . $_SERVER['PHP_SELF'] . "' method=post>";
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo _('Show all transactions after') . ": <input tabindex=1 type=text class='date' alt='".$_SESSION['DefaultDateFormat']."' id='datepicker' name='TransAfterDate' Value='" . $_POST['TransAfterDate'] . "' MAXLENGTH =10 size=12>" .
        "	<input tabindex=2 type=submit name='Refresh Inquiry' value='" . _('Refresh Inquiry') . "'></div><br>";

    $DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);
//echo 'StockItemsResult.....190<br>';
//echo "195 ".$_POST['StockLocation'];
echo "<br />
 <label class=\"closeto\">Sales Order Close to delivery Date</label>";
echo "<label class=\"pastdelivery\">Sales Order past delivery Date</label>";
echo "<label class=\"waypastdelivery\"> More than 120 days past delivery Date</label>";
echo "<i><b>Time taken to generate an invoice, its not outstanding salesorders</b></i>";
echo "<br /><br /><br />";
If (isset($StockItemsResult)) {

	echo '<table cellpadding=2 colspan=7 BORDER=2>';
	$TableHeader = "<tr>
				<th>" . _('Code') . "</th>
				<th>" . _('Description') . "</th>
				<th>" . _('On Hand') . "</th>
				<th>" . _('Units') . "</th>
			</tr>";
	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		printf("<td><input type=submit name='SelectedStockItem' VALUE='%s'</td>
			<td>%s</td>
			<td align=right>%s</td>
			<td>%s</td>
			</tr>",
			$myrow['stockid'],
			$myrow['description'],
			$myrow['qoh'],
			$myrow['units']);

		$j++;
		If ($j == 12){
			$j=1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}
//end if stock search results to show


	//figure out the SQL required from the inputs available
	
$Quotations =0;
	
	if(!isset($_POST['StockLocation'])) {
		$_POST['StockLocation'] = '';
	}

    $CurrentPeriod = GetPeriod(Date($_POST['TransAfterDate']),$db);
	if (isset($SelectedOrderNumber) && $SelectedOrderNumber !='') {
			$SQL = "SELECT salesorders.orderno,
					debtorsmaster.name,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto,
					salesorders.printedpackingslip,
					SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
					,debtortrans.transno
					,debtortrans.trandate
					, salesorders.OriginalOrderValue
				FROM salesorders,
					salesorderdetails,
					debtorsmaster,
					custbranch,
					debtortrans,
				WHERE salesorders.orderno = salesorderdetails.orderno
				salesorders.orderno = OpenOrdersReasons.salesnotransno
				AND salesorders.branchcode = custbranch.branchcode
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND debtorsmaster.debtorno = custbranch.debtorno
				AND debtortrans.debtorno = salesorders.debtorno
                AND debtortrans.order_ = salesorders.orderno
                                AND salesorders.flagged = 0
				AND salesorders.orderno=". $SelectedOrderNumber ."
				AND salesorders.quotation =" .$Quotations . "
				AND salesorders.debtorno = 'MM-BWH'
				AND debtortrans.prd >= ". $CurrentPeriod."
				GROUP BY salesorders.orderno,
					debtorsmaster.name,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto,
					salesorders.printedpackingslip
				ORDER BY salesorders.orderno";
	} else 

            if (isset($SelectedStockItem )) {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto,
					  salesorders.printedpackingslip,
						salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent) AS ordervalue
						,debtortrans.transno
						,debtortrans.trandate
						, salesorders.OriginalOrderValue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch,
						debtortrans
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND debtortrans.debtorno = salesorders.debtorno
                    AND debtortrans.order_ = salesorders.orderno
                                        AND salesorders.flagged = 0
					AND salesorders.quotation =" .$Quotations . "
					AND debtortrans.prd >= ". $CurrentPeriod."
					AND salesorders.debtorno = 'MM-BWH'
					AND salesorderdetails.stkcode='". $SelectedStockItem  ."'
					AND salesorders.debtorno='" . $SelectedCustomer ."'
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
					ORDER BY salesorders.orderno";


			} else if ($_POST['StockLocation']){
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
					  salesorders.printedpackingslip,
						salesorders.deliverydate, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
						,debtortrans.transno
						,debtortrans.trandate
						, salesorders.OriginalOrderValue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch,
						debtortrans
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
				    AND debtortrans.debtorno = salesorders.debtorno
                                        AND salesorders.flagged = 0
					AND salesorders.quotation =" .$Quotations . "
					AND debtortrans.prd >= ". $CurrentPeriod."
                    AND debtortrans.order_ = salesorders.orderno
					AND salesorders.debtorno = 'MM-BWH'
					AND salesorders.debtorno='" . $SelectedCustomer . "'
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						salesorders.debtorno,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate
					ORDER BY salesorders.orderno";

			
		} else if (isset($SelectedStockItem )) {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
					  	salesorders.printedpackingslip,
						salesorders.deliverydate, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
						,debtortrans.transno
						,debtortrans.trandate
						, salesorders.OriginalOrderValue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch,
						debtortrans
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND debtortrans.debtorno = salesorders.debtorno
                    AND debtortrans.order_ = salesorders.orderno
                                        AND salesorders.flagged = 0
					AND salesorders.quotation =" .$Quotations . "
					AND salesorders.debtorno = 'MM-BWH'
					AND debtortrans.prd >= ". $CurrentPeriod."
					AND salesorderdetails.stkcode='". $SelectedStockItem  . "'
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip
					ORDER BY salesorders.orderno";
                }
                if($_POST['StockLocation'] && $_POST['SearchOrders'])
                {
                    echo " ".$_POST['StockLocation'];
                    $SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
					  salesorders.printedpackingslip,
						SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
						,debtortrans.transno
						,debtortrans.trandate
						, salesorders.OriginalOrderValue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch,
						debtortrans
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND debtortrans.debtorno = salesorders.debtorno
                                        AND salesorders.flagged = 0
                    AND debtortrans.order_ = salesorders.orderno
					AND salesorders.debtorno = 'MM-BWH'
					AND debtortrans.prd >= ". $CurrentPeriod."
					AND salesorders.quotation =" .$Quotations . "
					AND salesorders.fromstkloc = '". $_POST['StockLocation']. "'
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip
					ORDER BY salesorders.orderno";
                }


			 else {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
					  salesorders.printedpackingslip,
						SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
						,debtortrans.transno
						,debtortrans.trandate
						, salesorders.OriginalOrderValue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch,
						debtortrans
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND debtortrans.debtorno = salesorders.debtorno
                                        AND salesorders.flagged = 0
					AND debtortrans.order_ = salesorders.orderno
					AND salesorders.debtorno = 'MM-BWH'
					AND salesorders.quotation =" .$Quotations . "
					AND debtortrans.prd >= ". $CurrentPeriod."
					AND salesorders.fromstkloc = '". $_SESSION['UserStockLocation']. "'
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip
					ORDER BY salesorders.orderno";
			}

		//} //end selected customer
	//} //end not order number selected

	$ErrMsg = _('No orders or quotations were returned by the SQL because');
	$SalesOrdersResult = DB_query($SQL,$db,$ErrMsg);

	/*show a table of the orders returned by the SQL */

	echo '<table >';
    //<th>" . _('Invoice') . "</th>
 /*   <th>" . _('Cutting-Slip') . "</th>
				<th>" . _('Customer') . "</th>
	<th>" . _('Delivery To') . "</th>*/
		$tableheader = "<tr>
				<th>" . _('Modify') . "</th>
                <th>" . _('Invoice') . "</th>
				<th>" . _('Disp. Note') . "</th>

				<th>" . _('Branch') . "</th>
				<th>" . _('Cust Order') . " #</th>
				<th>" . _('Order Date') . "</th>
				<th>" . _('Req Del Date') . "</th>
               <th>" . _('Inv Date') . "</th>
				<th>" . _('PO Value') . "</th>
				<th>" . _('Invoiced Amount') . "</th>
				<th>" . _('Variance Amount') . "</th>
				<th></th>
				<th>" . _('Notes') . "</th>
				</tr>";
	
	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter
    $Counter = 0;


	while ($myrow=DB_fetch_array($SalesOrdersResult)) {
                $Highlighter = '';
                 $diff = 0;
                 $days =  0;
                 $years  = 0;
                 $months = 0;
                 $FormatedDelDate  = '';
                 $trDate = '';
        $VarianceAmount = 0;
        //$selectStatement = '';
                 
                $todaysDate = str_replace("/", "-",Date($_SESSION['DefaultDateFormat']));
               $Datt = explode("-", $todaysDate);
                                $DB1 = $Datt['0'];
                                $DB2 = $Datt['1'];
                                $DB3 = $Datt['2'];
                $trDate = $DB3."-".$DB2."-".$DB1;
                $FormatedDelDate = ConvertSQLDate($myrow['deliverydate']);
		$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
        $FormatedInvDate = ConvertSQLDate($myrow['trandate']);
                //echo   "today ". $trDate ."Order ".$myrow['deliverydate'] ;
                
                //$diff =  strtotime($trDate) - strtotime($myrow['deliverydate']) ;
        $diff =  strtotime($myrow['trandate']) - strtotime($myrow['deliverydate']) ;
//                $years = floor($diff / (365*60*60*24));
//                $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
//                $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
       
            
               // $days = floor($diff / (60 * 60 * 24));
//                $ts1 = strtotime($date1);
//                $ts2 = strtotime($date2);
//
//                $seconds_diff = $ts2 - $ts1;
//
               $days = floor($diff/3600/24);
                //$days = floor($diff / (60 * 60 * 24));
                 //echo " difference".floor($diff/3600/24)."<--->".floor($diff / (60 * 60 * 24))." :Difference two = ". $days."difference ".$diff."<br /> " ;
                 
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
                if($days > 120){
                    
                    $Highlighter = '<tr style="background-color:#FF0000;">';
                   // $Highlighter = '<tr style="background-color:#FF9900;">';    
                }elseif( $days > 0){
                     //$Highlighter = '<tr style="background-color:#66FF00;">';
                    $Highlighter = '<tr style="background-color:#FF9900;">';  
                    
                }elseif($days <= 0 && $days > -5){
                    
                    $Highlighter= '<tr style="background-color:#3399FF;">';
                }
                echo $Highlighter;
		$ModifyPage = $rootpath . "/SelectOrderItems.php?" . SID . '&ModifyOrderNumber=' . $myrow['orderno'];
		//$Confirm_Invoice = $rootpath . '/ConfirmDispatch_Invoice.php?' . SID . '&OrderNumber=' .$myrow['orderno'];
        $Confirm_Invoice = $rootpath .'/PrintCustTrans.php?FromTransNo='. $myrow['transno'].'&InvOrCredit=Invoice';
                /*Corrine Request*/
		//if ($_SESSION['PackNoteFormat']==1){ /*Laser printed A4 default */
			//$PrintDispatchNote = $rootpath . '/PrintCustOrder_generic.php?' . SID . '&TransNo=' . $myrow['orderno'];
		//} else { /*pre-printed stationery default */
			$PrintDispatchNote = $rootpath . '/PrintCustOrder.php?' . SID . '&TransNo=' . $myrow['orderno'];
		//}
		$PrintCuttingSlip = $rootpath . '/PrintCuttingSlip.php?' . SID . '&ModifyOrderNumber=' . $myrow['orderno'];
		
		$FormatedOrderValue = number_format($myrow['ordervalue'],2);
        $FormatedOriginalValue = number_format($myrow['OriginalOrderValue'],2);
        $VarianceAmount = number_format(($myrow['OriginalOrderValue'] - $myrow['ordervalue']),2);

		if ($myrow['printedpackingslip']==0) {
		  $PrintText = _('Print');
		} else {
		  $PrintText = _('Reprint');
		}
       // echo '<td><select name=status>';
        /*$names = array("", "Promising", "Won", "Lost");
        foreach ($names as $key => $val) {
            if ($key == 0)
                continue;
            if ($key == $myrow['status']) {
                echo '<option selected value="' . $key . '">' . $val . '</option>';
            } else {
                echo '<option value="' . $key . '">' . $val . '</option>';
            }
        }*/


        $SQLReason = "SELECT * FROM OpenOrdersReasons WHERE salesnotransno = '".$myrow['orderno']."'";
        $resultreason = DB_query( $SQLReason, $db);
        $myrowreason= DB_fetch_array($resultreason);
     /*   (
                      `salesindex` int(11) NOT NULL AUTO_INCREMENT,
                      `salesnotransno` varchar(11) NOT NULL,
                      `intransno` varchar(11) NOT NULL,
                      `reason` varchar(5) default '',
                      `comments` longblob,
                       `user` varchar(40) NOT NULL,
                      `transdate` date NOT NULL default '0000-00-00',
                      `transdatetime` datetime default NULL,*/
                           /* <option value="1">No stock</option>
                            <option value="2">Delay</option>
                            <option value="3">Lost</option>
                            <option value="4">Other</option>*/
     $sqloption = "SELECT *
		        FROM OpenOrderTypes

		        ";

        $resultop = DB_query( $sqloption , $db);
        $selectStatement ='<td align=right><select name="status[]" id="create-user">';
                            while($myrowoption = DB_fetch_array($resultop))     {
                                if($myrowreason['reason']==$myrowoption['typeindex']){
                                  $selectStatement .='<option selected value='.$myrowoption['typeindex'].'>'.$myrowoption['Reason_type'].'</option>';
                                }else{
                                    $selectStatement .='<option value='.$myrowoption['typeindex'].'>'.$myrowoption['Reason_type'].'</option>';
                                }
                            }

        $selectStatement .= '</select></td>';

        //<textarea  name='editreason_'".$Counter." cols='100' rows='2'></textarea>
      //  echo ' </select></td>';
		//<td><a href='%s'>" . _('Invoice') . "</a></td>
        //$Confirm_Invoice,
       echo' <input type=hidden name=InvoiceNumber[] value="' . $myrow['transno'] . '">
                    <input type=hidden name=SalesOrderNumber[] value="' . $myrow['orderno'] . '">
                    ';
			printf("<td><b>%s</b></td>
                    <td><a target='_blank' href='%s'>%s</a></td>
				<td><a target='_blank' href='%s'>" . $PrintText . "</a></td>

				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>


                <td>%s</td>
                <td align=right>%s</td>
				<td align=right>%s</td>
				<td align=right>%s</td>
				%s
				<td><textarea name='Commments[]' cols='100' rows='2'>%s</textarea></td>
				</tr>",

				$myrow['orderno'],
                $Confirm_Invoice,
                $myrow['transno'],
				$PrintDispatchNote,


				$myrow['brname'],
				$myrow['customerref'],
				$FormatedOrderDate,
				$FormatedDelDate,
                $FormatedInvDate,
                $FormatedOriginalValue ,
				$FormatedOrderValue,$VarianceAmount,$selectStatement,$myrowreason['comments']);
      /*  $_SESSION['myArray'][$myrow['orderlineno']]['stockid'] = $myrow['stkcode'];
        $_SESSION['myArray'][$myrow['orderlineno']]['description'] = $myrow['description'];
        $_SESSION['myArray'][$myrow['orderlineno']]['itemqty'] = $myrow['quantity'];
        $_SESSION['myArray'][$myrow['orderlineno']]['packdescript'] = $myrow['packdesc'];
        $_SESSION['myArray'][$myrow['orderlineno']]['cutslipno'] = $myrow['cutslipno'];
        $_SESSION['myArray'][$myrow['orderlineno']]['OrderNo'] = $myrow['orderno'];*/
        //<textarea name="AuthorizationCommment" cols="100" rows="3"></textarea>
        //<td><a href='%s'>" . _('Cutting-Slip') . "</a></td>
        //$PrintCuttingSlip,
        //<td>%s</td>
        //$myrow['name'],
		//$myrow['deliverto'],
        $Counter++;
		$j++;
		If ($j == 12){
			$j=1;
			echo $tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop

	echo '</table>';

    echo "<br /><div class='centre'><input type=Submit Name='SaveChanges' Value='" . _('Save Changes') . "'></div>";
?>
</form>

<?php } //end StockID already selected

include('includes/footer.inc');
?>