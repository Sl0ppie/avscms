<?php
defined('_VALID') or die('Restricted Access!');

Auth::checkAdmin();

require $config['BASE_DIR']. '/include/config.template.php';

function admin_users_table_exists_settings()
{
    global $conn;
    $rs = $conn->execute("SHOW TABLES LIKE 'admin_users'");
    return ($rs && $conn->Affected_Rows() > 0);
}

$admin_id         = Auth::getAdminId();
$use_admin_table  = ($admin_id > 0 && admin_users_table_exists_settings());
$current_admin    = false;

$legacy_admin_name = isset($config['admin_name']) ? $config['admin_name'] : '';
$legacy_admin_pass = isset($config['admin_pass']) ? $config['admin_pass'] : '';

$admin_name       = isset($_SESSION['AUSERNAME']) && $_SESSION['AUSERNAME'] != '' ? $_SESSION['AUSERNAME'] : $legacy_admin_name;
$admin_email      = isset($config['admin_email']) ? $config['admin_email'] : '';
$noreply_email    = isset($config['noreply_email']) ? $config['noreply_email'] : '';

if ($use_admin_table) {
    $sql = "SELECT id, username, email, password, role FROM admin_users WHERE id = " .intval($admin_id). " AND is_active = 1 LIMIT 1";
    $rs  = $conn->execute($sql);
    if ($conn->Affected_Rows() == 1) {
        $current_admin = $rs->fields;
        $admin_name    = $current_admin['username'];
        $admin_email   = $current_admin['email'];
    } else {
        $use_admin_table = false;
    }
}

if ( isset($_POST['submit_admin']) ) {
    $filter                 = new VFilter();

    $admin_name             = $filter->get('admin_name');
    $admin_pass             = $filter->get('admin_pass');
    $admin_pass_np          = $filter->get('admin_pass_np');
    $admin_pass_cnp         = $filter->get('admin_pass_cnp');
    $admin_email            = $filter->get('admin_email');
    $noreply_email          = $filter->get('noreply_email');

    if ( $admin_name == '' ) {
        $errors[]   = 'Admin Name (used for siteadmin login) cannot be blank!';
        $err['admin_name'] = 1;
    } elseif ( strlen($admin_name) < 5 ) {
        $errors[]   = 'Admin Name (used for siteadmin login) must be at least 6 characters long!';
        $err['admin_name'] = 1;
    }

    if ($use_admin_table && $current_admin) {
        $valid_password = false;
        if (function_exists('password_verify')) {
            $valid_password = password_verify($admin_pass, $current_admin['password']);
        }
        if (!$valid_password && $admin_pass === $current_admin['password']) {
            $valid_password = true;
        }

        if (!$valid_password) {
            $errors[] = 'Wrong Admin Password!';
            $err['admin_pass'] = 1;
        }

        $sql = "SELECT id FROM admin_users WHERE username = " .$conn->qStr($admin_name). " AND id != " .intval($admin_id). " LIMIT 1";
        $conn->execute($sql);
        if ($conn->Affected_Rows() > 0) {
            $errors[] = 'Admin username is already used by another account!';
            $err['admin_name'] = 1;
        }
    } else {
        if ( $admin_pass != $legacy_admin_pass ) {
            $errors[]   = 'Wrong Admin Password!';
            $err['admin_pass'] = 1;
        }
    }

    if ( $admin_pass_np != '' || $admin_pass_cnp != '' ) {
        if ( strlen($admin_pass_np) < 5 ) {
            $errors[]   = 'Admin Password (used for siteadmin login) must be at least 6 characters long!';
            $err['admin_pass_np'] = 1;
            $err['admin_pass_cnp'] = 1;
        } elseif ( $admin_pass_np != $admin_pass_cnp ) {
            $errors[]   = 'New Password and Confirm New Password do not match!';
            $err['admin_pass_np'] = 1;
            $err['admin_pass_cnp'] = 1;
        }
    }

    if ( $admin_email == '' ) {
        $errors[]   = 'Admin Email field cannot be blank!';
        $err['admin_email'] = 1;
    } elseif ( !VValidation::email_($admin_email) ) {
        $errors[]   = 'Admin Email field is not a valid email address!';
        $err['admin_email'] = 1;
    }

    if ( $noreply_email == '' ) {
        $errors[]   = 'Noreply Email field cannot be blank!';
        $err['noreply_email'] = 1;
    } elseif ( !VValidation::email_($noreply_email) ) {
        $errors[]   = 'Noreply Email field is not a valid email address!';
        $err['noreply_email'] = 1;
    }

    if ( !$errors ) {
        $password_updated = false;

        if ($use_admin_table) {
            $set_sql = "username = " .$conn->qStr($admin_name). ", email = " .$conn->qStr($admin_email);
            if ($admin_pass_np != '' && function_exists('password_hash')) {
                $hash = password_hash($admin_pass_np, PASSWORD_BCRYPT);
                if ($hash) {
                    $set_sql .= ", password = " .$conn->qStr($hash);
                    $password_updated = true;
                }
            } elseif ($admin_pass_np != '') {
                $set_sql .= ", password = " .$conn->qStr($admin_pass_np);
                $password_updated = true;
            }

            $sql = "UPDATE admin_users SET " .$set_sql. " WHERE id = " .intval($admin_id). " LIMIT 1";
            $conn->execute($sql);

            $_SESSION['AUSERNAME'] = $admin_name;
        } else {
            $config['admin_name'] = $admin_name;
            if ( $admin_pass_np != '' ) {
                $config['admin_pass'] = $admin_pass_np;
                $password_updated = true;
            }
        }

        $config['admin_email']   = $admin_email;
        $config['noreply_email'] = $noreply_email;
        update_config($config);
        update_smarty();

        $details = 'Updated account profile';
        if ($password_updated) {
            $details .= ' and changed password';
        }
        AdminLog::write('Updated own admin settings', 'settings', $details);

        $messages[] = 'Admin Settings Updated Successfuly!';
    }

    $smarty->assign('admin_name', $admin_name);
    $smarty->assign('admin_email', $admin_email);
    $smarty->assign('noreply_email', $noreply_email);
}

$smarty->assign('admin_name', $admin_name);
$smarty->assign('admin_email', $admin_email);
$smarty->assign('noreply_email', $noreply_email);
$smarty->assign('templates', $templates);
$smarty->assign('err', $err);
?>
