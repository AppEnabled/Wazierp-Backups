<?php
/*Douglas : Consolidation
 *        : CSV
 *        : Negative Stock
 * 2012/04/13
 */
// $PageSecurity = 11;
session_set_cookie_params(3600 * 24 * 7);
include('includes/DefinePOIClass.php');
include('includes/SortArrayClass.php');
include('includes/session.inc');
$title = _('Stock Request Reprint');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

//$_POST['ConsoleLocationcs'] = "";
if ($_POST['FromStockLocation']=="") {
	$_POST['GetStockLocationcs'] = $_SESSION['UserStockLocation'];
}
 else {
  $_POST['GetStockLocationcs'] = $_POST['FromStockLocation'];
 // $_POST['ConsoleLocationcs'] = $_POST['FromStockLocation'];
}

////////////////////////////csv/////////////
function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(",", ".", $str);
}
//echo "<br>St num is " . $_SESSION['SKNumber'] . "<br>";
$ErrMsg = _('The SQL to get the stock quantities failed with the message');

$sqlHeader = "SELECT stockrequest.transno
					FROM stockrequest
					INNER JOIN stockmaster
					ON stockrequest.code = stockmaster.stockid
					WHERE stockrequest.loccode = '" . $_POST['GetStockLocationcs'] . "'
					AND (stockrequest.dispatched = 'N'
					OR stockrequest.received = 'N' OR stockrequest.pickingslip = 'N'
                                        )
					 GROUP BY  stockrequest.transno";
                               

               	
$resultHeader = DB_query($sqlHeader, $db, $ErrMsg);

if (!file_exists($_SESSION['reports_dir'])){
	$Result = mkdir('./' . $_SESSION['reports_dir']);
}

$filename = $_SESSION['reports_dir'] . '/StockRequests.csv';

$fp = fopen($filename,"w");
if ($fp==FALSE){

	prnMsg(_('Could not open or create the file under') . ' ' . $_SESSION['reports_dir'] . '/StockRequests.csv','error');
	include('includes/footer.inc');
	exit;
}

