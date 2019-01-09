<?php

# lib_vwuds.php - query routines associated with vwuds database
# may later genericize and migrate this to a more robust project based query group


function getMaxWithdrawalCapacity($listobject, $mpid, $debug) {
   
   # gets maximum capacity of a measuring point
   # the table vwuds_measuring_point has a column "CAPACITY" which should hold this 
   # value in MGD, however, as of 9/25/2007, this value is null in all cases
   # so, we default to querying the annual_data table to estimate this value
   # we estimate the value based on the maximum reported daily withdrawal (MAXDAY) in MGD
   # or the largest single month (in MG) divided by the number of days in that month
   # whichever is largest
   
   $listobject->querystring = "  select \"MPID\" as mpid ";
   $listobject->querystring .= "    MAX(GREATEST(\"MAXDAY\", ";
   $listobject->querystring .= "       \"JANUARY\"/31.0, \"FEBRUARY\"/28.0, \"MARCH\"/31.0, ";
   $listobject->querystring .= "       \"APRIL\"/30.0, \"MAY\"/31.0, \"JUNE\"/30.0, \"JULY\"/31.0, ";
   $listobject->querystring .= "       \"AUGUST\"/31.0, \"SEPTEMBER\"/30.0, \"OCTOBER\"/31.0, ";
   $listobject->querystring .= "       \"NOVEMBER\"/30.0, \"DECEMBER\"/31.0) ) as maxday";
   $listobject->querystring .= " from annual_data ";
   $listobject->querystring .= " WHERE \"ACTION\" = 'WL' ";
   $listobject->querystring .= "    and \"MPID\" = '$mpid' ";
   $listobject->querystring .= " GROUP BY \"MPID\" ";
   
   $listobject->performQuery();
   $maxday = $listobject->getRecordValue(1,'maxday');
   
   return $maxday;
}



# WKT Functions
function getMaxSurfaceWithdrawalCapacityByWKT($listobject, $wktshape, $debug) {
   
   # gets maximum capacity of a measuring point
   # the table vwuds_measuring_point has a column "CAPACITY" which should hold this 
   # value in MGD, however, as of 9/25/2007, this value is null in all cases
   # so, we default to querying the annual_data table to estimate this value
   # we estimate the value based on the maximum reported daily withdrawal (MAXDAY) in MGD
   # or the largest single month (in MG) divided by the number of days in that month
   # whichever is largest
   
   $listobject->querystring = "  select sum(maxday) as maxday from ";
   $listobject->querystring .= " (select a.\"MPID\" as mpid, ";
   $listobject->querystring .= "    MAX(";
   $listobject->querystring .= "       GREATEST(\"MAXDAY\", ";
   $listobject->querystring .= "       \"JANUARY\"/31.0, \"FEBRUARY\"/28.0, \"MARCH\"/31.0, ";
   $listobject->querystring .= "       \"APRIL\"/30.0, \"MAY\"/31.0, \"JUNE\"/30.0, \"JULY\"/31.0, ";
   $listobject->querystring .= "       \"AUGUST\"/31.0, \"SEPTEMBER\"/30.0, \"OCTOBER\"/31.0, ";
   $listobject->querystring .= "       \"NOVEMBER\"/30.0, \"DECEMBER\"/31.0) ";
   $listobject->querystring .= "    ) as maxday";
   $listobject->querystring .= " from annual_data as a, vwuds_measuring_point as b ";
   $listobject->querystring .= " WHERE a.\"ACTION\" = 'WL' ";
   $listobject->querystring .= "    and a.\"MPID\" = b.\"MPID\" ";
   $listobject->querystring .= "    and b.\"TYPE\" = 'SW' ";
   $listobject->querystring .= "    and contains(geomFromText('$wktshape', 4326), b.the_geom) ";
   $listobject->querystring .= " GROUP BY a.\"MPID\" ";
   $listobject->querystring .= " ) as a ";
   if ($debug) { 
      print("$listobject->querystring ; <br>"); 
   }
   $listobject->performQuery();
   $maxday = $listobject->getRecordValue(1,'maxday');
   
   return $maxday;
}

