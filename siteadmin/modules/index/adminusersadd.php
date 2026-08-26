<?php
defined('_VALID') or die('Restricted Access!');

Auth::requireSuperAdmin();

$rs = $conn->execute("SHOW TABLES LIKE 'admin_users'");
if (!$rs || $conn->Affected_Rows() == 0) {
    $errors[] = 'admin_users table was not found. Run scripts/migrate_admin_users.php first.';
    $smarty->assign('admins', array());
    $smarty->assign('admins_total', 0);
    $smarty->assign('paging', '');
    $smarty->assign('admin', array());
    return;
}

$admin = array('username' => '', 'email' => '', 'role' => 'admin', 'is_active' => '1');

if ( isset($_POST['add_admin_user']) ) {
    $filter = new VFilter();

    $admin['username'] = $filter->get('username');
    $password          = $filter->get('password');
    $password_confirm  = $filter->get('password_confirm');
    $admin['email']    = $filter->get('email');
    $admin['role']     = $filter->get('role');
    $admin['is_active']= isset($_POST['is_active']) ? '1' : '0';

    if ($admin['username'] == '') {
        $errors[] = 'Username cannot be blank!';
        $err['username'] = 1;
    } elseif (strlen($admin['username']) < 4) {
        $errors[] = 'Username must be at least 4 characters long!';
        $err['username'] = 1;
    }

    $sql = "SELECT id FROM admin_users WHERE username = " .$conn->qStr($admin['username']). " LIMIT 1";
    $conn->execute($sql);
    if ($conn->Affected_Rows() > 0) {
        $errors[] = 'Username already exists!';
        $err['username'] = 1;
    }

    if ($admin['email'] == '') {
        $errors[] = 'Email cannot be blank!';
        $err['email'] = 1;
    } elseif (!VValidation::email_($admin['email'])) {
        $errors[] = 'Email is not a valid email address!';
        $err['email'] = 1;
    }

    if ($password == '') {
        $errors[] = 'Password cannot be blank!';
        $err['password'] = 1;
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long!';
        $err['password'] = 1;
    }

    if ($password != $password_confirm) {
        $errors[] = 'Password and confirm password do not match!';
        $err['password'] = 1;
        $err['password_confirm'] = 1;
    }

    if ($admin['role'] != 'superadmin' && $admin['role'] != 'admin') {
        $admin['role'] = 'admin';
    }

    if (!$errors) {
        $password_hash = $password;
        if (function_exists('password_hash')) {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
        }

        $sql = "INSERT INTO admin_users (username, password, email, role, is_active, created_at)
                VALUES (" .$conn->qStr($admin['username']). ", " .$conn->qStr($password_hash). ", " .$conn->qStr($admin['email']). ",
                        " .$conn->qStr($admin['role']). ", " .intval($admin['is_active']). ", NOW())";
        $conn->execute($sql);

        if ($conn->Affected_Rows() == 1) {
            $new_id = intval($conn->Insert_ID());
            AdminLog::write('Created admin user #' .$new_id, 'adminusers', 'Created admin account ' .$admin['username']);
            $messages[] = 'Admin user added successfully!';
            $admin = array('username' => '', 'email' => '', 'role' => 'admin', 'is_active' => '1');
        } else {
            $errors[] = 'Failed to add admin user!';
        }
    }
}

$smarty->assign('admin', $admin);
$smarty->assign('err', $err);
?>
