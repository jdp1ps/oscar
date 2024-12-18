//import 'bootstrap/dist/css/bootstrap.css';
import './oscar-css.scss';
import {createApp} from "vue";
import Tooltip from "./components/Tooltip.vue";
import momentFilter from "./utils/MomentFilter.js";
import filesize from "./utils/Filesize.js";
import money from "./utils/MoneyFilter.js";
import traces from "./utils/Traces.js";
import GlobalModel from "./models/GlobalModel.js";
import Errors from "./components/Errors.vue";

let elemDatas = document.querySelector("#oscar-core");

GlobalModel.state.urlPerson = elemDatas.dataset.urlPerson;
const app = createApp(Tooltip, {
    "tooltip": elemDatas.dataset.tooltip == 'on'
});

const errors = createApp(Errors, {});


app.config.globalProperties.$filters = {
    timeAgo(date) {
        return momentFilter.timeAgo(date);
    },
    date(date) {
        return momentFilter.date(date);
    },
    dateFull(date) {
        return momentFilter.dateFull(date);
    },
    filesize(size) {
        return filesize.filesize(size);
    },
    money(amount) {
        return money.money(amount);
    },
    log(message) {
        return traces.log(message);
    }
};

app.mount("#oscar-core");
errors.mount("#oscar-errors");

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// MODE PRIVE
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
document.querySelector('#btn-toggle-private').addEventListener('click', e => {
    e.preventDefault();
    privatizer('switch');
});

function privatizer(setprivate = null) {
    let isPrivate = localStorage.getItem('privatize');

    if (setprivate !== null) {
        let actual = document.querySelector('body').classList.contains('privatize');
        let to = !actual;
        isPrivate = to ? "1" : "0";
        localStorage.setItem('privatize', isPrivate);
    }

    if (isPrivate === "1") {
        document.querySelector('body').classList.add('privatize');
    } else {
        document.querySelector('body').classList.remove('privatize');
    }
}

privatizer();