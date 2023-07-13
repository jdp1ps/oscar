import { createApp } from 'vue'

import ActivityWorkpackage from './views/ActivityWorkpackage.vue'

let elemDatas = document.querySelector('#activityworkpackage');
const app = createApp(ActivityWorkpackage, {
    "url": elemDatas.dataset.url,
    "manage": elemDatas.dataset.manage
})
app.mount('#activityworkpackage')