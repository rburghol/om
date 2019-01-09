<?php

// ***************************** //
// *** Master XAJAX Controls *** //
// ***************************** //

function showMasterInfoForm($formValues) {
	$formHTML = '';
	$formValues = setMasterFormDefaults($formValues);
	$formname = $formValues['formname'];
	
	$formHTML .= "<form name='$formname' id='$formname'>";
	$formHTML .= showHiddenField('actiontype', $formValues['actiontype'], 1, 'actiontype');
	$formHTML .= showHiddenField('projectid', $formValues['projectid'], 1, 'projectid');
	$formHTML .= showHiddenField('scenarioid', $formValues['scenarioid'], 1, 'scenarioid');
	$formHTML .= showHiddenField('elementid', $formValues['elementid'], 1, 'elementid');
	$formHTML .= showHiddenField('viewmode', $formValues['viewmode'], 1, 'viewmode');
	$formHTML .= "</form>";

	return $formHTML;
}

function setMasterFormDefaults($formValues) {
   // set whatever you wish to be default variables here
	if (!isset($formValues['formname'])) {
		$formValues['formname'] = 'master_info_form';
	}
	if (!isset($formValues['actiontype'])) {
		$formValues['actiontype'] = '';
	}
	if (!isset($formValues['projectid'])) {
		$formValues['projectid'] = 3;
	}
	if (!isset($formValues['scenarioid'])) {
		$formValues['scenarioid'] = -1;
	}
	if (!isset($formValues['elementid'])) {
		$formValues['elementid'] = -1;
	} else {
	   // if the elementid is set, should we then retrieve the scenarioid based on this objects location/info?
	}
	   
	if (!isset($formValues['viewmode'])) {
		$formValues['viewmode'] = '';
	}
	
	return $formValues;
}
?>