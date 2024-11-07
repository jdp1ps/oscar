<?php
require __DIR__ . '/functions.inc.php';

$labels = [
    "abs" => "Absent",
    "education" => "Éducation",
    "other" => "Autre",
    "research" => "Recherche"
];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Synthèse de l'activité</title>
        <style>
            <?php echo file_get_contents(__DIR__.'/common.css'); ?>
        </style>
    </head>
    <body>
            <table border="0">
                <tr>
                    <td>
                        <img width="150" src="<?= $logo_data ?>" alt="Pas d'image ">
                    </td>
                    <td>
                        <h1><?php echo $activity['projectacronym'] . ' ' . $activity['label'] ?></h1>
                       Période du <strong><?= $period['startLabel'] ?></strong>
                       au <strong><?= $period['endLabel'] ?></strong>
                    </td>
                </tr>
            </table>

            <table class="table table-condensed" style="border: 0">
                <thead>
                <tr>
                    <th>&nbsp;</th>
                    <?php

                    ////////////////////////////// Colonnes RECHERCHE
                    //// Colonnes du projet
                    $nbrColProjetLots = count($wps); // Lots
                    $nbrColProjetTotal = $nbrColProjetLots > 1 ? 1 : 0; // Colonne TOTAL
                    $nbrColProjet = $nbrColProjetLots + $nbrColProjetTotal;

                    //// Colonnes Autres Projets
                    $nbrColCEProjet = count($ces);
                    $nbrColCETotal = $nbrColCEProjet > 1 ? 1 : 0;
                    $nbrColCE = $nbrColCEProjet + $nbrColCETotal;

                    //// Colonnes Autres Recherche
                    $nbrColOtherSearchItems = count($othersGroups['research']);
                    $nbrColOtherSearchTotal = $nbrColOtherSearchItems > 1 ? 1 : 0;
                    $nbrColOtherSearch = $nbrColOtherSearchItems + $nbrColOtherSearchTotal;

                    $nbrColResearch = $nbrColProjet + $nbrColCE + $nbrColOtherSearch;


                    //// Colonnes Autres
                    $nbrColOthers = [];
                    foreach ($othersGroups as $key => $value) {
                        if( $key == "research" ){
                            continue;
                        }
                        $nbrItems = count($value);
                        $hasTotal = $nbrItems > 1 ? 1 : 0;
                        $total = $nbrItems + $hasTotal;

                        $nbrColOthers[$key] = [
                            'items' => $nbrItems,
                            'hasTotal' => $hasTotal,
                            'total' => $total,
                        ];
                    }
                    ?>
                    <th class="research" colspan="<?= $nbrColResearch + 1 ?>">Recherches</th>

                    <?php foreach ($othersGroups as $k=>$othersGroup): if( $k == 'research') continue;?>
                        <th class="<?= $k ?>" colspan="<?= $nbrColOthers[$k]['total'] ?>">
                            <?= array_key_exists($k, $labels) ? $labels[$k] : $k ?>
                        </th>
                    <?php endforeach; ?>
                    <th colspan="2" class="totalall">TOTAL période</th>
                </tr>
                <tr>
                    <th>&nbsp;</th>

                    <th class="research main" colspan="<?= $nbrColProjet ?>">
                        <strong>
                            <?= $activity['projectacronym'] ?>
                        </strong>
                    </th>
                    <?php if( $nbrColCE > 0): ?>
                        <th class="research ce" colspan="<?= $nbrColCE ?>">
                            <strong>Autres Projets</strong>
                        </th>
                    <?php endif; ?>
                    <th class="research other" colspan="<?= $nbrColOtherSearch ?>">
                        <span>Autres </span>
                    </th>
                    <th class="research totalcategory" rowspan="2">
                        Total<br> recherche
                    </th>

                    <?php foreach ($othersGroups as $k=>$othersGroup): if( $k == 'research') continue;?>
                        <?php foreach ($othersGroup as $other): ?>
                            <th class="labelvalue <?= $k ?>" rowspan="2">
                                <?= $other['label'] ?>
                            </th>
                        <?php endforeach; ?>
                        <?php if( count($othersGroup) > 1): ?>
                            <th class="<?= $k ?>" rowspan="2">
                                Total
                            </th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <th rowspan="2">Actif</th>
                    <th rowspan="2" class="total">TOTAL</th>
                </tr>
                <tr>
                    <th>&nbsp;</th>

                    <?php foreach ($wps as $code=>$wp): ?>
                        <th class="research activity main labelvalue">
                            <?= $wp['code'] ?>
                        </th>
                    <?php endforeach; ?>
                    <?php if( count($wps) > 1): ?>
                        <th class="research activity main labelvalue">
                            Total
                        </th>
                    <?php endif; ?>

                    <?php foreach ($ces as $acronym): ?>
                        <th class="research ce labelvalue">
                            <?= $acronym ?>
                        </th>
                    <?php endforeach; ?>
                    <?php if( count($ces) > 1): ?>
                        <th class="research ce labelvalue">
                            Total
                        </th>
                    <?php endif; ?>

                    <?php foreach ($othersGroups as $k=>$othersGroup): if( $k != 'research') continue;?>
                        <th class="<?= $k ?> research other labelvalue" colspan="<?= count($othersGroup)+(count($othersGroup)>1 ? 1 : 0) ?>">
                            <?= $labels[$k] ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($foo as $person=>$line): ?>
                    <tr>
                        <th><?= $person ?></th>
                        <?php foreach ($line['main'] as $wp=>$duration): ?>
                            <td class="research main <?= $duration ? 'value' : 'empty' ?>">
                                <?= duration($duration) ?>
                            </td>
                        <?php endforeach; ?>
                        <?php if($nbrColProjetLots > 1): ?>
                        <td class="stotal main research <?= $line['totalMain'] ? 'value' : 'empty' ?>">
                            <?= duration($line['totalMain']) ?>
                        </td>
                        <?php endif; ?>

                        <?php foreach ($line['ce'] as $acronym=>$duration): ?>
                            <td class="ce research <?= $duration ? 'value' : 'empty' ?>">
                                <?= duration($duration) ?>
                            </td>
                        <?php endforeach; ?>

                        <?php if( count($ces) > 1): ?>
                            <td class="stotal research ce <?= $line['totalProjects'] ? 'value' : 'empty' ?>">
                                <?= duration($line['totalProjects']) ?>
                            </td>
                        <?php endif; ?>

                        <?php if( array_key_exists('research', $line['othersGroups'] ) ): ?>
                            <?php foreach ($line['othersGroups']['research']['others'] as $duration): ?>
                                <td class="other research <?= $duration ? 'value' : 'empty' ?>">
                                    <?= duration($duration) ?>
                                </td>
                            <?php endforeach; ?>


                        <?php endif; ?>

                        <td class="research totalcategory <?= $line['totalResearch'] ? 'value' : 'empty' ?>">
                            <?= duration($line['totalResearch']) ?>
                        </td>

                        <?php foreach ($line['othersGroups'] as $group=>$dt): if($group == 'research') continue;?>
                            <?php foreach ($dt['others'] as $other=>$duration): ?>
                                <td class="<?= $group ?> <?= $duration ? 'value' : 'empty' ?>">
                                    <?= duration($duration) ?>
                                </td>
                            <?php endforeach; ?>
                            <?php if( count($dt['others']) > 1): ?>
                                <td class="soustotal <?= $group ?> <?= $duration ? 'value' : 'empty' ?>">
                                    <?= duration($dt['total']) ?>
                                </td>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <!-- TOTAL ACTIF -->
                        <td class="soustotal <?= $line['totaux']['totalWork'] ? 'value' : 'empty' ?>">
                            <?= duration($line['totaux']['totalWork']) ?>
                        </td>

                        <!-- TOTAL ABSOLUE -->
                        <td class="total <?= $line['totaux']['total'] ? 'value' : 'empty' ?>">
                            <?= duration($line['totaux']['total']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

                <tfoot>
                <tr style="border-top: 4px solid #000">
                    <th>Total période</th>
                    <?php foreach( $totaux['wps'] as $wp=>$duration ): ?>
                        <th class="research wp <?= $duration ? 'value' : 'empty' ?>">
                            <?= duration($duration) ?>
                        </th>
                    <?php endforeach; ?>

                    <?php if( count($wps) > 1 ): ?>
                        <th class="research wp <?= $totaux['totalMain'] ? 'value' : 'empty' ?>">
                            <?= duration($totaux['totalMain']) ?>
                        </th>
                    <?php endif; ?>


                    <?php foreach( $totaux['ce'] as $acronym=>$duration ): ?>
                        <th class="research ce <?= $duration ? 'value' : 'empty' ?>">
                            <?= duration($duration) ?>
                        </th>
                    <?php endforeach; ?>
                    <?php if( count($ces) > 1 ): ?>
                        <th class="research ce <?= $totaux['totalCe'] ? 'value' : 'empty' ?>">
                            <?= duration($totaux['totalCe']) ?>
                        </th>
                    <?php endif; ?>

                    <?php foreach ($othersGroups as $group=>$dt): if($group != 'research') continue;?>

                        <?php foreach ($dt as $code=>$other): ?>
                            <th class="research other <?= $code ?> <?= $group ?> <?= $totaux['others'][$code] ? 'value' : 'empty' ?>">
                                <?= duration($totaux['others'][$code]) ?>
                            </th>
                        <?php endforeach; ?>

                        <?php if( count($dt) > 1): ?>
                            <th class="research <?= $totaux['groups']['research'] ? 'value' : 'empty' ?>">
                                <?= duration($totaux['groups']['research']) ?>
                            </th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <th class="research <?= $totaux['totalResearch'] ? 'value' : 'empty' ?>">
                        <?= duration($totaux['totalResearch']) ?>
                    </th>

                    <?php foreach ($othersGroups as $group=>$dt): if($group == 'research') continue;?>

                        <?php foreach ($dt as $code=>$other): ?>
                            <th class="<?= $group ?> <?= $totaux['others'][$code] ? 'value' : 'empty' ?>">
                                <?= duration($totaux['others'][$code]) ?>
                            </th>
                        <?php endforeach; ?>
                        <?php if( count($dt) > 1): ?>
                            <th class="<?= $group ?> <?= $totaux['groups'][$group] ? 'value' : 'empty' ?>">
                                <?= duration($totaux['groups'][$group]) ?>
                            </th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <th class="total">
                        <?= duration($totaux['totalWork']) ?>
                    </th>
                    <td class="total">
                        <?= duration($totaux['total']) ?>
                    </td>


                    <th>&nbsp;</th>
                </tr>
                </tfoot>
            </table>
    </body>
</html>