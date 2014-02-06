<?php

/*
 * Tamelo Douglas
 * Modificatiion
 * Original Code BY: Dylan Johns
 * 
 */
// $PageSecurity = 21;

include('includes/session.inc');
$title = _('Send Statements');
include('includes/header.inc');
require_once('includes/class.phpmailer.php');


//-----------------------------Delete- Resend /mail-------------------------------------------------
if(isset($_GET['Delete']))
{
   $delr = "DELETE  FROM emailresend
                   WHERE email= '".$_GET['email']."'
                   AND debtorno = '".$_GET['Delete']."'";
   //echo $delr;
   $result = DB_query($del, $db, $ErrMsg);  
   
   if($result){
       echo "<div class='centre'><font color='green'>Debtor  " . $_GET['Delete'] . " successfully deleted from resend - table....</font></div>";
   }
   
   unset($_GET['email']);
   unset($_GET['Delete']);
}
//------------------------------------------------------------------------------

echo '<br/><br/><p class="page_title_text">Emails To Re-send Statements </p>';
echo "<form action='" . $_SERVER['PHP_SELF'] . "' method=post>";
print "<br/><table border=1>
<tr><th>Debtor Number</th> 
<th>Contact Email</th><th>Month</th><th>Select Email</th><th>DELETE</th></tr>";

$get = "SELECT `email`, MONTH(`time`) AS month, `debtorno` FROM emailresend ";
$ErrMsg = _('could not retrive no sent because');

