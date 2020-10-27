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

function om_formatMatrix($matrix, $valuetype, $numrows, $defaultval = 0, $force_refresh=0, $debug = 0) {
  $refreshed = 0;

  if (!$refreshed or $force_refresh) {
     $matrix_formatted = array();
     $matrix_rowcol = array();
     $mindex = 0;
     $numcols = intval(count($matrix) / $numrows);
     if ($debug) {
        error_log("Formatting matrix, with " . count($matrix) . " cells, and $numrows rows ($numcols columns) ");
     }
     for ($i = 0; $i < $numrows; $i++) {
       $matrix_rowcol[$i] = array();
        for ($j = 0; $j < $numcols; $j++) {
           // old - did not consider if a value was a variable or not
           //$matrix_rowcol[$i][$j] = $matrix[$mindex];
           $matrix_rowcol[$i][$j] = trim($matrix[$mindex]);
           $mindex++;
        }
     }
     if ($debug) {
        error_log("Row-Col Matrix Assembled: " . print_r($matrix_rowcol,2) );
     }
     switch ($valuetype) {
        case 0:
           // keep in row-col format, so do no further transformation
           $matrix_formatted = $matrix_rowcol;
        break;
        
        case 1:
           // put it in a single dimensional key-value relationship, with the first column being the keys
           for ($i = 0; $i < $numrows; $i++) {
              $key = $matrix_rowcol[$i][0];
              
              if ($numcols == 1) {
                 $values = $defaultval;
              } else {
                 $values = array();
                 for ($j = 1; $j < $numcols; $j++) {
                    $values[$j-1] = $matrix_rowcol[$i][$j];
                 }
                 // make it scalar if there is only one entry
                 if (count($values) == 1) {
                    $values = $values[0];
                 }
              }
              $matrix_formatted[$key] = $values;
           }
        break;
        
        case 2:
           // put it in a multi-dimensional key-value relationship, with the first column being the keys
           // the first column first row, however, will be a throw-away, 
           for ($i = 1; $i < $numrows; $i++) {
           //for ($i = 1; $i < count($matrix_rowcol); $i++) {
              $key = $matrix_rowcol[$i][0];
              
              if ($numcols == 1) {
                 $values = $defaultval;
              } else {
                 $values = array();
                 for ($j = 1; $j < $numcols; $j++) {
                    // set the key to be the first (0th) row entry for this column, the value to be the 
                    // value of this column in the current row
                    $values[$matrix_rowcol[0][$j]] = $matrix_rowcol[$i][$j];
                 }
              }
              $matrix_formatted[$key] = $values;
           }
           $colnames = array_keys($matrix_formatted[$key]);
           $rownames = array_keys($matrix_formatted);
        break;
        
        case 3:
           // csv format
           // put it in a multi-dimensional key-value relationship, with the first row values as keys
           for ($i = 1; $i < count($matrix_rowcol); $i++) {
              $key = $matrix_rowcol[$i][0];
              
              if ($numcols == 1) {
                 $values = $defaultval;
              } else {
                 $values = array();
                 for ($j = 0; $j < $numcols; $j++) {
                    // set the key to be the first (0th) row entry for this column, the value to be the 
                    // value of this column in the current row
                    $values[$matrix_rowcol[0][$j]] = $matrix_rowcol[$i][$j];
                 }
              }
              $matrix_formatted[] = $values;
           }
        break;
     }
     $matrix_formatted = $matrix_formatted;
     $matrix_rowcol = $matrix_rowcol;
     return array($matrix_formatted, $matrix_rowcol);
  }
}


function om_formatCSVMatrix($matrix_rowcol, $valuetype, $defaultval = 0, $force_refresh=0, $debug = 0) {
  // already in csv format instead of janky om flat list format
  $refreshed = 0;
  $matrix_formatted = array();

  switch ($valuetype) {
    case 0:
       // keep in row-col format, so do no further transformation
       $matrix_formatted = $matrix_rowcol;
    break;
    
    case 1:
       // put it in a single dimensional key-value relationship, with the first column being the keys
       for ($i = 0; $i < $numrows; $i++) {
          $key = $matrix_rowcol[$i][0];
          
          if ($numcols == 1) {
             $values = $defaultval;
          } else {
             $values = array();
             for ($j = 1; $j < $numcols; $j++) {
                $values[$j-1] = $matrix_rowcol[$i][$j];
             }
             // make it scalar if there is only one entry
             if (count($values) == 1) {
                $values = $values[0];
             }
          }
          $matrix_formatted[$key] = $values;
       }
    break;
    
    case 2:
       // put it in a multi-dimensional key-value relationship, with the first column being the keys
       // the first column first row, however, will be a throw-away, 
       for ($i = 1; $i < $numrows; $i++) {
       //for ($i = 1; $i < count($matrix_rowcol); $i++) {
          $key = $matrix_rowcol[$i][0];
          
          if ($numcols == 1) {
             $values = $defaultval;
          } else {
             $values = array();
             for ($j = 1; $j < $numcols; $j++) {
                // set the key to be the first (0th) row entry for this column, the value to be the 
                // value of this column in the current row
                $values[$matrix_rowcol[0][$j]] = $matrix_rowcol[$i][$j];
             }
          }
          $matrix_formatted[$key] = $values;
       }
       $colnames = array_keys($matrix_formatted[$key]);
       $rownames = array_keys($matrix_formatted);
    break;
    
    case 3:
       // csv format
       // put it in a multi-dimensional key-value relationship, with the first row values as keys
       for ($i = 1; $i < count($matrix_rowcol); $i++) {
          $key = $matrix_rowcol[$i][0];
          
          if ($numcols == 1) {
             $values = $defaultval;
          } else {
             $values = array();
             for ($j = 0; $j < $numcols; $j++) {
                // set the key to be the first (0th) row entry for this column, the value to be the 
                // value of this column in the current row
                $values[$matrix_rowcol[0][$j]] = $matrix_rowcol[$i][$j];
             }
          }
          $matrix_formatted[] = $values;
       }
    break;
  }
  return $matrix_formatted;
}