function getMaxMPIDSurfaceWithdrawalCapacityByWKT($listobject, $wktshape, $debug) {
   
   # gets maximum capacity of a measuring point
   # the table vwuds_measuring_point has a column "CAPACITY" which should hold this 
   # value in MGD, however, as of 9/25/2007, this value is null in all cases
   # so, we default to querying the annual_data table to estimate this value
   # we estimate the value based on the maximum reported daily withdrawal (MAXDAY) in MGD
   # or the largest single month (in MG) divided by the number of days in that month
   # whichever is largest
   
   $listobject->querystring = "  select a.\"MPID\" as mpid, ";
   $listobject->querystring .= "    MAX(";
   $listobject->querystring .= "       GREATEST(\"MAXDAY\", ";
   $listobject->querystring .= "       \"JANUARY\"/31.0, \"FEBRUARY\"/28.0, \"MARCH\"/31.0, ";
   $listobject->querystring .= "       \"APRIL\"/30.0, \"MAY\"/31.0, \"JUNE\"/30.0, \"JULY\"/31.0, ";
   $listobject->querystring .= "       \"AUGUST\"/31.0, \"SEPTEMBER\"/30.0, \"OCTOBER\"/31.0, ";
   $listobject->querystring .= "       \"NOVEMBER\"/30.0, \"DECEMBER\"/31.0) ";
   $listobject->querystring .= "    ) as maxday";
   $listobject->querystring .= " from annual_data as a, vwuds_measuring_point as b ";
   $listobject->querystring .= " WHERE a.\"ACTION\" = 'WL' ";
   $listobject->querystring .= "    and a.\"MPID\" = b.\"MPID\" ";
   $listobject->querystring .= "    and b.\"TYPE\" = 'SW' ";
   $listobject->querystring .= "    and contains(geomFromText('$wktshape', 4326), b.the_geom) ";
   $listobject->querystring .= " GROUP BY a.\"MPID\" ";
   if ($debug) { 
      print("$listobject->querystring ; <br>"); 
   }
   $listobject->performQuery();
   $maxday = $listobject->queryrecords;
   
   return $maxday;
}

function getTotalSurfaceWithdrawalByWKT($listobject, $startyear, $endyear, $wktshape, $debug) {
   
   # gets maximum capacity of a measuring point
   # the table vwuds_measuring_point has a column "CAPACITY" which should hold this 
   # value in MGD, however, as of 9/25/2007, this value is null in all cases
   # so, we default to querying the annual_data table to estimate this value
   # we estimate the value based on the maximum reported daily withdrawal (MAXDAY) in MGD
   # or the largest single month (in MG) divided by the number of days in that month
   # whichever is largest
   $output = array();
   $output['message'] = '';
   
   $listobject->querystring = "  select sum(a.\"ANNUAL/365\") as totalannual, ";
   $listobject->querystring .= "    sum(a.\"ANNUAL/365\" * c.consumption) as totalconsumptive, ";
   $listobject->querystring .= "    avg(a.\"ANNUAL/365\") as meanannual, ";
   $listobject->querystring .= "    avg(a.\"ANNUAL/365\" * c.consumption) as meanconsumptive, ";
   $listobject->querystring .= "    avg(a.\"JANUARY\") as jantotal, ";
   $listobject->querystring .= "    avg(a.\"JANUARY\" * c.consumption) as jan, ";
   $listobject->querystring .= "    avg(a.\"FEBRUARY\") as febtotal, ";
   $listobject->querystring .= "    avg(a.\"FEBRUARY\" * c.consumption) as feb, ";
   $listobject->querystring .= "    avg(a.\"MARCH\") as martotal, ";
   $listobject->querystring .= "    avg(a.\"MARCH\" * c.consumption) as mar, ";
   $listobject->querystring .= "    avg(a.\"APRIL\") as aprtotal, ";
   $listobject->querystring .= "    avg(a.\"APRIL\" * c.consumption) as apr, ";
   $listobject->querystring .= "    avg(a.\"MAY\") as maytotal, ";
   $listobject->querystring .= "    avg(a.\"MAY\" * c.consumption) as may, ";
   $listobject->querystring .= "    avg(a.\"JUNE\") as juntotal, ";
   $listobject->querystring .= "    avg(a.\"JUNE\" * c.consumption) as jun, ";
   $listobject->querystring .= "    avg(a.\"JULY\") as jultotal, ";
   $listobject->querystring .= "    avg(a.\"JULY\" * c.consumption) as jul, ";
   $listobject->querystring .= "    avg(a.\"AUGUST\") as augtotal, ";
   $listobject->querystring .= "    avg(a.\"AUGUST\" * c.consumption) as aug, ";
   $listobject->querystring .= "    avg(a.\"SEPTEMBER\") as septotal, ";
   $listobject->querystring .= "    avg(a.\"SEPTEMBER\" * c.consumption) as sep, ";
   $listobject->querystring .= "    avg(a.\"OCTOBER\") as octtotal, ";
   $listobject->querystring .= "    avg(a.\"OCTOBER\" * c.consumption) as oct, ";
   $listobject->querystring .= "    avg(a.\"NOVEMBER\") as novtotal, ";
   $listobject->querystring .= "    avg(a.\"NOVEMBER\" * c.consumption) as nov, ";
   $listobject->querystring .= "    avg(a.\"DECEMBER\") as dectotal, ";
   $listobject->querystring .= "    avg(a.\"DECEMBER\" * c.consumption) as dec ";
   $listobject->querystring .= " from annual_data as a, vwuds_measuring_point as b, waterusetype as c  ";
   $listobject->querystring .= " WHERE a.\"ACTION\" = 'WL' ";
   $listobject->querystring .= "    and a.\"MPID\" = b.\"MPID\" ";
   $listobject->querystring .= "    and b.\"TYPE\" = 'SW' ";
   $listobject->querystring .= "    and a.\"YEAR\" >= $startyear ";
   $listobject->querystring .= "    and a.\"YEAR\" <= $endyear ";
   $listobject->querystring .= "    and b.\"CAT_MP\" = c.typeabbrev ";
   $listobject->querystring .= "    and contains(geomFromText('$wktshape', 4326), b.the_geom) ";
   if ($debug) { 
      $output['message'] .= "$listobject->querystring ; <br>"; 
   }
   $listobject->performQuery();
   $totalannual = $listobject->getRecordValue(1,'totalannual');
   $totalconsumptive = $listobject->getRecordValue(1,'totalconsumptive');
   
   $output['totalannual'] = $totalannual;
   $output['totalconsumptive'] = $totalconsumptive;
   $output['records'] = $listobject->queryrecords;
   
   return $output;
}

