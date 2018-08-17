<?
openHTML();
openHEAD();
closeHEAD();
openBODY(3);
?>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.js"></script>
<?

# This file contains the actual scope annotation
# It takes pair id, layer id, type id  variables as inputa

# Sanitize the values
$pair_sid = (int)$mod->vars->pair_sid;
$rel_sid = (int)$mod->vars->rel_sid;
# Layer ID to go back to
$layer_id = (int)$mod->vars->layer_id;

# Pull the layer config
$sql = <<<EOF
SELECT
	source_table,
	text_separator,
	lock_text
FROM
	layers
WHERE
	NOT disabled AND
	layer_sid = {$layer_id};
EOF;

# If we can't pull the config, throw error
if (!db_count_rows($data = db_query($sql))) {
	echo "<div class='error'>No active layers found! Check your config!</div><br />";
	killPage();
# Otherwise, pull the layer config
} else {
	$layer_config = db_fetch_object($data);
}

# Delimiter
$delimiter = (isset($layer_config->text_separator) && $layer_config->text_separator != " "?$layer_config->text_separator:" ");

# Get the two texts from the given table
$sql = <<<EOF
SELECT
	text_1 as text_1,
	text_2 as text_2
FROM
	{$layer_config->source_table}
WHERE
	pair_sid = '{$pair_sid}';
EOF;

# If you can't, throw error
if (!db_count_rows($data = db_query($sql))) {
	echo "<div class='error'>Pair not found</div><br />";
	killPage();
}

$pair = db_fetch_object($data);

# Get the details for the relation type
$sql = <<<EOF
SELECT
	type_name,
	type_key
FROM
	relations
WHERE
	rel_sid = {$rel_sid};
EOF;

# If you can't, throw error
if (!db_count_rows($s_list = db_query($sql))) {
	echo "<div class='error'>Relation not found</div><br />";
	killPage();
}

$shead = db_fetch_object($s_list);

?>
<table class='non-exist' border='0' width='100%'>
  <tr>
    <th class='pagingth' colspan="5"><h4>Mark all the elements that belong to relation type <?=$shead->type_name?></h4></th>
  <tr>
    <form method='POST' action='/anno/update_type' name='form' id='form'>
    <input type='hidden' name='pair_sid' id='pair_sid' value='<?=$pair_sid?>' />
    <input type='hidden' name='rel_sid' id='rel_sid' value='<?=$rel_sid?>' />
    <input type='hidden' name='layer_id' id='layer_id' value='<?=$layer_id?>' />
    <th class='pagingth' style='text-align: right; min-width: 80px;'>Text 1:</th>
    <th class='pagingth' style='text-align: center; min-width: 700px;' colspan="3">
<?

# Check if text_1 is locked
if ($layer_config->lock_text == 1) {
	# If it is, just display the text, without option to select it
	$text_1 = str_replace($delimiter," ",$pair->text_1);
?>
	<?=$text_1?></th>
	<th class='pagingth' style='text-align: right; min-width: 80px;'>
	<span>&nbsp;</span>
    </th>
  </tr>

<?
# If sentence one is not locked proceed with the selection
} else {
	# Split the text by the delimiter
	$tokens = explode($delimiter,$pair->text_1);
	# Ugly fix for identifying sentences
	# 100 + token ID = the ID of the elements in sentence one
	# 200+ ... sentence two
	# 300+ ... key elements in text 1
	# 400+ ... key elements in text 2
	# These IDs are assigned to the different buttons so that the JS can handle them after
	$sent_inc = 100;
	foreach ($tokens as $tid => $token) {
		$token_uid = $sent_inc + $tid;
?>
        <input type='hidden' name='<?=$token_uid?>' id='<?=$token_uid?>' value='0' />
	<span style="cursor: pointer;" onclick="toggle(this,<?=$token_uid?>)"><u><?=$token?></u></span>
	<span>&nbsp;</span>
<?
	# Generate the option to select the whole text
	# This part can be commented if not usef
	# Special ID X99 is used to mark "all"
	}
?> 
    </th>
    <th class='pagingth' style='text-align: right; min-width: 80px;'>
        <input type='hidden' name='199' id='199' value='0' />
	<span style="cursor: pointer;" onclick="toggle(this,199)"><u>Whole text</u></span>
    </th>
  </tr>
<?
}
?>
  <tr>
    <th class='pagingth' style='text-align: right; min-width: 80px;'>Text 2:</th>
    <th class='pagingth' style='text-align: center; min-width: 700px;' colspan="3">
