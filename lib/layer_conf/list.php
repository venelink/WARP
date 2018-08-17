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

?>

<table class='non-exist' border='0' width='100%'>
  <tr>
    <th class='pagingth'>Order</th>
    <th class='pagingth'>Layer Name</th>
    <th class='pagingth'>Layer Type</th>
    <th class='pagingth'>Disabled</th>
    <th class='pagingth'>Reorder</th>
    <th class='pagingth'>Actions</th>
  </tr>
<?
$sql = <<<EOF
SELECT
	l.layer_sid as l_id, 
	l.layer_name as lname, 
	lt.type_name as ltype,
	l.layer_order as o_id,
	case when l.disabled then 'yes' else 'no' end as enabled
FROM
	layers as l 
LEFT JOIN
	layer_types as lt 
ON
	l.layer_type = lt.ltype_sid
ORDER BY
	layer_order
EOF;
	$data = db_query($sql);
	
	if (db_count_rows($data)) {
		$p_class = 0;
		while ($row = db_fetch_object($data)) {
?>
  <tr>
    <td class='pagingtd<?=$p_class?>'><?=$row->o_id?></td>
    <td class='pagingtd<?=$p_class?>'><?=$row->lname?></td>
    <td class='pagingtd<?=$p_class?>'><?=$row->ltype?></td>
    <td class='pagingtd<?=$p_class?>'><?=$row->enabled?></td>
    <td class='pagingtd<?=$p_class?>'>
      <?
	if ($row->o_id==1) {
		echo "<img src='/get/images/empty.png' />\n";
	} else {
?>
		<a href="#" onclick="document.location='/layer_conf/swap_layer?l1=<?=$row->o_id?>&l2=<?=$row->o_id-1?>';return false;"><img src='/get/images/up.png' /></a>
<?
	}
	if ($row->o_id==db_count_rows($data)) {
		echo "<img src='/get/images/empty.png' />\n";
	} else {
?>
		<a href="#" onclick="document.location='/layer_conf/swap_layer?l1=<?=$row->o_id?>&l2=<?=$row->o_id+1?>';return false;"><img src='/get/images/down.png' /></a>
<?
	}
      ?>
    </td>
    <td class='pagingtd<?=$p_class?>'>
	<a href="#" onclick="document.location='/layer_conf/configure?id=<?=$row->l_id?>';return false;" class='btn-small btn-view'><span>Config</span></a>
	<a href="#" onclick="document.location='/layer_conf/edit?id=<?=$row->l_id?>';return false;" class='btn-small btn-edit'><span>Edit</span></a>
	<a href="#" onclick="document.location='/layer_conf/delete?id=<?=$row->l_id?>';return false;" class='btn-small btn-delete'><span>Delete</span></a>
    </td>
  </tr>

<?
			$p_class = ($p_class+1)%2;
		}
	} else {
?>
  <tr>
    <td class='pagingtd0' colspan='6' style='text-align: center;'>No Layers Found</td>
  </tr>
<?
	}
?>
<div class="btn-holder"><a href="#" onclick="document.location='/layer_conf/new';return false;" class="btn-medium multi-button" style='float: right;'>
  <span style='width: 150px;'>New layer</span>
</a></div>
</table>

<?


closeBODY();
closeHTML();
?>