function getTotalAnnualSurfaceWithdrawalByWKT($listobject, $startyear, $endyear, $wktshape, $debug) {
   
   # gets maximum capacity of a measuring point
   # the table vwuds_measuring_point has a column "CAPACITY" which should hold this 
   # value in MGD, however, as of 9/25/2007, this value is null in all cases
   # so, we default to querying the annual_data table to estimate this value
   # we estimate the value based on the maximum reported daily withdrawal (MAXDAY) in MGD
   # or the largest single month (in MG) divided by the number of days in that month
   # whichever is largest
   $output = array();
   $output['message'] = '';
   
   $listobject->querystring = "  select a.\"YEAR\" as thisyear, sum(a.\"ANNUAL/365\") as totalannual, ";
   $listobject->querystring .= "    sum(a.\"ANNUAL/365\" * c.consumption) as totalconsumptive, ";
   $listobject->querystring .= "    sum(a.\"JANUARY\"/31.0) as jan, ";
   $listobject->querystring .= "    sum(a.\"FEBRUARY\"/28.0) as feb, ";
   $listobject->querystring .= "    sum(a.\"MARCH\"/31.0) as mar, ";
   $listobject->querystring .= "    sum(a.\"APRIL\"/30.0) as apr, ";
   $listobject->querystring .= "    sum(a.\"MAY\"/31.0) as may, ";
   $listobject->querystring .= "    sum(a.\"JUNE\"/30.0) as jun, ";
   $listobject->querystring .= "    sum(a.\"JULY\"/31.0) as jul, ";
   $listobject->querystring .= "    sum(a.\"AUGUST\"/31.0) as aug, ";
   $listobject->querystring .= "    sum(a.\"SEPTEMBER\"/30.0) as sep, ";
   $listobject->querystring .= "    sum(a.\"OCTOBER\"/31.0) as oct, ";
   $listobject->querystring .= "    sum(a.\"NOVEMBER\"/30.0) as nov, ";
   $listobject->querystring .= "    sum(a.\"DECEMBER\"/31.0) as dec ";
   $listobject->querystring .= " from annual_data as a, vwuds_measuring_point as b, waterusetype as c ";
   $listobject->querystring .= " WHERE a.\"ACTION\" = 'WL' ";
   $listobject->querystring .= "    and a.\"MPID\" = b.\"MPID\" ";
   $listobject->querystring .= "    and b.\"TYPE\" = 'SW' ";
   $listobject->querystring .= "    and a.\"YEAR\" >= $startyear ";
   $listobject->querystring .= "    and a.\"YEAR\" <= $endyear ";
   $listobject->querystring .= "    and b.\"CAT_MP\" = c.typeabbrev ";
   $listobject->querystring .= "    and contains(geomFromText('$wktshape', 4326), b.the_geom) ";
   $listobject->querystring .= " group by a.\"YEAR\" ";
   $listobject->querystring .= " order by a.\"YEAR\" ";
   if ($debug) { 
      $output['message'] .= "$listobject->querystring ; <br>"; 
   }
   $listobject->performQuery();
   $maxday = $listobject->queryrecords;
   
   $output['maxdayrecs'] = $maxday;
   
   return $output;
}


