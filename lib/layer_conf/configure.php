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
if (isset($mod->vars->i_name)) {
        # data verification
        $error_msg = '';
        $other_msg = '';
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $mod->vars->i_name) || strlen($mod->vars->i_name) < 2) {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Name should be alphanumeric and at least 6 chars long.";
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $mod->vars->i_sname) || strlen($mod->vars->i_sname) < 4) {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Short name should be alphanumeric and at least 4 chars long.";
        }

	if (!preg_match('/^[0-9]+$/', $mod->vars->i_parent)) {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid parent.";
	}

        if ($mod->vars->i_key != 0 && $mod->vars->i_key != 1) {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid key.";
        }

        # Check for name
        $r_name = db_escape_string($mod->vars->i_name);
	$r_sname = db_escape_string($mod->vars->i_sname);
        $sql = <<<EOF
SELECT
        TRUE
FROM
        relations
WHERE
        type_name = "{$r_name}" OR
	short_type = "{$r_sname}"
EOF;

        if (db_count_rows(db_query($sql))) {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Relationship exists.";
        }

        # Check if parent id is valid
	$parent = db_escape_string($mod->vars->i_parent);
	if ($parent != 0) {
        	$sql = <<<EOF
SELECT
        TRUE
FROM
        relations
WHERE
        rel_sid = {$parent};
EOF;
        	if (!db_count_rows(db_query($sql))) {
                	$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid parent.";
        	}
	}

	if ($error_msg == '') {
		$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."Checks passed.";
		
		$sql = <<<EOF
INSERT INTO
	relations
	(type_name, type_key, short_type, parent, layer)
VALUES
	("{$r_name}",{$mod->vars->i_key},"{$r_sname}",{$parent},{$mod->vars->id})
EOF;
		db_query($sql);
		$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."layer updated.";
	}
} else {
$sql = <<<EOF
SELECT
	inp_type
FROM
	layers 
LEFT JOIN
	layer_types 
ON
	layer_type = ltype_sid 
WHERE
	layer_sid = {$mod->vars->id}
EOF;
	if (!db_count_rows($data = db_query($sql))) {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid layer.";
	} else {
		$obj = db_fetch_object($data);
		$ltype = $obj->inp_type;
	}

}

if (isset($error_msg)&&strlen($error_msg)) {
	echo "<div class='error'>{$error_msg}</div><br />";
}

if (isset($other_msg)&&strlen($other_msg)) {
	echo "<div class='green'>{$other_msg}</div><br />";
	echo "<script lang='javascript'>setTimeout(\"document.location='/layer_conf/configure?id={$mod->vars->id}'\",$user->refresh);</script>";
	closeBODY();
	closeHTML();
	exit;
}

openForm($mod->self, 'POST', 'form', 'form');
?>
<input type='hidden' name='id' id='id' value='<?=$mod->vars->id?>' />
  <table class='non-exist' border='0' width='100%'>
    <tr>
      <th class='pagingth' colspan='1'>Relation</th>
      <th class='pagingth' colspan='1'><?=(isset($ltype)&&$ltype==1)?"Sub-relation":"Option"?></th>
      <th class='pagingth' colspan='1'>Short name</th>
      <th class='pagingth' colspan='1'>Key</th>
      <th class='pagingth' colspan='1'>Action</th>
    </tr>
<?
# Pull all existing PARENT relations
$sql = <<<EOF
SELECT
	rel_sid,
	type_name,
	short_type,
	case when type_key=1 then 'yes' else 'no' end as type_key
FROM
	relations
WHERE
	parent = 0 AND
	layer = {$mod->vars->id}
ORDER BY
	type_name
