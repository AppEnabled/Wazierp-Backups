<style type="text/css">
/*--Tooltip Styles--*/
.tip {
	color: #fff;
	background:#1d1d1d;
	display:none; /*--Hides by default--*/
	padding:10px;
	position:absolute;	z-index:1000;
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
}
.container {width: 960px; margin: 0 auto; overflow: hidden;}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script> 
<script type="text/javascript">
$(document).ready(function() {
	//Tooltips
	$(".tip_trigger").hover(function(){
		tip = $(this).find('.tip');
		tip.show(); //Show tooltip
	}, function() {
		tip.hide(); //Hide tooltip		  
	}).mousemove(function(e) {
		var mousex = e.pageX + 20; //Get X coodrinates
		var mousey = e.pageY + 20; //Get Y coordinates
		var tipWidth = tip.width(); //Find width of tooltip
		var tipHeight = tip.height(); //Find height of tooltip
		
		//Distance of element from the right edge of viewport
		var tipVisX = $(window).width() - (mousex + tipWidth);
		//Distance of element from the bottom of viewport
		var tipVisY = $(window).height() - (mousey + tipHeight);
		  
		if ( tipVisX < 20 ) { //If tooltip exceeds the X coordinate of viewport
			mousex = e.pageX - tipWidth - 20;
		} if ( tipVisY < 20 ) { //If tooltip exceeds the Y coordinate of viewport
			mousey = e.pageY - tipHeight - 20;
		} 
		tip.css({  top: mousey, left: mousex });
	});
});

</script>

<?php
/* $Revision: 1.49 $ */
$StockUpdatePermission = 30;
// $PageSecurity = 1;
//$PricesSecurity = 1;
//<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script> 
include('includes/session.inc');

$title = _('Search Inventory Items');

include('includes/header.inc');
unset($result);
$msg = '';
//echo "<br>Stock location is " . $_SESSION['StockLocation'] . "<br>";
//echo "<br>user stoc loc " . $_SESSION['UserStockLocation'] . "<br>";

if ($_POST['Location']) {
	$_SESSION['StockLocation'] = $_POST['Location'];
}

if (isset($_GET['StockID'])) {
    //The page is called with a StockID
    $_GET['StockID'] = trim(strtoupper($_GET['StockID']));
    $_POST['Select'] = trim(strtoupper($_GET['StockID']));
}

if (isset($_GET['NewSearch'])) {
    unset($StockID);
    unset($_SESSION['SelectedStockItem']);
    unset($_POST['Select']);
}

if (!isset($_POST['PageOffset'])) {
    $_POST['PageOffset'] = 1;
} else {
    if ($_POST['PageOffset'] == 0) {
        $_POST['PageOffset'] = 1;
    }
}

if (isset($_POST['StockCode'])) {

    $separate = explode("_QTY:_",trim($_POST['StockCode']));
   if($_POST['StockCode']!=$separate[0])
   {
        $_POST['StockCode'] = $separate[0];
   }else{
       $_POST['StockCode'] = trim($_POST['StockCode']);
   }
}

// Always show the search facilities

$SQL='SELECT categoryid,
        categorydescription
    FROM stockcategory
    ORDER BY categorydescription';

$result1 = DB_query($SQL,$db);
if (DB_num_rows($result1) == 0) {
    echo '<p><font size=4 color=red>' . _('Problem Report') . ':</font><br>' . _('There are no stock categories currently defined please use the link below to set them up');
    echo '<br><a href="' . $rootpath . '/StockCategories.php?' . SID .'">' . _('Define Stock Categories') . '</a>';
    exit;
}

echo '<form action="'. $_SERVER['PHP_SELF'] . '?' . SID .'" method=post onload=(document.myform.barCode.focus())>';
echo '<b>' . $msg . '</b>';
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . _('Search for Inventory Items');
echo '<table><tr>';
echo '<td>'. _('In Stock Category') . ':';
echo '<select name="StockCat">';

if (!isset($_POST['StockCat'])) {
	$_POST['StockCat'] = "";
}

if ($_POST['StockCat'] == "All") {
	echo '<option selected value="All">' . _('All');
} else {
	echo '<option value="All">' . _('All');
}

while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected VALUE="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
	} else {
		echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
	}
}

echo '</select>';
echo '<td>'. _('Enter partial') . '<b> ' . _('Description') . '</b>:';


if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size=20 maxlength=25>';
} else {
	echo '<input type="text" name="Keywords" size=20 maxlength=25>';
}

echo '</td></tr><tr><td></td>';

echo '<td><font size 3><b>' . _('OR') . '</b></font>' . _('Enter partial') .' <b>'. _('Stock Code') . '</b>:';
//echo '<td>';

if (isset($_POST['StockCode'])) {
	echo '<input type="text" name="StockCode" value="'. $_POST['StockCode'] . '" size=15 maxlength=18 onkeyup="ajax_showOptions(this,\'getItem\',event)" autocomplete=off>';
} else {
	echo '<input type="text" name="StockCode" size=15 maxlength=18 onkeyup="ajax_showOptions(this,\'getItem\',event)" autocomplete=off>';
}

echo '</td></tr>';
echo '<td></td><td><font size 3><b>' . _('OR') . '</b></font>' . _('Enter') .' <b>'. _('Barcode') . '</b>:';
//echo '<td>';

if (isset($_POST['barCode'])) {
	echo '<input type="text" name="barCode" value="'. $_POST['barCode'] . '"  size=30 maxlength=18>';
} else {
	echo '<input type="text" name="barCode"   size=30 maxlength=18>';
}

echo '</td></tr>';
/* If global session = Yes PDT 22 JUne 2010 */

if (isset($_SESSION['Global'])) {
$sql = 'SELECT loccode,
				locationname
		FROM locations';
$LocnResult = DB_query($sql,$db);
?><td align="right"></td><td><b><?php echo _('AND'); ?> </b><?php echo _(' Select'); ?> <?php echo _('Location'); ?>:</td>
		<td><select name='Location'>
		<?php
while ($myrow=DB_fetch_array($LocnResult)){

	if (isset($_SESSION['UserStockLocation']) and $myrow['loccode'] == $_SESSION['UserStockLocation']){

		echo "<option selected value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];

	} else {
		echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];

	}

}
}
echo '</table><br>';
echo '<div class="centre"><input type=submit name="Search" value="'. _('Search Now') . '"></div><hr>';
//echo '<br>'.$_POST['Search'].'<br>';