/** Export data to a csv **/
$HeaderInitial = "";
while($Header = DB_fetch_array($resultHeader))
{

    //fputs($fp, $lineHeader. "\n");
    $lineHeader = $Header['transno'];
    fputs($fp,"\n". $lineHeader. "\n");
    
    if($lineHeader != $HeaderInitial)
    {
    $HeaderInitial = $Header['transno'];
    $sql="SELECT stockrequest.transno,
					stockrequest.code,
                                        stockmaster.description,
					stockrequest.quantity,
                                        (stockrequest.quantity * stockmaster.materialcost)  as value,
                                        stockmaster.units,
                                        stockmaster.packsize,
                                        stockrequest.loccode,
					stockrequest.qtydispatched,
					stockrequest.dispatched,
					stockrequest.dispatchedfrom,
					stockrequest.received,
					stockmaster.stockid,
					stockmaster.kgs
					FROM stockrequest
					INNER JOIN stockmaster
					ON stockrequest.code = stockmaster.stockid
					WHERE stockrequest.transno = '" . $Header['transno'] . "'
					AND (stockrequest.dispatched = 'N'
					OR stockrequest.received = 'N' OR stockrequest.pickingslip = 'N'
                                        )
					ORDER BY stockrequest.code";
    }
            $result = DB_query($sql, $db, $ErrMsg);
//. ', ' . stripcomma($myrow[7]) . ', ' . stripcomma($myrow[8]) . ', ' . stripcomma($myrow[9])
$line = 'Trans-Number, Stockid, Descroption, Quantity,Value, Units, PackSize, Request From';
fputs($fp, $line . "\n");
$total = 0;
While ($myrow = DB_fetch_row($result)){
	$line = stripcomma($myrow[0]) . ', ' . stripcomma($myrow[1]) . ', ' . stripcomma($myrow[2]) . ', ' . stripcomma($myrow[3]) . ', ' . stripcomma($myrow[4]) . ', ' . stripcomma($myrow[5]) . ', ' . stripcomma($myrow[6]) . ', ' . stripcomma($myrow[7]);
	fputs($fp, $line . "\n");
        $total +=$myrow[4];
}
$line = ','.','.','.','.$total . ',';
fputs($fp, $line . "\n");

}
fclose($fp);
/*--End Export of CSV--*/
 /*

//////////////////////////////////////////////////////////////////////////////////////////////////////
//if ($_POST['FromStockLocation']==" ") {
	//$_POST['GetStockLocationcs'] = $_SESSION['UserStockLocation'];
//}
 //else {
  //$_POST['GetStockLocationcs'] = $_POST['FromStockLocation'];
//}
 * *
 */
  if($_POST['Consolidate'])
    {
                $ConsolationNo = "";
               // echo  $StockOrder.'<br>';
                $DuplicateCheck = count($_POST['transNo']);
                 $StockTransNo = $_POST['transNo'];
                 if($DuplicateCheck ==1 )
                 {
                     prnMsg(_('Sorry, can only consolidated more than on stockrequest '  ),'information');  
                     exit();
                 }
                for($i=0;$i<$DuplicateCheck;$i++) {
                $sql ="SELECT stockrequest.transno,
                                        stockrequest.consolidated,
                                        stockrequest.dispatched,
                                        stockrequest.received,
                                        stockrequest.loccode,
                                        stockrequest.pickingslip
					FROM stockrequest
					INNER JOIN stockmaster
					ON stockrequest.code = stockmaster.stockid
					WHERE stockrequest.transno ='".$StockTransNo[$i]. "'
                                        GROUP BY stockrequest.transno";
                                        $result = DB_query($sql, $db, $ErrMsg);
                                        $myrow = DB_fetch_array($result);
                                       
                                        if($myrow['dispatched'] == "Y" ||$myrow['pickingslip']=="Y" 
                                                || $myrow['received']=="Y")
                                        {
                                           prnMsg(_('Sorry, stockrequest number: '.$StockTransNo[$i] .', Cannot be consolidated '  ),'information');  
                                           exit();
                                        }
                                        /*Check if locations are the same */
                                        if($myrow['loccode']!=$_SESSION['consolelocation'])
                                        {
                                           prnMsg(_('Sorry, stockrequest number: '.$StockTransNo[$i] .', have location :'.$myrow['loccode'].', Consolidation Location :'.$_SESSION['consolelocation'].' '  ),'information');  
                                           exit();
                                        }
                                        if($myrow['consolidated']=="Y")
                                        {
                                            continue; 
                                        }
                                        else{
                                             $TypeNoField = "typeno" . $_SESSION['consolelocation'];
                                             $ConsolationNo =  $_SESSION['consolelocation']. GetNextTransNo(52, $db, $TypeNoField);
                                        }
                }
                //$checkbox = $_POST['RowSelect'];
                if($ConsolationNo != ""){
                        $countCheck = count($_POST['transNo']);
                         $StockOrder = $_POST['transNo'];
                         
                         for($i=0;$i<$countCheck;$i++) {
                            $del_id = $checkbox[$i];
                            $sqlHeader ="SELECT stockrequest.transno,
                                        stockrequest.quantity,
                                        stockrequest.code,
                                        stockrequest.loccode
					FROM stockrequest
					INNER JOIN stockmaster
					ON stockrequest.code = stockmaster.stockid
					WHERE stockrequest.transno  ='".$StockOrder[$i]. "'
                                        AND (stockrequest.dispatched = 'N'
					OR stockrequest.received = 'N' OR stockrequest.pickingslip = 'N'
                                        )
				       ";
                            echo " ".$sqlHeader."<br />";
                                    $resultHeader = DB_query($sqlHeader, $db, $ErrMsg);
                              //GROUP BY  stockrequest.transno
                $HeaderInitial = "";
                while($Header = DB_fetch_array($resultHeader))
                {
                  echo "<b>transno".$Header['transno'] ."Header ".$Header['code']  ." lines ".$Header['code']  ."</><br />";
                  //$lineHeader = $Header['transno'];
                  //if($lineHeader != $HeaderInitial)
                 // {
                              $HeaderInitial = $Header['transno'];
                              $sqlCheck = "SELECT stockrequest.transno,
                                            stockrequest.quantity,
                                            stockrequest.code
                                            FROM stockrequest
                                            WHERE stockrequest.transno <>'".$Header['transno']. "'
                                             AND stockrequest.loccode = '".$Header['loccode']. "'
                                             AND (stockrequest.dispatched = 'N'
					     OR stockrequest.received = 'N' OR stockrequest.pickingslip = 'N'
                                        )";
                             
                               $resultCheck = DB_query($sqlCheck, $db, $ErrMsg);
                           // if($Header['code'] != )AND stockrequest.code ='".$Header['code']. "'&& $StockOrder[$i] == $lineHeader
                               // $NumberCheck = "";
                                while($RowCheck = DB_fetch_array($resultCheck))
                                {
                                 
                                   // $Check = $RowCheck['code'];
                                    
                                   // if($Check != $NumberCheck )
                                   // {
                                    // $NumberCheck = $RowCheck['code'];
                                    if($RowCheck['code'] == $Header['code'])
                                    {
                                          $SQL = 'Update stockrequest
                                            SET  quantity = quantity +'.$RowCheck['quantity'] .',
                                            consolno = "'.$ConsolationNo .'",
                                            consolidated = "Y"
                                            WHERE transno = "'.$Header['transno']. '"
                                            AND stockrequest.code = "'.$Header['code']. '"';
                                            $ErrMsg = _('The order could not be Updated Because :');
                                            $AllwResult = DB_query($SQL, $db, $ErrMsg);
                                            echo "Header ".$Header['code']  ." lines ".$RowCheck['code']  ."<br />";
                                            echo $SQL ."<br />";
                                        //Now delete duplicates
                                            $SQLD = 'DELETE 
                                                    FROM stockrequest
                                            WHERE stockrequest.transno = "'.$RowCheck['transno']. '"
                                            AND stockrequest.code = "'.$RowCheck['code']. '"';
                                            $ErrMsg = _('The order could not be Updated Because :');
                                            $Result = DB_query($SQLD, $db, $ErrMsg);
                                            echo $SQLD ."<br />";
                                    }
                                        
                                  //  } //else{
                                       
                                  //  }   
                                
                                }
                                    $SQLUpdate = 'Update stockrequest
                                            SET consolno = "'.$ConsolationNo .'",
                                            consolidated = "Y"
                                            WHERE transno = "'.$Header['transno']. '"
                                            AND stockrequest.code = "'.$Header['code']. '"';
                                            $ErrMsg = _('The order could not be Updated Because :');
                                            $AllwResult = DB_query($SQLUpdate, $db, $ErrMsg);
                                            $UpOrder = $StockOrder[$i];   
                                            echo  $SQLUpdate .'<br />';//WHERE stockrequest.transno = '" . $Header['transno'] . "'
                              prnMsg(_('Updated transfer   NO:  ' .  $UpOrder .' Consolidation '.$ConsolationNo),' Success');
                   }
                           

            //}
     } 
    }//if consolidation
    else{
         prnMsg(_('Sorry, All ready consolated '  ),'information');
    }
    }
