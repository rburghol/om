<?php


   #******************************************************************
   # LOCAL VARIABLES
   #******************************************************************

   # the spreadid values that correspond to different distribution schemes
   # used in creating the output tables for
   $spread_manure = '1, 6, 10, 12, 5, 14';
   $spread_fert = '7, 8, 11';

   # virtual waste storage bin
   # this is enumerated in the table major_lutype table, and linked into the landuses table
   # we use this to indicate the portion of waste eligible for manure transport
   $vwaste_storage_lutype = '7';

   # default limiting nutrient for nutrient management
   $def_nm_planbase = 13;
   # non-NM nitrogen rate
   $defnrate = 1.3;
   $defprate = 1.1;
   $defmaxnrate = 1.8;
   $defmaxprate = 3.0;
   $defopttarg = 5; # high yield, excluding legume
   $defmaxtarg = 6; # high yield including legume

   # land use types for land use type queries
   # water landusetype id
   $watertype = 12;
   # ag land uses
   $ag_lutypes = '2,9,13,14';
   # nutrient management land use types
   $nm_lutypes = '9,13';
   # non-nutrient management land use types
   $nonnm_lutypes = '2,14';
   # crop land use types
   $crop_lutypes = '2,9';
   # pasture land use types - land where animals are grazing
   $pas_lutypes = '13,14';

   # source classes corresponding to fertilizer, manure, etc.
   # fertilizer source class
   $fertsourceclasses = '8';
   # manure type source classes - includes biosolids and wildlife
   $manure_sclass = '2, 5, 6, 11';
   # anthropogenic waste type source classes - includes biosolids but NO wildlife, no septic
   $anthro_waste = '2, 5, 6, 11';
   $manure_sclass_nobio = '2, 6, 11';
   # septics
   $septicid = 10;
   # pollutanttypes to output in septic files
   $septicpolls = '1';

   # legume nutrient ID
   $legume_nut = "7, 1";
   $legume_rate = 1.0; # this tells what rate above crop uptake legumes fix N - this is based on the max removal
                       # so, if max yield is 25% more than average yield, a legume_rate = 1.0 would fix 25% more N
                       # than the plant removed.
   $tracerpoll = 12;

   # NULLVAL - the value to print out if a value is null
   $nullval = -9;
   #$nullval = 0.0;

   # SLUETH - land use projection values
   $minresist = 5;
   $maxresist = 95;

   # IDs for residue and canopy factors in crop_curves tables
   $res_canopy = '5,6';

   # maximum cover values for sediment model (pc L. Linker)
   $maxc = 0.95;

   # miscellanious hacks
   $hayadjust = 1.0; # 1.0 assumes that hay is taken care of in inputs (true for scenario >= base 11)
   # scales the hay rate, until I finish the
   #crop factor adjusting routine more completely
   $urbanfert = array(1982=>4.6, 1987=>4.3 , 1992=>5.7, 1997=>6.7, 2000=>7.2, 2002=>7.7);
   $hackurban = 0; # enable to manually set urban fertilizer
   $urbanno = 0.2;
   $urbannh = 0.8;

   # column names in inputyields table (this is a long list, best to centralize it)
   $yieldcols = 'maxn,maxp,total_acres,legume_n,uptake_n,uptake_p,total_n,total_p,';
   $yieldcols .= 'nrate,prate,optn,optp,maxnrate,maxprate';
   $yieldcols .= ',mean_uptn,mean_uptp,n_urratio,p_urratio,mean_needn,mean_needp,';
   $yieldcols .= 'dc_pct,n_fix,high_uptp,';
   $yieldcols .= 'high_uptn,high_needp,high_needn,targ_needn,targ_needp,';
   $yieldcols .= 'targ_uptp,targ_uptn,nm_planbase,optyieldtarget,maxyieldtarget';

   # File stuff
   # max file size for upload
   $maxfilesize = 10000000;

   #******************************************************************
   # END LOCAL VARIABLES
   #******************************************************************


?>