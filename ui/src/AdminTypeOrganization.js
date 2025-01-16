import { createApp } from 'vue';

import AdminTypeOrganization from './views/AdminTypeOrganization.vue';

let divId = '#admintypeorganization';
let elemDatas = document.querySelector(divId);

const app = createApp(AdminTypeOrganization, {
    "url": elemDatas.dataset.url,
    "manage": elemDatas.dataset.manage
});
app.mount(divId);