<?php
	if (!defined('ER_MAGIC') || ER_MAGIC != '0c7e92147f298808eb3e2298e85c50882d85a580')
		die ('E_BAD_USAGE');

	if (defined('ER_AUTH'))
		die ('E_DOUBLE_AUTH');

	define('ER_AUTH', true);

	global $db;
	if (!defined('ER_DB'))
		die ('E_NO_DB');

	if (!isset($_COOKIE['warp_auth'])) {
		$user->username = 'Anonymous';
		$user->sid = 0;
		$user->auth = false;
	} else {
		$user->sid = (int)$_COOKIE['warp_auth_sid'];
		$user->username = $_COOKIE['warp_auth_user'];
		$user->hash     = $_COOKIE['warp_auth_hash'];

		$sql = <<<EOF_SQL
SELECT
	username AS ua1,
	password AS ua2,
	user_sid AS ua3,
	refresh AS refresh,
	pagesize AS pagesize,
	user_sid AS user_sid,
	admin AS admin
FROM
	warp_user
WHERE
	user_sid = {$user->sid} AND
	disabled = FALSE
LIMIT 1;
EOF_SQL;
		
		$f = db_query($sql);
		$obj = db_fetch_object($f);

		$user->user_sid = $obj->user_sid;
		$user->lang = 'en';
		$user->refresh = $obj->refresh;
		$user->refresh*=1000;
		$user->pagesize= $obj->pagesize;
		$user->admin = $obj->admin;

		$hash = sha1($obj->ua1.$obj->ua2.$obj->ua3.date('z.jMY'));

		if ($user->hash === $hash) {
			$user->auth = true;
			# TODO reset cookies
		} else {
			foreach ($_COOKIE as $var=>$val) {
				setcookie($var, '', time(), '/'.PREFIX);
			}
			header('Location: '.$_COOKIE['warp_default_index']);
			exit;
		}
	}
?>

