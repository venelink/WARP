<?
openHTML();
openHEAD();
closeHEAD();
openBODY(11);

# Admin panel. Check access.
if ($user->admin != "1") {
	header('Location: '.$_COOKIE['warp_default_index']);
	exit;
}

?>
<table class="details">
<?
# Print the number of pairs in the dataset
$sql = <<<EOF
SELECT
	count(pair_sid) as num_pairs
FROM
	dataset
EOF;
$obj = db_query($sql);
$data = db_fetch_object($obj);

echo "<tr><th class='pagingth' width='15%'>Corpus size (number of pairs)</th>";
echo "<td class='pagingtd0' width='10%'>" . $data->num_pairs . "</td></tr>";

# Print the number of non-admin users (annotators)
$sql = <<<EOF
SELECT
	count(username) as num_users
FROM
	warp_user
WHERE
	disabled = FALSE and
	admin = FALSE
EOF;
$obj = db_query($sql);
$data = db_fetch_object($obj);

echo "<tr><th class='pagingth' width='15%'>Number of annotators</th>";
echo "<td class='pagingtd0' width='10%'>" . $data->num_users . "</td></tr>";

echo"<tr class='blank_row'><td></td></tr>";

# Print number of pairs assigned
$sql = <<<EOF
SELECT
	count(distinct(pair_sid)) as uniq_pairs,
	count(pair_sid) as num_pairs
FROM
	pair_split as ps
LEFT JOIN
	warp_user as gu
ON
	ps.user_sid = gu.user_sid
WHERE
	NOT gu.disabled AND
	NOT gu.admin
EOF;
$obj = db_query($sql);
$data = db_fetch_object($obj);

echo "<tr><th class='pagingth' width='15%'>Assigned pairs</th>";
echo "<td class='pagingtd0' width='10%'>" . $data->num_pairs . " assignments (" . $data->uniq_pairs . " pairs)</td></tr>";

# Print number of unassigned pairs
$sql = <<<EOF
SELECT
	count(distinct(d.pair_sid)) as num_pairs
FROM
	dataset as d
LEFT JOIN
	pair_split as ps
ON
	d.pair_sid = ps.pair_sid
WHERE
	ps.split_sid is null
EOF;
$obj = db_query($sql);
$data = db_fetch_object($obj);

echo "<tr><th class='pagingth' width='15%'>Unassigned pairs</th>";
echo "<td class='pagingtd0' width='10%'>" . $data->num_pairs . "</td></tr>";

echo"<tr class='blank_row'><td></td></tr>";

# Print number of annotated pairs
$sql = <<<EOF
SELECT
	count(pair_sid) as num_ass,
	count(distinct(pair_sid)) as num_pairs
FROM
	pair_split as ps
LEFT JOIN
	warp_user as gu
ON
	ps.user_sid = gu.user_sid
WHERE
	ps.annotated = 1 AND
	NOT gu.disabled AND
	NOT gu.admin
EOF;
$obj = db_query($sql);
$data = db_fetch_object($obj);

echo "<tr><th class='pagingth' width='15%'>Annotated pairs</th>";
echo "<td class='pagingtd0' width='10%'>" . $data->num_ass . " completed assignments (" . $data->num_pairs ." pairs)</td></tr>";

# Print the number of not-annotated pairs
$sql = <<<EOF
SELECT
	count(pair_sid) as num_ass,
	count(distinct(pair_sid)) as num_pairs
FROM
	pair_split as ps
LEFT JOIN
	warp_user as gu
ON
	ps.user_sid = gu.user_sid
WHERE
	ps.annotated = 0 AND
	NOT gu.disabled AND
	NOT gu.admin
EOF;
$obj = db_query($sql);
$data = db_fetch_object($obj);

echo "<tr><th class='pagingth' width='15%'>Non-annotated pairs</th>";
echo "<td class='pagingtd0' width='10%'>" . $data->num_ass . " incomplete assignments (" . $data->num_pairs ." pairs)</td></tr>";
?>
<div class="btn-holder"><a href="#" onclick="document.location='/data_conf/assign';return false;" class="btn-medium multi-button" style='float: right;'>
  <span style='width: 150px;'>Pair assignment</span>
</a></div>
<div class="btn-holder"><a href="#" onclick="document.location='/data_conf/anno_stats';return false;" class="btn-medium multi-button" style='float: right;'>
  <span style='width: 150px;'>Annotation stats</span>
</a></div>
<div class="btn-holder"><a href="#" onclick="document.location='/data_conf/import';return false;" class="btn-medium multi-button" style='float: right;'>
  <span style='width: 150px;'>Import corpus</span>
</a></div>
<div class="btn-holder"><a href="#" onclick="document.location='/data_conf/export';return false;" class="btn-medium multi-button" style='float: right;'>
  <span style='width: 150px;'>Export annotation</span>
</a></div>
</table>

<?

closeBODY();
closeHTML();
?>

