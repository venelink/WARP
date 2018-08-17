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

$mod->vars->id = (int) $mod->vars->id;

# Check for posted data
if (isset($mod->vars->i_username)) {
	# data verification
	$error_msg = '';
	$other_msg = '';

	# Check if there is new password
	if (isset($mod->vars->i_password) && $mod->vars->i_password != "") {
	        if (!preg_match('/^[a-zA-Z0-9]+$/', $mod->vars->i_password) || strlen($mod->vars->i_password) < 6) {
        	        $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Password should be alphanumeric and at least 6 chars long.";
	        } else {
		$password = hash('sha256',$mod->vars->i_password);
		$pw_upd = "password = '" . $password ."',";
		}
	} else {
		$pw_upd = "";
	}

        if ($mod->vars->i_admin != 'true' && $mod->vars->i_admin != 'false') {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid admin value.";
        }

        if ($mod->vars->i_disabled != 'true' && $mod->vars->i_disabled != 'false') {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid disabled value.";
        }

        # Check for username
        $sql = <<<EOF
SELECT
        TRUE
FROM
        warp_user
WHERE
        user_sid = {$mod->vars->id};
EOF;
        if (!db_count_rows(db_query($sql))) {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Username doesn't exists.";
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
        } else {
		$colab_id = $mod->vars->id;
	}

	if ($error_msg == '') {
		$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."Checks passed.";
		
		$sql = <<<EOF
UPDATE
	warp_user
SET
	{$pw_upd}
	admin = {$mod->vars->i_admin},
	disabled = {$mod->vars->i_disabled},
	colab_id = '{$colab_id}'
WHERE
	user_sid = {$mod->vars->id}
EOF;
		db_query($sql);
		$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."User updated.";
	}
} else {
	$sql = <<<EOF
SELECT
	username	AS	i_username,
	colab_id	AS	i_colab_id,
	CASE WHEN admin THEN 'true' ELSE 'false' END	AS	i_admin,
	CASE WHEN disabled THEN 'true' ELSE 'false' END AS	i_disabled
FROM
	warp_user
WHERE
	user_sid = {$mod->vars->id};
EOF;
	$data = db_query($sql);
	if (!db_count_rows($data)) {
		echo "<div class='error'>"."User not found"."</div><br />";
		var_dump($sql);
		killPage();
	}
	
	$obj = db_fetch_object($data);
	
	foreach($obj AS $var=>$val) {
		$mod->vars->$var = $val;
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
<input type='hidden' name='id' id='id' value='<?=$mod->vars->id?>' />
  <table class='non-exist' border='0' width='100%'>
  <table class='non-exist' border='0' width='100%'>
    <tr>
      <th width='35%' style='text-align: right;'><label for='i_username'>Username</label></th>
      <td><input type='text' id='i_username' name='i_username' class='shadowinput' value='<?=(isset($mod->vars->i_username))?$mod->vars->i_username:''?>' readonly/></td>
    </tr>
    <tr>
      <th width='35%' style='text-align: right;'><label for='i_password'>Password</label></th>
      <td><input type='text' id='i_password' name='i_password' class='shadowinput' value='<?=(isset($mod->vars->i_password))?$mod->vars->i_password:''?>' /></td>
    </tr>
    <tr>
    <th width='35%' style='text-align: right;'><label for='i_colab_id'>Collaboration mode</label></td>
    <td><select class='shadowinput' id='i_colab_id' name='i_colab_id' value='<?=(isset($mod->vars->i_colab_id))?$mod->vars->i_colab_id:''?>';>
        <option value='0' <?=(isset($mod->vars->i_colab_id) && $mod->vars->id == $mod->vars->i_colab_id)?'selected':''?>>Independent</option>
<?
        $sql = <<<EOF
SELECT
        username,
        colab_id
FROM
        warp_user
WHERE
        user_sid = colab_id AND
	user_sid <> {$mod->vars->id} AND
        not disabled AND
	not admin
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
      <th width='35%' style='text-align: right;'><label for="i_disabled">Disabled</label></th>
      <td>
        <input type='radio' id='i_disabled' name='i_disabled' value='true' class='radio' <?=
                ((!isset($mod->vars->i_disabled))||$mod->vars->i_disabled=='true')?'checked':''?>/>
        <label for='i_disabled'>Yes</label>
        <input type='radio' id='i_non_disabled' name='i_disabled' value='false' class='radio' <?=
                ((isset($mod->vars->i_disabled))&&$mod->vars->i_disabled=='false')?'checked':''?>/>
        <label for='i_non_disabled'>No</label>
      </td>
    </tr>
    <tr>
      <td colspan='3'><a href="#" onclick="document.forms['form'].submit();return false;" class="btn-medium common-button"><span>Save</span></a></td>
    </tr>
</table>
<?

closeBODY();
closeHTML();
?>
