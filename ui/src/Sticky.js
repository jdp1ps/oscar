import { createApp } from "vue";
import Sticky from "./views/Sticky.vue";

let elemDatas = document.querySelector("#sticky");
const app = createApp(Sticky, {});
app.mount("#sticky");