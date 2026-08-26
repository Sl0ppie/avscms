<?php
defined('_VALID') or die('Restricted Access!');
require $config['BASE_DIR']. '/classes/pagination.class.php';

Auth::requireSuperAdmin();

$rs = $conn->execute("SHOW TABLES LIKE 'admin_users'");
if (!$rs || $conn->Affected_Rows() == 0) {
    $errors[] = 'admin_users table was not found. Run scripts/migrate_admin_users.php first.';
    $smarty->assign('admins', array());
    $smarty->assign('admins_total', 0);
    $smarty->assign('paging', '');
    $smarty->assign('role_filter', '');
    $smarty->assign('status_filter', '');
    return;
}

$page   = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
$remove = NULL;

if ( isset($_GET['a']) ) {
    $action  = trim($_GET['a']);
    $adminid = (isset($_GET['AID'])) ? intval($_GET['AID']) : 0;

    if ($adminid > 0) {
        if ($adminid == Auth::getAdminId() && ($action == 'delete' || $action == 'harddelete')) {
            $errors[] = 'You cannot delete your own admin account.';
        } else {
            $sql = "SELECT id, username, role, is_active FROM admin_users WHERE id = " .$adminid. " LIMIT 1";
            $rs  = $conn->execute($sql);
            if ($conn->Affected_Rows() == 1) {
                $admin_row = $rs->fields;

                if ($admin_row['role'] == 'superadmin' && ($action == 'delete' || $action == 'harddelete' || $action == 'deactivate')) {
                    $sql = "SELECT COUNT(id) AS total_superadmins FROM admin_users WHERE role = 'superadmin' AND is_active = 1";
                    $rs  = $conn->execute($sql);
                    if (intval($rs->fields['total_superadmins']) <= 1) {
                        $errors[] = 'Cannot modify the last active superadmin account.';
                        $action = '';
                    }
                }

                if ($action == 'delete') {
                    $sql = "UPDATE admin_users SET is_active = 0 WHERE id = " .$adminid. " LIMIT 1";
                    $conn->execute($sql);
                    $messages[] = 'Admin user was deactivated successfully.';
                    AdminLog::write('Deactivated admin user #' .$adminid, 'adminusers', 'Soft deleted admin account ' .$admin_row['username']);
                } elseif ($action == 'harddelete') {
                    $sql = "DELETE FROM admin_users WHERE id = " .$adminid. " LIMIT 1";
                    $conn->execute($sql);
                    $messages[] = 'Admin user was deleted successfully.';
                    AdminLog::write('Deleted admin user #' .$adminid, 'adminusers', 'Hard deleted admin account ' .$admin_row['username']);
                } elseif ($action == 'activate') {
                    $sql = "UPDATE admin_users SET is_active = 1 WHERE id = " .$adminid. " LIMIT 1";
                    $conn->execute($sql);
                    $messages[] = 'Admin user was activated successfully.';
                    AdminLog::write('Activated admin user #' .$adminid, 'adminusers', 'Activated admin account ' .$admin_row['username']);
                } elseif ($action == 'deactivate') {
                    $sql = "UPDATE admin_users SET is_active = 0 WHERE id = " .$adminid. " LIMIT 1";
                    $conn->execute($sql);
                    $messages[] = 'Admin user was deactivated successfully.';
                    AdminLog::write('Deactivated admin user #' .$adminid, 'adminusers', 'Deactivated admin account ' .$admin_row['username']);
                }
            } else {
                $errors[] = 'Admin user does not exist!';
            }
        }
    } else {
        $errors[] = 'Invalid admin user id!';
    }

    $remove = '&a=' .$action. '&AID=' .$adminid;
}

$role_filter   = (isset($_GET['role'])) ? trim($_GET['role']) : '';
$status_filter = (isset($_GET['status'])) ? trim($_GET['status']) : '';

$where = ' WHERE 1';
if ($role_filter == 'admin' || $role_filter == 'superadmin') {
    $where .= " AND role = " .$conn->qStr($role_filter);
}
if ($status_filter === 'active') {
    $where .= " AND is_active = 1";
} elseif ($status_filter === 'inactive') {
    $where .= " AND is_active = 0";
}

$count_sql = "SELECT COUNT(id) AS total_admins FROM admin_users" .$where;
$rs = $conn->execute($count_sql);
$total_admins = intval($rs->fields['total_admins']);

$pagination = new Pagination(20);
$limit      = $pagination->getLimit($total_admins);
$paging     = $pagination->getAdminPagination($remove);

$sql = "SELECT id, username, email, role, is_active, created_at, last_login
        FROM admin_users" .$where. " ORDER BY id DESC LIMIT " .$limit;
$rs = $conn->execute($sql);
$admins = $rs->getrows();

$smarty->assign('admins', $admins);
$smarty->assign('admins_total', $total_admins);
$smarty->assign('paging', $paging);
$smarty->assign('page', $page);
$smarty->assign('role_filter', $role_filter);
$smarty->assign('status_filter', $status_filter);
?>
