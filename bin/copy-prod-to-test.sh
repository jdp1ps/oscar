# DUMP
pg_dump --clean --no-owner -h pgsql.unicaen.fr -U ad_oscar oscar > data/backup_oscar-dev-lasted.sql

# PROD > TEST
psql -h pgsql.unicaen.fr -U ad_oscar_test oscar_test < data/backup_oscar-dev-lasted.sql