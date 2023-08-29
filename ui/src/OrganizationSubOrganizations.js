import { createApp } from 'vue'

import OrganizationSubOrganizations from './views/OrganizationSubOrganizations.vue'

let elemDatas = document.querySelector('#suborganizations');
const app = createApp(OrganizationSubOrganizations, {
    "url": elemDatas.dataset.url,
    "manage": elemDatas.dataset.manage
})
app.mount('#suborganizations')