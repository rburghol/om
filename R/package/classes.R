## OMI object model classes basic implementation
## range of values; method $undo() undoes the last edit.
om.omi.base <- setRefClass(
  "om.omi.base",
  fields = list(
    name = "character",
    value = "numeric",
    data = "list",
    inputs = "list",
    components = "list",
    host = 'character',
    type = 'character',
    compid = 'character',
    id = 'character'
  ),
  methods = list(
    initialize = function(){
      # init() in OM php
      if (length(components) > 0) {
        for (i in 1:length(components)) {
          components[[i]]$initialize()
        }
      }
    },
    prepare = function(){
      # preStep() in OM php
    },
    update = function(){
      # step() in OM php
      getInputs()
      if (length(components) > 0) {
        for (i in 1:length(components)) {
          components[[i]]$update()
        }
      }
    },
    finish = function(){
      # postStep() in OM php
    },
    validate = function(){
      
    },
    logState = function () {
      
    },
    getValue = function(name = "value"){
      # returns the value.  Defaults to simple case where object only has one possible value
      return(value)
    },
    addInput = function(
      local_name = character(), 
      object = om.omi.base, 
      remote_name = '', 
      input_type = 'numeric'
    ){
      # adds inputs named as "localname"
      # a given localname input may have multiple inputs
      # so they are stored as a nested list
      if (is.null(inputs[[local_name]])) {
        inputs[[local_name]] <<- list()
        print(paste("adding", local_name, inputs[local_name]))
      }
      iid = length(inputs[[local_name]]) + 1
      inputs[[local_name]][iid] <<- list(
        input = list(
          local_name = local_name,
          object = object,
          remote_name = remote_name,
          input_type = input_type
        )
      )
    },
    # added to base specification 
    getInputs = function () {
      # get data from related objects or internal timeseries feeds
      # store in internal "data" list 
      if (length(names(inputs)) > 0) {
        nms = names(inputs)
        for (i in 1:length(nms)) {
          i_name = nms[i]
          for (j in 1:length(inputs[i_name])) {
            input = inputs[[i_name]][[j]]
            print("obtained input")
            #print(input)
            i_object = input$object
            r_name = input$remote_name
            i_type = input$input_type
            if (length(r_name) > 0) {
              i_value = i_object$getValue(r_name)
            } else {
              i_value = i_object$getValue()
            }
            if (i_type == 'numeric') {
              if (j == 1) {
                # nullify on initial
                data[i_name] <<- 0
              }
              print(data[[i_name]])
              print(i_value)
              data[i_name] <<- data[[i_name]] + i_value
            }
          }
          
        }
      }
    },
    logState = function () {
      
    },
    addComponent = function (thiscomp = om.omi.base) {
      if (length(thiscomp$host) == 0) {
        thiscomp$host = 'localhost'
      }
      if (length(thiscomp$type) == 0) {
        thiscomp$type = 'unknown'
      }
      if (length(thiscomp$id) == 0) {
        thiscomp$id = paste('local', length(components) + 1, sep='');
      }
      thiscomp$compid = paste(thiscomp$host,thiscomp$type,thiscomp$id, sep=":")
      # we can add this with numberic indices if we like
      # however, we must have a way of linking objects, which
      # requires a persistent name, i.e. a sort of DOI
      # format: host:type:id, examples:
      #   localhost:feature:647 (well 647),
      #   localhost:component:1991 (the withdrawal amt)
      # Input/Link format:
      #   localname:host:type:id:[remote name]
      #   - if property name is null then just use getValue() without parameter
      #print(thiscomp$compid)
      components[thiscomp$compid] <<- list('object' = thiscomp)
    }
  )
)

om.omi.timer <- setRefClass(
  "om.omi.timer",
  fields = list(
    starttime = "POSIXct",
    endtime = "POSIXct",
    thistime = "POSIXct",
    tz = "integer",
    dt = "numeric" # time step increment in seconds
  ),
  contains = "om.omi.base",
  methods = list(
    update <- function () {
      thistime <- thistime + dt
    }
  )
)
# use: 

om.omi.runtimeController <- setRefClass(
  "om.omi.runtimeController",
  fields = list(
    timer = "om.omi.timer",
    code = "character"
  ),
  contains = "om.omi.base"
)

om.omi.linkableComponent <- setRefClass(
  "om.omi.linkableComponent",
  fields = list(
    value = "numeric",
    code = "character"
  ),
  contains = "om.omi.base"
)

om.omi.equation <- setRefClass(
  "om.equation",
  fields = list(
    equation = "character",
    eq = "expression",
    defaultvalue = "numeric"
  ),
  contains = "om.omi.linkableComponent",
  methods = list(
    initialize = function() {
      callSuper()
      if (length(defaultvalue) == 0) {
        defaultvalue <<- 0
      }
      value <<- defaultvalue
      eq <<- parse(text=equation)
    },
    update = function() {
      callSuper()
      # evaluating an equation should be:
      # 1. restricted to variables in the local $data array
      # step() in OM php
      data$value <<- value
      preval = eval(eq, data)
      value <<- as.numeric(preval)
    }
  )
)
