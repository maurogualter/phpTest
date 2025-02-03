<!doctype html>
<html>
  <head>
    <title>This is the title of the webpage!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .realtable          {font-size: 12px; line-height:1.4;
                            background-color: #DEEFF6;
                            border-collapse: collapse;
                            border-bottom: solid 2px #DEEFF6;
                            margin-top:11px;
                            margin-bottom:11px;}
        .realtable th       {background-color: #C2E2F0; 
                            border-bottom: solid #ffffff 1px; 
                            border-right: solid #ffffff 1px; 
                            padding: 3px 0px 3px 4px;
                            text-align: left;
                            vertical-align: top;}
        .realtable td       {border-bottom: solid #ffffff 1px; 
                            border-right: solid #ffffff 1px; 
                            vertical-align: top;
                            padding: 3px 0px 3px 4px;}
        .datumtijd          {font-size: 10px;
                            color: #939393;}

        .arcTB th, .arcTB td {
        border: 1px solid black;
        border-collapse: collapse;
      }

        /* The Modal (background) */
        .modal {
          display: none; /* Hidden by default */
          position: fixed; /* Stay in place */
          z-index: 1; /* Sit on top */
          padding-top: 100px; /* Location of the box */
          left: 0;
          top: 0;
          width: 100%; /* Full width */
          height: 100%; /* Full height */
          overflow: auto; /* Enable scroll if needed */
          background-color: rgb(0,0,0); /* Fallback color */
          background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }

        /* Modal Content */
        .modal-content {
          background-color: #fefefe;
          margin: auto;
          padding: 20px;
          border: 1px solid #888;
          width: 40%;
    
        }

        /* The Close Button */
        .close {
          color: #aaaaaa;
          float: right;
          font-size: 28px;
          font-weight: bold;
        }

        .close:hover,
        .close:focus {
          color: #000;
          text-decoration: none;
          cursor: pointer;
        }

          /* The Close Button */
          .openTable {
          color:rgb(14, 12, 131);
          float: right;
          font-size: 28px;
          font-weight: bold;
        }

        .openTable:hover,
        .openTable:focus {
          color: #000000;
          text-decoration: none;
          cursor: pointer;
        }

    </style>    
    <?php 
      function CallAPI($method, $url, $data = false)
      {

          $curl = curl_init();
          switch ($method)
          {
              case "POST":
                  curl_setopt($curl, CURLOPT_POST, 1);
                  if ($data)
                      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                  break;
              case "PUT":
                  curl_setopt($curl, CURLOPT_PUT, 1);
                  break;
              default:
                  if ($data)
                      $url = sprintf("%s?%s", $url, http_build_query($data));
          }
      
          // Optional Authentication:
          curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: eyJvcmciOiI1ZTU1NGUxOTI3NGE5NjAwMDEyYTNlYjEiLCJpZCI6ImE1OGI5NGZmMDY5NDRhZDNhZjFkMDBmNDBmNTQyNjBkIiwiaCI6Im11cm11cjEyOCJ9'
          ]);

          curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
          //curl_setopt($curl, CURLOPT_USERPWD, "username:password");
      
          curl_setopt($curl, CURLOPT_URL, $url);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      
          $result = curl_exec($curl);
      
          curl_close($curl);
      
          return $result;
      }

      function getFile($url){

          $opts = array(
            'http'=>array(
              'method'=>"GET",
              'header'=>"Content-Type: application/json" 
            )
          );

          $context = stream_context_create($opts);

          // Open the file using the HTTP headers set above
          $fileContents = file_get_contents($url, false, $context);

          return $fileContents;

      }

      function getArchive(){
        $servername = "localhost";
        $username = "mysql_root";
        $password = "root";
        
        try {
            // Create connection
            $conn = mysqli_connect($servername, $username, $password);

            if (mysqli_connect_errno())
            {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            }
            //echo "Connected successfully";

            $query = "select * from php_test.weather_forecast_week wfw";
            
            //$result = $conn->query($sql);
            
            $result = mysqli_query($conn, $query);

            $i=0;
            while ($row = mysqli_fetch_assoc($result)) 
            {
                $return[$i]['id'] = $row['id'];
                $return[$i]['dateTime'] = $row['dateTime'];
                $return[$i]['XML'] = $row['XML'];
                $i++;
            }

            $conn->close();
            if ($result->num_rows <= 0) { 
              return false;
            }
            return $return; 
        }
        catch(Exception $e) {
          echo  'Message: ' .$e->getMessage();
          $conn->close();
          return false;
        }
      }

      function getTags($xml){
        $dom = simplexml_load_string($xml);
        $xmlTags = $dom->{"Middellange_x0020_en_x0020_lange_x0020_Termijn"}[0];

        $return = [];
        for ($i = 1; $i <= 6; $i++) { 
          $return[$i]["dag".(string)$i ."_ddd"]                 = (string)$xmlTags->{"dag".(string)$i ."_ddd"};
          $return[$i]["zonneschijnkans_dag".(string)$i]         = (string)$xmlTags->{"zonneschijnkans_dag".(string)$i};
          $return[$i]["neerslagkans_dag".(string)$i]            = (string)$xmlTags->{"neerslagkans_dag".(string)$i};
          $return[$i]["neerslaghoeveelheid_min_dag".(string)$i] = (string)$xmlTags->{"neerslaghoeveelheid_min_dag".(string)$i}[0];
          $return[$i]["minimumtemperatuur_min_dag".(string)$i]  = (string)$xmlTags->{"minimumtemperatuur_min_dag".(string)$i}[0];
          $return[$i]["maximumtemperatuur_min_dag".(string)$i]  = (string)$xmlTags->{"maximumtemperatuur_min_dag".(string)$i}[0];
          $return[$i]["windrichting_dag".(string)$i]            = (string)$xmlTags->{"windrichting_dag".(string)$i};
          $return[$i]["windkracht_dag".(string)$i]              = (string)$xmlTags->{"windkracht_dag".(string)$i};
        }
        return $return;
      }


      function saveArchive($xml){
        $servername = "localhost";
        $username = "mysql_root";
        $password = "root";
        
        try {
          $servername = "localhost";
          $username = "mysql_root";
          $password = "root";
          $dbname = "php_test";

          
          // Create connection
          $conn = mysqli_connect($servername, $username, $password, $dbname);
          // Check connection
          if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
          }
          
          $sDate = date("Y-m-d H:i:s"); // 2015-04-07 07:12:51
          $sql = "INSERT INTO php_test.weather_forecast_week (dateTime, XML)
          VALUES ('$sDate', '$xml')";
          
          if (mysqli_query($conn, $sql)) {
            echo "New record created successfully";
          } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
          }
          
          mysqli_close($conn);

        }
        catch(Exception $e) {
          echo  'Message: ' .$e->getMessage();
     
          return false;
        }
      }
      ?>
      </head>

      <body>     
        </br>
        <?php 

      $url = "https://api.dataplatform.knmi.nl/open-data/v1/datasets/outlook_weather_forecast/versions/1.0/files";

    if (apcu_exists("weather_first")) {
      echo "<br>The key 'weather_first' exists in APCu.";
      $data = apcu_fetch("weather_first");
    } else {
      echo "<br>The key 'weather_first' does not exist in APCu.";
      $data = CallAPI("GET",$url);
      apcu_store("weather_first", $data, 3600);
    }   

    $dataJson = json_decode($data);
    $files = $dataJson->{"files"};
    $latesDate = "";
    $Filename = "";
    foreach($files as $file) {
      //echo "<BR>".$file->{"filename"};
      $a = str_replace("+00:00","",$file->{"lastModified"});
      //echo "<BR>".$a ;
      if($latesDate == "" && str_contains($file->{"filename"}, 'xml')){
        $latesDate = $a;
        $Filename = $file->{"filename"}; 
      }else{
        if(strcmp( $a,$latesDate)>0 && str_contains($file->{"filename"}, '.xml')){
          $latesDate = $a; 
          $Filename = $file->{"filename"}; 
        } 
      }

    }

    $url2 = "https://api.dataplatform.knmi.nl/open-data/v1/datasets/outlook_weather_forecast/versions/1.0/files/".$Filename."/url";

    if (apcu_exists("weather_file")) {
      echo "<br>The key 'weather_file' exists in APCu.";
      $data2 = apcu_fetch("weather_file");
    } else {
      echo "<br>The key 'weather_file' does not exist in APCu.";
      $data2 = CallAPI("GET",$url2);
      apcu_store("weather_file", $data2, 3600);
    }  


    $dataJson2 = json_decode($data2);
    $url3 = $dataJson2->{"temporaryDownloadUrl"};
    $content = getFile($url3);    
    $dom = simplexml_load_string($content);

    $weather = $dom->{"Middellange_x0020_en_x0020_lange_x0020_Termijn"}[0];

    //save XML
    saveArchive($content);
    ?>

