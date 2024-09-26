-- exemple uB: J030CVAU
-- Exemple à CAEN : 011C055C
select
'facture' as nature,
'engage et non paye',
SAPSR3.LFA1.NAME1 as nomfournisseur,
MEASURE AS pfi,
sapsr3.v_fmifi.RLDNR as AB9,
sapsr3.v_fmifi.STUNR as idsync,  
sapsr3.v_fmifi.awref AS numSifac,
sapsr3.v_fmifi.vrefbn as numCommandeAff,
sapsr3.v_fmifi.vobelnr as numPiece,
sapsr3.v_fmifi.LIFNR as numFournisseur,
sapsr3.v_fmifi.KNBELNR as pieceRef,
sapsr3.v_fmifi.fikrs AS codeSociete,
sapsr3.v_fmifi.BLART AS codeServiceFait,
sapsr3.v_fmifi.FAREA AS codeDomaineFonct,
sapsr3.v_fmifi.sgtxt AS designation,
sapsr3.v_fmifi.BKTXT as texteFacture,
sapsr3.v_fmifi.wrttp as typeDocument,
sapsr3.v_fmifi.TRBTR as montant,
sapsr3.v_fmifi.fistl as centreDeProfit,
sapsr3.v_fmifi.fipex as compteBudgetaire,
sapsr3.v_fmifi.prctr AS centreFinancier,
sapsr3.v_fmifi.HKONT AS compteGeneral,
sapsr3.v_fmifi.budat as datePiece,
sapsr3.v_fmifi.bldat as dateComptable,
sapsr3.v_fmifi.gjahr as dateAnneeExercice,
sapsr3.v_fmifi.zhldt AS datePaiement,
sapsr3.v_fmifi.PSOBT AS dateServiceFait
from sapsr3.v_fmifi, SAPSR3.LFA1
where
   ( SAPSR3.v_FMIFI.LIFNR=SAPSR3.LFA1.LIFNR(+) )
AND
sapsr3.v_fmifi.measure = '011C055C' 
AND sapsr3.v_fmifi.rldnr = '9B'
AND sapsr3.v_fmifi.MANDT = '430' -- MANDT 430 à CAEN pour la base Bac à sable
-- facture
AND sapsr3.v_fmifi.wrttp = '54'
-- on conserve seulement compteBudgetaire FG PG* IG RG*
AND ( sapsr3.v_fmifi.fipex IN ('FG', 'IG') OR substr( sapsr3.v_fmifi.fipex , 1, 2) IN ('PG', 'RG') )
-- on retire les numCommandeAff qui commencent par 011, 012, 113
AND substr( sapsr3.v_fmifi.vrefbn ,1 ,3) NOT IN ('011', '012', '113')
;