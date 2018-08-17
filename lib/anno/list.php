<?
openHTML();
openHEAD();
closeHEAD();
openBODY(3);

$search = '';
$error_msg = '';

# SELECT config

# Source table

# Layer 1

$dqclause = "user_sid = " . $user->user_sid . " and annotated=1";

if (isset($error_msg)&&strlen($error_msg)) {
        echo "<div class='error'>{$error_msg}</div><br />";
        echo "<script lang='javascript'>setTimeout(\"document.location='/anno/list'\",$user->refresh);</script>";
        }
        else {

$p = new Paging('perm', true);
$p->setData("d.pair_sid as p_id, d.description_1 as sent1, d.description_2 as sent2");
$p->setSource('pair_split as p left join dataset as d on p.pair_sid=d.pair_sid');
$p->setColumns("Pair ID", "Sentence 1","Sentence 2", "Actions");
$p->setColumnsData('{p_id}', '{sent1}', '{sent2}', "<a href='#' onclick=\"document.location='/anno/meta?pair_sid={p_id}&layer_id=0';return false;\" class='btn-small btn-edit'><span>Edit</span></a>\n");
$p->setColumnsOrder('p_id', 'sent1', 'sent2', NULL);
$p->setClause($dqclause);
if (!isset($mod->vars->perm)) {
        $p->setDefaultOrder('p_id');
        $p->setASC('asc');
        $p->setLimit($user->pagesize);
}

$p->setCSSClass('log');
?>
<div class="btn-holder"><a href="#" onclick="document.location='/anno/meta';return false;" class="btn-medium multi-button" style='float: right;'>
  <span style='width: 150px;'>Annotate next</span>
</a></div>
<?
$p->exec();
$p->output();
}
closeBODY();
closeHTML();
?>
