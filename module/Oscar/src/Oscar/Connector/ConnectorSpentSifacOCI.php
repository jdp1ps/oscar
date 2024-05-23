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
    private SpentService $spentService;

    /** @var array */
    private array $sifacAccess;

    const SPENT_QUERY = "select  MEASURE AS pfi,  RLDNR as AB9, STUNR as idsync,  awref AS numSifac, vrefbn as numCommandeAff, vobelnr as numPiece, LIFNR as numFournisseur, KNBELNR as pieceRef, fikrs AS codeSociete, BLART AS codeServiceFait, FAREA AS codeDomaineFonct, sgtxt AS designation, BKTXT as texteFacture, wrttp as typeDocument, TRBTR as montant, fistl as centreDeProfit, fipex as compteBudgetaire, prctr AS centreFinancier, HKONT AS compteGeneral, budat as datePiece, bldat as dateComptable, gjahr as dateAnneeExercice, zhldt AS datePaiement, MANDT as mandt, BTART as BTART,  PSOBT AS dateServiceFait from sapsr3.v_fmifi where RLDNR ='9A' AND measure = '%s' AND MANDT ='430' AND BTART IN('0250','0100')";

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

    /**
     * Synchronisation complète.
     *
     * @throws OscarException
     */
    public function syncAll(): void
    {
        $pfis = $this->spentService->getPFIList();
        foreach ($pfis as $pfi) {
            $this->sync($pfi);
        }
    }

    /**
     * @param $pfi
     * @return string
     * @throws OscarException
     */
    public function sync($pfi, bool $optimized = false) :string
    {
        $this->logInfo("> call sync('$pfi')'" . ($optimized ? ' [optimized]' : ''));

        try {
            $c = $this->getConnection();
            $exists = $this->spentService->getSpentsSyncIdByPFI($pfi);
            $query = sprintf($this->sifacAccess['spent_query'], $pfi);
            if( $optimized ){
                $this->logDebug("Optimisation : récupération du dernier syncId");
                $lastId = $this->spentService->getLastSyncId($pfi);
                if( $lastId ){
                    $query .= sprintf($this->sifacAccess['optimize_clause'], $lastId);
                }
            }

            $this->logDebug("Query : '$query'");

            if ($c) {
                $stid = oci_parse($c, $query);
                if (!$stid) {
                    throw new OscarException("ORACLE - PARSE ERROR : " . oci_error());
                }
                if (!oci_execute($stid)) {
                    throw new OscarException("ORACLE - QUERY ERROR : " . oci_error());
                }
                $nbr = 0;
                $nbrAdd = 0;
                $nbrExist = count($exists);

                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    $nbr++;
                    if (!in_array($row['IDSYNC'], $exists)) {
                        try {
                            $this->spentService->addSpentLine($row);
                            $nbrAdd++;
                        } catch (\Exception $e) {
                            throw new OscarException(
                                "ERROR - Enregistrement d'une ligne de dépense impossible pour " . $row['IDSYNC'] . " : " . $e->getMessage()
                            );
                        }
                    }
                }

                $message = sprintf(
                    "%s déjà synchronisé(s), %s entrée(s) dans SIFAC, %s résultat(s) ajouté(s) dans Oscar pour %s.",
                    $nbrExist,
                    $nbr,
                    $nbrAdd,
                    $pfi
                );

                $this->logInfo($message);

                return $message;
            }
            else {
                throw new OscarException("Erreur de connection ORACLE");
            }
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            throw $e;
        }
    }

    /**
     * @return mixed
     * @throws OscarException
     */
    protected function getConnection()
    {
        if (!function_exists('oci_connect')) {
            throw new \Exception("Le module OCI pour les connections PHP > ORACLE est nécessaire.");
        }

        $c = @oci_connect(
            $this->sifacAccess['username'],
            $this->sifacAccess['password'],
            sprintf(
                "%s:%s/%s",
                $this->sifacAccess['hostname'],
                $this->sifacAccess['port'],
                $this->sifacAccess['SID']
            )
        );

        if (!$c) {
            $err = oci_error();
            $message = "Unknow error";
            if( array_key_exists('message', $err) ){
                $message = $err['message'];
            }
            throw new OscarException($message);
        }

        return $c;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////// Logger

    /**
     * @param string $msg
     * @return void
     */
    protected function logInfo(string $msg): void
    {
        $this->spentService->getLoggerService()->info("[sifac connector] " . $msg);
    }

    /**
     * @param string $msg
     * @return void
     */
    protected function logDebug(string $msg): void
    {
        $this->spentService->getLoggerService()->debug("[sifac connector] " . $msg);
    }

    /**
     * @param string $msg
     * @return void
     */
    protected function logError(string $msg): void
    {
        $this->spentService->getLoggerService()->error("[sifac connector] " . $msg);
    }
}
