#!/bin/sh


# migrate all code from local dir to NAS
rsync -auP  --no-group --no-perms /backup/omdata/ /media/NAS/omdata/
