<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 05/03/20
 * Time: 15:09
 */

namespace Oscar\Formatter\Spent;


use Oscar\Entity\Activity;
use Oscar\Formatter\CSVDownloader;
use Oscar\Formatter\IFormatter;

class SpentActivityExcelFormater implements IFormatter
{
    /** @var array  */
    private $datas;

    /** @var Activity  */
    private $activity;

    /**
     * SpentActivityExcelFormater constructor.
     * @param $datas
     */
    public function __construct(array $datas, Activity $activity)
    {
        $this->datas = $datas;
        $this->activity = $activity;
    }


    public function format($options)
    {
        $pfi = $this->activity->getCodeEOTP();
        $activityNumOscar = $this->activity->getOscarNum();
        $project = $this->activity->getAcronym();

        // Génération des clefs
        $headers = ['PFI', 'PROJECT', 'ACTIVITY', 'ACTIVITYNUM','MONTANT','TEXTE','TYPE','COMPTE', 'DATE COMPTABLE', 'DATE PAIEMENT', 'ANNEE', 'REF PIECE', 'OSCARID', 'SYNCID'];

        $filename = sprintf('/tmp/spent-%s.csv', uniqid());
        $writer = fopen($filename, 'w');
        fputcsv($writer, $headers);
        foreach ($this->datas['spents'] as $line) {
            $wroteLine = [
                $pfi,
                $project,
                (string)$this->activity,
                $activityNumOscar,
                $line['montant'],
                implode(',', $line['text']),
                implode(',', $line['types']),
                implode(',', $line['compteBudgetaire']),
                $line['datecomptable'],
                $line['datepaiement'],
                $line['annee'],
                $line['refPiece'],
                $this->activity->getId(),
                implode(',', $line['syncIds']),
            ];
            var_dump($wroteLine);
            fputcsv($writer, $wroteLine);
        }
        fclose($writer);
        die();
        //
        if( array_key_exists('download', $options) && $options['download'] === true ){
            $downloader = new CSVDownloader();
            $downloader->downloadCSVToExcel($filename);
        }

    }
}