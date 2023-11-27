<?php
require_once __DIR__."/functions.inc.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Feuille de temps</title>
    <style>
        <?php include __DIR__.'/common.css'; ?>
    </style>
    </head>
<?php
?>
<body>
<div class="container-fluid wrapper-page">
<table>
    <thead>
    <tr>
        <th colspan="5"><img src="<?php echo $logo_data ?>" height="90" alt="">
        <th colspan="<?= $width-5 ?>">
            <h1><strong><?= $periodLabel ?></strong> - <?= $person ?>
            </h1>
        </th>
    </tr>
    <tr>
        <td colspan="<?= $width ?>">&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="<?= $colSize4 ?>" class="valueLabel">Agent : </td>
        <td colspan="<?= $colSize4 ?>" class="value"><?= $person ?></td>
        <td colspan="<?= $padding ?>">&nbsp;</td>
        <td colspan="<?= $colSize4 ?>" class="valueLabel">Période : </td>
        <td colspan="<?= $colSize4 ?>" class="value"><?= $periodLabel ?></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="<?= $colSize4 ?>" class="valueLabel">Projets : </td>
        <td colspan="<?= $colSize4 ?>" class="value"><?= $acronyms ?></td>
        <td colspan="<?= $padding ?>">&nbsp;</td>
        <td colspan="<?= $colSize4 ?>" class="valueLabel">N°Oscar : </td>
        <td colspan="<?= $colSize4 ?>" class="value"><?= $num ?></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="<?= $colSize4 ?>" class="valueLabel">Période : </td>
        <td colspan="<?= $colSize4 ?>" class="value"><?= $periodLabel ?></td>
        <td colspan="<?= $padding ?>">&nbsp;</td>
        <td colspan="<?= $colSize4 ?>" class="valueLabel">PFI : </td>
        <td colspan="<?= $colSize4 ?>" class="value"><?= $pfi ?></td>
        <td>&nbsp;</td>
    </tr>


    <?php $i=0; foreach ($organizations as $role=>$organizations): ?>
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

    <tr>
        <td colspan="<?= $width ?>" >&nbsp;
            <?php if($format == 'html'): ?>
            <a href="/feuille-de-temps/excel?action=export2&period=<?= $period ?>&personid=<?= $person_id ?>&out=pdf">Télécharger</a>
            <?php endif; ?>
        </td>
    </tr>
    </thead>
</table>

