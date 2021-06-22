# File example  
#!/bin/sh

t=4988636
if [ $# -lt 2 ]; then
  echo 1>&2 "This is used to copy from a single source template to a files list of property pids"
  echo 1>&2 "Usage: om_copy_file_props.sh propname file [template pid]"
  exit 2
fi
p=$1
f=$2
if [ $# -gt 2 ]; then
  t=$3
fi

echo "Loading model pid list from $f"
for i in `cat $f`; do
  # copy new flowby lookup from template
  echo "Trying drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $t dh_properties $i $p"
  drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $t dh_properties $i $p
done
