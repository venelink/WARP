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
        layer
FROM
        relations
WHERE
        rel_sid = {$mod->vars->id};
EOF;
        if (!db_count_rows($data = db_query($sql))) {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Relation doesn't exists.";
        }

	if ($error_msg == '') {
		$obj = db_fetch_object($data);
		$layer = $obj->layer;
 
		# Delete all annotations of the type and the child types
		$sql = <<<EOF
DELETE 
	annotation
FROM
	annotation
LEFT JOIN
	relations
ON
	annotation.rel_sid = relations.rel_sid
WHERE
	parent = {$mod->vars->id} OR
	annotation.rel_sid = {$mod->vars->id}
EOF;
		db_query($sql);

		# Delete all child types
		$sql = <<<EOF
DELETE FROM
	relations
WHERE
	parent = {$mod->vars->id};
EOF;
		db_query($sql);
		
		# Delete the type
		$sql = <<<EOF
DELETE FROM
	relations
WHERE
	rel_sid = {$mod->vars->id};
EOF;
                db_query($sql);
		$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."Relation deleted.";
	}
}

if (isset($error_msg)&&strlen($error_msg)) {
	echo "<div class='error'>{$error_msg}</div><br />";
	echo "<script lang='javascript'>setTimeout(\"document.location='/layer_conf/configure?id={$layer}'\",$user->refresh);</script>";
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
<input type='hidden' name='i_confirm' id='ii_confirm' value='1' />
  <table class='non-exist' border='0' width='100%'>
    <tr>
	<div class='error'>
Deleting the relation is irreversible!<br/>
Deleting the relation will delete all sub-relations and/or values<br/>
Deleting the relation will also permanently delete all annotations of this kind!<br/>
Are you sure you want to delete this relation?
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