if ($_GET['Type']) {
	$_SESSION['Type'] = $_GET['Type'];

}
echo "<br>Session Type is  " . $_SESSION['Type'] . "<br>";
if (isset($_POST['Commit'])) {
    /*location exchange/submit of the picking slip*/
	//	echo "<br>Post 1 is  " . print_r($_POST) . "<br><br>";
	//echo "<br>Post is  " . print_r($_SESSION['PO']->LineItems) . "<br>";
	$ACount = count($_POST['lineNo']);
	//echo "<br>Acount is " . $ACount . "<br>";
	//	foreach ($_SESSION['PO']->LineItems as $POLine) {
	for ($i=0; $i<$ACount; $i++) {
		//	echo "<br>LineNo  " . $_POST['lineNo'][$i] . "<br>";
			//echo "<br>Qty  " . $_POST['Qtyd'][$i] . "<br>";
		//echo "<br>TotUnitsd  " . $_POST['TotUnitsd'][$i] . "<br>";
		//echo "<br>TotWeightd  " . $_POST['TotWeightd'][$i] . "<br>";
		$_SESSION['PO']->update_order_item(
			$_POST['lineNo'][$i],
			$_POST['Qtyd'][$i],
			$_POST['TotUnitsd'][$i],
			$_POST['TotWeightd'][$i],
			$_POST['ComSd'][$i],
			$_POST['ComRd'][$i]);
               // $_SESSION['CommentSend'] = $_POST['ComSd'][$i] ;
               // $_SESSION['CommentReceive'] = $_POST['ComRd'][$i];
		//echo "<br>q is  " . $_POST['ComSd'][$i] . "<br>";
		//echo "<br>qoh is  " . $_POST['ComRd'][$i]. "<br>";
		//check that there is enough qoh

		if ($_POST['Qtyd'][$i] > $_POST['Qoh'][$i] && $_SESSION['Type'] == "P" ){
		//if ( $_POST['Qtyd'][$i] > $_POST['Qoh'][$i] ){
			$InputError = True;
			prnMsg( _('The quantity entered ('). $_POST['Qtyd'][$i] . ') for '. $_POST['Stockid'][$i] . ' ' . _('is more than the quantity on hand of ') . $_POST['Qoh'][$i],'warn');
			$ErrorMessage .= _('The quantity entered for').' '. $_POST['StockID'][$i] . ' ' . _('is more than the quantity on hand of ') . $_POST['Qoh'][$i] .'<br>';

		}
	}//END FOR
	//echo "<br>error is  " . $InputError . "<br>";
	//echo "<br>Post after is  " . print_r($_SESSION['PO']->LineItems) . "<br>";
	if (!$InputError) {

	echo "<br>Now at line 54 ";
	$RDate = date("Y-m-d");
	foreach ($_SESSION['PO']->LineItems as $POLine){
           // echo " ".$POLine;
		if ($_POST['Commit'] == "Process Picking Slip") {
			$Qty = "qtydispatched";
			$Status = "pickingslip";
			$Date = "pickdate";
		} else
		if ($_POST['Commit'] == "Receive Shipment") {
			$Qty = "qtyreceived";
			$Status = "received";
			$Date = "rcvdate";
		} else {
			$Qty = "qtydispatched";
				$Status = "dispatched";
			$Date = "dispatchdate";

		}
                //echo " ". $_POST['Commit'];
                //if ($_POST['Commit'] == "Process Picking Slip")
                     //echo "Equals to ".$_POST['Commit'];
			//echo "<br>Stock id is " . $POLine->StockID . "<br>";
                       //  echo $POLine->CommentsR;
			$sql = "UPDATE stockrequest
					set $Qty = $POLine->Units,
						total = $POLine->TotalUnits,
						weight = $POLine->TotalWeight,";
						if ($_POST['Commit'] == "Receive Shipment") {
							$sql .= "rcvby = '" . $_SESSION['UserID'] . "',";
							$sql .= "$Status = 'N',";

						} else {
							$sql .= "$Status = 'Y',";
						}
						if ($_POST['SupplyFromStockLocation']) {
							$sql .= "dispatchedfrom = '" . $_POST['SupplyFromStockLocation'] . "',";
						}
						if ($_POST['Commit'] == "Send Shipment") {
							$sql .= "dispatchedby = '" . $_SESSION['UserID'] . "',";
							$sql .= "commentsS = '" . $POLine->CommentS ."'," ;
						}

						if ($_POST['Commit'] == "Receive Shipment") {
							$sql .= "commentsR = '".$POLine->CommentR ."'," ;
						}
						if ($_POST['Commit'] == "Process Picking Slip") {
							$sql .= "pickby = '" . $_SESSION['UserID']  . "',";
						}
						$sql .= "$Date = '" . $RDate . "'

						WHERE transno = '" . $_SESSION['RId'] . "'
						AND code = '" . $POLine->StockID . "'
						";
		        //  echo "<br>sql is  " . $sql . "<br>";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to enter Stock Request');
			$resultLocShip = DB_query($sql,$db, $ErrMsg);
			if ($_POST['Commit'] == "Send Shipment") {
				$sql = 'SELECT
						stockrequest.transno,
						stockrequest.code,
						stockrequest.dispatchedfrom,
						stockrequest.qtydispatched
						FROM stockrequest
						WHERE stockrequest.transno = "' . $_SESSION['RId'] . '"
						AND stockrequest.code = "' . $POLine->StockID . '"
						';
				//echo "<br>sql 109 " . $sql . "<br>";
				$resultDF = DB_query($sql,$db);
				$myrowDF = DB_fetch_array($resultDF);


				$sql = 'INSERT INTO stockintransit (
									transno,
									stockid,
									loccode,
									user,
									quantity)';
				$sql = $sql . "VALUES (
								'" . $_SESSION['RId'] . "',
								'" . $POLine->StockID . "',
								'" . $myrowDF['dispatchedfrom'] . "',
								'" . $_SESSION['UserID'] . "',
								" . $myrowDF['qtydispatched'] . "
								)";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to enter Stock into transit');
				$resultTrans = DB_query($sql,$db, $ErrMsg);
			}

		if ($_POST['Commit'] == "Receive Shipment") {
			$sql = "DELETE from stockintransit
					WHERE transno = '" . $_SESSION['RId'] . "'
					AND stockid = '" . $POLine->StockID . "'";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to delete item from transit');
			$resultDel = DB_query($sql,$db, $ErrMsg);
		}


	}

		//if ($_POST['Commit'] == "Send Shipment") {
		//	echo '<td><a target="blank" href="'.$rootpath.'/PDFStockSend.php?' . SID . 'TransferNo=' . $myrow['transno'] . '">'.	_('Print BOL'). '</a></td>';
		//	unset($_POST['SupplyFromStockLocation']);
	//	}
		//Stock are recieved, now do the moves and update locstock with new values
		if ($_POST['Commit'] == "Receive Shipment") {

			$sql="SELECT stockrequest.transno,
					stockrequest.code,
					stockrequest.quantity,
					stockrequest.qtydispatched,
					stockrequest.loccode,
					stockrequest.dispatched,
					stockrequest.dispatchedfrom,
					stockrequest.received,
					stockrequest.rcvdate,
					stockrequest.qtyreceived,
					stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.packsize,
					stockmaster.kgs
					FROM stockrequest
					INNER JOIN stockmaster
					ON stockrequest.code = stockmaster.stockid
					WHERE stockrequest.transno = '" . $_SESSION['RId'] . "'
					";
                        /*checkin quantities tamelo*/
                        $ErrMsg = _('Alerts cannot be retrieved because');
			$DbgMsg = _('The SQL used to check negative stock but failed was');
			$resultCheck = DB_query($sql,$db,$ErrMsg,$DbgMsg);
                        $Check = array ();
                        $CheckCount = 0;
                        while($myrowCheck = DB_fetch_array($resultCheck))
                        {
                            $SQLFRM = 'SELECT
						locstock.quantity
						FROM locstock
						WHERE locstock.stockid = "' .$myrowCheck['code'] . '"
						AND locstock.loccode = "' .$myrowCheck['dispatchedfrom'] . '"
						';
					$resultStkFRMLocs = DB_query($SQLFRM ,$db);
					$myrowDisploc = DB_fetch_array($resultStkFRMLocs);
                            
                          if($myrowDisploc['quantity'] < $myrowCheck['qtyreceived'])
                            {
                                 $Check[] = $myrowCheck['code'];
                            }
                        }
                        if(!empty($Check))
                        {
                            prnMsg(" Quantity Requested is higher that quantity on hand for ");
                            foreach($Check  as $value)
                            {
                               echo '<b>Item  '.$value .'</b><br>';
                            }
                            exit();
                        }
			$ErrMsg = _('Alerts cannot be retrieved because');
			$DbgMsg = _('The SQL used to retrieve the request details but failed was');
			$result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);
                        
			
                               $PeriodNo = 0;
				while($myrow = DB_fetch_array($result1) ){
					//echo "<br>received total is  " . $myrow['qtyreceived'] . "<br>";
					//Add the stock to location
                                    if($PeriodNo == 0){
                                            $pdate = ConvertSQLDate($myrow['rcvdate']);
                                            $PeriodNo = GetPeriod($pdate, $db);
                                    }
					$sql = 'SELECT
						locstock.quantity
						FROM locstock
						WHERE locstock.stockid = "' . $myrow['code'] . '"
						AND locstock.loccode = "' . $myrow['loccode'] . '"
						';
					//echo "<br>sql 109 " . $sql . "<br>";
					$resultStkLocs = DB_query($sql,$db);
					$myrowloc = DB_fetch_array($resultStkLocs);
					$newtotal = $myrowloc['quantity'] + $myrow['qtyreceived'];
					$sql = "UPDATE locstock
							set quantity =  $newtotal
							WHERE locstock.stockid = '" . $myrow['code'] . "'
							AND locstock.loccode = '" . $myrow['loccode'] . "'
						";
					//echo "<br>sql line 151 " . $sql . "<br>";
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to update Stock quantities');
					$resultUp = DB_query($sql,$db, $ErrMsg);
					//echo "<br>code " . $myrow['code'] . "<br>";
					//echo "<br>transno " . $_SESSION['RId'] . "<br>";
					//echo "<br>ddate " . $myrow['rcvdate'] . "<br>";
				//	echo "<br>period " . $PeriodNo . "<br>";
					//echo "<br>qtyrecv " . $myrow['qtyreceived'] . "<br>";

					$SQL = "INSERT INTO stockmoves (
									stockid,
									type,
                                                                        debtorno,
                                                                        branchcode,
									transno,
									loccode,
									trandate,
									prd,
									reference,
									qty,
									newqoh )
							VALUES ('" . $myrow['code'] . "',
								19,
                                                                '".$_SESSION['UserID'] ."',
                                                                '".$_SESSION['UserID'] ."',
								'" . $_SESSION['RId'] . "',
								'" . $myrow['loccode'] . "',
								'" . $myrow['rcvdate'] . "',
								" . $PeriodNo . ",
								'" . _('Stock received with internal transfer from') . ': ' .  $myrow['dispatchedfrom'] . "',
								" . $myrow['qtyreceived'] . ",
								" . $newtotal . "
								)";
					//echo "<br>sql line 186 " . $SQL . "<br>";
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to insert data into Stockmoves');
					$resultSM = DB_query($SQL,$db, $ErrMsg);
						//subtract the stock from location
					$sql = 'SELECT
						locstock.quantity
						FROM locstock
						WHERE locstock.stockid = "' . $myrow['code'] . '"
						AND locstock.loccode = "' . $myrow['dispatchedfrom'] . '"
						';
					//echo "<br>sql 192 " . $sql . "<br>";
					$resultStkLocs = DB_query($sql,$db);
					$myrowloc = DB_fetch_array($resultStkLocs);
					$newtotal = $myrowloc['quantity'] - $myrow['qtyreceived'];
					$sql = "UPDATE locstock
							set quantity = '" . $newtotal . "'
							WHERE locstock.stockid = '" . $myrow['code'] . "'
							AND locstock.loccode = '" . $myrow['dispatchedfrom'] . "'
						";
					//echo "<br>sql line 204 " . $sql . "<br>";
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to update Stock quantities');
					$resultUp = DB_query($sql,$db, $ErrMsg);

					$sql = "UPDATE stockrequest
							set received  = 'Y'
							WHERE stockrequest.code = '" . $myrow['code'] . "'
							AND stockrequest.transno = '" . $_SESSION['RId'] . "'
						";
					//echo "<br>sql line 204 " . $sql . "<br>";
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to receive status');
					$resultUp = DB_query($sql,$db, $ErrMsg);

					$SQL = "INSERT INTO stockmoves (
									stockid,
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
									newqoh )
							VALUES ('" . $myrow['code'] . "',
								19,
								'" . $_SESSION['RId'] . "',
								'" . $myrow['dispatchedfrom'] . "',
								'" . $myrow['rcvdate'] . "',
								'',
								'',
								" . $PeriodNo . ",
								'" . _('Internal stock transfer to') . ': ' .  $myrow['loccode'] . "',
								" . -$myrow['qtyreceived'] . ",
								'',
								0,
								" . $newtotal . "
								)";
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to update Stock quantities');
					$resultSM = DB_query($SQL,$db, $ErrMsg);
                                        
                                     /*Tamelo : now Delete From temp_locationTranshistory*/
                                      /*What if location requested, same item with different transfer numbers*/
                                     $sqlcheckR = "UPDATE temp_locationTranshistory
						set qty = qty - " . $myrow['qtyreceived'] . "
					WHERE temp_locationTranshistory.stockid = '" . $myrow['code'] . "'
                                        AND temp_locationTranshistory.loccodefrom ='".$myrow['dispatchedfrom']."'
					AND temp_locationTranshistory.loccodeto = '" . $myrow['loccode']. "'";
                                     $ErrMsg = _('CRITICAL ERROR') . '! ' . _('failed to update temp_locationTranshistory');
                                     $ResultCheckR = DB_query($sqlcheckR,$db, $ErrMsg);
                                     
                                     $sqlSelectcheckR = "SELECT * FROM temp_locationTranshistory
					WHERE temp_locationTranshistory.stockid = '" . $myrow['code'] . "'
                                        AND temp_locationTranshistory.loccodefrom ='".$myrow['dispatchedfrom']."'
					AND temp_locationTranshistory.loccodeto = '" . $myrow['loccode']. "'";
                                     $ErrMsg = _('CRITICAL ERROR') . '! ' . _('failed to select temp_locationTranshistory records');
                                     $ResultSelectCheckR = DB_query($sqlSelectcheckR,$db, $ErrMsg);
                                     $myrowCheckR = DB_fetch_array($ResultSelectCheckR);
                                     
                                     if($myrowCheckR['qty']== 0 || $myrowCheckR['qty'] < 0 )
                                     {
                                         $SQL = "DELETE 
                                            FROM temp_locationTranshistory
                                            WHERE loccodefrom ='".$myrow['dispatchedfrom']."'
                                            AND loccodeto ='".$myrow['loccode']."'
                                            AND stockid ='".$myrow['code']."'";
                                         $ErrMsg = _('CRITICAL ERROR') . '! ' . _('failed to Delete from temp_locationTranshistory');
                                         $ResultTemp = DB_query($SQL,$db, $ErrMsg);
                                     }
                                        
				}
			prnMsg( _('Request for stock ' . $_SESSION['$Trf_ID'] . ' received and stock item levels updated successfully'),'success');
			echo '<p><a target="new" href="'.$rootpath.'/PDFStockReceive.php?' . SID . 'TransferNo=' . $_SESSION['RId'] . '">'.
		_('Print Receive Document'). '</a>';

				//echo '<td><a target="blank" href="'.$rootpath.'/PDFStockReceive.php?' . SID . 'TransferNo=' . $myrow['transno'] . '">'.	_('Print Shipping Document'). '</a></td>';
		}
	$_GET['Newitem'] = TRUE;
}