$getr = DB_query($get, $db, $ErrMsg);
$months = array("none","January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
while ($info = mysql_fetch_array($getr)) {

     echo "<tr><td>{$info['debtorno']}</td>   
	  <td>{$info['email']}</td>
          <td>{$months[$info['month']]}</td>
          <td><input type='checkbox'  name='emailSelected[]' value=".$info['debtorno'] ."#".$info['email']."></td>";
       echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?Delete=' . $info['debtorno'].'&email='.$info['email'].'"> DELETE/NOT TO RESEND </td>';
       echo "  </tr>";
}
print "</table>";

echo '<br/><hr><br/>';

echo "<div class='centre'>";

// Select Month
echo "Month to send: ";
$curr_month = date("m");
$curr_year = " " . date("Y");
$month = array(1 => "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$select = '<select name=month>\n';
foreach ($month as $key => $val) {
    $select .= "\t<option val=\"" . $key . "\"";
    if ($key == $curr_month) {
        $select .= " selected=\"selected\">" . $val . $curr_year . "</option>\n";
    } else {
        $select .= ">" . $val . $curr_year . "</option>\n";
    }
}
$select .= "</select>";
echo $select;

// Submit Button
echo "<br/><br/><input type=submit name='SendEmails' VALUE='" . _('Send Statements to Emails Above') . "'>";
echo "</form></div><br/>";


if (isset($_POST['SendEmails'])) {



    $month = $_POST['month'];
    $datanospace = str_replace(' ', '', $month);

    // Checks if the statements exists
    $filename = "statements/" . $datanospace;
    if (!file_exists($filename)) {
        prnMsg(_('There are no statements stored for this month. Please choose another month.'), 'error');
        include('includes/footer.inc');
        exit;
    }
    $checkbox = $_POST['emailSelected'];
    $countCheck = count($_POST['emailSelected']);
    $Msg = array();
    for ($i = 0; $i < $countCheck; $i++) {
        $separate = explode("#", $checkbox[$i]);
        $detorNO = $separate[0];
        $detorEmail = $separate[1];
        
        $get = "SELECT `email`, `time`, `debtorno` FROM emailresend
                     WHERE email= '".$detorEmail."'
                     AND debtorno = '".$detorNO."'";
   // }
       $getr = DB_query($get, $db, $ErrMsg);

    //while ($info = mysql_fetch_array($getr)) {
        $info = mysql_fetch_array($getr);
        $address = $info['email'];
        $debtorno = $info['debtorno'];

        $statementfilename = "statements/" . $datanospace . "/" . trim($debtorno) . ".pdf";

        if (!file_exists($statementfilename)) {
            echo "<div class='centre'><font color='red'>Error sending email to " . $address . " - Statement for " . $debtorno . " not found.</font></div>";
        } else {

            $message = "Dear Customer
				<br/><br/>
				Please find attatched your statement for $month from Duroplastic Technologies.<br/>
				Please supply remittance advice with payment before due date to enjoy a settlement discount if applicable.<br/>
				Should you require invoices or POD's, we are able to send these to you electronically.<br/>
				For any queries and/or sending remittances please contact us at accounts@duroplastic.com
				<br/><br/><br/>
				Regards
				<br/><br/>
				Duroplastic Accounts Department
				<br/>
				Tel: (021) 981 1440
				<br/>
				Email: accounts@duroplastic.com
				<br/><br/><br/>
				- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -<br/>
				Please note: This email has been auto generated. If you find that any of the information is incorrect, please contact as immediatly so we can correct it.<br/>
				This email was sent to Duroplastic: $debtorno Debtor <br/>";

            $mail = new PHPMailer(); // defaults to using php "mail()"

            $mail->IsSendmail(); // telling the class to use SendMail transport

            $mail->AddReplyTo("accounts@duroplastic.com", "Duroplastic Accounts Department");
            $mail->SetFrom('accounts@duroplastic.com', 'Duroplastic Accounts Department');
            $mail->AddAddress($address);
            $mail->Subject = "Duroplastic - statement for " . $month;
            $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
            $mail->Body = $message;
            $mail->AddAttachment("statements/" . $datanospace . "/" . trim($debtorno) . ".pdf"); // attachment
            $Sendresult = $mail->Send();
            if (!$Sendresult) {
                echo "<div class='centre'> <font color='green'>Email Not Sent to " . $address . " for $debtorno </font></div>";
                echo "<div class='centre'> Mailer Error: " . $mail->ErrorInfo . "</div>";
                echo "<div class='centre'><font color='green'>....Resending to " . $address . " for $debtorno </font></div>";
                $SecondTry = $mail->Send();
            }
            if (!$SecondTry && !$Sendresult) {
                echo "<div class='centre'> Mailer Error: " . $mail->ErrorInfo . " <font color='green'>.....Resending Third Attempt </font></div>";
                $ThirdTry = $mail->Send();
            }
            if (!$SecondTry && !$Sendresult && !$ThirdTry) {
                echo "<div class='centre'><font color='green'>....Resending last attempt to..." . $address . " for $debtorno </font></div>";
                $lastTry = $mail->Send();
            }
            if (!$SecondTry && !$Sendresult && !$ThirdTry && !$lastTry) {
                echo "<div class='centre'>Fourth attempt failed... Mailer Error: " . $mail->ErrorInfo . " <font color='red'>Cant send this email </font></div>";

                 echo "<div class='centre'><font color='green'>Something is wrong, please confirm email for $debtorno ...</font></div>";

            } else {
                
                  $MailStatues = _('SENT');
                   /**-Lets insert into the database table(sentmail_history), what was sent-**/
                   $SQLHistory = "INSERT INTO sentmail_history(email,debtorno,trandate,status,user)
                                                          VALUES('".$address."',
                                                           '".$debtorno."',
                                                           '".Date("Y-m-d H:i:s")."',
                                                           '".$MailStatues."',
                                                           '".$_SESSION['UserID']."')";

                    $Historyresult = DB_query($SQLHistory,$db,$ErrMsg);
                
                
                   $Msg[$i] = "<div class='centre'><font color='green'>Email sent to " . $address . " for $debtorno</font></div>";
                    /*Now empty the table.........*/
                         $del = "DELETE  FROM emailresend
                                 WHERE email= '".$address."'
                                 AND debtorno = '".$debtorno."'";
                          $result = DB_query($del, $db, $ErrMsg);
                     
            }
        }
    }
    if(!empty($Msg)){  
        for($i = 0;$i < count($Msg); $i++)
        {
            echo $Msg[$i];
        }
         sleep(1);
         echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/resendStatements.php?' . SID .'">';
    }
}

echo "<br/>";
include('includes/footer.inc');
?>