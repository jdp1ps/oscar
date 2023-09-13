import { createApp } from 'vue'

import OrganizationFiche from './views/OrganizationFiche.vue'

let elemDatas = document.querySelector('#organization-view');
const app = createApp(OrganizationFiche, {
    "entrypoint": elemDatas.dataset.entrypoint
})
app.mount('#organization-view')