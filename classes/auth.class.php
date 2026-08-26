<?php
class Auth
{
    private static $admin_users_table_checked = false;
    private static $admin_users_table_exists = false;

    public function check()
    {
        if ( isset($_SESSION['uid']) && isset($_SESSION['email']) ) {
            if ( $_SESSION['uid'] != '' && $_SESSION['email'] != '' ) {
                return true;
            }
        }

        global $config;
        $_SESSION['redirect'] = ( isset($_SERVER['REQUEST_URI']) ) ? $_SERVER['REQUEST_URI'] : $config['BASE_URL'];
        VRedirect::go($config['BASE_URL']. '/login');
    }

    public static function check_()
    {
        if ( isset($_SESSION['uid']) && isset($_SESSION['email']) ) {
            if ( $_SESSION['uid'] != '' && $_SESSION['email'] != '' ) {
                return true;
            }
        }

        global $config;
        $_SESSION['redirect'] = ( isset($_SERVER['REQUEST_URI']) ) ? $_SERVER['REQUEST_URI'] : $config['BASE_URL'];
        VRedirect::go($config['BASE_URL']. '/login');
    }

    private static function adminUsersTableExists()
    {
        global $conn;

        if (self::$admin_users_table_checked) {
            return self::$admin_users_table_exists;
        }

        self::$admin_users_table_checked = true;
        $rs = $conn->execute("SHOW TABLES LIKE 'admin_users'");
        if ($rs && $conn->Affected_Rows() > 0) {
            self::$admin_users_table_exists = true;
        }

        return self::$admin_users_table_exists;
    }

    private static function loadAdminById($admin_id)
    {
        global $conn;

        $sql = "SELECT id, username, role, is_active FROM admin_users WHERE id = " .intval($admin_id). " LIMIT 1";
        $rs  = $conn->execute($sql);
        if ($conn->Affected_Rows() == 1) {
            return $rs->fields;
        }

        return false;
    }

    private static function setAdminSession($row)
    {
        $_SESSION['AUID']      = intval($row['id']);
        $_SESSION['AUSERNAME'] = $row['username'];
        $_SESSION['AROLE']     = ($row['role'] == 'superadmin') ? 'superadmin' : 'admin';

        if (!isset($_SESSION['APASSWORD'])) {
            $_SESSION['APASSWORD'] = '';
        }

        if (defined('_ADMIN') && isset($GLOBALS['smarty']) && is_object($GLOBALS['smarty'])) {
            $GLOBALS['smarty']->assign('admin_name', $_SESSION['AUSERNAME']);
            $GLOBALS['smarty']->assign('admin_role', $_SESSION['AROLE']);
            $GLOBALS['smarty']->assign('is_superadmin', ($_SESSION['AROLE'] == 'superadmin'));
        }
    }

    public static function checkAdmin()
    {
        global $config;

        $access = false;

        if (self::adminUsersTableExists()) {
            if (isset($_SESSION['AUID']) && intval($_SESSION['AUID']) > 0) {
                $row = self::loadAdminById($_SESSION['AUID']);
                if ($row && intval($row['is_active']) === 1) {
                    self::setAdminSession($row);
                    $access = true;
                }
            }
        }

        if (!$access && isset($_SESSION['AUID']) && isset($_SESSION['APASSWORD'])) {
            $legacy_admin_name = isset($config['admin_name']) ? $config['admin_name'] : '';
            $legacy_admin_pass = isset($config['admin_pass']) ? $config['admin_pass'] : '';
            if ( $_SESSION['AUID'] == $legacy_admin_name && $_SESSION['APASSWORD'] == $legacy_admin_pass ) {
                $access = true;
                $_SESSION['AUSERNAME'] = $legacy_admin_name;
                if (!isset($_SESSION['AROLE']) || $_SESSION['AROLE'] == '') {
                    $_SESSION['AROLE'] = 'superadmin';
                }

                if (defined('_ADMIN') && isset($GLOBALS['smarty']) && is_object($GLOBALS['smarty'])) {
                    $GLOBALS['smarty']->assign('admin_name', $_SESSION['AUSERNAME']);
                    $GLOBALS['smarty']->assign('admin_role', $_SESSION['AROLE']);
                    $GLOBALS['smarty']->assign('is_superadmin', ($_SESSION['AROLE'] == 'superadmin'));
                }
            }
        }

        if (!$access) {
            VRedirect::go($config['BASE_URL']. '/siteadmin/login.php');
        }
    }

    public static function getAdminId()
    {
        if (isset($_SESSION['AUID']) && intval($_SESSION['AUID']) > 0) {
            return intval($_SESSION['AUID']);
        }

        return 0;
    }

    public static function getAdminUsername()
    {
        if (isset($_SESSION['AUSERNAME']) && $_SESSION['AUSERNAME'] != '') {
            return $_SESSION['AUSERNAME'];
        }

        if (isset($_SESSION['AUID']) && !is_numeric($_SESSION['AUID'])) {
            return $_SESSION['AUID'];
        }

        return '';
    }

    public static function getAdminRole()
    {
        if (isset($_SESSION['AROLE']) && $_SESSION['AROLE'] != '') {
            return $_SESSION['AROLE'];
        }

        return 'admin';
    }

    public static function isSuperAdmin()
    {
        return (self::getAdminRole() === 'superadmin');
    }

    public static function requireSuperAdmin()
    {
        global $config;

        self::checkAdmin();
        if (!self::isSuperAdmin()) {
            VRedirect::go($config['BASE_URL']. '/siteadmin/index.php?err=Superadmin access required!');
        }
    }

    public function confirm()
    {
        global $config;

        if ( $config['email_verification'] == '0' ) {
            return true;
        }

        if ( isset($_SESSION['uid']) && isset($_SESSION['email']) ) {
            if ( isset($_SESSION['emailverified']) && $_SESSION['emailverified'] == 'yes' ) {
                return true;
            }
        }

        $_SESSION['redirect'] = ( isset($_SERVER['REQUEST_URI']) ) ? $_SERVER['REQUEST_URI'] : $config['BASE_URL'];
        VRedirect::go($config['BASE_URL']. '/confirm');
    }
}
?>
