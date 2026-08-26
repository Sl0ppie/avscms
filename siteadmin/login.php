<?php
define('_VALID', true);
define('_ADMIN', true);
include('../include/config.php');
require '../classes/auth.class.php';
require '../classes/adminlog.class.php';

function admin_users_table_exists()
{
    global $conn;

    $rs = $conn->execute("SHOW TABLES LIKE 'admin_users'");
    return ($rs && $conn->Affected_Rows() > 0);
}

$legacy_admin_name = isset($config['admin_name']) ? $config['admin_name'] : '';
$legacy_admin_pass = isset($config['admin_pass']) ? $config['admin_pass'] : '';

$username = '';
$password = '';
$err = NULL;
$msg = NULL;

if (isset($_SESSION['AUID']) && intval($_SESSION['AUID']) > 0 && admin_users_table_exists()) {
    $sql = "SELECT id, username, role FROM admin_users WHERE id = " .intval($_SESSION['AUID']). " AND is_active = 1 LIMIT 1";
    $rs  = $conn->execute($sql);
    if ($conn->Affected_Rows() == 1) {
        $_SESSION['AUID']      = intval($rs->fields['id']);
        $_SESSION['AUSERNAME'] = $rs->fields['username'];
        $_SESSION['AROLE']     = ($rs->fields['role'] == 'superadmin') ? 'superadmin' : 'admin';
        $_SESSION['APASSWORD'] = '';
        VRedirect::go($config['BASE_URL']. '/siteadmin/index.php');
    }
}

if ((isset($_SESSION['AUID']) && $_SESSION['AUID'] == $legacy_admin_name) && isset($_SESSION['APASSWORD']) && $_SESSION['APASSWORD'] == $legacy_admin_pass) {
    $_SESSION['AUID']       = $legacy_admin_name;
    $_SESSION['AUSERNAME']  = $legacy_admin_name;
    $_SESSION['AROLE']      = 'superadmin';
    $_SESSION['APASSWORD']  = $legacy_admin_pass;
    VRedirect::go($config['BASE_URL']. '/siteadmin/index.php');
}


if ( isset($_POST['submit_login']) ) {
    $username   = trim($_POST['username']);
    $password   = trim($_POST['password']);

    if ( $username == '' or $password == '' ) {
        $errors[] = 'Please provide a username and password!';
        $err['account'] = 1;
    } else {
        $authenticated = false;

        if (admin_users_table_exists()) {
            $sql = "SELECT id, username, password, role FROM admin_users WHERE username = " .$conn->qStr($username). " AND is_active = 1 LIMIT 1";
            $rs  = $conn->execute($sql);
            if ($conn->Affected_Rows() == 1) {
                $hash = $rs->fields['password'];
                $valid = false;

                if (function_exists('password_verify')) {
                    $valid = password_verify($password, $hash);
                }

                $looks_like_bcrypt = (bool) preg_match('/^\$2[ayb]\$[0-9]{2}\$/', substr($hash, 0, 7));
                if (!$valid && !$looks_like_bcrypt && $password === $hash) {
                    $valid = true;
                    AdminLog::write('Legacy password fallback login #' .intval($rs->fields['id']), 'auth', 'Account used plaintext legacy password fallback and was re-hashed');
                    if (function_exists('password_hash')) {
                        $new_hash = password_hash($password, PASSWORD_BCRYPT);
                        if ($new_hash) {
                            $conn->execute("UPDATE admin_users SET password = " .$conn->qStr($new_hash). " WHERE id = " .intval($rs->fields['id']). " LIMIT 1");
                            $hash = $new_hash;
                        }
                    }
                }

                if ($valid) {
                    $_SESSION['AUID']      = intval($rs->fields['id']);
                    $_SESSION['AUSERNAME'] = $rs->fields['username'];
                    $_SESSION['AROLE']     = ($rs->fields['role'] == 'superadmin') ? 'superadmin' : 'admin';
                    $_SESSION['APASSWORD'] = '';

                    $conn->execute("UPDATE admin_users SET last_login = NOW() WHERE id = " .intval($rs->fields['id']). " LIMIT 1");
                    AdminLog::write('Logged in to admin panel', 'auth', 'Successful login');

                    $authenticated = true;
                }
            }
        }

        if (!$authenticated && $legacy_admin_name != '' && $username == $legacy_admin_name && $password == $legacy_admin_pass) {
            $_SESSION['AUID']       = $legacy_admin_name;
            $_SESSION['AUSERNAME']  = $legacy_admin_name;
            $_SESSION['AROLE']      = 'superadmin';
            $_SESSION['APASSWORD']  = $legacy_admin_pass;
            $authenticated          = true;
        }

        if ($authenticated) {
            VRedirect::go($config['BASE_URL']. '/siteadmin/index.php');
        } else {
            $errors[] = 'Invalid username and/or password!';
            $err['account'] = 1;
        }
    }
}

if ( isset($_POST['submit_forgot']) ) {
    if ( !isset($_SESSION['email_forgot']) ) {
        $_SESSION['email_forgot'] = 1;
    }

    if ( $_SESSION['email_forgot'] > 2 ) {
        $errors[] = 'Please try again later!';
    }

    if ( !$errors ) {
        $target_email = '';

        if (admin_users_table_exists() && $username != '') {
            $sql = "SELECT email FROM admin_users WHERE username = " .$conn->qStr($username). " AND is_active = 1 LIMIT 1";
            $rs  = $conn->execute($sql);
            if ($conn->Affected_Rows() == 1) {
                $target_email = trim($rs->fields['email']);
            }
        }

        if ($target_email == '') {
            $target_email = isset($config['admin_email']) ? $config['admin_email'] : '';
        }

        if ($target_email == '') {
            $errors[] = 'No admin email address is configured.';
        } else {
            require '../classes/email.class.php';
            $mail           = new VMail();
            $mail->set();
            $mail->Subject  = 'Admin login assistance for ' .$config['site_name'];
            $message        = "A password assistance request was submitted for the admin panel.\n";
            if ($username != '') {
                $message .= 'Username: ' .$username. "\n";
            }
            $message       .= 'For security reasons, passwords are not sent by email. Please reset or update the account password from a superadmin account.';
            $mail->AltBody  = $message;
            $mail->Body     = nl2br($message);
            $mail->AddAddress($target_email);
            $mail->Send();
            $messages[] = 'If the account exists, a password assistance email has been sent.';
        }
    }

    $_SESSION['email_forgot'] = $_SESSION['email_forgot']+1;
}

$smarty->assign('username',$username);
$smarty->assign('password',$password);
$smarty->assign('messages',$messages);
$smarty->assign('err',$err);
$smarty->assign('errors',$errors);
$smarty->display('login.tpl');
?>