function getTotalSurfaceCATWithdrawalByWKT($listobject, $startyear, $endyear, $wktshape, $debug) {
   
   # gets maximum capacity of a measuring point
   # the table vwuds_measuring_point has a column "CAPACITY" which should hold this 
   # value in MGD, however, as of 9/25/2007, this value is null in all cases
   # so, we default to querying the annual_data table to estimate this value
   # we estimate the value based on the maximum reported daily withdrawal (MAXDAY) in MGD
   # or the largest single month (in MG) divided by the number of days in that month
   # whichever is largest
   $output = array();
   $output['message'] = '';
   
   $listobject->querystring = "  select b.\"CAT_MP\" as category, sum(a.\"ANNUAL/365\") as totalannual, ";
   $listobject->querystring .= "    sum(a.\"ANNUAL/365\" * c.consumption) as totalconsumptive ";
   $listobject->querystring .= " from annual_data as a, vwuds_measuring_point as b, waterusetype as c ";
   $listobject->querystring .= " WHERE a.\"ACTION\" = 'WL' ";
   $listobject->querystring .= "    and a.\"MPID\" = b.\"MPID\" ";
   $listobject->querystring .= "    and b.\"TYPE\" = 'SW' ";
   $listobject->querystring .= "    and a.\"YEAR\" >= $startyear ";
   $listobject->querystring .= "    and a.\"YEAR\" <= $endyear ";
   $listobject->querystring .= "    and b.\"CAT_MP\" = c.typeabbrev ";
   $listobject->querystring .= "    and contains(geomFromText('$wktshape', 4326), b.the_geom) ";
   $listobject->querystring .= " group by b.\"CAT_MP\" ";
   if ($debug) { 
      $output['message'] .= "$listobject->querystring ; <br>"; 
   }
   $listobject->performQuery();
   $maxday = $listobject->queryrecords;
   
   $output['maxdayrecs'] = $maxday;
   
   return $output;
}

function getTotalAnnualSurfaceCATWithdrawalByWKT($listobject, $startyear, $endyear, $wktshape, $debug) {
   
   # gets maximum capacity of a measuring point
   # the table vwuds_measuring_point has a column "CAPACITY" which should hold this 
   # value in MGD, however, as of 9/25/2007, this value is null in all cases
   # so, we default to querying the annual_data table to estimate this value
   # we estimate the value based on the maximum reported daily withdrawal (MAXDAY) in MGD
   # or the largest single month (in MG) divided by the number of days in that month
   # whichever is largest
   $output = array();
   $output['message'] = '';
   
   $listobject->querystring = "  select b.\"CAT_MP\" as category, a.\"YEAR\" as thisyear, ";
   $listobject->querystring .= "    sum(a.\"ANNUAL/365\") as totalannual, ";
   $listobject->querystring .= "    sum(a.\"ANNUAL/365\" * c.consumption) as totalconsumptive, ";
   $listobject->querystring .= "    sum(a.\"JANUARY\"/31.0) as jan, ";
   $listobject->querystring .= "    sum(a.\"FEBRUARY\"/28.0) as feb, ";
   $listobject->querystring .= "    sum(a.\"MARCH\"/31.0) as mar, ";
   $listobject->querystring .= "    sum(a.\"APRIL\"/30.0) as apr, ";
   $listobject->querystring .= "    sum(a.\"MAY\"/31.0) as may, ";
   $listobject->querystring .= "    sum(a.\"JUNE\"/30.0) as jun, ";
   $listobject->querystring .= "    sum(a.\"JULY\"/31.0) as jul, ";
   $listobject->querystring .= "    sum(a.\"AUGUST\"/31.0) as aug, ";
   $listobject->querystring .= "    sum(a.\"SEPTEMBER\"/30.0) as sep, ";
   $listobject->querystring .= "    sum(a.\"OCTOBER\"/31.0) as oct, ";
   $listobject->querystring .= "    sum(a.\"NOVEMBER\"/30.0) as nov, ";
   $listobject->querystring .= "    sum(a.\"DECEMBER\"/31.0) as dec ";
   $listobject->querystring .= " from annual_data as a, vwuds_measuring_point as b, waterusetype as c ";
   $listobject->querystring .= " WHERE a.\"ACTION\" = 'WL' ";
   $listobject->querystring .= "    and a.\"MPID\" = b.\"MPID\" ";
   $listobject->querystring .= "    and b.\"TYPE\" = 'SW' ";
   $listobject->querystring .= "    and a.\"YEAR\" >= $startyear ";
   $listobject->querystring .= "    and a.\"YEAR\" <= $endyear ";
   $listobject->querystring .= "    and b.\"CAT_MP\" = c.typeabbrev ";
   $listobject->querystring .= "    and contains(geomFromText('$wktshape', 4326), b.the_geom) ";
   $listobject->querystring .= " group by b.\"CAT_MP\", a.\"YEAR\" ";
   $listobject->querystring .= " order by b.\"CAT_MP\", a.\"YEAR\" ";
   if ($debug) { 
      $output['message'] .= "$listobject->querystring ; <br>"; 
   }
   $listobject->performQuery();
   $maxday = $listobject->queryrecords;
   
   $output['totalcatrecs'] = $maxday;
   
   return $output;
}