EOF;
$data = db_query($sql);
# Loop through parent relations
while ($p_rel = db_fetch_object($data)) {
	echo"<tr class='blank_row'><td></td></tr>";
?>
    <tr>
      <th class='pagingth' colspan='1'style='text-align: left;'><?=$p_rel->type_name?></th>
      <th class='pagingth' colspan='1'style='text-align: left;'></th>
      <th class='pagingth' colspan='1'style='text-align: left;'><?=$p_rel->short_type?></th>
      <th class='pagingth' colspan='1'style='text-align: left;'><?=$p_rel->type_key?></th>
      <th class='pagingth' colspan='1'style='text-align: left;'><a href="#" onclick="document.location='/layer_conf/del_rel?id=<?=$p_rel->rel_sid?>';return false;" class='btn-small btn-delete'><span>Delete</span></a></th>
    </tr>
<?
	# Pull all children relations
	$sql2 = <<<EOF
SELECT
	rel_sid,
	type_name,
	short_type,
	case when type_key=1 then 'yes' else 'no' end as type_key
FROM
	relations
WHERE
	parent = {$p_rel->rel_sid}
ORDER BY
	type_name
EOF;
	$data2 = db_query($sql2);
	# Loop through child relations
	while ($c_rel = db_fetch_object($data2)) {
?>
    <tr>
      <th class='pagingth' colspan='1'style='text-align: left;'></th>
      <th class='pagingth' colspan='1'style='text-align: left;'><?=$c_rel->type_name?></th>
      <th class='pagingth' colspan='1'style='text-align: left;'><?=$c_rel->short_type?></th>
      <th class='pagingth' colspan='1'style='text-align: left;'><?=$c_rel->type_key?></th>
      <th class='pagingth' colspan='1'style='text-align: left;'><a href="#" onclick="document.location='/layer_conf/del_rel?id=<?=$c_rel->rel_sid?>';return false;" class='btn-small btn-delete'><span>Delete</span></a></th>
    </tr>
<?
	}
}
?>
    <tr class='blank_row'><td></td></tr>
    <tr>
      <th class='pagingth' colspan='5' style='text-align: center;'>Add new relations</th>
    </tr>
    <tr>
      <th class='pagingth' colspan='1'><?=(isset($ltype)&&$ltype==1)?"Meta relation":"Relation"?></th>
      <th class='pagingth' colspan='1'><?=(isset($ltype)&&$ltype==1)?"Relation":"Option"?></th>
      <th class='pagingth' colspan='1'>Short name</th>
      <th class='pagingth' colspan='1'>Key</th>
      <th class='pagingth' colspan='1'></th>
    </tr>
    <tr>
      <th class='pagingth'><select class='shadowinput-small' id='i_parent' name='i_parent' value='<?=(isset($mod->vars->i_parent))?$mod->vars->i_parent:0?>';>
        <option value='0'><?=(isset($ltype)&&$ltype==1)?"Meta relation":"Relation"?></option>
<?
$sql = <<<EOF
SELECT
	rel_sid,
	type_name
FROM
	relations
WHERE
	parent = 0 AND
	layer = {$mod->vars->id}
ORDER BY
	type_name
EOF;
$r_data = db_query($sql);
while ($r = db_fetch_object($r_data)) {
        if ($mod->vars->i_parent == $r->rel_sid) {
                echo "<option value='{$r->rel_sid}' selected>{$r->type_name}</option>";
        } else {
                echo "<option value='{$r->rel_sid}'>{$r->type_name}</option>";
        }

}


?>
      <th class='pagingth'><input style='width:150px;' type='text' id='i_name' name='i_name' class='shadowinput' value='<?=(isset($mod->vars->i_name))?$mod->vars->i_name:''?>' /></th>
      <th class='pagingth'><input style='width:150px;' type='text' id='i_sname' name='i_sname' class='shadowinput' value='<?=(isset($mod->vars->i_sname))?$mod->vars->i_sname:''?>' /></th>
      <th class='pagingth'><input style='width:50px;' type='text' id='i_key' name='i_key' class='shadowinput' value='<?=(isset($mod->vars->i_key))?$mod->vars->i_key:0?>' /></th>
      <th class='pagingth'><a href="#" onclick="document.forms['form'].submit();return false;" class="btn-small btn-edit"><span>Add</span></a></th>
    </tr>
</table>
<?

closeBODY();
closeHTML();
?>
