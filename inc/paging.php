<?php

define('NAV_ROW_LIMITS',0);
define('NAV_PAGE_NUMBERS',1);
class Paging {
	private $name;
	private $source;
	private $data;
	private $clause;
	private $columns;
	private $col_num;
	private $col_data;
	private $col_order;
	private $col_deforder;
	private $asc;
	private $css_class;
	private $page;
	private $limit;
	private $suppress_limit;
	private $extraUrlParams;

	private $row_filter;
	private $cell_filter;
	
	private $sql;
	private $total_sql;
	private $obj;
	private $raw;
	private $p_css;
	private $qNav;
	private $navType;
	private $actionsColPos;

	function __construct ($name = null, $auto = false)
	{
		if (is_null($name))
			$name = 'tmp'.rand(10000000,99999999);
		$this->name = $name;
		$this->p_css = null;
		$this->row_filter = array();
		$this->cell_filter = array();

		global $mod;
		
		/* Modifications below are related to security vulnerability of the ordering code */
		if ($auto&&isset($mod->vars->$name)) {
			$this->page = (int)$mod->vars->{$name}['page'];
			$this->limit = (int)$mod->vars->{$name}['limit'];
			$this->col_deforder = $mod->vars->{$name}['col_deforder'];
			$this->asc = ($mod->vars->{$name}['asc']=='ASC')?'ASC':'DESC';
		} else {
			$this->limit = 50;
			$this->page = 0;
		}
		$this->extraUrlParams = array();
		$this->suppress_limit = false;
		$this->qNav->left = '';
		$this->qNav->right = '';
		$this->navType = NAV_ROW_LIMITS;
		$this->actionsColPos = false;
	}
	
	private function generate_td($row)
	{
		if (is_null($this->p_css))
			$this->p_css = 1;

		$this->p_css = ($this->p_css+1)%2;
		
		foreach ($this->row_filter AS $filter) {
			if ($this->check($row->{$filter['member']}, $filter['op'], $filter['val']))
				return $filter['result'];
		}
		
		return "class='pagingtd{$this->p_css}'";
	}
	
	private function check($var, $op, $val)
	{
		switch ($op) {
			case '==':
				return ($var==$val);
				break;
			case '>=':
				return ($var>=$val);
				break;
			case '<=':
				return ($var<=$val);
				break;
			case '>':
				return ($var>$val);
				break;
			case '<':
				return ($var<$val);
				break;
			case 'regex':
				return (preg_match($val, $var));
				break;
			case 'function':
			case 'func';
				if (is_array($val)) {
					$f = $val[0];
					$val[0] = $var;
					return call_user_func_array($f, $val);
				} else
					return call_user_func($val, $var);
				break;
			default:
				return ($var==$val);
				break;
		}
	}
	
	function setSQL($sql)
	{
		$this->sql = $sql;
	}
	
	function getSQL()
	{
		return $this->sql;
	}
	
	function makeSQL()
	{
		$this->total_sql .= "SELECT\n\tcount(*) AS count \nFROM \n\t{$this->source}\n";
		if (strlen($this->clause))
			$this->total_sql .= "WHERE\n\t{$this->clause}\n";

		$this->sql ="\n";
		$this->sql .= "SELECT\n\t{$this->data}\n";
		$this->sql .= "FROM \n\t{$this->source}\n";
		
		if (strlen($this->clause))
			$this->sql .= "WHERE\n\t{$this->clause}\n";
		
		/* Modifications below are related to security vulnerability of the ordering code */
		if (!in_array($this->col_deforder, $this->col_order)) {
			die("PAGINATION MODULE :: INTERNAL ERROR Ex" . __LINE__);
		}
		
		if (strlen($this->col_deforder)) {
			$this->sql .= "ORDER BY {$this->col_deforder} {$this->asc}\n";
		}

		if(!$this->suppress_limit) {
			$this->sql .= "LIMIT {$this->limit} OFFSET ".($this->page*$this->limit);
		}
	}
	
	function setSource($src)
	{
		$this->source = $src;
	}
	
