<?php
  require_once 'db_connect.php';

  $db = new DB_CONNECT();

  $id = $_GET['id'];

  $sql = "SELECT * FROM member WHERE member_id = $id";

  $results = mysql_query ($sql) or die(mysql_error());

  if(mysql_num_rows($results)>0){
    while($row = mysql_fetch_array($results)){
      //temp data
      $data[] = array(
        'memberid' => $row["member_id"],
        'name' => $row["member_name"],
        'email' => $row["member_email"]
      );
    }

    echo  json_encode($data);
  }
?>
