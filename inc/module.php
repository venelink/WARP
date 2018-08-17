<?php
$module = $_SERVER["REQUEST_URI"];

if (preg_match('/\?/', $module)) {
	$module = preg_split('/\?/', $module);
	$module = $module[0];
}
if ($module[0]=='/')
	$module = substr($module, 1, strlen($module));

if ($module[strlen($module)-1]=='/')
	$module = substr($module, 0, strlen($module)-1);

if (!defined('ER_DB'))
	die ('E_NO_DB');

if (!defined('ER_AUTH'))
	die ('E_NO_AUTH');

$e_module = db_escape_string($module);
$sql = <<<EOF_SQL
SELECT
	warp_module.module_index AS mod_index,
	warp_module.name AS name,
	warp_module.module_sid AS sid
FROM
	warp_module
LEFT JOIN
	warp_module_alias
ON
	warp_module_alias.module_sid = warp_module.module_sid
WHERE
	warp_module_alias.alias = '{$e_module}' OR
	warp_module.module_index = '{$e_module}'
LIMIT 1;
EOF_SQL;
$f = db_query($sql);
if ((!db_count_rows($f)||$user->sid==0) && $module!='login/reset.php') {
	$mod->name = 'Login';
	$mod->mod_index = 'login/index.php';
	$mod->sid = 2;
} elseif ($module=='login/reset.php') {
	$mod->name = 'Reset password';
	$mod->mod_index = 'login/reset.php';
	$mod->sid = 2;
} else {
	$mod = db_fetch_object($f);
}
	$mod->paramcount = 1;
	$mod->params[0]->var = '*';
	$mod->params[0]->allow = TRUE;
	$mod->params[0]->regex = '/.*/';

	foreach ($_REQUEST AS $var => $val) {
		$i = 0;
		$valid = false;
	
		while ($i<$mod->paramcount) {
			$a_r = $mod->params[$i]->allow;

			# matching for allow
			if (	($mod->params[$i]->var == '*')||
				($mod->params[$i]->var == $var)||
				(($mod->params[$i]->var[0]=='/')&&(preg_match($mod->params[$i]->var, $var)))) {

				# current var name is matched

				if (!isset($mod->params[$i]->regex)||(!strlen($mod->params[$i]->regex))) {
					# no regex, mark as allowed
					$valid = $a_r;
				} else {
					if (!is_array($val)) {
						if (preg_match($mod->params[$i]->regex, $val)) {
							# try to match the val of current var agains the regex
							$valid = $a_r;
						}
					} else {
						if (!isset($mod->vars->$var))
							$mod->vars->$var = Array();

						foreach ($val AS $n => $v) {
							if (is_array($v))
								continue;
							
							$vf = is_numeric($n)?"[$n]$v":"['$n']$v";
							
							if (preg_match($mod->params[$i]->regex, $vf)) {
								if ($a_r) {
									$mod->vars->{$var}[$n] = $v;
								} else {
									if (isset($mod->vars->{$var}[$n])) 
										unset($mod->vars->{$var}[$n]);
								}
							}
						}
					}
				}
			}
			
			$i++;
		}
		
		if (!is_array($val)&&$valid)
			$mod->vars->$var = $val;
	}
/*
}
*/
unset($_REQUEST);

$mod->self = '/'.PREFIX.$mod->mod_index;


require_once('lib/'.$mod->mod_index);


?>
