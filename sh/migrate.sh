#!/bin/sh


# migrate all code from local dir to NAS
rsync -auP  --no-group --no-perms /backup/omdata/ /media/NAS/omdata/

# old school om libs
rsync -auP /var/www/html/images/ deq4:/var/www/html/images/
rsync -auP /var/www/html/lib/xajax deq4:/var/www/html/lib/
rsync -auP /var/www/html/lib/xajax/ deq4:/var/www/html/lib/
rsync -auP /var/www/html/lib/xajax/ deq4:/var/www/html/lib/xajax
rsync -auP /var/www/html/lib/xajax/ deq4:/var/www/html/lib/xajax
rsync -auP /var/www/html/lib/xajax/ deq4:/var/www/html/lib/xajax
rsync -auP /var/www/html/lib/xdg/ deq4:/var/www/html/lib/xdg
rsync -auP /var/www/html/lib/xdg/ deq4:/var/www/html/lib/xdg/
rsync -auP /var/www/html/lib/adg/ deq4:/var/www/html/lib/adg/
rsync -auP /var/www/html/styles/ deq4:/var/www/html/styles/
rsync -auP /var/www/html/scripts/ deq4:/var/www/html/scripts/
