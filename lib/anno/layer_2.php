<?
openHTML();
openHEAD();
closeHEAD();
openBODY(3);

# File that handles "default" layer 2 - atomic relations
# The initial input to this file should be pair id and layer order id

$error_msg = '';
$other_msg = '';

# Sanitize the input
$pair_sid = (int) $mod->vars->pair_sid;
$layer_id = (int) $mod->vars->layer_id;

# Pull the config from the database
$sql = <<<EOF
SELECT
	l.parrent,
	l.child,
	l.source_table,
	l.text_separator,
	lt.inp_type,
	l.display_prev,
	l.lock_text
FROM
	layers as l
LEFT JOIN
	layer_types as lt
ON
	l.layer_type = lt.ltype_sid
WHERE
	NOT l.disabled AND
	l.layer_sid = {$layer_id};
EOF;
# If we can't pull the config, throw error
if (!db_count_rows($data = db_query($sql))) {
	$error_msg = $error_msg . "No active layers found! Check your config!";
# Otherwise, pull the layer config
} else {
	$layer_config = db_fetch_object($data);
}


# Pull the texts from the table
$sql = <<<EOF
SELECT
	text_1,
	text_2
FROM
	{$layer_config->source_table}
WHERE
	pair_sid = {$pair_sid};
EOF;

# If we can't pull the texts, throw error
if (!db_count_rows($data = db_query($sql))) {
	$error_msg = $error_msg . "No input pair found! Check your config!";
# Otherwise, pull the data
} else {
	$input_texts = db_fetch_object($data);
	$delimiter = (isset($layer_config->text_separator) && $layer_config->text_separator != " "?$layer_config->text_separator:" ");
	$text_1 = str_replace($delimiter," ",$input_texts->text_1);
	$text_2 = str_replace($delimiter," ",$input_texts->text_2);
}

# Print errors
if (isset($error_msg)&&strlen($error_msg)) {
	echo "<div class='error'>".$error_msg."</div><br />";
	killpage();
}

?>
<table class='non-exist' border='0' width='100%'>
  <tr>
    <th class='pagingth' colspan='1' width='25%' style='text-align: right; min-width: 160px;'><label for='text_1'>Text 1:</label>&nbsp;</th>
    <th class='pagingth' colspan='3' style='text-align: center; min-width: 700px;'><?=$text_1?></th>
  </tr>
  <tr>
    <th class='pagingth' colspan='1' width='25%' style='text-align: right; min-width: 160px;'><label for='text_2'>Text 2:</label>&nbsp;</th>
    <th class='pagingth' colspan='3' style='text-align: center; min-width: 700px;'><?=$text_2?></th>
  </tr>
<?
# Check if display previous is enabled
if (isset($layer_config->display_prev) && $layer_config->display_prev == 1) {

	# Pull all previous layers
	$sql = <<<EOF
SELECT
	l.layer_sid,
	lt.inp_type
FROM
	layers as l
LEFT JOIN
	layer_types as lt
ON
	l.layer_type = lt.ltype_sid
WHERE
	NOT disabled AND
	l.layer_order < {$layer_id};
EOF;
	$data = db_query($sql);
	# Loop through previous layers
	while ($prev_layer = db_fetch_object($data)) {
		# Pull the annotation for the layer
		$inner_sql = <<<EOF
SELECT
	a.rel_sid,
	r_type.type_name as type_name,
	a.meta_sid,
	r_meta.type_name as meta_name,
	a.s1_scope,
	a.s2_scope,
	a.s1_text,
	a.s2_text,
	a.key_s1,
	a.key_s2,
	a.k1_text,
	a.k2_text
FROM
	annotation as a
LEFT JOIN
	relations as r_type
ON
	a.rel_sid = r_type.rel_sid
LEFT JOIN
	relations as r_meta
ON
	a.meta_sid = r_meta.rel_sid
WHERE
	a.pair_sid = {$pair_sid} AND
	a.user_sid = {$user->user_sid} AND
	a.layer = {$prev_layer->layer_sid};
EOF;
		$data_2 = db_query($inner_sql);
		# Check the type of the layer (it determines the display)
		# For the FIRST version, we only support display of previous inp_type 0, the other types will be added later
		if ($prev_layer->inp_type == 0) {
			while ($prev_anno = db_fetch_object($data_2)) {
				echo "<tr><th class='pagingth' colspan='1' width='25%' style='text-align: right; min-width: 160px;'><label for='{$prev_anno->meta_name}'>{$prev_anno->meta_name}</label>&nbsp;</th>\n";
				echo "<th class='pagingth' colspan='3' style='text-align: center; min-width: 700px;'><label for='{$prev_anno->type_name}'>{$prev_anno->type_name}</label>&nbsp;</th></tr>\n";
			}
		}
	}
}
# Submit button. Goes back to "meta" with the current layer and pair id
?>
  <tr>
    <th class='pagingth' style='text-align: right; min-width: 120px;' colspan='3'></th>
    <th class='pagingth'><a href='#' onclick="document.location='/anno/meta?pair_sid=<?=$pair_sid?>&layer_id=<?=$layer_id?>';return false;"class="btn-medium common-button"><span>Next</span></a></th>
  </tr>
  <tr><th class='pagingth' colspan='4'><h4>Current Annotation</h4></th></tr>
  <tr>
    <th class='pagingth'>Type</th>
    <th class='pagingth'>Scope</th>
    <th class='pagingth'>Key</th>
    <th class='pagingth'>Actions</th>
  </tr>
