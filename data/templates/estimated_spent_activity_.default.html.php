<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document PDF</title>
    <style>
        <?php include __DIR__.'/common.css'; ?>

        .thead-main {
            font-size: 1em;
        }

        .total {
            font-weight: bold;
        }

        th {
            text-align: left;
        }

        .presenter {
            width: 80%;
            margin-left: 5%;
        }

        .prevision {
            width: 90%;
            margin-left: 5%;
        }
    </style>
</head>
<body>
<?php
/** Activity $activity */
function montant($value){
   return number_format($value, 2);
}

$colors = ['#c0e1f1', "#BCF1C3", "#F0F1C0", "#F1C2AF", "#F1C8DE"];
?>



    <table class="presenter">
        <tr>
            <td width="25%">&nbsp;</td>
            <td><img src="data:image/png;base64,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" height="90" alt=""></td>
            <th>
                <h1 style="padding-left: 1em">
                    Dépenses prévisionnelles<br>
                    <small><?php echo $activity->getFullLabel() ?></small>
                </h1>
            </th>
            <td width="25%">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <th>Du <strong><?= $activity->getDateStartStr() ?></strong></th>
            <th>Au <strong><?= $activity->getDateEndStr() ?></strong></th>
            <td width="25%">&nbsp;</td>
        </tr>
    </table>
    <table class="prevision">
        <thead class="thead-main">
            <tr>
                <td>
                   &nbsp;
                </td>
                <td colspan="<?= count($years) + 1?>">
                    Prévisions
                </td>
            </tr>
            <tr>
                <th>Nature des dépenses</th>
                <?php foreach($years as $year): ?>
                <th><?= $year ?></th>
                <?php endforeach; ?>
                <th class="total">TOTAL</th>
            </tr>
        </thead>

        <?php $c=0; foreach( $masses as $masseKey=>$masse ): ?>
        <tbody style="background: <?= $colors[$c++] ?>">
            <tr class="group ">
                <th colspan="<?php echo count($years) + 3 ?>"><?= $masse ?></th>
            </tr>
            <?php foreach( $lines as $line ): if( $line['annexe'] != $masseKey ) continue;?>
                <tr class="subgroup">
                    <th><code style="display: inline-block; width: 3em; text-align: right"><?= $line['code'] ?></code> - <?= $line['label'] ?></th>
                    <?php foreach($years as $year): ?>
                        <td><?= montant($totaux['lines'][$line['code']][$year]) ?></td>
                    <?php endforeach; ?>
                    <td class="total">
                        <?= montant($totaux['lines'][$line['code']]['total']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>


            <tr class="group">
                <th>&nbsp;</th>
                <?php foreach($years as $year): ?>
                    <td><?= montant($totaux['lines'][$masseKey][$year]) ?></td>
                <?php endforeach; ?>
                <td class="total"><?= montant($totaux['lines'][$masseKey]['total']) ?></td>
            </tr>
        </tbody>
        <?php endforeach; ?>
        <tfoot>
            <tr>
                <th>TOTAL</th>
                <?php foreach($years as $year): ?>
                    <td class="total"><?= montant($totaux['years'][$year]) ?></td>
                <?php endforeach; ?>
                <td class="total"><?= montant($totaux['total']) ?></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>