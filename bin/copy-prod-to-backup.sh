# DUMP
pg_dump --clean --no-owner -h pgsql.unicaen.fr -U ad_oscar oscar > data/backup_oscar-prod-local.sql

# PROD > TEST
psql -h localhost -U oscar_empty oscar_empty < data/backup_oscar-prod-local.sql