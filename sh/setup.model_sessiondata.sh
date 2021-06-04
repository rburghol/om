#!/bin/sh
# shell
if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: setup.model_scratch.sh PG_VERSION"
  echo 1>&2 "Example: setup.model_scratch.sh 9.5"
  exit 2
fi
pgv=$1
echo "PostgreSQL version: $pgv"

pgpath="/data/postgres/$pgv"
bindir="/usr/lib/postgresql/$pgv/bin"
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
plr_sql="/usr/share/postgresql/$pgv/extension/plr.sql"
cat $plr_sql | psql --username=$pguser -d model_sessiondata -p $db_port
cat /opt/model/om/sh/r_functions.sql |  psql --username=$pguser $dbname -p $db_port

