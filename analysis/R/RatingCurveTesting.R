#Need to plot head vs discharge equations
head<-seq(0,5,.01)
d<-3
h<-2

weir<-function(head,diameter=d){
  flow<-3.1*diameter*head^1.5
  return(flow)
}
pipe<-function(head,diameter=d,height=h){
  flow<-numeric(length(head))
  for (i in 1:length(head)){
    if (head[i]<height){
      flow[i]<-NA
    } else {
      flow[i]<-0.6*height*diameter*sqrt(2*32.2*(head[i]-0.5*height))
    }
  }
  return(flow)
}
pipe2<-function(head,diameter=d,height=h){
  flow<-numeric(length(head))
  for (i in 1:length(head)){
    if (head[i]<height){
      flow[i]<-NA
    } else {
      flow[i]<-0.6*(2/3)*diameter*sqrt(2*32.2)*(head[i]^1.5-(head[i]-height)^1.5)
    }
  }
  return(flow)
}

plot(head,weir(head),xlab="Hydraulic Head (ft)",ylab="Discharge (cfs)",type='l',lwd=2,cex.axis=2,cex.lab=2)
lines(head,pipe(head),col='blue',lwd=2)
lines(head,pipe2(head),col='red',lwd=2)
abline(v=l,lty=3)
legend('topleft',c('R Weir','RO','New RO'),lty=1,col=c('black','blue','red'),bty='n',cex=2,y.intersp = 0.25,x.intersp=0.5)
