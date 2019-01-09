
source("/usr/local/home/git/vahydro/om/R/package/classes.R") 
# instantiate a version of om.equation
m <- om.omi.runtimeController();

j <- om.omi.equation();
k <- om.omi.equation();
l <- om.omi.equation();
k$equation = "value + 1"; # just add one to previous value
k$defaultvalue = 0
m$addComponent(k) 
m$addComponent(l) 
m$addComponent(j) 
k$equation = "value + 1"
l$equation = "kval + 2"
l$addInput('kval', k, 'value', 'numeric')
j$equation = "k^2 + l"
j$addInput('k', k, 'value', 'numeric')
j$addInput('l', l, 'value', 'numeric')
m$initialize()

for (i in c(1:10)) {
  m$update()
  print(paste("k @ ts = ", i, ",", k$equation, " value = ", m$components[[k$compid]]$value))
  print(paste("l @ ts = ", i, ",", l$equation, " value = ", m$components[[l$compid]]$value))
  print(paste("j @ ts = ", i, ",", j$equation, " value = ", m$components[[j$compid]]$value))
  #l$getInputs()
}

