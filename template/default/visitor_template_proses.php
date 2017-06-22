<?php
require_once 'db_connect.php';

$db = new DB_CONNECT();

$id = $_GET['id'];
$sql = "SELECT * FROM member WHERE member_id = $id";
$results = mysql_query ($sql) or die(mysql_error());

    while ($row = mysql_fetch_assoc($results)) {
        $data[] = array(
                    'memberid' => $row->member_id,
                    'name' => $row->member_name,
                    'email' => $row->member_email
    );
}
echo json_encode($data);
//return json_encode($data);
?>
