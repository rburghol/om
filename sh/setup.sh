#!/bin/sh
# Set up scripts
# All
sudo chown www-data:allmodelers www/om/cache

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
rm /var/www/html/lib/lib_wooomm.php
ln -s /opt/model/om-dev/php/lib/lib_wooomm.php /var/www/html/lib/lib_wooomm.php
rm /var/www/html/lib/lib_wooomm.USGS.php
ln -s /opt/model/om-dev/php/lib/lib_wooomm.USGS.php /var/www/html/lib/lib_wooomm.USGS.php
rm /var/www/html/lib/lib_usgs.php
ln -s /opt/model/om-dev/php/lib/lib_usgs.php /var/www/html/lib/lib_usgs.php
rm /var/www/html/lib/lib_wooomm.cbp.php
ln -s /opt/model/om-dev/php/lib/lib_wooomm.cbp.php /var/www/html/lib/lib_wooomm.cbp.php
rm /var/www/html/lib/lib_wooomm.wsp.php
ln -s /opt/model/om-dev/php/lib/lib_wooomm.wsp.php /var/www/html/lib/lib_wooomm.wsp.php
rm /var/www/html/om/adminsetup.php
ln -s /opt/model/om-dev/php/src/adminsetup.php /var/www/html/om/adminsetup.php
rm /var/www/html/om/set_subprop.php
ln -s /opt/model/om-dev/php/src/set_subprop.php /var/www/html/om/set_subprop.php
rm /var/www/html/om/get_modelStatus.php
ln -s /opt/model/om-dev/php/src/get_modelStatus.php /var/www/html/om/get_modelStatus.php
rm /var/www/html/om/get_model.php
ln -s /opt/model/om-dev/php/src/get_model.php /var/www/html/om/get_model.php
rm /var/www/html/om/get_statusTree.php
ln -s /opt/model/om-dev/php/src/get_statusTree.php /var/www/html/om/get_statusTree.php
rm /var/www/html/om/xajax_modeling.element.php
ln -s /opt/model/om-dev/php/src/xajax_modeling.element.php /var/www/html/om/xajax_modeling.element.php
rm /var/www/html/om/fn_message_model.php
ln -s /opt/model/om-dev/php/src/fn_message_model.php /var/www/html/om/fn_message_model.php
rm /var/www/html/om/fn_addObjectLink.php
ln -s /opt/model/om-dev/php/src/fn_addObjectLink.php /var/www/html/om/fn_addObjectLink.php

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
rm /var/www/html/lib/lib_wooomm.php
ln -s /opt/model/om/php/lib/lib_wooomm.php /var/www/html/lib/lib_wooomm.php
rm /var/www/html/lib/lib_wooomm.USGS.php
ln -s /opt/model/om/php/lib/lib_wooomm.USGS.php /var/www/html/lib/lib_wooomm.USGS.php
rm /var/www/html/lib/lib_usgs.php
ln -s /opt/model/om/php/lib/lib_usgs.php /var/www/html/lib/lib_usgs.php
rm /var/www/html/lib/lib_wooomm.cbp.php
ln -s /opt/model/om/php/lib/lib_wooomm.cbp.php /var/www/html/lib/lib_wooomm.cbp.php
rm /var/www/html/lib/lib_wooomm.wsp.php
ln -s /opt/model/om/php/lib/lib_wooomm.wsp.php /var/www/html/lib/lib_wooomm.wsp.php
rm /var/www/html/om/adminsetup.php
ln -s /opt/model/om-dev/php/src/adminsetup.php /var/www/html/om/adminsetup.php
rm /var/www/html/om/set_subprop.php
ln -s /opt/model/om/php/src/set_subprop.php /var/www/html/om/set_subprop.php
rm /var/www/html/om/get_modelStatus.php
ln -s /opt/model/om/php/src/get_modelStatus.php /var/www/html/om/get_modelStatus.php
rm /var/www/html/om/get_model.php
ln -s /opt/model/om/php/src/get_model.php /var/www/html/om/get_model.php
rm /var/www/html/om/get_statusTree.php
ln -s /opt/model/om/php/src/get_statusTree.php /var/www/html/om/get_statusTree.php
rm /var/www/html/om/xajax_modeling.element.php
ln -s /opt/model/om/php/src/xajax_modeling.element.php /var/www/html/om/xajax_modeling.element.php
rm /var/www/html/om/fn_message_model.php
ln -s /opt/model/om/php/src/fn_message_model.php /var/www/html/om/fn_message_model.php
rm /var/www/html/om/fn_addObjectLink.php
ln -s /opt/model/om/php/src/fn_addObjectLink.php /var/www/html/om/fn_addObjectLink.php