// end of showing search facilities

// query for list of record(s)

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
    if(isset($result)){
        unset($result);
    }
    $SQL = "";
//echo "<br> line 148 <br>";
    if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
        // if Search then set to first page
        $_POST['PageOffset'] = 1;
    }

    if ($_POST['Keywords'] AND $_POST['StockCode']) {
        $msg=_('Stock description keywords have been used in preference to the Stock code extract entered');
    }
	/*If global session not set, restrict to loccation PDT 9July 10 */


    if ($_POST['Keywords']) {
        //insert wildcard characters in spaces
        $_POST['Keywords'] = strtoupper($_POST['Keywords']);
        $i = 0;
        //$SearchString = '%';
        $Wildcast = '%';
//        while (strpos($_POST['Keywords'], ' ', $i)) {
//            $wrdlen = strpos($_POST['Keywords'], ' ', $i) - $i;
//            $SearchString = $SearchString . substr($_POST['Keywords'], $i, $wrdlen) . '%';
//            $i = strpos($_POST['Keywords'], ' ', $i) + 1;
//        }$Wildcast. 
        //$SearchString = $SearchString. substr($_POST['Keywords'], $i) . '%';
        $SearchString = $_POST['Keywords'] . '%';
        if ($_POST['StockCat'] == 'All' && $SearchString != ''){
         echo "1 <br />";
           
//           $SQL = "SELECT *
//                FROM stockmaster
//                  
//                WHERE (stockmaster.description  LIKE  '%".$SearchString."'
//                 OR UPPER(stockmaster.description) LIKE '%".$SearchString."'";
//AND locstock.loccode='" . $_SESSION['UserStockLocation'] . "'
   $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    SUM(locstock.quantity) AS qoh,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                FROM stockmaster,
                    locstock
                WHERE stockmaster.stockid=locstock.stockid
                AND locstock.loccode='" . $_SESSION['UserStockLocation'] . "'
                AND stockmaster.description  LIKE  '%".$SearchString."'
                AND  stockmaster.mbflag <> 'G'
               GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                ORDER BY stockmaster.stockid
              ";
            
         //   SUM( ORDER BY stockmaster.stockid
//              GROUP BY stockmaster.stockid,
//                    stockmaster.description,
//                    stockmaster.units,
//                    stockmaster.mbflag,
//                    stockmaster.decimalplaces
        } else {
           echo "2";
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    SUM(locstock.quantity) AS qoh,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                FROM stockmaster,
                    locstock
                WHERE stockmaster.stockid=locstock.stockid
                AND locstock.loccode='" . $_SESSION['UserStockLocation'] . "'
                AND description " .  LIKE . " '$SearchString'
                AND categoryid='" . $_POST['StockCat'] . "'
                AND  stockmaster.mbflag <> 'G'
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                ORDER BY stockmaster.stockid";
        }
    	//echo "<br>sql line 196 is " . $SQL . "<br>";
    }
    
    if($_POST['StockCat'] && $_POST['StockCat'] != 'All'){
        echo "2.1";
           $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    SUM(locstock.quantity) AS qoh,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                FROM stockmaster,
                    locstock
                WHERE stockmaster.stockid=locstock.stockid
                AND locstock.loccode='" . $_SESSION['UserStockLocation'] . "'
                
                AND categoryid='" . $_POST['StockCat'] . "'
                AND  stockmaster.mbflag <> 'G'
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                ORDER BY stockmaster.stockid";
        
    }
    
    if ($_POST['StockCode'] && $_POST['StockCode']!= "") {
echo "3".$_POST['barCode'];

        $_POST['StockCode'] = strtoupper($_POST['StockCode']);
        if ($_POST['StockCat'] == 'All') {
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.mbflag,
                    SUM(locstock.quantity) AS qoh,
                    stockmaster.units,
                    stockmaster.decimalplaces
                FROM stockmaster,
                    locstock
                WHERE stockmaster.stockid=locstock.stockid
                AND locstock.loccode='" . $_SESSION['UserStockLocation'] . "'
                AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
                AND  stockmaster.mbflag <> 'G'
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                ORDER BY stockmaster.stockid";
//              echo "<br>sql line 235 is " . $SQL . "<br>";
        } else {
            echo "4";
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.mbflag,
                    sum(locstock.quantity) as qoh,
                    stockmaster.units,
                    stockmaster.decimalplaces
                FROM stockmaster,
                    locstock
                WHERE stockmaster.stockid=locstock.stockid
                AND locstock.loccode='" . $_SESSION['UserStockLocation'] . "'
                AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
                AND categoryid='" . $_POST['StockCat'] . "'
                AND  stockmaster.mbflag <> 'G'
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                ORDER BY stockmaster.stockid";
        }
//	echo "<br>sql lin 239 is " . $SQL . "<br>";
    }elseif ($_POST['barCode'] != ''  && $_POST['StockCode'] == "" )
    {
        echo "5";
//        echo "If statement....".$_POST['barCode'];
            if ($_POST['StockCat'] == 'All') {
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
                AND locstock.loccode='" . $_SESSION['UserStockLocation'] . "'
                AND (stockmaster.barcode= '" . $_POST['barCode'] . "' OR stockmaster.barcodetwo= '".$_POST['barCode'] ."')
                AND  stockmaster.mbflag <> 'G'
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                ORDER BY stockmaster.stockid";

        } else {
         echo "6";
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.mbflag,
                    stockmaster.barcode,
                    sum(locstock.quantity) as qoh,
                    stockmaster.units,
                    stockmaster.decimalplaces
                FROM stockmaster,
                    locstock
                WHERE stockmaster.stockid=locstock.stockid
                AND locstock.loccode='" . $_SESSION['UserStockLocation'] . "'
                AND (stockmaster.barcode= '" . $_POST['barCode'] . "' OR stockmaster.barcodetwo= '".$_POST['barCode'] ."')
                AND categoryid='" . $_POST['StockCat'] . "'
                AND  stockmaster.mbflag <> 'G'
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                ORDER BY stockmaster.stockid";
        }

    }

    elseif (!isset($_POST['StockCode']) AND $_POST['barCode']== '' &&$_POST['StockCode']=='' AND !isset($_POST['Keywords']) && !isset($_POST['barCode'])) {
       echo "7";
        if ($_POST['StockCat'] == 'All'){
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.mbflag,
                    SUM(locstock.quantity) AS qoh,
                    stockmaster.units,
                    stockmaster.decimalplaces
                FROM stockmaster,
                    locstock
                WHERE stockmaster.stockid=locstock.stockid
                AND locstock.loccode='" . $_SESSION['UserStockLocation'] . "'
                AND  stockmaster.mbflag <> 'G'
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                ORDER BY stockmaster.stockid";
        } else {
         echo "8";
            $SQL = "SELECT stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.mbflag,
                    SUM(locstock.quantity) AS qoh,
                    stockmaster.units,
                    stockmaster.decimalplaces
                FROM stockmaster,
                    locstock
                WHERE stockmaster.stockid=locstock.stockid
                AND locstock.loccode='" . $_SESSION['UserStockLocation'] . "'
                AND categoryid='" . $_POST['StockCat'] . "'
                AND  stockmaster.mbflag <> 'G'
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.decimalplaces
                ORDER BY stockmaster.stockid";
        }
    }
   echo "<br>Sql line 282 is " . $SQL . "<br>";
    $ErrMsg = _('No stock items were returned by the SQL because');
    $DbgMsg = _('The SQL that returned an error was');
    $result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

    if (DB_num_rows($result) == 0) {
        prnMsg(_('No stock items were returned by this search please re-enter alternative criteria to try again'),'info');
    	exit;
    } elseif (DB_num_rows($result) == 1) {
        /* autoselect it
         * to avoid user hitting another keystroke */
        $myrow = DB_fetch_row($result);
        $_POST['Select'] = $myrow[0];
    	$_POST['LocCode'] = $_SESSION['UserStockLocation'];
//        echo "<br>362".$_POST['Select']."<br>";
    }
    unset($_POST['Search']);
    unset($_POST['StockCode']);
    unset($_POST['Keywords']);
    unset($_POST['barCode']);
}
/* end query for list of records */