<?
# Check if text2 is locked 
if ($layer_config->lock_text == 2) {
	# If it is, just display the text, without option to select it
	$text_2 = str_replace($delimiter," ",$pair->text_2);
?>
	<?=$text_2?></th>
	<th class='pagingth' style='text-align: right; min-width: 80px;'>
	<span>&nbsp;</span>
    </th>
  </tr>

<?
# If sentence two is not locked proceed with the selection
} else {

	$tokens = explode($delimiter,$pair->text_2);
	# See the comment about the ugly ID fix on text 1
	$sent_inc = 200;
	foreach ($tokens as $tid => $token) {
		$token_uid = $sent_inc + $tid;
?>
        <input type='hidden' name='<?=$token_uid?>' id='<?=$token_uid?>' value='0' />
	<span style="cursor: pointer;" onclick="toggle(this,<?=$token_uid?>)"><u><?=$token?></u></span>
	<span>&nbsp;</span>
<?
	}
?> 
    </th>
    <th class='pagingth' style='text-align: right; min-width: 80px;'>
        <input type='hidden' name='299' id='299' value='0' />
	<span style="cursor: pointer;" onclick="toggle(this,299)"><u>Whole text</u></span>
	<span>&nbsp;</span>
    </th>
  </tr>
<?
}
# Check if key is enabled
# Key is a second selection (i.e. when two separate (overlapping) scopes 
# need to be annotated for the same relation)
if ($shead->type_key == 1) {
?>
  <tr>
    <th class='pagingth' colspan="5"><h4>Mark the KEY elements of the relation type <?=$shead->type_name?></h4></th>
<?
	# If sentence 1 is locked, do not display key_1 altogether
	if ($layer_config->lock_text != 1) {
?>
  <tr>
    <th class='pagingth' style='text-align: right; min-width: 80px;'>Sentence 1:</th>
    <th class='pagingth' style='text-align: center; min-width: 700px;' colspan="3">
<?
		$tokens = explode($delimiter,$pair->text_1);
		# See ID comment above
		$sent_inc = 300;
		foreach ($tokens as $tid => $token) {
			$token_uid = $sent_inc + $tid;
?>
        <input type='hidden' name='<?=$token_uid?>' id='<?=$token_uid?>' value='0' />
	<span style="cursor: pointer;" onclick="toggle(this,<?=$token_uid?>)"><u><?=$token?></u></span>
	<span>&nbsp;</span>
<?
		}
?> 
    </th>
    <th class='pagingth' style='text-align: right; min-width: 80px;'>
	&nbsp;
    </th>
  </tr>
<?
	}
	# If sentence 2 is locked, do not display key_2 altogether
	if ($layer_config->lock_text != 2) {
?>
  <tr>
    <th class='pagingth' style='text-align: right; min-width: 80px;'>Sentence 2:</th>
    <th class='pagingth' style='text-align: center; min-width: 700px;' colspan="3">
<?
		$tokens = explode($delimiter,$pair->text_2);
		# See ID comment above
		$sent_inc = 400;
		foreach ($tokens as $tid => $token) {
			$token_uid = $sent_inc + $tid;
?>
        <input type='hidden' name='<?=$token_uid?>' id='<?=$token_uid?>' value='0' />
	<span style="cursor: pointer;" onclick="toggle(this,<?=$token_uid?>)"><u><?=$token?></u></span>
	<span>&nbsp;</span>
<?
		}
?> 
    </th>
    <th class='pagingth' style='text-align: right; min-width: 80px;'>
	&nbsp;
    </th>
  </tr>

<?
	}
}
?>
  <tr>
    <th class='pagingth' colspan="5">
      <input type='submit' style='display: none;' />
      <a href='#' onclick="document.forms['form'].submit();return false;" class="btn-medium common-button"><span>Add Type</span></a>
    </th>
  </tr>
  </form>

</table>

<script type="text/javascript">//<![CDATA[

function toggle(e,id){

	if(document.getElementById(id).value == 0)
  {
	document.getElementById(id).value = 1;
    $(e).css('background-color','blue');
  }
  else
  {
	document.getElementById(id).value = 0;
    $(e).css('background-color','');
  }
}
//]]> 

</script>

<?

closeBODY();
closeHTML();

?>
