/**
 * Created by jacksay on 17-01-12.
 */
import Vue from "vue";

var ActivitiesExport = Vue.extend({
    template: `
        <form :action="urlPost" method="POST">
            <input type="text" name="" id="" :value="ids" />
            <button>Configurer les champs</button>

            <div class="">TEST</div>
        </form>
    `,

    data(){
        return {
            ids: [],
            fields: [],
            urlPost: null
        }
    },

    computed: {

    },

    methods:{

    },

    created(){
        console.log('IDS', this.ids);
        console.log('URL', this.urlPost);
    }
});

export default ActivitiesExport;
