<?php
defined('_VALID') or die('Restricted Access!');

if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
    $strip_slashes = function (&$value) use (&$strip_slashes) {
        if (!is_array($value)) {
            $value = stripslashes($value);
            return;
        }

        foreach ($value as &$nested) {
            $strip_slashes($nested);
        }
    };

    $strip_slashes($_GET);
    $strip_slashes($_POST);
    $strip_slashes($_COOKIE);
}

function disableRegisterGlobals()
{
    if( (bool)@ini_get('register_globals') ) {
        $noUnset    = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');                                                                   
        $input      = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
    
        foreach ($input as $k => $v) {
            if (!in_array($k, $noUnset) && isset($GLOBALS[$k])) {
                unset($GLOBALS[$k]);
            }
        }
    }
}
?>
