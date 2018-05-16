riser_length<-2
riser_diameter<-5
riser_pipe_flow_head<-2
riser_opening_elev<-2
riser_emerg_elev_elev<-8
riser_emerg_diameter<-20

stage<-seq(0,10,0.1)
riser_head <- stage - riser_opening_elev
riser_emerg_head <- stage - riser_emerg_elev_elev

riser_flow<-numeric(length(stage))
riser_mode<-character(length(stage))

for (i in 1:length(stage)){
  if (riser_head[i] > 0) {
    if (riser_head[i] > riser_pipe_flow_head & riser_emerg_head[i] < 0) {
      riser_flow[i] <-0.6*riser_length*riser_diameter*sqrt(2.0*32.2*(riser_head[i]-(0.5*riser_length)))
      riser_mode[i] <- 'pipe'
    } else if (riser_emerg_head[i] >= 0){
      riser_flow[i]<-(0.6*riser_length*riser_diameter*sqrt(2.0*32.2*(riser_head[i]-(0.5*riser_length))))+3.1*riser_emerg_diameter*riser_emerg_head[i]^1.5
      riser_mode[i] <- 'Emergency'
    }else {
      riser_flow[i]<-3.1 * riser_diameter * riser_head[i]^1.5
      riser_mode[i]<-'weir'
    } 
  } else {
    riser_flow[i]<-0.0
  }
}
plot(1:length(stage),riser_flow)