/* display list if there is more than one record */

if (isset($result) AND !isset($_POST['Select'])) {
//echo "<br>Now at line 304 ".$_POST['Select']."<br>";
    $ListCount = DB_num_rows($result);
    if ($ListCount > 0) {
    // If the user hit the search button and there is more than one item to show

        $ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);

        if (isset($_POST['Next'])) {
            if ($_POST['PageOffset'] < $ListPageMax) {
                $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
            }
        }

        if (isset($_POST['Previous'])) {
            if ($_POST['PageOffset'] > 1) {
                $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
            }
        }

        if ($_POST['PageOffset'] > $ListPageMax) {
            $_POST['PageOffset'] = $ListPageMax;
        }
        if ($ListPageMax > 1) {
            echo "<div class='centre'><p>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';

            echo '<select name="PageOffset">';

            $ListPage=1;
            while ($ListPage <= $ListPageMax) {
                if ($ListPage == $_POST['PageOffset']) {
                    echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
                } else {
                    echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
                }
                $ListPage++;
            }
            echo '</select>
                <input type=submit name="Go" value="' . _('Go') . '">
                <input type=submit name="Previous" value="' . _('Previous') . '">
                <input type=submit name="Next" value="' . _('Next') . '">';
            echo '<p></div>';
        }

        echo '<table cellpadding=2 colspan=7 border=1>';
        $tableheader = '<tr>
                    <th>' . _('Code') . '</th>
                    <th>' . _('Description') . '</th>
                    <th>' . _('Total Qty On Hand') . '</th>
                    <th>' . _('Units') . '</th>
                </tr>';
        echo $tableheader;

        $j = 1;

        $k = 0; //row counter to determine background colour

    $RowIndex = 0;

    if (DB_num_rows($result) <> 0) {
        DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
    }

        while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

            if ($k == 1) {
                echo '<tr class="EvenTableRows">';
                $k = 0;
            } else {
                echo '<tr class="OddTableRows">';
                $k++;
            }

            if ($myrow['mbflag'] == 'D') {
                $qoh = 'N/A';
            } else {
                $qoh = $myrow["qoh"];
            }

            printf("<td><input type=submit name='Select' value='%s'</td>
                <td>%s</td>
                <td class='number'>%s</td>
                <td>%s</td>
                </tr>",
                $myrow['stockid'],
                //$myrow['loccode'],
                $myrow['description'],
                $qoh,
                $myrow['units']);

            $j++;
            if ($j == 20 AND ($RowIndex+1 != $_SESSION['DisplayRecordsMax'])) {
                $j = 1;
                echo $tableheader;

            }
            $RowIndex = $RowIndex + 1;
            //end of page full new headings if
        }
        //end of while loop

        echo '</table><br>';
/*      if ($ListPageMax >1) {
            echo "<p>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';

            echo '<select name="Page>';

            $ListPage=1;
            while($ListPage <= $ListPageMax) {
                if ($ListPage == $_POST['PageOffset']) {
                    echo '<option VALUE=' . $ListPage . ' selected>' . $ListPage . '</option>';
                } else {
                    echo '<option VALUE=' . $ListPage . '>' . $ListPage . '</option>';
                }
                $ListPage++;
            }
            echo '</select>
                <input type=submit name="Go" VALUE="' . _('Go') . '">
                <input type=submit name="Previous" VALUE="' . _('Previous') . '">
                <input type=submit name="Next" VALUE="' . _('Next') . '">';
            echo '<p>';
        } */
    }
}
/* end display list if there is more than one record */

/* displays item options if there is one and only one selected */

