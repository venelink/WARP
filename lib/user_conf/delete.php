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
if (isset($mod->vars->i_confirm) && $mod->vars->i_confirm == "1") {
	# data verification
	$error_msg = '';
	$other_msg = '';

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

	if ($error_msg == '') {
		# Delete all annotations by the user
		$sql = <<<EOF
DELETE FROM
	annotation
WHERE
	user_sid = {$mod->vars->id};
EOF;
		db_query($sql);

		# Delete all pair assignments for the user
		$sql = <<<EOF
DELETE FROM
	pair_split
WHERE
	user_sid = {$mod->vars->id};
EOF;
		db_query($sql);
		

		# Delete the user
		$sql = <<<EOF
DELETE FROM
	warp_user
WHERE
	user_sid = {$mod->vars->id};
EOF;
                db_query($sql);
		$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."User deleted.";
	}
}

if (isset($error_msg)&&strlen($error_msg)) {
	echo "<div class='error'>{$error_msg}</div><br />";
	echo "<script lang='javascript'>setTimeout(\"document.location='/user_conf/list'\",$user->refresh);</script>";
        closeBODY();
        closeHTML();
        exit;
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
<input type='hidden' name='i_confirm' id='i_confirm' value='1' />
  <table class='non-exist' border='0' width='100%'>
    <tr>
	<div class='error'>
Deleting the user is irreversible!<br/>
Deleting the user will also permanently delete all of his or her annotations!<br/>
If you want to keep annotations done by the user, use "edit" and "disable"!<br/>
Are you sure you want to delete this user?
</div>
    </tr>
    <tr>
      <td><a href="#" onclick="document.forms['form'].submit();return false;" class="btn-medium common-button"><span>DELETE</span></a></td>
      <td><a href="#" onclick="document.location='/user_conf/list';return false;" class="btn-medium common-button"><span>CANCEL</span></a></td>
    </tr>
</table>
<?

closeBODY();
closeHTML();
?>
