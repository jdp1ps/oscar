import { createApp } from "vue"
import EntityWithRole from "./views/EntityWithRole.vue"

let elemDatas = document.querySelector("#persons_roled");
const app = createApp(EntityWithRole, {
    "url": elemDatas.dataset.url,
    "title": elemDatas.dataset.title,
})

app.mount("#persons_roled")