if (!isset($_POST['Search']) AND (isset($_POST['Select']) OR isset($_SESSION['SelectedStockItem']))) {

    if (isset($_POST['Select'])) {
        $_SESSION['SelectedStockItem'] = $_POST['Select'];
        $StockID = $_POST['Select'];
        unset($_POST['Select']);
    } else {
        $StockID = $_SESSION['SelectedStockItem'];
    }
 //echo "<br>Loccode line 438 is " . $_SESSION['StockLocation'] . "<br>";
    $result = DB_query("SELECT stockmaster.description,
                            stockmaster.mbflag,
                            stockcategory.stocktype,
                            stockmaster.units,
                            stockmaster.decimalplaces,
                            stockmaster.controlled,
                            stockmaster.serialised,
                            stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS cost,
                            stockmaster.discontinued,
                            stockmaster.eoq,
                            stockmaster.volume,
                            locstock.loccode,
                            stockmaster.kgs,
                            stockmaster.actualcost
                            FROM stockmaster join locstock INNER JOIN stockcategory
                            ON stockmaster.categoryid=stockcategory.categoryid
                            WHERE stockmaster.stockid='" . $StockID . "'
                            AND stockmaster.mbflag <> 'G'
                            AND locstock.loccode='" . $_SESSION['UserStockLocation'] . "'",$db);

    $myrow = DB_fetch_array($result);

    $Its_A_Kitset_Assembly_Or_Dummy = false;
    $Its_A_Dummy = false;
    $Its_A_Kitset = false;
    $Its_A_Labour_Item = false;
    $Its_A_Manufactured_Item = false;

    echo '<table width="90%" border="1"><tr><th colspan=3><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Inventory') . '" alt=""><b>' . ' ' . $StockID . ' - ' . $myrow['description'] . '</th></tr></b>';

    echo '<tr><td width="40%" valign="top">
            <table>'; //nested table

    echo '<tr><th align=right>' . _('Item Type:') . '</th><td colspan=2>';

    switch ($myrow['mbflag']) {
        case 'A':
            echo _('Assembly Item');
            $Its_A_Kitset_Assembly_Or_Dummy = True;
            break;
        case 'K':
            echo _('Kitset Item');
            $Its_A_Kitset_Assembly_Or_Dummy = True;
            $Its_A_Kitset = True;
            break;
        case 'D':
            echo _('Service/Labour Item');
            $Its_A_Kitset_Assembly_Or_Dummy = True;
            $Its_A_Dummy = True;
            if ($myrow['stocktype']=='L'){
                $Its_A_Labour_Item = True;
            }
            break;
        case 'B':
            echo _('Purchased Item');
            break;
        case 'Z':
        	echo _('Merchandise');
        	break;
        default:
            echo _('Manufactured Item');
            $Its_A_Manufactured_Item = True;
            break;
    }
    echo '</td><th align=right>' . _('Control Level:') .'</th><td>';
    if ($myrow['serialised'] == 1) {
        echo _('serialised');
    } elseif ($myrow['controlled'] == 1) {
        echo _('Batchs/Lots');
    } else {
        echo _('N/A');
    }
    echo '</td><th align=right>' . _('Units') . ':</th><td>' . $myrow['units'] . '</td></tr>';
    echo '<tr><th align=right>' . _('Volume') . ':</th><td align=right colspan=2>' . number_format($myrow['volume'], 3) . '</td>
            <th align=right>' . _('Weight') . ':</th><td align=right>' . number_format($myrow['kgs'], 3) . '</td>
            <th align=right>' . _('EOQ') . ':</th><td align=right>' . number_format($myrow['eoq'],$myrow['decimalplaces']) . '</td></tr>';

	if (in_array($PricesSecurity,$_SESSION['AllowedPageSecurityTokens']) OR !isset($PricesSecurity)){
		//echo "<br>line 517<br>";
    	echo '<tr><th colspan=2>' . _('Sell Price') . ':</th><td>';

    	$PriceResult = DB_query("SELECT typeabbrev, price FROM prices
                                WHERE currabrev ='" . $_SESSION['CompanyRecord']['currencydefault'] . "'
                                AND typeabbrev = '" . $_SESSION['DefaultPriceList'] . "'
                                AND debtorno=''
                                AND branchcode=''
                                AND stockid='".$StockID."'",
                                $db);
		if ($myrow['mbflag'] == 'K' OR $myrow['mbflag'] == 'A') {

			$CostResult = DB_query("SELECT SUM(bom.quantity*
							(stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost)) AS cost
						FROM bom INNER JOIN
							stockmaster
						ON bom.component=stockmaster.stockid
						WHERE bom.parent='" . $StockID . "'
						AND bom.effectiveto > '" . Date("Y-m-d") . "'
						AND bom.effectiveafter < '" . Date("Y-m-d") . "'",
						$db);
			$CostRow = DB_fetch_row($CostResult);
			$Cost = $CostRow[0];
		} else {
			$Cost = $myrow['cost'];
		}

		if (DB_num_rows($PriceResult) == 0) {
			echo _('No Default Price Set in Home Currency');
			$Price = 0;
                        echo '<a class="tip_trigger" href="">' . _('Other Prices') ;
                            ?>
                       <span class="tip">
                         <?php
                                $PriceResult = DB_query("SELECT typeabbrev, price FROM prices
                                WHERE  typeabbrev = 'SW'
                                AND currabrev ='" . $_SESSION['CompanyRecord']['currencydefault'] . "'
                                AND stockid='".$StockID."'",
                                $db);

                          //  AND debtorno=''
                          //AND branchcode=''
                                //currabrev ='" . $_SESSION['CompanyRecord']['currencydefault'] . "'
                               // AND

                            echo '<table cellpadding=2 BORDER=0>';

                            
                                    $tableheader = '<tr>
                                                    <th>' . _('typeabbrev') . '</th>
                                                    <th>' . _('Price') . '</th>
                                                    </tr>';
                                    echo $tableheader;
                                    while ($PriceRow = DB_fetch_row($PriceResult)) {
                                        echo '<tr><td>' . $PriceRow[0] . '</td><td align=right>' . number_format($PriceRow[1],2) . '</td></tr>';
                                    }
                                    echo '</table>';
                                                    ?>
                           
                                        </span>
           <?php
		} else {
			$PriceRow = DB_fetch_row($PriceResult);
			$Price = $PriceRow[1];
			echo $PriceRow[0] . '</td><td class="number">' . number_format($Price, 2) . '</td>
				<th class="number">' . _('Gross Profit') . '</th><td class="number">';
				if ($Price > 0) {
					$GP = number_format(($Price - $Cost) * 100 / $Price, 2);
				} else {
					$GP = _('N/A');
				}
				echo $GP.'%'. '</td></tr>';
				echo '</td></tr>';
			while ($PriceRow = DB_fetch_row($PriceResult)) {
				print_r($PriceRow);
				$Price = $PriceRow[1];
				echo '<tr><td></td><th>' . $PriceRow[0] . '</th><td align=right>' . number_format($Price,2) . '</td>
				<th align=right>' . _('Gross Profit') . '</th><td align=right>';
				if ($Price > 0) {
					$GP = number_format(($Price - $Cost) * 100 / $Price, 2);
				} else {
					$GP = _('N/A');
				}
				echo $GP.'%'. '</td></tr>';
				echo '</td></tr>';
			}
		}
		if ($myrow['mbflag'] == 'K' OR $myrow['mbflag'] == 'A') {
			$CostResult = DB_query("SELECT SUM(bom.quantity*
							(stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost)) AS cost
						FROM bom INNER JOIN
							stockmaster
						ON bom.component=stockmaster.stockid
						WHERE bom.parent='" . $StockID . "'
						AND bom.effectiveto > '" . Date("Y-m-d") . "'
						AND bom.effectiveafter < '" . Date("Y-m-d") . "'",
						$db);
			$CostRow = DB_fetch_row($CostResult);
			$Cost = $CostRow[0];
		} else {
			$Cost = $myrow['cost'];
		}
		echo '<th align=right>' . _('Av Cost') . '</th><td align=right>' . number_format($Cost,3) . '</td>';
                echo '</tr><tr><th align=right>' . _('Actual Cost') . '</th><td align=right>' . number_format($myrow['actualcost'],3). '</td></tr>';
	} //end of if PricesSecuirty allows viewing of prices
    echo '</table>'; //end of first nested table
   // Item Category Property mod: display the item properties
       echo '<table>';
       $CatValResult = DB_query("SELECT categoryid
                    FROM stockmaster
                WHERE stockid='" . $StockID . "'", $db);
               $CatValRow = DB_fetch_row($CatValResult);
               $CatValue = $CatValRow[0];

       $sql = "SELECT stkcatpropid,
            label,
            controltype,
            defaultvalue
        FROM stockcatproperties
        WHERE categoryid ='" . $CatValue . "'
        AND reqatsalesorder =0
        ORDER BY stkcatpropid";

       $PropertiesResult = DB_query($sql,$db);
       $PropertyCounter = 0;
       $PropertyWidth = array();

       while ($PropertyRow = DB_fetch_array($PropertiesResult)) {

               $PropValResult = DB_query("SELECT value
                            FROM stockitemproperties
                    WHERE stockid='" . $StockID . "'
                    AND stkcatpropid =" . $PropertyRow['stkcatpropid'],
                                                                       $db);
               $PropValRow = DB_fetch_row($PropValResult);
               $PropertyValue = $PropValRow[0];

               echo '<tr><th align="right">' . $PropertyRow['label']
. ':</th>';
               switch ($PropertyRow['controltype']) {
                       case 0; //textbox
                               echo '<td align=right width=60><input type="text" name="PropValue' .
                               	$PropertyCounter . '" value="'. $PropertyValue.'">';
                               break;
                       case 1; //select box
                               $OptionValues = explode(',',$PropertyRow['defaultvalue']);
                                echo '<td align=left width=60><select name="PropValue' .$PropertyCounter . '">';
                               foreach ($OptionValues as $PropertyOptionValue) {
                                       if ($PropertyOptionValue == $PropertyValue) {
                                               echo '<option selected value="' . $PropertyOptionValue . '">' .
$PropertyOptionValue . '</option>';
                                       } else {
                                               echo '<option value="' . $PropertyOptionValue . '">' .
$PropertyOptionValue . '</option>';
                                       }
                               }
                               echo '</select>';
                               break;
                       case 2; //checkbox
                               echo '<td align=left width=60><input type="checkbox" name="PropValue' . $PropertyCounter . '"';
                               if ($PropertyValue==1){
                                       echo ' checked';
                               }
                               echo '>';
                               break;
               } //end switch
               echo '</td></tr>';
               $PropertyCounter++;
       } //end loop round properties for the item category
       echo '</table>'; //end of Item Category Property mod

    echo '<td width="15%">
            <table>'; //nested table to show QOH/orders

//echo "<br>Now at line 654<br>";
    
//function my_number_format($number, $dec_point, $thousands_sep) 
//{ 
//    $was_neg = $number < 0; // Because +0 == -0 
//    $number = abs($number); 
//
//    $tmp = explode('.', $number); 
//    $out = number_format($tmp[0], 0, $dec_point, $thousands_sep); 
//    if (isset($tmp[1])) $out .= $dec_point.$tmp[1]; 
//
//    if ($was_neg) $out = "-$out"; 
//
//    return $out; 
//} 
    $QOH = 0;
    switch ($myrow['mbflag']) {
        case 'A':
        case 'D':
        case 'K':
            $QOH = _('N/A');
            $QOO = _('N/A');
            break;
        case 'M':
        case 'B':
        case 'Z':
            $QOHResult = DB_query("SELECT sum(quantity)
                        FROM locstock
                        WHERE stockid = '" . $StockID . "'
                        AND loccode = '" . $_SESSION['UserStockLocation'] . "'",
                                        $db);
        	//echo "<br>sql line 670 " . $QOHResult . "<br>";
            $QOHRow = DB_fetch_row($QOHResult);
        	//echo "<br>Qrow is " . $QOHRow[0] . "<br>";
            if($QOHRow[0] >= 0.000){
                $QOH = number_format($QOHRow[0],$myrow['decimalplaces']);
            }else{
               // $num = abs();
                
                 //$num = abs($QOHRow[0]);
                // $QOH = number_format($num,$myrow['decimalplaces']) * -1;
                $QOH = $QOHRow[0];
            }
            
 
			//echo "<br>Line 673 " . $QOH . "<br>";
            $QOOResult = DB_query("SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd)
                                    FROM purchorderdetails
                                    WHERE purchorderdetails.itemcode='" . $StockID . "'",
                                    $db);
            if (DB_num_rows($QOOResult) == 0){
                $QOO = 0;
            } else {
            	//echo "<br>this is line number 631<br>";
                $QOORow = DB_fetch_row($QOOResult);
                $QOO = $QOORow[0];
            }
            //Also the on work order quantities
            $sql = "SELECT SUM(woitems.qtyreqd-woitems.qtyrecd) AS qtywo
                FROM woitems INNER JOIN workorders
                ON woitems.wo=workorders.wo
                WHERE workorders.closed=0
                AND woitems.stockid='" . $StockID . "'";
            $ErrMsg = _('The quantity on work orders for this product cannot be retrieved because');
            $QOOResult = DB_query($sql,$db,$ErrMsg);

            if (DB_num_rows($QOOResult) == 1) {
                $QOORow = DB_fetch_row($QOOResult);
                $QOO +=  $QOORow[0];
            }
            $QOO = number_format($QOO,$myrow['decimalplaces']);
            break;
    }
    $Demand = 0;
    $DemResult = DB_query("SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
                FROM salesorderdetails INNER JOIN salesorders
                ON salesorders.orderno = salesorderdetails.orderno
                WHERE salesorderdetails.completed=0
                AND salesorders.quotation=0
                AND salesorderdetails.stkcode='" . $StockID . "'",
                            $db);

    $DemRow = DB_fetch_row($DemResult);
    $Demand = $DemRow[0];
    $DemAsComponentResult = DB_query("SELECT  SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
                FROM salesorderdetails,
                    salesorders,
                    bom,
                    stockmaster
                WHERE salesorderdetails.stkcode=bom.parent
                AND salesorders.orderno = salesorderdetails.orderno
                AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
                AND bom.component='" . $StockID . "'
                AND stockmaster.stockid=bom.parent
                AND stockmaster.mbflag='A'
                    AND salesorders.quotation=0",
                    $db);

    $DemAsComponentRow = DB_fetch_row($DemAsComponentResult);
    $Demand += $DemAsComponentRow[0];
    //Also the demand for the item as a component of works orders

    $sql = "SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd)) AS woqtydemo
                FROM woitems INNER JOIN worequirements
                ON woitems.stockid=worequirements.parentstockid
                INNER JOIN workorders
                ON woitems.wo=workorders.wo
                AND woitems.wo=worequirements.wo
                WHERE  worequirements.stockid='" . $StockID . "'
                AND workorders.closed=0";

    $ErrMsg = _('The workorder component demand for this product cannot be retrieved because');
    $DemandResult = DB_query($sql,$db,$ErrMsg);

    if (DB_num_rows($DemandResult) == 1) {
        $DemandRow = DB_fetch_row($DemandResult);
        $Demand += $DemandRow[0];
    }
	$sql = "SELECT SUM(quantity) AS qty
                FROM stockintransit
                WHERE  stockintransit.stockid='" . $StockID . "'
                AND  stockintransit.loccode='" . $_SESSION['UserStockLocation'] . "'
                ";

	$ErrMsg = _('The workorder component demand for this product cannot be retrieved because');
	$ITResult2 = DB_query($sql,$db,$ErrMsg);
	$ITTotal2 = DB_fetch_row($ITResult2);
	//echo "<br>qoh1 " . $QOH . "<br>";
	if (mysql_num_rows($ITTotal2) > 0) {
		$QOH -= $ITTotal['0'];

	}


    echo '<tr><th align=right width="15%">' . _('Quantity On Hand') . ':</th><td width="17%" align=right>' . $QOH . '</td></tr>';
	echo '<tr><th align=right width="15%">' . _('Quantity In Transit') . ':</th><td width="17%" align=right>' . $ITTotal['0'] . '</td></tr>';
    echo '<tr><th align=right width="15%">' . _('Quantity Demand') . ':</th><td width="17%" align=right>' . number_format($Demand,$myrow['decimalplaces']) . '</td></tr>';
    echo '<tr><th align=right width="15%">' . _('Quantity On Order') . ':</th><td width="17%" align=right>' . $QOO . '</td></tr>
                </table>';//end of nested table

    echo '</td>'; //end cell of master table
    if ($myrow['mbflag'] == 'B'or ($myrow['mbflag'] == 'M')) {
        echo '<td width="50%" valign="top"><table>
            <tr><th width="50%">' . _('Supplier') . '</th>
                <th width="15%">' . _('Cost') . '</th>
                <th width="5%">' . _('Curr') . '</th>
                <th width="15%">' . _('Eff Date') . '</th>
                <th width="10%">' . _('Lead Time') . '</th>
                <th width="5%">' . _('Prefer') . '</th></tr>';

        $SuppResult = DB_query("SELECT  suppliers.suppname,
                        suppliers.currcode,
                        suppliers.supplierid,
                        purchdata.price,
                        purchdata.effectivefrom,
                        purchdata.leadtime,
                        purchdata.conversionfactor,
                        purchdata.preferred
                    FROM purchdata INNER JOIN suppliers
                    ON purchdata.supplierno=suppliers.supplierid
                    WHERE purchdata.stockid = '" . $StockID . "'",
                    $db);
        while ($SuppRow = DB_fetch_array($SuppResult)) {
            echo '<tr><td>' . $SuppRow['suppname'] . '</td>
                        <td align=right>' . number_format($SuppRow['price']/$SuppRow['conversionfactor'],2) . '</td>
                        <td>' . $SuppRow['currcode'] . '</td>
                        <td>' . ConvertSQLDate($SuppRow['effectivefrom']) . '</td>
                        <td>' . $SuppRow['leadtime'] . '</td>';
            switch ($SuppRow['preferred']) {
            /* 2008-08-19 ToPu */
            case 1:
                echo '<td>' . _('Yes') . '</td>';
                break;
            case 0:
                echo '<td>' . _('No') . '</td>';
                break;
            }
            echo '</tr>';
        }
        echo '</tr></table></td>';

        DB_data_seek($result, 0);
    }

    echo '</tr></table><hr>'; // end first item details table

    echo '<table width="90%" border="1"><tr>
        <th width="33%">' . _('Item Inquiries') . '</th>
        <th width="33%">' . _('Item Transactions') . '</th>
        <th width="33%">' . _('Item Maintenance') . '</th>
    </tr>';
    echo '<tr><td valign="top">';

    /*Stock Inquiry Options */

    echo '<a href="' . $rootpath . '/StockSupplier.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Supplier') . '</a><br>';
    echo '<a href="' . $rootpath . '/StockMovements.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Stock Movements') . '</a><br>';
    echo '<a href="' . $rootpath . '/PriceChangeHistory.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Cost Change History') . '</a><br>';
    if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
        echo '<a class="tip_trigger" href="' . $rootpath . '/StockStatus.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Stock Status') ;
        ?>
     <span class="tip">
         <?php
       $sql = "SELECT locstock.loccode,
               locations.locationname,
               locstock.quantity,
               locstock.reorderlevel,
	       locations.managed
               FROM locstock,
                    locations
               WHERE locstock.loccode=locations.loccode AND
                     locstock.stockid = '" . $StockID . "'
               ORDER BY locstock.loccode";

