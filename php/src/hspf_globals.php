<html>

<body>

<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="Post">
<b>HSPF Global Details:</b>

<?php

include('config.php');

$inpath = $indir;
$outpath = $outdir;
$fileext = '.uci';

# set up db connection
$listobject = new pgsql_QueryObject;
$listobject->dbconn = $dbconn;
$debug = 0;

if (isset($_GET["projectid"])) {
   $projectid = $_GET["projectid"];
} else {
   $projectid = -1;
}

if (isset($HTTP_POST_VARS['projectid'])) {
   $projectid = $HTTP_POST_VARS['projectid'];
   updateHSPFGlobals($listobject,$_POST);

   print("Updated Project<br>");

}


$listobject->querystring = "select * from hspf_globals where projectid = $projectid";
if ($debug) { print("$listobject->querystring<br>"); }
$listobject->performQuery();

$ucifile = $listobject->getRecordValue(1,'ucifile');
$startdate = $listobject->getRecordValue(1,'startdate');
$enddate = $listobject->getRecordValue(1,'enddate');
$timestep = $listobject->getRecordValue(1,'timestep');
$wdm1 = $listobject->getRecordValue(1,'wdm1');
$wdm2 = $listobject->getRecordValue(1,'wdm2');
$wdm3 = $listobject->getRecordValue(1,'wdm3');
$wdm4 = $listobject->getRecordValue(1,'wdm4');
$precip_wdm_id = $listobject->getRecordValue(1,'precip_wdm_id');
$evap_wdm_id = $listobject->getRecordValue(1,'evap_wdm_id');

$uzsn_mo = $listobject->getRecordValue(1,'uzsn_mo');
$lzetp_mo = $listobject->getRecordValue(1,'lzetp_mo');
$cepsc_mo = $listobject->getRecordValue(1,'cepsc_mo');
$nsur_mo = $listobject->getRecordValue(1,'nsur_mo');

$depwater = $listobject->getRecordValue(1,'depwater');
$fcreach = $listobject->getRecordValue(1,'fcreach');

$usethiessen = $listobject->getRecordValue(1,'usethiessen');

$copyreaches = $listobject->getRecordValue(1,'copyreaches');
$copysubsheds = $listobject->getRecordValue(1,'copysubsheds');

# enable general quality constituent?
$usegqual = $listobject->getRecordValue(1,'usegqual');
$consqual = $listobject->getRecordValue(1,'consqual');
$impwashoff = $listobject->getRecordValue(1,'impwashoff');

# enable run tracking
$trackruns = $listobject->getRecordValue(1,'trackruns');

$useftablefile = $listobject->getRecordValue(1,'useftablefile');
$ftablefile = $listobject->getRecordValue(1,'ftablefile');
$usehydromonfile = $listobject->getRecordValue(1,'usehydromonfile');
$hydromonfile = $listobject->getRecordValue(1,'hydromonfile');
$calcioqcsqolim = $listobject->getRecordValue(1,'calcioqcsqolim');
$monioqc = $listobject->getRecordValue(1,'monioqc');
$zerodate = $listobject->getRecordValue(1,'zerodate');

$if = $listobject->getRecordValue(1,'if');
$ro = $listobject->getRecordValue(1,'ro');
$allowagwetp = $listobject->getRecordValue(1,'allowagwetp');

#print("$uzsn_mo, $lzetp_mo, $cepsc_mo<br>");

$listobject->querystring = "select a.luid,b.hspf_lu,a.pct_impervious from landuses as a, map_hspf_lu as b where a.projectid = $projectid and b.projectid = $projectid and a.luid = b.project_luid";
$listobject->performQuery();
$implus = $listobject->queryrecords;

$outfile = "$outpath/$ucifile";


$uciobject = new HSPF_UCIobject;
$uciobject->ucidir = $indir;
$uciobject->uciname = $runname;
$uciobject->listobject = $listobject;
$uciobject->ucitables = $ucitables;
#$uciobject->init();
$uciobject->debug = 0;
# masslinks loaded in config file
$uciobject->masslinks = $masslinks;


?>

