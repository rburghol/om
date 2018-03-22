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
    $riser_flow = 3.1*$diameter * pow($head,1.5);
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
    } else if ($head > 0 and $head < $this->riser_length){
        $riser_flow = $this->weir($head, $this->riser_diameter);
    } else if ($head > 0 and $head >= $this->riser_length) {
      $riser_flow = $this->pipe($head, $this->riser_diameter, $this->riser_length);
    } else {
      $riser_flow = 0;
    }
    return $riser_flow;
  }

  function solver($S1) {
    $Stg = floatval($this->storage_stage_area->evaluateMatrix($S1,'stage'));
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
    $S0 = $vars['Storage'];
    $dt = $vars['dt'];
    $precip_acfts = $vars['precip_acfts'];
    $evap_acfts = $vars['evap_acfts'];
    $Qin = $vars['Qin'];
    $flowby = $vars['flowby'];
    $discharge = $vars['discharge'];
    $refill = $vars['refill'];
    $demand = $vars['demand'];
    // ********************************************
    // Initial Estimate of Riser Head
    // Need storage guess to estimate riser head
    // this initial guess assumes 0 outflow through riser, so is max possible head to start
    // ********************************************
    // guess S1
    $S1 = $S0 
      + (($Qin - $flowby) * $dt / 43560.0) 
      + (1.547 * $discharge * $dt / 43560.0) 
      + (1.547 * $refill * $dt / 43560.0) 
      - (1.547 * $demand * $dt /  43560.0) 
      - ($evap_acfts * $dt) 
      + ($precip_acfts * $dt)
    ;
    // calculate riser_head at this storage
    $this->storage_stage_area->lutype2 = 0; // a fix
    $stage = floatval($this->storage_stage_area->evaluateMatrix($S1,'stage'));
    $riser_head = $stage - $this->riser_opening_elev;
    // @todo: add emergency spillway handling
    $riser_emerg_head = $stage - $this->riser_emerg_elev;
    //error_log("RISER($this->state[runid] : Current stage: $stage, riser_head: $riser_head, Riser Opening S = $this->riser_opening_storage (elev: $this->riser_opening_elev), Current S1 = $S1");
    //error_log("RISER($this->state[runid] : Riser Head: $riser_head, riser_pipe_flow_head: $this->riser_pipe_flow_head, Riser Opening S = $this->riser_opening_storage");
    // Now, if max possible riser_head > 0 then we have at least some flow out of riser
    //error_log("S0 ($S0) + Qin ($Qin) = $S1"); 
    
    $x = 0; //Need a loop counter
    $Si = 0;//A lower bound storage estimate
    $Sn = $S1;//A storage iterator for within the loop
    $diff = 1.0; // initial difference value to force into while loop
    $riserP = empty($this->riser_flow) ? 0 : $this->riser_flow;//A reference to previous riser flow
    $riser_flow = empty($this->riser_flow) ? 0 : $Qin;
    $initial = abs(($Sn-$S0+$riser_flow*$dt/43560)-($Qin*$dt/43560));
    //error_log("$initial = (abs(($Sn-$S0+$riser_flow*$dt/43560)-($Qin*$dt/43560)) > $this->tolerance)");
    while ($diff > $this->tolerance){
      $x += 1;
      #Check the conditional statement in the while loop to break the loop before computation
      if ($x > 500) {
        $Sn = $S0;
        $riser_flow = $Qin;
        break;
      }
      if ($diff > $this->tolerance){
        //If tolerance has not been achieved, use the bisection method to find S and Q
        $Sn = ($S1+$Si)/2.0; //New storage computed from the midpoint of max and min storage, S1 and Si respectivley
        $riser_flow = $this->solver($Sn); //Corresponding outflow
        //error_log("$riser_flow = this->solver($Sn)");
        //Now that flow has been calculated, the bisection method can be continued. Need to shorten interval with guess Sn
        //Compute the MPM equation for S1 (maximum storage) and Sn (current iterator). If product is negative, they are of
        //opposite sign. Thus, a solution for S and Q are contained within this new interval, replace Si with Sn. Otherwise,
        //if they are of the same sign, assign Sn as S1 to serve as the new maximum storage value. Then replace riserP with 
        //the current riser_flow for future reference in solving the MPM for S1
        if (
          (
            ( ($Sn-$S0+$riser_flow*$dt/43560) - ($Qin*$dt/43560.0) )
            * ( ($S1-$S0+$riserP*$dt/43560) - ($Qin*$dt/43560.0) )
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
      $diff = abs(($Sn - $S0 + $riser_flow*$dt/43560)-($Qin*$dt/43560));
    }//end loop
    // store this in both places, the 'value' property is assumed for subcomps and others are for state 
    $this->riser_flow = $riser_flow;
    $this->riser_head = $riser_head;
    $this->riser_mode = $riser_mode;
    $this->value = $riser_flow;
    if (is_object($this->container)) {
      // if we are operating as a plugin we have the ability to alter the state of the container object
      $this->container->state['riser_flow'] = $riser_flow;
      $this->container->state['riser_head'] = $riser_head;
      $this->container->state['riser_mode'] = $riser_mode;
      $this->container->state['its'] = $x;
    }
  }
}
