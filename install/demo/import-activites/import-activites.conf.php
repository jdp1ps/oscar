<?php
/**
 * Exemple de configuration pour les importations "avancées" d'activité.
 *
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-21 09:19
 * @copyright Certic (c) 2017
 */
return [
    0 => "uid",

    2   => 'project.acronym',
    4   => "project.label",

    3   => "label",
    1   => 'description',
    5   => "PFI",
    19  => "datePFI",
    22  => "dateSigned",

    6   => "organizations.Composante Responsable",
    7   => "organizations.Laboratoire",
    21   => "organizations.Laboratoire",

    8   => "persons.Responsable Scientifique",
    9   => "persons.Ingénieur",
    10   => "persons.Ingénieur",

    11  => "amount",

    12  => "payments.1.2",
    15  => "payments.1.2",
    24  => "payments.-1",

    18  => "milestones.Rapport financier",
    20  => "type",

    25  => [
        'key' => "persons.Participants",
        'separator' => ','
    ],

    26  => "tva",
    27  => "financialImpact",
    28  => "currency",
    29  => "assietteSubventionnable",
    30  => "status",
    32  => "disciplines",

    34  => "milestones.Rapport scientifique 9.1"
];
