COPY (
  select a.hydroid, 'dh_properties' as entity_type, 
    b.pid as featureid, 'om_class_Equation' as varkey,
    'consumption' as propname, 
    c.pid,
    CASE
      WHEN a.ftype = 'agriculture' THEN '1.0'
      WHEN a.ftype = 'manufacturing' THEN '0.1'
      WHEN a.ftype = 'municipal' THEN 'consumption_monthly'
      WHEN a.ftype = 'industrial' THEN '0.1'
      WHEN a.ftype = 'commercial' THEN '0.1'
      WHEN a.ftype = 'irrigation' THEN '1.0'
      WHEN a.ftype = 'other' THEN '0.1'
      WHEN a.ftype = 'nuclearpower' THEN '0.015'
      WHEN a.ftype = 'fossilpower' THEN '0.015'
      WHEN a.ftype = 'unknown' THEN '0.1'
      WHEN a.ftype = 'mining' THEN '0.75'
    END as propcode,
    0.0 as propvalue
  from dh_feature as a 
  left outer join dh_properties as b 
    on (
      a.hydroid = b.featureid 
      and b.entity_type = 'dh_feature' 
      and b.varid in (select hydroid from dh_variabledefinition where varkey = 'om_water_system_element') 
      and b.propcode = 'vahydro-1.0'
    ) 
   left outer join dh_properties as c 
  on (
    c.featureid = b.pid
    and c.entity_type = 'dh_properties'
    and c.propname = 'consumption'
  )
  where a.bundle = 'facility'
    and b.pid is not null 
) to '/tmp/fac-consumption-default.txt' with CSV header delimiter E'\t'
;
