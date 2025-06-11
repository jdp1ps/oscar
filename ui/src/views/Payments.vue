<template>
  <section class="payments">
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
                         type="text"/>
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
                  <label class=" control-label">Date prévue</label>
                  <datepicker v-model="formData.datePredicted"
                              @input="value => {formData.datePredicted = value}"/>

                  <div class="oscar-form-message error" v-if="formData.status == 1 && !formData.datePredicted">
                    Les versements prévisionnels necessitent une date.
                  </div>
                </div>
              </div>
              <div class="col-xs-6">
                <div class="form-group  ">
                  <label class=" control-label">Statut</label>
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
                                v-model="formData.datePayment"
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

    <nav class="admin-bar" v-if="manage">
      <a href="#" @click.prevent="handlerNewPayment" class="btn btn-default btn-xs">
        <i class="icon-bank"></i>
        Nouveau versement</a>
      <button class="btn btn-warning btn-xs" @click.prevent="fetch" v-if="debugEnabled">
        <i class="icon-bug"></i>
        fetch
      </button>
    </nav>
    <payment v-for="p in payments" :payment="p" :key="p.id" :manage="manage"
             @delete="handlerDelete"
             @edit="handlerEdit"/>

    <article class="payment total">
      <div class="heading">
        <strong class="amount text-private">
          {{ $filters.money(total)}} €
        </strong>
        <span class="date">/
                  <strong class="text-private">{{ $filters.money(amount)}} €</strong>
                </span>
      </div>
    </article>

    <div class="alert alert-danger alertAmount" v-if="total != amount">
      <p><i class="icon-attention-1"></i>
        Le total des versements prévus et réalisés ne
        semble pas correspondre avec le montant prévu
        initialement, Somme des versements :</p>
      <ul>
        <li><strong class="amountPrevu text-private">{{ $filters.money(total) }} {{ currencySymbol }}</strong> en versement,</li>
        <li><strong title="Valeur exacte : <?= $entity->getAmount() ?>" class="text-private">
          {{ $filters.money(amount)}} {{ currencySymbol }}</strong> prévu
        </li>
      </ul>
    </div>

  </section>
</template>
<script>
import Payment from './PaymentItem.vue';
import Datepicker from './../components/Datepicker.vue';
import axios from "axios";
import moment from "moment";
import AxiosMessage from "../utils/AxiosMessage.js";

export default {
  props: ['url', 'amount', 'currency', 'currencies', 'manage', 'payments', 'debugEnabled'],

  data() {
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

    total() {
      let total = 0.0;
      if( this.payments ){
        this.payments.forEach(payment => {
          let rate = 1;
          if (payment.currency) {
            rate = payment.rate;
          }
          total += payment.amount / rate;
        })
      }
      return Math.round(total * 100) / 100;
    },

    /**
     * Retourne le symbole de la devise.
     *
     * @returns {any}
     */
    currencySymbol() {
      return this.currency ? this.currency.symbol : "€";
    },

    formHasError() {
      return !this.formData.amount ||
          (this.formData.status == 2 && !this.formData.datePayment) ||
          (this.formData.status == 1 && !this.formData.datePredicted);
    }
  },

  methods: {
    getPaymentDateValue(payment) {
      if (payment.status == 2)
        return payment.datePayment ? payment.datePayment.date : null;
      else if (payment.status == 1)
        return payment.datePredicted ? payment.datePredicted.date : null;
      return null;
    },

    handlerNewPayment() {
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

    handlerDelete(payment) {
      this.deletePayment = payment;
    },

    handlerEdit(payment) {
      this.formData = JSON.parse(JSON.stringify(payment));
      this.formData.currencyId = payment.currency.id;
      this.formData.datePayment = payment.datePayment ? moment(payment.datePayment).format('YYYY-MM-DD') : "";
      this.formData.datePredicted = payment.datePredicted ? moment(payment.datePredicted).format('YYYY-MM-DD') : "";
      this.formData.currencyId = payment.currency.id;
    },

    /**
     * En cas de changement de devise, on actualise automatiquement le taux de conversion en EURO.
     */
    handlerFormUpdateRate() {
      let currency = this.currencies.find((c) => {
        return c.id == this.formData.currencyId
      })
      if (currency) {
        this.formData.rate = currency.rate;
      }
    },

    /**
     * Enregistrement du formulaire
     */
    performSave() {

      this.loading = "Enregistrement du paiement";

      if (this.formHasError) {
        return;
      }

      if (this.formData.id) {
        axios.post(this.url, this.formData).then(
            (response) => {
              this.formData = null;
              this.fetch();
            },
            (error) => {
              this.error = AxiosMessage.manageErrorResponse(error);
            }
        )
      } else {
        axios.put(this.url, this.formData).then(
            (response) => {
              this.formData = null;
              this.fetch();
            },
            (error) => {
              this.error = AxiosMessage.manageErrorResponse(error);
            }
        )
      }
    },

    /**
     * Suppression effective du versement.
     */
    performDelete() {
      axios.delete(this.url + '?id=' + this.deletePayment.id).then(
          (success) => {
            this.fetch();
          },
          (fail) => {
            this.error = "Impossible de supprimer le versement : " + AxiosMessage.manageErrorResponse(fail).message;
          }
      ).then(() => {
        this.deletePayment = null;
      });
    },

    /**
     * Chargement des versements depuis l'API
     */
    fetch() {
      this.loading = "Chargement des versements";
      axios.get(this.url).then(
          (success) => {
            this.$emit('update', success.data.datas.payments);
          },
          (fail) => {
            this.error = AxiosMessage.manageErrorResponse(fail);
          }
      ).finally(f => this.loading = null);
    }
  },

  mounted() {
    // this.fetch()
  }
}
</script>