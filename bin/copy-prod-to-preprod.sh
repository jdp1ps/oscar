# DUMP
pg_dump --clean --no-owner -h pgsql.unicaen.fr -U ad_oscar oscar > data/backup_oscar-dev-lasted.sql

# PROD > PREPROD
psql -h pgsql.unicaen.fr -U ad_oscar_pp oscar_pp < data/backup_oscar-dev-lasted.sql