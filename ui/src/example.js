import { createApp } from "vue"

// Importation du composant
import Example from "./components/Example.vue"

// Création de la vue
const app = createApp(Example)

// Affichage dans l'élément du DOM #example (<div id="example"></div>)
app.mount("#example")