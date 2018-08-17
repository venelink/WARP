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

$mod->vars->l1 = (int) $mod->vars->l1;
$mod->vars->l2 = (int) $mod->vars->l2;

$sql = <<<EOF
SELECT
	layer_sid
FROM
	layers
WHERE
	layer_order = {$mod->vars->l1} OR
	layer_order = {$mod->vars->l2};
EOF;

if (db_count_rows($data = db_query($sql))!=2) {
	echo "<div class='error'>Invalid layer IDs</div><br />";
	killPage();
}

$route = db_fetch_object($data);

$sql = <<<EOF
UPDATE
	layers
SET
	layer_order=999
WHERE
	layer_order = {$mod->vars->l1};
EOF;

db_query($sql);


$sql = <<<EOF
UPDATE
	layers
SET
	layer_order={$mod->vars->l1}
WHERE
	layer_order = {$mod->vars->l2};
EOF;

db_query($sql);

$sql = <<<EOF
UPDATE
	layers
SET
	layer_order={$mod->vars->l2}
WHERE
	layer_order = 999;
EOF;

db_query($sql);


?>
<script lang='javascript'>document.location='/layer_conf/list';</script>
<?
	killPage();
?>