function getMaxWithdrawalCapacityByWKT($listobject, $wktshape, $debug) {
   
   # gets maximum capacity of a measuring point
   # the table vwuds_measuring_point has a column "CAPACITY" which should hold this 
   # value in MGD, however, as of 9/25/2007, this value is null in all cases
   # so, we default to querying the annual_data table to estimate this value
   # we estimate the value based on the maximum reported daily withdrawal (MAXDAY) in MGD
   # or the largest single month (in MG) divided by the number of days in that month
   # whichever is largest
   
   $listobject->querystring = "  select sum(maxday) as maxday from ";
   $listobject->querystring .= " (select a.\"MPID\" as mpid, ";
   $listobject->querystring .= "    MAX(";
   $listobject->querystring .= "       GREATEST(\"MAXDAY\", ";
   $listobject->querystring .= "       \"JANUARY\"/31.0, \"FEBRUARY\"/28.0, \"MARCH\"/31.0, ";
   $listobject->querystring .= "       \"APRIL\"/30.0, \"MAY\"/31.0, \"JUNE\"/30.0, \"JULY\"/31.0, ";
   $listobject->querystring .= "       \"AUGUST\"/31.0, \"SEPTEMBER\"/30.0, \"OCTOBER\"/31.0, ";
   $listobject->querystring .= "       \"NOVEMBER\"/30.0, \"DECEMBER\"/31.0) ";
   $listobject->querystring .= "    ) as maxday";
   $listobject->querystring .= " from annual_data as a, vwuds_measuring_point as b ";
   $listobject->querystring .= " WHERE a.\"ACTION\" = 'WL' ";
   $listobject->querystring .= "    and a.\"MPID\" = b.\"MPID\" ";
   $listobject->querystring .= "    and contains(geomFromText('$wktshape', 4326), b.the_geom) ";
   $listobject->querystring .= " GROUP BY a.\"MPID\" ";
   $listobject->querystring .= " ) as a ";
   if ($debug) { 
      print("$listobject->querystring ; <br>"); 
   }
   $listobject->performQuery();
   $maxday = $listobject->getRecordValue(1,'maxday');
   
   return $maxday;
}


function getUserMPIDsByWKT($listobject, $wktshape, $mptypes, $mpactions, $debug) {
   
   if (is_array($mptypes)) {
      if (count($mptypes) > 0) {
         $mptypes = "'" . join("','", $mptypes) . "'";
      }
   }
   if (is_array($mpactions)) {
      if (count($mpactions) > 0) {
         $mpactions = "'" . join("','", $mpactions) . "'";
      }
   }
   
   if ( (strlen($mptypes) > 0) and ($mptypes <> -1)) {
      $mptypecond = " b.\"TYPE\" in ($mptypes) ";
   } else {
      $mptypecond = " ( 1 = 1) ";
   }
   
   if ( (strlen($mpactions) > 0) and ($mpactions <> -1)) {
      $mpactioncond = " b.\"ACTION\" in ($mpactions) ";
   } else {
      $mpactioncond = " ( 1 = 1) ";
   }
   
   $retvals = array();
   $retvals['debug'] = '';
   $listobject->querystring = "  select b.\"USERID\", b.\"MPID\" as mpid, b.\"ACTION\" as mpaction, b.\"TYPE\" as mptype ";
   $listobject->querystring .= " from vwuds_measuring_point as b ";
   $listobject->querystring .= " WHERE $mptypecond ";
   $listobject->querystring .= "    and $mpactioncond ";
   $listobject->querystring .= "    and contains(geomFromText('$wktshape', 4326), b.the_geom) ";
   $listobject->querystring .= " GROUP BY b.\"USERID\", b.\"MPID\", b.\"ACTION\", b.\"TYPE\" ";
   if ($debug) { 
      $retvals['debug'] .= "$listobject->querystring ; <br>";
   }
   $listobject->performQuery();
   $retvals['records'] = $listobject->queryrecords;
   $retvals['mpids'] = array();
   $retvals['userids'] = array();
   foreach ($retvals['records'] as $thisrec) {
      array_push($retvals['mpids'], $thisrec['mpid']);
      if (!in_array($thisrec['userid'], $retvals['userids'])) {
         array_push($retvals['userids'], $thisrec['userid']);
      }
   }
   
   return $retvals;   
}


