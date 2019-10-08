#!/bin/sh
# Set up scripts
# dev
rm  /var/www/html/om/fn_copy_element.php
ln -s /opt/model/om-dev/php/src/fn_copy_element.php /var/www/html/om/fn_copy_element.php
rm  /var/www/html/om/run_shakeTree.php
ln -s /opt/model/om-dev/php/src/run_shakeTree.php /var/www/html/om/run_shakeTree.php
rm  /var/www/html/om/lib_verify.php
ln -s /opt/model/om-dev/php/src/lib_verify.php /var/www/html/om/lib_verify.php
rm  /var/www/html/om/fn_copy_group_subcomp.php
ln -s /opt/model/om-dev/php/src/fn_copy_group_subcomp.php /var/www/html/om/fn_copy_group_subcomp.php
rm  /var/www/html/om/fn_batchedit_broadcast_matrix.php
ln -s /opt/model/om-dev/php/src/fn_batchedit_broadcast_matrix.php /var/www/html/om/fn_batchedit_broadcast_matrix.php
rm /var/www/html/lib/lib_hydrology.php
ln -s /opt/model/om-dev/php/lib/lib_hydrology.php /var/www/html/lib/lib_hydrology.php
# live
rm /var/www/html/om/fn_copy_element.php
ln -s /opt/model/om/php/src/fn_copy_element.php /var/www/html/om/fn_copy_element.php
rm /var/www/html/om/fn_copy_group_subcomp.php
ln -s /opt/model/om/php/src/fn_copy_group_subcomp.php /var/www/html/om/fn_copy_group_subcomp.php
rm /var/www/html/om/run_shakeTree.php
ln -s /opt/model/om/php/src/run_shakeTree.php /var/www/html/om/run_shakeTree.php
rm /var/www/html/om/lib_verify.php
ln -s /opt/model/om/php/src/lib_verify.php /var/www/html/om/lib_verify.php
rm /var/www/html/om/fn_batchedit_broadcast_matrix.php
ln -s /opt/model/om/php/src/fn_batchedit_broadcast_matrix.php /var/www/html/om/fn_batchedit_broadcast_matrix.php
rm /var/www/html/lib/lib_hydrology.php
ln -s /opt/model/om/php/lib/lib_hydrology.php /var/www/html/lib/lib_hydrology.php
