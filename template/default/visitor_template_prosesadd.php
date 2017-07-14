<?php
  require_once 'db_connect.php';
  // connecting to db
  $db = new DB_CONNECT();

  $memberid = $_POST['memberid'];
  $activities = $_POST['activities'];
  $checkin = date('Y-m-d H:i:s');

  // mysql inserting a new row
  $result = mysql_query("INSERT INTO visitor_count(member_id, activities, checkin_date) VALUES('$memberid', '$activities', '$checkin')");
  if($result){
    header('Location: http://localhost/perpus/?p=visitor');
  } else {
    echo mysql_error();
  }

?>
