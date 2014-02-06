<?php

/* Tamelo Douglas
 * Cron jobs
 * 
 * 
 *  */

// $PageSecurity = 3;

include('includes/session.inc');
$title = _('Cron Jobs');
include('includes/header.inc');
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/sales.png" title="' . _('Crons') . '" alt="">' . ' ' . _('Cron Jobs') . '</p> ';
?>
<script type="text/javascript">
    function displayResult()
    {
        document.getElementById("myTable").insertRow(-1).innerHTML = '<tr><td>Email To :</td><td><input type="Text" name="email[]" size=35 maxlength=35 ></td></tr>';
    }
</script>
<?php

echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


if (isset($_GET['SelectedSaleperson'])) {
    $SelectedSaleperson = strtoupper($_GET['SelectedSaleperson']);
} elseif (isset($_POST['SelectedSaleperson'])) {
    $SelectedSaleperson = strtoupper($_POST['SelectedSaleperson']);
}

if (isset($Errors)) {
    unset($Errors);
}

$SQLH = "CREATE TABLE IF NOT EXISTS `sendLowGPcron` (
  `script_id` int(5) NOT NULL ,
  `scriptname` varchar(50) NOT NULL DEFAULT '',
  `margin` varchar(20) NOT NULL DEFAULT '',
  `belocost` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`script_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";


$runH = DB_query($SQLH, $db);

/* * ******************************************* */
$SQLS = "CREATE TABLE IF NOT EXISTS `sendLowGPcronemails` (
  `cron_id` int(11) NOT NULL AUTO_INCREMENT,
  `script_id` int(5) NOT NULL,
  `email` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`cron_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";

$runS = DB_query($SQLS, $db);

/* * **************************************************** */

if ($_POST['email'] && !isset($_POST['updatecron'])) {

    $repcode = "";
    $repname = "";
    if (is_array($_POST['email'])) {

        $repcode = $_POST['repcode'];
        $repname = $_POST['repname'];

        $SQLCheck = "SELECT * FROM sendLowGPcron
                        WHERE script_id =" . $repcode;
        $resultscheck = DB_query($SQLCheck, $db);

        if (DB_num_rows($resultscheck) == 0) {

            $SQL = "INSERT INTO sendLowGPcron(script_id,userid,crondisable) VALUES(" . $repcode . ",
                                                                    '" . $_POST['repname'] . "',
                                                                     " . $_POST['cronenable'] . ")";
            $runin = DB_query($SQL, $db);
        }

        $emails = $_POST['email'];
        if (is_array($emails)) {
            foreach ($emails as $mail) {
                if ($mail != "") {
                    /* check duplicates */
                    $SQLdup = "SELECT * FROM sendLowGPcronemails
                               WHERE email ='" . $mail . "'
                               AND script_id = {$repcode}";
                    $resultsduplicates = DB_query($SQLdup, $db);

                    if (DB_num_rows($resultsduplicates) == 0) {

                        $SQL = "INSERT INTO sendLowGPcronemails(script_id,email) VALUES(" . $repcode . ",
                                                            '" . $mail . "')";
                        $runin = DB_query($SQL, $db);
                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
            }
        }
        unset($_POST['repcode']);
        unset($_POST['repname']);
    }

    unset($_POST['email']);
} elseif ($_POST['updatecron']) {


    $crondisable = $_POST['cronenable'];
    $repcode = $_POST['repcode'];
    $repname = $_POST['repname'];

    if (isset($_POST['cronenable'])) {

        $SQLset = "UPDATE sendLowGPcron SET crondisable= " . $crondisable . "
                                 WHERE script_id = " . $repcode;

        $runup = DB_query($SQLset, $db);
        unset($_POST['cronenable']);
    }

    foreach ($_POST['email'] as $key => $value) {
        if ($value != "") {

            $SQLdup = "SELECT * FROM sendLowGPcronemails
                               WHERE cron_id = " . $key;
            $resultsduplicates = DB_query($SQLdup, $db);

            if (DB_num_rows($resultsduplicates) <= 0) {

                $SQL = "INSERT INTO sendLowGPcronemails(script_id,email) VALUES(" . $repcode . ",
                                                            '" . strtolower(trim($value)) . "')";
                $runin = DB_query($SQL, $db);
            } else {

                $SQL = "UPDATE  sendLowGPcronemails SET email = '" . strtolower(trim($value)) . "',
                                                       script_id = " . $repcode . "
                                                       WHERE cron_id = " . $key;
                $runin = DB_query($SQL, $db);
            }
        }
    }

    unset($_POST['updatecron']);
    unset($_POST['email']);
}