<body>    

<table width=451 border=0 cellspacing=0 cellpadding=0 class="realtable">
<tr class="trcolor">
<th></th>
<?php for ($i = 1; $i <= 6; $i++) {  ?>
    <th align="left" width=40><?php echo $weather->{"dag".(string)$i ."_ddd"};?></th>
<?php }?>
</tr>
<tr>
<td>Zonneschijn (%)</td>
<?php for ($i = 1; $i <= 6; $i++) {  ?>
    <td align="left" width=40><?php echo $weather->{"zonneschijnkans_dag".(string)$i};?></td>
<?php }?>
</tr>
<tr>
    <td>Neerslagkans (%)</td>
<?php for ($i = 1; $i <= 6; $i++) {  ?>
    <td align="left" width=40><?php echo $weather->{"neerslagkans_dag".(string)$i};?></td>
<?php }?>
</tr>
<tr>
    <td>Neerslaghoeveelheid (mm)</td>
<?php for ($i = 1; $i <= 6; $i++) {  ?>
    <td align="left" width=40><?php echo $weather->{"neerslaghoeveelheid_min_dag".(string)$i}[0];?></td>
<?php }?>
</tr>
<tr>
    <td>Minimumtemperatuur (&deg;C)</td>
<?php for ($i = 1; $i <= 6; $i++) {  ?>
    <td align="left" width=40><?php echo $weather->{"minimumtemperatuur_min_dag".(string)$i}[0];?></td>
<?php }?>
</tr>
<tr>
    <td>Middagtemperatuur (&deg;C)</td>
