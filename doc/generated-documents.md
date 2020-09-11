# Documents générés

La version **2.9 Matrix** de *Oscar* introduit un système pour générer des documents à partir de la fiche activité.

## Configuration d'un modèle

Dans le fichier `config/autoload/local.php`, on renseigne la clef `generated-documents` : 

```php
<?php
// config/autoload/local.php
return array(
    'oscar' => [
        'generated-documents' => [
            /**/
            'activity' => [
                'sample' => [
                    // label : Texte affiché dans le bouton
                    'label' => 'Exemple de document',
                    
                    // Emplacement du gabarit
                    'template' => __DIR__ . '/../../data/generated-documents/activity/sample.docx',
                    
                    // Non utilisé pour le moment
                    'type' => 'Word'
                ]
            ]
            /****/
        ]
    ]
);
```

Des exemples de gabarits sont disponibles dans le dossier `data/generated-docuements/activity`.

Pour voir la liste des champs disponibles, accéder à l'URL : 

`https://oscar.domain/activites-de-recherche/generer-document/ID/dump`

ID correspond à l'identifiant de l'activité visible dans l'URL de la fiche activité.

Exemple : 

<table border='1'><tr><th>id</th><td><small>STRING</small></td><td><code>2</code></td></tr><tr><th>acronym</th><td><small>STRING</small></td><td><code>RELACSV</code></td></tr><tr><th>amount</th><td><small>STRING</small></td><td><code>15000</code></td></tr><tr><th>pfi</th><td><small>STRING</small></td><td><code></code></td></tr><tr><th>oscar</th><td><small>STRING</small></td><td><code>2015DRI00001</code></td></tr><tr><th>montant</th><td><small>STRING</small></td><td><code>15 000,00€</code></td></tr><tr><th>annee-debut</th><td><small>STRING</small></td><td><code>2015</code></td></tr><tr><th>annee-fin</th><td><small>STRING</small></td><td><code>2017</code></td></tr><tr><th>debut</th><td><small>STRING</small></td><td><code>01/01/2015</code></td></tr><tr><th>fin</th><td><small>STRING</small></td><td><code>31/12/2017</code></td></tr><tr><th>intitule</th><td><small>STRING</small></td><td><code>Exemple d'activité 2</code></td></tr><tr><th>label</th><td><small>STRING</small></td><td><code>Exemple d'activité 2</code></td></tr><tr><th>type</th><td><small>STRING</small></td><td><code>Colloques</code></td></tr><tr><th>ingenieur</th><td><small>STRING</small></td><td><code>Sarah Déclarant, Marcel Grossmann, John Doe</code></td></tr><tr><th>ingenieur-list</th><td><small>[LIST]</small></td><td>Sarah Déclarant, Marcel Grossmann, John Doe</td></tr><tr><th>responsable-scientifique</th><td><small>STRING</small></td><td><code>Maurice Solovine, Albert Einstein</code></td></tr><tr><th>responsable-scientifique-list</th><td><small>[LIST]</small></td><td>Maurice Solovine, Albert Einstein</td></tr><tr><th>composante-responsable</th><td><small>STRING</small></td><td><code>ACME </code></td></tr><tr><th>composante-responsable-list</th><td><small>[LIST]</small></td><td>ACME </td></tr><tr><th>laboratoire</th><td><small>STRING</small></td><td><code>US Robots , Cyberdyne </code></td></tr><tr><th>laboratoire-list</th><td><small>[LIST]</small></td><td>US Robots , Cyberdyne </td></tr><tr><th>jalon-fin-d-eligibilite-des-depenses</th><td><small>STRING</small></td><td><code>30/06/2019</code></td></tr><tr><th>jalon-fin-d-eligibilite-des-depenses-list</th><td><small>[LIST]</small></td><td>30/06/2019</td></tr><tr><th>jalon-debut-d-eligibilite-des-depenses</th><td><small>STRING</small></td><td><code>01/01/2019</code></td></tr><tr><th>jalon-debut-d-eligibilite-des-depenses-list</th><td><small>[LIST]</small></td><td>01/01/2019</td></tr><tr><th>versements-prevus</th><td><small>STRING</small></td><td><code>2,500.00 € le 01/03/2019, 5,000.00 € le 01/04/2019, 3,000.00 € le 01/05/2019, 4,500.00 € le 01/06/2019</code></td></tr><tr><th>versements-effectues</th><td><small>STRING</small></td><td><code></code></td></tr><tr><th>versementPrevuMontant</th><td><small>[LIST]</small></td><td>2,500.00 €, 5,000.00 €, 3,000.00 €, 4,500.00 €</td></tr><tr><th>versementPrevuDate</th><td><small>[LIST]</small></td><td>01/03/2019, 01/04/2019, 01/05/2019, 01/06/2019</td></tr><tr><th>versement-effectue-montant</th><td><small>[LIST]</small></td><td>2,500.00 €, 5,000.00 €, 3,000.00 €, 4,500.00 €</td></tr><tr><th>versement-effectue-date</th><td><small>[LIST]</small></td><td>01/03/2019, 01/04/2019, 01/05/2019, 01/06/2019</td></tr></table>

## Variable STRING dans le gabarit

Les variables typées **STRING** peuvent être affichée dans le gabarit avec l'expression `${variable}` où variable correspond au nom de la variable. Par exemple si l'on souhaite afficher l'acronyme de l'activité, on utilisera `${acronym}`.

> Les variables issues des jalons/organisations/personnes dépendent des rôles/type de jalon que vous utilisez dans votre installation Oscar.




## Variable LIST dans le gabarit

A VENIR (en cours)