//unset ($_SESSION['CommentSend'] );
//unset($_SESSION['CommentReceive']);
}
//unset ($_POST['Commit']);


if ($_GET['Newitem']) {


	unset($_SESSION['PO']);
	unset($_SESSION['RId']);
	unset($_SESSION['Loccode']);
	$_POST['FromStockLocation'] = $_SESSION['FromStock'];
	unset($_POST['SupplyFromStockLocation']);
	unset($_SESSION['FromStock']);
	unset($_SESSION['Locationname']);
	unset($_SESSION['DispatchedFrom']);


}
if ($_GET['TransNo']) {

	unset($_SESSION['PO']);
	//echo "<br>Now at line 327 ";
	if (!$_SESSION['PO']->LineItems) {
	//Now at line 367 echo "<br>Now at line 309 ";
		$_SESSION['RId'] = $_GET['TransNo'];
		$_SESSION['PO'] = new PurchOrderI();

        if(!empty($_SESSION['PO']->LinesOnOrder)){
            $_SESSION['PO']->LinesOnOrder = "";
        }
		$_SESSION['PO']->LinesOnOrder = 0;
		$sql="SELECT stockrequest.transno,
					stockrequest.code,
					stockrequest.quantity,
					stockrequest.qtydispatched,
					stockrequest.loccode,
					stockrequest.dispatched,
					stockrequest.dispatchedfrom,
					stockrequest.received,
					stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.packsize,
					stockmaster.kgs
					FROM stockrequest
					INNER JOIN stockmaster
					ON stockrequest.code = stockmaster.stockid
					WHERE stockrequest.transno = '" . $_GET['TransNo'] . "'
                                        
					AND (stockrequest.dispatched = 'N'
					OR stockrequest.received = 'N' OR stockrequest.pickingslip = 'N'
                                        )
					ORDER BY stockrequest.code";
		//echo "<br>sql 333 is  " . $sql . "<br>";AND consolidated = 'Y'
		$ErrMsg = _('Alerts cannot be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the alerts details but failed was');
		$result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		while($myrow=DB_fetch_array($result1)){
            /*Initialize */
            $Quantity = 0;
            $totalunits = 0;
			$totalweight = 0;

			if ($_SESSION['Type'] == "P") {
				$Quantity = $myrow['quantity'];
				//echo "<br>Q1 is " . $Quantity . "<br>";
			} else {
				$Quantity = $myrow['qtydispatched'];
				//echo "<br>Q2 is " . $Quantity . "<br>";
			}
			if ($myrow['packsize'] == "" || $myrow['packsize'] == 0) {
				$myrow['packsize'] =1;
			}
                       // if(myrow['packsize'] )
			//echo "<br>code is " . $myrow['code'] . "<br>";
		//	echo "<br>desc is  " . $myrow['description'] . "<br>";
		//	echo "<br>pack is  " . $myrow['packsize'] . "<br>";
		//	echo "<br>q is  " . $myrow['quantity'] . "<br>";
		//	echo "<br>uom  " . $myrow['uom'] . "<br>";
		//	echo "<br>tot " . $myrow['total'] . "<br>";
		//	echo "<br>weight  " . $myrow['WeightEach'] . "<br>";
			$totalunits = $myrow['quantity'] * $myrow['packsize'];
			$totalweight = $myrow['quantity'] * $myrow['kgs'];
			$_SESSION['Loccode'] = $myrow['loccode'];


			if ($myrow['dispatchedfrom'] != "") {
				$_SESSION['DispatchedFrom'] = $myrow['dispatchedfrom'];
			}

			$_SESSION['PO']->add_to_orderI (
			$_SESSION['PO']->LinesOnOrder+1,
			$myrow['code'],
			$myrow['description'],
			$myrow['packsize'],
			$Quantity,
			$myrow['units'],
			$totalunits,
			$totalweight,
			$myrow['kgs']
			);
		}//end while statements

	} else {
		unset($_SESSION['PO']);
	}

	$_SESSION['cancel'] = 1;
}


