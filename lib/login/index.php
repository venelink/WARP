<?php                                       
if (!defined('ER_MAGIC') || ER_MAGIC != '0c7e92147f298808eb3e2298e85c50882d85a580')
	die ('E_BAD_USAGE');

if ($user->auth) {
	header('Location: '.$_COOKIE['warp_default_index']);
}
if (isset($mod->vars->username)&&isset($mod->vars->password)) {
	$password = hash('sha256',$mod->vars->password);
	$username = $mod->vars->username;
	$username_esc = db_escape_string($username);
	$password_esc = db_escape_string($password);
$sql = <<<EOF_SQL
SELECT
	warp_user.username AS ua1,
	warp_user.password AS ua2,
	warp_user.user_sid AS ua3,
	warp_user.admin as adm
FROM
	warp_user
WHERE
	warp_user.username = '{$username_esc}' AND
	warp_user.password = '{$password_esc}';
EOF_SQL;
	$f = db_query($sql);
	
	if (!db_count_rows($f)) {
		$error_msg = 'Bad username or password';
	} else {
		$obj = db_fetch_object($f);
		$time = time()+3600;
		setcookie('warp_auth', 'true', $time, '/'.PREFIX);
		setcookie('warp_auth_sid', $obj->ua3, $time, '/'.PREFIX);
		setcookie('warp_auth_user', $obj->ua1, $time, '/'.PREFIX);
		setcookie('warp_auth_hash', sha1($obj->ua1.$obj->ua2.$obj->ua3.date('z.jMY')), $time, '/'.PREFIX);

		if ($obj->adm == 0) {
			setcookie('warp_default_index', '/'.PREFIX.'anno/details', $time, '/'.PREFIX);
		} else {
			setcookie('warp_default_index', '/'.PREFIX.'data_conf/details', $time, '/'.PREFIX);
		}
	
		header('Location: /'.$_COOKIE['warp_default_index']);
		exit(0);
		# Get module url by id from def (OR Make that url with Join ;P
		# Redirect to that page :)
		# exit
	}
}

openHTML();
openHEAD();
closeHEAD();
openBODY(8);
?>
    <?=isset($error_msg)?"<span class='error'>".$error_msg."</span><br /><br />":''?>

    <div>
      <? openForm($mod->self, 'POST', 'login_form', 'login_form'); ?>
        <table class='non-exist login' border='0'>
          <tr><td colspan="2"><h1>Login</h1></td></tr>
          <tr><td width='100px' style='text-align: right;'><label for='username'>Username:</label>&nbsp;</td>
          <td><input type='text' id='username' name='username' value='<?=isset($username)?$username:''?>' class='cleardefault shadowinput' /></td></tr>
          <tr><td style='text-align: right;'><label for='password'>Password:</label>&nbsp;</td>
          <td><input type='password' id='password' name='password' class='cleardefault shadowinput'/></td></tr>
          <tr><td colspan="2">
          <a href="#" onclick="document.forms['login_form'].submit();return false;" class="btn-medium common-button"><span>Submit</span></a></td></tr>
	  
        </table>
      <? closeForm(); ?>
    </div>
<?
closeBODY();
closeHTML();
?>