	function setData($data)
	{
		$this->data = $data;
	}
	
	function setClause($clause)
	{
		$this->clause = $clause;
	}
	
	function setColumns()
	{
		if (!func_num_args())
			return;

		$this->columns = func_get_args();
		$this->col_num = func_num_args();
	}
	
	//call this function last one of the Columns functions (after the data, order, etc)!!!
	function setActionsColPos($pos) 
	{
		if(preg_match('/^[1-9][0-9]*$/',$pos) && $this->col_num >= $pos)
		$this->actionsColPos = $pos;
	}
	
	function setColumnsData()
	{
		if (func_num_args()!=$this->col_num)
			return;

		$this->col_data = func_get_args();
	}
	
	function setColumnsOrder()
	{
		if (func_num_args()!=$this->col_num)
			return;

		$this->col_order = func_get_args();
	}
	
	function setDefaultOrder($v)
	{
		$this->col_deforder = $v;
	}
	
	function setASC($asc)
	{
		if (preg_match('/^desc$/i', $asc))
			$this->asc = 'DESC';
		else
			$this->asc = 'ASC';
	}
	
	function setCSSClass($class)
	{
		$this->css_class = $class;
	}

	function setPage($n)
	{
		$this->page = $n;
	}
	
	function setLimit($l)
	{
		$this->limit = $l;
	}
	
	function suppressLimit()
	{
		$this->suppress_limit = true;
	}
	
	function addRowFilter($member, $op, $value, $result)
	{
		$this->row_filter[] = array('member' => $member, 'op' => $op, 'val' =>$value, 'result' => $result);
	}

	function addCellFilter($column, $member, $op, $value, $result)
	{
		if (is_int($column))
			$this->cell_filter[] = array('column' => $column, 'member' => $member, 'op' => $op, 'val' =>$value, 'result' => $result);
	}
	
	//extra params array added to allow correct fields sorting if there are more than the 4 default params used 
	function setExtraUrlParams($extra)
	{
		if(is_array($extra) && count($extra) > 0)
			$this->extraUrlParams = $extra;
	}
	
	function setNavType($type) {
		if($type === NAV_ROW_LIMITS || $type === NAV_PAGE_NUMBERS)
			$this->navType = $type;
	}
	function setQuickNav(){
		if($this->page-5 > 0) {	
			$this->qNav->left .= "<a href='#' onclick='updateVar(\"{$this->name}[page]\",\"0\");formatVars();'>&nbsp;|&lt;&nbsp;</a>";
		}	
		if($this->page-6 > 0) {	
			$tarPg = $this->page - 6;
			$this->qNav->left .= "<a href='#' onclick='updateVar(\"{$this->name}[page]\",\"{$tarPg}\");formatVars();'>&nbsp;&lt;&lt;&nbsp;</a>";
		}
		if($this->page+5<($this->obj_num_rows/$this->limit)-1) {
			$tarPg = $this->page + 5;
			$this->qNav->right .= "<a href='#' onclick='updateVar(\"{$this->name}[page]\",\"{$tarPg}\");formatVars();'>&nbsp;&gt;&gt;&nbsp;</a>";
		}
		if($this->page+5<$this->obj_num_rows/$this->limit) {
			$tarPg = $this->obj_num_rows/$this->limit;
			if($tarPg == round($tarPg)) $tarPg -= 1;
			$this->qNav->right .= "<a href='#' onclick='updateVar(\"{$this->name}[page]\",\"{$tarPg}\");formatVars();'>&nbsp;&gt;|&nbsp;</a>";
		}
		
	}
	
	function setMainNav(){
		for (
				$i=(($this->page-5>0)?$this->page-5:0);
				$i<(($this->page+5<$this->obj_num_rows/$this->limit)?($this->page+5):$this->obj_num_rows/$this->limit);
				$i++) {
			if($this->navType === NAV_ROW_LIMITS) 
				$navLbl = $i*$this->limit.'-'.((($i+1)*$this->limit<$this->obj_num_rows)?($i+1)*$this->limit:$this->obj_num_rows);
			elseif($this->navType === NAV_PAGE_NUMBERS)
				$navLbl = $i+1;
			if ($this->page!=$i)
				echo "[<a href='#' onclick='updateVar(\"{$this->name}[page]\",\"{$i}\");formatVars();'>".$navLbl.'</a>] ';
			else
				echo '['.$navLbl.'] ';
		}
	}

