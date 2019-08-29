# Note de développement

## Récupération des bilans financiers

```sql
select * from sapsr3.prps
  where mandt = '430'
  and PKOKR = '1010'
  and posid like '014CG019%'
;
```

```sql
select * from sapsr3.v_fmifi
  where mandt = '430'
  and FIKRS = '1010'
  and GJAHR >=2017
  and measure = '014CG019'
;
```

(Céline) Récupération des dates de début/fin du projet :

```sql
select
    m.MEASURE,
    m.FMAREA,
    m.VALID_FROM,
    m.VALID_TO,
    m.DATE_EXP,
    mt.SHORT_DESC,
    mt.DESCRIPTION,
    mt.SHTXT

from
    sapsr3.fmmeasure m,
    sapsr3.fmmeasuret mt

where m.MEASURE=mt.MEASURE
    and m.FMAREA=mt.FMAREA

    -- EXEMPLE
    and m.CLIENT = '430'
    and m.FMAREA = '1010'
    and m.measure = '014CG019'
;
```
