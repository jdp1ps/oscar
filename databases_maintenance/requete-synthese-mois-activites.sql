SELECT 
	person_id,
	workpackage_id,
	label,
	comment,
	EXTRACT(EPOCH from dateto - datefrom) / 3600 as duration,
	to_char(datefrom, 'YYYY-MM') as period 
	
FROM timesheet WHERE to_char(datefrom, 'YYYY-MM') = '2017-12'