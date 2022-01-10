# Composant VUEJS

Procédure de création d'un composant d'interface VUEJS dans Oscar.

## Prérequis

 - *Node v10.24.1* (**lts/dubnium**)

> Vous pouvez installer **NVM** pour faire cohabiter plusieurs version de NodeJS sur votre système.


## Sources (VUE)

Les sources sont rangées dans le dossier `./front/`

## Fichier VUE

Créez un fichier `MonInterface.vue` dans **public/js/oscar/src** :

```vue
<template>
    <section>
        <h1>Mon Interface : {{ message }}</h1>
        <p>Reçu de PHTML : {{ depuisphtml }}
        <button @click="handlerClick">FOO</button>
    </section>
</template>
<script>
    //
    export default {
      // Propriétés récupérée via le PHTML
        props: {
            depuisphtml: {
                default: "VIDE"
            }
        },

        // Données brutes utilisées dans l'interface
        data(){
            return {
                message: "pas de message"
            }
        },

        // Méthodes
        methods:{
            handlerClick(){
                this.message = "Vous avez cliquez";
            },
        },

        mounted(){
          // Quand le composant est monté dans le DOM
        }
    }
</script>
```

Le composant porte le nom du fichier **MonInterface**.

## Configurez RequireJS

éditer le fichier `public/js/config-oscar.js` pour y référencer le composant :

```js
/**
 * Configuration des dépendances Javascript dans le projet Oscar©.
 *
 * Created by jacksay on 17/09/15.
 */

requirejs.config({
    baseUrl: '/js/',
    paths: {
        // ...
        // liste des composants
        'MonInterface': 'oscar/dist/MonInterface'
    },
    shim: {
        // ...
        // Configuration des dépendances
    }
});
// ...
```

## Compilation du composant

Commencez par instaler les librairies NodeJS :

```bash
# depuis la racine oscar
npm install
```

Puis compilez le module avec **POI** :

```bash
# depuis la racine oscar
nodejs ./node_modules/.bin/poi build \
  --format umd \
  --moduleName  MonInterface \
  --filename.js MonInterface.js \
  --dist public/js/oscar/dist \
  public/js/oscar/src/MonInterface.vue
```

> Si le développement est en cours, remplacez 'build' par 'watch' pour disposer d'une recompilation automatique ainsi qu'une version plus verbeuse (et beaucoup plus lourde).

## Utiliser le composant dans un template PHP

```html
<div id="moninterface"></div>
<script>
    require(['vue', 'MonInterface'], function(Vue, MonInterface){
        new Vue({
            render: function(h){
                return h(MonInterface.default,{ props: {
                        depuisphtml: 'Données envoyée au composant'
                    }})
            },
            el: '#moninterface'
        })
    });
</script>
```
