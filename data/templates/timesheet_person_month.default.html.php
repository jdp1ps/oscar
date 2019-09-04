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
        <th colspan="5"><img src="data:image/png;base64,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" height="90" alt="">
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
</table>
<table>
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