<br><b>Name of UCI:</b> <input name=ucifile type=text value='<?php echo $ucifile;?>'>
<br><b>Start Date:</b><input name=startdate type=text value='<?php echo $startdate;?>'>
<br><b>End Date:</b><input name=enddate type=text value='<?php echo $enddate;?>'>
<br><b>Zero Date (for conversion to Julian Date in CE-QUAL-W2):</b><input name=zerodate type=text value='<?php echo $zerodate;?>'>
<br><b>Time Step (decimal, in hours, E: 1.0 = 1 hour):</b><input name=timestep type=text value='<?php echo $timestep;?>'>
<br><b>Name of WDM1 (Meteorological Inputs):</b><input name=wdm1 type=text value='<?php echo $wdm1;?>'>
<br><b>WDM ID of default precip source:</b><input name=precip_wdm_id type=text value='<?php echo $precip_wdm_id;?>'>
<?php
   print("<br><b>Use Thiessen Polygons for precip:</b>");
   showTFListType('usethiessen',$usethiessen,1);
?>
<br><b>WDM ID of default evaporation source:</b><input name=evap_wdm_id type=text value='<?php echo $evap_wdm_id;?>'>
<br><b>Name of WDM2 (Flow Inflows/Outflows):</b><input name=wdm2 type=text value='<?php echo $wdm2;?>'>
<br><b>Name of WDM3 (Quality Inputs):</b><input name=wdm3 type=text value='<?php echo $wdm3;?>'>
<br><b>Name of WDM4 (Misc.):</b><input name=wdm4 type=text value='<?php echo $wdm4;?>'>
<br><b>List of reaches to route to a copy block:</b><input name=copyreaches type=text value='<?php echo $copyreaches;?>'>
<br><b>List of subsheds to route to a copy block:</b><input name=copysubsheds type=text value='<?php echo $copysubsheds;?>'>
<br><br><b>Obtain FTABLES from a file?: </b><?php showTFList('useftablefile',$useftablefile); ?>
<br><b>FTABLE file name (located in /in directory, with extension .ftab):</b><?php fileSelectedForm('ftablefile',$inpath,'ftab',$ftablefile) ?>
<br>
<br><br><b>Obtain Hydrology MON Blocks from a file?: </b><?php showTFListType('usehydromonfile',$usehydromonfile, 1); ?>
<br><b>FTABLE file name (located in /in directory, with extension .mon):</b><?php fileSelectedForm('hydromonfile',$inpath,'mon',$hydromonfile) ?>
<?php

print("<br><b>Auto-number uci names:</b>");
showTFListType('trackruns',$trackruns,1);

print("<br><b>Allow AGWETP on non-wetland landuses?:</b>");
showTFListType('allowagwetp',$allowagwetp,1);

print("<br><b>Enable Monthly UZSN:</b>");
showTFList('uzsn_mo',$uzsn_mo);
print("<br><b>Enable Monthly LZETP:</b>");
showTFList('lzetp_mo',$lzetp_mo);
print("<br><b>Enable Monthly CEPSC:</b>");
showTFList('cepsc_mo',$cepsc_mo);
print("<br><b>Enable Monthly Manning's:</b>");
showTFList('nsur_mo',$nsur_mo);
print("<br><b>Enable General Quality Constituent Modeling:</b>");
showTFList('usegqual',$usegqual);
print("<br><b>Model Quality Constituent as Conservative?:</b>");
showTFListType('consqual',$consqual,1);
print("<br><b>Calculate IOQC based on SQO (loading rate)?:</b>");
showTFListType('calcioqcsqolim',$calcioqcsqolim,1);
print("<br><b>Use monthly IOQC distribution?:</b>");
showTFListType('monioqc',$monioqc,1);
print("<br><b>Enable Deposition on Water PERLNDs:</b>");
showTFListType('depwater',$depwater,1);
print("<br><b>Use Deposition Factor for Reach FC Input (yes during calibration):</b>");
showTFListType('fcreach',$fcreach,1);
print("<br><b>Washoff factor for impervious land uses:</b>");
showTextField('impwashoff',$impwashoff);

showHiddenField('projectid',$projectid);
showHiddenField('updateproject',1);

print("<br>");

$i = 0;
foreach ($implus as $thislu) {
   $imppct = $thislu['pct_impervious'];
   $luid = $thislu['luid'];
   $hspf_lu = $thislu['hspf_lu'];
   print("<br><b>$hspf_lu </b>Impervious Fraction: <input name=imppct[$luid] type=text value='$imppct'>");
}

?>

<br><br><b>Route Interflow to Reaches (should always be true, unless testing): </b><?php showTFList('if',$if); ?>
<br><b>Route runoff to Reaches (should always be true, unless testing): </b><?php showTFList('ro',$ro); ?>

<br>
<input type="submit" name="submit" value="submit"></form>

</form>

</body>
</html>