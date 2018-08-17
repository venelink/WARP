<?php
if (!defined('ER_MAGIC') || ER_MAGIC != '0c7e92147f298808eb3e2298e85c50882d85a580')
	die ('E_BAD_USAGE');

if ($user->auth) {
	foreach ($_COOKIE as $var=>$val) {
		setcookie($var, '', time(), '/'.PREFIX);
	}
	header('Location: '.$_COOKIE['warp_default_index']);
} else 
	header('Location: /'.PREFIX);

exit(0);

?>
