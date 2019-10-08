#!/bin/sh
# Set up scripts
# dev
ln -s /opt/model/om-dev/php/src/fn_copy_element.php /var/www/html/om/fn_copy_element.php
ln -s /opt/model/om-dev/php/src/run_shakeTree.php /var/www/html/om/run_shakeTree.php
ln -s /opt/model/om-dev/php/src/fn_copy_group_subcomp.php /var/www/html/om/fn_copy_group_subcomp.php
ln -s /opt/model/om-dev/php/src/fn_batchedit_broadcast_matrix.php /var/www/html/om/fn_batchedit_broadcast_matrix.php
ln -s /opt/model/om-dev/php/lib/lib_hydrology.php /var/www/html/lib/lib_hydrology.php
# live
ln -s /opt/model/om/php/src/fn_copy_element.php /var/www/html/om/fn_copy_element.php
ln -s /opt/model/om/php/src/fn_copy_group_subcomp.php /var/www/html/om/fn_copy_group_subcomp.php
ln -s /opt/model/om/php/src/run_shakeTree.php /var/www/html/om/run_shakeTree.php
ln -s /opt/model/om/php/src/fn_batchedit_broadcast_matrix.php /var/www/html/om/fn_batchedit_broadcast_matrix.php
ln -s /opt/model/om/php/lib/lib_hydrology.php /var/www/html/lib/lib_hydrology.php
