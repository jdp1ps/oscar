<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 12/11/19
 * Time: 10:50
 */

namespace Oscar\Connector;


use Oscar\Service\SpentService;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConnectorSpentSifacOCI
{
    /** @var SpentService */
    private $spentService;

    /** @var array */
    private $sifacAccess;

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

    public function sync( $pfi ){
        $c = $this->getConnection();
        if( $c ){
            $stid = oci_parse($c, sprintf("select 
MEASURE AS pfi, 
RLDNR as AB9,
STUNR as idsync, 
awref AS numSifac,
vrefbn as numCommandeAff,
vobelnr as numPiece,
LIFNR as numFournisseur,
KNBELNR as pieceRef,
fikrs AS codeSociete,
BLART AS codeServiceFait,
FAREA AS codeDomaineFonct,
sgtxt AS designation,
BKTXT as texteFacture,
wrttp as typeDocument,
TRBTR as montant,
fistl as centreDeProfit,
fipex as compteBudgetaire,
prctr AS centreFinancier,
HKONT AS compteGeneral,
budat as datePiece,
bldat as dateComptable,
gjahr as dateAnneeExercice,
zhldt AS datePaiement, 
PSOBT AS dateServiceFait

from sapsr3.v_fmifi where measure = '%s' AND rldnr='9A' AND MANDT='430' AND BTART='0250'", $pfi));

            if( !$stid ){
                throw new \Exception("ORACLE - PARSE ERROR : " . oci_error());
            }

            if( !oci_execute($stid) ){
                throw new \Exception("ORACLE - QUERY ERROR : " . oci_error());
            }

            $nbr = 0;
            while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
                $nbr++;
                try {
                    $this->spentService->addSpentLine($row);
                } catch (\Exception $e) {
                    throw new \Exception("ERROR - TRAITEMENT IMPOSSIBLE pour ". $row['IDSYNC']." : " . $e->getMessage());
                }
            }

            return sprintf("%s résultat(s) traité(s) pour %s.", $nbr, $pfi);
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