<?php
defined('_VALID') or die('Restricted Access!');

require $config['BASE_DIR']. '/classes/filter.class.php';
require $config['BASE_DIR']. '/include/compat/json.php';
require $config['BASE_DIR']. '/include/adodb/adodb.inc.php';
require $config['BASE_DIR']. '/include/dbconn.php';
require $config['BASE_DIR']. '/include/function_video.php';
require_once $config['BASE_DIR']. '/classes/auth.class.php';
require $config['BASE_DIR']. '/classes/adminlog.class.php';
Auth::checkAdmin();

$response = array('status' => 0);

$filter  = new VFilter();
$vid     = $filter->get('video_id', 'INTEGER');
$vstatus = $filter->get('video_status', 'INTEGER');

if ($vstatus) {
	$sql = "UPDATE video SET active = '0' WHERE VID = " .$conn->qStr($vid). " LIMIT 1";
	$conn->execute($sql);
	if ( $conn->Affected_Rows() == 1 ) {	
		$response['status'] = 1;	
		AdminLog::write('Unpublished video #' .$vid, 'videos', 'Set video active=0');
	}
} else {
	$sql = "UPDATE video SET active = '1' WHERE VID = " .$conn->qStr($vid). " LIMIT 1";
	$conn->execute($sql);
	if ( $conn->Affected_Rows() == 1 ) {
		//send_video_approve_email($vid);
		$response['status'] = 1;
		AdminLog::write('Published video #' .$vid, 'videos', 'Set video active=1');
	}
}

echo json_encode($response);
die();
?>
