<?php
define('_VALID', true);
require 'include/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$ap_mysqli = new mysqli("localhost","beastpornhub","be#po*hub62@8","beastpornhub");
$ps_mysqli = new mysqli("localhost","pornsocket","p2sm4s^erp4ss8","pornsocket");

$ap_mysqli->set_charset('utf8mb4');
$ps_mysqli->set_charset('utf8mb4');

$channel_id = 1;
$to_channel = 6;
$to_user = 5;

if ($ap_mysqli->connect_errno || $ps_mysqli->connect_errno) {
  echo "Failed to connect to MySQL: <br/>";
  exit();
}

// Get videos in a category
$result = $ap_mysqli->query( "SELECT * FROM video WHERE channel = " . $channel_id . " AND active = 1 AND type = 'public'" );
echo "AP has " . $result->num_rows . " in channel " . $channel_id . "<br/>";
//die();

while( $v = $result->fetch_assoc() ) {
	$old_vid = $v['VID'];
	$new_vid = (int)$old_vid + 400000;
	$v['VID'] = $new_vid;
	$v['vkey'] = substr(bin2hex(random_bytes(10)), 0, 20);
	$v['channel'] = $to_channel;
	$v['UID'] = $to_user;
	$v['server'] = 'https://animalpornrocks.com';

	$vresult = $ps_mysqli->query( "SELECT * FROM video WHERE VID = " . $v['VID'] );
	if( $vresult->num_rows ) {
		echo "Target already has VID " . $v['VID'] . "<br/>";
		continue;
	}

	//echo "Insert.";

	$data = $v;

	//var_export( $data );

	$columns = array_keys($data);
	$columnSql = implode(', ', array_map(fn($c) => "`$c`", $columns));
	$placeholders = implode(', ', array_fill(0, count($data), '?'));

	//var_export($columnSql);

	$sql = "INSERT INTO `video` ($columnSql) VALUES ($placeholders)";

	//var_export($sql);

	//echo count($data);

	$values = array_values($data);

	try {
		$stmt = $ps_mysqli->prepare( $sql );

		$stmt->bind_param(
			'isssssssssssssssssssssssssssssssssssssssssssssssssss',
			...$values
		);

		$stmt->execute();
	} catch (mysqli_sql_exception $e) {
    echo "MySQL error: " . $e->getMessage();
	}

	echo "Imported VID " . $old_vid . " from AP<br/>";

	//sleep(5);
}