<?php for ($i = 1; $i <= 6; $i++) {  ?>
    <td align="left" width=40><?php echo $weather->{"maximumtemperatuur_min_dag".(string)$i}[0];?></td>
<?php }?>
</tr>
<tr>
    <td>Windrichting</td>
<?php for ($i = 1; $i <= 6; $i++) {  ?>
    <td align="left" width=40><?php echo $weather->{"windrichting_dag".(string)$i};?></td>
<?php }?>
</tr>
<tr>
    <td>Windkracht (bft)</td>
<?php for ($i = 1; $i <= 6; $i++) {  ?>
    <td align="left" width=40><?php echo $weather->{"windkracht_dag".(string)$i};?></td>
<?php }?>
</tr>

<?php $archiveTable = getArchive();?>

</table>

<br>

<?php $archiveTable = getArchive();?>

<h<h2>Archive of save weather forecast</h2>".
<table class="arcTB">
  <tr>
    <th>id</th>
    <th>dateTime</th>
    <th>Open Table</th>
  </tr>
  <?php   
     $archive ="";
      for ($i = 0; $i < count($archiveTable); $i++) {
      $tags = getTags($archiveTable[$i]["XML"]);
      $tagsJson = json_encode($tags);
       $bodytag = str_replace("\"", "\\\"", $tagsJson );
        $archive = $archive."<tr>";
        $archive = $archive."<td>" . $archiveTable[$i]["id"]. "</td><td>" . $archiveTable[$i]["dateTime"]. "</td>"
        ."<td><span class='openTable' onclick='openTableClick(\"".$bodytag."\");' ><i class='fa fa-file-excel-o'></i></span></td>";
        $archive = $archive."</tr>";
        
      }     
   echo $archive;
  ?>
  </table>
  