	function exec()
	{
		if (!strlen($this->sql))
			$this->makeSQL();

		$this->obj = db_query($this->sql);
		$this->obj_num_rows = db_fetch_object(db_query($this->total_sql))->count;

		foreach ($this->col_data AS $i=>$col) {
			$this->raw[$i] = array();
			preg_match_all('/\{([A-Za-z0-9_]+)\}/', $col, $this->raw[$i]);
			$this->raw[$i] = $this->raw[$i][1];
		}
		
	}
	
	function output()
	{
		echo "<table>";
		echo "<tr>";
		for ($i=0; $i<count($this->columns);$i++) {
			if (is_null($this->col_order[$i]))
				echo "<th class='pagingth'>{$this->columns[$i]}</th>";
			else
				echo "<th class='pagingth'><a href='#' onclick='" .
					"updateVar(\"{$this->name}[col_deforder]\",\"{$this->col_order[$i]}\");" .
					"updateVar(\"{$this->name}[asc]\", \"" .
					(($this->col_order[$i]!=$this->col_deforder)?'ASC':(($this->asc=='ASC')?'DESC':'ASC')) . "\");" .
					"formatVars();'>{$this->columns[$i]}</a></th>";
		}
		echo "</tr>";
		
		while ($o = db_fetch_object($this->obj)) {
			$td_a = $this->generate_td($o);
			echo "<tr>";
			foreach ($this->col_data AS $i=>$col) {
				foreach($this->raw[$i] AS $lobj) {
					$col = preg_replace("/\\{{$lobj}\\}/", $o->$lobj, $col);
				}
				
				$c_td_a = $td_a;
				foreach ($this->cell_filter AS $filter) {
					
					if (($i+1)==$filter['column']&&$this->check($o->{$filter['member']}, $filter['op'], $filter['val'])) {
						$c_td_a = $filter['result'];
						break;
					}
				}
				
				if($this->actionsColPos && $this->actionsColPos == $i + 1) 
					$c_td_a = str_replace("class='","class='btns-area ",$c_td_a);	
				echo "<td {$c_td_a}>{$col}</td>";
			}
			echo "</tr>";
		}
		
		echo "<tr><td colspan='{$this->col_num}' style='text-align: center;'>";
		$this->setNavType(NAV_PAGE_NUMBERS);
		$this->setQuickNav();	
		echo $this->qNav->left;	
		$this->setMainNav();	
		echo $this->qNav->right;
		echo "<br />(total {$this->obj_num_rows})</td></tr>";
		
		echo "</table>";
		
		if (!defined('ER_USED_PAGING'))
			define('ER_USED_PAGING', true);
		
		global $ER_PAGING_VARS;
		global $ER_PAGING_VARS_AR_POINTER;
		$ER_PAGING_VARS .= "paging[".$ER_PAGING_VARS_AR_POINTER++."]='{$this->name}[limit]={$this->limit}';\n";
		$ER_PAGING_VARS .= "paging[".$ER_PAGING_VARS_AR_POINTER++."]='{$this->name}[page]={$this->page}';\n";
		$ER_PAGING_VARS .= "paging[".$ER_PAGING_VARS_AR_POINTER++."]='{$this->name}[col_deforder]={$this->col_deforder}';\n";
		$ER_PAGING_VARS .= "paging[".$ER_PAGING_VARS_AR_POINTER++."]='{$this->name}[asc]={$this->asc}';\n";
		foreach($this->extraUrlParams as $extra_param)
			$ER_PAGING_VARS .= "paging[".$ER_PAGING_VARS_AR_POINTER++."]='".$extra_param['param']."={$extra_param['value']}';\n";
	}
}

?>