<?
# Pull the current annotations of this pair on this layer
$sql = <<<EOF
SELECT
	a.anno_sid as anno_sid,
	a.s1_text as scope_1,
	a.s2_text as scope_2,
	COALESCE(a.k1_text,"n/a") as key_1,
	COALESCE(a.k2_text,"n/a") as key_2,
	r.short_type as type_name
FROM
	annotation as a
LEFT JOIN
	relations as r
	ON a.rel_sid = r.rel_sid
WHERE
	a.pair_sid = {$pair_sid} AND
	a.layer = {$layer_id} AND
	a.user_sid = {$user->user_sid}
ORDER BY
	a.meta_sid
EOF;
	$data = db_query($sql);
	# If there are any annotations, loop through them and display them
	if (db_count_rows($data)) {
		while ($row = db_fetch_object($data)) {
?>
  <tr>
    <td class='pagingtd0' style='text-align: left; min-width: 120px;'><?=$row->type_name?></td>
    <td class='pagingtd0' style='text-align: left; min-width: 320px;'>&nbsp;</td>
    <td class='pagingtd0' style='text-align: left; min-width: 240px;'>&nbsp;</td>
    <td class='pagingtd0'>
<?
	# In future version, here will be the option to add button for child layer
?>
      <a href='#' onclick="document.location='/anno/del_type?id=<?=$row->anno_sid?>';return false;" class='btn-small btn-delete'><span>Delete</span></a>
    </td>
  </tr>
  <tr>
    <td class='pagingtd1' style='text-align: right; min-width: 120px;'>Text 1</td>
    <td class='pagingtd1'><?=$row->scope_1?></td>
    <td class='pagingtd1'><?=$row->key_1?></td>
    <td class='pagingtd1'>&nbsp;</td>
  </tr>
  <tr>
    <td class='pagingtd1' style='text-align: right; min-width: 120px;'>Text 2</td>
    <td class='pagingtd1'><?=$row->scope_2?></td>
    <td class='pagingtd1'><?=$row->key_2?></td>
    <td class='pagingtd1'>&nbsp;</td>
  </tr>

<?
		}
	} else {
?>
  <tr>
    <td class='pagingtd0' colspan='4' style='text-align: center;'>No annotated relations.</td>
  </tr>
<?
	}
?>
  <tr><th class='pagingth' colspan='4'><h4>Add Type</h4></th></tr>
  <tr>
    <form method='POST' action='/anno/add_type' name='form' id='form'>
      <input type='hidden' name='pair_sid' id='pair_sid' value='<?=$pair_sid?>' />
    <th class='pagingth' colspan='1'><select class='shadowinput-small' id='meta_type' name='meta_type' onChange="document.getElementById('td_rel_sid').innerHTML = td_values[document.getElementById('meta_type').value]";><?
# Get all meta categories for the current layer
$sql = <<<EOF
SELECT DISTINCT
	type_name,
	rel_sid
FROM
	relations
WHERE
	parent = 0 AND
	layer = {$layer_id}
ORDER BY
	rel_sid
EOF;
	$mt_list = db_query($sql);
	# Loop through all meta categories
	while ($meta_types = db_fetch_object($mt_list)) {
		# Generate the buttons for the first drop down
		echo "<option value='{$meta_types->rel_sid}'>{$meta_types->type_name}</option>\n";
		# Get all subcategories for the curent meta category
		$sql = <<<EOF
SELECT
	rel_sid,
	type_name
FROM
	relations
WHERE
	parent = {$meta_types->rel_sid}
ORDER BY
	rel_sid
EOF;
		$s_data = db_query($sql);

		# Generate a different select for each different meta category
		# Changing the value of the meta category button automatically changes the
		# second select for the sub categories
		$s_data_out[$meta_types->rel_sid] = "<select class='shadowinput-large' id='type_data' name='type_data'>";
		while ($r = db_fetch_object($s_data)) {
			 $s_data_out[$meta_types->rel_sid] = $s_data_out[$meta_types->rel_sid]."<option value='{$r->rel_sid}'>{$r->type_name}</option>";
		}
		$s_data_out[$meta_types->rel_sid] = $s_data_out[$meta_types->rel_sid]."</select>";

		if (!isset($td_services_list_init)) {
			$td_services_list_init = $s_data_out[$meta_types->rel_sid];
		}

	}
	

?></select></th>
<script lang='javascript'>
	td_values=new Array();
<?
foreach ($s_data_out AS $var=>$val) {
	echo "td_values['{$var}'] = \"{$val}\";\n";
}
?>
</script>
    <th class='pagingth' id='td_rel_sid' colspan='2'><?=$td_services_list_init?></th>
    <input type='hidden' name='rel_sid' id='rel_sid' value='' />
    <input type='hidden' name='layer_id' id='layer_id' value='<?=$layer_id?>' />
    <th class='pagingth'>
      <input type='submit' style='display: none;' />
      <a href='#' onclick="document.getElementById('rel_sid').value=document.getElementById('type_data').value; document.forms['form'].submit();return false;" class="btn-medium common-button"><span>Add Type</span></a>
    </th>
  </tr>
  </form>

</table>

<?


closeBODY();
closeHTML();
?>
