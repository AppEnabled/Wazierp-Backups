<?php

if(isset($_POST['SaveChanges'])){

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

$sql = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				debtorsmaster.address5,
				debtorsmaster.address6,
				debtorsmaster.currcode,
				debtorsmaster.salestype,
				debtorsmaster.clientsince,
				debtorsmaster.holdreason,
				debtorsmaster.paymentterms,
				debtorsmaster.discount,
				debtorsmaster.discountcode,
				debtorsmaster.pymtdiscount,
				debtorsmaster.creditlimit,
				debtorsmaster.settlementdiscount,
				    debtorsmaster.basicrebate,
				    debtorsmaster.advertisingrebate,
					debtorsmaster.bulkrebate,
				debtorsmaster.invaddrbranch,
				debtorsmaster.taxref,
				debtorsmaster.customerpoline,
				debtorsmaster.typeid,
				debtorsmaster.statements,
				debtorsmaster.emailstatements,
				debtorsmaster.regnum,
				debtorsmaster.tel
				FROM debtorsmaster
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE debtorsmaster.typeid = debtortype.typeid
			AND custbranch.defaultlocation = '" . $_SESSION['UserStockLocation'] . "'
			GROUP BY debtorsmaster.debtorno
			";

$ErrMsg = _('The customer details could not be retrieved because');
$result = DB_query($sql,$db,$ErrMsg);


//$myrow = DB_fetch_array($result);

	/*show a table of the orders returned by the SQL */

	echo '<table >';
		$tableheader = "<tr>
				<th>" . _('Debtor NO') . "</th>
                <th>" . _('Name') . "</th>
				<th>" . _('paymentterms') . "</th>

				<th>" . _('Select') . "</th>

				</tr>";
	
	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter
    $Counter = 0;


	while ($myrow=DB_fetch_array($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

       $sqloption = "SELECT reasoncode, reasondescription FROM holdreasons";

        $resultop = DB_query( $sqloption , $db);
        $selectStatement ='<td align=right><select name="HoldReason[]" id="create-user">';
                            while($myrowoption = DB_fetch_array($resultop))     {
                                if($_POST['HoldReason']=$myrow['reasoncode']){
                                  $selectStatement .='<option selected value='.$myrow['reasoncode'] .'>'. $myrow['reasondescription'].'</option>';
                                }else{
                                    $selectStatement .='<option value='.$myrow['reasoncode'] .'>'. $myrow['reasondescription'].'</option>';
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
                    <td>%s</td>
				    <td>%s</td>
				    %s</tr>",

				$myrow['debtorno'],
                $myrow['name'],
                $myrow['paymentterms'],
				$PrintDispatchNote
                ,$selectStatement);

		$j++;
		If ($j == 12){
			$j=1;
			echo $tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop

	echo '</table>';

    //echo "<br /><div class='centre'><input type=Submit Name='SaveChanges' Value='" . _('Save Changes') . "'></div>";
?>
</form>

<?php //} //end StockID already selected

include('includes/footer.inc');
?>