<?php
defined('_VALID') or die('Restricted Access!');

Auth::requireSuperAdmin();

function hash_admin_password_edit($password)
{
    if (function_exists('password_hash')) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    $salt = '$2y$10$' . substr(str_replace('+', '.', base64_encode(md5(uniqid(mt_rand(), true), true))), 0, 22);
    return crypt($password, $salt);
}

$rs = $conn->execute("SHOW TABLES LIKE 'admin_users'");
if (!$rs || $conn->Affected_Rows() == 0) {
    $errors[] = 'admin_users table was not found. Run scripts/migrate_admin_users.php first.';
    $smarty->assign('admin', array());
    return;
}

$AID = (isset($_GET['AID'])) ? intval($_GET['AID']) : 0;
if ($AID <= 0) {
    $errors[] = 'Invalid admin user id!';
    $admin = array();
} else {
    $sql = "SELECT id, username, email, role, is_active FROM admin_users WHERE id = " .$AID. " LIMIT 1";
    $rs = $conn->execute($sql);
    if ($conn->Affected_Rows() != 1) {
        $errors[] = 'Admin user not found!';
        $admin = array();
    } else {
        $admin = $rs->fields;
        $admin['is_active'] = $admin['is_active'] ? '1' : '0';
        $original_role = $admin['role'];
        $original_is_active = $admin['is_active'];

        if ( isset($_POST['edit_admin_user']) ) {
            $filter = new VFilter();

            $admin['email']      = $filter->get('email');
            $admin['role']       = $filter->get('role');
            $admin['is_active']  = isset($_POST['is_active']) ? '1' : '0';
            $password            = $filter->get('password');
            $password_confirm    = $filter->get('password_confirm');

            if ($admin['email'] == '') {
                $errors[] = 'Email cannot be blank!';
                $err['email'] = 1;
            } elseif (!VValidation::email_($admin['email'])) {
                $errors[] = 'Email is not a valid email address!';
                $err['email'] = 1;
            }

            if ($admin['role'] != 'superadmin' && $admin['role'] != 'admin') {
                $admin['role'] = 'admin';
            }

            if ($password != '') {
                if (strlen($password) < 6) {
                    $errors[] = 'Password must be at least 6 characters long!';
                    $err['password'] = 1;
                }
                if ($password != $password_confirm) {
                    $errors[] = 'Password and confirm password do not match!';
                    $err['password'] = 1;
                    $err['password_confirm'] = 1;
                }
            }

            if ($AID == Auth::getAdminId() && $admin['is_active'] == '0') {
                $errors[] = 'You cannot deactivate your own account.';
                $err['is_active'] = 1;
            }

            if ($admin['role'] == 'admin') {
                $sql = "SELECT COUNT(id) AS total_superadmins FROM admin_users WHERE role = 'superadmin' AND is_active = 1";
                $rs = $conn->execute($sql);
                $total_superadmins = intval($rs->fields['total_superadmins']);

                if ($original_role == 'superadmin' && intval($original_is_active) == 1 && $total_superadmins <= 1) {
                    $errors[] = 'Cannot downgrade the last active superadmin account.';
                    $err['role'] = 1;
                }
            }

            if ($admin['is_active'] == '0' && $original_role == 'superadmin') {
                $sql = "SELECT COUNT(id) AS total_superadmins FROM admin_users WHERE role = 'superadmin' AND is_active = 1";
                $rs = $conn->execute($sql);
                if (intval($rs->fields['total_superadmins']) <= 1) {
                    $errors[] = 'Cannot deactivate the last active superadmin account.';
                    $err['is_active'] = 1;
                }
            }

            if (!$errors) {
                $set_sql = "email = " .$conn->qStr($admin['email']). ", role = " .$conn->qStr($admin['role']). ", is_active = " .intval($admin['is_active']);

                if ($password != '') {
                    $password_hash = hash_admin_password_edit($password);
                    $set_sql .= ", password = " .$conn->qStr($password_hash);
                }

                $sql = "UPDATE admin_users SET " .$set_sql. " WHERE id = " .$AID. " LIMIT 1";
                $conn->execute($sql);

                AdminLog::write('Updated admin user #' .$AID, 'adminusers', 'Updated admin account ' .$admin['username']);
                $messages[] = 'Admin user updated successfully!';
            }
        }
    }
}

$smarty->assign('admin', $admin);
$smarty->assign('err', $err);
?>
