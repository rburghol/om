#!/bin/bash
# Usage: batch_all_2020.sh elementid [startdate] [enddate] [ccstartdate] [ccenddate]
if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: Usage: batch_all_2020.sh elementid [run_ids inside double quotes space delimited]"
  echo 1>&2 "run_ids is a double quotes space delimited list]"
  exit 2
fi
elid=$1
cachedate='2019-12-01'
startdate='1984-01-01'
enddate='2014-01-01'
ccstart='1984-01-01'
ccend='2000-12-31'
runids="11 13 18 17 12 19 20"
if [ $# -gt 1 ]; then
  runids=$2
fi 

force=2 # 0=none, 1=all elements, 2=model outlet only, 3=all of type watershed model node

# This is run with nohup so no need to background the individual processes, in fact, that would cause them to run over each other 
# which would make the fail.
cd /var/www/html/om
for runid in $runids; do
  echo "Starting Run $runid "
  cmd="no match"
  case runid in 
    11)
      run_mode=10 # 9=VWP Exempt, 10=2020 Demand, 11=2030 Demand, 12=2040 Demand
      flow_mode=4 # 3=VAHydro 1.0/CBP5.3, 4=CBP Phase 6, 5=CBP Phase 6 CC1 50/50,6=CBP Phase 6 CC2 10/10, 7=CBP Phase 6 CC3 90/90
      cmd="/usr/bin/php run_shakeTree.php 1 $elid $runid $startdate $enddate $cachedate $force 37 -1 $run_mode normal flow_mode=$flow_mode"
      ;;

    13)
      run_mode=12 # 9=VWP Exempt, 10=2020 Demand, 11=2030 Demand, 12=2040 Demand
      flow_mode=4 # 3=VAHydro 1.0/CBP5.3, 4=CBP Phase 6, 5=CBP Phase 6 CC1 50/50,6=CBP Phase 6 CC2 10/10, 7=CBP Phase 6 CC3 90/90
      cmd="/usr/bin/php run_shakeTree.php 1 $elid $runid $startdate $enddate $cachedate $force 37 -1 $run_mode normal flow_mode=$flow_mode" 
      ;;

    18)
      run_mode=9
      flow_mode=4
      cmd="/usr/bin/php run_shakeTree.php 1 $elid $runid $startdate $enddate $cachedate $force 37 -1 $run_mode normal flow_mode=$flow_mode" 
      ;;

    17)
      run_mode=12
      flow_mode=6
      cmd="/usr/bin/php run_shakeTree.php 1 $elid $runid $ccstart $ccend $cachedate $force 37 -1 $run_mode normal flow_mode=$flow_mode" 
      ;;

    12)
      run_mode=11 # 9=VWP Exempt, 10=2020 Demand, 11=2030 Demand, 12=2040 Demand
      flow_mode=4 # 3=VAHydro 1.0/CBP5.3, 4=CBP Phase 6, 5=CBP Phase 6 CC1 50/50,6=CBP Phase 6 CC2 10/10, 7=CBP Phase 6 CC3 90/90
      cmd="/usr/bin/php run_shakeTree.php 1 $elid $runid $startdate $enddate $cachedate $force 37 -1 $run_mode normal flow_mode=$flow_mode" 
      ;;

    19)
      run_mode=12
      flow_mode=5
      cmd="/usr/bin/php run_shakeTree.php 1 $elid $runid $ccstart $ccend $cachedate $force 37 -1 $run_mode normal flow_mode=$flow_mode" 
      ;;

    20)
      run_mode=12
      flow_mode=7
      cmd="/usr/bin/php run_shakeTree.php 1 $elid $runid $ccstart $ccend $cachedate $force 37 -1 $run_mode normal flow_mode=$flow_mode" 
      ;;
  esac

  echo "cmd: $cmd"

done