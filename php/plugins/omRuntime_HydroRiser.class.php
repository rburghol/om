<?php
class omRuntime_HydroRiser extends omRuntime_SubComponent {
  var $storage_stage_area = FALSE;
  var $riser_opening_elev = FALSE;
  var $riser_opening_storage = FALSE;
  var $riser_pipe_flow_head = FALSE;
  var $riser_flow = FALSE;
  var $riser_head = FALSE;
  var $riser_length = FALSE;
  var $riser_mode = 'weir'; # the flow mode currentlyt occuring - weir, pipe
  var $riser_diameter = FALSE;
  var $riser_emerg_storage = FALSE;
  var $riser_emerg_elev = FALSE;
  var $riser_emerg_head = FALSE;
  var $riser_emerg_diameter = FALSE;
  var $container = FALSE;
  var $tolerance = 0.0001;
  
  function __construct($options) {
    $this->storage_stage_area = isset($options['storage_stage_area']) ? $options['storage_stage_area'] : FALSE;
    if (!$this->storage_stage_area) {
      return FALSE;
    }
    $this->container = isset($options['container']) ? $options['container'] : FALSE;
    $this->riser_opening_storage = isset($options['riser_opening_storage']) ? $options['riser_opening_storage'] : 0.0;
    $this->riser_length = isset($options['riser_length']) ? $options['riser_length'] : 1.0;
    $this->riser_diameter = isset($options['riser_diameter']) ? $options['riser_diameter'] : 1.0;
    $this->riser_emerg_diameter = isset($options['riser_emerg_diameter']) ? $options['riser_emerg_diameter'] : 1.0;
    $this->riser_emerg_storage = isset($options['riser_emerg_storage']) ? $options['riser_emerg_storage'] : 1.0;
    $this->riser_pipe_flow_head = isset($options['riser_pipe_flow_head']) ? $options['riser_pipe_flow_head'] : 0.0;
    // must have the stage/storage/sarea dataMatrix for this to work
    $this->storage_stage_area->lutype2 = 0; // a fix since this settign gets lost?
    //$this->riser_opening_elev->debug = TRUE;
    $this->riser_opening_elev = $this->storage_stage_area->evaluateMatrix($this->riser_opening_storage,'stage'); // find storage at riser opening stage
    $this->riser_emerg_elev = $this->storage_stage_area->evaluateMatrix($this->riser_emerg_storage,'stage'); // find storage at riser opening stage
    /*
    //error_log("Riser properties: 
      length: $this->riser_length
      diam: $this->riser_diameter
      opening storage: $this->riser_opening_storage
      opening elev (calc): $this->riser_opening_elev
      pipe flow head: $this->riser_pipe_flow_head "
    );
    */
    //error_log("RISER($this->state[runid] : Evaluating Riser Opening Elevation = $this->riser_opening_elev at S = $this->riser_opening_storage ");
    if (is_object($this->container)) {
      $this->container->state['riser_mode'] = 'weir';
    }
  }
  
  function weir($head,$diameter){
    $coeff = 0.6 * pow(32.2,0.5);
    $riser_flow = $coeff * $diameter * pow($head,1.5);
    return $riser_flow;
  }
  function pipe($head,$diameter,$height) {
    $riser_flow = 0.6 * $height * $diameter*pow(2.0 * 32.2 * ($head - 0.5*$height), 0.5);
    return $riser_flow;
  }
  function discharge($stage) {
    $head = $stage - $this->riser_opening_elev;
    if($head <= 0) {
      $riser_flow = 0;
    } else if ( ($head > 0) and ($head < $this->riser_length) ){
        $riser_flow = $this->weir($head, $this->riser_diameter);
    } else if ( ($head > 0) and ($head >= $this->riser_length) ) {
      $riser_flow = $this->pipe($head, $this->riser_diameter, $this->riser_length);
    } else {
      $riser_flow = 0;
    }
    return $riser_flow;
  }
  function solver($sto) {
    $Stg = floatval($this->storage_stage_area->evaluateMatrix($sto,'stage'));
    $riser_flow = $this->discharge($Stg);
    return $riser_flow;
  }
  
