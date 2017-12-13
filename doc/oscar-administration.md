## Gestion des Accès


### Créer un accès 'libre'

```bash
php public/index.php oscar auth:add <login> <email> <pass> <displayname>
```


### Créer un administrateur

Se connecter en SSH sur la machine hébergeant Oscar et se rendre dans le dossier racine : 

On commence par créer un utilisateur :  

```bash
php public/index.php oscar auth:add <login> <email> <pass> <displayname>
```

Puis lon lui attribut le rôle "Administrateur" : 

```bash
php public/index.php oscar auth:promote <login> <role> 
```

## Excel > Json > Oscar

Pour convertir un fichier Excel (format CSV) en JSON, il faut commencé par configurer dans un fichier le descriptif des colonnes : 

```php
// Description des colonnes du fichier
<?php
// Correspondance ( COLONNE => Traitement prévu )
return [
    // Données simple (UNIQUE)
    0 =>    "project.",
    1 =>    "label",
    120 =>  "amount", //
    166 =>  "dateStart", //
    167 =>  "dateEnd", //
    427 => "codeEOTP",
    
    // persons.Role
    423 => "persons.Responsable scientifique",
    424 => "persons.Chargé d'affaires",
    425 => "persons.Assistant ingénieur",
    
    // Organizations > Role de l'organisation
    3 =>    "organizations.Porteur du projet",
    4 =>    "organizations.Laboratoire interne porteur",
    5 =>    "organizations.Laboratoire interne partenaire",
    10 =>   "organizations.Partenaire externe",
    23 =>   "organizations.Partenaire externe",
    421 =>  "organizations.Laboratoire", //
    117 =>  "organizations.Financeur", //
    119 =>  "organizations.Payeur", //
    426 =>  "organizations.Antenne financière référente",

    // Versement (Date) et INDEX+1 => Montant
    11 =>   "payments.date", // 12 => Date
    13 =>   "payments.date", // 14 => Date
    15 =>   "payments.date", // 16 => Date
     
    // Exemple de JALON
    169 =>  "milestones.Début d'éligibilité des dépenses", //
    170 =>  "milestones.Fin d'éligibilité des dépenses", //
];
```

la commandes : 

```bash
$ php public/index.php oscar activity:sync2 chemin/vers/source-activités.csv chemin/vers/configuration.php
```

Elle va produire en sortie des données au format JSON que l'on pourra synchroniser ensuite dans oscar : 

```bash
$ php public/index.php oscar activity:sync chemin/vers/source-activités.json
```
