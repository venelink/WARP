<?

require_once('config.inc');

if (!defined('ER_DB'))
	define('ER_DB', true);
else
	exit();

$db_is_connected = FALSE;

function db_connect ($user = __DBUSER__, $pass = __DBPASS__, $dbhost = __DBHOST__){
	global $db_is_connected;
	global $db_link;

	if (!$db_is_connected) {
		$db_link = mysqli_connect($dbhost, $user, $pass, 'warp_db');

		if (mysqli_connect_errno()) {
			error_log(mysqli_connect_error() . "\n", 3, "/var/log/apache2/warp-sql.log") && die('DB Error');
		}

		mysqli_set_charset($db_link, "utf8")
			or error_log(mysqli_error($db_link) . "\n", 3, "/var/log/apache2/warp-sql.log") && die('DB Error');

		$db_is_connected = true;

	}
}

function db_disconnect ()
{
	global $db_is_connected;
	global $db_link;

	if (!$db_is_connected) {
		mysqli_close($db_link)
			or error_log(mysqli_error($db_link) . "\n", 3, "/var/log/apache2/warp-sql.log") && die('DB Error');
		$db_is_connected = false;
	}
}

function db_query ($sql)
{
	global $db_is_connected;
	global $db_link;

	if (!$db_is_connected) {
		db_connect();
	}
	$dbbt = debug_backtrace();

	$result = mysqli_query($db_link,$sql)
                        or error_log(":::" . date("Y.n.d H:m:s") . ": " . $dbbt[1]['file'] . "\n----> " . $sql .  "\n<----\n" . mysqli_error($db_link) ."\n", 3, "/var/log/apache2/warp-sql.log") && die('DB Error');

	return $result;
}

function db_fetch_array ($result)
{
	return mysqli_fetch_assoc($result);
}

function db_fetch_object ($result)
{
	return mysqli_fetch_object($result);
}

function db_escape_string ($result)
{
	global $db_is_connected;
	global $db_link;

	if (!$db_is_connected) {
		db_connect();
	}
	return mysqli_real_escape_string($db_link,$result);
}

function db_count_rows ($result)
{
	return mysqli_num_rows($result);
}

function db_affected_rows ()
{
	global $db_is_connected;
	global $db_link;

	if (!$db_is_connected) {
		db_connect();
	}
	return mysqli_affected_rows($db_link);
}

function db_insert_id ()
{
	global $db_is_connected;
	global $db_link;

	if (!$db_is_connected) {
		db_connect();
	}
	return mysqli_insert_id($db_link);
}

function db_free_result ($result)
{
	global $db_is_connected;
	global $db_link;

	if (!$db_is_connected) {
		db_connect();
	}
	return mysqli_free_result($db_link,$result);
}
?>
