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

class SpentActivityDetailsExcelFormater implements IFormatter
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

        $headers = ['id',
            'syncid',
            'pfi',
            'numSifac',
            'numPiece',
            'montant',
            'compteBudgetaire',
            'centreProfit',
            'compteGeneral',
            'centreFinancier',
            'texteFacture',
            'designation',
            'dateAnneeExercice',
            'datePaiement',
            'datePiece',
            'dateComptable',
            'masse',
            'compte',
            'type'];
        $filename = sprintf('/tmp/spent-%s.csv', uniqid());
        $writer = fopen($filename, 'w');
        fputcsv($writer, $headers);
        foreach ($this->datas['spents'] as $line) {
            $line['montant'] = number_format($line['montant'], 2, ',', ' ');


                fputcsv($writer, $line);
        }
        fclose($writer);
          //


        if( array_key_exists('download', $options) && $options['download'] === true ){
            $downloader = new CSVDownloader();
            $downloader->downloadCSV($filename);
        }

    }
}