  function evaluate() {
    // ********************************************
    // Get state from parent
    // ********************************************
    if ($this->container) {
      // we are a plugin so we access container directly
      $vars = $this->container->state;
    } else {
      // we are a standalone variable so we count on arData as our source
      $vars = $this->arData;
    }
    //$this->storage_stage_area->lutype2 = 0; // a fix - needed?
    // initialize
    $S0 = $vars['Storage'];
    $dt = floatval($vars['dt']);
    $Qprev = $vars['Qout'];
    $Qin = $vars['Qin'];
    $flowby = $vars['flowby'];
    $discharge = $vars['discharge'];
    $refill = $vars['refill'];
    $demand = $vars['demand'];
    $overflow = FALSE;
    // maintain backward compatibility with old ET nomenclature
    if (!($vars['et_in'] === NULL)) {
       $et_in = $vars['et_in'];
    } else {
       $et_in = $vars['pan_evap']; // assumed to be in inches/day
    }
    // maintain backward compatibility with old precip nomenclature
    if (!($vars['precip_in'] === NULL)) {
       $precip_in = $vars['precip_in']; // assumed to be in inches/day
    } else {
       $precip_in = $vars['precip']; // assumed to be in inches/day
    }
    $ET = $et_in * $dt / 12.0 / 86400;
    $P = $precip_in * $dt / 12.0 / 86400;
    $SA = floatval($this->storage_stage_area->evaluateMatrix($S0,'surface_area'));
    $ET_imp = $SA * $ET;
    $P_imp = $SA * $P; 
    // *****************************
    // * DEBUG INFO -- initial guess
    // *****************************
    $debug_detail_log = array();
    $debug_detail_log[] = "**1st Guess: S0= $S0, SA = $SA  ";
    $debug_detail_log[] = "**1st Guess: et_in = $et_in, precip_in = $precip_in ";
    $debug_detail_log[] = "**1st Guess: ET = $ET = $et_in * $dt * 12.0 / 86400 ";
    $debug_detail_log[] = "**1st Guess: P = $P = $precip_in * $dt * 12.0 / 86400 ";
    $debug_detail_log[] = "**1st Guess: P_imp = $P_imp : ET_imp = $ET_imp ";
    // ********************************************
    // Initial Estimate of Riser Head
    // Need storage guess to estimate riser head
    // this initial guess assumes 0 outflow through riser, so is max possible head to start
    // ********************************************
    $S1 = $S0 
      + (($Qin - $flowby) * $dt / 43560.0) 
      + (1.547 * $discharge * $dt / 43560.0) 
      + (1.547 * $refill * $dt / 43560.0) 
      - (1.547 * $demand * $dt /  43560.0) 
      - $ET_imp 
      + $P_imp
    ;
    if ($S1 > $this->container->maxcapacity) {
      $S1 = $this->container->maxcapacity;
      $riser_flow = $this->solver($S1);
      $S2 = $S0 
        + (($Qin - $flowby - $riser_flow) * $dt / 43560.0) 
        + (1.547 * $discharge * $dt / 43560.0) 
        + (1.547 * $refill * $dt / 43560.0) 
        - (1.547 * $demand * $dt /  43560.0) 
        - $ET_imp 
        + $P_imp
      ;
      if ($S2 >= $this->container->maxcapacity) {
        $overflow = TRUE;
      }
    }
    $riser_S1_stage = floatval($this->storage_stage_area->evaluateMatrix($S1,'stage'));
    $riser_flow = $this->solver($S1);
    $riserP = $riser_flow;
    // @todo: I don't think zero Si is a good guess, but to be consistent w/R code we go with it
    //$Si = $this->riser_opening_storage;
    $Si = 0.0;
    $Sn = $S1;//A storage iterator for within the loop
    $SA = floatval($this->storage_stage_area->evaluateMatrix($Sn,'surface_area'));
    $ET_imp = $SA * $ET;
    $P_imp = $SA * $P; 
    // set up a case specific tolerance
    //$tolerance = $this->tolerance * $Sn;
    $tolerance = $this->tolerance;
    $debug_detail_log[] = "Tolerance: $tolerance = $this->tolerance * $Sn";
    // *****************************
    // * DEBUG INFO -- continued
    // *****************************
    $debug_detail_log[] = "RISER($this->state[runid] : Riser Opening S = $this->riser_opening_storage (opening elev: $this->riser_opening_elev), Initial Guess S1 = $S1";
    $debug_detail_log[] = "RISER($this->state[runid] : riser_pipe_flow_head: $this->riser_pipe_flow_head ";
    $debug_detail_log[] = "riser_length = $this->riser_length : riser_diameter = $this->riser_diameter ";
    $debug_detail_log[] = "P_in = $P : ET_in = $ET ";
    $debug_detail_log[] = "S0 = $S0 : S1 = $S1 ";
    $debug_detail_log[] = "SA = $SA : Sn = $Sn ";
    $debug_detail_log[] = "ET_imp = SA * ET >>> $ET_imp = $SA * $ET "; 
    $debug_detail_log[] = "P_imp = SA * P >>>> $P_imp = $SA * $P "; 
    $debug_detail_log[] = "Max riser_flow = $riser_flow @ stage (S1) = $riser_S1_stage "; 
    $debug_detail_log[] = "S0 ($S0) + Qin ($Qin) * $dt / 43560.0) - ET ($ET_imp) + P($P_imp ) = $S1"; 
    
    $x = 0; //Need a loop counter
    // static parts of the diff term
    $diffStatic = (($Qin ) * $dt / 43560.0) 
      - ($flowby * $dt / 43560.0) 
      + (1.547 * $discharge * $dt / 43560.0) 
      + (1.547 * $refill * $dt / 43560.0) 
      - (1.547 * $demand * $dt /  43560.0) 
    ;
    $diff = abs($Sn - $S0 + $ET_imp - $P_imp + ($riser_flow*$dt/43560.0) - $diffStatic);
    if ($riser_flow > 0 and !$overflow) {
      while ($diff > $tolerance){
        $x += 1;
        if ($x == 50) {
          error_log("** WARNING: x = 50");
        }
        #Check the conditional statement in the while loop to break the loop before computation
        if ($x > 500) {
          $SA = floatval($this->storage_stage_area->evaluateMatrix($S0,'surface_area'));
          $ET_imp = $SA * $ET;
          $P_imp = $SA * $P; 
          $Sn = $S0-ET_imp+P_imp;
          $riser_flow = $Qin;
          break;
        }
        if ($diff > $tolerance){
          //If tolerance has not been achieved, use the bisection method to find S and Q
          $Sn = ($S1+$Si)/2.0; //New storage computed from the midpoint of max and min storage, S1 and Si respectively
          // catch going outside of the storage table
          if ($Sn > $this->container->maxcapacity) {
            $Sn = $this->container->maxcapacity;
            $debug_detail_log[] = "its $x, estimated Storage $Sn exceeds max_capacity $this->container->maxcapacity ";
          }
          $riser_flow = $this->solver($Sn); //Corresponding outflow
          $SA = floatval($this->storage_stage_area->evaluateMatrix($Sn,'surface_area'));
          $ET_imp = $SA * $ET;
          $P_imp = $SA * $P; 
          //error_log("$riser_flow = this->solver($Sn)");
          $debug_detail_log[] = "its $x, diff=$diff, S1 = $S1, Si = $Si; $riser_flow = this->solver($Sn) ";
          //Now that flow has been calculated, the bisection method can be continued. Need to shorten interval with guess Sn
          //Compute the MPM equation for S1 (maximum storage) and Sn (current iterator). If product is negative, they are of
          //opposite sign. Thus, a solution for S and Q are contained within this new interval, replace Si with Sn. Otherwise,
          //if they are of the same sign, assign Sn as S1 to serve as the new maximum storage value. Then replace riserP with 
          //the current riser_flow for future reference in solving the MPM for S1
          $SA1 = floatval($this->storage_stage_area->evaluateMatrix($S1,'surface_area'));
          // initial guess of precip/evap based on surface area
          $ET_imp1 = $SA1 * $ET;
          $P_imp1 = $SA1 * $P; 
          $debug_detail_log[] = "A, et, p: $SA, $ET_imp, $P_imp";
          
          $direction = (
              ( $Sn - $S0 + $ET_imp - $P_imp + ($riser_flow*$dt/43560.0) - $diffStatic )
              * ( $S1 - $S0 + $ET_imp1 - $P_imp1 + ($riserP*$dt/43560.0) - $diffStatic )
            )
          ;
          $debug_detail_log[] = "direction: $direction";
          $debug_detail_log[] = " = ( ( $Sn - $S0 + $ET_imp - $P_imp + ($riser_flow*$dt/43560.0) - $diffStatic ) ";
          $debug_detail_log[] = "     * ( $S1 - $S0 + $ET_imp1 - $P_imp1 + ($riserP*$dt/43560.0) - $diffStatic ) ) ";

          if (
            (
              ( $Sn - $S0 + $ET_imp - $P_imp + ($riser_flow*$dt/43560.0) - $diffStatic )
              * ( $S1 - $S0 + $ET_imp1 - $P_imp1 + ($riserP*$dt/43560.0) - $diffStatic )
            ) < 0
          ) {
            $Si = $Sn;
          } else {
            $S1 = $Sn;
            $riserP = $riser_flow;
          }
          //error_log("$riser_flow = this->solver($Sn)");
        } else {
          //Tolerance achieved, solution found
          break;
        }
        $diff = abs($Sn - $S0 + $ET_imp - $P_imp + ($riser_flow*$dt/43560.0) - $diffStatic);
      }//end loop
    }
    // store this in both places, the 'value' property is assumed for subcomps and others are for state 
    $stage = floatval($this->storage_stage_area->evaluateMatrix($Sn,'stage'));
    $riser_head = $stage - $this->riser_opening_elev;
    $this->riser_flow = $riser_flow;
    $this->riser_storage = $Sn; // storage calc'ed by this at end of time step
    $this->riser_head = $riser_head;
    $this->riser_mode = ($riser_head < $this->riser_length) ? 'weir' : 'pipe';
    $this->value = $riser_flow;
    if (is_object($this->container)) {
      // if we are operating as a plugin we have the ability to alter the state of the container object
      $this->container->state['riser_flow'] = $riser_flow;
      $this->container->state['riser_head'] = $riser_head;
      $this->container->state['riser_mode'] = $riser_mode;
      $this->container->state['riser_stage'] = $stage;
      $this->container->state['its'] = $x;
    }
    $thisdate = $this->container->state['thisdate'];
    //error_log("Solution Summary: @ $thisdate ( $x iterations )");
    if ($this->container->log_solution_problems and $x > 500) {
    //if (this->container->log_solution_problems) {
      error_log("***** BEGIN - DETAILED SOLUTION LOG");
      $j = 0;
      foreach ($debug_detail_log as $logline) {
        $j++;
        error_log($logline);
        if ($j > 60) {
          break;
        }
      }
      error_log("***** END - DETAILED SOLUTION LOG");
    }
  }
}