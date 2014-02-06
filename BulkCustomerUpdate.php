<?php
// $PageSecurity = 36;

include('includes/session.inc');
$title = _('Update Customers');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/sales.png" title="' . _('Sales') . '" alt="">' . ' ' . _('Bulk customer Updates') . '</p> ';

if(isset($_POST['SaveChanges'])){

    $countCheck = count($_POST['HoldReason']);

    for ($i = 0; $i <= $countCheck; $i++) {


        if(!empty($_POST['SelectedDebtorNo'][$i])){
            if(isset($_POST['HoldReason'][$i])){

            $sql = "UPDATE debtorsmaster SET
                        holdreason='" . $_POST['HoldReason'][$i]. "'
                      WHERE debtorno = '" . $_POST['SelectedDebtorNo'][$i] . "'";
               // echo  $sql ."<br />";
                $ExistResult = DB_query( $sql, $db, $ErrMsg);
                prnMsg( _('Customer '.$_POST['SelectedDebtorNo'][$i].', holdreason '.$_POST['HoldReason'][$i].' updated'),'success');
            }

             //
        }


  }

    unset($_POST['SaveChanges']);
}

echo "<br><div class='centre'><form action='" . $_SERVER['PHP_SELF'] . "' method=post>";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';



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
                            while($myrowhold = DB_fetch_array($resultop))     {
                                if($myrow['holdreason']==$myrowhold['reasoncode']){
                                  $selectStatement .='<option selected value='.$myrowhold['reasoncode'] .'>'. $myrowhold['reasondescription'].'</option>';
                                }else{
                                    $selectStatement .='<option value='.$myrowhold['reasoncode'] .'>'. $myrowhold['reasondescription'].'</option>';
                                }
                            }

        $selectStatement .= '</select></td>';

       echo' <input type=hidden name=SelectedDebtorNo[] value="' . $myrow['debtorno'] . '">
                    <input type=hidden name=SalesOrderNumber[] value="' . $myrow['orderno'] . '">
                    ';
			printf("<td><b>%s</b></td>
                    <td>%s</td>
				    <td>%s</td>
				    %s</tr>",

				$myrow['debtorno'],
                $myrow['name'],
                $myrow['paymentterms']
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

    echo "<br /><div class='centre'><input type=Submit Name='SaveChanges' Value='" . _('Save Changes') . "'></div>";
?>
</form>

<?php //} //end StockID already selected

include('includes/footer.inc');
?>