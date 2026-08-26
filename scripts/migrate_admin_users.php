<?php
define('_VALID', 1);
define('_ENTER', true);
define('_CLI', true);

$basedir = dirname(dirname(__FILE__));
require $basedir. '/include/config.php';

function make_bcrypt_hash($password)
{
    if (function_exists('password_hash')) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    $salt = '$2y$10$' . substr(str_replace('+', '.', base64_encode(md5(uniqid(mt_rand(), true), true))), 0, 22);
    return crypt($password, $salt);
}

echo "Starting admin users migration...\n";

$sql = "CREATE TABLE IF NOT EXISTS admin_users (
          id int(11) NOT NULL AUTO_INCREMENT,
          username varchar(100) NOT NULL,
          password varchar(255) NOT NULL,
          email varchar(255) NOT NULL,
          role enum('superadmin','admin') NOT NULL DEFAULT 'admin',
          is_active tinyint(1) NOT NULL DEFAULT '1',
          created_at datetime DEFAULT NULL,
          last_login datetime DEFAULT NULL,
          PRIMARY KEY (id),
          UNIQUE KEY username (username),
          KEY email (email),
          KEY role (role),
          KEY is_active (is_active)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$conn->execute($sql);

echo "Ensured admin_users table exists.\n";

$sql = "CREATE TABLE IF NOT EXISTS admin_activity_log (
          id int(11) NOT NULL AUTO_INCREMENT,
          admin_id int(11) NOT NULL,
          admin_username varchar(100) NOT NULL,
          action varchar(255) NOT NULL,
          module varchar(100) NOT NULL,
          details text,
          ip_address varchar(45) NOT NULL DEFAULT '',
          created_at datetime DEFAULT NULL,
          PRIMARY KEY (id),
          KEY admin_id (admin_id),
          KEY module (module),
          KEY created_at (created_at)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$conn->execute($sql);

echo "Ensured admin_activity_log table exists.\n";

$admin_name  = isset($config['admin_name']) ? trim($config['admin_name']) : '';
$admin_pass  = isset($config['admin_pass']) ? (string) $config['admin_pass'] : '';
$admin_email = isset($config['admin_email']) ? trim($config['admin_email']) : '';

if ($admin_name == '' || $admin_pass == '') {
    echo "No legacy admin_name/admin_pass values found in config.local.php.\n";
    echo "Migration finished.\n";
    exit(0);
}

$sql = "SELECT id FROM admin_users WHERE username = " .$conn->qStr($admin_name). " LIMIT 1";
$rs  = $conn->execute($sql);
if ($conn->Affected_Rows() == 0) {
    $hash = make_bcrypt_hash($admin_pass);
    $sql = "INSERT INTO admin_users (username, password, email, role, is_active, created_at)
            VALUES (" .$conn->qStr($admin_name). ", " .$conn->qStr($hash). ", " .$conn->qStr($admin_email). ",
                    'superadmin', 1, NOW())";
    $conn->execute($sql);
    echo "Created superadmin account from legacy config credentials.\n";
} else {
    $sql = "UPDATE admin_users SET role = 'superadmin', is_active = 1 WHERE username = " .$conn->qStr($admin_name). " LIMIT 1";
    $conn->execute($sql);
    echo "Legacy admin user already exists; ensured superadmin role and active status.\n";
}

echo "Migration finished successfully.\n";
echo "You can now remove admin_name and admin_pass from include/config.local.php after confirming login works.\n";
?>
