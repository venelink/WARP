<?
openHTML();
openHEAD('WARP-Text');
closeHEAD();
openBODY(11);

# Admin panel. Check access.
if ($user->admin != "1") {
        header('Location: '.$_COOKIE['warp_default_index']);
        exit;
}

# Check for posted data
if (isset($mod->vars->i_confirm)) {
	# data verification
	$error_msg = '';
	$other_msg = '';

	
	if ($mod->vars->i_dataset != 'true' && $mod->vars->i_dataset != 'false') {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid dataset value.";
	}

	if ($mod->vars->i_relations != 'true' && $mod->vars->i_relations != 'false') {
		$error_msg = $error_msg.(($error_msg!='')?'<br/>':'')."Invalid relations value.";
	}

	# If there were no errors, proceed with export
	if ($error_msg == '') {
		$other_msg = $other_msg.(($other_msg!='')?'<br/>':'')."Checks_passed.";

		# Create empty tmp file
		$out_fname = sys_get_temp_dir() . '/warp_export.zip';
		# Object to generate the zip
		$exp_file = new ZipArchive;
		if ($exp_file->open($out_fname, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) !== TRUE) {
			die ("An error occurred creating your ZIP file.");
		}

		# Export the dataset if requested
		if ($mod->vars->i_dataset == "true") {
			$pairsName = tempnam(sys_get_temp_dir(), 'pairs');
			extract_pairs($pairsName);
			$exp_file->addFile($pairsName,"pairs.xml");
		}

		# Export the relations if requested
		if ($mod->vars->i_relations == "true") {
			$relationsName = tempnam(sys_get_temp_dir(), 'relations');
			extract_relations($relationsName);
			if (file_exists($relationsName)) {
				$exp_file->addFile($relationsName,"relations.xml") or die ("ERROR: Could not add file");
			} else {
				die("Error creating archive.");
			}
		}

		# Export the annotations
		$annoName = tempnam(sys_get_temp_dir(), 'annotations');
		extract_type($annoName,"pair_sid>0");
		$exp_file->addFile($annoName,"annotations.xml");
		$exp_file->close();

		# Set headers
		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename=warp_export.zip');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($out_fname));

		ob_clean();
		flush();

		readfile($out_fname);

		unlink($out_fname);

	}
}
if (isset($error_msg)&&strlen($error_msg)) {
	echo "<div class='error'>{$error_msg}</div><br />";
}

openForm($mod->self, 'POST', 'form', 'form');
?>
  <table class='non-exist' border='0' width='100%'>
<input type='hidden' name='i_confirm' id='i_confirm' value='1' />
    <tr>
      <th colspan='2'>The export file is a zip archive that contains the following files:</br>
      - annotations.xml - all current annotations done by the annotators </br>
      - pairs.xml (if export pairs is set) - the raw dataset, converted to xml </br>
      - relations.xml (if export relations is set) - all relations, their parents and IDs </br>
      </th>
    </tr>
    <tr>
      <th width='35%' style='text-align: right;'><label for="i_dataset">Export pairs</label></th>
      <td>
        <input type='radio' id='i_dataset' name='i_dataset' value='true' class='radio' <?=
		((!isset($mod->vars->i_dataset))||$mod->vars->i_dataset=='true')?'checked':''?>/>
	<label for='i_dataset'>Yes</label>
        <input type='radio' id='i_non_dataset' name='i_dataset' value='false' class='radio' <?=
		((isset($mod->vars->i_dataset))&&$mod->vars->i_dataset=='false')?'checked':''?>/>
        <label for='i_non_dataset'>No</label>
      </td>
    </tr>
    <tr>
      <th width='35%' style='text-align: right;'><label for="i_admin">Export relations</label></th>
      <td>
        <input type='radio' id='i_relations' name='i_relations' value='true' class='radio' <?=
		((!isset($mod->vars->i_relations))||$mod->vars->i_relations=='true')?'checked':''?>/>
	<label for='i_relations'>Yes</label>
        <input type='radio' id='i_non_relations' name='i_relations' value='false' class='radio' <?=
		((isset($mod->vars->i_relations))&&$mod->vars->i_relations=='false')?'checked':''?>/>
        <label for='i_non_relations'>No</label>
      </td>
    </tr>
    <tr>
      <td colspan='3'><a href="#" onclick="document.forms['form'].submit();return false;" class="btn-medium common-button"><span>Export</span></a></td>
    </tr>
  </table>
<?

closeForm();
closeBODY();
closeHTML();
?>
