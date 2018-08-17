<?
openHTML();
openHEAD('WARP Text');
closeHEAD();
openBODY(11);

# Admin panel. Check access.
if ($user->admin != "1") {
	header('Location: '.$_COOKIE['warp_default_index']);
	exit;
}

if (isset($mod->vars->i_confirm)) {

        $error_msg = '';
        $other_msg = '';

	# Check if file is uploaded
	if ( isset($_FILES["csv"])) {

		# Undefined | Multiple Files | $_FILES Corruption Attack
		# If this request falls under any of them, treat it invalid.
		if (
			!isset($_FILES['csv']['error']) ||
			is_array($_FILES['csv']['error'])
		) {
			$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid parameters.";
		} else {
	
			# Check $_FILES['csv']['error'] value.
			switch ($_FILES['csv']['error']) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."No file sent.";
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Exceeded filesize limit.";
				default:
					$error_msg = $error_msg.(($error_msg!='')?'<br/>':'').'Unknown errors.';
			}
		
			# You should also check filesize here.
			if ($_FILES['csv']['size'] > 1000000) {
				$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Exceeded filesize limit.";
			}
	
	
			#if everything is ok, proceed
			if ($error_msg == '') {
				$tmpName = $_FILES['csv']['tmp_name'];
				$csvAsArray = array_map(function($v){return "'" . implode("','",array_map('db_escape_string',str_getcsv($v, "\t"))) ."'";}, file($tmpName));
		
				# Insert the csv in the database
				# N.B.: the system expects 7 values - label, sent1_id, sent2_id, text_1, text_2, description_1, description_2
				$values = "(" . implode("), (", $csvAsArray) . ")";
				$sql = "INSERT INTO dataset (label,text1_id,text2_id,text_1,text_2,description_1,description_2) VALUES {$values};";
				db_query($sql);
		
				echo "<div class='green'>Import Successful</div><br />";
				echo "<script lang='javascript'>setTimeout(\"document.location='/data_conf/details'\",$user->refresh);</script>";
			}
		}

	 } else {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."No file sent";
	 }
}

if (isset($error_msg)&&strlen($error_msg)) {
        echo "<div class='error'>{$error_msg}</div><br />";
}

?>
<table class="details">

<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data" name="form" id="form">
<input type='hidden' name='i_confirm' id='i_confirm' value='1' />
<tr>
<th colspan='3'>Import a tab delimited csv file with your raw dataset. The file should have 7 columns.</th>
</tr>
<tr class='blank_row'><td></td></tr>
<tr>
<th colspan='3'>
#1 Label (0 if not applicable);</br>
#2 Text 1 ID (for backtracking source);</br>
#3 Text 2 ID (for backtracking source);</br>
#4 Text 1 (If you need custom units, they should be separated using a special string (ex.: EndOfString));</br>
#5 Text 2 (If you need custom units, they should be separated using a special string (ex.: EndOfString));</br>
#6 Description of Text 1 (Human readable description, displayed at certain parts of the interface);</br>
#7 Description of Text 2 (Human readable description, displayed at certain parts of the interface);</br>
</th>
</tr>
<tr class='blank_row'><td></td></tr>
<tr>
<th width='35%'><label for="i_upload">Select csv file</label></th>
<td class='pagingth'><input type="file" name="csv" id="csv" /></td>
<td class='pagingth'><a href="#" onclick="document.forms['form'].submit();return false;" class="btn-medium common-button"><span>Import</span></a></td>
<!--<td><input type="submit" name="submit" /></td>
--!>
</table>

<?

closeBODY();
closeHTML();
?>

