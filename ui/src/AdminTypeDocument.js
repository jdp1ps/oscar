import { createApp } from 'vue'

import AdminTypeDocument from './views/AdminTypeDocument.vue'

let elemDatas = document.querySelector('#admintypedocument');
const app = createApp(AdminTypeDocument, {
    "url": elemDatas.dataset.url,
    "manage": elemDatas.dataset.manage
})
app.mount('#admintypedocument')