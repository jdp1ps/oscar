# OSCAR UI (2023)

## Installation

Les sources des fichiers d'interface sont présents dans ``./ui``

### Prérequis

- **Node 16+** (ex: nvm use stable)
- **Yarn 1.22**

### Installation

```bash
cd ui
yarn run dev
```

## Développement d'une nouvelle interface

## Mode DEV

1. lancer le serveur VITE :

```
cd ui
yarn run dev
```

2. activer le mode DEV dans oscar dans ``./config/autoload/local.php`` :

```php
<?php
// ...
return array(
    // ...
    // Oscar
    'oscar' => [
        'vite' => [
            'mode' => 'dev', // dev/prod <<< ICI
            'src' => __DIR__ . '/../../ui',
            'dest' => __DIR__ . '/../../public/js/oscar/vite/dist',
            'base_url_dev' => 'http://localhost:5173',
            'base_url_prod' => '/js/oscar/vite/dist',
        ],

        //
        'log_level' => \Monolog\Logger::DEBUG,
        // ...
    ]
    // ...
)
// ...
```

### Nouveau composant

Création du fichier JS :

```js
// ui/src/MonComposant.js
import {createApp} from 'vue'
import MonComposant from './views/MonComposant.vue'

// éléments dans le DOM
let elemId = '#identifiant-dans-le-dom';
let elemDatas = document.querySelector(elemId);

// Création de l'App
const app = createApp(DeclarersList, {
    "argument1": elemDatas.dataset.argument1,
    "argument2": elemDatas.dataset.argument2
});

// Affichage de l'app
app.mount(elemId);
```

La vue **MonComposant** (``ui/views/MonComposant.vue``) :

```vue

<template>
  <section>
    <div>
      Argument : <code>{{ argument1 }}</code>
      Argument : <code>{{ argument2 }}</code>
    </div>
  </section>
</template>
<script>

// fichier : ui/views/MonComposant.vue
export default {
  props: {
    argument1: {required: true},
    argument2: {required: true}
  }
  // Some stuff
}
</script>
```

Puis, déclarer le composant dans vite dans le fichier de configuration Vite ``ui/vite.config.js`` :

```js
// ui/vite.config.js
import {defineConfig, splitVendorChunkPlugin} from 'vite'
import vue from '@vitejs/plugin-vue'
import {resolve} from 'path'

console.log("OSCAR BUILDER v3");

// https://vitejs.dev/config/
export default defineConfig({
    // ...
    build: {
        // ...
        rollupOptions: {
            // les composants chargés dans 'input' sous la forme : 
            // nomcomposant: resolve(FichierJs.js)  
            input: {
                moncomposant: resolve(__dirname, 'src/MonComposant.js'),
                // ...
            },
            // ...
        },
    },
    // ...
})
```

Enfin, dans la vue Oscar :

```php
<!-- module/Oscar/view/oscar/template-exemple.phtml -->
<div class="container">
    <div id="identifiant-dans-le-dom"
        data-argument1="Valeur transmise au composant" 
        data-argument2="<?php echo 'Autre valeur transmise au composant'; ?>" 
    ></div>
    <?php echo $this->Vite()->addJs('src/MonComposant.js'); ?>
</div>
```

## Compiler

```bash
yarn run prod
```
Cela va créé des fichiers dans le ``outDir`` configuré dans ``vite.config.js`` (``public/js/oscar/vite/dist``)

Pour tester, repasser en prod le module VITE de Oscar : 

```php
<?php
// ...
return array(
    // ...
    // Oscar
    'oscar' => [
        'vite' => [
            'mode' => 'prod', // dev/prod <<< ICI
            // ...
        ],
        // ...
    ]
    // ...
)
// ...
```