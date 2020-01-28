 select a.name || ':' || r.name , 
    a.hydroid, r.hydrocode 
  from dh_feature as a
  left outer join dh_properties as b 
  on (a.hydroid = b.featureid
    and b.entity_type = 'dh_feature'
    and b.propcode = 'vahydro-1.0' 
    and varid in (select hydroid from dh_variabledefinition where varkey in ('om_model_element', 'om_water_system_element'))
  )
  left outer join field_data_dh_link_facility_mps as mpl
  on ( 
    mpl.dh_link_facility_mps_target_id = a.hydroid
    and mpl.bundle = 'intake'
  ) 
  left outer join dh_feature as mp
  on (
    mpl.entity_id = mp.hydroid 
    AND mp.bundle = 'intake'
  )
  left outer join field_data_dh_geofield as mpg
  on (
    mp.hydroid = mpg.entity_id 
    and mpg.entity_type = 'dh_feature'
  )
  left outer join field_data_dh_geofield as rg
  on (
    rg.deleted = rg.deleted 
    AND st_setsrid(mpg.dh_geofield_geom, 4326) && st_setsrid(rg.dh_geofield_geom, 4326)
    AND mpg.entity_id <> rg.entity_id 
    and st_contains(
      st_setsrid(rg.dh_geofield_geom, 4326), st_setsrid(mpg.dh_geofield_geom, 4326)
    )
    and rg.entity_type = 'dh_feature'
    AND rg.bundle = 'watershed' 
    and rg.entity_id in (select hydroid from dh_feature where bundle = 'watershed' and ftype = 'vahydro')
  )
  left outer join dh_feature as r 
  on (
    r.hydroid = rg.entity_id
      AND r.bundle = 'watershed'
      AND r.ftype = 'vahydro'
  )
  where a.hydroid in 
    (193261,427967,72587,394857,432280,428194,392036,427965,441596,427960,72813,428216, 427913,410846,378964,71762,73939,90432,224231,400037,432279,459548,401262,72472,453917, 379263,410706,419188,381598,72634,72414,67174,67337,71608,71645,71687,71767,71787,71807, 71807,71810,71891,71931,71977,71999,71999,72006,72023,72023,72246,72247,72273,72394,72446, 72471,72471,72495,72538,72538,72548,72578,72578,72578,72578,72603,72672,72677,72734,72739, 72930,72975,73032,73042,73049,73110,73125,73159,73199,73200,73259,73295,73310,73351,73351, 73391,73394,73537,73573,73730,73773,73969,74049,74059,74071,74154,74184,74447,74458,74461, 74466,90416,442389,442389,73394,395731,398105,428215 )
  and b.pid is null
    and (mp.fstatus is null or ( mp.fstatus not in ('inactive', 'duplicate')) )
    and mp.fstatus not in ('inactive', 'duplicate')
  order by a.name ;
  