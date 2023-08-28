import { createApp } from 'vue'

import ActivityDocument from './views/ActivityDocument.vue'

let elemDatas = document.querySelector('#activitydocuments');
const app = createApp(ActivityDocument, {
    "url": elemDatas.dataset.url,
    "manage": elemDatas.dataset.manage
})
app.mount('#activitydocuments')