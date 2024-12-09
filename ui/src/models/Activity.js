import axios from "axios";
import AxiosMessage from "../utils/AxiosMessage.js";

const store = {
    budget: null,
    credentials: null,
    core: null,
    persons: null,
    organizations: null,

    fetchActivity(url){
        console.log("Fetch ", url);
        axios.get(url).then(response => {
            console.log(response.data);
            this.budget = response.data.activity.datas.budget;
            this.core = response.data.activity.datas.core;
            this.persons = response.data.activity.datas.persons;
            this.organizations = response.data.activity.datas.organizations;
            this.credentials = response.data.activity.credentials;

        }, error => {
            this.handlerError(AxiosMessage.manageErrorResponse(error));
        }).finally(f => {
            this.loading = false;
        })
    }
};

export default store;