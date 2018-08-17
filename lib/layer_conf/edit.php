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
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $mod->vars->i_name) || strlen($mod->vars->i_name) < 6) {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Name should be alphanumeric and at least 6 chars long.";
        }

        if ($mod->vars->i_sep == "" || $mod->vars->i_sep == " ") {
                $mod->vars->i_sep = " ";
        } elseif (!preg_match('/^[a-zA-aZ0-9]+$/', $mod->vars->i_sep)) {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Separator should be alphanumeric.";
        }

        if ($mod->vars->i_prev != 'true' && $mod->vars->i_prev != 'false') {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid display previous value.";
        }

        if ($mod->vars->i_enable != 'true' && $mod->vars->i_enable != 'false') {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid enable layer value.";
        }

        if ($mod->vars->i_lock != 0 && $mod->vars->i_lock != 1 && $mod->vars->i_lock != 2 ) {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid text lock value.";
        }

        # Check for name
        $l_name = db_escape_string($mod->vars->i_name);
        $sql = <<<EOF
SELECT
        TRUE
FROM
        layers
WHERE
        layer_name = "{$l_name}";
EOF;

        if (!db_count_rows(db_query($sql))) {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Layer doesn't exists.";
        }

        # Check if type id is valid
        $layer_type = db_escape_string($mod->vars->i_ltype);
        $sql = <<<EOF
SELECT
        TRUE
FROM
        layer_types
WHERE
        ltype_sid = "{$layer_type}";
EOF;
        if (!db_count_rows(db_query($sql))) {
                $error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid layer type.";
        }

	if ($error_msg == '') {
		$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."Checks passed.";
		
		$sql = <<<EOF
UPDATE
	layers
SET
	layer_type = '{$layer_type}',
	text_separator = '{$mod->vars->i_sep}',
	display_prev = {$mod->vars->i_prev},
	lock_text = '{$mod->vars->i_lock}',
	disabled = {$mod->vars->i_enable}
WHERE
	layer_sid = {$mod->vars->id}
EOF;
		db_query($sql);
		$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."layer updated.";
	}
} else {
	$sql = <<<EOF
SELECT
	layer_name	AS	i_name,
	layer_type	AS	i_ltype,
	text_separator	AS	i_sep,
	lock_text	AS	i_lock,
	CASE WHEN display_prev THEN 'true' ELSE 'false' END	AS	i_prev,
	CASE WHEN disabled THEN 'true' ELSE 'false' END AS	i_enable
FROM
	layers
WHERE
	layer_sid = {$mod->vars->id};
EOF;
	$data = db_query($sql);
	if (!db_count_rows($data)) {
		echo "<div class='error'>"."Layer not found"."</div><br />";
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
	echo "<script lang='javascript'>setTimeout(\"document.location='/layer_conf/list'\",$user->refresh);</script>";
	closeBODY();
	closeHTML();
	exit;
}

openForm($mod->self, 'POST', 'form', 'form');
?>
<input type='hidden' name='id' id='id' value='<?=$mod->vars->id?>' />
  <table class='non-exist' border='0' width='100%'>
    <tr>
      <th width='35%' style='text-align: right;'><label for='i_name'>Layer name</label></th>
      <td><input type='text' id='i_name' name='i_name' class='shadowinput' value='<?=(isset($mod->vars->i_name))?$mod->vars->i_name:''?>' readonly/></td>
    </tr>
    <tr>
      <th width='35%' style='text-align: right;'><label for='i_sep'>Text separator</label></th>
      <td><input type='text' id='i_sep' name='i_sep' class='shadowinput' value='<?=(isset($mod->vars->i_sep))?$mod->vars->i_sep:''?>' /></td>
    </tr>
    <tr>
    <th width='35%' style='text-align: right;'><label for='i_ltype'>Layer type</label></td>
    <td><select class='shadowinput' id='i_ltype' name='i_ltype' value='<?=(isset($mod->vars->i_ltype))?$mod->vars->i_ltype:''?>';>
<?
        $sql = <<<EOF
SELECT
        ltype_sid,
        type_name
FROM
        layer_types
EOF;
$s_data = db_query($sql);

while ($r = db_fetch_object($s_data)) {
        if ($mod->vars->i_ltype == $r->ltype_sid) {
                echo "<option value='{$r->ltype_sid}' selected>{$r->type_name}</option>";
        } else {
                echo "<option value='{$r->ltype_sid}'>{$r->type_name}</option>";
        }

}

?></select>
    </td>
    </tr>
    <tr>
    <th width='35%' style='text-align: right;'><label for='i_lock'>Sentence Lock</label></td>
    <td><select class='shadowinput' id='i_lock' name='i_lock' value='<?=(isset($mod->vars->i_lock))?$mod->vars->i_lock:''?>';>
        <option value='0'>None</option>
        <option value='1' <?=(isset($mod->vars->i_lock) && $mod->vars->i_lock == 1)?'selected':''?>>Sentence 1</option>
        <option value='2' <?=(isset($mod->vars->i_lock) && $mod->vars->i_lock == 2)?'selected':''?>>Sentence 2</option>
      </select>
    </td>
    </tr>
    <tr>
      <th width='35%' style='text-align: right;'><label for="i_prev">Show previous layers</label></th>
      <td>
        <input type='radio' id='i_prev' name='i_prev' value='true' class='radio' <?=
                ((!isset($mod->vars->i_prev))||$mod->vars->i_prev=='true')?'checked':''?>/>
        <label for='i_prev'>Yes</label>
        <input type='radio' id='i_non_prev' name='i_prev' value='false' class='radio' <?=
                ((isset($mod->vars->i_prev))&&$mod->vars->i_prev=='false')?'checked':''?>/>
        <label for='i_non_prev'>No</label>
      </td>
    </tr>
    <tr>
      <th width='35%' style='text-align: right;'><label for="i_enable">Disable layer</label></th>
      <td>
        <input type='radio' id='i_enable' name='i_enable' value='true' class='radio' <?=
                ((!isset($mod->vars->i_enable))||$mod->vars->i_enable=='true')?'checked':''?>/>
        <label for='i_enable'>Yes</label>
        <input type='radio' id='i_non_enable' name='i_enable' value='false' class='radio' <?=
                ((isset($mod->vars->i_enable))&&$mod->vars->i_enable=='false')?'checked':''?>/>
        <label for='i_non_enable'>No</label>
      </td>
    </tr>
    <tr>
      <th width='35%' style='text-align: right;'><label for="i_rel">Layer Relations</label></th>
      <td><a href="#" onclick="document.location='/layer_conf/configure?id=<?=$mod->vars->id?>';return false;" class="btn-medium redirect-button"><span>Configure</span></a></td>
    </tr>
    <tr>
      <td colspan='3'><a href="#" onclick="document.forms['form'].submit();return false;" class="btn-medium common-button"><span>Save</span></a></td>
    </tr>
</table>
<?

closeBODY();
closeHTML();
?>
