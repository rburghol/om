<?php


function om_arrayLookup($src_array, $search_key, $lookup_method, $defval, $debug=0) {
   // takes an array, and a key to search for, and performs a lookup, with flexible
   // key matching (exact match, interpolation, or stair-step) and value tranformation (if stair-step)
   switch ($lookup_method) {
      case 0:
      # exact match lookup table
      if (in_array($search_key, array_keys($src_array))) {
         $luval = $src_array[$search_key];
      } else {
         $luval = $defval;
      }
      break;

      case 1:
      # interpolated lookup table
      $lukeys = array_keys($src_array);
      if ($debug) {
         error_log("Trying to interpolate key $search_key in set " . print_r($lukeys,1));
      }
      $luval = $defval;
      for ($i=0; $i < (count($lukeys) - 1); $i++) {
         $lokey = $lukeys[$i];
         $hikey = $lukeys[$i+1];
         $loval = $src_array[$lokey];
         $hival = $src_array[$hikey];
         $minkey = min(array($lokey,$hikey));
         $maxkey = max(array($lokey,$hikey));
         if ($debug) {
            error_log("Is ($minkey <= $search_key) and ($maxkey >= $search_key) ?? ");
         }
         if ( ($minkey <= $search_key) and ($maxkey >= $search_key) ) {
            if (is_array($loval)) {
               // we have an array, so we have to interpolate each member of the hi and low value arrays
               $luval = array();
               foreach ($loval as $key => $value) {
                  $hv = $hival[$key];
                  $lv = $value;
                  $intval = om_interpValue($search_key, $lokey, $lv, $hikey, $hv);
                  $luval[$key] = $intval;
               }
            } else {
               if ($debug) {
                  error_log("Interpolating: om_interpValue($search_key, $lokey, $loval, $hikey, $hival) ");
               }
               $luval = om_interpValue($search_key, $lokey, $loval, $hikey, $hival);
            }
         }
      }
      break;

      case 2:
      # stair-step lookup table
      $lukeys = array_keys($src_array);
      if ($debug) {
         error_log("Stair Step Lookup requested for key $search_key in set " . print_r($lukeys,1));
      }
      $luval = $defval;
      $lastkey = 'N/A';
      for ($i=0; $i <= (count($lukeys) - 1); $i++) {
        $lokey = $lukeys[$i];
        $loval = $src_array[$lokey];
        if ($debug) {
           error_log("Comparing $lokey <= $search_key");
        }
        if ( ((float)$lokey <= $search_key) ) {
          $luval = $loval;
          $lastkey = $lokey;
          if ($debug) {
             error_log("match, setting  luval = $loval ");
          }
        }
      }
      break;

      case 3:
      # interpolated lookup table, but rather than return the value, returns the interpolated key
      // useful for return period type calcs
      $lukeys = array_keys($src_array);
      $luval = $defval;
      for ($i=0; $i < (count($lukeys) - 1); $i++) {
         $lokey = $lukeys[$i];
         $hikey = $lukeys[$i+1];
         $loval = $src_array[$lokey];
         $hival = $src_array[$hikey];
         $minkey = min(array($loval,$hival));
         $maxkey = max(array($loval,$hival));
         //error_log("Type 3 Lookup: Row $i : $search_key, $loval, $lokey, $hival, $hikey");
         if (!is_array($loval) and !is_array($hival)) {
            if ( ($minkey <= $search_key) and ($maxkey >= $search_key) ) {
               $luval = om_interpValue($search_key, $loval, $lokey, $hival, $hikey);
            }
         }
      }
      break;

      default:
      # exact match lookup table
      if (in_array($search_key, array_keys($src_array))) {
         $luval = $src_array[$search_key];
      } else {
         $luval = $defval;
      }
      break;

   }
   
   if ($debug) {
      error_log("Returning $luval ");
   }
   return $luval;
   
}

function om_interpValue($thistime, $ts, $tv, $nts, $ntv, $intmethod=1) {

   switch ($intmethod) {
      default:
         //error_log(" $tv + ($ntv - $tv) * ( ($thistime - $ts) / ($nts - $ts) ) ");
         $retval = $tv + ($ntv - $tv) * ( ($thistime - $ts) / ($nts - $ts) );
      break;

   }
   return $retval;
}
?>