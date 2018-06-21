# Développement

Ce document regroupe des informations informelles lièes au développement des fonctionnalités dans Oscar.



## UI

### Présentation

La compilation du code *front* est centralisée dans les tâches *Gulp* (fichier `gulpfile.js`). Il gère : 

 - La compilation des Fichiers `.Vue` (source `./public/js/oscar/src`)
 - Compilation des fichiers SCSS (Sass)

> Certaines parties historiques sont encore développées avec **BackboneJS**, elles seront progressivement remplacées.

Les composants d'interface sont développées avec le *framework* **VueJS**.
 
Le moteur de compilation **POI** (basé sur **Webpack**) est utilisé pour compilé les fichiers `**.vue` Depuis la version 2.5.x, une version 10 de POI est disponible mais n'est pas compatible avec Oscar, les scripts fonctionnent bien avec la version 9.

```bash
npm i -g poi
```

## Commandes de développement

Dans la mesure du possible, la plus part des opérations liés au traitement et à la compilation des modules d'interface ont été regroupé dans l'automatisateur de tache **Gulp** (`gulpfile.js`).


### Compilations des modules JS basés sur VueJS

Construction des fichiers JS : 

```bash
# Compilation des JS (VueJS)
gulp modules-oscar

# Compilation des CSS
gulp sass 
```


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


## BUG Connus

Erreur du watcher GULP : 

```
$ gulp watch
[15:47:44] Using gulpfile ~/Projects/Unicaen/oscar/gulpfile.js
[15:47:44] Starting 'watch'...
[15:47:44] 'watch' errored after 8.94 ms
[15:47:44] Error: watch /home/bouvry/Projects/Unicaen/oscar/public/css/ ENOSPC
    at _errnoException (util.js:1022:11)
    at FSWatcher.start (fs.js:1382:19)
    at Object.fs.watch (fs.js:1408:11)
    at Gaze._watchDir (/home/bouvry/Projects/Unicaen/oscar/node_modules/gaze/lib/gaze.js:289:30)
    at /home/bouvry/Projects/Unicaen/oscar/node_modules/gaze/lib/gaze.js:358:10
    at iterate (/home/bouvry/Projects/Unicaen/oscar/node_modules/gaze/lib/helper.js:52:5)
    at Object.forEachSeries (/home/bouvry/Projects/Unicaen/oscar/node_modules/gaze/lib/helper.js:66:3)
    at Gaze._initWatched (/home/bouvry/Projects/Unicaen/oscar/node_modules/gaze/lib/gaze.js:354:10)
    at Gaze.add (/home/bouvry/Projects/Unicaen/oscar/node_modules/gaze/lib/gaze.js:177:8)
    at new Gaze (/home/bouvry/Projects/Unicaen/oscar/node_modules/gaze/lib/gaze.js:74:10)
```

```bash
echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf && sudo sysctl -p
```