<?php
/* $Revision: 1.11 $ */
/* Created on 25 Oct 10 PDT */
// $PageSecurity = 18;
$StockAllowUpdate = 31;
ini_set('session.gc_maxlifetime', 2880);
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
$title = _('Stock Take');

include('includes/header.inc');
///////////////////////////////	Prepare data for csv /////////////////////////////
function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(",", ".", $str);
}
//echo "<br>St num is " . $_SESSION['SKNumber'] . "<br>";
$ErrMsg = _('The SQL to get the stock quantities failed with the message');

$sqlHeader = 'SELECT categoryid 
			 	FROM stockmaster ';
                               
              if($_POST['FromCriteria'] != "All")
              {
                $sqlHeader .= ' WHERE stockmaster.categoryid = "' . $_POST['FromCriteria']. '"
                                GROUP BY categoryid ';
              }
 else {
          $sqlHeader .= ' GROUP BY categoryid ';
 }

               	
$resultHeader = DB_query($sqlHeader, $db, $ErrMsg);

if (!file_exists($_SESSION['reports_dir'])){
	$Result = mkdir('./' . $_SESSION['reports_dir']);
}

$filename = $_SESSION['reports_dir'] . '/StockQties.csv';

$fp = fopen($filename,"w");
if ($fp==FALSE){

	prnMsg(_('Could not open or create the file under') . ' ' . $_SESSION['reports_dir'] . '/StockQties.csv','error');
	include('includes/footer.inc');
	exit;
}
$HeaderInitial = "";
while($Header = DB_fetch_array($resultHeader))
{
    //fputs($fp, $lineHeader. "\n");
    $lineHeader = $Header['categoryid'].',';
    fputs($fp,"\n". $lineHeader. "\n");
    
    if($lineHeader != $HeaderInitial)
    {
    $HeaderInitial = $Header['categoryid'];
    $sql = 'SELECT stockcounts.stockbatch,
				stockcounts.stockid,
				stockcounts.qtycounted,
				stockcounts.qoh,
				stockcounts.variance,
				stockcounts.materialcost,
				stockcounts.department,
			 	stockcounts.description,
			 	stockcounts.kgs,
			 	stockcounts.units
			 	FROM stockcounts
                                     INNER JOIN stockmaster ON stockcounts.stockid = stockmaster.stockid
		WHERE stockcounts.finalised = "N"';
         //if($_POST['FromCriteria']!="All")
          //{
           //   $sql .= ' AND stockmaster.categoryid = "' .$Header['categoryid']. '"
                       // AND stockcounts.loccode = "' . $_SESSION['UserStockLocation'] . '"';
         //  }elseif($_POST['FromCriteria'] == "All"){
                   $sql .= ' AND stockmaster.categoryid = "' .$Header['categoryid']. '"
                             AND stockcounts.loccode = "' . $_SESSION['UserStockLocation'] . '"
                             GROUP BY stockcounts.stockid';
          // }
    }
            $result = DB_query($sql, $db, $ErrMsg);

$line = 'Stockbatch, Stockid, Counted, Qoh, Variance, Materialcost, Department, Description, KGS, Units';
fputs($fp, $line . "\n");
While ($myrow = DB_fetch_row($result)){
	$line = stripcomma($myrow[0]) . ', ' . stripcomma($myrow[1]) . ', ' . stripcomma($myrow[2]) . ', ' . stripcomma($myrow[3]) . ', ' . stripcomma($myrow[4]) . ', ' . stripcomma($myrow[5]) . ', ' . stripcomma($myrow[6]) . ', ' . stripcomma($myrow[7]) . ', ' . stripcomma($myrow[8]) . ', ' . stripcomma($myrow[9]);
	fputs($fp, $line . "\n");
}
    //$HeaderInitial = "";

}
fclose($fp);


/////////////////////////////////	END of csv data prepare /////////////////////////////////////////////////////////


