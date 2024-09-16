import { createApp } from 'vue'

import AdminRoleOrganization from './views/AdminRoleOrganization.vue'

let elemDatas = document.querySelector('#adminroleorganization');
const app = createApp(AdminRoleOrganization, {
    "url": elemDatas.dataset.url,
    "manage": elemDatas.dataset.manage
})
app.mount('#adminroleorganization')