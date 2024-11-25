<template>
    <section class="payments">
        <h2><i class="icon-bank"></i> Versements</h2>

        <transition name="fade">
            <div class="error overlay" v-if="error">
                <div class="overlay-content">
                    <i class="icon-warning-empty"></i>
                    {{ error }}
                    <br>
                    <a href="#" @click="error = null" class="btn btn-default">
                        <i class="icon-cancel-outline"></i>
                        Fermer</a>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="pending overlay" v-if="pendingMsg">
                <div class="overlay-content">
                    <i class="icon-spinner animate-spin"></i>
                    {{ pendingMsg }}
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="deleteconfirm overlay" v-if="deletePayment">
                <div class="overlay-content">
                    <h3><i class="icon-help-circled"></i> Supprimer ce versement ?</h3>
                    <nav>
                        <button class="btn btn-default" @click="performDelete">
                            <i class="icon-trash"></i>
                            Supprimer
                        </button>
                        <button class="btn btn-default" @click="deletePayment = null">
                            <i class="icon-cancel-outline"></i>
                            Annuler
                        </button>
                    </nav>
                </div>
            </div>
        </transition>

        <transition name="fade">
            <div class="overlay" v-if="formData">
                <div class="overlay-content">

                    <h3 v-if="formData.id">Modification du versement</h3>
                    <h3 v-else>Nouveau versement</h3>

                    <div class="container">
                        <div class="row">
                            <div class="col-xs-6">
                                <div class="form-group  ">
                                    <label class="control-label" for="amount">Montant</label>
                                    <input name="amount" class="form-control input-lg form-control"
                                           v-model="formData.amount"
                                           type="text" />
                                    <div class="oscar-form-message error" v-if="!formData.amount">
                                        Vous devez indiquer un montant.
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <div class="form-group  ">
                                    <label class=" control-label">Devise pour le versement</label>
                                    <select name="currency" class="form-control form-control"
                                            v-model="formData.currencyId"
                                            @change="handlerFormUpdateRate">
                                        <option v-for="c in currencies" :value="c.id">{{ c.label }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <div class="form-group  ">
                                    <label class=" control-label">Taux</label>
                                    <input name="rate" class="form-control form-control"
                                           v-model="formData.rate"
                                           type="text">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-6">
                                <div class="form-group  ">
                                    <label class=" control-label" >Date prévue</label>
                                    <datepicker :moment="moment"
                                                :value="formData.datePredicted"
                                                @input="value => {formData.datePredicted = value}"/>

                                    <div class="oscar-form-message error" v-if="formData.status == 1 && !formData.datePredicted">
                                        Les versements prévisionnels necessitent une date.
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-6">
                                <div class="form-group  ">
                                    <label class=" control-label" >Statut</label>

                                    <select name="status" class="form-control form-control" v-model="formData.status">
                                        <option value="1" selected="selected">Prévisionnel</option>
                                        <option value="2">Réalisé</option>
                                        <option value="3">Écart</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="done">
                            <h2>Informations sur le versement effectif</h2>
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group  ">
                                        <label class=" control-label">Date effective</label>
                                        <datepicker :moment="moment"
                                                    :value="formData.datePayment"
                                                    @input="value => {formData.datePayment = value}"/>

                                        <div class="oscar-form-message error" v-if="formData.status == 2 && !formData.datePayment">
                                            Les versements réalisés necessitent une date effective
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group  ">
                                        <label class=" control-label">N° de pièce</label>
                                        <input name="codeTransaction"
                                               class="form-control form-control"
                                               v-model="formData.codeTransaction" type="text">
                                    </div>
                                    <p class="help">Numéro permettant d'identifier l'opération auprès des services comptables.</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-group  ">
                            <label class=" control-label">Commentaire</label>
                            <textarea name="comment" class="form-control form-control"
                                      placeholder="Commentaire"
                                      v-model="formData.comment"></textarea>
                        </div>

                        <nav class="text-center">
                            <nav class="btn-group">
                                <a class="btn btn-default button-back" href="#" @click.prevent="formData = null">Annuler</a>
                                <button class="btn btn-primary"
                                        @click.prevent="performSave"
                                        :class="{ 'disabled': formHasError }">
                                    Enregistrer
                                </button>
                            </nav>
                        </nav>
                    </div>
                </div>
            </div>
        </transition>
        <payment v-for="p in payments" :payment="p" :moment="moment" :key="p.id" :manage="manage"
                @delete="handlerDelete"
                @edit="handlerEdit" />

        <article class="payment total">
            <div class="heading">
                <strong class="amount">
                    {{ total | money }} {{ currencySymbol }}
                </strong>
                <span class="date">/<strong>{{ amount | money }} {{ currencySymbol }}</strong></span>
            </div>
        </article>

        <div class="alert alert-danger alertAmount" v-if="total != amount">
            <p><i class="icon-attention-1"></i>
                Le total des versements prévus et réalisés ne
                semble pas correspondre avec le montant prévu
                initialement, Somme des versements :</p>
            <ul>
                <li><strong class="amountPrevu">{{ total | money }} {{ currencySymbol }}</strong> en versement,</li>
                <li><strong title="Valeur exacte : <?= $entity->getAmount() ?>">
                    {{ amount | money}} {{ currencySymbol }}</strong> prévu</li>
            </ul>
        </div>

        <nav class="text-right" v-if="manage">
            <a href="#" @click.prevent="handlerNewPayment" class="oscar-link">
                <i class="icon-bank"></i>
                Nouveau versement</a>
        </nav>
    </section>
</template>
<script>
    import Payment from './PaymentItem.vue';
    import Datepicker from './Datepicker.vue';

    // nodejs node_modules/.bin/poi watch --format umd --moduleName  Payments --filename.css Payments.css --filename.js Payments.js --dist public/js/oscar/dist public/js/oscar/src/Payments.vue

    export default {
        props: ['model', 'moment', 'url', 'amount', 'currency', 'currencies', 'manage'],

        data(){
            return {
                formData: null,
                deletePayment: null,
                error: "",
                pendingMsg: ""
            }
        },

        components: {
            'payment': Payment,
            'datepicker': Datepicker,
        },

        computed: {
            payments(){
                let payments = this.model.payments;
                payments.sort( (a, b) => {
                    return this.moment(this.getPaymentDateValue(a)).unix()
                        - this.moment(this.getPaymentDateValue(b)).unix()
                });
                return payments;
            },
            total(){
                let total = 0.0;
                this.model.payments.forEach( payment => {
                    let rate = 1;
                    if( payment.currency ){
                        rate = payment.rate;
                    }
                    total += payment.amount / rate;
                })
                return Math.round(total*100)/100;
            },

            /**
             * Retourne le symbole de la devise.
             *
             * @returns {any}
             */
            currencySymbol(){
                return this.currency ? this.currency.symbol : "?";
            },

            formHasError(){
                return !this.formData.amount ||
                    (this.formData.status == 2 && !this.formData.datePayment) ||
                    (this.formData.status == 1 && !this.formData.datePredicted);
            }
        },

        methods: {
            getPaymentDateValue( payment ){
                if( payment.status == 2 )
                    return payment.datePayment ? payment.datePayment.date : null;
                else if ( payment.status == 1 )
                    return payment.datePredicted ? payment.datePredicted.date : null;
                return null;
            },

            handlerNewPayment(){
                this.formData = {
                    id: null,
                    amount: 0.0,
                    currencyId: 1,
                    rate: 1.0,
                    datePredicted: "",
                    status: 1,
                    datePayment: "",
                    codeTransaction: "",
                    comment: ""
                }
            },

            handlerDelete(payment){
                this.deletePayment = payment;
            },

            handlerEdit(payment){
                this.formData = JSON.parse(JSON.stringify(payment));
                this.formData.currencyId = payment.currency.id;
                this.formData.datePayment = payment.datePayment ? this.moment(payment.datePayment.date).format('YYYY-MM-DD') : "";
                this.formData.datePredicted = payment.datePredicted ? this.moment(payment.datePredicted.date).format('YYYY-MM-DD') : "";
                this.formData.currencyId = payment.currency.id;
            },

            /**
             * En cas de changment de devise, on actualise automatiquement le taux de conversion en EURO.
             */
            handlerFormUpdateRate(){
                let currency = this.currencies.find( (c)=> {
                    return c.id == this.formData.currencyId
                })
                if( currency ){
                    this.formData.rate = currency.rate;
                }
            },

            /**
             * Enregistrement du formulaire
             */
            performSave(){

                if( this.formHasError ){
                    return;
                }

                var datas = new FormData();
                datas.append('id', this.formData.id);
                datas.append('amount', this.formData.amount.toString().replace(',', '.'));
                datas.append('currencyId', this.formData.currencyId);
                datas.append('rate', this.formData.rate);
                datas.append('datePredicted', this.formData.datePredicted ? this.formData.datePredicted : "");
                datas.append('status', this.formData.status);
                datas.append('datePayment', this.formData.datePayment ? this.formData.datePayment : "");
                datas.append('codeTransaction', this.formData.codeTransaction);
                datas.append('comment', this.formData.comment);

                this.pendingMsg = "Enregistrement du versement";
                datas.append('action', this.formData.id ? 'update' : 'create');


                this.$http.post(this.url, datas).then(
                    success => {
                        this.fetch();
                    },
                    fail => {
                        this.error = fail.body;
                    }
                ).then( () => {
                    this.pendingMsg = "";
                    this.formData = null;
                })
            },

            /**
             * Suppression effective du versement.
             */
            performDelete(){
                this.$http.delete(this.url + '?id=' + this.deletePayment.id).then(
                    (success) => {
                        this.fetch();
                    },
                    (fail) => {
                        this.error = "Impossible de supprimer le versement : " + fail.body;
                    }
                ).then( () => {
                    this.deletePayment = null;
                });
            },

            /**
             * Chargement des versements depuis l'API
             */
            fetch(){
                this.$http.get(this.url).then(
                    (success) => {
                        this.model.payments = success.data;
                    },
                    (fail) => {
                        this.error = "Impossible de charger les versements : " + fail.body;
                    }
                );
            }
        },

        mounted(){
            this.fetch()
        }
    }
</script>