if ($_POST['Finalise']) {
	$sql = 'SELECT 	stockcounts.stockid,
					stockcounts.qtycounted,
					stockcounts.qoh,
					stockcounts.loccode,
					stockcounts.materialcost,
					stockcounts.stockbatch,
					stockcounts.variance
			FROM stockcounts
			INNER JOIN stockmaster
			ON stockcounts.stockid = stockmaster.stockid
			WHERE finalised = "N"
			AND stockcounts.loccode="' . $_SESSION['UserStockLocation'] . '"';
	//echo "<br>SQl ffs is " . $sql . "<br>";
	$ErrMsg = _('Could not retrieve stock info because');
	$DbgMsg = _('The SQL used to retrieve info was');
	$Result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$StockInfo1 = DB_fetch_array($Result);

	//$TypeNoField = "typeno" . $_SESSION['UserStockLocation'];
	//$NextNo = GetNextTransNoTenderType(43, $db, $TypeNoField);
	$STNo = $_SESSION['SKNumber'];
	//echo "<br>sknumber " . $_SESSION['SKNumber'] . "<br>";
	//$STNo = GetNextTransNo(43, $db);
	$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
	$Datenow = date("Y-m-d");
	echo $rows = DB_num_rows($Result);
	$count = 1;
while ($StockInfo = DB_fetch_array($Result1)) {

	//echo "<br>Count is " . $count . "<br>";
	//echo "<br>Now at line 38<br>";
        /*Get current quantity of stock..*/
      $sqlq = "SELECT  quantity FROM locstock 
                                WHERE loccode = '" . $_SESSION['UserStockLocation'] . "'
                                AND stockid = '" . $StockInfo['stockid'] . "'";
         
         
        $ErrMsg = _('Could not retrieve stock quantities');
	$DbgMsg = _('The SQL used to retrieve info was');
	$QResults = DB_query($sqlq ,$db,$ErrMsg,$DbgMsg);
        $qrows = DB_fetch_array($QResults);
        
        /*varience = counted - qty(location quantity at that time)*/
        /* $count variance =  5 - 6;
         * $count variance = -1
         * 
         *(1) 2 gets invoiced or something...
         */
         /* variance/currrent quantity plus variance(4+(-1)) = 3... so should work 4 + 1 = 5 ...)
         * gl =  3 * materialcost
         * 
         *(2) nothing taken off
         * 
         *  variance/currrent quantity plus variance(6+(-1)) = 5.
         *  gl =  5 * materialcost
         */
        
        $NewQty = $qrows['quantity'] + $StockInfo['variance'];
	//$NewQty = $StockInfo['qtycounted']; $NewQty 
	//$GLCost = $StockInfo['variance'] * $StockInfo['materialcost'];
        $GlValue =  $NewQty - $qrows['quantity'];
        $GLCost =   $GlValue * $StockInfo['materialcost'];
        
	if ($StockInfo['variance'] != 0) {
		
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
								price,
								newqoh)
						VALUES
							('" . $StockInfo['stockid'] . "',
							43,
							'" . $StockInfo['stockbatch']  . "',
							'" . $_SESSION['UserStockLocation'] . "',
							'" . $Datenow . "',
							'" . $_SESSION['UserID'] . "',
							'" . $_SESSION['UserID'] . "',
							" . $PeriodNo . ",
							'" .$StockInfo['Reference'] . "',
							" . $StockInfo['variance'] . ",
							" . $StockInfo['materialcost'] . ",
							" . $StockInfo['materialcost'] . ",
							" . $NewQty . ")";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
		$DbgMsg = _('The following SQL to insert the stock movement records was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		/* Update the locstock with the new quantities PDT */
		$sql = "UPDATE locstock set quantity = '$NewQty'
							WHERE loccode = '" . $_SESSION['UserStockLocation'] . "'
							AND stockid = '" . $StockInfo['stockid'] . "'";
		$ErrMsg = _('Could not locstock because');
		$DbgMsg = _('The SQL used was');
		$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

		/* Gl entries for adjusting stock values debit stockact and credit stkadjustment acc */
	        $StockGLCode = GetStockGLCode($StockInfo['stockid'], $db);
		$SQL = "INSERT INTO gltrans (type,
				                                typeno,
				                                trandate,
				                                user,
				                                loccode,
				                                periodno,
				                                account,
				                                narrative,
				                                amount)
				                            VALUES (43,
				                                '" . $STNo . "',
				                                '" . $Datenow . "',
				                                '" . $_SESSION['UserID'] . "',
				                                '" . $_SESSION['UserStockLocation'] . "',
				                                " . $PeriodNo . ",
				                                '" . $StockGLCode['stockact'] . "',
				                                '" . $StockInfo['stockid'] . ' adjusted from  ' . $qrows['quantity'] . ' to ' .  $NewQty . "',
				                                " . $GLCost . ")";
		//echo "<br>Glsql is " . $SQL . "<br>";
		
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL credit for the stock cost adjustment posting could not be inserted because');
		$DbgMsg = _('The following SQL to insert the GLTrans record was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

// CJ input due to non matched entry in trial balance everytime a stock take is done and adjustment done.
		
		$SQL = "INSERT INTO gltrans (type,
				                                typeno,
				                                trandate,
				                                user,
				                                loccode,
				                                periodno,
				                                account,
				                                narrative,
				                                amount)
				                            VALUES (43,
				                                '" . $STNo . "',
				                                '" . $Datenow . "',
				                                '" . $_SESSION['UserID'] . "',
				                                '" . $_SESSION['UserStockLocation'] . "',
				                                " . $PeriodNo . ",
								'" .  $StockGLCode['adjglact'] . "',
				                                '" . $StockInfo['stockid'] . ' adjusted from  ' . $qrows['quantity'] . ' to ' .   $NewQty . "',
				                                " . -($GLCost) . ")";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL credit for the stock cost adjustment posting could not be inserted because');
		$DbgMsg = _('The following SQL to insert the GLTrans record was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	}


	$sql = "UPDATE stockcounts set finalised = 'Y', datefinal = '" . $Datenow . "'
							WHERE loccode = '" . $_SESSION['UserStockLocation'] . "'
							AND stockid = '" . $StockInfo['stockid'] . "'
							AND stockbatch = '" . $_SESSION['SKNumber'] . "'
							";
	$ErrMsg = _('Could not locstock because');
	$DbgMsg = _('The SQL used was');
	$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
	++$count;
	}
	unset($_SESSION['SKNumber']);
	unset($_SESSION['PHDATE']);
	//echo "<br>Records updated " . $count . "<br>";
} ///////////////////////////END FINALISE ////////////////////////////////////////////////////////

if ($_POST['NewStockCount']) {
	if (isset($_SESSION['SKNumber'])) {
		unset($_SESSION['SKNumber']);
	}
    $SQL = 'SELECT stockmaster.stockid,
    			stockmaster.materialcost,
    			stockmaster.department,
    			stockmaster.description,
    			stockmaster.kgs,
    			stockmaster.units,
    			stockmaster.categoryid,
    			locstock.quantity
    			FROM stockmaster
			INNER JOIN locstock
			ON stockmaster.stockid=locstock.stockid
			WHERE locstock.loccode="' . $_SESSION['UserStockLocation'] . '"';
    // echo "<br>sql 1" . $SQL . "<br>";
    $result = DB_query($SQL, $db);
    $QtyCount = 0;
    //echo "<br>Lcation " . $_SESSION['UserStockLocation'] . "<br>";
	$TypeNoField = "typeno" . $_SESSION['UserStockLocation'];
	$NextNo = GetNextTransNoTenderType(43, $db, $TypeNoField);
	$Stockbatch = $_SESSION['UserStockLocation'] . $NextNo;
	$_SESSION['SKNumber'] = $Stockbatch;
	//echo "<br>st 200 is  " . $_SESSION['SKNumber'] . "<br>";
	//echo "<br>stockbatch is " . $Stockbatch . "<br>";
//	echo "<br>SBBBBBBBBBB " . $_SESSION['SKNumber'] . "<br>";
	//$Stockbatch = GetNextTransNo(44, $db);
//	echo "<br>Batch nr " . $Stockbatch . "<br>";

	$Datenow = date("Y-m-d");
	$_SESSION['PHDATE'] = $Datenow;
    while ($myrow = DB_fetch_array($result)) {

        $sqlcheck = 'SELECT stockcounts.stockid,
					stockcounts.qtycounted,
					stockcounts.qoh,
					stockcounts.loccode,
					stockcounts.materialcost,
					stockcounts.stockbatch,
					stockcounts.variance
			FROM stockcounts
			WHERE finalised = "N"
			AND stockcounts.loccode="' . $_SESSION['UserStockLocation'] . '"
			AND stockcounts.stockbatch = "'.$myrow['stockid'].'"
			AND stockcounts.stockbatch = "'.$_SESSION['SKNumber'].'"';
        $resultdupcheck = DB_query($sqlcheck, $db);

        if (DB_num_rows($resultdupcheck) > 0) {
            /* Avoid duplicates */
            continue;
        }

        echo "<br>Inserting " . $myrow['stockid']. " in stock counts <br />";
	    $Finalised = "N";
        $sql = 'INSERT INTO stockcounts (stockbatch,
        							stockid,
									loccode,
									qtycounted,
									qoh,
									stockcountdate,
									finalised,
									materialcost,
									department,
									description,
									kgs,
									units,
									category
									)';
        $sql .= "		VALUES ('" . $Stockbatch . "',
						'" . $myrow['stockid'] . "',
						'" . $_SESSION['UserStockLocation'] . "',
						" . $QtyCount . ",
						" . $myrow['quantity'] . ",
						'" . $Datenow . "',
						'" . $Finalised . "',
						" . $myrow['materialcost'] . ",
						'" . $myrow['department'] . "',
						'" .addslashes($myrow['description']) . "',
						" . $myrow['kgs'] . ",
						'" . $myrow['units'] . "',
						'" . $myrow['categoryid'] . "'
						)";
        // echo "<br>sql is " . $sql . "<br>";
        $ErrMsg = _('The stock count line number') . ' ' . $i . ' ' . _('could not be entered because');
        $DbgMsg = _('The SQL used was');
        $EnterResult = DB_query($sql, $db, $ErrMsg,$DbgMsg);
        // prnMsg( _('The stock code entered on line') . ' ' . $i . ' ' . _('is not a part code that has been added to the stock check file') . ' - ' . _('the code entered was') . ' ' . $_POST[$StockID] . '. ' . _('This line will have to be re-entered'),'warn');
        // $InputError = True;
    }
}

