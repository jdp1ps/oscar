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

## Requête finale de récupération

```sql
select 
    -- DESCRIPTION / QUALIFICATION
    -- N°
    "MEASURE" AS PFI,
    awref AS numSifac,    
    vrefbn as numCommandeAff, 
    vobelnr as numPiece,
    LIFNR as numFournisseur,
    KNBELNR as pieceRef,
    
    -- codea
    fikrs AS codeSociete, -- 1010 > Société
    BLART AS codeServiceFait,
    FAREA AS codeDomaineFonct, -- 

    -- Description
    sgtxt AS designation,
    BKTXT as texteFacture,
    wrttp as typeDocument, -- 54 > Facture
    TRBTR as montant,

    
    -- Pognon
    fistl as centreDeProfit,
    fipex as compteBudgetaire,
    prctr AS centreFinancier,
    prctr AS centreFinancier,
    HKONT AS compteGeneral,
    
    -- Dates
    budat as datePiece,
    bldat as dateComptable,
    gjahr as dateAnneeExercice,
    zhldt AS datePaiement, 
    PSOBT AS dateServiceFait,
    
    ---    
    bus_area AS domaineActivite
    
from 
    sapsr3.v_fmifi
    
where 
    --mandt = '430'
    --and FIKRS = '1010'
    GJAHR >=2017
    and measure = '014CG019'
;
```


Requête OSCAR : 

```sql
SELECT numpiece, numpiece, MAX(datecomptable) as datecomptable, SUM(montant) as Total FROM spentline 
WHERE pfi = '014CG019' GROUP BY numpiece
ORDER BY datecomptable
```

## Récupération d'un listing des dépenses

```sql
SELECT 
	MAX(a.id), 
	a.codeEOTP, 
	max(a.amount) AS budget,
	SUM(s.montant) AS depences
	
FROM activity a

LEFT JOIN project p ON p.id = a.project_id 
LEFT JOIN spentline s ON s.pfi = a.codeEOTP 
WHERE 
	codeEOTP IS NOT NULL AND codeEOTP != '' 
	AND s.compteBudgetaire NOT IN('RG_RPRO','TECH_BIL','TECH_PRODUITS') 
--	AND EXTRACT(year from a.dateEnd) = 2019 
--	AND EXTRACT(year from a.datestart) = 2017 
	
GROUP BY a.codeEOTP 
ORDER BY codeEoTP
```