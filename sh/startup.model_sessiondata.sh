#!/bin/sh
# shell
pgpath="/data/postgres/9.5"
bindir="/usr/lib/postgresql/9.5/bin"
pguser="postgres"
db_port=5433
# Startup As of 6/6/2019 use this:
$bindir/pg_ctl -D $pgpath/sessiondata restart -l logfile
