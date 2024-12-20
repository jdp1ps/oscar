<template>
  <section class="milestones">

    <transition name="fade">
      <div class="error overlay" v-if="error">
        <div class="overlay-content">
          <i class="icon-warning-empty"></i>
          {{ error }}
          <br>
          <a href="#" @click="error = null" class="btn btn-sm btn-default btn-xs">
            <i class="icon-cancel-circled"></i>
            Fermer</a>
        </div>
      </div>
    </transition>

    <transition name="fade">

      <div class="overlay" v-if="formData">
        <div class="overlay-content">

          <h2>
            <i class="icon-calendar"></i>
            <span v-if="formData.id">Modification du jalon <strong>{{ formData.type.label }}</strong></span>
            <span v-else>Nouveau jalon</span>
          </h2>

          <div class="form-group">
            <label for="">Type de jalon</label>
            <select name="" id="" v-model="formData.type.id" class="form-control">
              <optgroup :label="g.label" v-for="g in groupedTypes">
                <option :value="t.id" v-for="t in g.types">{{ t.label }}</option>
              </optgroup>

            </select>
            <p v-show="formTypeFinishable" class="help">
              <i class="icon-info-circled"></i>
              Ce type de jalon inclut des méchanismes de validation pour marquer le jalon comme terminé
            </p>
          </div>

          <div class="form-group">
            <label for="">Date prévue pour le jalon</label>
            <datepicker v-model="formData.dateStart" @input="value => {formData.dateStart = value}"/>
          </div>

          <div class="form-group">
            <label for="">Description</label>
            <textarea v-model="formData.comment" class="form-control"></textarea>
          </div>

          <nav>
            <button class="btn btn-default" @click="performSave">
              <i class="icon-floppy"></i>
              Enregistrer
            </button>
            <button class="btn btn-default" @click="formData = null">
              <i class="icon-cancel-outline"></i>
              Annuler
            </button>
          </nav>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="deleteconfirm overlay" v-if="deleteMilestone">
        <div class="overlay-content">
          <h2><i class="icon-help-circled"></i>
            Supprimer ce jalon ?</h2>
          <p>Cette suppression sera <strong>définitive</strong>, si vous souhaitez signifier que ce jalon est réalisé,
            utilisez plutôt l'option <em>Marquer comme terminé</em>. Si cette option n'est pas disponible, demandez à
            l'administrateur Oscar si vous avez les privilèges pour réaliser cette action ou si le type de jalon
            <strong>{{ deleteMilestone.type.label }}</strong> est correctement configuré.</p>
          <nav>
            <button class="btn btn-default" @click="preformDelete">
              <i class="icon-trash"></i>
              Supprimer
            </button>
            <button class="btn btn-default" @click="deleteMilestone = null">
              <i class="icon-cancel-outline"></i>
              Annuler
            </button>
          </nav>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="validconfirm overlay" v-if="validMilestone">
        <div class="overlay-content">
          <h2>
            <i class="icon-help-circled"></i>
            Valider ce jalon ?
          </h2>
          <p>Les jalons marqués comme terminés ne feront pas l'objet de notifications ou d'alertes.</p>
          <nav>
            <button class="btn btn-default" @click="performValid('valid')">
              <i class="icon-ok-circled"></i>
              Marquer ce jalon comme terminé
            </button>
            <button class="btn btn-default" @click="validMilestone = null">
              <i class="icon-cancel-outline"></i>
              Annuler
            </button>
          </nav>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="inprogressconfirm overlay" v-if="inProgressMilestone">
        <div class="overlay-content">
          <h2>
            <i class="icon-help-circled"></i>
            Marquer ce jalon "en cours" ?
          </h2>
          <p></p>
          <nav>
            <button class="btn btn-default" @click="performValid('inprogress')">
              <i class="icon-cw-outline"></i>
              Marquer ce jalon comme en cours
            </button>
            <button class="btn btn-default" @click="inProgressMilestone = null">
              <i class="icon-cancel-outline"></i>
              Annuler
            </button>
          </nav>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="inprogressconfirm overlay" v-if="actionMessage">
        <div class="overlay-content">
          <h2>
            <i class="icon-help-circled"></i>
            {{ actionMessage }} ?
          </h2>
          <p>Les jalons marqués comme terminés (Validé, refusé ou sans suite) ne feront pas l'objet de notifications ou
            d'alertes</p>
          <nav>
            <button class="btn btn-default" @click="performValid(action)">
              <i class="icon-cw-outline"></i>
              {{ actionMessage }}
            </button>
            <button class="btn btn-default" @click="handlerActionCancel">
              <i class="icon-cancel-outline"></i>
              Annuler
            </button>
          </nav>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="validconfirm overlay" v-if="unvalidMilestone">
        <div class="overlay-content">
          <h2>
            <i class="icon-help-circled"></i>
            Invalider ce jalon ?
          </h2>
          <p>L'état d'avancement du jalon sera réinitialisé.</p>
          <nav>
            <button class="btn btn-success" @click="performValid('unvalid')">
              <i class="icon-ok-circled"></i>
              Réinitialiser la progression de ce jalon
            </button>
            <button class="btn btn-danger" @click="unvalidMilestone = null">
              <i class="icon-cancel-outline"></i>
              Annuler
            </button>
          </nav>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="alert alert-info" v-if="pendingMsg">
        <i class="icon-spinner animate-spin"></i>
        {{ pendingMsg }}
      </div>
    </transition>

    <nav class="admin-bar" v-if="manage">
      <a href="#" @click.prevent="handlerNew" class="btn btn-xs btn-default">
        <i class="icon-calendar-plus-o"></i>
        Nouveau Jalon
      </a>
      <a href="#" @click.prevent="fetch" class="btn btn-xs btn-warning">
        <i class="icon-bug"></i>
        fetch
      </a>

    </nav>
    {{ progression }}
    <section class="list" v-if="milestones != null">
      <milestone :milestone="m" v-for="m in milestones" :key="m.id"
                 :manage="manage"
                 :progression="progression"
                 @valid="handlerValid"
                 @unvalid="handlerUnvalid"
                 @inprogress="handlerInProgress"
                 @cancel="handlerActionConfirm($event, 'cancel','Marquer ce jalon comme sans suite')"
                 @refused="handlerActionConfirm($event, 'refused','Marquer ce jalon comme refusé')"
                 @remove="handlerRemove"
                 @edit="handlerEdit"
      />
    </section>
    <div class="alert" v-else>
      Aucun jalon
    </div>
  </section>

