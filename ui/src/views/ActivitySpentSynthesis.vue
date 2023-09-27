<template>
  <section>
    <transition name="fade">
      <div class="overlay" v-if="showCuration">
        <div class="overlay-content">
          <h3>
            Qualification des comptes
            <span class="overlay-closer" @click="showCuration = false">X</span>
          </h3>
          <p>Les comptes suivants ne sont pas qualifiés, vous pouvez utiliser cet écran pour les attribuer à une masse budgétaire :</p>
          <div class="card row" v-for="c in synthesis.curations">
            <div class="col-md-4">
              <strong>{{ c.compte }}</strong> - <em>{{ c.compteInfos.label }}</em>
            </div>
            <div class="col-md-8">
              <select name="" id="" class="form-control" v-model="affectations[c.compte]" @change="updateAffectations(c.compte, $event)">
                <option value="0">Ignorer</option>
                <option value="1">Traiter comme une recette</option>
                <option :value="masse" v-for="text,masse in masses">{{ text }}</option>
              </select>
            </div>
          </div>
          <hr>
          <button @click="handlerCurationCancel" class="btn btn-danger"><i class="icon-cancel-circled"></i>Annuler</button>
          <button @click="handlerCurationConfirm" class="btn btn-success"><i class="icon-floppy"></i>Enregistrer</button>
        </div>
      </div>
    </transition>

    <transition name="fade">
      <div class="alert alert-danger" v-if="error">
        <i class="icon-attention-1"></i>
        Il y'a eut un problème lors de la récupération des données financières :
        {{ error }}
      </div>
    </transition>

    <transition name="fade">
      <div class="alert-warning alert" v-if="warning">
        <i class="icon-warning-empty"></i>
        Les données affichées peuvent ne pas être à jour :
        {{ warning }}
      </div>
    </transition>

    <transition name="fade">
      <div class="pending" v-if="pendingMsg">
        <div class="">
          <i class="icon-spinner animate-spin"></i>
          {{ pendingMsg }}
        </div>
      </div>
    </transition>

    <table class="table table-condensed" v-if="!pendingMsg && synthesis != null">
      <tr>
        <th>masse</th>
        <th style="text-align: right;">Réalisées</th>
        <th style="text-align: right;">Engagées</th>
      </tr>
      <tr v-for="m,k in masses">
        <th>{{ m }}</th>
        <td style="text-align: right; white-space: nowrap">{{ $filters.money(synthesis['effective_totals'][k]) }} €</td>
        <td style="text-align: right; white-space: nowrap">{{ $filters.money(synthesis['predicted_totals'][k]) }}&nbsp;€</td>
      </tr>
      <tr style="border-top: solid #000 thin" v-if="synthesis['N.B']">
        <th>
          Hors masse<br>
          <small style="font-weight: 300" class="error-block"><i class="icon-attention"></i> Les annexes de certains comptes ne sont pas renseignés :
            <ul>
              <li v-for="c in getNoMasse"><strong>{{c}}</strong>
                <div v-if="synthesis.curations">

                </div>
              </li>
            </ul>
            <a @click="handlerCuration" v-if="manageDepense" class="btn btn-xs btn-default"> <i class="icon-cog"></i>Qualifer les comptes</a>
            <span v-else>Merci de contacter un administrateur pour que les annexes des comptes soient configurés.</span>
          </small>
        </th>
        <td style="text-align: right; white-space: nowrap">{{ $filters.money(synthesis['effective_totals']['N.B']) }}&nbsp;€</td>
        <td style="text-align: right; white-space: nowrap">{{ $filters.money(synthesis['predicted_totals']['N.B']) }}&nbsp;€</td>
      </tr>
      <tr style="border-top: solid #000 thin; font-size: 1.6em">
        <th>TOTAL : </th>
        <td style="text-align: right; white-space: nowrap">{{ $filters.money(synthesis['effective_total']) }}&nbsp;€</td>
        <td style="text-align: right; white-space: nowrap">{{ $filters.money(synthesis['predicted_total']) }}&nbsp;€</td>
      </tr>
    </table>

    <table class="table table-condensed" v-if="synthesis && synthesis.recettes">
      <tr>
        <th><i class="icon-euro"></i>Recettes</th>
        <td style="text-align: right; white-space: nowrap">{{ $filters.money(synthesis.recettes.total) }}&nbsp;€</td>
      </tr>
    </table>
    <small>Données mise à jour : <strong v-if="dateUpdated">{{ dateUpdated.date | dateFull }}</strong></small>
  </section>
</template>
<script>
import axios from "axios";

export default {
  props: {
    url: {
      required: true
    }
  },

  computed:{
    synthesis(){
      return this.infos
    }
  },

  data(){
    return {
      infos: null,
      pendingMsg: null,
      showCuration: false,
      masses: []
    }
  },

  methods: {
    fetch(){
      this.pendingMsg = "Chargement des données financières";
      axios.get(this.url).then(
          ok => {
            console.log("OK",ok);
            this.infos = ok.data.synthesis;
            this.masses = ok.data.masses;
          },
          ko => {
            console.log(ko)
          }
      ).then(foo=> {
        this.pendingMsg = false;
      })
    }
  },

  mounted() {
    this.fetch()
  }
}
</script>