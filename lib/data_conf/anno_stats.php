<?
openHTML();
openHEAD('WARP-Text');
closeHEAD();
openBODY(11);

# Admin panel. Check access.
if ($user->admin != "1") {
        header('Location: '.$_COOKIE['warp_default_index']);
        exit;
}


?>
<table class="details">
<tr>
<th class='pagingth' colspan='3'>Annotation statistics</th>
</tr>
<?

$sql = <<<EOF
SELECT
	gu.user_sid as user_sid,
	gu.username as username,
	count(ps.split_sid) as tanno
FROM
	warp_user as gu
LEFT JOIN
	pair_split as ps
ON
	gu.user_sid = ps.user_sid
WHERE
	NOT gu.admin AND
	NOT gu.disabled
GROUP BY
	gu.user_sid
EOF;
$obj = db_query($sql);
while ($data = db_fetch_object($obj)){

	echo"<tr class='blank_row'><td></td></tr>";
	echo "<tr><th class='pagingth' width='15%'>User</th>";
	echo "<td class='pagingtd0' colspan='2'>".$data->username."</td></tr>";
	echo "<tr><th class='pagingth' width='15%'>Total Pairs</th>";
	echo "<td class='pagingtd0' colspan='2'>".$data->tanno."</td></tr>";
	
	

	$sql2 = <<<EOF
SELECT
	CASE WHEN annotated THEN "Annotated Pairs" ELSE "Remaining Pairs" END as anno,
	pair_sid
FROM
	pair_split
WHERE
	user_sid = {$data->user_sid} AND
	annotated
EOF;
	$data2 = db_query($sql2);
	$count = db_count_rows($data2);
	$ranges = getRanges($data2);
	$own_ranges = implode(' ; ',$ranges);

	echo "<tr><th class='pagingth' width='15%'>Annotated pairs:</th>";
	echo "<td class='pagingtd0' colspan='2'>".$own_ranges." (".$count." pairs in total) </td></tr>";

	$sql2 = <<<EOF
SELECT
	pair_sid
FROM
	pair_split
WHERE
	user_sid = {$data->user_sid} AND
	NOT annotated
EOF;
	$data2 = db_query($sql2);
	$count = db_count_rows($data2);
	$ranges = getRanges($data2);
	$own_ranges = implode(' ; ',$ranges);

	echo "<tr><th class='pagingth' width='15%'>Left to annotate:</th>";
	echo "<td class='pagingtd0' colspan='2'>".$own_ranges." (".$count." pairs in total) </td></tr>";

}
?>
<tr>
<th>
<div class="btn-holder"><a href="#" onclick="document.location='/data_conf/assign';return false;" class="btn-medium multi-button" style='float: right;'>
  <span style='width: 150px;'>Pair assignment</span>
</a></div>
</th>
</tr>
</table>
<?

closeBODY();
closeHTML();

?>

