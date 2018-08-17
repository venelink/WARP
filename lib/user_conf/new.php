<?
openHTML();
openHEAD('WARP-Text');
closeHEAD();
openBODY(12);

# Admin panel. Check access.
if ($user->admin != "1") {
        header('Location: '.$_COOKIE['warp_default_index']);
        exit;
}

# Check for posted data
if (isset($mod->vars->i_username)) {
	# data verification
	$error_msg = '';
	$other_msg = '';
	if (!preg_match('/^[a-zA-Z0-9]+$/', $mod->vars->i_username) || strlen($mod->vars->i_username) < 6) {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Username should be alphanumeric and at least 6 chars long.";
	}

	if (!preg_match('/^[a-zA-Z0-9]+$/', $mod->vars->i_password) || strlen($mod->vars->i_password) < 6) {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Password should be alphanumeric and at least 6 chars long.";
	}
	
	if ($mod->vars->i_admin != 'true' && $mod->vars->i_admin != 'false') {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid admin value.";
	}

	# Check for duplicated username
	$username = db_escape_string($mod->vars->i_username);
	$sql = <<<EOF
SELECT
	TRUE
FROM
	warp_user
WHERE
	username = "{$username}";
EOF;
	if (db_count_rows(db_query($sql))) {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Username already exists.";
	}

	# Check if colab id is valid
	if ($mod->vars->i_colab_id != 0) {
		$colab_id = db_escape_string($mod->vars->i_colab_id);
		$sql = <<<EOF
SELECT
	TRUE
FROM
	warp_user
WHERE
	colab_id = "{$colab_id}";
EOF;
		if (!db_count_rows(db_query($sql))) {
			$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid collaboration.";
		}
	}

	# If there were no errors, proceed with insertion
	if ($error_msg == '') {
		$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."Checks_passed.";

		# Hash the password
		$password = hash('sha256',$mod->vars->i_password);		

		$sql = <<<EOF
INSERT INTO
	warp_user
	(username, password, admin, refresh, pagesize)
VALUES
	('{$username}', '{$password}',{$mod->vars->i_admin},2,15)
EOF;
		db_query($sql);

		# Get the user id of the insertion
		$user_sid = db_insert_id();

		# Update the colab id
		if ($mod->vars->i_colab_id != 0) {
			$sql = <<<EOF
UPDATE
	warp_user
SET
	colab_id = {$colab_id}
WHERE
	user_sid = {$user_sid};
EOF;
			db_query($sql);
		} else {
			$sql = <<<EOF
UPDATE
	warp_user
SET
	colab_id = user_sid
WHERE
	user_sid = {$user_sid};
EOF;
			db_query($sql);

		}

		$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."User created.";		
	}
}

if (isset($error_msg)&&strlen($error_msg)) {
	echo "<div class='error'>{$error_msg}</div><br />";
}

if (isset($other_msg)&&strlen($other_msg)) {
	echo "<div class='green'>{$other_msg}</div><br />";
	echo "<script lang='javascript'>setTimeout(\"document.location='/user_conf/list'\",$user->refresh);</script>";
	closeBODY();
	closeHTML();
	exit;
}

openForm($mod->self, 'POST', 'form', 'form');
?>
  <table class='non-exist' border='0' width='100%'>
    <tr>
      <th width='35%' style='text-align: right;'><label for='i_username'>Username</label></th>
      <td><input type='text' id='i_username' name='i_username' class='shadowinput' value='<?=(isset($mod->vars->i_username))?$mod->vars->i_username:''?>' /></td>
    </tr>
    <tr>
      <th width='35%' style='text-align: right;'><label for='i_password'>Password</label></th>
      <td><input type='text' id='i_password' name='i_password' class='shadowinput' value='<?=(isset($mod->vars->i_password))?$mod->vars->i_password:''?>' /></td>
    </tr>
    <tr>
    <th width='35%' style='text-align: right;'><label for='i_colab_id'>Collaboration mode</label></td>
    <td><select class='shadowinput' id='i_colab_id' name='i_colab_id' value='<?=(isset($mod->vars->i_colab_id))?$mod->vars->i_colab_id:''?>';>
        <option value='0'>Independent</option>
<?
        $sql = <<<EOF
SELECT
        username,
	colab_id
FROM
        warp_user
WHERE
	user_sid = colab_id AND
	not disabled
EOF;
$s_data = db_query($sql);

while ($r = db_fetch_object($s_data)) {
	if ($mod->vars->i_colab_id == $r->colab_id) {
		echo "<option value='{$r->colab_id}' selected>{$r->username}</option>";
	} else {
		echo "<option value='{$r->colab_id}'>{$r->username}</option>";
	}

}

?></select>
    </td>
    </tr>
    <tr>
      <th width='35%' style='text-align: right;'><label for="i_admin">Admin</label></th>
      <td>
        <input type='radio' id='i_admin' name='i_admin' value='true' class='radio' <?=
		((!isset($mod->vars->i_admin))||$mod->vars->i_admin=='true')?'checked':''?>/>
	<label for='i_admin'>Yes</label>
        <input type='radio' id='i_non_admin' name='i_admin' value='false' class='radio' <?=
		((isset($mod->vars->i_admin))&&$mod->vars->i_admin=='false')?'checked':''?>/>
        <label for='i_non_admin'>No</label>
      </td>
    </tr>
    <tr>
      <td colspan='3'><a href="#" onclick="document.forms['form'].submit();return false;" class="btn-medium common-button"><span>Create</span></a></td>
    </tr>
  </table>
<?
closeForm();

closeBODY();
closeHTML();
?>
