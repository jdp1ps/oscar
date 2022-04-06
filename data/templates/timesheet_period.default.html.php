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
        <th>Personne / périodes</th>
        <?php foreach ($headings['current']['workpackages'] as $wp): ?>
        <th class="project main research"><?= $wp['code'] ?></th>
        <?php endforeach; ?>
        <th class="project main research total">TOTAL</th>

        <?php foreach ($headings['prjs']['prjs'] as $prj): ?>
        <th class="project research"><?= $prj['label'] ?></th>
        <?php endforeach; ?>

        <?php foreach ($headings['others'] as $other): ?>
            <th class="other <?= $other['group'] ?>"><?= $other['label'] ?></th>
        <?php endforeach; ?>
        <th class="other total">TOTAL</th>
    </thead>

    <tbody>
    <?php foreach ($lines as $line): ?>
        <tr class="person">
            <th><?= $line['label'] ?></th>
            <?php foreach ($line['datas']['current']['workpackages'] as $wp): ?>
            <td class="project main research"><?= $duration($wp['total']) ?> </td>
            <?php endforeach; ?>
            <td class="project main research total"><?= $duration($line['datas']['current']['total']) ?></td>

            <?php foreach ($line['datas']['prjs'] as $prj): ?>
            <td class="project research"><?= $duration($prj['total']) ?></td>
            <?php endforeach; ?>

            <?php foreach ($line['datas']['others'] as $other): ?>
                <td class="other <?= $other['group'] ?>"><?= $duration($other['total']) ?></td>
            <?php endforeach; ?>
            <td class="other total"><?= $duration($line['total']) ?></td>

        </tr>
    <?php endforeach; ?>
    </tbody>

    <tfoot>
    <th>TOTAUX</th>
    <?php foreach ($headings['current']['workpackages'] as $wp): ?>
        <th class="project main research"><?= $duration($wp['total']) ?></th>
    <?php endforeach; ?>
    <th class="project main research total"><?= $duration($headings['current']['total']) ?></th>

    <?php foreach ($headings['prjs']['prjs'] as $prj): ?>
        <th class="project research"><?= $duration($prj['total']) ?></th>
    <?php endforeach; ?>

    <?php foreach ($headings['others'] as $other): ?>
        <th class="other <?= $other['group'] ?>"><?= $duration($other['total']) ?></th>
    <?php endforeach; ?>
    <th class="other total"><?= $duration($headings['total']) ?></th>
    </tfoot>
</table>
</body>
</html>