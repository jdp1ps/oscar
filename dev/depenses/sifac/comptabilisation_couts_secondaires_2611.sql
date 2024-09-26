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
where rmeasure = '014C311F'
and rwrttp = '95'
AND rclnt = '430';
