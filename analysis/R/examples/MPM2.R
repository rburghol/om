#Load in a stage storage table and input orifice height, diameter, and normal stage
SS<-read.csv("C:/usr/local/home/git/om-github/om/analysis/R/examples/SS.csv")
diameter<-8.95
height<-2
NS<-7.406753
dt<-3600

#Load in inflow data (as well as other model data)
fxn_locations = 'C:/Users/conno/Desktop/GitHub/r-dh-ecohydro/Analysis'
source(paste(fxn_locations,"fn_vahydro-1.0.R", sep = "/"))
source(paste(fxn_locations,"fn_iha.R", sep = "/"))
runid<-7998
elid<-340298
dat<-fn_get_runfile(elid, runid)
dat<-dat[order(as.POSIXct(dat$timestamp)),]
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
dat$its<-numeric(length(dat$impoundment_Qin))
#loop that looks at each inflow and calculates storage and outflow simealtaneously by creating a function to 
#find S such that dS=Qin-Qout (MPM equation)
for (i in 2:length(dat$impoundment_Qin)){
  S0<-dat$MPMStorage[i-1]#Stores previous timestep storage for easy reference
  Qin<-as.numeric(dat$impoundment_Qin[i])#Stores inflow for easy reference
  ET<-dat$et_in[i]/12/24
  P<-dat$precip_in[i]/12/24
  SA<-approx(x=SS$Storage,y=SS$SA,xout=S0,rule=1)$y
  ET_imp<-ET*SA
  P_imp<-P*SA
  S1<-S0+(Qin*3600/43560)-(ET_imp-P_imp)#Maximum possible storage
  riser_flow<-Solver(S1)#Maximum possible outflow
  riserP<-riser_flow#Maximum interval outflow for use in bisection method
  Si<-0#Minimuim storage for use in bisection method
  Sn<-S1#A storage to be iterated within the below while loop
  SA<-approx(x=SS$Storage,y=SS$SA,xout=Sn,rule=1)$y
  ET_imp<-ET*SA
  P_imp<-P*SA
  #Begin a loop that continuously computes the MPM equation until tolerance is achieved
  x<-1
  if(riser_flow>0){
    while (abs((Sn-S0+(ET_imp-P_imp)+riser_flow*dt/43560)-(Qin*dt/43560)) > 0.0001){
      x<-x+1
      #Check the conditional statement in the while loop to break the loop before computation
      if (x>500){
        SA<-approx(x=SS$Storage,y=SS$SA,xout=S0,rule=1)$y
        ET_imp<-ET*SA
        P_imp<-P*SA
        Sn<-S0-(ET_imp-P_imp)
        riser_flow<-Qin
        SA1<-approx(x=SS$Storage,y=SS$SA,xout=S1,rule=1)$y
        ET_imp1<-ET*SA1
        P_imp1<-P*SA1
        diff<-(S1-S0+(ET_imp1-P_imp1)+riserP*dt/43560)-(Qin*dt/43560)
        if(round(Si,9)==round(S1,9)&diff>0.0001){
          Sn<-S1
          riser_flow<-((S1-S0+(ET_imp1-P_imp1))-(Qin*dt/43560))*43560/-dt
        }
        break
      }
      diff<-(Sn-S0+(ET_imp-P_imp)+riser_flow*dt/43560)-(Qin*dt/43560)
      if (abs((Sn-S0+(ET_imp-P_imp)+riser_flow*dt/43560)-(Qin*dt/43560)) > 0.0001){
        #If tolerance has not been achieved, use the bisection method to find S and Q
        Sn<-(S1+Si)/2#New storage computed from the midpoint of max and min storage, S1 and Si respectivley
        riser_flow<-Solver(Sn)#Corresponding outflow
        SA<-approx(x=SS$Storage,y=SS$SA,xout=Sn,rule=1)$y
        ET_imp<-ET*SA
        P_imp<-P*SA
        #Now that flow has been calculated, the bisection method can be continued. Need to shorten interval with guess Sn
        #Compute the MPM equation for S1 (maximum storage) and Sn (current iterator). If product is negative, they are of
        #opposite sign. Thus, a solution for S and Q are contained within this new interval, replace Si with Sn. Otherwise,
        #if they are of the same sign, assign Sn as S1 to serve as the new maximum storage value. Then replace riserP with 
        #the current riser_flow for future reference in solving the MPM for S1
        SA1<-approx(x=SS$Storage,y=SS$SA,xout=S1,rule=1)$y
        ET_imp1<-ET*SA1
        P_imp1<-P*SA1
        if(((Sn-S0+(ET_imp-P_imp)+riser_flow*dt/43560)-(Qin*dt/43560))*((S1-S0+(ET_imp1-P_imp1)+riserP*dt/43560)-(Qin*dt/43560))<0){
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
  }
  #Store stage, storage, and outflow calculated from the MPM method for plotting
  dat$MPMStorage[i]<-Sn
  dat$MPMQout[i]<-riser_flow
  dat$MPMStage[i]<-approx(x=SS$Storage,y=SS$Stage,xout=S1,rule=1)$y
  dat$its[i]<-x
}

par(mar=c(5,6,2,4))
plot(
  as.POSIXct(dat$timestamp),
  as.numeric(dat$MPMStage),
  type='l',
  col='blue',
  lwd=2,
  cex.lab=2,
  cex.axis=2,
  #ylim=c(0,200),
  xlab='Time',
  ylab='Stage (ft)',
  ylim=c(as.numeric(min(dat$impoundment_lake_elev)),as.numeric(max(dat$impoundment_lake_elev)))
)
lines(
  as.POSIXct(dat$timestamp),
  dat$impoundment_lake_elev,
  col='red',
  lwd=2,
  type='l'
)
points(as.POSIXct(dat$timestamp[dat$impoundment_its>500&dat$impoundment_riser_head>0]),dat$impoundment_lake_elev[dat$impoundment_its>500&dat$impoundment_riser_head>0],pch=19,cex=3)
par(new=T)
plot(dat$timestamp,dat$impoundment_Qin,type='l',lty=3,ann=F,axes=F)

legend('top',c('MPM stage','VA Hydro stage','Inflow'),col=c('blue','red','black'),lty=c(1,1,3),lwd=4,cex=2,bty='n',y.intersp = 0.5,x.intersp = 0.5,seg.len=0.75)



################TESTING
yr1985<-dat[dat$year==1985,]
plot(yr1985$timestamp,yr1985$impoundment_lake_elev,type='l')
points(as.POSIXct(yr1985$timestamp[yr1985$impoundment_its>500&yr1985$impoundment_riser_head>0]),yr1985$impoundment_lake_elev[yr1985$impoundment_its>500&yr1985$impoundment_riser_head>0],col='red')

start<-'1985-07-25'
end<-'1985-07-27'
subst<-dat[as.Date(dat$timestamp)>=start&as.Date(dat$timestamp)<=end,]

par(mar=c(5,6,2,5))
plot(subst$timestamp,subst$impoundment_lake_elev,type='l',lwd=2,cex.axis=2,cex.lab=2,xlab='Date',ylab='Stage (ft)',main=paste(start,end))
abline(h=NS,lty=3)
points(as.POSIXct(subst$timestamp[subst$impoundment_its>500&subst$impoundment_riser_head>0]),subst$impoundment_lake_elev[subst$impoundment_its>500&subst$impoundment_riser_head>0],col='red')
legend('top',lwd=2,legend=c('Inflow',"Outflow"),col=c('blue','red'),cex=2,y.intersp = 0.5,x.intersp=0.5,bty='n')
par(new=TRUE)
plot(subst$timestamp,subst$impoundment_Qin,type='l',col='blue',ann=F,axes=F,lwd=2)
lines(subst$timestamp,subst$Qout,col='red',lwd=2)
axis(4,cex.axis=2)
mtext('Flow (cfs)',4,cex=2,line=3)
