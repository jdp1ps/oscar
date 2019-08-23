<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 21/08/19
 * Time: 16:51
 */

namespace Oscar\Formatter;

use Dompdf\Dompdf;

class TimesheetPersonPeriodFormatter2
{

    public function __construct(){}

    /**
     * @param $path
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generate($filename){
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->spreadsheet);
        $writer->save('php://output');
        die();
    }

    public function generatePdf($filename){
        //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        // TODO orientation paysage avec DOMPdf
        $writer = IOFactory::createWriter($this->spreadsheet, 'Dompdf');
        // new Mpdf($this->spreadsheet);
        $writer->save('php://output');
        die();
    }

    public function output($datas, $outputFormat='excel'){

        $width = count($datas[daysInfos]) +2;
        ob_start(); ?>
        <style>
            body {
                font-size: 12px;
                font-family: Helvetica, Arial, sans-serif;
            }
            table {
                width: 100%;
                max-width: 100%;
                border-collapse: collapse;
            }
            tr { border: thin solid #adb6bd}
            td {}
            thead th {
                text-align: center;
            }
            thead th:nth-child(odd) {
                background-color: #c0e1f1;
            }
            thead th:nth-child(even) {
                background-color: #cde7f1;
            }

            .label { text-align: left }
            .subgroup {
                font-size: 9px;
            }
            .subgroup .label {
                font-weight: normal;
            }
            th {
                border: thin solid #adb6bd;
            }
            td {
                text-align: right;
                background: #f5faf8;
                padding: 4px;
                border: thin solid #e4edf4;
            }
            td:nth-child(odd){
                background: #e3e8e6;
            }
            .feed {
                font-weight: bold;
            }
            .empty {
                font-size: 10px;
            }
            .lock {
                background: #fff !important;
            }
            .soustotal {
                font-weight: bold;
                font-size: 12px;
            }
            .total {
                font-weight: 700;
                font-size: 14px;
            }
        </style>

<table>
<thead>
    <tr>
        <th colspan="<?= $width ?>">
            <h1>
                Feuille de temps de
                <strong><?= $datas['person'] ?></strong>
            </h1>
        </th>
    </tr>
    <tr>
        <th><?= $datas['period'] ?></th>
        <?php foreach($datas['daysInfos'] as $i=>$day): ?>
        <th><small style="font-weight: 100; font-size: 8px"><?= $day['label']?></small><br><?= $i ?></th>
        <?php endforeach; ?>
        <th>TOTAL</th>
    </tr>
</thead>
    <tbody>
    <tr class="group">
        <th class="label" colspan="20">Recherche</th>
    </tr>
    <?php foreach ($datas['declarations']['activities'] as $labelActivity=>$dataActivity):?>
    <tr class="group">
        <th class="label" colspan="<?= $width ?>"><?= $dataActivity['label'] ?></th>
    </tr>
    <?php foreach ($dataActivity['subgroup'] as $labelActivity=>$dataGroup):?>
        <tr class="subgroup">
            <th class="label"> - <?= $dataGroup['label'] ?></th>
            <?php foreach ($datas['daysInfos'] as $i=>$day):
                $dayKey = $i<10?"0$i":"$i";
                $value = 0;
                $class = 'empty';
                if( array_key_exists($dayKey, $dataGroup['days']) ){
                    $value = number_format($dataGroup['days'][$dayKey], 2);
                    $class = "feed";
                }
                if( $day['locked'] ){
                    $class = 'lock';
                    $value = $value == '0' ? '.' : $value;
                }
                ?>
            <td class="<?= $class ?>"><?= $value ?></td>
            <?php endforeach; ?>
            <td class="soustotal"><?= number_format($dataGroup['total'],2) ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <?php foreach ($datas['declarations']['others'] as $otherKey=>$dataOther):?>
        <?php foreach ($dataOther['subgroup'] as $otherKey=>$dataOtherGroup): if( $dataOtherGroup['group'] != 'research' ) continue; ?>
            <tr class="subgroup">
                <th class="label"><strong><?= $dataOtherGroup['label'] ?></strong></th>
                <?php foreach ($datas['daysInfos'] as $i=>$day):
                    $dayKey = $i<10?"0$i":"$i";
                    $value = 0.0;
                    $class = 'empty';
                    if( array_key_exists($dayKey, $dataOtherGroup['days']) ){
                        $value = number_format($dataOtherGroup['days'][$dayKey], 2);
                        $class = "feed";
                    }
                    if( $day['locked'] ){
                        $class = 'lock';
                        $value = $value == '0' ? '.' : $value;
                    }
                    ?>
                    <td class="<?= $class ?>"><?= $value ?></td>
                <?php endforeach; ?>
                <td class="soustotal"><?= number_format($dataOtherGroup['total'],2) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>

        <tr class="subgroup">
                <th class="label">TOTAL RECHERCHE</th>
                <?php foreach ($datas['daysInfos'] as $i=>$day):
                    $dayKey = $i<10?"0$i":"$i";
                    $value = 0.0;
                    $class = 'empty';
                    if( array_key_exists($dayKey, $datas['totalGroup']['research']['days']) ){
                        $value = $datas['totalGroup']['research']['days'][$dayKey]; //number_format($dataOtherGroup['days'][$dayKey], 2);
                        if( $value ) $value = number_format($value, 2);
                        $class = "feed";
                    }
                    if( $day['locked'] ){
                        $class = 'lock';
                        $value = $value == '0' ? '.' : $value;
                    }
                    ?>
                    <td class="<?= $class ?>"><?= $value ?></td>
                <?php endforeach; ?>
                <td class="soustotal"><?= number_format($datas['totalGroup']['research']['total'],2) ?></td>
        </tr>

    <tr class="group">
        <th class="label" colspan="<?= $width ?>">Inactivité</th>
    </tr>
    <?php foreach ($datas['declarations']['others'] as $otherKey=>$dataOther):?>
        <?php foreach ($dataOther['subgroup'] as $otherKey=>$dataOtherGroup): if( $dataOtherGroup['group'] != 'abs' ) continue; ?>
            <tr class="subgroup">
                <th class="label"> - <?= $dataOtherGroup['label'] ?></th>
                <?php foreach ($datas['daysInfos'] as $i=>$day):
                    $dayKey = $i<10?"0$i":"$i";
                    $value = 0;
                    $class = 'empty';
                    if( array_key_exists($dayKey, $dataOtherGroup['days']) ){
                        $value = number_format($dataOtherGroup['days'][$dayKey], 2);
                        $class = "feed";
                    }
                    if( $day['locked'] ){
                        $class = 'lock';
                        $value = $value == '0' ? '.' : $value;
                    }
                    ?>
                    <td class="<?= $class ?>"><?= $value ?></td>
                <?php endforeach; ?>
                <td class="soustotal"><?= number_format($dataOtherGroup['total'],2) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <tr class="group">
        <th class="label" colspan="<?= $width ?>">Autres</th>
    </tr>

    <?php foreach ($datas['declarations']['others'] as $otherKey=>$dataOther):?>
        <?php foreach ($dataOther['subgroup'] as $otherKey=>$dataOtherGroup): if( $dataOtherGroup['group'] == 'research' || $dataOtherGroup['group'] == 'abs' ) continue; ?>
            <tr class="subgroup">
                <th class="label"> - <?= $dataOtherGroup['label'] ?></th>
                <?php foreach ($datas['daysInfos'] as $i=>$day):
                    $dayKey = $i<10?"0$i":"$i";
                    $value = '0';
                    $class = 'empty';
                    if( array_key_exists($dayKey, $dataOtherGroup['days']) ){
                        $value = number_format($dataOtherGroup['days'][$dayKey], 2);
                        $class = "feed";
                    }
                    if( $day['locked'] ){
                        $class = 'lock';
                        $value = $value == '0' ? '.' : $value;
                    }
                    ?>
                    <td class="<?= $class ?>"><?= $value ?></td>
                <?php endforeach; ?>
                <td class="soustotal"><?= number_format($dataOtherGroup['total'],2) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <tr class="group">
        <th class="label" >Total pour la période</th>
        <?php foreach ($datas['daysInfos'] as $i=>$day):
            $value = $day['duration'];
            $class = 'empty';
            if( $value ){
                $value = number_format($value, 2);
                $class = "feed";
            }
            if( $day['locked'] ){
                $class = 'lock';
                $value = $value == '0' ? '.' : $value;
            }
            ?>
            <td class="<?= $class ?>"><?= $value ?></td>
        <?php endforeach; ?>
        <td class="total"><?= number_format($datas['total'],2) ?></td>
    </tr>

    </tbody>
</table>
    <?php
        $html = ob_get_clean();
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream();
        die();
    }
}