function getUserMeasuringPoints($listobject, $userids, $mptypes, $debug) {
   
   
}

function getUserPeriodSummary($listobject, $userids, $mptypes, $debug) {
   

   $listobject->querystring = "  SELECT a.\"MPID\" as mpid, a.\"USERID\" as userid, ";
   $listobject->querystring .= "   a.\"YEAR\" as thisyear, a.\"ANNUAL\" as annual, ";
   $listobject->querystring .= "   b.minannual, b.maxannual, b.meanannual, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "    WHEN b.meanannual = 0 THEN -1 ";
   $listobject->querystring .= "    ELSE a.\"ANNUAL\" / b.meanannual ";
   $listobject->querystring .= "    END as pct_diff, b.numrecs, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "    WHEN b.meanannual = 0 THEN -1 ";
   $listobject->querystring .= "    ELSE a.\"ANNUAL\" / b.maxannual ";
   $listobject->querystring .= "    END as max_diff ";
   $listobject->querystring .= " FROM annual_data AS a, ";
   $listobject->querystring .= " (   select b.\"MPID\",  min(b.\"ANNUAL\") as minannual, max(b.\"ANNUAL\") as maxannual,";
   $listobject->querystring .= "     avg( b.\"ANNUAL\" ) as meanannual, count(b.\"ANNUAL\") as numrecs ";
   $listobject->querystring .= "     from annual_data as b ";
   $listobject->querystring .= "     where b.\"YEAR\" >= ($searchyear - $numyears) and b.\"YEAR\" < $searchyear ";
   $listobject->querystring .= "        and b.\"ANNUAL\" is not null ";
   $listobject->querystring .= "        and b.\"ACTION\" = 'WL' ";
   $listobject->querystring .= "        and b.\"MPID\" = '$mpid' ";
   $listobject->querystring .= "     group by b.\"MPID\"";
   $listobject->querystring .= " )  AS b";
   $listobject->querystring .= " WHERE a.\"MPID\" = '$mpid' ";
   $listobject->querystring .= " and a.\"YEAR\" = $searchyear ";
   $listobject->querystring .= " and a.\"ACTION\" = 'WL' ";
   $listobject->querystring .= " and a.\"ANNUAL\" is not null ";
   $listobject->querystring .= " ORDER BY max_diff DESC ";
   $listobject->performQuery();

   print("<hr><b>Selected Record</b><br>");
   $dbrecs = $listobject->queryrecords;
   print("<table>");
   print("<tr>");
   print("<td>MPID</td><td>User ID</td><td>Year</td><td>Annual ($searchyear)</td><td>Mean Previous</td><td>Min. Previous</td><td>Max Previous</td><td>Pct. of Mean</td><td>Pct. of Max</td><td>Number of records</td>");
   print("</tr>");
   foreach ($dbrecs as $thisrec) {
      $mpid = $thisrec['mpid'];
      $userid = $thisrec['userid'];
      $thisyear = $thisrec['thisyear'];
      $annual = $thisrec['annual'];
      $meanannual = number_format($thisrec['meanannual'],3);
      $minannual = $thisrec['minannual'];
      $pct_diff = number_format(100.0 * $thisrec['pct_diff'], 2);
      $maxannual = $thisrec['maxannual'];
      $max_diff = number_format(100.0 * $thisrec['max_diff'], 2);
      $numrecs = $thisrec['numrecs'];
      print( "<tr><td>$mpid</td>");
      print( "<td>$userid</td>");
      print("<td>$thisyear</td><td>$annual</td><td>$meanannual</td><td>$minannual</td><td>$maxannual</td><td>$pct_diff %</td><td>$max_diff %</td><td>$numrecs</td></tr>");

   }
   print("</table>");


   print("<hr><b>Annual Totals by UserID</b><br>");
   $listobject->querystring = "  SELECT a.\"USERID\" as userid, a.\"YEAR\", ";
   $listobject->querystring .= "   sum(a.\"ANNUAL\") as annual_total ";
   $listobject->querystring .= " FROM annual_data AS a ";
   $listobject->querystring .= " WHERE a.\"USERID\" = '$userid' ";
   $listobject->querystring .= " and a.\"YEAR\" >= ( $searchyear - $numyears) ";
   $listobject->querystring .= " and a.\"ACTION\" = 'WL' ";
   $listobject->querystring .= " GROUP BY a.\"USERID\", a.\"YEAR\" ";
   $listobject->querystring .= " ORDER BY a.\"YEAR\" ";
   $listobject->performQuery();

   $dbrecs = $listobject->queryrecords;

   $listobject->showList();

   print("<hr><b>Annual Records by MPID</b><br>");
   $listobject->querystring = "  SELECT a.\"MPID\" as mpid, a.\"USERID\" as userid, ";
   $listobject->querystring .= "   a.\"YEAR\" as thisyear, a.\"ANNUAL\" as annual ";
   $listobject->querystring .= " FROM annual_data AS a ";
   $listobject->querystring .= " WHERE a.\"USERID\" = '$userid' ";
   $listobject->querystring .= " and a.\"YEAR\" >= ( $searchyear - $numyears) ";
   $listobject->querystring .= " and a.\"ACTION\" = 'WL' ";
   $listobject->querystring .= " ORDER BY a.\"MPID\", a.\"YEAR\" ";
   $listobject->performQuery();

   $dbrecs = $listobject->queryrecords;

   $listobject->showList();
   
}

