# Dépenses SIFAC

> Ce document historise les tests pour améliorer la synchronisation des dépenses entre Oscar est SIFAC. Les résultats / notes sont historisés dans le document du plus récent au début, au plus ancien en bas.



## Test 2024/09/25

Réunion avec Céline BERNERY qui propose une optimisation des requêtes proposées par Bourgogne

> Note de céline : les résultats sont peut être corrects en bourgogne, mais chez nous ça colle pas --> une partie de ce qui remonte comme "engagé et non payé" est déjà payé.

```sql
-- point b engagement frais de d�placement
-- exemple uB J030CVAU

SELECT
  DD07V.DDTEXT,
  FMIOI.WRTTP, 
  'engage et non paye',
  LFA1.NAME1 NOMFOUR,
  FMIOI.MEASURE,
  FMIOI.RLDNR Ledger,
  FMIOI.STUNR STUNR_id_unique_BDD,
  '' numsifac,
  FMIOI.REFBN N_piece_de_ref,
  FMIOI.RFPOS n_poste_ref,
  '' numpiece,
  FMIOI.LIFNR N_cpte_four,
  '' pieceref,
  FMIOI.BUKRS codesociete,
  '' codeservicefait,
  FMIOI.FAREA FAREA_Dom_fonctionnel,
  FMIOI.SGTXT SGTXT_texte_du_poste,
  '' texteFacture,
  FMIOI.WRTTP WRTTP_type_de_valeur,
  FMIOI.TRBTR TRBTR_montant_devise_transac,
  FMIOI.FISTL FISTL_centre_financier,
  FMIOI.FIPEX FIPEX_compte_budgetaire,
  NVL(substr(FMIOI.OBJNRZ, '12'), FMIOI.PRCTR) CENTREFINANCIER,
  FMIOI.HKONT compteGeneral,
  fmioi.budat date_piece_a_confirmer,
  fmioi.bldocdate date_comptable_a_confirmer,
  fmioi.gjahr date_annee_exercice_a_confirmer,
  fmioi.zhldt date_paiement_a_confirmer,
  '' date_service_fait
FROM
  SAPSR3.FMIOI FMIOI,
  SAPSR3.DD07V DD07V,
  SAPSR3.LFA1 LFA1
WHERE 
  DD07V.DOMNAME = 'FM_WRTTP' AND DD07V.DDLANGUAGE = 'F' 
  AND FMIOI.WRTTP=DD07V.DOMVALUE_L(+) 
  AND FMIOI.LIFNR=LFA1.LIFNR(+) 
  and FMIOI.MANDT=LFA1.mandt(+)
  --mandant --> à modifier pour prod: 500
  AND FMIOI.MANDT ='430' 
  AND FMIOI.FIKRS='1010' 
  AND  FMIOI.RLDNR = '9B' 
  AND fmioi.measure = '950DSI30' 
  and FMIOI.WRTTP = '52'
;
```

```sql
--point a : commandes d'achat

SELECT
  DD07V.DDTEXT,
  FMIOI.WRTTP,
  'engage et non paye',
  LFA1.NAME1 NOMFOUR,
  FMIOI.MEASURE,
  FMIOI.RLDNR,
  FMIOI.STUNR,
  '' numsifac,
  FMIOI.REFBN,
  FMIOI.RFPOS,
  FMIOI.LIFNR,
  '' pieceref,
  FMIOI.BUKRS codesociete,
  '' codeservicefait,
  FMIOI.FAREA,
  FMIOI.SGTXT,
  '' texteFacture,
  FMIOI.WRTTP,
  FMIOI.TRBTR,
  FMIOI.FISTL,
  FMIOI.FIPEX,
  NVL(substr(FMIOI.OBJNRZ, '12'), FMIOI.PRCTR) CENTREFINANCIER,
  FMIOI.HKONT,
  fmioi.budat date_piece_a_confirmer,
  fmioi.bldocdate date_comptable_a_confirmer,
  fmioi.gjahr date_annee_exercice_a_confirmer,
  fmioi.zhldt date_paiement_a_confirmer,
  '' date_service_fait
  
FROM
  SAPSR3.FMIOI FMIOI,
  SAPSR3.DD07V, 
  SAPSR3.LFA1 LFA1
  
WHERE
  FMIOI.LIFNR=LFA1.LIFNR (+)
  and FMIOI.MANDT=LFA1.mandt (+)
  and (DD07V.DOMNAME = 'FM_WRTTP' AND DD07V.DDLANGUAGE = 'F' )
  and FMIOI.WRTTP=DD07V.DOMVALUE_L
  AND FMIOI.RLDNR='9B'
  --mandant --> à modifier pour prod: 500
  AND FMIOI.MANDT='430' 
  AND FMIOI.FIKRS='1010'
  --PFI à indiquer
  AND fmioi.measure = '950DSI06' 
  --and fmioi.refbn='4500322586'
  AND FMIOI.WRTTP = '51'
;
```

## Test 2024/09/23

> Requêtes proposées par Bourgogne. Testées à Caen (OK)
> - PFI : 011C055C
> - MANDT : 430 (base de test)

 - [Script des Commandes](./commandes.sql) : OK
 - [Script des Coûts secondaires](./comptabilisation_couts_secondaires_2611.sql) : OK, mais pas de résultat avec le PFI de test
 - [Engagement des frais de déplacement](./engagement_frais_de_déplacement_2611.sql) : OK
 - [Factures 2611](./factures_2611.sql) : OK
 - [Paiements 2611](./paiements_2611.sql) : OK
 - [Transferts de résultats](./trasferts%20de%20résultats_2611.sql) : OK, mais pas de résultats avec le PFI de test)


BLDATE/DATE PIECE = Date de référence de création de la ligne/pièce