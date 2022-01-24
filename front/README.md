# Gestion des modules d'interface VUEJS

Informations pour le développement : Ajout / Modification des modules

## Prérequis

Compilation des sources avec Node **dubnium** (*v10.24.1*).

```bash
nvm use lts/dubnium
 
Now using node v10.24.1 (npm v6.14.12)
```

Affichage des compilations disponibles :

```bash
node node_modules/.bin/gulp

[09:30:33] Using gulpfile ~/Projects/Unicaen/OscarProject/Spartan/oscar/front/gulpfile.js
[09:30:33] Starting 'default'...
Usage : 
activityDocument  : 
 - compile > node_modules/.bin/gulp activityDocument
 - watch   > node_modules/.bin/gulp activityDocumentWatch
activityValidator  : 
 - compile > node_modules/.bin/gulp activityValidator
 - watch   > node_modules/.bin/gulp activityValidatorWatch
administrationPcru  : 
 - compile > node_modules/.bin/gulp administrationPcru
 - watch   > node_modules/.bin/gulp administrationPcruWatch
administrationPcruPC  : 
 - compile > node_modules/.bin/gulp administrationPcruPC
 - watch   > node_modules/.bin/gulp administrationPcruPCWatch
componentRNSRField  : 
 - compile > node_modules/.bin/gulp componentRNSRField
 - watch   > node_modules/.bin/gulp componentRNSRFieldWatch
createProcessusPCRU  : 
 - compile > node_modules/.bin/gulp createProcessusPCRU
 - watch   > node_modules/.bin/gulp createProcessusPCRUWatch
[09:30:33] Finished 'default' after 2.7 ms
```

