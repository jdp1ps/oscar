# Gestion des documents

> Oscar ne permet pas de déléguer la gestion des document à une GÈDE, cela sera possible dans une prochaine version

Oscar permet des gérer des documents numériques, on trouve différents type de document :

 - Les documents publiques (Accueil > Documents pratiques)
 - Les documents des activités (Fiche activité)
 - Les documents pour les demandes d'activités

## Configuration des emplacements

Les emplacements physiques pour le stockage des documents est situé dans la clef `oscar > paths` du fichier `config/autoload/local.php`:

```php
<?php
// config/autoload/local.php
return array(
    // (...)

    // Oscar
    'oscar' => [
        // (...)
        // Emplacement
        'paths' => [
          // Documents des activités
          'document_oscar' => realpath( __DIR__.'/../../data/documents/activity/'),

          // Documents des demandes d'activités
          'document_request' => realpath( __DIR__.'/../../data/documents/request'),

          // Documents 'publiques"
          'document_admin_oscar' => realpath( __DIR__.'/../../data/documents/public/'),

            // ...
        ],
    ],
);
```

## Configurer la taille des Documents

Oscar s'appuit sur la configuration PHP, pensez à renseigner le fichier `php.ini` pour permettre des téléversements de documents plus volumineux. Par défaut, PHP autorise des fichiers n'excédant pas 2 Mo.

```ini
[PHP]
;;;;;;;;;;;;;;;;
; File Uploads ;
;;;;;;;;;;;;;;;;

; Modifiez cette valeur pour augmenter la taille des fichiers autorisé
upload_max_filesize = 2M
```

Puis pensez à recharger Apache :

```bash
service apache2 restart
```
