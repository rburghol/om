<?php

class omRuntime_SubComponent {
  var $propvalue;
  var $propvalue;
  function __construct($options) {
    return TRUE;
  }
}

class omRuntime_HydroRiser extends omRuntime_SubComponent {
  var $storage_stage_area = FALSE;
  var $riser_opening_elev = FALSE;
  var $riser_opening_storage = FALSE;
  var $riser_pipe_flow_head = FALSE;
  var $riser_flow = FALSE;
  var $riser_head = FALSE;
  var $riser_length = FALSE;
  var $riser_diameter = FALSE;
  var $container = FALSE;
  
  function __construct($options) {
    $this->storage_stage_area = isset($options['storage_stage_area']) ? $options['storage_stage_area'] : FALSE;
    if (!$this->storage_stage_area) {
      return FALSE;
    }
    $this->container = isset($options['container']) ? $options['container'] : FALSE;
    if (!$this->container) {
      return FALSE;
    }
    $this->riser_opening_storage = isset($options['riser_opening_storage']) ? $options['riser_opening_storage'] : 0.0;
    $this->riser_length = isset($options['riser_length']) ? $options['riser_length'] : 1.0;
    $this->riser_pipe_flow_head = isset($options['riser_pipe_flow_head']) ? $options['riser_pipe_flow_head'] : 0.0;
    // must have the stage/storage/sarea dataMatrix for this to work
    //$this->storage_stage_area->lutype2 = 0; // a fix since this settign gets lost?
    $this->riser_opening_elev = $this->storage_stage_area->evaluateMatrix($this->riser_opening_storage,'stage'); // find storage at riser opening stage
    $riser_head = $stage - $this->riser_opening_elev;
    error_log("Riser properties: 
      length: $this->riser_length
      opening storage: $this->riser_opening_storage
      opening elev (calc): $this->riser_opening_elev
      pipe flow head: $this->riser_pipe_flow_head " . print_r($options,1));
      
    error_log("RISER($this->state[runid] : Initial matrix " . print_r((array)$this->matrix,1));
    error_log("RISER($this->state[runid] : Evaluating Riser Opening Elevation = $this->riser_opening_elev at S = $this->riser_opening_storage, riser_head = $riser_head ");
  }
  
  function evaluate() {
    // ********************************************
    // Get state from parent
    // ********************************************
    $S0 = $this->container->state['Storage'];
    $precip_acfts = $this->container->state['precip_acfts'];
    $evap_acfts = $this->container->state['evap_acfts'];
    $Qin = $this->container->state['Qin'];
    $flowby = $this->container->state['flowby'];
    $discharge = $this->container->state['discharge'];
    $refill = $this->container->state['refill'];
    $demand = $this->container->state['demand'];
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
    $stage = $this->storage_stage_area->evaluateMatrix($S1,'stage');
    $riser_head = $stage - $this->riser_opening_elev;
    error_log("RISER($this->state[runid] : Current stage: $stage, riser_head: $this->riser_head, Riser Opening S = $this->riser_opening_storage, Current S1 = $S1");
    //error_log("RISER($this->state[runid] : Riser Head: $riser_head, riser_pipe_flow_head: $this->riser_pipe_flow_head, Riser Opening S = $this->riser_opening_storage");
    // Now, if max possible riser_head > 0 then we have at least some flow out of riser
    if ($riser_head > 0) {
      // determine which orifice equation to use depending on riser_head
      if ($riser_head > $this->riser_pipe_flow_head) {
        // pipe flow
        $riser_flow = 0.6 * $this->riser_length 
          * $this->riser_diameter 
          * sqrt(2.0 * 32.2 * ($riser_head - $this->riser_length))
        ;
      } else {
        // weir flow 
        $riser_flow = 3.1 * $this->riser_diameter * pow($riser_head, 1.5);
      }
      $S1 = $S0 
        + (($Qin - $flowby - $riser_flow) * $dt / 43560.0) 
        + (1.547 * $discharge * $dt / 43560.0)  
        + (1.547 * $refill * $dt / 43560.0) 
        - (1.547 * $demand * $dt /  43560.0) 
        - ($evap_acfts * $dt) 
        + ($precip_acfts * $dt)
      ;
      if ($S1 < $this->riser_opening_storage) {
        // calculate intermediate value to see what riser flow WOULD be to make S1 = riser_opening_storage
        $S00 = $S0 
          + (($Qin - $flowby) * $dt / 43560.0) 
          + (1.547 * $discharge * $dt / 43560.0)  
          + (1.547 * $refill * $dt / 43560.0) 
          - (1.547 * $demand * $dt /  43560.0) 
          - ($evap_acfts * $dt) 
          + ($precip_acfts * $dt)
        ;
        $riser_flow = ($S00 - $this->riser_opening_storage) * 43560.0 / $dt;
        if ($riser_flow < 0 ) {
          $riser_flow = 0;
        }
      }
    } else {
      $riser_flow = 0.0;
    }
    // store this in both places, the 'value' property is assumed for subcomps and others are for state 
    $this->riser_flow = $riser_flow;
    $this->value = $riser_flow;
  }
}

?>