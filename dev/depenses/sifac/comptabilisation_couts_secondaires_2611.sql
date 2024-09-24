-- comptabilisation co�ts secondaires
-- exemple ub: H164CVFU
select 
'couts secondaires' as NATURE,
'paye',
'',
rmeasure PFI,
rldnr,
gl_sirid,
docnr,
'',
refdocnr,
'',
refdocnr,
fikrs,
'',
rfarea,
'',
sgtxt,
rwrttp,
hsl,
rfistl,
rfipex,
rfistl,
rhkont,
budat,
budat,
refryear,
budat,
''

from SAPSR3.fmia
where rmeasure = '011C055C'
and rwrttp = '95'
AND rclnt = '500';
