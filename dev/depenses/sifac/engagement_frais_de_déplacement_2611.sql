-- point b engagement frais de d�placement
-- exemple uB J030CVAU

SELECT
  SAPSR3.DD07V.DDTEXT,
  'engage et non paye',
  SAPSR3.LFA1.NAME1 NOMFOUR,
  SAPSR3.FMIOI.MEASURE,
  SAPSR3.FMIOI.RLDNR Ledger,
  SAPSR3.FMIOI.STUNR STUNR_id_unique_BDD,
  '' numsifac,
  SAPSR3.FMIOI.REFBN N_piece_de_ref,
  '' numpiece,
  SAPSR3.FMIOI.LIFNR N_cpte_four,
  '' pieceref,
  '1010' codesociete,
  '' codeservicefait,
  SAPSR3.FMIOI.FAREA FAREA_Dom_fonctionnel,
  SAPSR3.FMIOI.SGTXT SGTXT_texte_du_poste,
  '' texteFacture,
  SAPSR3.FMIOI.WRTTP WRTTP_type_de_valeur,
  SAPSR3.FMIOI.TRBTR TRBTR_montant_devise_transac,
  SAPSR3.FMIOI.FISTL FISTL_centre_financier,
  SAPSR3.FMIOI.FIPEX FIPEX_compte_budgetaire,
  NVL(substr(SAPSR3.FMIOI.OBJNRZ, '12'), SAPSR3.FMIOI.PRCTR) CENTREFINANCIER,
  SAPSR3.FMIOI.HKONT compteGeneral,
  sapsr3.fmioi.budat date_piece_a_confirmer,
  sapsr3.fmioi.bldocdate date_comptable_a_confirmer,
  sapsr3.fmioi.gjahr date_annee_exercice_a_confirmer,
  sapsr3.fmioi.zhldt date_paiement_a_confirmer,
  '' date_service_fait
FROM
  SAPSR3.FMIOI,
  SAPSR3.FMFCTRT,
  SAPSR3.DD07V,
  SAPSR3.DD07V  DD07V1,
  SAPSR3.FMCIT,
  SAPSR3.LFA1,
  SAPSR3.CSKT,
  SAPSR3.CSKT  CSKT_FMIOI,
  SAPSR3.TFKBT,
  SAPSR3.VBAP,
  SAPSR3.PA0001
WHERE
  ( SAPSR3.FMFCTRT.FICTR=SAPSR3.FMIOI.FISTL  )
  AND  ( SAPSR3.FMIOI.FAREA=SAPSR3.TFKBT.FKBER  )
  AND  ( SAPSR3.FMIOI.FIPEX=SAPSR3.FMCIT.FIPEX  )
  AND  ( SAPSR3.FMIOI.GJAHR=SAPSR3.FMCIT.GJAHR  )
  AND  ( SAPSR3.FMIOI.WRTTP=SAPSR3.DD07V.DOMVALUE_L(+)  )
  AND  ( SAPSR3.FMIOI.BTART=DD07V1.DOMVALUE_L  )
  AND  ( SAPSR3.FMIOI.LIFNR=SAPSR3.LFA1.LIFNR(+)  )
  AND  ( substr(SAPSR3.FMIOI.OBJNRZ,'12')=SAPSR3.CSKT.KOSTL(+)  )
  AND  ( CSKT_FMIOI.KOSTL(+)=SAPSR3.FMIOI.PRCTR  )
  AND  ( SAPSR3.FMIOI.REFBN=SAPSR3.VBAP.VBELN(+)  )
  AND  ( SAPSR3.FMIOI.RFORG=SAPSR3.PA0001.PERNR(+)  )
  AND  ( SAPSR3.FMIOI.BUKRS=SAPSR3.PA0001.WERKS(+)  )
  AND  ( SAPSR3.DD07V.DOMNAME = 'FM_WRTTP' AND SAPSR3.DD07V.DDLANGUAGE = 'F'  )
  AND  ( DD07V1.DOMNAME = 'FM_BTART' AND DD07V1.DDLANGUAGE = 'F'  )
  AND  ( SAPSR3.FMIOI.RLDNR = '9B' )
  AND  
  (
   SAPSR3.FMIOI.MANDT  IN  ( '430'  )
   AND
   sapsr3.fmioi.measure = '956C078B'
   and
   SAPSR3.FMIOI.WRTTP = '52'
  )
;