<?
openHTML();
openHEAD();
closeHEAD();
openBODY(3);

# File that handles "default" layer 1 - textual relations
# The initial input to this file should be pair id and layer order id

$error_msg = '';
$other_msg = '';

# Sanitize the input
$pair_sid = (int) $mod->vars->pair_sid;
$layer_id = (int) $mod->vars->layer_id;

# Pull the config from the database
$sql = <<<EOF
SELECT
	l.source_table,
	l.text_separator,
	lt.inp_type
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

# Check if the type of the layer corresponds to textual

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
	$text_1 = $input_texts->text_1;
	$text_2 = $input_texts->text_2;

	# Remove ALL text separators from the text to be displayed
	$sql = <<<EOF
SELECT
	distinct(text_separator) as ts
FROM
	layers
EOF;
	$data = db_query($sql);
	while ($sep_obj = db_fetch_object($data)) {
		$delimiter = (isset($sep_obj->ts) && $sep_obj->ts != " "?$sep_obj->ts:" ");
		$text_1 = str_replace($delimiter," ",$text_1);
		$text_2 = str_replace($delimiter," ",$text_2);
	}
}

# Pull the existing annotation
$sql = <<<EOF
SELECT
	rel_sid,
	meta_sid
FROM
	annotation
WHERE
	pair_sid = {$pair_sid} AND
	layer = {$layer_id} AND
	user_sid = {$user->user_sid}
EOF;

$old_anno = array();
$data = db_query($sql);
while($obj=db_fetch_object($data)) {
	$old_anno[$obj->meta_sid]=$obj->rel_sid;
}

# if we have data - attempt to update the entry
if (isset($mod->vars->sub_anno)) {
	# Loop through all variables
	foreach ($mod->vars as $var=>$val) {
		# ignore id, layer, submit
		if (!in_array($var,array("pair_sid","layer_id","sub_anno"))) {
			$var = (int) $var;
			$val = (int) $val;
			$text_1_esc = db_escape_string($text_1);
			$text_2_esc = db_escape_string($text_2);
			# Sanity check
			# No relation should have ID 0 (it's seq starting from 1)
			# This should filter out empty values
			if ($val == 0) {
				continue;
			}

			# Check if data for the values already exists
			# Do not insert duplicate entries (!)
			if (isset($old_anno[$var])){
				# Update the DB
				$sql = <<<EOF
UPDATE
	annotation
SET
	rel_sid = {$val}
WHERE
	meta_sid = {$var} AND
	user_sid = {$user->user_sid} AND
	layer = {$layer_id}
EOF;
				db_query($sql);
			} else {	
				# Insert in DB
				$sql = <<<EOF
INSERT INTO
	annotation
	(pair_sid, rel_sid, meta_sid, s1_scope, s2_scope, s1_text, s2_text, user_sid, layer)
VALUES
	({$pair_sid}, {$val}, {$var}, "all", "all", "{$text_1_esc}", "{$text_2_esc}", {$user->user_sid}, {$layer_id})
EOF;
				db_query($sql);
			}
		}
	}
	$other_msg = "OK";
}

# Print errors
if (isset($error_msg)&&strlen($error_msg)) {
	echo "<div class='error'>".$error_msg."</div><br />";
	killpage();
}
# Print OK and redirect back to meta or to the parrent layer
if (isset($other_msg)&&strlen($other_msg)) {
	echo "<div class='green'>{$other_msg}</div><br />";
	echo "<script lang='javascript'>setTimeout(\"document.location='/anno/meta?pair_sid={$pair_sid}&layer_id={$layer_id}'\",$user->refresh);</script>";
	closeBODY();
	closeHTML();
	exit;
}
openForm($mod->self);
?>
  <table class='non-exist' border='0' width='100%'>
  <tr>
    <th class='pagingth' colspan='2' width='25%' style='text-align: right; min-width: 160px;'><label for='text1'>Text 1:</label>&nbsp;</th>
    <th class='pagingth' colspan='3' style='text-align: center; min-width: 700px;'><?=$text_1?></th>
  </tr>
  <tr>
    <th class='pagingth' colspan='2' width='25%' style='text-align: right; min-width: 160px;'><label for='text2'>Text 2:</label>&nbsp;</th>
    <th class='pagingth' colspan='3' style='text-align: center; min-width: 700px;'><?=$text_2?></th>
  </tr>
<?

# Pull all the possible meta relations
$sql = <<<EOF
SELECT
	rel_sid,
	type_name
FROM
	relations
WHERE
	layer = {$layer_id} AND
	parent = 0;
EOF;
$data=db_query($sql);
# Loop through all possible meta relations
while ($dropdown = db_fetch_object($data)) {
	# Pull all the options for each relation
	$inner_sql = <<<EOF
SELECT
	rel_sid,
	type_name
FROM
	relations
WHERE
	layer = {$layer_id} AND
	parent = {$dropdown->rel_sid}
EOF;
	$data_2 = db_query($inner_sql);

	# Check the number of options
	# If it's less than three - generate radio buttons
	if (db_count_rows($data_2) < 3) {
		echo "<tr><th class='pagingth' colspan='2' width='25%' style='text-align: right; min-width: 160px;'><label for={$dropdown->type_name}>{$dropdown->type_name}</label>&nbsp;</th>\n";
		echo "<th class='pagingth' colspan='3'>\n";
		while ($sel_opt = db_fetch_object($data_2)) {
			# Check if we have old annotation for this
			if ($old_anno[$dropdown->rel_sid] == $sel_opt->rel_sid) {
				echo"<input type='radio' id='{$sel_opt->type_name}' name='{$dropdown->rel_sid}' value={$sel_opt->rel_sid} class='radio' checked/>\n";
				echo"<label for='{$sel_opt->type_name}'>{$sel_opt->type_name}</label>\n";
			} else {
				echo"<input type='radio' id='{$sel_opt->type_name}' name='{$dropdown->rel_sid}' value={$sel_opt->rel_sid} class='radio'/>\n";
				echo"<label for='{$sel_opt->type_name}'>{$sel_opt->type_name}</label>\n";
			}
		}
		echo "</th></tr>";
	# Otherwise, generate dropdown
	} else {

		echo "<tr><th class='pagingth' colspan='2' width='25%' style='text-align: right; min-width: 160px;'><label for={$dropdown->type_name}>{$dropdown->type_name}</label>&nbsp;</th>\n";
		echo "<th class='pagingth' colspan='3'><select class='shadowinput-small' id='{$dropdown->rel_sid}' name='{$dropdown->rel_sid}'>\n";
		while ($sel_opt = db_fetch_object($data_2)) {
			# Check if we have old annotation for this
			if ($old_anno[$dropdown->rel_sid] == $sel_opt->rel_sid) {
				echo "<option value='{$sel_opt->rel_sid}' selected>{$sel_opt->type_name}</option>\n";
			} else {
				echo "<option value='{$sel_opt->rel_sid}'>{$sel_opt->type_name}</option>\n";
			}
		}
		echo "<option value='0' selected>None</option>\n";
		echo "</tr>";
	}
}
?>
      <input type='hidden' name='pair_sid' id='pair_sid' value='<?=$pair_sid?>' />
      <input type='hidden' name='layer_id' id='layer_id' value='<?=$layer_id?>' />
      <input type='hidden' name='sub_anno' id='sub_anno' value='1' />
    <tr>
      <td colspan='3'><a href="#" onclick="document.forms['form'].submit();return false;" class="btn-medium common-button"><span>Next</span></a></td>
    </tr>
</table>
<?
closeForm();

closeBODY();
closeHTML();
?>
