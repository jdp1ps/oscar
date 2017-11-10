# Oscar en ligne de commande

Oscar dispose d'un accès terminal pour lancer automatiquement certaines tâche de maintenance


## Accès / Droits

### Créer un accès 'libre'

```bash
php public/index.php oscar auth:add
```

Répondre aux différentes questions pour activer ce compte : 

```
$ php public/index.php oscar auth:add
Entrez l'identifiant : pbanner
Nom affiché (pbanner) : Peter Banner
Email (éviter de laisser vide) : peter.banner@oscar-demo.com
Entrez le mot de passe (8 caractères minimum): 
L'utilisateur suivant va être créé :                                                                                                                                 
Identifiant de connexion : pbanner
Nom affiché : Peter Banner
Courriel : peter.banner@oscar-demo.com
Créer l'utilisateur ? y
pbanner a été créé avec succès.

```


## Modifier le mot de passe d'un utilisateur

La commande `php public/index.php oscar auth:pass LOGIN` permet de lancer la procédure de modifiction d'un mot de passe en base de donnée. Elle propose une série d'invite permettant de vérifier l'utilisteur et le mot de passe : 

```
$ php public/index.php oscar auth:pass bohr
Modification du mot de passe pour bohr (Niels Bohr, niels.bohr@jacksay.com)
Entrez le nouveau mot de passe : 
Confirmer le nouveau mot de passe :                                                                                                                                  
Modifier le mot de passe ? (Y|n) y                                                                                                                                   
Le mot de passe a été mis à jour
```

L'option `--ldap` permet d'appliquer la règle "LDAP" pour activer l'authentification LDAP de ce compte.

```
$ php public/index.php oscar auth:pass bohr --ldap
Modification du mot de passe pour bohr (Niels Bohr, niels.bohr@jacksay.com)
Modifier le mot de passe ? (Y|n) y
Le mot de passe a été mis à jour
```



## Promouvoir un utilisateur

La commande ```php public/index.php oscar auth:promote LOGIN``` permet de gérer les rôles au niveau **Application**.

Elle propose la liste des rôles disponibles pour les ajouter à un utilisateur : 

```
$ php public/index.php oscar auth:promote bohr
Liste des rôles : 
Quel rôle ajouter à Niels Bohr ?
  a) Administrateur
  b) beta_testeur
  c) Chargé de mission Europe
  d) Chargé de valorisation
  e) Chercheur
  f) Gestionnaire
  g) Directeur de composante
  h) Directeur de laboratoire
  i) Doctorant
  k) Gestionnaire recherche de laboratoire
  l) Ingénieur
  m) Post-doc
  n) Responsable
  o) Responsable administratif et gestionnaire de composante
  p) Responsable financier
  q) Responsable juridique
  r) Responsable RH
  s) Responsable scientifique
  t) Superviseur
  u) Utilisateur
  v) valo
```

### Créer un administrateur

Se connecter en SSH sur la machine hébergeant Oscar et se rendre dans le dossier racine :

On commence par créer un utilisateur, puis lui attribuer le rôle *Administrateur* :

```bash
php public/index.php oscar auth:promote hulk
```

### Lister les authentifications actives

On peut également lister les authentifications actives dans la BDD : 

```bash
php public/index.php oscar auth:list
```

### Informations utilisateurs

Permet d'afficher les informations sur une autentification 

```bash
php public/index.php oscar auth:info login
```

L'option `--org` permet d'afficher les rôles de la personne associée dans les organisations référencées dans oscar.
L'option `--act` permet d'afficher les rôles de la personne associée dans les activités.

```text
$ php public/index.php oscar auth:info einstein --act --org
ID : 124
username (identifiant) : einstein
displayName : Albert Einstein
email : albert.einstein@oscar-demo.fr
Rôles : 
Person : Albert Einstein
# Rôles dans des organisations : 
Olympia : Responsable scientifique
# Rôles dans des activitès : 
[2017DRI00001] Relativité Générale : Responsable scientifique
[2017DRI00002] Relativité restrinte : Responsable scientifique
```

### Personnes (Person)

Rechercher la/les personne par connector ID : 

```
php public/index.php oscar persons:search:connector CONNECTOR VALUE
```

Par exemple : 

```bash
php public/index.php oscar persons:search:connector rest ed209
```

Recherche parmis les personnes celle qui a, pour le connector **rest** la valeur **ed209**.

## Moteur de recherche

Reconstruire l'index de recherche des activités :

```bash
php public/index.php oscar activity:search:build
```

## Importer des authentifications depuis un fichier JSON

On peut (pour le développement) importer des autentifications en BDD.

```bash
php public/index.php oscar authentifications:sync install/demo/authentification.json
```

Les données sont sous la forme : 

```json
[
  {
    "displayname": "Albert Einstein",
    "email": "albert.einstein@jacksay.com",
    "password": "alberteinstein",
    "login": "alberteinstein",
    "approles": []
  },
  {
    "displayname": "Maurice Solovine",
    "email": "maurice.solovine@jacksay.com",
    "password": "mauricesolovine",
    "login": "mauricesolovine",
    "approles": []
  },
   {
      "displayname": "Flying Spagetti Monster",
      "email": "flyingsm@jacksay.com",
      "password": "bcrypt:$2y$14$MotDePasseCrypt.cestPratique",
      "login": "flyingsm",
      "approles": ["Administrateur"]
    }
]
```

## Importation de donnée PERSON

On peut égalementimporter des personnes en BDD.

```bash
php public/index.php oscar personsjson:sync install/demo/persons.json
```

Les données sont sous la forme : 

```json
[
  {
    "firstname": "Albert",
    "lastname": "Einstein",
    "mail": "albert.einstein@jacksay.com",
    "login": "alberteinstein",
    "uid": "alberteinstein",
    "roles": {
      "14": ["Responsable scientifique"]
    }
  },
  {
    "firstname": "Maurice",
    "lastname": "Solovine",
    "mail": "maurice.solovine@jacksay.com",
    "login": "mauricesolovine",
    "uid": "mauricesolovine",
    "roles": {
      "14": ["Responsable scientifique", "Doctorant"]
    }
  }
]
```


