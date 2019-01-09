<?php


function getWithdrawalElementInfo($elementid) {
   global $listobject, $serverip, $baseurl;
   /*
   error_log("Calling quick retrievval for $elementid");
	$obtest = unSerializeParentObject($elementid);
	$thisobject = $obres['object'];
   error_log("Quick Object retrieved");
   */
error_log("Retrieving $elementid");
	$obres = unSerializeSingleModelObject($elementid);
error_log("Object retrieved");
	$thisobject = $obres['object'];
	$geninfo = getElementInfo($listobject, $elementid, $debug);

	$proplist = array();
	//$proplist = $geninfo;
	$proplist['waterusetype'] = $thisobject->waterusetype;
	$proplist['wdtype'] = $thisobject->wdtype;
	$proplist['lat_dd'] = $geninfo['lat_dd'];
	$proplist['lon_dd'] = $geninfo['lon_dd'];
	$proplist['elemname'] = htmlspecialchars($geninfo['elemname']);
   $proplist['historic_data_url'] = "http://$serverip/$baseurl/remote/get_modelData.php?elementid=$elementid&variables=historic_mgd&runid=2";
	$proplist['elementid'] = $data['elementid'];
	$mofrac = array('jan'=>0.0833,'feb'=>0.0833,'mar'=>0.0833,'apr'=>0.0833,'may'=>0.0833,'jun'=>0.0833,'jul'=>0.0833,'aug'=>0.0833,'sep'=>0.0833,'oct'=>0.0833,'nov'=>0.0833,'dec'=>0.0833);
	$current_mgy = 0.0;
	if (isset($thisobject->processors['historic_monthly_pct'])) {
	$mofrac = $thisobject->processors['historic_monthly_pct']->getProp('matrix', 'matrix_formatted');
	$mofrac = array('jan'=>$mofrac[1],'feb'=>$mofrac[2],'mar'=>$mofrac[3],'apr'=>$mofrac[4],'may'=>$mofrac[5],'jun'=>$mofrac[6],'jul'=>$mofrac[7],'aug'=>$mofrac[8],'sep'=>$mofrac[9],'oct'=>$mofrac[10],'nov'=>$mofrac[11],'dec'=>$mofrac[12]);
	}
	if (isset($thisobject->processors['current_mgy'])) {
	$current_mgy = $thisobject->processors['current_mgy']->getProp('current_mgy','equation');
	}
	if (isset($thisobject->processors['safe_yield_mgy'])) {
	$safe_yield_mgy = $thisobject->processors['safe_yield_mgy']->getProp('current_mgy','equation');
	} else {
	$safe_yield_mgy = $current_mgy;
	}
	$proplist = array_merge($proplist, $mofrac);
	$proplist['current_mgy'] = $current_mgy;
	$proplist['safe_yield_mgy'] = $safe_yield_mgy;
	return $proplist;
}


function getPointsourceElementInfo($elementid) {
   global $listobject, $serverip, $baseurl;
	$obres = unSerializeSingleModelObject($elementid);
	$thisobject = $obres['object'];
	$geninfo = getElementInfo($listobject, $elementid, $debug);

	$proplist = array();
	//$proplist = $geninfo;
   $parentid = getContainingNodeType($elementid, 0, array('custom1'=>array('cova_ws_container')));
	$proplist['modelnode_elid'] = $parentid;
   $parentinfo = getElementInfo($listobject, $parentid);
	$proplist['cbp_riversegment'] = $parentinfo['custom2'];
	$proplist['wdtype'] = 'pointsource';
	$proplist['elemname'] = $geninfo['elemname'];
	$proplist['lat_dd'] = $geninfo['lat_dd'];
	$proplist['lon_dd'] = $geninfo['lon_dd'];
	$proplist['elementid'] = $data['elementid'];
   // currently no historical time series for VPDES
   //$proplist['historic_data_url'] = "http://$serverip/$baseurl/remote/get_modelData.php?elementid=$elementid&variables=historic_mgd&runid=2";
	$mofrac = array('jan'=>0.0,'feb'=>0.0,'mar'=>0.0,'apr'=>0.0,'may'=>0.0,'jun'=>0.0,'jul'=>0.0,'aug'=>0.0,'sep'=>0.0,'oct'=>0.0,'nov'=>0.0,'dec'=>0.0);
	$current_mgy = 0.0;
	if (isset($thisobject->processors['current_monthly_discharge'])) {
	   $mofrac = $thisobject->processors['current_monthly_discharge']->getProp('matrix', 'matrix_formatted');
	   $mofrac = array('jan_ps_mgd'=>$mofrac[1],'feb_ps_mgd'=>$mofrac[2],'mar_ps_mgd'=>$mofrac[3],'apr_ps_mgd'=>$mofrac[4],'may_ps_mgd'=>$mofrac[5],'jun_ps_mgd'=>$mofrac[6],'jul_ps_mgd'=>$mofrac[7],'aug_ps_mgd'=>$mofrac[8],'sep_ps_mgd'=>$mofrac[9],'oct_ps_mgd'=>$mofrac[10],'nov_ps_mgd'=>$mofrac[11],'dec_ps_mgd'=>$mofrac[12]);
	}
	if (isset($thisobject->processors['current_mgy'])) {
	   $current_mgy = $thisobject->processors['current_mgy']->getProp('current_mgy','equation');
	}
	if (isset($thisobject->processors['max_discharge_mgy'])) {
	   $max_discharge_mgy = $thisobject->processors['max_discharge_mgy']->getProp('current_mgy','equation');
	} else {
	   $max_discharge_mgy = $current_mgy;
	}
	$proplist = array_merge($proplist, $mofrac);
	$proplist['current_mgy'] = $current_mgy;
	$proplist['max_discharge_mgy'] = $max_discharge_mgy;
	return $proplist;
}

?>