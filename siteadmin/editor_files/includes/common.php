<?php
$magic_quotes_gpc = function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc();
// Slash data if it isn't slashed
if (!$magic_quotes_gpc) {
	// get
	if (is_array($_GET)) {
		foreach ($_GET as $k => $v) {
			if (is_array($v)) {
				foreach ($v as $k2 => $v2) {
					$_GET[$k][$k2] = addslashes($v2);
				}
			} else {
				$_GET[$k] = addslashes($v);
			}
		}
	}
	// post
	if (is_array($_POST)) {
		foreach ($_POST as $k => $v) {
			if (is_array($v)) {
				foreach ($v as $k2 => $v2) {
					$_POST[$k][$k2] = addslashes($v2);
				}
			} else {
				$_POST[$k] = addslashes($v);
			}
		}
	}
	// cookie
	if (is_array($_COOKIE)) {
		foreach ($_COOKIE as $k => $v) {
			if (is_array($v)) {
				foreach ($v as $k2 => $v2) {
					$_COOKIE[$k][$k2] = addslashes($v2);
				}
			} else {
				$_COOKIE[$k] = addslashes($v);
			}
		}
	}
}
if (isset ($_GET['lang']) ? $_GET['lang'] : '') {
	if (wp_file_name_ok($_GET['lang'])) {
		$lang_include = $_GET['lang'];
	} else {
		$lang_include = DEFAULT_LANG;
	}
} else if (isset ($_POST['lang']) ? $_POST['lang'] : '') { 
	if (wp_file_name_ok($_POST['lang'])) {
		$lang_include = $_POST['lang'];
	} else {
		$lang_include = DEFAULT_LANG;
	}
} else {
	$lang_include = DEFAULT_LANG;
}
?>