if (isset($_GET['add']) && isset($_GET['SelectedSaleperson'])) {
    $selectedrep = $_GET['SelectedSaleperson'];
    $selectedrepname = $_GET['repname'];

    echo '<table id="myTable" >';
    echo "<tr><input type=hidden name=repcode value =" . $selectedrep . " /></tr>";
    echo "<tr><input type=hidden name=repname value ='" . $selectedrepname . "' /></tr>";
    echo '<tr><td>' . _('Rep code ') . ':</td><td><b>' . $selectedrep . ' : ' . $selectedrepname . '</b></td></tr>';
    echo '<tr><td>' . _('Send Cron') . '</td>';
    echo '<td><select name=cronenable>';
    $selectedEdit = array(0 => "NO", 1 => "YES");
    foreach ($selectedEdit as $key => $val) {
        if ($key == $myrows['crondisable']) {
            echo '<option selected value=' . $key . '>' . $val . '</option>';
        } else {
            echo '<option value=' . $key . '>' . $val . '</option>';
        }
    }

    echo ' </select></td></tr>';

    echo '</table>';
    echo '<div class="centre"><button type="button" onclick="displayResult()">' . _('Insert new row') . '</button></div>';
    echo "<div class='centre'><input type='submit' name='addcron' value='" . _('Add to Crons') . "'></div>";
} elseif (isset($_GET['remove'])) {

    $mail = $_GET['remove'];
    $repcode = $_GET['repcode'];

    $SQLdup = "SELECT * FROM sendLowGPcronemails
                               WHERE script_id = {$repcode}";
    $resultsduplicates = DB_query($SQLdup, $db);

    if (DB_num_rows($resultsduplicates) > 0) {

        $SQLDELETE = "DELETE FROM sendLowGPcronemails WHERE
                                                email = '" . $mail . "'
                                                AND script_id = " . $repcode;

        $runDel = DB_query($SQLDELETE, $db);
    }
}