function getMPPeriodSummary($listobject, $mpids, $startyear, $endyear) {
   
   
}


function projectWaterUse($listobject, $totalcatrecs, $targetyears, $projectid, $debug) {

   # this is specifically for use with  the land use projection feature
   # and will not give accurate results for population interpolations
   # project the entire groups totals (set negative to zero)
   # then project the individual subshed units (set negative to zero)
   # then scale the individual projections based on the ratio of the
   # (group projection) / (the sum of the subshed units)
   # currently this operates on the scale of subshed, not lrseg
   # later it may be adapted to this finer lrseg scale.

   
   
   # creates a temp table with our data
   # combine sources - using the column linkpop. This column is set to be equal to the source id, unless it
   # is something like phytase version of another creature.
   $listobject->querystring = "  create temp table tmp_catuse ";
   $listobject->querystring .= " (category varchar(12), thisyear integer, totalannual float8, totalconsumptive float8)";
   if ($debug) { 
      $output['message'] .= "<br>$listobject->querystring<br>"; 
   }
   $listobject->performQuery();
   foreach ($totalcatrecs as $thisrec) {
      $cat = $thisrec['category'];
      $thisyear = $thisrec['thisyear'];
      $ta = $thisrec['totalannual'];
      $tc = $thisrec['totalconsumptive'];
      $listobject->querystring = "  insert into tmp_catuse (category, thisyear, totalannual, totalconsumptive ) ";
      $listobject->querystring .= " values ('$cat', $thisyear, $ta, $tc ) ";
      if ($debug) {
         $output['message'] .= "$listobject->querystring";
      }
      $listobject->performQuery();
   }
   if ($debug) {
      $listobject->querystring = "select * from tmp_catuse";
      $listobject->performQuery();
      $output['message'] .= "$listobject->querystring";
   }

   # now, get a list of years to facilitate use and visualization of the extrapolation output
   $listobject->querystring = "select min(thisyear) as minyr, max(thisyear) as maxyr from tmp_catuse";
   $listobject->performQuery();
   $minyr = $listobject->getRecordValue(1,'minyr');
   $maxyr = $listobject->getRecordValue(1,'maxyr');
   $yrar = array($minyr, $maxyr);
   $tar = array();
   if (strlen($targetyears) > 0) {
      $tar = split(",", $targetyears);
   }
   $allyrs = array_merge($yrar, $tar);
   $loyr = min($allyrs);
   $hiyr = max($allyrs);
   $exyrs = '';
   $exdel = '';
   for ($j = $loyr; $j <= $hiyr; $j++) {
      $exyrs .= "$exdel" . $j;
      $exdel = ',';
   }
   if ($debug) {
      $output['message'] .= "Years: $exyrs<br>";
   }

   # create the best fit table for the group as a whole
   genericExtrap($listobject, $exyrs, 'tmp_catuse', 'tmp_grpextrap', 'thisyear', 'category', 'totalconsumptive', 0.0, 1, 0, 0, 0, $debug, 1, 1);

   # now eliminate any < 0 values
   $listobject->querystring = "update tmp_grpextrap set totalconsumptive = 0 where totalconsumptive < 0.0 ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   
}


