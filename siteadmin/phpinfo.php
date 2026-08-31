<?php
define('_VALID', true);
define('_ADMIN', true);
require '../include/config.php';
require_once '../classes/auth.class.php';

Auth::checkAdmin();
echo phpinfo();
?>