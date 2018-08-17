<?
openHTML();
openHEAD();
closeHEAD();
openBODY(3);

?>
<table class="details">
<tr>
<th class='pagingth' colspan=2>Annotation statistics</th>
</tr>
<?

$sql = <<<EOF
SELECT
	gu.username as username,
	count(ps.split_sid) as tanno
FROM
	warp_user as gu
LEFT JOIN
	pair_split as ps
ON
	gu.user_sid = ps.user_sid
WHERE
	gu.user_sid = {$user->user_sid}
EOF;
$obj = db_query($sql);
$data = db_fetch_object($obj);

echo "<tr><th class='pagingth' width='15%'>User</th>";
echo "<td class='pagingtd0' width='10%'>".$data->username."</td></tr>";
echo "<tr><th class='pagingth' width='15%'>Total Pairs</th>";
echo "<td class='pagingtd0' width='10%'>".$data->tanno."</td></tr>";



$sql = <<<EOF
SELECT
        CASE WHEN annotated THEN "Annotated Pairs" ELSE "Remaining Pairs" END as anno,
        count(*) as count
FROM
        pair_split
WHERE
        user_sid = {$user->user_sid}
GROUP BY
        annotated;
EOF;
$obj = db_query($sql);

while ($data = db_fetch_object($obj)) {
        echo "<tr><th class='pagingth' width='15%'>".$data->anno."</th>";
        echo "<td class='pagingtd0' width='10%'>".$data->count."</td></tr>";
}

?>
<tr>
<th><div class="btn-holder"><a href="#" onclick="document.location='/anno/meta';return false;" class="btn-medium multi-button" style='float: right;'>
  <span style='width: 150px;'>Annotate Next</span>
</a></div></th>
<th>
<div class="btn-holder"><a href="#" onclick="document.location='/anno/list';return false;" class="btn-medium multi-button" style='float: right;'>
  <span style='width: 150px;'>Review Annotations</span>
</a></div></th>
</table>
<?

closeBODY();
closeHTML();
?>

