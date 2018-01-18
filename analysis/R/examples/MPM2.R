#Load in a stage storage table and input orifice height, diameter, and normal stage
SS<-read.csv("C:/Users/connorb5/Desktop/GitHub/om/analysis/R/examples/SS.csv")
diameter<-13.1
height<-2
NS<-5.7124
dt<-3600

#Load in inflow data (as well as other model data)
fxn_locations = 'C:/Users/connorb5/Desktop/GitHub/r-dh-ecohydro/Analysis'
source(paste(fxn_locations,"fn_vahydro-1.0.R", sep = "/"))
source(paste(fxn_locations,"fn_iha.R", sep = "/"))
runid<-99997
elid<-340136
dat<-fn_get_runfile(elid, runid)
S<-dat$impoundment_Storage[1]#Input base storage

#Function to calculate flow from weir stage of our orifice
weir<-function(head,diameter=d){
  riser_flow<-3.1*diameter*head^1.5
  return(riser_flow)
}
#Function to calculate flow from pipe stage of our orifice
pipe<-function(head,diameter=d,height=h){
  riser_flow<-0.6*height*diameter*sqrt(2*32.2*(head-0.5*height))
  return(riser_flow)
}
#Function to calculate outflow based on stage and normal pool elevation
discharge<-function(stage){
  head<-stage-NS
  if(head<0){
    riser_flow<-0
  } else if (head>0&head<height){
      riser_flow<-weir(head,diameter)
  } else if (head>0&head>=height){
    riser_flow<-pipe(head,diameter,height)
  } else {
    riser_flow<-0
  }
  return(riser_flow)
  }
#Function to calculate discharge from a given storage
Solver<-function(Storage){
  Stg<-approx(x=SS$Storage,y=SS$Stage,xout=Storage,rule=1)$y
  riser_flow<-discharge(Stg)
  return(riser_flow)
}

#Create empty columns to store data. initialize with above boundary conditions
dat$MPMStorage<-numeric(length(dat$impoundment_Qin));dat$MPMStorage[1]<-S
dat$MPMQout<-numeric(length(dat$impoundment_Qin))
dat$MPMStage<-numeric(length(dat$impoundment_Qin));dat$MPMStage[1]<-approx(x=SS$Storage,y=SS$Stage,xout=S,rule=1)$y
#loop that looks at each inflow and calculates storage and outflow simealtaneously by creating a function to 
#find S such that dS=Qin-Qout (MPM equation)
for (i in 2:length(dat$impoundment_Qin)){
  S0<-dat$MPMStorage[i-1]#Stores previous timestep storage for easy reference
  Qin<-as.numeric(dat$impoundment_Qin[i])#Stores inflow for easy reference
  S1<-S0+(Qin*3600/43560)#Maximum possible storage
  riser_flow<-Solver(S1)#Maximum possible outflow
  riserP<-riser_flow#Maximum interval outflow for use in bisection method
  Si<-0#Minimuim storage for use in bisection method
  Sn<-S1#A storage to be iterated within the below while loop
  #Begin a loop that continuously computes the MPM equation until tolerance is achieved
  while (abs((Sn-S0+riser_flow*dt/43560)-(Qin*dt/43560)) > 0.0001){
    #Check the conditional statement in the while loop to break the loop before computation
    if (abs((Sn-S0+riser_flow*dt/43560)-(Qin*dt/43560)) > 0.0001){
      #If tolerance has not been achieved, use the bisection method to find S and Q
      Sn<-(S1+Si)/2#New storage computed from the midpoint of max and min storage, S1 and Si respectivley
      riser_flow<-Solver(Sn)#Corresponding outflow
      #Now that flow has been calculated, the bisection method can be continued. Need to shorten interval with guess Sn
      #Compute the MPM equation for S1 (maximum storage) and Sn (current iterator). If product is negative, they are of
      #opposite sign. Thus, a solution for S and Q are contained within this new interval, replace Si with Sn. Otherwise,
      #if they are of the same sign, assign Sn as S1 to serve as the new maximum storage value. Then replace riserP with 
      #the current riser_flow for future reference in solving the MPM for S1
      if(((Sn-S0+riser_flow*dt/43560)-(Qin*dt/43560))*((S1-S0+riserP*dt/43560)-(Qin*dt/43560))<0){
        Si<-Sn
      } else {
        S1<-Sn
        riserP<-riser_flow
      }
    } else {
      #Tolerance achieved, solution found
      break
    }
  }
  #Store stage, storage, and outflow calculated from the MPM method for plotting
  dat$MPMStorage[i]<-Sn
  dat$MPMQout[i]<-riser_flow
  dat$MPMStage[i]<-approx(x=SS$Storage,y=SS$Stage,xout=S1,rule=1)$y
}

plot(
#  dat$timestamp,
  as.numeric(dat$MPMStage),
  type='l',
  col='blue',
  lwd=2,
  cex.lab=2,
  cex.axis=2,
  #ylim=c(0,200),
  xlab='Time',
  ylab='Stage (ft)'
)
lines(
  dat$impoundment_lake_elev,
  col='red',
  lwd=2,
  type='l'
)
#lines(
#  as.numeric(dat$impoundment_Qin),
#  col='green',
#  lwd=2,
#  type='l',
#  lty=3
#)
#lines(
#  as.numeric(dat$MPMQout),
#  col='black',
#  lwd=2,
#  type='l',
#  lty=3
#)

legend('topleft',c('MPM','VA Hydro'),col=c('blue','red'),lwd=2,pch=1,cex=2,bty='n',y.intersp = 0.5)


# Just show a specific design storm 
destorm <- window(
  dat, 
  start = as.POSIXct("1996-05-06 16:00"), 
  end = as.POSIXct("1996-05-07 0:00")
);

plot(
  #  dat$timestamp,
  as.numeric(destorm$MPMStage),
  type='o',
  col='blue',
  lwd=2,
  cex.lab=2,
  cex.axis=2,
  ylim=c(0,200),
  xlab='Time',
  ylab='Stage (ft)'
)
lines(
  as.numeric(destorm$impoundment_Qin),
  col='red',
  lwd=2,
  type='o'
)
lines(
  as.numeric(destorm$MPMQout),
  col='green',
  lwd=2,
  type='o'
)

#check2<-data.frame(dat$impoundment_Qin,dat$MPMStorage,dat$MPMQout,dat$MPMStage)