if ($_POST['UpdateStockCountCode']) {

    $SQL = 'SELECT stockmaster.stockid,
    			stockmaster.materialcost,
    			stockmaster.department,
    			stockmaster.description,
    			stockmaster.kgs,
    			stockmaster.units,
    			stockmaster.categoryid,
    			locstock.quantity
    			FROM stockmaster
			INNER JOIN locstock
			ON stockmaster.stockid=locstock.stockid
			WHERE locstock.loccode= "'. $_SESSION['UserStockLocation'] . '"
                        AND stockmaster.categoryid ="'.$_POST['FromCriteria'].'"';
  
    $result = DB_query($SQL, $db);
    $QtyCount = 0;

    while ($myrow = DB_fetch_array($result)) {
            $sql = "UPDATE stockcounts SET category = '" . $myrow['categoryid']. "'
						 WHERE stockid = '" . $myrow['stockid']. "'";

        $ErrMsg = _('The stock count line number') . ' ' . $i . ' ' . _('could not be entered because');
        $EnterResult = DB_query($sql, $db, $ErrMsg);
        echo "Catergory for ".$myrow['stockid']." Changed From -> ".$myrow['categoryid'].", To ".$_POST['FromCriteria']. "<br />";
    }
    unset($_POST['FromCriteria']);
    unset($_POST['UpdateStockCountCode']);
}/*Update categories*/

