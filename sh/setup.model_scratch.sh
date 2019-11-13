#!/bin/sh
# shell
pgpath="/data/postgres/9.5"
bindir="/usr/lib/postgresql/9.5/bin"
db_port=5433

mkdir $pgpath/scratch
$bindir/initdb -D $pgpath/scratch
cp $pgpath/postgresql.conf $pgpath/scratch/
cp $pgpath/pg_hba.conf $pgpath/scratch/
# edit the postgresql.conf file and set the new port
# port 5444
nano $pgpath/scratch/postgresql.conf
# start it up
$bindir/pg_ctl -D $pgpath/scratch start -l "logfile.scratch"

$bindir/createdb model_scratch -p $db_port
echo "CREATE EXTENSION postgis;" | psql model_scratch -p $db_port