</template>
<script>

//////////////////////////////////////////////////////////////
import MilestoneItem from './MilestoneItem.vue'
import Datepicker from './../components/Datepicker.vue'
import moment from 'moment';
import axios from 'axios';
import GlobalModel from "../models/GlobalModel.js";


export default {
  props: {
    'url': {'required': true},
    'manage': {'required': true},
    'progression': { default: false },
    // Payements chargés depuis un autre composant
    'items' : { default: [], type: Array },
    'types' : { default: [], type: Array },
    'payments': {'required': false, default: [], type: Array}
  },

  components: {
    'milestone': MilestoneItem,
    'datepicker': Datepicker
  },
  data() {
    return {
      error: null,
      formData: null,
      pendingMsg: "",
      creatable: false,
      deleteMilestone: null,
      editMilestone: null,
      validMilestone: null,
      cancelMilestone: null,
      refusedMilestone: null,
      unvalidMilestone: null,
      inProgressMilestone: null,

      //
      action: null,
      actionMessage: "",
      actionMilestone: null,
      model: {},
    }
  },

  computed: {
    //// MODEL
    types() {
      return this.types;
    },

    milestones() {
      let milestones = [];

      this.payments.forEach(payment => {

        // Récupération de la bonne date
        let datePayment = new Date(),
            late = false,
            done = false,
            comment;
        switch (payment.status) {
          case 1 :
            datePayment = payment.datePredicted;
            if (!datePayment) {
              comment = "ERREUR DE DATE"
            } else {
              comment = "PRÉVU";
              late = moment(payment.datePredicted.date).unix() < moment().unix();
              if (late) comment += " EN RETARD";
            }

            break;
          case 2 :
            datePayment = payment.datePayment;
            comment = "RÉALISÉ";
            done = true;
            break;
          default:
            return;
        }
        if (!datePayment)
          datePayment = new Date();

        milestones.push({
          dateStart: datePayment,
          comment: 'VERSEMENT ' + comment,
          deletable: false,
          late: late,
          done: done,
          editable: false,
          validable: false,
          isPayment: true,
          type: {
            label: 'Versement de ' + payment.amount + payment.currency.symbol,
            facet: 'payment'
          }
        });
      });

      this.items.forEach(milestone => {
        milestones.push(milestone);
      });

      milestones.sort((a, b) => {
        let vA = moment(a.dateStart).unix();
        let vB = moment(b.dateStart).unix();
        return vA - vB;
      });

      return milestones;
    },

    payments() {
      return this.payments;
    },

    formTypeFinishable() {
      if (!this.formData)
        return false;
      return this.types.find(type => type.id == this.formData.type.id && type.finishable);
    },

    groupedTypes() {
      let groupedTypes = {};
      this.types.forEach(type => {
        let facet = type.facet;
        if (!groupedTypes.hasOwnProperty(facet)) {
          groupedTypes[facet] = {
            label: facet,
            types: []
          };
        }
        groupedTypes[type.facet].types.push(type);
      });
      return groupedTypes;
    }
  },

  methods: {
    ////////////////////////////////////////////////////////////////
    //
    // HANDLERS
    //
    ////////////////////////////////////////////////////////////////

    /**
     * Demande de validation
     */
    handlerValid(milestone) {
      this.validMilestone = milestone;
    },

    handlerInProgress(milestone) {
      this.inProgressMilestone = milestone;
    },

    handlerUnvalid(milestone) {
      this.unvalidMilestone = milestone;
    },

    handlerActionConfirm(milestone, action, actionMessage) {
      this.actionMilestone = milestone;
      this.action = action;
      this.actionMessage = actionMessage;
    },

    handlerActionCancel() {
      this.actionMilestone = null;
      this.action = null;
      this.actionMessage = "";
    },

    handlerCancel(milestone) {
      this.unvalidMilestone = milestone;
    },

    /**
     * Demande de suppression
     */
    handlerRemove(milestone) {
      this.deleteMilestone = milestone;
    },

    /**
     * Édition : Hydratation du formulaire
     */
    handlerEdit(milestone) {
      this.editMilestone = milestone;
      this.formData = {
        type: milestone.type,
        id: milestone.id,
        comment: milestone.comment,
        dateStart: moment(milestone.dateStart).format('YYYY-MM-DD'),
      };
    },

    /**
     * Création : Hydratation du formulaire
     */
    handlerNew() {
      this.formData = {
        id: 0,
        type: JSON.parse(JSON.stringify(this.types[0])),
        dateStart: moment().format('Y-M-D HH:mm:ss'),
        comment: ""
      };
    },


    ////////////////////////////////////////////////////////////////
    //
    // OPERATIONS REST
    //
    ////////////////////////////////////////////////////////////////

    /**
     * Suppression : Envoi REST
     */
    preformDelete() {
      this.pendingMsg = "Suppression du jalon";
      axios.delete(this.url + "?id=" + this.deleteMilestone.id).then(
          success => {
            this.fetch();
          },
          error => {
            GlobalModel.commit("addErrorAxios", error);
          }
      ).then(foo => {
        this.pendingMsg = null;
        this.deleteMilestone = null;
      })
    },

    /**
     * Marquer le jalon comme terminé.
     */
    performValid(action) {
      var datas = {}, milestone;

      switch (action) {
        case 'valid':
          this.pendingMsg = "Validation du jalon";
          milestone = this.validMilestone;
          break;

        case 'unvalid':
          this.pendingMsg = "Réinitialisation du jalon";
          milestone = this.unvalidMilestone;
          break;

        case 'inprogress':
          this.pendingMsg = "Marquage du jalon comme en cours";
          milestone = this.inProgressMilestone;
          break;

        case 'cancel':
        case 'refused':
          milestone = this.actionMilestone;
          break;

        default :
          this.error = "Action incorrecte";
          return;
          break;
      }

      datas.id = milestone.id;
      datas.action = action;

      this.action = null;
      this.actionMessage = "";
      this.actionMilestone = null;

      axios.put(this.url, datas).then(
          success => {
            this.fetch();
          },
          error => {
            GlobalModel.commit("addErrorAxios", error);
          }
      ).then(foo => {
        this.pendingMsg = null;
        this.validMilestone = null;
        this.unvalidMilestone = null;
        this.inProgressMilestone = null;
      })
    },

    /**
     * Enregistrement des données (Création ou édition)
     */
    performSave() {
      this.pendingMsg = this.formData.id ? "Enregistrement des modifications" : "Création du nouveau jalon";

      if( this.formData.id ) {
        axios.put(this.url, this.formData).then(
            success => {
              this.fetch();
            },
            error => {
              GlobalModel.commit("addErrorAxios", error);
            }
        ).then(foo => {
          this.pendingMsg = null;
          this.formData = null;
        });
      } else {
        axios.post(this.url, this.formData).then(
            success => {
              this.fetch();
            },
            error => {
              GlobalModel.commit("addErrorAxios", error);
            }
        ).then(foo => {
          this.pendingMsg = null;
          this.formData = null;
        });
      }
    },

    /**
     * Chargement des jalons depuis l'API
     */
    fetch() {
      this.pendingMsg = "Chargement des jalons : " + this.url;
      axios.get(this.url).then(
          success => {
            this.$emit('update', success.data.datas.milestones.entities);
          },
          error => {
            console.log(error);
            this.error = "Impossible de charger les jalons de cette activités : " + error
          }
      ).then(n => {
        this.pendingMsg = "";
      });
    },
  },

  mounted() {

  }
}
</script>