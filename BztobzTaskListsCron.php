    <?php
    error_reporting(E_ALL ^ E_NOTICE);
    //set_include_path('/home/username123/public_html/includes/nusoaplib/');

    /*  function connectToDBwazinet() {
    $link = mysql_connect("www.wazierp.net", "waziuser", "weberp98");
    if (!$link){
        die("Couldn't connect to MySQL");
    }else {
        mysql_select_db("waziuser_duroplastic", $link)
        or die("Couldn't open db:" . mysql_error());
    }


    }
error_reporting(E_ALL);
    connectToDBwazinet();*/
    set_include_path('/home/waziuser/public_html/wazi/includes/nusoaplib/');

    // require_once('includes/nusoaplib/nusoap.php');
    require 'nusoap.php';

    $client = new nusoap_client('http://www.bzto.bz/bztobz8/WebServices/WaziComm.asmx',false);
    $soapaction = "http://tempuri.org/selectUsersWeekActHistory";
    $namespace= "http://tempuri.org/";
    $err = $client->getError();
    /*************************************************************************************************************************/
    $from = "crons@duroplastic.com";
    $replyto = "support@duroplastic.com";
    /*  Send email */
    $headers = "From: " . strip_tags($from) . "\r\n";
    $headers .= "Reply-To: " . strip_tags($replyto) . "\r\n";
    //$headers .= "CC: dylan@duroplastic.com.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    $message = '<html>';
    $message.='<head><style type="text/css">

    .outer {background: #FAFAFA; font-family: Arial, Helvetica, sans-serif; font-size: 12px;}
    p {margin: 0px 0 10px;margin-left:10px;}
    h2{font-weight: bold; font-size: 14px; margin-bottom: 5px;margin-left:5px;  display: block }
    .text {border-radius: 5px;font-size: 1.5em; padding: 5px;}
    </style>
            </head>';
    $message .= '<h1>Bzto.bz Activities</h1>';
    $message.= '<body>';
    if ($err) {
    $message.= '<p class ="text">
        <small style="color:#666"><pre>'.$err .'</pre></small></p>';
    //  echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
    }
    //$query = 'methanol';
    //$token = 'token';
    //$result = $client->call(SimpleSearch, array('query' => $query, 'token' => $token), array('return' => 'xsd:string'), "http://www.chemspider.com/SimpleSearch") ;
    $result = $client->call('selectUsersWeekActHistory', '',$namespace,$soapaction);
    // Check for a fault
    if ($client->fault) {
    //  echo '<h2>Fault</h2><pre>';
    //   print_r($result);
    //  echo '</pre>';
    $message.= '<p class ="text">
        <small style="color:#666"><pre>'.print_r($result).'</pre></small></p>';
    } else {
    // Check for errors
    $err = $client->getError();
    if ($err) {
    // Display the error
    // echo '<h2>Error</h2><pre>' . $err . '</pre>';
        $message.= '<p class ="text">
        <small style="color:#666"><pre>'.$err.'</pre></small></p>';
    } else {
        $MobiVisit = "MobiVisit";
        $Visits = "Visit";
        $Other = "Other";
    // Display the result
    //echo '<h2>Result</h2><pre>';
    //  print_r($result);
    //echo '</pre>';
    //  $result['electUsersActHistoryResult']['int']
    /*
    (
    [TaskHist] => Array
        (
            [CustName] => PENNY PINCHERS - BOKSBURG
            [TaskNumber] => 42980
            [CustPriKey] => 8818
            [DateStamp] => 2013/09/27 12:00:00 AM
            [ActMessage] => Spoke to Tabiso, the roof sheeting has started selling better and the awnings too. Their stock is scheduled for delivery today. I topped up brochures.
            [DueDate] => 2013/09/27 08:39:32 AM
            [ActStatus] => Y
            [ActType] => MobiVisit
            [ActFinishedDate] => 2013/09/27 08:39:32 AM
            [ActMileage] => 0
            [ActInitName] => Graeme Mappin
            [ActInitInputBYName] => Graeme Mappin
            [ActRepCode] =>
            [ActRepCodeID] => 249
            [ActInputByID] => 249
            [ActRepUserID] => 249
            [ActCompanyID] => 1
        )

    )
         */
    $k = 0;
        $ArrayData = array();

        $ActTypeActRepUserID  = "";
        $Counternumber = count($result['TaskWeekHist']);
        $Counternumbers = count($result);

    if($Counternumber > 1 && $Counternumber != 17 && $Counternumbers==1){
    foreach ($result['TaskWeekHist'] as $user => $value) {
     /*echo  $user." => ".$value; TaskHist => Array*/

      if($ActTypeActRepUserID != $value['ActInputByID']  ){
      //echo $value['ActInitName'];
          $ActTypeActRepUserID = $value['ActInputByID'];
         $ArrayData[$ActTypeActRepUserID]['ActInitInputBYName'] = $value['ActInitName'];
     }
        if(isset($value['ActType']) && !empty($value['ActType'])){
         if( $value['ActType'] == $MobiVisit ){

             $ArrayData[$ActTypeActRepUserID]["MobiVisit"]+= 1;

         }elseif($value['ActType'] == $Visits)
         {
             $ArrayData[$ActTypeActRepUserID][$Visits] += 1 ;
            // ActTypeVisit  += $value[ActType];
         }else{
             //ActTypePhone   += $value[ActType];
             $ArrayData[$ActTypeActRepUserID][$Other] += 1 ;
         }
        }
    }
    }else{
       foreach ($result as $user => $value) {
    //  echo  $user." =>xxx ".$value; //TaskHist => Array*/

         if($ActTypeActRepUserID != $value['ActInputByID']  ){
             // echo $value['ActInitName'];
               $ActTypeActRepUserID =  $value['ActInputByID'];
               $ArrayData[$ActTypeActRepUserID]['ActInitInputBYName'] = $value['ActInitName'];
        }
        if(isset($value['ActType'])&& !empty($value['ActType'])){
           if($value['ActType'] == $MobiVisit){
               $ArrayData[$ActTypeActRepUserID][$MobiVisit] += 1;

           }elseif($value['ActType'] == $Visits)
           {
               $ArrayData[$ActTypeActRepUserID][$Visits] += 1 ;
               // ActTypeVisit  += $value[ActType];
           }else{
               //ActTypePhone   += $value[ActType];
               $ArrayData[$ActTypeActRepUserID][$Other] += 1 ;
           }
        }
    }
    }

    $message.= ' <h2>Report:</h2>';
    $message.=  "<table border=1>
        <tr>
                    <th>Reps Name</th>
                    <th> Visit </th>
                    <th>Mobi Visit</th>
                    <th>Other</th>
        </tr>";
        if (!empty($ArrayData)) {
    // print_r($ArrayData);
            $TotalMobiCounter = 0;
            $TotalVisistsCounter = 0;
            $TotalOtherCounter = 0;
            foreach ($ArrayData as $key => $value) {
                $MobiCounter = 0;
                $VisistsCounter = 0;
                $OtherCounter = 0;
               /* $MobiVisit = "MobiVisit";
                $Visits = "Visit";
                $Other = "Other";*/
                $message.=  "<tr><td>".$ArrayData[$key]['ActInitInputBYName'] ."</td>";
                if(empty($ArrayData[$key][$Visits]))
                {
                    $message.=   " <td>".$VisistsCounter."</td>";
                }else{
                    $TotalVisistsCounter +=$ArrayData[$key][$Visits];
                    $message.=   " <td>".$ArrayData[$key][$Visits] ."</td>";
                }

                if(empty($ArrayData[$key][$MobiVisit]))
                {
                    $message.=  " <td>".$MobiCounter ."</td>";
                }else{
                    $TotalMobiCounter += $ArrayData[$key][$MobiVisit];

                    $message.=   " <td>".$ArrayData[$key][$MobiVisit] ."</td>";
                }

                if(empty($ArrayData[$key][$Other]))
                {
                    $message.= " <td>". $OtherCounter."</td>";
                }else{

                    $TotalOtherCounter += $ArrayData[$key][$Other];
                    $message.=   " <td>".$ArrayData[$key][$Other] ."</td>";
                }



                $message.=  " </tr>";

            }
            $message.=  "<tr><td>Total</td><td>".$TotalVisistsCounter."</td><td>".$TotalMobiCounter ."</td><td>".$TotalOtherCounter."</td></tr> ";
        }
        $message.=  "</table>";

    }
    $message .= '</body></html>';
    /*if (!mail("clinton@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }*/

        //$SQLmails = "SELECT * FROM sendcronemails
             //         WHERE rep_id = {$Rep}";

      //  $resultmails = mysql_query($SQLmails);
        //while ($myrowmails = mysql_fetch_array($resultmails)) {

      //  }//end while email
    if (!mail("alicia@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }
    if (!mail("tamelo@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
            , $headers)) {
            echo "Mail not send";
    }

        if (!mail("clinton@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
            , $headers)) {
            echo "Mail not send";
        }
        if (!mail("roelanda@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
            , $headers)) {
            echo "Mail not send";
        }
    if (!mail("lindie@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }
    if (!mail("carl@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }
    if (!mail("tendai@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }
    if (!mail("riaz@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }
    if (!mail("dieter@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }
    if (!mail("rowan@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }
    if (!mail("nicholas@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }
    if (!mail("graeme@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }
    if (!mail("natasha@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }
    if (!mail("roofgeorge@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }/*
    if (!mail("warren@duroplastic.com", 'Bztobz Report: ' . $dbt, $message
        , $headers)) {
        echo "Mail not send";
    }*/
    }

    //include('includes/footer.inc');
    ?>