<?
openHTML();
openHEAD();
closeHEAD();
openBODY(3);

# This page removes an entry from the database
# Sanitize the variables
$anno_id = (int) $mod->vars->id;

$sql = <<<EOF
SELECT
	a.pair_sid as pair_sid,
	a.layer as layer,
	lt.url as url
FROM
	annotation as a
LEFT JOIN
	layers as l
ON
	a.layer = l.layer_sid
LEFT JOIN
	layer_types as lt
ON
	l.layer_type = lt.ltype_sid
WHERE
	anno_sid = {$mod->vars->id} AND
	user_sid = {$user->user_sid};
EOF;

var_dump($sql);
if (!db_count_rows($data = db_query($sql))) {
	echo "<div class='error'>Annotation not found</div><br />";
	killPage();
}

$obj = db_fetch_object($data);

$sql = <<<EOF
DELETE FROM
	annotation
WHERE
	anno_sid = {$mod->vars->id} AND
	user_sid = {$user->user_sid};
EOF;

db_query($sql);

?>
<script lang='javascript'>document.location='<?=$obj->url?>?pair_sid=<?=$obj->pair_sid?>&layer_id=<?=$obj->layer?>';</script>
<?
	killPage();
?>