<table style="table-layout:fixed;">
    <!-- LISTE DES JOURS -->
    <thead>
    <tr class="dateHeading">
        <th class="valueLabel"><?= $periodLabel ?></th>
        <?php foreach($daysInfos as $i=>$day): ?>
            <th width="2.5%">
                <?= $day['label']?><br>
                <?= $i ?>
            </th>
        <?php endforeach; ?>
        <th class="value valueLabel">TOTAL</th>
    </tr>
    </thead>
    <tbody>
    <tr class="group">
        <th class="grouptitle" colspan="<?= $width ?>">Recherche</th>
    </tr>
    <?php foreach ($declarations['activities'] as $labelActivity=>$dataActivity):?>
        <tr class="group">
            <th class="research" colspan="<?= $width ?>"><?= $dataActivity['label'] ?></th>
        </tr>
        <?php foreach ($dataActivity['subgroup'] as $labelActivity=>$dataGroup):?>
            <tr class="subgroup">
                <th class="research"><?= $dataGroup['label'] ?></th>
                <?php foreach ($daysInfos as $i=>$day):
                    $dayKey = $i<10?"0$i":"$i";
                    $value = 0.0;
                    $class = 'empty';
                    if( array_key_exists($dayKey, $dataGroup['days']) ){
                        $value = $dataGroup['days'][$dayKey];
                        $class = "value";
                    }
                    if( $day['locked'] ){
                        $class = 'lock';
                        $value = $value == 0 ? '.' : $value;
                    }
                    ?>
                    <td class="<?= $class ?> research"><?= duration($value) ?></td>
                <?php endforeach; ?>
                <td class="soustotal research"><?= durationRounded($dataGroup['total']) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr class="group">
            <th class="research" colspan="<?= $width-1 ?>" style="text-align: right; border-bottom: solid thin black">Total <?= $dataActivity['acronym'] ?> : </th>
            <td class="research soustotal" style="text-align: right; border-bottom: solid thin black"><?= duration($dataActivity['total']) ?></td>
        </tr>
    <?php endforeach; ?>

    <?php foreach ($declarations['others'] as $otherKey=>$dataOther):?>
        <?php foreach ($dataOther['subgroup'] as $otherKey=>$dataOtherGroup): if( $dataOtherGroup['group'] != 'research' ) continue; ?>
            <tr class="subgroup">
                <th class="research"><strong><?= $dataOtherGroup['label'] ?></strong></th>
                <?php foreach ($daysInfos as $i=>$day):
                    $dayKey = $i<10?"0$i":"$i";
                    $value = 0.0;
                    $class = 'empty';
                    if( array_key_exists($dayKey, $dataOtherGroup['days']) ){
                        $value = $dataOtherGroup['days'][$dayKey];
                        $class = "value";
                    }
                    if( $day['locked'] ){
                        $class = 'lock';
                        $value = $value == '0' ? '.' : $value;
                    }
                    ?>
                    <td class="<?= $class ?> research"><?= duration($value) ?></td>
                <?php endforeach; ?>
                <td class="soustotal research"><?= durationRounded($dataOtherGroup['total']) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <tr class="subgroup">
        <th class="research">RECHERCHE</th>
        <?php foreach ($daysInfos as $i=>$day):
            $dayKey = $i<10?"0$i":"$i";
            $value = 0.0;
            $class = 'empty';

            if( $totalGroup['research']['days'] && array_key_exists($dayKey, $totalGroup['research']['days']) ){
                $value = $totalGroup['research']['days'][$dayKey]; //number_format($dataOtherGroup['days'][$dayKey], 2);
                if( $value ) $value = $value;
                $class = "value";
            }
            if( $day['locked'] ){
                $class = 'lock';
                $value = $value == '0' ? '.' : $value;
            }
            ?>
            <td class="<?= $class ?> research"><?= durationRounded($value) ?></td>
        <?php endforeach; ?>
        <td class="soustotal research"><?= durationRounded($totalGroup['research']['total']) ?></td>
    </tr>


    <tr class="group">
        <th colspan="<?= $width ?>">Hors-lot</th>
    </tr>

    <?php foreach ($declarations['others'] as $otherKey=>$dataOther):?>
        <?php foreach ($dataOther['subgroup'] as $otherKey=>$dataOtherGroup): if( $dataOtherGroup['group'] == 'research' || $dataOtherGroup['group'] == 'abs' ) continue; ?>
            <tr class="subgroup">
                <th class="<?= $dataOtherGroup['group'] ?>"> - <?= $dataOtherGroup['label'] ?></th>
                <?php foreach ($daysInfos as $i=>$day):
                    $dayKey = $i<10?"0$i":"$i";
                    $value = '0';
                    $class = 'empty';
                    if( array_key_exists($dayKey, $dataOtherGroup['days']) ){
                        $value = $dataOtherGroup['days'][$dayKey];
                        $class = "value";
                    }
                    if( $day['locked'] ){
                        $class = 'lock';
                        $value = $value == '0' ? '.' : $value;
                    }
                    ?>
                    <td class="<?= $class ?> <?= $dataOtherGroup['group'] ?>"><?= durationRounded($value) ?></td>
                <?php endforeach; ?>
                <td class="soustotal <?= $dataOtherGroup['group'] ?>"><?= durationRounded($dataOtherGroup['total']) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>

            <tr class="group">
                <th class="label" style="border-bottom: solid black 2px">Activité effective</th>
                <?php foreach ($daysInfos as $i=>$day):
                    $dayKey = $i<10?"0$i":"$i";
                    $value = '0';
                    $class = 'empty';
                    if( array_key_exists($dayKey, $active['days']) ){
                        $value = $active['days'][$dayKey];
                        $class = "value";
                    }
                    if( $day['locked'] ){
                        $class = 'lock';
                        $value = $value == '0' ? '.' : $value;
                    }
                    ?>
                    <td class="<?= $class ?>" style="border-bottom: solid black 2px"><?= durationRounded($value) ?></td>
                <?php endforeach; ?>
                <td class="soustotal" style="border-bottom: solid black 2px"><?= durationRounded($active['total']) ?></td>
            </tr>
    <tr class="group">
        <th class="" colspan="<?= $width ?>">Inactivité</th>
    </tr>
    <?php foreach ($declarations['others'] as $otherKey=>$dataOther):?>
        <?php foreach ($dataOther['subgroup'] as $otherKey=>$dataOtherGroup): if( $dataOtherGroup['group'] != 'abs' ) continue; ?>
            <tr class="subgroup">
                <th class="<?= $dataOtherGroup['group'] ?>"> - <?= $dataOtherGroup['label'] ?></th>
                <?php foreach ($daysInfos as $i=>$day):
                    $dayKey = $i<10?"0$i":"$i";
                    $value = 0;
                    $class = 'empty';
                    if( array_key_exists($dayKey, $dataOtherGroup['days']) ){
                        $value = number_format($dataOtherGroup['days'][$dayKey], 2);
                        $class = "value";
                    }
                    if( $day['locked'] ){
                        $class = 'lock';
                        $value = $value == '0' ? '.' : $value;
                    }
                    ?>
                    <td class="<?= $class ?> <?= $dataOtherGroup['group'] ?>"><?= duration($value) ?></td>
                <?php endforeach; ?>
                <td class="soustotal <?= $dataOtherGroup['group'] ?>"><?= durationRounded($dataOtherGroup['total']) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <tr class="group">
        <th class="valueLabel" style="border-bottom: solid black 2px; font-size: 10px">Total pour la période</th>
        <?php foreach ($daysInfos as $i=>$day):
            $value = $day['duration'];
            $class = 'empty';
            if( $value ){
                $value = number_format($value, 2);
                $class = "valueLabel";
            }
            if( $day['locked'] ){
                $class = 'lock empty';
                $value = $value == '0' ? '.' : $value;
            }
            ?>
            <td class="<?= $class ?>" style="border-bottom: solid black 2px; font-size: 10px"><?= durationRounded($value) ?></td>
        <?php endforeach; ?>
        <td class="valueLabel total" style="border-bottom: solid black 2px; font-size: 10px"><?= durationRounded($total) ?></td>
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
        <td colspan="<?= $width-7 ?>" class="" style=""><?= $commentaires ?></td>
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
        <td colspan="<?= $width ?>">
            <table>
                <tr>
                    <td>&nbsp;</td>
                    <td>Agent : <br>
                        <strong><?= $person ?></strong>
                    </td>
                    <td>Validation projet : <br>
                        <?php foreach ($validations['validators']['prj'] as $validatorInfos) :?>
                            <strong><?= $validatorInfos['person'] ?></strong>
                            <em>(<?= $validatorInfos['human_date'] ?>)</em>
                            <br><br>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        Validation scientifique : <br>
                        <?php foreach ($validations['validators']['sci'] as $validatorInfos) :?>
                            <strong><?= $validatorInfos['person'] ?></strong>
                            <em>(<?= $validatorInfos['human_date'] ?>)</em>
                            <br><br>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        Validation administrative : <br>
                        <?php foreach ($validations['validators']['adm'] as $validatorInfos) :?>
                            <strong><?= $validatorInfos['person'] ?></strong>
                            <em>(<?= $validatorInfos['human_date'] ?>)</em>
                            <br><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>


    <tr>
        <td>&nbsp;</td>
        <td colspan="<?= ($col) ?>" class="value" style="height: 2em">&nbsp;</td>
        <td>&nbsp;</td>
        <td colspan="<?= ($col) ?>" class="value" style="height: 2em">&nbsp;</td>
        <td>&nbsp;</td>
        <td colspan="<?= $col ?>" class="value" style="height: 2em">&nbsp;</td>
        <td>&nbsp;</td>
    </tr>

    <tr>
        <td colspan="<?= $width ?>"></td>
    </tr>
    </tfoot>
</table>
<?php if( isset($outputFormat) && $outputFormat == 'html' ): ?>
    <a href="?action=export2&out=pdf&period=<?= $period ?>&personid=<?= $_REQUEST['personid'] ?>">Télécharger le PDF</a>
<?php endif; ?>
</div>
</body>
</html>
