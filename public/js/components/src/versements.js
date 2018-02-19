/**
 * Created by jacksay on 17-01-12.
 */
import Vue from "vue";
import VueResource from "vue-resource";
import moment from "mm";

Vue.use(VueResource);

var FormulaireVersement = {
    props: ['formData', 'bstatus', 'bcurrencies'],
    data(){
        return {
            status: this.bstatus,
            currencies: this.bcurrencies,
        }
    },
    template: `
        <div>
        <div class="form-group">
            <label for="">Montant</label>
            <input type="text" v-model="formData.amount" class="form-control">
        </div>
        <div class="form-group">
            <label for="">Type</label>
            <select v-model="formData.status" class="form-control">
                <option :value="k" v-for="(st, k) in status">{{ st }}</option>
            </select>
        </div>
        <div class="form-group">
            <label for="">Devise</label>
            <select v-model="formData.currency.id" class="form-control">
                <option :value="c.id" v-for="c in currencies">{{ c.label }}</option>
            </select>
        </div>
        </div>
    `
};

var versements = Vue.extend({
    http: {
        emulateHTTP: true,
        emulateJSON: true
    },
    components: {
        'formulaire-versement': FormulaireVersement
    },
    template: `
<div class="versements">
    <h1>Versements</h1>
    <section>
        <formulaire-versement
            :bstatus="status"
            :bcurrencies="currencies"
            :form-data="formData"
            v-if="formData"></formulaire-versement>
        <article v-for="versement in versements" class="card xs payment" :class="'status-' +versement.status +' ' + (versement.late ? 'past' : '')">
            <div class="heading">
                <strong class="amount">{{ versement.amount | currency }}{{ versement.currency.symbol }}</strong>
                <div class="date">
                    <i class="icon-calendar"></i>
                    
                    <template v-if="versement.status == 1">
                    <time :datetime="versement.datePredicted" class="date" v-if="versement.datePredicted">
                        {{ versement.datePredicted | date}}
                    </time>
                    <strong class="text-danger">
                        Pas de date prévue !
                    </strong>
                    </template>
                    <time :datetime="versement.datePayment" class="date" v-else>
                        {{ versement.datePayment | date}}
                    </time>
                <br>
                 N° <strong>{{ versement.codeTransaction }}</strong>
                </div>
                <nav>
                    <a href="#" class="btn-delete" @click.prevent="remove(versement)">
                        <i class="icon-trash"></i>
                    </a>
                    <a href="#" class="btn-edit" @click.prevent="update(versement)">
                        <i class="icon-pencil"></i>
                    </a>
                </nav>
            </div>
            <p class="comment">{{ versement.comment }}</p>
        </article>
        <article class="payment total">
            <div class="heading">
                <strong class="amount">{{ total.effectif | currency }}€</strong>
                <span class="date">
                    <span class="curreny">
                        <span class="value">{{ total.prevu | currency }}</span>
                        <span class="curreny">€</span>
                    </span>
                </span>
            </div>
        </article>
    </section>
    <pre>{{ $data.status }}</pre>
</div>`,
    filters: {
        /**
         * Retourne la date au format 'humain'
         * @param v
         * @returns {*}
         */
        date (v) {
            if (!v || !v.date) return "Pas de date"
            else return moment(v.date).format('dddd Do MMMM YYYY')
        },
        /**
         * Retourne le somme d'argent au format humain
         * @param v
         */
        currency(amount){
            if( amount == undefined )
                return "error";
            var split, unit, fraction, i, j, formattedUnits, value,
                decimal = 2,
                decimalSeparator = ",",
                hundredSeparator = " ";

            // Format decimal
            value = amount.toFixed(decimal).toString();

            // split
            split = value.split('.');
            unit = split[0];
            fraction = split[1];
            formattedUnits = "";

            if( unit.length > 3 ){
                for( i=unit.length-1, j=0; i>=0; i--, j++ ){
                    if( j%3 === 0 && i < unit.length-1 ){
                        formattedUnits = hundredSeparator+ formattedUnits;
                    }
                    formattedUnits = unit[i]+formattedUnits;
                }
            } else {
                formattedUnits = unit;
            }
            return formattedUnits+decimalSeparator+fraction;
        }
    },
    data(){
        return {
            versements: [],
            status: [],
            currencies: [],
            formData: null
        }
    },
    created(){
        this.fetch();
    },
    methods: {
        fetch(){
            this.$http.get(this.url).then(
                (res)=> {
                    this.versements = res.body.payments;
                    this.status = res.body.payments_status;
                    this.currencies = res.body.currencies;
                },
                (err)=> {
                    console.error(err)
                    this.$emit('notifications', 'error', 'Impossible de charger les versements', err);
                }
            );
            console.log('fetch', this.url, this)
        },
        remove(versement){
            console.log("Suppression de ", versement)
            this.$http.delete(this.url+'/'+versement.id).then(
                (res)=> {
                    this.versements.splice(this.versements.indexOf(versement), 1);
                },
                (err)=> {
                    console.error(err)
                    this.$emit('notifications', 'error', 'Impossible de supprimer le versement', err);
                }
            );
        },
        update(versement){
            this.formData = JSON.parse(JSON.stringify(versement));
            /*
            this.$http.put(this.url+'/'+versement.id, versement).then(
                (res)=> {
//                    this.versements.splice(this.versements.indexOf(versement), 1);
                },
                (err)=> {
                    console.error(err)
                    this.$emit('notifications', 'error', 'Impossible de supprimer le versement', err);
                }
            );*/
        }
    },
    computed: {
        total(){
            var total = {
                prevu: 0.0,
                effectif: 0.0
            };
            this.versements.forEach((v)=>{
                var montant = v.amount * v.currency.rate;
                if(v.status != 1)
                    total.effectif += montant;
                total.prevu += montant;
            })
            return total
        }
    }
});

export default versements;
