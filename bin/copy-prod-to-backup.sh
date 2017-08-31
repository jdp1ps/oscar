# DUMP
pg_dump --clean --no-owner -h pgsql.unicaen.fr -U ad_oscar oscar > data/backup_oscar-prod-lasted.sql

# PROD > TEST
psql -h pgsql.unicaen.fr -U ad_oscar_demo oscar_demo < data/backup_oscar-prod-lasted.sql