#!/bin/sh
file="/tmp/oscar-"$(date +%Y%m%d)".sql"
sourcePass=lotIrqR3XV
pg_dump -h pgsql.unicaen.fr -U ad_oscar_pp oscar_pp > "$file"

3qD0pwRq1m