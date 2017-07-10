/**
 * Created by jacksay on 17-01-12.
 */
import Vue from "vue";

var ActivitiesExport = Vue.extend({
    template: `
        <form :action="urlPost" method="POST">
            <input type="hidden" name="" id="" :value="ids" />
            <div class="btn-group">
                <button type="submit" class="btn btn-xs btn-default"> <i class="icon-download-outline"></i>Télécharger le CSV</button>
                <button type="button" class="btn btn-xs btn-default" disabled> <i class="icon-cog"></i>Configurer</button>
            </div>
            <section v-show="showConfiguration">
                <label v-for="field, i in fields">
                    {{ field }}
                    
                </label>
            </section>
        </form>
    `,

    data(){
        return {
            ids: [],
            fields: [],
            urlPost: null,
            showConfiguration: false
        }
    },

    computed: {

    },

    methods:{

    },

    created(){
        console.log('IDS', this.ids);
        console.log('URL', this.urlPost);
        console.log('FIELDS', this.fields);
    }
});

export default ActivitiesExport;