if ($_POST['Cancel']) {
	$sql = "delete from stockcounts where finalised = 'N' AND loccode='" . $_SESSION['UserStockLocation'] . "'";
	$ErrMsg = _('Could not cancel because');
        $DbgMsg = _('The SQL used was');
	$EnterResult = DB_query($sql, $db, $ErrMsg,$DbgMsg);
}

/////////////////////////////////END NEWSTOCKCOUNT///////////////////////////////////////////
echo "<form action='" . $_SERVER['PHP_SELF'] . "' method=post>";

echo "<br>";
if ($_POST['DelNC']) {
	$sql = "delete from stockcounts where finalised = 'N' AND loccode='" . $_SESSION['UserStockLocation'] . "' and counted='N'";
	$ErrMsg = _('Could not delete entries');
	$EnterResult = DB_query($sql, $db, $ErrMsg);
}
/* Check if entries in stockcounts exist */

if ($_POST['UpdateCounts']) {
    /*Get Old Session*/
    // $_SESSION['SKNumber'] = "";
    //print_r($_SESSION['SKNumber']);
   //// $Datenow = date("Y-m-d");
//    $_SESSION['PHDATE'] = $Datenow;
//      foreach ($_POST['StockId'] as $key=>$val){
//		echo "<br>Key is " . $key . "<br>";
//		echo "<br>Val is " . $val . "<br>";
//		echo "<br>QTY is " . $_POST['Stockqoh'][$key] . "<br>";
//		echo "<br>Count is " . $_POST['StockCount'][$key] . "<br>";
//                echo "<br>Count is " . $_SESSION['SKNumber']. "<br>";
//	}

//  //  foreach ($_POST['StockId'] as $key => $val) {
//    $sqlGet = "SELECT  stockbatch  FROM stockcounts
//				WHERE loccode ='" . $_SESSION['UserStockLocation'] . "'
//                                AND counted = 'Y'
//				AND finalised='N'
//                                AND stockcountdate ='".$_SESSION['PHDATE']."'";
//        $results = DB_query($sqlGet, $db);
//        $myrow = DB_fetch_array($results);
//       //if($_SESSION['SKNumber'] != $myrow['stockbatch'])
//       //{
//            $_SESSION['SKNumber'] = $myrow['stockbatch'];
//             $sqlEmpty = "UPDATE stockcounts SET stockbatch = '" . $_SESSION['SKNumber'] . "'
//							WHERE loccode ='" . $_SESSION['UserStockLocation'] . "'
//							AND stockcountdate ='".$_SESSION['PHDATE']."'
//							AND finalised='N'
//                                                        AND stockbatch = '' ";
//            $EnterResultEmpty = DB_query($sqlEmpty, $db, $ErrMsg);
//       //}
//  //  }
//    // echo "<br>????????????????????????????????????????????????<br>";
//   print_r($_SESSION['SKNumber']);*/
    foreach ($_POST['StockId'] as $key => $val) {
         ///echo "<br>Count is " . $_POST['StockCount'][$key] . "<br>";
    	if ($_POST['StockCount'][$key] != "") {


        //if ($_POST['StockCount'][$key] == "") {
        //    $_POST['StockCount'][$key] = 0;
       // }
        /*2013-03-08 - Swapped this around, discussion with corrine and louise*/
	$UpdateVariance =  $_POST['StockCount'][$key] - $_POST['Qoh'][$key];
    	//echo "<br>Qcount " . $_POST['QCount'][$key] . "<br>";
    	//echo "<br>Qoh " . $_POST['Qoh'][$key] . "<br>";
    	//echo "<br>Variance is " . $UpdateVariance . "<br>";
        $sql = "UPDATE stockcounts
							SET qtycounted = " . $_POST['StockCount'][$key] . ",
							variance = " . $UpdateVariance . ",
							counted = 'Y'
							WHERE stockid = '" . $val . "'
							AND loccode ='" . $_SESSION['UserStockLocation'] . "'
							AND stockbatch = '" . $_SESSION['SKNumber'] . "'
							AND finalised='N'";
         //echo "<br>sql 271 is " . $sql . "<br>";
        $ErrMsg = _('The stock count for') . ' ' . $val . ' ' . _('could not be entered because');
        $EnterResult = DB_query($sql, $db, $ErrMsg);
    }//END IF
    } // end of loop
    prnMsg($Added . _(' Stock Counts Entered'), 'success');
    
  
    unset($_POST['EnterCounts']);
    $_POST['SelectLocation'] = $_SESSION['UserStockLocation'];
    //$_POST['UpdateCounts'] = true;
	//$_POST['UR'] = TRUE;
} // end of update button hit

