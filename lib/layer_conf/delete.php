<?
openHTML();
openHEAD('WARP-Text');
closeHEAD();
openBODY(13);

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

	# Check for the relation
	$sql = <<<EOF
SELECT
	layer_order
FROM
	layers
WHERE
	layer_sid = {$mod->vars->id};
EOF;
	if (!db_count_rows($data = db_query($sql))) {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Layer doesn't exists.";
	}

	if ($error_msg == '') {
 
		# Delete all annotations of the layer
		$sql = <<<EOF
DELETE FROM
	annotation
WHERE
	layer = {$mod->vars->id}
EOF;
		db_query($sql);

		# Delete all relations of the layer
		$sql = <<<EOF
DELETE FROM
	relations
WHERE
	layer = {$mod->vars->id};
EOF;
		db_query($sql);
		
		# Delete the layer
		$sql = <<<EOF
DELETE FROM
	layers
WHERE
	layer_sid = {$mod->vars->id};
EOF;
		db_query($sql);
		$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."Layer deleted.";
	}
}

if (isset($error_msg)&&strlen($error_msg)) {
	echo "<div class='error'>{$error_msg}</div><br />";
	echo "<script lang='javascript'>setTimeout(\"document.location='/layer_conf/list'\",$user->refresh);</script>";
	closeBODY();
	closeHTML();
	exit;
}

if (isset($other_msg)&&strlen($other_msg)) {
	echo "<div class='green'>{$other_msg}</div><br />";
	echo "<script lang='javascript'>setTimeout(\"document.location='/layer_conf/list'\",$user->refresh);</script>";
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
Deleting the layer is irreversible!<br/>
Deleting the layer will delete all relations<br/>
Deleting the layer will also permanently delete all annotations!<br/>
If you want to preserve the annotations and the relations, you can disable the layer in the EDIT page.
Are you sure you want to delete this layer?
</div>
    </tr>
    <tr>
      <td><a href="#" onclick="document.forms['form'].submit();return false;" class="btn-medium common-button"><span>DELETE</span></a></td>
      <td><a href="#" onclick="document.location='/layer_conf/list';return false;" class="btn-medium common-button"><span>CANCEL</span></a></td>
    </tr>
</table>
<?

closeBODY();
closeHTML();
?>