$ErrMsg = _('The stock held at each location cannot be retrieved because');
$DbgMsg = _('The SQL that was used to update the stock item and failed was');
$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

echo '<table cellpadding=2 BORDER=0>';

if ($Its_A_KitSet_Assembly_Or_Dummy == True){
	$tableheader = '<tr>
			<th>' . _('Location') . '</th>
			<th>' . _('Demand') . '</th>
			</tr>';
} else {
	$tableheader = '<tr>
			<th>' . _('Location') . '</th>
			<th>' . _('Quantity On Hand') . '</th>
			<th>' . _('Re-Order Level') . '</font></th>
			<th>' . _('Demand') . '</th>
			<th>' . _('Available') . '</th>
			<th>' . _('On Order') . '</th>
			</tr>';
}
echo $tableheader;
$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($LocStockResult)) {

	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
                 FROM salesorderdetails,
                      salesorders
                 WHERE salesorders.orderno = salesorderdetails.orderno AND
                 salesorders.fromstkloc='" . $myrow['loccode'] . "' AND
                 salesorderdetails.completed=0 AND
		 salesorders.quotation=0 AND
                 salesorderdetails.stkcode='" . $StockID . "'";

	$ErrMsg = _('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
	$DemandResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($DemandResult)==1){
	  $DemandRow = DB_fetch_row($DemandResult);
	  $DemandQty =  $DemandRow[0];
	} else {
	  $DemandQty =0;
	}

	//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.
	$sql = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
                 FROM salesorderdetails,
                      salesorders,
                      bom,
                      stockmaster
                 WHERE salesorderdetails.stkcode=bom.parent AND
                       salesorders.orderno = salesorderdetails.orderno AND
                       salesorders.fromstkloc='" . $myrow['loccode'] . "' AND
                       salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0 AND
                       bom.component='" . $StockID . "' AND stockmaster.stockid=bom.parent AND
                       stockmaster.mbflag='A'
		       AND salesorders.quotation=0";

	$ErrMsg = _('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
	$DemandResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($DemandResult)==1){
		$DemandRow = DB_fetch_row($DemandResult);
		$DemandQty += $DemandRow[0];
	}

	//Also the demand for the item as a component of works orders

	$sql = "SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd)) AS woqtydemo
				FROM woitems INNER JOIN worequirements
				ON woitems.stockid=worequirements.parentstockid
				INNER JOIN workorders
				ON woitems.wo=workorders.wo
				AND woitems.wo=worequirements.wo
				WHERE workorders.loccode='" . $myrow['loccode'] . "'
				AND worequirements.stockid='" . $StockID . "'
				AND workorders.closed=0";

	$ErrMsg = _('The workorder component demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
	$DemandResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($DemandResult)==1){
		$DemandRow = DB_fetch_row($DemandResult);
		$DemandQty += $DemandRow[0];
	}

	if ($Its_A_KitSet_Assembly_Or_Dummy == False){

		$sql = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS qoo
                   	FROM purchorderdetails
                   	INNER JOIN purchorders ON purchorderdetails.orderno=purchorders.orderno
                   	WHERE purchorders.intostocklocation='" . $myrow['loccode'] . "' AND
                   	purchorderdetails.itemcode='" . $StockID . "'";
		$ErrMsg = _('The quantity on order for this product to be received into') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$QOOResult = DB_query($sql,$db,$ErrMsg, $DbgMsg);

		if (DB_num_rows($QOOResult)==1){
			$QOORow = DB_fetch_row($QOOResult);
			$QOO =  $QOORow[0];
		} else {
			$QOO = 0;
		}

		//Also the on work order quantities
		$sql = "SELECT SUM(woitems.qtyreqd-woitems.qtyrecd) AS qtywo
				FROM woitems INNER JOIN workorders
				ON woitems.wo=workorders.wo
				WHERE workorders.closed=0
				AND workorders.loccode='" . $myrow['loccode'] . "'
				AND woitems.stockid='" . $StockID . "'";
		$ErrMsg = _('The quantity on work orders for this product to be received into') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$QOOResult = DB_query($sql,$db,$ErrMsg, $DbgMsg);

		if (DB_num_rows($QOOResult)==1){
			$QOORow = DB_fetch_row($QOOResult);
			$QOO +=  $QOORow[0];
		}

		echo '<td>' . $myrow['locationname'] . '</td>';

		printf("<td align=right>%s</td>
			<td align=right>%s</td>
			<td align=right>%s</td>
			<td align=right>%s</td>
			<td align=right>%s</td>",
			number_format($myrow['quantity'], $DecimalPlaces),
			number_format($myrow['reorderlevel'], $DecimalPlaces),
			number_format($DemandQty, $DecimalPlaces),
			number_format($myrow['quantity'] - $DemandQty, $DecimalPlaces),
			number_format($QOO, $DecimalPlaces)
			);

		if ($Serialised ==1){ /*The line is a serialised item*/

			echo '<td><a target="_blank" href="' . $rootpath . '/StockSerialItems.php?' . SID . '&Serialised=Yes&Location=' . $myrow['loccode'] . '&StockID=' .$StockID . '">' . _('Serial Numbers') . '</a></td></tr>';
		} elseif ($Controlled==1){
			echo '<td><a target="_blank" href="' . $rootpath . '/StockSerialItems.php?' . SID . '&Location=' . $myrow['loccode'] . '&StockID=' .$StockID . '">' . _('Batches') . '</a></td></tr>';
		}

	} else {
	/* It must be a dummy, assembly or kitset part */

		printf("<td>%s</td>
			<td align=right>%s</td>
			</tr>",
			$myrow['locationname'],
			number_format($DemandQty, $DecimalPlaces)
			);
	}
//end of page full new headings if
}
//end of while loop
echo '</table>';
         ?>
    </span>  
       <?php
        echo '</a><br>';
        echo '<a href="' . $rootpath . '/StockUsage.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Stock Usage') . '</a><br>';
    }
        echo '<a href="' . $rootpath . '/SelectSalesOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Sales Orders') . '</a><br>';
        echo '<a href="' . $rootpath . '/SelectCompletedOrder.php?' .SID . '&SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</a><br>';
    if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
        echo '<a href="' . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Purchase Orders') . '</a><br>';
        echo '<a href="' . $rootpath . '/PO_SelectPurchOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Search All Purchase Orders') . '</a><br>';
        echo '<a href="' . $rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg?' . SID . '">' . _('Show Part Picture (if available)') . '</a><br>';
    }

    if ($Its_A_Dummy == False) {
        echo '<a href="' . $rootpath . '/BOMInquiry.php?' . SID . '&StockID=' . $StockID . '">' . _('View Costed Bill Of Material') . '</a><br>';
        echo '<a href="' . $rootpath . '/WhereUsedInquiry.php?' . SID . '&StockID=' . $StockID . '">' . _('Where This Item Is Used') . '</a><br>';
    }
    if ($Its_A_Labour_Item==True) {
        echo '<a href="' . $rootpath . '/WhereUsedInquiry.php?' . SID . '&StockID=' . $StockID . '">' . _('Where This Labour Item Is Used') . '</a><br>';
    }
    wikiLink('Product', $StockID);

    echo '</td><td valign="top">';

    /* Stock Transactions */
    if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
        echo '<a href="' . $rootpath . '/StockAdjustments.php?' . SID . '&StockID=' . $StockID . '">' . _('Quantity Adjustments') . '</a><br>';
        echo '<a href="' . $rootpath . '/StockTransfers.php?' . SID . '&StockID=' . $StockID . '">' . _('Location Transfers') . '</a><br>';
        echo '<a href="' . $rootpath . '/MovementHistory.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Movement') . '</a><br>';
        /**
         * 2008-08-19 ToPu
         * enter a purchase order for this SelectedStockItem and suppliers
         * supplierid -- one link for each supplierid.
         */
        if ($myrow['mbflag'] == 'B' || $myrow['mbflag'] == 'Z') {
            /**/
            echo '<br>';
            $SuppResult = DB_query("SELECT  suppliers.suppname,
                        suppliers.supplierid,
                        purchdata.preferred
                    FROM purchdata INNER JOIN suppliers
                    ON purchdata.supplierno=suppliers.supplierid
                    WHERE purchdata.stockid = '" . $StockID . "'",
                    $db);
            while ($SuppRow = DB_fetch_array($SuppResult)) {
                /**/
                //
                echo '<a href="' . $rootpath . '/PO_Header.php?' . SID . '&NewOrder=Yes' . '&SelectedSupplier=' . $SuppRow['supplierid'] . '&StockID=' . $StockID . '">' . _('Purchase this Item from') . ' ' . $SuppRow['suppname'] . ' (default)</a><br>';
                /**/
            } /* end of while */
        } /* end of $myrow['mbflag'] == 'B' */
    } /* end of ($Its_A_Kitset_Assembly_Or_Dummy == False) */

    echo '</td><td valign="top">';

    /* Stock Maintenance Options */

  echo '<a href="' . $rootpath . '/Stocks.php?"><font color=red>' . _('Add Inventory Items') . '</font></a><br>';
  echo '<a href="' . $rootpath . '/Stocks.php?' . SID . '&StockID=' . $StockID . '&Qoh=' . $QOH . '"><font color=red>' . _('Modify Item Details') . '</font></a><br>';
    if(in_array($StockUpdatePermission,$_SESSION['AllowedPageSecurityTokens']))
    {
        if ($Its_A_Kitset_Assembly_Or_Dummy == False) {
            echo '<a href="' . $rootpath . '/StockReorderLevel.php?' . SID . '&StockID=' . $StockID . '">' . _('Maintain Reorder Levels') . '</a><br>';
                echo '<a href="' . $rootpath . '/StockCostUpdate.php?' . SID . '&StockID=' . $StockID . '&LocCode=' . $_SESSION['UserStockLocation'] . '">' . _('Maintain Standard Cost(Relevant to importers and manufacturers)') . '</a><br>';
                echo '<a href="' . $rootpath . '/StockActualCostUpdate.php?' . SID . '&StockID=' . $StockID . '&LocCode=' . $_SESSION['UserStockLocation'] . '">' . _('Maintain Actual Cost') . '</a><br>';
                echo '<a href="' . $rootpath . '/PurchData.php?' . SID . '&StockID=' . $StockID . '">' . _('Maintain Purchasing Data') . '</a><br>';
        }
        if ($Its_A_Labour_Item==True){
                echo '<a href="' . $rootpath . '/StockCostUpdate.php?' . SID . '&StockID=' . $StockID . '&loccode=' . $_SESSION['UserStockLocation'] . '">' . _('Maintain Standard Cost') . '</a><br>';
        }
        if (! $Its_A_Kitset) {
            echo '<a href="' . $rootpath . '/Prices.php?' . SID . '&Item=' . $StockID . '">' . _('Maintain Selling Price') . '</a><br>';
                if (isset($_SESSION['CustomerID']) AND $_SESSION['CustomerID'] != "" AND Strlen($_SESSION['CustomerID']) > 0) {
                echo '<a href="' . $rootpath . '/Prices_Customer.php?' . SID . '&Item=' . $StockID . '">' . _('Special Prices for customer') . ' - ' . $_SESSION['CustomerID'] . '</a><br>';
                }
                    echo '<a href="' . $rootpath . '/DiscountCategories.php?' . SID . '&StockID=' . $StockID . '">' . _('Maintain Discount Category') . '</a><br>';
                    if($Its_A_Manufactured_Item==True)
                    {
                        echo '<a href="' . $rootpath . '/BOMStockCostRecalc.php?' . SID . '&StockID=' . $StockID . '">' . _('Adjust Manufactured Costs') . '</a><br>';
                    }
        }

        echo '</td></tr></table>';
    }
} else {
  // options (links) to pages. This requires stock id also to be passed.
    echo '<table width=90% colspan=2 border=2 cellpadding=4>';
    echo '<tr>
        <th width=33%>' . _('Item Inquiries') . '</th>
        <th width=33%>' . _('Item Transactions') . '</th>
        <th width=33%>' . _('Item Maintenance') . '</th>
    </tr>';
    echo '<tr><td>';

    /*Stock Inquiry Options */

    echo '</td><td>';

    /* Stock Transactions */

    echo '</td><td>';

    /*Stock Maintenance Options */

    echo '<a href="' . $rootpath . '/Stocks.php?">' . _('Add Inventory Items') . '</a><br>';

    echo '</td></tr></table>';

}// end displaying item options if there is one and only one record

echo '<script  type="text/javascript">defaultControl(document.forms[0].barCode);</script>';

echo '</form>';

include('includes/footer.inc');
//print_r($_SESSION);
?>