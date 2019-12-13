create table tmp_runoff_archives as (
  select el.elementid, 
    CASE
      WHEN r2.elementid is not null THEN r2.output_file
      WHEN r21.elementid is not null THEN r21.output_file
      WHEN r22.elementid is not null THEN r22.output_file
      WHEN r11.elementid is not null THEN r11.output_file
    END as src_file
  FROM scen_model_element as el
  left outer join scen_model_run_elements  as r11  
  on (
    el.elementid = r11.elementid
      and r11.runid = 11 
      and r11.starttime <= '1984-01-01'
      and r11.endtime >= '2014-12-31'
  )
  left outer join scen_model_run_elements  as r2
  on (
    el.elementid = r2.elementid
    and r2.runid = 2 
    and r2.starttime <= '1984-01-01'
    and r2.endtime >= '2005-12-31'
  )
  left outer join scen_model_run_elements  as r21
  on (
    el.elementid = r21.elementid
    and r21.runid = 21 
      and r21.starttime <= '1984-01-01'
      and r21.endtime >= '2005-12-31'
  )
  left outer join scen_model_run_elements  as r22
  on (
    el.elementid = r22.elementid
    and r22.runid = 22 
     and r22.starttime <= '1984-01-01'
      and r22.endtime >= '2005-12-31'
  )
  where el.scenarioid = 37
  and el.custom1 = 'va_hydro'
);