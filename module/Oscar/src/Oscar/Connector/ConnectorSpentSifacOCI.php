<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 12/11/19
 * Time: 10:50
 */

namespace Oscar\Connector;


use Oscar\Exception\OscarException;
use Oscar\Service\SpentService;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConnectorSpentSifacOCI
{
    /** @var SpentService */
    private $spentService;

    /** @var array */
    private $sifacAccess;

    const SPENT_QUERY = "select  MEASURE AS pfi,  RLDNR as AB9, STUNR as idsync,  awref AS numSifac, vrefbn as numCommandeAff, vobelnr as numPiece, LIFNR as numFournisseur, KNBELNR as pieceRef, fikrs AS codeSociete, BLART AS codeServiceFait, FAREA AS codeDomaineFonct, sgtxt AS designation, BKTXT as texteFacture, wrttp as typeDocument, TRBTR as montant, fistl as centreDeProfit, fipex as compteBudgetaire, prctr AS centreFinancier, HKONT AS compteGeneral, budat as datePiece, bldat as dateComptable, gjahr as dateAnneeExercice, zhldt AS datePaiement,  PSOBT AS dateServiceFait from sapsr3.v_fmifi where measure = '%s' AND rldnr='9A' AND MANDT='430' AND BTART='0250'";

    /**
     * ConnectorSpentSifacOCI constructor.
     * @param SpentService $spentService
     * @param array $sifacAccess
     */
    public function __construct(SpentService $spentService, array $sifacAccess)
    {
        $this->spentService = $spentService;
        $this->sifacAccess = $sifacAccess;
    }

    public function syncAll(SymfonyStyle $io){
        $pfis = $this->spentService->getPFIList();
        foreach ($pfis as $pfi) {
            $this->sync($pfi);
        }
        return "TOTAL : " . count($pfis);
    }

    public function sync( $pfi ){
        $c = $this->getConnection();

        $exists = $this->spentService->getSpentsSyncIdByPFI($pfi);

        // Limite de 5 secondes pour cette requête
        oci_set_call_timeout($c, 5000);

        if( $c ){
            $stid = oci_parse($c, sprintf($this->sifacAccess['spent_query'], $pfi));
            if( !$stid ){
                throw new \Exception("ORACLE - PARSE ERROR : " . oci_error());
            }
            if( !oci_execute($stid) ){
                throw new \Exception("ORACLE - QUERY ERROR : " . oci_error());
            }
            $nbr = 0;
            $nbrAdd = 0;
            $nbrExist = count($exists);

            while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
                $nbr++;
                if( !in_array($row['IDSYNC'], $exists) ) {
                    try {
                        $this->spentService->addSpentLine($row);
                        $nbrAdd++;
                    } catch (\Exception $e) {
                        throw new \Exception("ERROR - TRAITEMENT IMPOSSIBLE pour " . $row['IDSYNC'] . " : " . $e->getMessage());
                    }
                }
            }
            return sprintf("%s déjà synchronisé(s), %s entrée(s) dans SIFAC, %s résultat(s) ajouté(s) dans Oscar pour %s.", $nbrExist, $nbr, $nbrAdd, $pfi);
        } else {
            throw new OscarException("Erreur de connection ORACLE");
        }
    }

    protected function getConnection(){
        if( !function_exists('oci_connect') ){
            throw new \Exception("Le module OCI pour les connections PHP > ORACLE est necessaire.");
        }
        $c = oci_connect($this->sifacAccess['username'],$this->sifacAccess['password'], sprintf("%s:%s/%s", $this->sifacAccess['hostname'], $this->sifacAccess['port'], $this->sifacAccess['SID']));
        return $c;
    }
}