//if (isset($_GET['SelectedSaleperson']) && !$_GET['add'] && !$_GET['remove']) {
if (isset($_POST['AddEmails'])){

   /* $selectedrep = $_GET['SelectedSaleperson'];
    $SQLSet = "SELECT * FROM sendLowGPcron
                        WHERE script_id =" . $selectedrep;

    $resultset = DB_query($SQLSet, $db);
*/
    //sendLowGPcron.userid
    $SQL = "SELECT sendLowGPcron.script_id,sendLowGPcronemails.email,sendLowGPcronemails.cron_id
                FROM sendLowGPcronemails,sendLowGPcron
                WHERE sendLowGPcron.script_id = sendLowGPcronemails.script_id
                AND sendLowGPcronemails.script_id = " . $_POST['scriptid'];
    $result = DB_query($SQL, $db);


    echo '<table id="myTable">';
    echo "<tr><input type=hidden name=repcode value =" .  $_POST['scriptid']. " /></tr>";
    echo "<tr><input type=hidden name=repname value ='" . $selectedrepname . "' /></tr>";
    echo '<tr><th colspan=5>Edit emails for </b></th></tr>';
   /* echo '<tr><td>' . _('Send Cron') . '</td>';


    $myrows = db_fetch_array($resultset);

    echo '<td><select name=cronenable>';
    $selectedEdit = array(0 => "NO", 1 => "YES");
    foreach ($selectedEdit as $key => $val) {
        if ($key == $myrows['crondisable']) {
            echo '<option selected value='.$key.'>' . $val . '</option>';
        } else {
            echo '<option value='.$key.'>' . $val . '</option>';
        }
    }

    echo ' </select></td></tr>';*/
    while ($myrow = db_fetch_array($result)) {
        $Link = $_SERVER['PHP_SELF'] . '?' . SID . '&remove=' . $myrow['email'] . '&repcode=' . $myrow['script_id'];
        echo '<tr><td>' . _('Email To ') . ":</td><td><input type='Text' name=\"email[" . $myrow['cron_id'] . "]\" size=35 maxlength=35 value='" . $myrow['email'] . "' ></td>
                            <td><a href=\"$Link\" >Delete</a></td></tr>";
    }
    echo '</table>';
    echo '<div class="centre"><button type="button" onclick="displayResult()">' . _('Insert new row/mail') . '</button></div>';
    echo "<p><div class='centre'><input type=submit Name='updatecron' Value='" . _('updates cron') . "'onclick=\"return confirm('" . _('Are you sure you wish to proceed ?') . '\');"/></div></p>';
}
if($_POST['SaveSettings']){
    if(isset($_POST['belowcost'])){
        $belowcost = 1;
    }else{
        $belowcost = 0;
    }
    if(isset($_POST['crondisable'])){
        $crondisable = 1;
    }else{
        $crondisable = 0;
    }

    $SQL = "INSERT INTO sendLowGPcron(margin,belocost,crondisable) VALUES(" .$_POST['margins']. ",
                                                                    " .$belowcost. ",
                                                                     " .$crondisable. ")";
    $runin = DB_query($SQL, $db);
    //$SQL = "INSERT INTO sendLowGPcron () "
}

if($_POST['UpdateSettings']){
    if(isset($_POST['belowcost'])){
        $belowcost = 1;
    }else{
        $belowcost = 0;
    }
    if(isset($_POST['crondisable'])){
        $crondisable = 1;
    }else{
        $crondisable = 0;
    }

    $SQL = "UPDATE  sendLowGPcron SET margin = " . $_POST['margins'] . ",
                                                      belocost = " . $belowcost . ",
                                                      crondisable = " . $crondisable . "
                                                       WHERE script_id = " . $_POST['scriptid'];
    $runin = DB_query($SQL, $db);
}

$SQL = "SELECT * FROM sendLowGPcron";
$result = DB_query($SQL, $db);

$myrow  = DB_fetch_array($result);
$_POST['margins'] = $myrow['margin'];

if($myrow['belocost']==1){
    $belotick = "checked='true'";
}else{
    $belotick = "";
}

if($myrow['crondisable']==1){
    $cronDisTick =  "checked='true'";
}else{
    $cronDisTick = "";
}

$_POST['belowcost'] = $myrow['belocost'];
$_POST['crondisable']  = $myrow['crondisable'];

    echo "<table>";
    echo "<tr><input type=hidden name=scriptid value =" . $myrow['script_id']. " /></tr>";
    echo "<tr><td>" . _('Margin %') . "</td>
		<td><input type='text' name='margins' value='".$_POST['margins']."'></td></tr><tr>
                <td>" . _('Below Cost Of (if null)') . "</td>
		<td><input type='checkbox' name='belowcost' value='".$_POST['belowcost']."' $belotick></td></tr><tr>
		  <td>" . _('Disable Cron') . "</td>
		<td><input type='checkbox' name='crondisable' value='".$_POST['crondisable']."' $cronDisTick></td>
		</tr></table>";
    if(DB_num_rows($result)==0){
      echo "<div class='centre'><input type=Submit Name='SaveSettings' Value='" . _('Save Settings') . "'></div>";
    }else{
        echo "<div class='centre'><input type=Submit Name='UpdateSettings' Value='" . _('Update Settings') . "'></div>";
        echo "<div class='centre'><input type=Submit Name='AddEmails' Value='" . _('Add Email Address') . "'></div>";
    }

    echo '</table>';
//} //end of ifs and buts!
echo '</form>';
include('includes/footer.inc');

?>