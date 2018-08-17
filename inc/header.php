<?php

#
# This sounds redicously bad in its current view, though this will get pretty complicated
#
$ER_PAGING_VARS = '';
$ER_PAGING_VARS_AR_POINTER = 0;


function openHTML ()
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php
}

function openHEAD ($title = 'WARP-Text')
{
?>
  <head>
    <title><?=$title?></title>
    <link rel='stylesheet' type='text/css' href='/<?=PREFIX?>get/css/main.css' />
    <link rel='stylesheet' type='text/css' href='/<?=PREFIX?>get/css/shailan-dropdown.css' />
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<!-- /Dropdown Menu Widget Styles -->

<style type="text/css">
body {font-family:Tahoma, Geneva, sans serif;}
h1 { font-family:Tahoma, Geneva, sans serif;}
h2 { font-family:Tahoma, Geneva, sans serif;}
h3 { font-family:Tahoma, Geneva, sans serif;}
h4 { font-family:Tahoma, Geneva, sans serif;}
p  {  font-family:Tahoma, Geneva, sans serif;}
h2 a, h3 a{color:#555;}
td,th { text-align: left; }
</style>


<!-- Dropdown Menu Widget Styles by shailan (http://shailan.com) v1.7.2 on wp3.2.1 -->

<style type="text/css" media="all">
  ul.dropdown { white-space: nowrap;  }  
  /** Show submenus */
  ul.dropdown li:hover > ul, ul.dropdown li.hover ul{ display: block; }
  /** Show current submenu */
  ul.dropdown li.hover ul, ul.dropdown ul li.hover ul, ul.dropdown ul ul li.hover ul, ul.dropdown ul ul ul li.hover ul, ul.dropdown ul ul ul ul li.hover ul , ul.dropdown li:hover ul, ul.dropdown ul li:hover ul, ul.dropdown ul ul li:hover ul, ul.dropdown ul ul ul li:hover ul, ul.dropdown ul ul ul ul li:hover ul { display: block; } 
</style>
<!-- /Dropdown Menu Widget Styles -->
<?
}

function closeHEAD ()
{
?>
</head>
<?php
}

function openBODY ($menuItem=0)
{
	global $user;
?>
<body>
<div id="wrapper">
  <div id="header">
  </div>
  <div id="content">
    <div id="primary-menu" class="menu-main-container"><ul id="menu-main" class='' style='width: 100%'>
<?
	# Check if we have any user
	if ($user->sid!=0) {
		# Check if it's the admin panel
		if ($user->admin == 1) {
?>
      <li id="menu-item-11" class="<?=checkMenuItem($menuItem,11);?>menu-item menu-item-type-post_type menu-item-object-page" style='z-index:1000;'><a href="/data_conf/list">Dataset</a></li>
      <li id="menu-item-12" class="<?=checkMenuItem($menuItem,12);?>menu-item menu-item-type-post_type menu-item-object-page" style='z-index:1000;'><a href="/user_conf/list">Users</a></li>
      <li id="menu-item-13" class="<?=checkMenuItem($menuItem,13);?>menu-item menu-item-type-post_type menu-item-object-page" style='z-index:1000;'><a href="/layer_conf/list">Layers</a></li>

      <li id="menu-item-09" class="<?=checkMenuItem($menuItem,9);?>menu-item menu-item-type-post_type menu-item-object-page" style='float: right'><a href="/logout">Logout</a></li>

<?
		# If it isn't, it's the anno panel
		} else {
?>


      <li id="menu-item-02" class="<?=checkMenuItem($menuItem,1);?>menu-item menu-item-type-post_type menu-item-object-page" style='z-index:1000;'><a href="/anno/details">Home</a></li>
      <li id="menu-item-09" class="<?=checkMenuItem($menuItem,9);?>menu-item menu-item-type-post_type menu-item-object-page" style='float: right'><a href="/logout">Logout</a></li>
<?
		}
	# IF the user sid is 0, we are at the login page, no menu
	} else {
?>
      <li id="menu-item-10" class="<?=checkMenuItem($menuItem,10);?>menu-item menu-item-type-post_type menu-item-object-page"><a href="/login">login</a></li>

<?
	}
?>
    </ul></div>
    <div class="shadow_980"></div>
      <div id="article-header">
<?php
}

function closeBODY ()
{
?>
</div>
	</div>

	<div class="automargin">
		<div id="footer">
		</div>
	</div>

</div>
<?
	if (defined('ER_USED_PAGING')) {
		global $ER_PAGING_VARS;
?>
	<script type="text/javascript">
	paging=new Array();
<?=$ER_PAGING_VARS?>
	
	function updateVar(varname,value)
	{
		for (i=0; i<paging.length; i++) {
			if (paging[i].split('=')[0]==varname) {
				paging[i] = varname+'='+value;
				break;
			}
		}
	}
	
	function formatVars()
	{
		url = location.protocol+'//'+location.host+location.pathname+'?';
		for (i=0; i<paging.length; i++) {
			url += paging[i];
			if (i!=paging.length-1)
				url+='&';
		}
			location.href=url;
	}
	
	</script>
	
<?
	}
?>
  </body>
<?php
}

function closeHTML ()
{
?>
</html>
<?php
}

function killPage()
{
	closeBODY();
	closeHTML();
	exit;
}

function openForm($action, $method = 'POST', $name = 'form', $id = 'form')
{
?>
<form action='<?=$action?>' method='<?=$method?>' name='<?=$name?>' id='<?=$id?>'>
<?
}

function closeForm($add_submit = TRUE)
{
	if ($add_submit) {
?>
  <input type='submit' style='visibility: hidden;' />
<?
	}
?>
</form>
<?
}
$pagesz=17;
function curPageURL() {

	$pageURL='http';
	$request_URI=requestUri();
	if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") $pageURL.="s";
	$pageURL.="://";
	if($_SERVER["SERVER_PORT"] != "80") $pageURL.=$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$request_URI;
	else $pageURL.=$_SERVER["SERVER_NAME"].$request_URI;
	return $pageURL;
}
function requestUri() {

	$uri = '';
	if(isset($_SERVER['REQUEST_URI'])) $uri=$_SERVER['REQUEST_URI'];
	else
	{
		if(isset($_SERVER['argv'])) $uri=$_SERVER['SCRIPT_NAME'].(isset($_SERVER['argv'][0])?'?'.$_SERVER['argv'][0]:'');
		elseif(isset($_SERVER['QUERY_STRING'])) $uri=$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
		else $uri=$_SERVER['SCRIPT_NAME'];
	}
	$uri='/'.ltrim($uri,'/');
	return $uri;
}

function checkMenuItem($menuItem, $pageMenuItem){
	return $menuItem === $pageMenuItem ? 'current-menu-item ' : '';
}
function prepareSearchInput($input,&$qclause)
{
	global $error_msg,$user;

	if (!preg_match($input->preg,$input->val)) {
	$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Bad search.";
	}
	switch($input->itype) {
	default:
		case 'like':
		$qclause = $qclause ." AND {$input->dbFld} like " . "'%" . $input->val . "%'";
		break;
		case 'begin';
		$qclause = $qclause ." AND {$input->dbFld} like " . "'" . $input->val . "%'";
		break;
		case 'end';
		$qclause = $qclause ." AND {$input->dbFld} like " . "'%" . $input->val . "'";
		break;
		case 'exact';
		$qclause = $qclause ." AND {$input->dbFld} like " . "'" . $input->val . "'";
		break;
	}
}
function newItem($start, $prev)
{
    if ($start == $prev)
    {
	$result = $start;
    }
    else
    {
	$result = $start . ' - ' . $prev;
    }

    return $result;
}

function getRanges($q_result){

	$newarr = array();

	while($pair = db_fetch_object($q_result)) {
		if ($prev) {
			if ($pair->pair_sid != $prev + 1) {
				$newarr[] = newItem($start, $prev);
				$start = $pair->pair_sid;
			}
		} else {
			$start = $pair->pair_sid;
		}
		$prev = $pair->pair_sid;
	}
	$newarr[] = newItem($start, $prev);

	return ($newarr);
}

function extract_type ($out_file,$condition)
{
	$condition = db_escape_string($condition);

	$sql = <<<EOF
SELECT
	pair_sid,
	a.rel_sid as rel_sid,
	type_name,
	s1_scope,
	s2_scope,
	s1_text,
	s2_text,
	key_s1,
	key_s2,
	k1_text,
	k2_text,
	user_sid
FROM
	annotation as a
LEFT JOIN
	relations as r
ON
	a.rel_sid=r.rel_sid
WHERE
	{$condition}
ORDER BY
	pair_sid
EOF;

	$data = db_query($sql);
	# Create the XML document
	$domtree = new DOMDocument('1.0', 'UTF-8');
	$domtree->formatOutput = true;

	# Create and append the xml root
	$xmlRoot = $domtree->createElement("xml");
	$xmlRoot = $domtree->appendChild($xmlRoot);


	# Loop through all entries
	while ($row = db_fetch_object($data)) {

		foreach ($row as $var => $val) {
			$row->$var = trim($row->$var);
			$row->$var = htmlspecialchars($val,ENT_COMPAT,'UTF-8');
		}

		# Create and append new type
		$new_type = $domtree->createElement('relation');
		$new_type = $xmlRoot->appendChild($new_type);

		# Add the properties of the type
		$new_type->appendChild($domtree->createElement('pair_sid',$row->pair_sid));
		$new_type->appendChild($domtree->createElement('rel_sid',$row->rel_sid));
		$new_type->appendChild($domtree->createElement('type_name',$row->type_name));
		$new_type->appendChild($domtree->createElement('s1_scope',$row->s1_scope));
		$new_type->appendChild($domtree->createElement('s2_scope',$row->s2_scope));
		$new_type->appendChild($domtree->createElement('s1_text',$row->s1_text));
		$new_type->appendChild($domtree->createElement('s2_text',$row->s2_text));
		$new_type->appendChild($domtree->createElement('key_s1',$row->key_s1));
		$new_type->appendChild($domtree->createElement('key_s2',$row->key_s2));
		$new_type->appendChild($domtree->createElement('k1_text',$row->k1_text));
		$new_type->appendChild($domtree->createElement('k2_text',$row->k2_text));
		$new_type->appendChild($domtree->createElement('annotator',$row->user_sid));

	}

	# Output the XML
	$domtree->save($out_file);

}

function extract_pairs ($out_file)
{

	$sql = <<<EOF
SELECT
	pair_sid as pair,
	text1_id as s1,
	text2_id as s2,
	description_1 as desc1,
	description_2 as desc2,
	text_1 as text1,
	text_2 as text2,
	label as label
FROM
	dataset
ORDER BY
	pair_sid
EOF;

	$data = db_query($sql);
	# Create the XML document
	$domtree = new DOMDocument('1.0', 'UTF-8');
	$domtree->formatOutput = true;

	# Create and append the xml root
	$xmlRoot = $domtree->createElement("xml");
	$xmlRoot = $domtree->appendChild($xmlRoot);


	# Loop through all entries
	while ($row = db_fetch_object($data)) {

		foreach ($row as $var => $val) {
			$row->$var = htmlspecialchars($val,ENT_COMPAT,'UTF-8');
		}

		# Create and append new type
		$new_type = $domtree->createElement('text_pair');
		$new_type = $xmlRoot->appendChild($new_type);

		# Add the properties of the type
		$new_type->appendChild($domtree->createElement('pair_sid',$row->pair));
		$new_type->appendChild($domtree->createElement('text1_id',$row->s1));
		$new_type->appendChild($domtree->createElement('text2_id',$row->s2));
		$new_type->appendChild($domtree->createElement('text1',$row->text1));
		$new_type->appendChild($domtree->createElement('text2',$row->text2));
		$new_type->appendChild($domtree->createElement('desc_1',$row->desc1));
		$new_type->appendChild($domtree->createElement('desc_2',$row->desc2));
		$new_type->appendChild($domtree->createElement('label',$row->label));
	}

	# Output the XML
	$domtree->save($out_file);

}

function extract_relations ($out_file)
{

	$sql = <<<EOF
SELECT
	r.rel_sid as rel_sid,
	r.type_name as type_name,
	rp.type_name as meta_type
FROM
	relations as r
LEFT JOIN
	relations as rp
ON
	r.parent = rp.rel_sid
WHERE
	rp.type_name is not null
ORDER BY
	r.rel_sid
EOF;

	$data = db_query($sql);
	# Create the XML document
	$domtree = new DOMDocument('1.0', 'UTF-8');
	$domtree->formatOutput = true;

	# Create and append the xml root
	$xmlRoot = $domtree->createElement("xml");
	$xmlRoot = $domtree->appendChild($xmlRoot);


	# Loop through all entries
	while ($row = db_fetch_object($data)) {

		foreach ($row as $var => $val) {
			$row->$var = htmlspecialchars($val,ENT_COMPAT,'UTF-8');
		}

		# Create and append new type
		$new_type = $domtree->createElement('relation');
		$new_type = $xmlRoot->appendChild($new_type);

		# Add the properties of the type
		$new_type->appendChild($domtree->createElement('rel_sid',$row->rel_sid));
		$new_type->appendChild($domtree->createElement('type_name',$row->type_name));
		$new_type->appendChild($domtree->createElement('type_category',$row->meta_type));

	}
	# Output the XML
	$domtree->save($out_file);

}


?>