<!-- The Modal -->
<div id="myModal" class="modal">
  <!-- Modal content -->
  <div class="modal-content">
    <span class="close">&times;</span>
    <div id="tt">
      <p>this is me</p>
    </div>
    
  </div>

</div>



<script>
// Get the modal
var modal = document.getElementById("myModal");

// Get the button that opens the modal
var btn = document.getElementById("myBtn");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];


// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks the button, open the modal 
openTableClick = function(tagsJson) {

  json = tagsJson.replace('\"','"');
  const objTags = JSON.parse(json);

  const list = document.getElementById("tt");
  list.removeChild(list.firstElementChild);
  
  var table = document.createElement('table');
  
    var tr1 = document.createElement('tr');
    var th0 = document.createElement('th');
    var th1 = document.createElement('th');
    var th2 = document.createElement('th');
    var th3 = document.createElement('th');
    var th4 = document.createElement('th');
    var th5 = document.createElement('th');
    var th6 = document.createElement('th');

    var text1 = document.createTextNode(objTags[1].dag1_ddd);
    var text2 = document.createTextNode(objTags[2].dag2_ddd);
    var text3 = document.createTextNode(objTags[3].dag3_ddd);
    var text4 = document.createTextNode(objTags[4].dag4_ddd);
    var text5 = document.createTextNode(objTags[5].dag5_ddd);
    var text6 = document.createTextNode(objTags[6].dag6_ddd);

    th1.appendChild(text1);
    th2.appendChild(text2);
    th3.appendChild(text3);
    th4.appendChild(text4);
    th5.appendChild(text5);
    th6.appendChild(text6);

    tr1.appendChild(th0);
    tr1.appendChild(th1);
    tr1.appendChild(th2);
    tr1.appendChild(th3);
    tr1.appendChild(th4);
    tr1.appendChild(th5);
    tr1.appendChild(th6);

    var tr2 = document.createElement('tr');
    var td0 = document.createElement('td');
    var td1 = document.createElement('td');
    var td2 = document.createElement('td');
    var td3 = document.createElement('td');
    var td4 = document.createElement('td');
    var td5 = document.createElement('td');
    var td6 = document.createElement('td');

    var text0 = document.createTextNode("Zonneschijn");
    var text1 = document.createTextNode(objTags[1].zonneschijnkans_dag1);
    var text2 = document.createTextNode(objTags[2].zonneschijnkans_dag2);
    var text3 = document.createTextNode(objTags[3].zonneschijnkans_dag3);
    var text4 = document.createTextNode(objTags[4].zonneschijnkans_dag4);
    var text5 = document.createTextNode(objTags[5].zonneschijnkans_dag5);
    var text6 = document.createTextNode(objTags[6].zonneschijnkans_dag6);

    td0.appendChild(text0);
    td1.appendChild(text1);
    td2.appendChild(text2);
    td3.appendChild(text3);
    td4.appendChild(text4);
    td5.appendChild(text5);
    td6.appendChild(text6);

    tr2.appendChild(td0);
    tr2.appendChild(td1);
    tr2.appendChild(td2);
    tr2.appendChild(td3);
    tr2.appendChild(td4);
    tr2.appendChild(td5);
    tr2.appendChild(td6);

    var tr3 = document.createElement('tr');
    var td0 = document.createElement('td');
    var td1 = document.createElement('td');
    var td2 = document.createElement('td');
    var td3 = document.createElement('td');
    var td4 = document.createElement('td');
    var td5 = document.createElement('td');
    var td6 = document.createElement('td');

    var text0 = document.createTextNode("Neerslagkans");
    var text1 = document.createTextNode(objTags[1].neerslagkans_dag1);
    var text2 = document.createTextNode(objTags[2].neerslagkans_dag2);
    var text3 = document.createTextNode(objTags[3].neerslagkans_dag3);
    var text4 = document.createTextNode(objTags[4].neerslagkans_dag4);
    var text5 = document.createTextNode(objTags[5].neerslagkans_dag5);
    var text6 = document.createTextNode(objTags[6].neerslagkans_dag6);

    td0.appendChild(text0);
    td1.appendChild(text1);
    td2.appendChild(text2);
    td3.appendChild(text3);
    td4.appendChild(text4);
    td5.appendChild(text5);
    td6.appendChild(text6);

    tr3.appendChild(td0);
    tr3.appendChild(td1);
    tr3.appendChild(td2);
    tr3.appendChild(td3);
    tr3.appendChild(td4);
    tr3.appendChild(td5);
    tr3.appendChild(td6);

    var tr4= document.createElement('tr');
    var td0 = document.createElement('td');
    var td1 = document.createElement('td');
    var td2 = document.createElement('td');
    var td3 = document.createElement('td');
    var td4 = document.createElement('td');
    var td5 = document.createElement('td');
    var td6 = document.createElement('td');

    var text0 = document.createTextNode("Neerslaghoeveelheid");
    var text1 = document.createTextNode(objTags[1].neerslaghoeveelheid_min_dag1);
    var text2 = document.createTextNode(objTags[2].neerslaghoeveelheid_min_dag2);
    var text3 = document.createTextNode(objTags[3].neerslaghoeveelheid_min_dag3);
    var text4 = document.createTextNode(objTags[4].neerslaghoeveelheid_min_dag4);
    var text5 = document.createTextNode(objTags[5].neerslaghoeveelheid_min_dag5);
    var text6 = document.createTextNode(objTags[6].neerslaghoeveelheid_min_dag6);

    td0.appendChild(text0);
    td1.appendChild(text1);
    td2.appendChild(text2);
    td3.appendChild(text3);
    td4.appendChild(text4);
    td5.appendChild(text5);
    td6.appendChild(text6);

    tr4.appendChild(td0);
    tr4.appendChild(td1);
    tr4.appendChild(td2);
    tr4.appendChild(td3);
    tr4.appendChild(td4);
    tr4.appendChild(td5);
    tr4.appendChild(td6);

    var tr5 = document.createElement('tr');

    var td0 = document.createElement('td');
    var td1 = document.createElement('td');
    var td2 = document.createElement('td');
    var td3 = document.createElement('td');
    var td4 = document.createElement('td');
    var td5 = document.createElement('td');
    var td6 = document.createElement('td');

    var text0 = document.createTextNode("Minimumtemperatuur");
    var text1 = document.createTextNode(objTags[1].minimumtemperatuur_min_dag1);
    var text2 = document.createTextNode(objTags[2].minimumtemperatuur_min_dag2);
    var text3 = document.createTextNode(objTags[3].minimumtemperatuur_min_dag3);
    var text4 = document.createTextNode(objTags[4].minimumtemperatuur_min_dag4);
    var text5 = document.createTextNode(objTags[5].minimumtemperatuur_min_dag5);
    var text6 = document.createTextNode(objTags[6].minimumtemperatuur_min_dag6);

    td0.appendChild(text0);
    td1.appendChild(text1);
    td2.appendChild(text2);
    td3.appendChild(text3);
    td4.appendChild(text4);
    td5.appendChild(text5);
    td6.appendChild(text6);

    tr5.appendChild(td0);
    tr5.appendChild(td1);
    tr5.appendChild(td2);
    tr5.appendChild(td3);
    tr5.appendChild(td4);
    tr5.appendChild(td5);
    tr5.appendChild(td6);

    var tr6 = document.createElement('tr');
    var td0 = document.createElement('td');
    var td1 = document.createElement('td');
    var td2 = document.createElement('td');
    var td3 = document.createElement('td');
    var td4 = document.createElement('td');
    var td5 = document.createElement('td');
    var td6 = document.createElement('td');

    var text0 = document.createTextNode("Middagtemperatuur");
    var text1 = document.createTextNode(objTags[1].maximumtemperatuur_min_dag1);
    var text2 = document.createTextNode(objTags[2].maximumtemperatuur_min_dag2);
    var text3 = document.createTextNode(objTags[3].maximumtemperatuur_min_dag3);
    var text4 = document.createTextNode(objTags[4].maximumtemperatuur_min_dag4);
    var text5 = document.createTextNode(objTags[5].maximumtemperatuur_min_dag5);
    var text6 = document.createTextNode(objTags[6].maximumtemperatuur_min_dag6);

    td0.appendChild(text0);
    td1.appendChild(text1);
    td2.appendChild(text2);
    td3.appendChild(text3);
    td4.appendChild(text4);
    td5.appendChild(text5);
    td6.appendChild(text6);

    tr6.appendChild(td0);
    tr6.appendChild(td1);
    tr6.appendChild(td2);
    tr6.appendChild(td3);
    tr6.appendChild(td4);
    tr6.appendChild(td5);
    tr6.appendChild(td6);

    var tr7 = document.createElement('tr');
    var td0 = document.createElement('td');
    var td1 = document.createElement('td');
    var td2 = document.createElement('td');
    var td3 = document.createElement('td');
    var td4 = document.createElement('td');
    var td5 = document.createElement('td');
    var td6 = document.createElement('td');

    var text0 = document.createTextNode("Windrichting");
    var text1 = document.createTextNode(objTags[1].windrichting_dag1);
    var text2 = document.createTextNode(objTags[2].windrichting_dag2);
    var text3 = document.createTextNode(objTags[3].windrichting_dag3);
    var text4 = document.createTextNode(objTags[4].windrichting_dag4);
    var text5 = document.createTextNode(objTags[5].windrichting_dag5);
    var text6 = document.createTextNode(objTags[6].windrichting_dag6);

    td0.appendChild(text0);
    td1.appendChild(text1);
    td2.appendChild(text2);
    td3.appendChild(text3);
    td4.appendChild(text4);
    td5.appendChild(text5);
    td6.appendChild(text6);

    tr7.appendChild(td0);
    tr7.appendChild(td1);
    tr7.appendChild(td2);
    tr7.appendChild(td3);
    tr7.appendChild(td4);
    tr7.appendChild(td5);
    tr7.appendChild(td6);

    var tr8 = document.createElement('tr');
    var td0 = document.createElement('td');
    var td1 = document.createElement('td');
    var td2 = document.createElement('td');
    var td3 = document.createElement('td');
    var td4 = document.createElement('td');
    var td5 = document.createElement('td');
    var td6 = document.createElement('td');

    var text0 = document.createTextNode("Windkracht");
    var text1 = document.createTextNode(objTags[1].windkracht_dag1);
    var text2 = document.createTextNode(objTags[2].windkracht_dag2);
    var text3 = document.createTextNode(objTags[3].windkracht_dag3);
    var text4 = document.createTextNode(objTags[4].windkracht_dag4);
    var text5 = document.createTextNode(objTags[5].windkracht_dag5);
    var text6 = document.createTextNode(objTags[6].windkracht_dag6);

    td0.appendChild(text0);
    td1.appendChild(text1);
    td2.appendChild(text2);
    td3.appendChild(text3);
    td4.appendChild(text4);
    td5.appendChild(text5);
    td6.appendChild(text6);

    tr8.appendChild(td0);
    tr8.appendChild(td1);
    tr8.appendChild(td2);
    tr8.appendChild(td3);
    tr8.appendChild(td4);
    tr8.appendChild(td5);
    tr8.appendChild(td6);

  table.appendChild(tr1);
  table.appendChild(tr2);
  table.appendChild(tr3);
  table.appendChild(tr4);
  table.appendChild(tr5);
  table.appendChild(tr6);
  table.appendChild(tr7);
  table.appendChild(tr8);

  document.getElementById("tt").appendChild(table);
  modal.style.display = "block";
}
</script>
  </body>
</html>
