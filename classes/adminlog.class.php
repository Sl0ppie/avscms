<?php
defined('_VALID') or die('Restricted Access!');

class AdminLog
{
    private static $table_checked = false;
    private static $table_exists  = false;

    private static function tableExists()
    {
        global $conn;

        if (self::$table_checked) {
            return self::$table_exists;
        }

        self::$table_checked = true;
        $sql = "SHOW TABLES LIKE 'admin_activity_log'";
        $rs  = $conn->execute($sql);
        if ($rs && $conn->Affected_Rows() > 0) {
            self::$table_exists = true;
        }

        return self::$table_exists;
    }

    private static function getClientIp()
    {
        $ip = '';
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && $_SERVER['HTTP_CF_CONNECTING_IP'] != '') {
            $ip = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip    = trim($parts[0]);
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '') {
            $ip = trim($_SERVER['REMOTE_ADDR']);
        }

        return substr($ip, 0, 45);
    }

    public static function write($action, $module, $details = '')
    {
        global $conn;

        if (!self::tableExists()) {
            return false;
        }

        $admin_id = Auth::getAdminId();
        if (!$admin_id) {
            return false;
        }

        $username = Auth::getAdminUsername();
        $ip       = self::getClientIp();

        $action   = substr(trim($action), 0, 255);
        $module   = substr(trim($module), 0, 100);

        $sql = "INSERT INTO admin_activity_log (admin_id, admin_username, action, module, details, ip_address, created_at)
                VALUES (" .intval($admin_id). ", " .$conn->qStr($username). ", " .$conn->qStr($action). ",
                        " .$conn->qStr($module). ", " .$conn->qStr($details). ", " .$conn->qStr($ip). ", NOW())";
        $conn->execute($sql);

        return ($conn->Affected_Rows() > 0);
    }
}
?>