if (!$_GET['TransNo'] && !$_POST['SupplyFromStockLocation'] ) {


?>

<form action="<?=$_SERVER['PHP_SELF']?>" method="post" id="Rform">
<table width="525" border="1" align="center">

  <tr>
    <th colspan="3">Oustanding Stock requests</th>
    </tr>
  <tr>
    <td>Select Location</td>
    <td colspan="2">
    <?php
    $sql = 'SELECT loccode, locationname FROM locations';
    $resultStkLocs = DB_query($sql,$db);
    echo '<select name="FromStockLocation" onchange=submitform("Rform");>';
    while ($myrow=DB_fetch_array($resultStkLocs)){
    	if (isset($_POST['FromStockLocation'])){
    		if ($myrow['loccode'] == $_POST['FromStockLocation']){
    			echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
    		} else {
    			echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
    		}
    	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
    		echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
    		$_POST['FromStockLocation']=$myrow['loccode'];
    	} else {
    		echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
    	}
    }
    echo '</select>';
?>
</td>

  </tr>

</table>
</form>
<?php

if ($_POST['FromStockLocation']) {
	$_SESSION['FromStock'] = $_POST['FromStockLocation'];
         $tols = 0.00;
         $sqla = 'DELETE  FROM stockrequest WHERE total <='.$tols;
         $results = DB_query($sqla,$db);

         $sqlquanntity = 'SELECT transno,code, user, requestdate, dispatched, received, pickingslip
		FROM stockrequest
		WHERE loccode = "' . $_POST['FromStockLocation'] . '"
                AND  (total <='.$tols.'
                OR quantity <='.$tols.')
		AND (dispatched != "Y"
		OR received != "Y" OR pickingslip != "Y")
                ORDER BY code ';
          $resultsQuantity = DB_query($sqlquanntity ,$db);
          $message = "";
          $stcodeidcode;
          $transNo;
         while($myrow=DB_fetch_array($resultsQuantity))
         {
             $stcodeidcode = $myrow['code'];
             $transNo = $myrow['transno'];
             $message .= "<tr><td class=tdc><font color=red><u>Stock Code $stcodeidcode ,transfer: $transNo , have zero quntity/total please correct/delete </u></font></td></tr>";
         }
         
         if($message != "")
         {
            echo '<link href="'.$rootpath.'/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
            echo '<script src="'.$rootpath.'/facebox/src/facebox.js" type="text/javascript"></script>';
            echo "<script>jQuery.facebox('$message');</script>";
         }
    $sql = 'SELECT DISTINCT(transno), user, requestdate, dispatched,lastUpdate, received, pickingslip,consolidated 
		FROM stockrequest
		WHERE loccode = "' . $_POST['FromStockLocation'] . '"
		AND (dispatched != "Y"
		OR received != "Y" OR pickingslip != "Y")
                GROUP BY transno
                ';
    $_SESSION['consolelocation'] = $_POST['FromStockLocation'];
//}
	//echo "<br>sql 245 " . $sql . "<br>"; AND consolidated = "Y"
$resultStkLocs = DB_query($sql,$db);
	?>

<form action="<?=$_SERVER['PHP_SELF']?>" method="post" />
<table >

<?php
//width=800
if (mysql_num_rows($resultStkLocs) > 0) {
	echo '<tr><th>Request number</th><th>Requested by</th><th>Date</th><th>Last Updated</th><th>Dispatched</th><th>Consolidated</th><th></th><th></th><th></th><th></th></tr>';
} else {
	echo '<tr><th colspan=3>No records to display</th></tr>';
}
//<th>Select</th>
$CantConsolidate = false;
while ($myrow=DB_fetch_array($resultStkLocs)){
    $lastUpdated = "";
    if($myrow['dispatched'] == "N" && $myrow['consolidated'] == "Y")
    {
      echo '<tr BGCOLOR="red">';
      
    } elseif($myrow['dispatched'] == "Y") {
        echo '<tr BGCOLOR="yellow">';
    }
 else {
        echo '<tr style="background-color: #99FF99"; >';
    }
    
	if ($myrow['pickingslip'] == "N" && $myrow['consolidated'] == "N") {
		//echo "pick is pppp";
		$Type = "P";
		$LinkStatus = "Picking Slip";
	}
	if ($myrow['dispatched'] == "N" && $myrow['pickingslip'] == "Y" && $myrow['consolidated'] == "N") {
	//	echo "type is dddd";
		$Type = "D";
		$LinkStatus = "Send Stock";
	}
        if($myrow['dispatched'] == "Y" && $myrow['consolidated'] == "N") {
	//	echo "type is dddd";
		$Type = "R";
		$LinkStatus = "Receive Stock";
        }
        if($myrow['lastUpdate'] == null || $myrow['lastUpdate'] = '0000-00-00'){
          $lastUpdated =  $myrow['requestdate'];
        }else{
           $lastUpdated =  $myrow['lastUpdate'];
        }
          //<td><input type='checkbox' id='checkbox[]' name='RowSelect[]' value='%s'></td>
                                //<td><input type='checkbox' id='checkbox2[]' name='OrderNo[]' value='%s'checked='yes'  style='display:none;' ></td>
        echo '<td>' . $myrow['transno'] . '</td><td>' . $myrow['user'] . '</td><td>' . $myrow['requestdate'] . '</td><td>' . $lastUpdated . '</td><td>' . $myrow['dispatched'] . '</td><td>'.$myrow['consolidated'].'</td><td><input type="checkbox" id="checkbox2[]" name="transNo[]" value="'.$myrow['transno'] .'"></td><td><a target="_blank" href="'.$rootpath.'/PDFStockRequest.php?' . SID . 'TransferNo='.$myrow['transno'] .'">'. _('Print'). ' </a></td>';
        if ($Type == "R" && $myrow['consolidated'] == "N") {
		//echo "type is d";
             //echo '<tr><td>' . $myrow['transno'] . '</td><td>' . $myrow['user'] . '</td><td>' . $myrow['requestdate'] . '</td><td>' . $myrow['dispatched'] . '</td><td><a href=StockRequest.php?TransNo=' . $myrow['transno'] . '&Type=' . $Type . '>' . $LinkStatus . '</a></td>';
		echo '<td><a href=StockRequest.php?TransNo=' . $myrow['transno'] . '&Type=' . $Type . '>' . $LinkStatus . '</a></td><td><a target="new" href="'.$rootpath.'/PDFStockSend.php?' . SID . 'TransferNo=' . $myrow['transno'] . '">'.	_('Print Shipping Document'). '</a></td>';
	}
        
	if ($Type == "D" && $myrow['consolidated'] == "N") {
		//echo "type is d";
		echo '<td><a href=StockRequest.php?TransNo=' . $myrow['transno'] . '&Type=' . $Type . '>' . $LinkStatus . '</a></td><td><a target="new" href="'.$rootpath.'/PDFStockPick.php?' . SID . 'TransferNo=' . $myrow['transno'] . '">'.	_('Print Picking Slip'). '</a></td>';
		//echo '<td><a target="new" href=StockRequest.php?TransNo=' . $myrow['transno'] . '&Type=' . $Type . '>'.	_('Edit order'). '</a></td>';
	}
//	if ($Type == "R" && $myrow['consolidated'] == "N") {
//		//echo "type is d";
//		echo '<td><a target="new" href="'.$rootpath.'/PDFStockSend.php?' . SID . 'TransferNo=' . $myrow['transno'] . '">'.	_('Print Shipping Document'). '</a></td>';
//	}
	if ($Type == "P" && $myrow['consolidated'] == "N") {
		//echo "type is d";
		//echo '<td><a target="blank" href="'.$rootpath.'/PDFStockPick.php?' . SID . 'TransferNo=' . $myrow['transno'] . '">'.	_('Print Picking Slip'). '</a></td>';
		echo '<td><a href=StockRequest.php?TransNo=' . $myrow['transno'] . '&Type=' . $Type . '>' . $LinkStatus . '</a></td><td><a  href="'.$rootpath.'/UpdateStockLocTransferIntern.php?' . SID . 'TransNo=' . $myrow['transno'] . '&Type=edit">'.	_('Edit order'). '</a></td>';
	}
  //  }
echo '</tr>';

    
}
    echo "<tr><td><a href='" . $rootpath . '/' . $_SESSION['reports_dir'] . "/StockRequests.csv'>" . _('click here') .  '</a> ' . _('to export all to csv') . '</td></tr>';
     // echo "<tr><td><a href='" . $rootpath . '/' . $_SESSION['reports_dir'] . "/StockRequests.csv'>" . _('click here') .  '</a> ' . _('to export all to csv') . '</td></tr>';

echo '</table>';
    echo '<br /><div class="centre"><input type=submit name="Consolidate" VALUE="' . _('Consolidate Requests') . '"onclick="return confirm(\'' . _('Are you sure you wish to continue ?\nRequest will be cololidated to one request!!') . '\');"></div>';
?>
</form>
<?php
}
}
if ($_SESSION['PO']->LinesOnOrder > 0) {
//echo "<br>Now at line 290 ";
	$sorter = new t_object_sorter;
	$sorter->sort($PurchOrderI->LineDetails, 'StockID');

	echo "<table border=1>";
	echo '<form action="' . $_SERVER['PHP_SELF'] . '?'. SID . '" method=post id = Sform>';

	echo '<tr><td colspan=2><h2>'. _('Inventory Location Transfer Shipment Reference').' # '. $_SESSION['RId']. '</td></tr></h2>';
	if ($_SESSION['Type'] == "D" || $_POST['SupplyFromStockLocation'] || $_SESSION['Type'] == "P") {


	$sql = 'SELECT loccode, locationname FROM locations WHERE loccode != "' . $_SESSION['Loccode'] . '"';

	$Locresult = DB_query($sql,$db);
		//$myrow=DB_fetch_array($Locresult);

		//echo "<br>loccode is " . $myrow['loccode'] . "<br>";
		if ($_SESSION['Type'] == "P") {
			echo '<tr><td>' ._('From Stock Location').':<select name="SupplyFromStockLocation" onchange=submitform("Sform");>';
			while ($myrow=DB_fetch_array($Locresult)){
				if (isset($_POST['SupplyFromStockLocation'])){
					if ($myrow['loccode'] == $_POST['SupplyFromStockLocation']){
						$_SESSION['Locationname'] = $myrow['locationname'];
						echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
					} else {
						echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
					}
				} elseif ($myrow['loccode']==$_SESSION['SupplyFromStockLocation']){
					echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
					$_POST['SupplyFromStockLocation']=$myrow['loccode'];
				} else {
					echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
				}
			}
			echo '</select></td>';
		} else {
			$sql = 'SELECT loccode, locationname FROM locations WHERE loccode = "' . $_SESSION['DispatchedFrom'] . '"';

			$resultStkLocs = DB_query($sql,$db);
			$myrow=DB_fetch_array($resultStkLocs);
			echo '<tr><td>' ._('From Stock Location').':' . $myrow['locationname'];
		}


	DB_data_seek($resultStkLocs,0); //go back to the start of the locations result
		$sql = 'SELECT loccode, locationname FROM locations WHERE loccode = "' . $_SESSION['Loccode'] . '"';

		$resultStkLocs = DB_query($sql,$db);
		$myrow=DB_fetch_array($resultStkLocs);
	echo '<td>' ._('To Stock Location:'). $myrow['locationname'];

	echo '</td></tr>';
	}
	echo '<table>';
	$tableheader = "<tr>
				<th colspan=7>" . _('Already in cart') . "</th>
				</tr><tr>
				<th>" . _('Code')  . "</th>
				<th>" . _('Description') . "</th>
				<th>" . _('Item Pack Size') . "</th>
				<th>" . _('No of Items') . "</th>
				<th>" . _('UOM') . "</th>
				<th>" . _('Total') . "</th>
				<th>" . _('Weight in Kgs') . "</th>";
				if ($_SESSION['Type'] == "D" || $_SESSION['Type'] == "P") {
					$tableheader .= "<th>" . _('QOH') . "</th>";
				}
				if ($_SESSION['Type'] == "D" || $_SESSION['Type'] == "R") {
					$tableheader .= "<th>" . _('Comments') . "</th>";
				}


				$tableheader .= "</tr>";
	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter
	$count = 0;
	$tabindex = 1;
        $ShowMessage = " ";
        
	foreach ($_SESSION['PO']->LineItems as $POLine) {
		//echo "<br>Now at line 367 ";
		if (!$_POST['SupplyFromStockLocation']) {
			$_POST['SupplyFromStockLocation'] = $_SESSION['DispatchedFrom'];
		}
		$sql="SELECT
		stockid,
  		quantity
  		FROM locstock
		WHERE stockid = '" . $POLine->StockID . "'
		AND loccode = '" . $_POST['SupplyFromStockLocation'] . "'
		";
		$ErrMsg = _('Alerts cannot be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the alerts details but failed was');
		$qohresult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		$myrowStock=DB_fetch_array($qohresult);
		//echo "<br>Line is  " . $POLine->LineNo . "<br>";
		if ($POLine->Deleted== FALSE) {
			if ($POLine->PackSize == "" || $POLine->PackSize == 0) {
				$POLine->PackSize =1;
			}

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
                        $FromQuantity = round($myrowStock['quantity'],2);
                        if ($_SESSION['Type'] == "D"||$_SESSION['Type'] == "P" || $_SESSION['Type'] == "R")
                        {
                            if($POLine->Units > $FromQuantity)
                            {
                             
                                  $ShowMessage = "<tr><td class=tdc> PLEASE correct quantities highlighted. Quantity request is more than dispatch location quantity </td></tr>";
                               
                               echo '<tr BGCOLOR="FF6600">';
                               
                            }
                        }
			$WeightEach = $POLine->WeightEach;
			echo "<td>".$POLine->StockID."</td>
				<td>".$POLine->ItemDescr."</td>
				<input type=hidden name=descriptiond[] value='" . $POLine->ItemDescr . "'>
				<input type=hidden name=Stockid[] value='" . $POLine->StockID . "'>
				<input type=hidden name=lineNo[] value='" . $POLine->LineNo . "'>
				<td><input type=text id=Packd" . $count . " name=Packd[] size=11 value=". $POLine->PackSize ." readonly></td>
				<td><input tabindex=" . $tabindex . " type=text id=Qtyd" . $count . " name=Qtyd[] onkeyup=CalcUnitsTF('d',$count,$WeightEach); size=11 value=".$POLine->Units."></td>
				<td><input type=text id=Unitsd" . $count . " name=Unitsd[] size=11 value=". $POLine->UOM ." readonly></td>
				<td><input readonly type=text id=TotUnitsd" . $count . " name=TotUnitsd[] size=11 value=". round($POLine->TotalUnits,2) ."></td>
				<td><input type=text id=TotWeightd" . $count . " name=TotWeightd[] size=11 value=". round($POLine->TotalWeight,2) ." readonly></td>";
			if ($_SESSION['Type'] == "D" || $_SESSION['Type'] == "P") {
				echo "<td><input type=text id=Qoh" . $count . " name=Qoh[] size=11 value=". round($myrowStock['quantity'],2) ." readonly></td>";
			}
			if ($_SESSION['Type'] == "D") {
				echo "<td><input type=text id=ComSd" . $count . " name=ComSd[] size=60 value=". $POLine->CommentsS ."></td>";
			}
			if ($_SESSION['Type'] == "R") {
				echo "<td><input type=text id=ComRd" . $count . " name=ComRd[] size=60 value=". $POLine->CommentsR ."></td>";
			}
			echo 	"<input type=hidden name=StockIDd[] value=" . $POLine->StockID . ">

			</tr>";


			++$count;
		}
                // $AvoidMultipleMessage = 0;
	}
	#end of while loop
         if($ShowMessage  != "")
         {
            echo '<link href="'.$rootpath.'/facebox/src/facebox.css" media="screen" rel="stylesheet" type="text/css"/>';
            echo '<script src="'.$rootpath.'/facebox/src/facebox.js" type="text/javascript"></script>';
            echo "<script>jQuery.facebox('$ShowMessage');</script>";
         }
	if (($_SESSION['Type'] == "P")) {
		$ButtonValue = "Process Picking Slip";

	} else if ($_SESSION['Type'] == "D") {
		$ButtonValue = "Send Shipment";
	} else if ($_SESSION['Type'] == "R") {
		$ButtonValue = "Receive Shipment";
	}
	echo '<tr><td><input type="submit" name="Commit" value="' . $ButtonValue . '"></td></tr>';
	echo '</table>';




}
include('includes/footer.inc');
//print_r($_SESSION['PO']->LineItems);
?>