<?
openHTML();
openHEAD();
closeHEAD();
openBODY(3);

# This page does the actual insertion of the annotation in the database
# It takes pair_sid, rel_sid, layer_id an numeric element IDs as variables

# Sanitize the variables
$pair_sid = (int) $mod->vars->pair_sid;
$rel_sid = (int) $mod->vars->rel_sid;
$layer_id = (int) $mod->vars->layer_id;

# Pull the layer config
$sql = <<<EOF
SELECT
	lt.url,
	l.source_table,
	l.text_separator
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
	echo "<div class='error'>No active layers found! Check your config!</div><br />";
	killPage();
# Otherwise, pull the layer config
} else {
	$layer_config = db_fetch_object($data);
}

# Check if the relation is valid
$sql = <<<EOF
SELECT
	parent
FROM
	relations
WHERE
	rel_sid = {$rel_sid} AND
	layer = {$layer_id};
EOF;

# If we can't pull the config, throw error
if (!db_count_rows($data = db_query($sql))) {
	echo "<div class='error'>Invalid relation. Check your config!</div><br />";
	killPage();
# Otherwise, pull the layer config
} else {
	$meta_id = db_fetch_object($data);
}

# Delimiter
$delimiter = (isset($layer_config->text_separator) && $layer_config->text_separator != " "?$layer_config->text_separator:" ");

# Initialize the variables
$scope_1 = array();
$scope_2 = array();
$key_1 = array();
$key_2 = array();

# Loop through all mod vars
foreach ($mod->vars as $var => $val) {

	# Discard pair, layer, type ids
	if ($var == "pair_sid" || $var == "layer_id" || $var == "rel_sid") {
		continue;
	} 

	# Scope Text 1 - from 100 to 199
	if ((int)$var >= 100 && (int)$var <=199){
		# Check if each element is clicked or not
		if ($val == 1) {
			# Get the original position and put it in the array
			$pos = (int)$var - 100;
			array_push($scope_1,$pos);
		}
	}
	# Scope Text 2 - from 200 to 299 
	if ((int)$var >= 200 && (int)$var <=299){
		# Check if each element is clicked or not
		if ($val == 1) {
			# Get the original position and put it in the array
			$pos = (int)$var - 200;
			array_push($scope_2,$pos);
		}
	}
	# Key Text 1 - from 300 to 399
	if ((int)$var >= 300 && (int)$var <=399){
		# Check if each element is clicked or not
		if ($val == 1) {
			# Get the original position and put it in the array
			$pos = (int)$var - 300;
			array_push($key_1,$pos);
		}
	}
	# Key Text 2 - from 400 to 499
	if ((int)$var >= 400 && (int)$var <=499){
		# Check if each element is clicked or not
		if ($val == 1) {
			# Get the original position and put it in the array
			$pos = (int)$var - 400;
			array_push($key_2,$pos);
		}
	}
}

# Select the text
$sql = <<<EOF
SELECT
	text_1 as text_1,
	text_2 as text_2
FROM
	{$layer_config->source_table}
WHERE
	pair_sid = {$pair_sid};
EOF;

if (!db_count_rows($data = db_query($sql))) {
	echo "<div class='error'>"."Pair not found!"."</div><br />";
	killPage();
}

# Split the texts by the layer delimiter
$pair = db_fetch_object($data);
$sent_1 = explode($delimiter,$pair->text_1);
$sent_2 = explode($delimiter,$pair->text_2);

# Handle the special case of "Whole text" (scope sentence 1)
# By default it just writes "the whole text" in the DB
# Alternatively, you can insert text_1 and text_2 respectively
# The "whole text" input is easier for tracking the progress of
# the annotation and can easily be replaced with actual scope
# after that
if (isset($mod->vars->{"199"}) && $mod->vars->{"199"} == 1) {
	$s1_text = "The whole text.";
	$s1_ids = "whole sentence";
# Otherwise, insert every element. Separate them by whitespace.
} else {
	foreach ($scope_1 as $pos) {
		if (!isset($s1_text)) {
			$s1_text = $sent_1[$pos];
			$s1_ids = "$pos";

		} else { 
			$s1_text = $s1_text . " " . $sent_1[$pos];
			$s1_ids = $s1_ids . ", " . "$pos";
		}
	}
}

# Handle the special case of "Whole text" (scope sentence 2)
if (isset($mod->vars->{"299"}) && $mod->vars->{"299"} == 1) {
	$s2_text = "The whole text.";
	$s2_ids = "whole sentence";
# Otherwise, insert every element. Separate them by whitespace.
} else {
	foreach ($scope_2 as $pos) {
		if (!isset($s2_text)) {
			$s2_text = $sent_2[$pos];
			$s2_ids = "$pos";

		} else { 
			$s2_text = $s2_text . " " . $sent_2[$pos];
			$s2_ids = $s2_ids . ", " . "$pos";
		}
	}
}

# Check if key is added
# If so, insert every element. Separate them by whitespace.
if(!empty($key_1)){
	foreach ($key_1 as $pos) {
		if (!isset($k1_text)) {
			$k1_text = $sent_1[$pos];
			$k1_ids = "$pos";

		} else { 
			$k1_text = $k1_text . " " . $sent_1[$pos];
			$k1_ids = $k1_ids . ", " . "$pos";
		}
	}
# If it is not added, put n/a
# Instead, an empty string can be set here
} else {
	$k1_text = "n/a";
	$k1_ids = "n/a";
}

# Check if key is added
# If so, insert every element. Separate them by whitespace.
if(!empty($key_2)){
	foreach ($key_2 as $pos) {
		if (!isset($k2_text)) {
			$k2_text = $sent_2[$pos];
			$k2_ids = "$pos";

		} else { 
			$k2_text = $k2_text . " " . $sent_2[$pos];
			$k2_ids = $k2_ids . ", " . "$pos";
		}
	}
# If it is not added, put n/a
# Instead, an empty string can be set here
} else {
	$k2_text = "n/a";
	$k2_ids = "n/a";
}

$sql = <<<EOF
INSERT INTO
	annotation
	(rel_sid, pair_sid, s1_scope, s2_scope, s1_text, s2_text, 
	key_s1, key_s2, k1_text, k2_text, user_sid, layer, meta_sid)
VALUES
	({$rel_sid},{$pair_sid},"{$s1_ids}", "{$s2_ids}", "{$s1_text}", "{$s2_text}",
	"{$k1_ids}", "{$k2_ids}", "{$k1_text}", "{$k2_text}", "{$user->user_sid}", {$layer_id}, {$meta_id->parent}) 
EOF;

db_query($sql);

?>
<script lang='javascript'>document.location='<?=$layer_config->url?>?pair_sid=<?=$pair_sid?>&layer_id=<?=$layer_id?>';</script>
<?
	killPage();
?>