function graphBestFitUse($listobject, $goutdir, $goutpath, $targetyears, $debug) {

   #$lus = join(", ", $sources);
   # create basic conditional clauses
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $srccond = " sourceid in ($srclist) ";
      $asrccond = " a.sourceid in ($srclist) ";
   } else {
      $srccond = ' (1 = 1) ';
      $asrccond = ' (1 = 1) ';
   }

   # get records for all input years
   $listobject->querystring = " select  ";
   $listobject->querystring .= "    CASE  ";
   $listobject->querystring .= "       WHEN b.thisyear IS NULL THEN a.thisyear ";
   $listobject->querystring .= "       ELSE b.thisyear ";
   $listobject->querystring .= "    END as thisyear, ";
   $listobject->querystring .= "    CASE  ";
   $listobject->querystring .= "       WHEN a.thisyear IS NULL THEN 0.0 ";
   $listobject->querystring .= "       ELSE sum(a.totalconsumptive) ";
   $listobject->querystring .= "    END as totalconsumptive ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= "    ( select thisyear, totalconsumptive ";
   $listobject->querystring .= "      from tmp_catuse ";
   $listobject->querystring .= "    ) as a  ";
   $listobject->querystring .= " full join  ";
   $listobject->querystring .= " (select thisyear from tmp_grpextrap group by thisyear) as b ";
   $listobject->querystring .= " on (a.thisyear = b.thisyear) ";
   $listobject->querystring .= " group by a.thisyear, b.thisyear ";
   $listobject->querystring .= " order by b.thisyear ";
   if ($debug) { print("$listobject->querystring <br>"); }
   $listobject->performquery();
   #$listobject->showList();
   $lurecs = $listobject->queryrecords;

   # get all best-fit years
   $listobject->querystring = "select thisyear, sum(totalconsumptive) as totalconsumptive from tmp_grpextrap group by thisyear order by thisyear";
   $listobject->performquery();
   $bfrecs = $listobject->queryrecords;

   # get ONLY requested Best-Fit years
   if (strlen($targetyears) > 0) {
      $listobject->querystring = " select  ";
      $listobject->querystring .= "    CASE  ";
      $listobject->querystring .= "       WHEN b.thisyear IS NULL THEN a.thisyear ";
      $listobject->querystring .= "       ELSE b.thisyear ";
      $listobject->querystring .= "    END as thisyear, ";
      $listobject->querystring .= "    CASE  ";
      $listobject->querystring .= "       WHEN a.thisyear IS NULL THEN 0.0 ";
      $listobject->querystring .= "       ELSE a.totalconsumptive ";
      $listobject->querystring .= "    END as totalconsumptive ";
      $listobject->querystring .= " from ";
      $listobject->querystring .= "    (select thisyear, sum(totalconsumptive) as totalconsumptive ";
      $listobject->querystring .= "     from tmp_grpextrap ";
      $listobject->querystring .= "     where thisyear in ($targetyears) ";
      $listobject->querystring .= "     group by thisyear";
      $listobject->querystring .= "    ) as a  ";
      $listobject->querystring .= " full join  ";
      $listobject->querystring .= " (select thisyear from tmp_grpextrap group by thisyear) as b ";
      $listobject->querystring .= " on (a.thisyear = b.thisyear) ";
      $listobject->querystring .= " order by b.thisyear ";
      if ($debug) { print("$listobject->querystring <br>"); }
      $listobject->performquery();
      if ($debug) { $listobject->showList(); }
      $extraprecs = $listobject->queryrecords;
   }

   $lugraph = array();
   $lugraph['graphrecs'] = $lurecs;
   $lugraph['xcol'] = 'thisyear';
   $lugraph['ycol'] = 'totalconsumptive';
   $lugraph['color'] = 'orange';
   $lugraph['ylegend'] = 'Historic';

   # a totally trasnparent (outline only) display of the best fit line
   $bfgraph = array();
   $bfgraph['graphrecs'] = $bfrecs;
   $bfgraph['xcol'] = 'thisyear';
   $bfgraph['ycol'] = 'totalconsumptive';
   $bfgraph['color'] = 'brown';
   $bfgraph['ylegend'] = 'Best Fit';
   $bfgraph['alpha'] = 1.0;

   # selected records to extrapolate from the best fit in blue
   $extrapgraph = array();
   $extrapgraph['graphrecs'] = $extraprecs;
   $extrapgraph['xcol'] = 'thisyear';
   $extrapgraph['ycol'] = 'totalconsumptive';
   $extrapgraph['color'] = 'blue';
   $extrapgraph['ylegend'] = 'Targeted';
   $extrapgraph['alpha'] = 0.3;
   $multibar = array('title'=>"Historic versus Best Fit for: $lus", 'xlabel'=>'Year', 'num_xlabels'=>3, 'bargraphs'=>array($lugraph, $bfgraph, $extrapgraph));

   $bfgraph = showGenericMultiBar($goutdir, $goutpath, $multibar, $debug);
   return $bfgraph;

}
?>