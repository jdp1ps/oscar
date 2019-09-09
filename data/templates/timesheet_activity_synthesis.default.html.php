<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/05/19
 * Time: 14:55
 */

$labels = [
    "abs" => "Absent",
    "education" => "Éducation",
    "other" => "Autre",
    "research" => "Recherche"
]
?>
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

    tbody th:nth-child(odd) {
        background-color: #c0e1f1;
    }
    tbody th:nth-child(even) {
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
    td.value {
        text-align: right;
        font-weight: 900;
        font-size: 12px;
    }
    thead td.valueLabel, tfoot td.valueLabel {
        text-align: right;
    }

    td, th {
        text-align: right;
        padding: 1px;
        border-top: thin solid rgba(255,255,255,.7);
        border-right: thin solid rgba(255,255,255,.3)
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
<style>
    small {
        font-weight: 100;
        display: block;
        border-top: thin solid #777;
    }
    th {
        vertical-align: center;
        font-weight: normal;
    }
    .main {
        background: #97c6d6;
    }
    .research {
        background: #8bd1dd;
    }
    .education {
        background: #a6e3c7;
    }
    .abs {
        background: #d6e4b2;
    }
    .other {
        background: #d7b5e5;
    }
    .duration {
        text-align: right;
    }

    .labelvalue {
        font-size: 10px;
    }


    .research.ce {
        background: #67b4c2;
    }
    .research.main {
        background: #93d8e4;
    }
    .research.other {
        background: #b6dbe1;
    }
    .research.totalcategory {
        background: #8bd1dd;
    }

    tr {
        border: none;
    }
    .table tr > th {
        border: none;
    }
    .table > thead > tr th {
        vertical-align: center;
    }
</style>

<div class="container-fluid">

    <h1>
        <img src="data:image/png;base64,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" height="90" alt="" style="float: left; margin-right: 1em">
        <div style="margin-left: 1em;">
            <em style="font-weight: 100">Synthèse des déclarations </em> :
            <strong><?= $activity['projectacronym'] ?></strong> <?= $activity['project'] ?><br>
            <small>
                <i class="icon-calendar"></i> Du <strong><?= $period['startLabel'] ?></strong>
                au <strong><?= $period['endLabel'] ?></strong>
            </small>
        </div>
    </h1>
    <?php if( $format != 'pdf' && $format != 'html'): ?>
        <p>
            <a class="btn btn-primary" href="/feuille-de-temps/synthesisactivity?activity_id=<?= $activity['id'] ?>&format=excel&period=<?= $period['year'] ?>-<?= $period['month'] ?>">
                <i class="icon-file-excel"></i>
                Télécharger la version Excel
            </a>
            <a class="btn btn-primary disabled" href="/feuille-de-temps/synthesisactivity?activity_id=<?= $activity['id'] ?>&format=pdf&period=<?= $period['year'] ?>-<?= $period['month'] ?>">
                <i class="icon-file-pdf"></i>
                Télécharger la version PDF (bientôt disponible)
            </a>
        </p>
    <?php endif; ?>
    <table class="table table-condensed" style="border: 0">
        <thead>
        <tr>
            <th>&nbsp;</th>
            <?php
            // Nombre de colonne dans les groupe
            $colWP      = count($wps) + (count($wps)>1 ? 1 : 0);
            $colCE      = count($ces) + (count($ces)>1 ? 1 : 0);
            $colSEARCH  = count($othersGroups['research']) + (count($othersGroups['research'])>1 ? 1 : 0);

            // Largeur des colonnes
            $colonneLot = count($wps);
            $colonneLotTotal = $colonneLot > 1 ? 1 : 0;

            $colonneAutresProjets = count($ces);
            $colonneAutresProjetsTotal = $colonneAutresProjets > 1 ? 1 : 0;

            $colonneRecherches = count($othersGroups['research']);
            $colonneRecherchesTotal = $colonneRecherches > 1 ? 1 : 0;

            $colonneCategorieRecherche = $colonneLot + $colonneLotTotal + $colonneRecherches + $colonneRecherchesTotal + $colonneAutresProjets + $colonneAutresProjetsTotal;
            $colonneCategorieRechercheTotal = $colonneCategorieRecherche > 2 ? 1 : 0;

            $colonneAbs = count($othersGroups['abs']);
            $colonneAbsTotal = $colonneAbs > 1 ? 1 : 0;

            $colonneOthers = count($othersGroups['others']);
            $colonneOthersTotal = $colonneOthers > 1 ? 1 : 0;

            $colonneOthers = count($othersGroups['education']);
            $colonneOthersTotal = $colonneOthers > 1 ? 1 : 0;



            $colspanLot = count($wps) -1 +1;
            $colspanCE = count($ces) -1;
            if( $colspanCE > 1 ) $colspanCE +=1;

            $colEDU = count()
            ?>
            <th class="research" colspan="<?= $colonneCategorieRecherche + $colonneCategorieRechercheTotal ?>">Recherches</th>

            <?php foreach ($othersGroups as $k=>$othersGroup): if( $k == 'research') continue;?>
                <th class="<?= $k ?>" colspan="<?= count($othersGroup)+(count($othersGroup)>1 ? 1 : 0) ?>">
                    <?= $labels[$k] ?>
                </th>
            <?php endforeach; ?>
            <th colspan="2" class="totalall">TOTAL</th>
        </tr>
        <tr>
            <th>&nbsp;</th>

            <th class="research main" colspan="<?= $colonneLot + $colonneLotTotal ?>">
                <strong><?= $activity['projectacronym'] ?></strong>
            </th>
            <?php if( count($ces) > 0): ?>
                <th class="research ce" colspan="<?= $colonneAutresProjets + $colonneAutresProjetsTotal ?>">
                    <strong>Projets</strong>
                </th>
            <?php endif; ?>
            <th class="research other" colspan="<?= $colonneRecherches + $colonneRecherchesTotal ?>">
                <strong>Autres</strong>
            </th>
            <th class="research totalcategory" rowspan="2">
                Total
            </th>

            <?php foreach ($othersGroups as $k=>$othersGroup): if( $k == 'research') continue;?>
                <?php foreach ($othersGroup as $other): ?>
                    <th class="labelvalue <?= $k ?>" rowspan="2">
                        <?= $other['label'] ?>
                    </th>
                <?php endforeach; ?>
                <?php if( count($othersGroup) > 1): ?>
                    <th class="<?= $k ?>" rowspan="2">
                        =
                    </th>
                <?php endif; ?>
            <?php endforeach; ?>
            <th rowspan="2">ACTIF</th>
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
                        <?= $this->duration($duration) ?>
                    </td>
                <?php endforeach; ?>
                <td class="stotal main research <?= $line['totalMain'] ? 'value' : 'empty' ?>">
                    <?= $this->duration($line['totalMain']) ?>
                </td>

                <?php foreach ($line['ce'] as $acronym=>$duration): ?>
                    <td class="ce research <?= $duration ? 'value' : 'empty' ?>">
                        <?= $this->duration($duration) ?>
                    </td>
                <?php endforeach; ?>

                <?php if( count($ces) > 1): ?>
                    <td class="stotal research ce <?= $line['totalProjects'] ? 'value' : 'empty' ?>">
                        <?= $this->duration($line['totalProjects']) ?>
                    </td>
                <?php endif; ?>

                <?php if( array_key_exists('research', $line['othersGroups'] ) ): ?>
                    <?php foreach ($line['othersGroups']['research']['others'] as $duration): ?>
                        <td class="other research <?= $duration ? 'value' : 'empty' ?>">
                            <?= $this->duration($duration) ?>
                        </td>
                    <?php endforeach; ?>


                <?php endif; ?>

                <td class="research totalcategory <?= $line['totalResearch'] ? 'value' : 'empty' ?>">
                    <?= $this->duration($line['totalResearch']) ?>
                </td>

                <?php foreach ($line['othersGroups']['research'] as $dt): ?>
                    <?php foreach ($dt['others'] as $other=>$duration): ?>
                        <td class="ce research">
                            <?= $this->duration($duration) ?>
                        </td>
                    <?php endforeach; ?>
                    <?php if( count($dt['others']) > 1): ?>
                        <td class="ce research">
                            <?= $this->duration($dt['total']) ?>
                        </td>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php foreach ($line['othersGroups'] as $group=>$dt): if($group == 'research') continue;?>
                    <?php foreach ($dt['others'] as $other=>$duration): ?>
                        <td class="<?= $group ?> <?= $duration ? 'value' : 'empty' ?>">
                            <?= $this->duration($duration) ?>
                        </td>
                    <?php endforeach; ?>
                    <?php if( count($dt['others']) > 1): ?>
                        <td class="soustotal <?= $group ?> <?= $duration ? 'value' : 'empty' ?>">
                            <?= $this->duration($dt['total']) ?>
                        </td>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- TOTAL ACTIF -->
                <td class="soustotal <?= $line['totaux']['totalWork'] ? 'value' : 'empty' ?>">
                    <?= $this->duration($line['totaux']['totalWork']) ?>
                </td>

                <!-- TOTAL ABSOLUE -->
                <td class="total <?= $line['totaux']['total'] ? 'value' : 'empty' ?>">
                    <?= $this->duration($line['totaux']['total']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>

        <tfoot>
        <tr style="border-top: 4px solid #000">
            <th>Total période</th>
            <?php foreach( $totaux['wps'] as $wp=>$duration ): ?>
                <th class="research wp <?= $duration ? 'value' : 'empty' ?>">
                    <?= $this->duration($duration) ?>
                </th>
            <?php endforeach; ?>

            <?php if( count($wps) > 1 ): ?>
                <th class="research wp <?= $totaux['totalMain'] ? 'value' : 'empty' ?>">
                    <?= $this->duration($totaux['totalMain']) ?>
                </th>
            <?php endif; ?>


            <?php foreach( $totaux['ce'] as $acronym=>$duration ): ?>
                <th class="research ce <?= $duration ? 'value' : 'empty' ?>">
                    <?= $this->duration($duration) ?>
                </th>
            <?php endforeach; ?>
            <?php if( count($ces) > 1 ): ?>
                <th class="research ce <?= $totaux['totalCe'] ? 'value' : 'empty' ?>">
                    <?= $this->duration($totaux['totalCe']) ?>
                </th>
            <?php endif; ?>

            <?php foreach ($othersGroups as $group=>$dt): if($group != 'research') continue;?>

                <?php foreach ($dt as $code=>$other): ?>
                    <th class="research other <?= $code ?> <?= $group ?> <?= $totaux['others'][$code] ? 'value' : 'empty' ?>">
                        <?= $this->duration($totaux['others'][$code]) ?>
                    </th>
                <?php endforeach; ?>
                <?php if( count($dt['others']) > 1): ?>
                    <th class="research <?= $totaux['groups']['research'] ? 'value' : 'empty' ?>">
                        <?= $this->duration($totaux['groups']['research']) ?>
                    </th>
                <?php endif; ?>
            <?php endforeach; ?>
            <th class="research <?= $totaux['totalResearch'] ? 'value' : 'empty' ?>">
                <?= $this->duration($totaux['totalResearch']) ?>
            </th>

            <?php foreach ($othersGroups as $group=>$dt): if($group == 'research') continue;?>

                <?php foreach ($dt as $code=>$other): ?>
                    <th class="<?= $group ?> <?= $totaux['others'][$code] ? 'value' : 'empty' ?>">
                        <?= $this->duration($totaux['others'][$code]) ?>
                    </th>
                <?php endforeach; ?>
                <?php if( count($dt) > 1): ?>
                    <th class="<?= $group ?> <?= $totaux['groups'][$group] ? 'value' : 'empty' ?>">
                        <?= $this->duration($totaux['groups'][$group]) ?>
                    </th>
                <?php endif; ?>
            <?php endforeach; ?>
            <th class="total">
                <?= $this->duration($totaux['totalWork']) ?>
            </th>
            <td class="total">
                <?= $this->duration($totaux['total']) ?>
            </td>


            <th>&nbsp;</th>
        </tr>
        </tfoot>
    </table>
</div>