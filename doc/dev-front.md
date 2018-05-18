# Développement

## UI

La compilation du code *front* est centralisée dans les tâches *Gulp* (fichier `gulpfile.js`). Il gère : 

 - La compilation des Fichiers `.Vue` (source `./public/js/oscar/src`)
 - Compilation des fichiers SCSS (Sass)

> Cetraines parties historiques sont encore développées avec **BackboneJS**, elles seront progressivement remplacées.

Les composants d'interface sont développées avec le *framework* **VueJS**.
 
Le moteur de compilation **POI** (basé sur **Webpack**) est utilisé pour compilé les fichiers `**.vue`

## Commandes de développement

Les commandes qui suivent permettent d'utiliser POI pour travailler sur les différentes modules de l'interface en mode debug.

```bash
# Compilation du calandrier (DEBUG)
poi watch --format umd --moduleName  Calendar --filename.css Calendar.css --filename.js Calendar.js --dist public/js/oscar/dist public/js/oscar/src/Calendar.vue 

# Polyfill JS
poi watch --format umd --moduleName  Polyfill --filename.css Polyfill.css --filename.js Polyfill.js --dist public/js/oscar/dist public/js/oscar/src/Polyfill.js 

```