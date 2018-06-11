# Développement

## UI

### Présentation

La compilation du code *front* est centralisée dans les tâches *Gulp* (fichier `gulpfile.js`). Il gère : 

 - La compilation des Fichiers `.Vue` (source `./public/js/oscar/src`)
 - Compilation des fichiers SCSS (Sass)

> Certaines parties historiques sont encore développées avec **BackboneJS**, elles seront progressivement remplacées.

Les composants d'interface sont développées avec le *framework* **VueJS**.
 
Le moteur de compilation **POI** (basé sur **Webpack**) est utilisé pour compilé les fichiers `**.vue`

```bash
npm i -g poi
```

## Commandes de développement

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
```