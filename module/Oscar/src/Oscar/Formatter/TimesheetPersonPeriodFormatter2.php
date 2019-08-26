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

        $nbrJours = count($datas['daysInfos']);
        $width = $nbrJours +2;

        $colSize4 = ceil(($nbrJours-3) / 4);
        $padding = $nbrJours - ($colSize4*4);

        // Logo
        $logo = realpath(__DIR__.'/../../../../../data/timesheet-logo.png');

        $type = pathinfo($logo, PATHINFO_EXTENSION);
        $data = file_get_contents($logo);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        


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
            thead h1 {
                font-weight: normal;
            }

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
            thead td {
                background: #e3e8e6;
            }
            thead td.value, tfoot td.value {
                background: #f1f6f4;
                text-align: left;
                font-weight: 900;
            }
            thead td.valueLabel, tfoot td.valueLabel {
                text-align: right;
            }

            tbody td {
                text-align: right;
                background: #f5faf8;
                padding: 4px;
                border: thin solid #e4edf4;
            }
            tbody td:nth-child(odd){
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
        <th colspan="5"><img src="<?= $base64 ?>" height="90" alt=""></th>
        <th colspan="<?= $width-5 ?>">
            <h1>

                Feuille de temps de
                <strong><?= $datas['person'] ?></strong>
                pour
                <strong><?= $datas['periodLabel'] ?></strong>
            </h1>
        </th>
    </tr>
    <tr>
        <td colspan="<?= $width ?>">
            &nbsp;
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="<?= $colSize4 ?>" class="valueLabel">Agent : </td>
        <td colspan="<?= $colSize4 ?>" class="value"><?= $datas['person'] ?></td>
        <td colspan="<?= $padding ?>">&nbsp;</td>
        <td colspan="<?= $colSize4 ?>" class="valueLabel">Période : </td>
        <td colspan="<?= $colSize4 ?>" class="value"><?= $datas['periodLabel'] ?></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="<?= $colSize4 ?>" class="valueLabel">Projets : </td>
        <td colspan="<?= $colSize4 ?>" class="value"><?= $datas['acronyms'] ?></td>
        <td colspan="<?= $padding ?>">&nbsp;</td>
        <td colspan="<?= $colSize4 ?>" class="valueLabel">N°Oscar : </td>
        <td colspan="<?= $colSize4 ?>" class="value"><?= $datas['num'] ?></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="<?= $colSize4 ?>" class="valueLabel">Période : </td>
        <td colspan="<?= $colSize4 ?>" class="value"><?= $datas['periodLabel'] ?></td>
        <td colspan="<?= $padding ?>">&nbsp;</td>
        <td colspan="<?= $colSize4 ?>" class="valueLabel">PFI : </td>
        <td colspan="<?= $colSize4 ?>" class="value"><?= $datas['pfi'] ?></td>
        <td>&nbsp;</td>
    </tr>


        <?php $i=0; foreach ($datas['organizations'] as $role=>$organizations): ?>
            <?php if($i%2 == 0): ?>
            <tr>
                <td>&nbsp;</td>
                <td colspan="<?= $colSize4 ?>" class="valueLabel"><?= $role ?></td>
                <td colspan="<?= $colSize4 ?>" class="value"><?= implode(", ", $organizations) ?></td>
                <td colspan="<?= $padding ?>">&nbsp;</td>
            <?php else: ?>
                <td colspan="<?= $colSize4 ?>" class="valueLabel"><?= $role ?></td>
                <td colspan="<?= $colSize4 ?>" class="value"><?= implode(", ", $organizations) ?></td>
                <td>&nbsp;</td>
            </tr>
            <?php endif; ?>
        <?php $i++ ?>
        <?php endforeach; ?>
    <?php if($i%2 != 0): ?>
        <td colspan="<?= $colSize4*2 ?>" class="valueLabel">&nbsp;</td>
        <td>&nbsp;</td>
        </tr>
    <?php endif; ?>


    <?php /* for($i = 0; $i<ceil(count($datas['organizations'])/2); $i++) { ?>
        <tr>
            <td>&nbsp;</td>
            <?php if( count($datas['organizations']) > $i * 2): ?>
                    <td colspan="<?= $colSize4 ?>" class="valueLabel">ORG : </td>
                    <td colspan="<?= $colSize4 ?>" class="value">foo</td>
            <?php else: ?>
                <td colspan="<?= $colSize4*2 ?>" class="valueLabel">&nbsp;</td>
            <?php endif ?>
            <td colspan="<?= $padding ?>">&nbsp;</td>

            <?php if( count($datas['organizations']) > $i * 2 + 1): ?>
                <td colspan="<?= $colSize4 ?>" class="valueLabel">ORG : </td>
                <td colspan="<?= $colSize4 ?>" class="value">foo</td>
            <?php else: ?>
                <td colspan="<?= $colSize4*2 ?>" class="valueLabel">&nbsp;</td>
            <?php endif ?>
            <td>&nbsp;</td>
        </tr>

    <?php }*/ ?>


    <tr>
        <td colspan="<?= $width ?>">&nbsp;</td>
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
    <tfoot>
        <tr>
            <td colspan="<?= $width ?>">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="6" class="valueLabel">Commentaire : </td>
            <td>&nbsp;</td>
            <td colspan="<?= $width-7 ?>" class="value" style="white-space: pre-wrap"><?= $datas['commentaires'] ?></td>
            <td>&nbsp;</td>
        </tr>

        <?php
        $col = floor(($width-4)/3);
        $extraPadding = $width - 4;
        ?>
        <tr>
            <td colspan="<?= $width ?>">&nbsp;</td>
        </tr>

        <tr>
            <td>&nbsp;</td>
            <td colspan="<?= ($col) ?>" class="valueLabel">Signature de l'agent : </td>
            <td>&nbsp;</td>
            <td colspan="<?= ($col) ?>" class="valueLabel">Validation scientifique : </td>
            <td>&nbsp;</td>
            <td colspan="<?= $col ?>" class="valueLabel">Validation administrative :</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="<?= ($col) ?>" class="value" style="height: 75px">&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="<?= ($col) ?>" class="value" style="height: 75px">&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="<?= $col ?>" class="value" style="height: 75px">&nbsp;</td>
            <td>&nbsp;</td>
        </tr>

        <tr>
            <td colspan="<?= $width ?>"></td>
        </tr>
    </tfoot>
</table>
        <?php if( $outputFormat == 'html' ): ?>
            <a href="?action=export2&out=pdf&period=<?= $datas['period'] ?>&personid=<?= $_REQUEST['personid'] ?>">Télécharger le PDF</a>
        <?php endif; ?>
    <?php
        $html = ob_get_clean();
        if( $outputFormat == 'html' ){
            die($html);
        }
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($datas['filename']);
        die();
    }
}