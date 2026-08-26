<?php
defined('_VALID') or die('Restricted Access!');
require $config['BASE_DIR']. '/classes/pagination.class.php';

Auth::checkAdmin();

$rs = $conn->execute("SHOW TABLES LIKE 'admin_activity_log'");
if (!$rs || $conn->Affected_Rows() == 0) {
    $errors[] = 'admin_activity_log table was not found. Run scripts/migrate_admin_users.php first.';
    $smarty->assign('logs', array());
    $smarty->assign('logs_total', 0);
    $smarty->assign('paging', '');
    $smarty->assign('admin_options', array());
    $smarty->assign('filter_admin', 0);
    $smarty->assign('filter_date_from', '');
    $smarty->assign('filter_date_to', '');
    return;
}

$page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
$admin_role = Auth::getAdminRole();
$current_admin_id = Auth::getAdminId();

$filter_admin = (isset($_GET['admin_id'])) ? intval($_GET['admin_id']) : 0;
$filter_date_from = (isset($_GET['date_from'])) ? trim($_GET['date_from']) : '';
$filter_date_to   = (isset($_GET['date_to'])) ? trim($_GET['date_to']) : '';

$where = ' WHERE 1';
if ($admin_role != 'superadmin') {
    $where .= " AND admin_id = " .$current_admin_id;
    $filter_admin = $current_admin_id;
} elseif ($filter_admin > 0) {
    $where .= " AND admin_id = " .$filter_admin;
}

if ($filter_date_from != '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date_from)) {
    $where .= " AND created_at >= " .$conn->qStr($filter_date_from . ' 00:00:00');
}
if ($filter_date_to != '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date_to)) {
    $where .= " AND created_at <= " .$conn->qStr($filter_date_to . ' 23:59:59');
}

$count_sql = "SELECT COUNT(id) AS total_logs FROM admin_activity_log" .$where;
$rs = $conn->execute($count_sql);
$total_logs = intval($rs->fields['total_logs']);

$pagination = new Pagination(25);
$limit      = $pagination->getLimit($total_logs);
$paging     = $pagination->getAdminPagination();

$sql = "SELECT id, admin_id, admin_username, action, module, details, ip_address, created_at
        FROM admin_activity_log" .$where. " ORDER BY id DESC LIMIT " .$limit;
$rs = $conn->execute($sql);
$logs = $rs->getrows();

$admin_options = array();
if ($admin_role == 'superadmin') {
    $sql = "SELECT id, username FROM admin_users ORDER BY username ASC";
    $rs = $conn->execute($sql);
    $admin_options = $rs->getrows();
}

$smarty->assign('logs', $logs);
$smarty->assign('logs_total', $total_logs);
$smarty->assign('paging', $paging);
$smarty->assign('page', $page);
$smarty->assign('filter_admin', $filter_admin);
$smarty->assign('filter_date_from', $filter_date_from);
$smarty->assign('filter_date_to', $filter_date_to);
$smarty->assign('admin_options', $admin_options);
?>
