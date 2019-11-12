#!/bin/sh
# shell
pgpath="/data/postgres/9.5"
bindir="/usr/lib/postgresql/9.5/bin"
pguser="postgres"
db_port=5433

mkdir $pgpath/sessiondata
$bindir/initdb -D $pgpath/sessiondata
cp $pgpath/postgresql.conf $pgpath/sessiondata/
cp $pgpath/pg_hba.conf $pgpath/sessiondata/
# edit the postgresql.conf file and set the new port
# to $db_port
nano $pgpath/sessiondata/postgresql.conf
# start it up
$bindir/pg_ctl -D $pgpath/sessiondata start -l "logfile.sessiondata"

$bindir/createdb model_sessiondata -p $db_port
echo "CREATE EXTENSION postgis;" | psql model_sessiondata -p $db_port

cat msdef.sql | psql model_sessiondata -p $db_port
plr_sql='/home/rob/src/plr/plr.sql'
cat $plr_sql | psql --username=$pguser -d model_sessiondata -p $db_port
cat /home/rob/working/database/R/sql/r_functions.sql |  psql --username=$pguser $dbname -p $db_port
