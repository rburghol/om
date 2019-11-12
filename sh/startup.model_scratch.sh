#!/bin/sh
pgpath="/data/postgres/9.5"
bindir="/usr/lib/postgresql/9.5/bin"
# As of 6/6/2019
$bindir/pg_ctl -D $pgpath/scratch/ restart -l logfile.model_scratch
