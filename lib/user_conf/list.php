<?
openHTML();
openHEAD('WARP-Text');
closeHEAD();
openBODY(12);

# Admin panel. Check access.
if ($user->admin != "1") {
        header('Location: '.$_COOKIE['warp_default_index']);
        exit;
}

$search = '';
$error_msg = '';


$dqclause = "username <> ''";

if (isset($error_msg)&&strlen($error_msg)) {
        echo "<div class='error'>{$error_msg}</div><br />";
	header('Location: '.$_COOKIE['warp_default_index']);
	exit;
        }
        else {

$p = new Paging('perm', true);
$p->setData("gu.user_sid as g_id, gu.username as username, case when gu.admin then 'yes' else 'no' end as admin, case when gu.disabled then 'yes' else 'no' end as disabled");
$p->setSource('warp_user as gu');
$p->setColumns("Username", "Admin","Disabled", "Actions");
$p->setColumnsData('{username}', '{admin}', '{disabled}', "<a href='#' onclick=\"document.location='/user_conf/edit?id={g_id}';return false;\" class='btn-small btn-edit'><span>Edit</span></a><a href='#' onclick=\"document.location='/user_conf/delete?id={g_id}';return false;\" class='btn-small btn-delete'><span>Delete</span></a>\n");
$p->setColumnsOrder('username', 'admin', 'disabled', NULL);
$p->setClause($dqclause);
if (!isset($mod->vars->perm)) {
        $p->setDefaultOrder('username');
        $p->setASC('asc');
        $p->setLimit($user->pagesize);
}

$p->setCSSClass('log');
?>
<div class="btn-holder"><a href="#" onclick="document.location='/user_conf/new';return false;" class="btn-medium multi-button" style='float: right;'>
  <span style='width: 150px;'>New user</span>
</a></div>
<?
$p->exec();
$p->output();
}
closeBODY();
closeHTML();
?>
