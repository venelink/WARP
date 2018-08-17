<?
openHTML();
openHEAD('WARP-Text');
closeHEAD();
openBODY(11);

# Admin panel. Check access.
if ($user->admin != "1") {
        header('Location: '.$_COOKIE['warp_default_index']);
        exit;
}

# Check for posted data
if (isset($mod->vars->i_user_id)) {

	# data verification
	$error_msg = '';
	$other_msg = '';

	# Check for username
	$user_id = db_escape_string($mod->vars->i_user_id);
	$sql = <<<EOF
SELECT
	TRUE
FROM
	warp_user
WHERE
	user_sid = "{$user_id}";
EOF;
	if (!db_count_rows(db_query($sql))) {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."User doesn't already exists.";
	}

	# Check if the range is ok (rage given as start, end)
	if (!preg_match('/^[0-9]+$/', $mod->vars->i_start) || !preg_match('/^[0-9]+$/', $mod->vars->i_end)) {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid range.";
	}

	if ($mod->vars->i_action != 'add' && $mod->vars->i_action != 'del') {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid action.";
	}

	# Adding
	if ($mod->vars->i_action == 'add') {

		# Check for duplicate entries
		$sql = <<<EOF
SELECT
	split_sid
FROM
	pair_split
WHERE
	user_sid = "{$user_id}" AND
	pair_sid BETWEEN {$mod->vars->i_start} AND {$mod->vars->i_end}
EOF;
		if (db_count_rows(db_query($sql))) {
			$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Some of the pairs are already assigned to this user.";
		}

		# If there were no errors, proceed with insertion
		if ($error_msg == '') {
			$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."Checks_passed.";

			# Convert the range into array
			$entries = array();
			$r_id = $mod->vars->i_start;
			while ($r_id <= $mod->vars->i_end) {
				$entries[] = '("' . $user_id . '" , "' . $r_id .'")';
				$r_id = $r_id +1;
			}

			# Generate the SQL by imploding the array
			$sql = 'INSERT INTO pair_split (user_sid, pair_sid) VALUES ' . implode(',', $entries);

			db_query($sql);

			$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."Pairs assigned.";		
		}

	} elseif ($mod->vars->i_action == 'del') {
		# If there were no errors, proceed with deletion
		if ($error_msg == '') {
			$sql = <<<EOF
DELETE
FROM
	pair_split
WHERE
	user_sid = "{$user_id}" AND
	pair_sid BETWEEN {$mod->vars->i_start} AND {$mod->vars->i_end}
EOF;
			db_query($sql);
			# Delete all annotations by the user
			$sql = <<<EOF
DELETE
FROM
	annotation
WHERE
	user_sid = "{$user_id}" AND
	pair_sid BETWEEN {$mod->vars->i_start} AND {$mod->vars->i_end}
EOF;
			db_query($sql);
			$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."Pairs de-assigned.";
		}
	}
}

if (isset($error_msg)&&strlen($error_msg)) {
	echo "<div class='error'>{$error_msg}</div><br />";
}

if (isset($other_msg)&&strlen($other_msg)) {
	echo "<div class='green'>{$other_msg}</div><br />";
	echo "<script lang='javascript'>setTimeout(\"document.location='/data_conf/assign'\",$user->refresh);</script>";
	closeBODY();
	closeHTML();
	exit;
}

openForm($mod->self, 'POST', 'form', 'form');
?>
  <table class='non-exist' border='0' width='100%'>
    <tr>
      <th class='pagingth' colspan='1'>User</th>
      <th class='pagingth' colspan='4'>Assigned pairs</th>
    </tr>
    <tr>
<?
# Pull all the users
$sql = <<<EOF
SELECT
	user_sid,
	username
FROM
	warp_user
WHERE
	not admin AND
	not disabled
ORDER BY
	username
EOF;

$data = db_query($sql);
# Loop through users
while ($gw_user = db_fetch_object($data)) {

	# Get all the assigned pairs for the user
	$sql2 = <<<EOF
SELECT
	pair_sid
FROM
	pair_split
WHERE
	user_sid = {$gw_user->user_sid}
ORDER BY
	pair_sid
EOF;
	$a_data = db_query($sql2);

	# Get all the ranges
	$ranges = getRanges($a_data);

	# Implode them with commas
	$own_ranges = implode(' ; ',$ranges);	

?>
      <th class='pagingth' colspan='1'style='text-align: left;'><label for='i_username'><?=$gw_user->username?></label></th>
      <th class='pagingth' colspan='4'><?=$own_ranges?></td>
    </tr>
<?
}
?>
<tr class="blank_row">
    <td colspan="5"></td>
</tr>
    <tr>
      <th class='pagingth' colspan='5' style='text-align: center;'>Assign or De-assign pairs</th>
    </tr>
    <tr>
      <th class='pagingth'>User</th>
      <th class='pagingth'>Action</th>
      <th class='pagingth' width='64px'>Start ID</th>
      <th class='pagingth' width='64px'>End ID</th>
      <th class='pagingth'></th>
    </tr>
    <tr>
      <th class='pagingth'><select class='shadowinput-small' id='i_user_id' name='i_user_id' value='<?=(isset($mod->vars->i_user_id))?$mod->vars->i_user_id:''?>';>
<?
        $sql = <<<EOF
SELECT
        username,
	user_sid
FROM
        warp_user
WHERE
	not disabled AND
	not admin;
EOF;
$s_data = db_query($sql);

while ($r = db_fetch_object($s_data)) {
	if ($mod->vars->i_user_id == $r->user_sid) {
		echo "<option value='{$r->user_sid}' selected>{$r->username}</option>";
	} else {
		echo "<option value='{$r->user_sid}'>{$r->username}</option>";
	}

}

?></select>
    </th>
      <th class='pagingth'><select class='shadowinput-small' id='i_action' name='i_action' value='<?=(isset($mod->vars->i_action))?$mod->vars->i_action:''?>';>
        <option value='add'>Assign</option>
        <option value='del'>De-assign</option>
        </select>
      </th>
      <th class='pagingth'><input style='width:150px;' type='text' id='i_start' name='i_start' class='shadowinput' value='<?=(isset($mod->vars->i_start))?$mod->vars->i_start:''?>' /></th>
      <th class='pagingth'><input style='width:150px;' type='text' id='i_end' name='i_end' class='shadowinput' value='<?=(isset($mod->vars->i_end))?$mod->vars->i_end:''?>' /></th>
      <th class='pagingth'><a href="#" onclick="document.forms['form'].submit();return false;" class="btn-small btn-edit"><span>Exec</span></a></th>
    </tr>
  </table>
<?
closeForm();

closeBODY();
closeHTML();

?>
