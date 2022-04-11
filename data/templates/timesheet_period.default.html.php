<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Feuille de temps</title>
    <style>
        <?php include __DIR__.'/common.css'; ?>

        thead td, thead th, tbody {
            font-size: 1.2em;
        }

        tr {
            line-height: 1em;
        }

        thead tr, tfoot tr {
            line-height: 1.4em;
        }

        tfoot th {
            border-top: #333333 solid 1px;
        }

        td.none {
            color: rgba(0,0,0,.5);
            font-size: .8em;
        }
        td.valued {

        }

        .error {
            background: #e34242;
            color: white;
        }
        .error.none {
            background: #e34242;
            color: rgba(255,255,255,.5);
        }
    </style>
</head>
<?php

$duration = function($value) {
    return $this->duration($value);
};

$durationRounded = function( $duration ) {
    $roundStep = 5;
    $heures = floor($duration);
    $minutes = round(($duration - $heures)*60 / $roundStep)*$roundStep;
    if( $minutes < 10 ){
        $minutes = '0'.$minutes;
    }
    return sprintf('%s:%s', $heures, $minutes);
};

$renderDate = function( $str ){
    setlocale(LC_ALL, 'fr_FR');
    $date = new DateTime($str);
    return $date->format("F Y m d");
};

$lines = $facet == 'period' ? $by_periods : $by_persons;
?>
<body>
<header>
    <h1>
        Synthèse du temps pour l'activité
        <em><?= $activity['num'] ?></em> -
        <strong><?= $activity['label'] ?></strong>
    </h1>
    <h2>
        de <strong><?= $period_from_label ?></strong>
        à <strong><?= $period_to_label ?></strong>
    </h2>
</header>

<table>
    <thead>
        <tr>
        <th>Personne / périodes</th>
            <?php foreach ($headings['current']['workpackages'] as $wp): ?>
            <th class="project main research"><?= $wp['code'] ?></th>
            <?php endforeach; ?>
            <th class="project main research total">TOTAL</th>

            <?php foreach ($headings['prjs']['prjs'] as $prj): ?>
            <th class="project research"><?= $prj['label'] ?></th>
            <?php endforeach; ?>

            <?php foreach ($headings['others'] as $other):
                // On n'affiche pas la colonne "Invalid" si ça n'est pas utile
                if( $other['label'] == 'Invalid' && ($headings['has_invalid'] == false) ) continue;
                ?>
                <th class="<?= $other['group'] ?>"><?= $other['label'] ?></th>
            <?php endforeach; ?>
            <th class="total">TOTAL</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($lines as $line): ?>
        <tr class="person">
            <th><?= $line['label'] ?></th>
            <?php foreach ($line['datas']['current']['workpackages'] as $wp): ?>
            <td class="main research <?= $wp['total'] == 0 ? 'none' : 'valued'?>"><?= $duration($wp['total']) ?> </td>
            <?php endforeach; ?>
            <td class="main research total <?= $line['datas']['current']['total'] == 0 ? 'none' : 'valued'?>"><?= $duration($line['datas']['current']['total']) ?></td>

            <?php foreach ($line['datas']['prjs'] as $prj): ?>
            <td class="research <?= $prj['total'] == 0 ? 'none' : 'valued'?>"><?= $duration($prj['total']) ?></td>
            <?php endforeach; ?>

            <?php foreach ($line['datas']['others'] as $other):
                // On n'affiche pas la colonne "Invalid" si ça n'est pas utile
                if( $other['label'] == 'Invalid' && ($headings['has_invalid'] == false) ) continue;
                ?>
                <td class="<?= $other['group'] ?> <?= $other['total'] == 0 ? 'none' : 'valued'?>"><?= $duration($other['total']) ?></td>
            <?php endforeach; ?>
            <td class="total <?= $line['total'] == 0 ? 'none' : 'valued'?>"><?= $duration($line['total']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>

    <tfoot>
    <th>TOTAUX</th>
    <?php foreach ($headings['current']['workpackages'] as $wp): ?>
        <th class="main research <?= $wp['total'] == 0 ? 'none' : 'valued'?>"><?= $duration($wp['total']) ?></th>
    <?php endforeach; ?>
    <th class="main research total <?= $headings['current']['total'] == 0 ? 'none' : 'valued'?>"><?= $duration($headings['current']['total']) ?></th>

    <?php foreach ($headings['prjs']['prjs'] as $prj): ?>
        <th class="research <?= $prj['total'] == 0 ? 'none' : 'valued'?>"><?= $duration($prj['total']) ?></th>
    <?php endforeach; ?>

    <?php foreach ($headings['others'] as $other):
        // On n'affiche pas la colonne "Invalid" si ça n'est pas utile
        if( $other['label'] == 'Invalid' && ($headings['has_invalid'] == false) ) continue;
        ?>
        <th class="<?= $other['group'] ?> <?= $other['total'] == 0 ? 'none' : 'valued'?>"><?= $duration($other['total']) ?></th>
    <?php endforeach; ?>
    <th class="total <?= $headings['total'] == 0 ? 'none' : 'valued'?>"><?= $duration($headings['total']) ?></th>
    </tfoot>
</table>
</body>
</html>