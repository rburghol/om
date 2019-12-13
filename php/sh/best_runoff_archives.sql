select a.custom2 as riverseg,
  c.elementid as parentid, 
  d.elementid as oldelement, 
  rf.src_file
from scen_model_element as a 
left outer join map_model_linkages as l1   
on (
  a.elementid = l1.dest_id 
  and l1.linktype = 1
) 
left outer join scen_model_element as c  
on (c.elementid = l1.src_id)  
left outer join map_model_linkages as l2   
on (
  c.elementid = l2.dest_id 
  and l2.linktype = 1
) 
left outer join scen_model_element as d  
on (d.elementid = l2.src_id and d.custom1 = 'va_hydro')  
left outer join tmp_runoff_archives as rf
on (rf.elementid = d.elementid)
where c.custom1 = 'cova_runoff' 
  and a.scenarioid = 37 
  and d.elementid is not null 
  and src_file is not null
;
