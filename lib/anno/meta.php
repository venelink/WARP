<?
openHTML();
openHEAD();
closeHEAD();
openBODY(3);

# File that handles the moving between layers in the annotation interface
# The input given through post parameters is the last known pair ID and 
# the last known layer order id
 
$error_msg = '';
$other_msg = '';

# Sanitize the parameters
$cur_pair = (isset($mod->vars->pair_sid) ? (int)$mod->vars->pair_sid : 0 );
$cur_layer = (isset($mod->vars->layer_id) ? (int)$mod->vars->layer_id : 0 );

# Check if we have pair_sid
# If not, pull the next pair_sid from the dataset
# This is the "next pair" button at the start of the annotation
if ($cur_pair == 0) {

	$sql = <<<EOF
SELECT
	pair_sid
FROM
	pair_split
WHERE
	annotated = 0 AND
	user_sid = {$user->user_sid}
ORDER BY
	pair_sid
LIMIT 1;
EOF;

	# If there are no more pairs, print it
	if (!db_count_rows($data = db_query($sql))) {
		$error_msg = $error_msg . " No more pairs to annotate!";
		echo "<div class='error'>".$error_msg."</div><br />";
		killpage();
	# Otherwise, pull the pair
	} else {
		$p_id = db_fetch_object($data);
		$cur_pair = $p_id->pair_sid;

	}
}


# The main loop of the annotation process
# Get the order ID of the current layer
# If this is edit, just pull the first layer
if ($cur_layer == 0) {
	$cur_layer_ord = 0;
} else {
	$sql = <<<EOF
SELECT
	layer_order
FROM
	layers
WHERE
	layer_sid = {$cur_layer};
EOF;
	$l_data = db_query($sql);
	$l_obj = db_fetch_object($l_data);
	$cur_layer_ord = $l_obj->layer_order;
}

# Check what is the next layer in the annotation process
$sql = <<<EOF
SELECT
	l.layer_sid,
	lt.url
FROM
	layers as l
LEFT JOIN
	layer_types as lt
ON
	l.layer_type = lt.ltype_sid
WHERE
	l.layer_order > {$cur_layer_ord} AND
	not l.disabled
ORDER BY
	layer_order
LIMIT 1;
EOF;

# Check if there are any more layers in the configuration
# If there are none, finish the current pair and go for the next
if (!db_count_rows($data = db_query($sql))) {

	# Update the current pair as annotated
	$sql = <<<EOF
UPDATE
	pair_split
SET
	annotated = TRUE
WHERE
	user_sid = {$user->user_sid} AND
	pair_sid = {$cur_pair};
EOF;
	db_query($sql);

	# Pull the next pair
	$sql = <<<EOF
SELECT
	pair_sid
FROM
	pair_split
WHERE
	annotated = 0 AND
	user_sid = {$user->user_sid}
ORDER BY
	pair_sid
LIMIT 1;
EOF;

	# If there are no more pairs, print it
	if (!db_count_rows($data = db_query($sql))) {
		$error_msg = $error_msg . " No more pairs to annotate!";
		echo "<div class='error'>".$error_msg."</div><br />";
		killpage();
	# Otherwise, pull the pair
	} else {
		$p_id = db_fetch_object($data);
		$cur_pair = $p_id->pair_sid;

	# Pull the first layer of the annotation
		$sql = <<<EOF
SELECT
	l.layer_sid,
	lt.url
FROM
	layers as l
LEFT JOIN
	layer_types as lt
ON
	l.layer_type = lt.ltype_sid
WHERE
	NOT l.disabled
ORDER BY
	l.layer_order
LIMIT 1;
EOF;
		# If there are no layers, then the system is not configured, print error
		if (!db_count_rows($data = db_query($sql))) {
			$error_msg = $error_msg . "No active layers found! Check your config!";
			echo "<div class='error'>".$error_msg."</div><br />";
			killpage();
		# Otherwise, pull the layer
		} else {
			$l_id = db_fetch_object($data);
			$cur_layer = $l_id->layer_sid;
			$next_page = $l_id->url;
		}
	}
# Otherwise, pull the layer
} else {
	$l_id = db_fetch_object($data);
	$cur_layer = $l_id->layer_sid;
	$next_page = $l_id->url;
}

echo "<div class='green'>OK</div><br />";
echo "<script lang='javascript'>setTimeout(\"document.location='{$next_page}?pair_sid={$cur_pair}&layer_id={$cur_layer}'\",$user->refresh);</script>";
closeBODY();
closeHTML();
exit;
?>