////////////////////////	END UPDATE ////////////////////////////////////
	$sql = "SELECT * FROM stockcounts where finalised = 'N'
			AND loccode = '" . $_SESSION['UserStockLocation'] . "'
			";
	//echo "<br>sql 307  is  " . $sql . "<br>";
	$ErrMsg = _('The stock count line number') . ' ' . $i . ' ' . _('could not be entered because');
	$CountRow = DB_query($sql, $db, $ErrMsg);

//if (DB_num_rows($CountRow) > 0 && !isset($_POST['UpdateCounts'])) {
    //$_POST['UpdateCounts'] = true;
//}
if (DB_num_rows($CountRow) > 0) {
    echo '<table border = 1>';
	//if ($_SESSION['SKNumber']) {
		//echo '<th colspan=2>STOCK TAKE WITH NUMBER ' . $_SESSION['SKNumber'] . ' STARTED ' . $_SESSION['PHDATE'] . '</th>';
	//}else{
     $_SESSION['SKNumber']  = "";
              $sqlGet = "SELECT  stockbatch  FROM stockcounts
				WHERE loccode ='" . $_SESSION['UserStockLocation'] . "'
                                AND finalised = 'N'
                                ";
        $results = DB_query($sqlGet, $db);
       $myrow = DB_fetch_array($results);
       //AND stockcountdate ='2012-02-24'
            $_SESSION['SKNumber'] = $myrow['stockbatch'];
       // }
        echo '<tr><td>' . _('Stock Batch') . ":</td><td>" . $_SESSION['SKNumber'] . '</td></tr>';
        echo '<tr><td>' . _('Stock Check Counts at Location') . ":</td><td>" . $_SESSION['LocationRecord']['locationname'] . '</td></tr>';
	echo '<tr><td>' . _('Category Code') . ':</font></td><td><select name=FromCriteria>
											<option valie="All">All</option>';

	$sql='SELECT categoryid, categorydescription FROM stockcategory ORDER BY categoryid';
	$CatResult= DB_query($sql,$db);
	While ($myrow = DB_fetch_array($CatResult)){
		echo "<option value='" . $myrow['categoryid'] . "'>" . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
	}
	echo '</select></td></tr>';
	 echo '<tr><th colspan=2 class=tdl><b>Select search method:</b></th></tr>';
    echo '<tr><td>Start Character:</td><td><input type="text" name="SChar" size="3" value="' . $_POST['SChar']. '"></td></tr>';
    echo '<tr><td>End Character:</td><td><input type="text" name="EChar" size="3" value="' . $_POST['EChar']. '"></td></tr>';
    echo '<tr><td colspan=2><b>OR</b></td></tr>';
    echo '<tr><td>Combo(Seperate with space):</td><td><input type="text" name="Combo" size="50" value="' . $_POST['Combo']. '"></td></tr>';
	if ($_GET['Continue'] || $_POST['Qohc'] || $_GET['Show'] || $_POST['NewStockCount']) {
		echo '<tr><th colspan=2 class=tdl><b>Additional options:</b></th></tr>';
		echo '<tr><td>Show Qoh:</td><td><input type="checkbox" name="Qohc" checked></td></tr>';
		echo '<tr><td>Show Variances Only:</td><td><input type="checkbox" name="Var"></td></tr>';
		echo '<tr><td>Show Counted Only:</td><td><input type="checkbox" name="Counted"></td></tr>';
                echo '<tr><td>Dont Show Zero:</td><td><input type="checkbox" name="ShowZeroValues"></td></tr>';
		echo '<tr><td>Display Variance in Value greater than:</td><td><input type="text" name="HVar"></td></tr>';
	}



    if ($_SESSION['UserStockLocation']) {
    	if (!$_POST['Qohc'] && !$_GET['Show'] && !$_GET['Continue'] && !$_POST['NewStockCount']) {
    		echo '<tr><td></td><td class=tdr><a href="'  . $_SERVER['PHP_SELF'] . '?Continue=yes">Continue</a></td></tr>';
    	} else {
    		echo '<tr><td class="tdr" colspan="2"><input type="submit" name="SelectLocation" value="Select"></td></tr>';
    		
              //  if(isset($_POST['FromCriteria']))
              //  {
                   // echo "<tr><td class='tdr' colspan='2'><a href='" . $rootpath . '/' . $_SESSION['reports_dir'] . "/StockQties.csv?'".SID."catergory=".$_POST['FromCriteria']."'>" . _('click here') . '</a> ' . _('to export csv') . '</td></tr>';
               // }else{
                      echo "<tr><td class='tdr'><a href='" . $rootpath . '/' . $_SESSION['reports_dir'] . "/StockQties.csv'>" . _('click here') . '</a> ' . _('to export csv') . '</td>';
                      echo "<td class='tdr'><a href='" . $rootpath ."/PDFStockCounts.php?". SID."Criteria=".$_POST['FromCriteria']."&ShowZeroValues=".$_POST['ShowZeroValues']."'>" . _('click here') . '</a> ' . _('to Print PDF format') . '</td>
                         </tr>';               
               // }
    	}

    }
    echo '</table>';
    echo '<table cellpadding=2 BORDER=1>';
    if ($_POST['SelectLocation'] && !$_POST['UpdateCounts']) {
    	$_POST['UR'] == false;

        $sql = 'SELECT stockcounts.stockid,
						stockcounts.qoh,
						stockcounts.qtycounted,
						stockcounts.variance,
						stockcounts.materialcost,
						stockcounts.counted,
						stockcounts.finalised,
						stockcounts.stockbatch,
						stockcounts.stockcountdate,
						stockmaster.description AS masterid
						';


        $sql .= ' FROM stockcounts
					INNER JOIN stockmaster ON stockcounts.stockid = stockmaster.stockid';
        $sql .= ' WHERE finalised = "N"
				AND stockcounts.loccode ="' . $_SESSION['UserStockLocation'] . '"
				';
//echo "<br>sqlxxxxx is  " . $sql . "<br>";
        if(isset($_POST['ShowZeroValues'])&& $_POST['ShowZeroValues']!="")
        {
            $sql .= ' AND stockcounts.qoh <> 0 ';
        }
        
        if($_POST['HVar'])
        {
            $sql .= ' AND abs(stockcounts.qoh - stockcounts.qtycounted)*stockcounts.materialcost > '. $_POST['HVar'];
        }
        
        if ($_POST['SChar'] != "" && $_POST['EChar'] != "" && $_POST['Combo'] == "") {
            $s = $_POST['SChar'];
            $e = $_POST['EChar'];
            $sql .= ' AND stockcounts.stockid REGEXP "^[' . $s . '-' . $e . ']"';
        }
        if ($_POST['SChar'] != "" && $_POST['EChar'] == "" && $_POST['Combo'] == "") {
            $sql .= ' AND stockcounts.stockid like "' . $_POST['SChar'] . '%"';
        }
    	//check if combo was used, look for spaces
    	if ($_POST['Combo'] != "") {
    	//	echo "Under combo";
    		$CheckForSpace = strstr($_POST['Combo'], " ");
    		if ($CheckForSpace) {
    			$Code = explode(" ", $_POST['Combo']);
    			//echo "<br>Code is " . $_POST['Combo'] . "<br>";
    			//echo "<br>Code1 is " . print_r($Code) . "<br>";
    		} else {
    			$Code[] = $_POST['Combo'];
    			//echo "<br>Combo is  " . print_r($Code) . "<br>";
    		}

    		foreach ($Code as $key=>$val){
    			if ($key == 0) {
    				$sql .= ' AND (stockcounts.stockid like "' . $val . '%"';
    			} else {
    				$sql .= ' OR stockcounts.stockid like "' . $val . '%"';
    			}

    		}
    		$sql .= ') ';
    	}

        if (isset($_POST['Var'])) {
            $sql .= ' AND stockcounts.qtycounted != qoh';
        }
    	if ($_POST['Counted'] || isset($_POST['Var'])) {
    		$sql .= ' AND stockcounts.counted = "Y"';
    	}
    	if ($_POST['FromCriteria'] != "All") {
    		$sql .= ' AND 	stockcounts.category = "' . $_POST['FromCriteria'] . '"';
    	}
		$sql .= ' order by stockid';
//echo "<br>sql is  " . $sql . "<br>";
//echo "<br>more " . $_POST['CostMore'] . "<br>";
//echo "<br>less " . $_POST['CostLess'] . "<br>";
	//echo "<br>sql is " . $sql . "<br>";
        $result = DB_query($sql, $db);

    //echo "<br>sql 396 is " . $sql . "<br>";

    echo "<tr>
		<th></th>
		<th>" . _('Stock Code') . "</th>";
		if ($_POST['Qohc']) {
		echo "<th>" . _('Quantity') . "</th>";
		}

		echo "<th>" . _('Count') . "</th>";
    	if (!$_POST['Qohc']) {
    		echo "<th>" . _('Description') . "</th>";
    	}
		if ($_POST['Qohc']) {
			echo "<th>" . _('Variance') . "</th>
			<th>" . _('Cost') . '</th></tr>';
		}


    $Count = 0;
    $TotalCost = 0;
    $TotalVariance = 0;
    while ($myrow = mysql_fetch_array($result)) {
    	//echo "<br>des is  " . $myrow['masterid'] . "<br>";
    	if (!$_SESSION['SKNumber'] || $_SESSION['SKNumber'] == "") {
    		$_SESSION['SKNumber'] = $myrow['stockbatch'];
    	}
    //   if($_SESSION['SKNumber'] !=  $myrow['stockbatch'])
       // {
           // $_SESSION['SKNumber'] = $myrow['stockbatch'];
       // }
        
        if (!$_SESSION['PHDATE'] || $_SESSION['PHDATE'] == "") {
            $_SESSION['PHDATE'] = $myrow['stockcountdate'];
    	}
    	//echo "<br>batch is " . $myrow['stockbatch'] . "<br>";
    	//echo "<br>Variance " . $myrow['variance'] . "<br>";
    	if ($myrow['counted'] == "Y") {
    		//echo "<br>Now at line 402 ";
    		$Variance = $myrow['qoh'] - $myrow['qtycounted'];
    		//echo "<br>qtyc is " . $myrow['qtycounted'] . "<br>";
    		//echo "<br>qoh is " . $myrow['qoh'] . "<br>";
    		//echo "<br>variance " . $Variance . "<br>";
    		$Cost = $myrow['materialcost'] * $Variance;
    		//echo "<br>cost is  " . $Cost . "<br>";
    		$bgcolor = "yellow";
    	}


        if ($myrow['qtycounted'] == 0) {
            $myrow['qtycounted'] = "";
        }
    	if ($_POST['HVar'] > 0 && $_POST['HVar'] != "" && abs($Variance) > $_POST['HVar'] && $myrow['counted'] == 'Y') {
    		$bgcolor = "red";

    	}
    	if ($_POST['HVar'] < 0 && $_POST['HVar'] != "" && $Variance < $_POST['HVar'] && $myrow['counted'] == 'Y') {
    		$bgcolor = "red";

    	}
		if (!$_POST['Qohc']) {
			$Qoh = "";
		} else {
			$Qoh = number_format($myrow['qoh'], 2);
		}
    	if ($_POST['Var']) {
    		if ($Qoh - $myrow['qtycounted'] > 0 && $myrow['counted'] == 'Y') {
    			$bgcolor = "red";
    		}
    	}
//echo "<br>Var " . $Variance . "<br>";
//echo "<br>Hvar " . $_POST['HVar'] . "<br>";
			echo "<tr>
			<td>$Count</td>
			<td>$myrow[stockid]</td>";

    	if ($_POST['Qohc']) {
    		//$myrow['qtycounted'] = number_format($myrow['qtycounted'], 2);
    		echo "<td width=\"100\">" . $Qoh . "</td>";
    		echo "<td ><input type=TEXT name=\"StockCount[]\" maxlength=20 size=20 value=\"$myrow[qtycounted]\"></td>";
    	} else {
    		echo "<td width=\"100\"></td>";
    	}
    	if (!$_POST['Qohc']) {
    		echo "<td>$myrow[masterid]</td>";
    	}
    	if ($_POST['Qohc']) {
    		echo "<td width=\"100\" bgcolor=" . $bgcolor . ">" . number_format($Variance, 2) . "</td>
					<td width=\"100\" class=\"tdr\">" . number_format($Cost, 2) . "</td>";
    	}

			echo "<input type=\"hidden\" name=\"StockId[]\" value=\"$myrow[stockid]\">
			<input type=\"hidden\" name=\"QCount[]\" value=\"$myrow[qtycounted]\">
			<input type=\"hidden\" name=\"Qoh[]\" value=\"$myrow[qoh]\"></td></tr>";

			++$Count;
    	if ($myrow['counted'] == "Y") {
    		//echo "<br>Now at line 436 ";
    		$TotalCost = $TotalCost + $Cost;
    		$TotalVariance = $TotalVariance + $Variance;
    		$Variance = 0;
    		$Cost = 0;
    		//echo "<br>total cost  " . $TotalCost . "<br>";
    	}

    	$bgcolor = "";
        // echo "<input type=\"hidden\" name=\"StockId[]\" value=\"$myrow[stockid]\"></td></tr>";
    }
    // <td>$myrow[stockid]<input type=TEXT name='StockID_" . $i . "' maxlength=20 size=20></td>
	if ($_POST['Qohc']) {
    	if ($TotalCost > 0) {
		echo '<tr><td></td><td></td><td></td><td>Stock Loss</td><td>' . number_format($TotalVariance, 2) . '</td><td bgcolor=red class="tdr">' . number_format($TotalCost, 2) . '</td></tr>';
	}		
  	if ($TotalCost < 0) {
		echo '<tr><td></td><td></td><td></td><td>Stock Gain</td><td>' . number_format($TotalVariance, 2) . '</td><td bgcolor=yellow class="tdr">' . number_format($TotalCost, 2) . '</td></tr>';
				}       
		echo "<tr>";
                if(in_array($StockAllowUpdate,$_SESSION['AllowedPageSecurityTokens']))
                {
                        echo "<td bgcolor=\"red\"><input type=submit name='Finalise' VALUE='" . _('Finalise') . "' onClick='return confirmDelete();'></td>";
                        echo "<td></td><td><input type=submit name='Cancel' VALUE='" . _('Cancel Stocktake') . "'></td><td></td>";
                }
		
                   echo " <td><input type=submit name='UpdateCounts' VALUE='" . _('Update') . "'></td>";
                 if(in_array($StockAllowUpdate,$_SESSION['AllowedPageSecurityTokens']))
                 {
                   echo "<td><input type=submit name='DelNC' VALUE='" . _('Delete not counted') . "' onClick='return confirmDelete();'>";
                 }
                 echo "</tr>";
	}




    echo '</table></form>';
}
} else {
    
    echo "<form action='" . $_SERVER['PHP_SELF'] . "' method=post>";
    echo '<table>';
    echo "<tr><th>There ar no open stock take/adjust sessions.</th></tr>
			<tr><td bgcolor=\"red\"><input type=submit name='NewStockCount' VALUE='" . _('Create New Session for ' . $_SESSION['LocationRecord']['locationname']) . "'></td>";
} ///END If unfinalised records exist
// END OF action=ENTER
if(in_array($StockAllowUpdate,$_SESSION['AllowedPageSecurityTokens']))
{
  echo "</tr><tr><th>Please note: for admin user only.</th></tr>
			<tr><td bgcolor=\"red\"><input type=submit name='UpdateStockCountCode' VALUE='" . _('Update Category FOR: ' . $_SESSION['LocationRecord']['locationname']) . "'></td>";
}
echo '</tr></table></form>';
include('includes/footer.inc');
//print_r($_SESSION['AllowedPageSecurityTokens']);
?>