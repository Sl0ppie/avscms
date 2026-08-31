<?php
defined('_VALID') or die('Restricted Access!');

require $config['BASE_DIR']. '/classes/filter.class.php';
require $config['BASE_DIR']. '/include/compat/json.php';
require $config['BASE_DIR']. '/include/adodb/adodb.inc.php';
require $config['BASE_DIR']. '/include/dbconn.php';
require_once $config['BASE_DIR']. '/classes/auth.class.php';
require $config['BASE_DIR']. '/classes/adminlog.class.php';
Auth::checkAdmin();

$response = array('status' => 0);

$filter  = new VFilter();
$nid     = $filter->get('notice_id', 'INTEGER');
$gstatus = $filter->get('notice_status', 'INTEGER');

if ($gstatus) {
	$sql = "UPDATE notice SET status = '0' WHERE NID = " .$conn->qStr($nid). " LIMIT 1";
	$conn->execute($sql);
	if ( $conn->Affected_Rows() == 1 ) {	
		$response['status'] = 1;
		AdminLog::write('Unpublished notice #' .$nid, 'notices', 'Set notice status=0');
	}
} else {
	$sql = "UPDATE notice SET status = '1' WHERE NID = " .$conn->qStr($nid). " LIMIT 1";
	$conn->execute($sql);
	if ( $conn->Affected_Rows() == 1 ) {
		$response['status'] = 1;
		AdminLog::write('Published notice #' .$nid, 'notices', 'Set notice status=1');
	}
}

echo json_encode($response);
die();
?>
