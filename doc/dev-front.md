# Développement

Ce document regroupe des informations informelles lièes au développement des fonctionnalités dans Oscar.


## UI (via vue-cli)

```bash
npm install
```


## UI

### Présentation

La compilation du SASS > CSS est géré par un Gulp, vous pouvez l'executer pendant le développement : 

Les fichiers SASS seront automatiquement surveillés et compilés.

```bash
nodejs node_modules/.bin/gulp
```

et la comilation : 

```bash
nodejs node_modules/.bin/gulp sass
```

Les fichiers VUE utilisent l'utilitaire POI (v9)

> Certaines parties historiques sont encore développées avec **BackboneJS**, elles seront progressivement remplacées.

Les composants d'interface sont développées avec le *framework* **VueJS**.
 
Le moteur de compilation **POI** (basé sur **Webpack**) est utilisé pour compilé les fichiers `**.vue` Depuis la version 2.5.x, une version 10 de POI est disponible mais n'est pas compatible avec Oscar, les scripts fonctionnent bien avec la version 9.


## Commandes de développement

### Commandes spécifiques

Les commandes qui suivent permettent d'utiliser POI pour travailler sur les différentes modules de l'interface en mode debug.

```bash
# Compilation du calandrier (DEBUG)
poi watch --format umd --moduleName  Calendar --filename.css Calendar.css --filename.js Calendar.js --dist public/js/oscar/dist public/js/oscar/src/Calendar.vue 

# Polyfill JS
poi watch --format umd --moduleName  Polyfill --filename.css Polyfill.css --filename.js Polyfill.js --dist public/js/oscar/dist public/js/oscar/src/Polyfill.js 

# Compilation de l'interface de gestion des disciplines
poi watch --format umd --moduleName DisciplineUI public/js/oscar/src/DisciplineUI.vue --filename.css DisciplineUI.css --filename.js DisciplineUI.js --dist public/js/oscar/dist

# Compilation des notifications
poi watch --format umd --moduleName  Notification --filename.css Notification.css --filename.js Notification.js --dist public/js/oscar/dist public/js/oscar/src/Notification.vue

# Interface d'export des activités
poi watch --format umd --moduleName  ActivitiesExport --filename.css ActivitiesExport.css --filename.js ActivitiesExport.js --dist public/js/oscar/dist public/js/oscar/src/ActivitiesExport.vue

poi watch --format umd --moduleName  TimesheetMonth --filename.css TimesheetMonth.css --filename.js TimesheetMonth.js --dist public/js/oscar/dist public/js/oscar/src/TimesheetMonth.vue
```

Les commandes de chaque module sont disponibles en commentaire