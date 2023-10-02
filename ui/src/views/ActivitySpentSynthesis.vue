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

    <table class="table table-condensed card synthesis" v-if="synthesis">
      <thead>
      <tr>
        <th>Masse</th>
        <th style="text-align: right">Engagé</th>
        <th style="text-align: right">Réalisé</th>
      </tr>
      </thead>

      <tbody>
      <tr v-for="dt,key in synthesis.masses">
        <th>
          <small>{{ dt }}</small>
          <a class="label label-info xs" :href="'#repport-' + key">{{ synthesis.synthesis[key].nbr_effectue }} /
            {{ synthesis.synthesis[key].nbr_engage }}</a>
        </th>
        <td style="text-align: right">{{ $filters.money(synthesis.synthesis[key].total_engage) }}</td>
        <td style="text-align: right">{{ $filters.money(synthesis.synthesis[key].total_effectue) }}</td>
      </tr>
      </tbody>
      <tbody>
      <tr class="total">
        <th>Total</th>
        <td style="text-align: right">{{ $filters.money(synthesis.synthesis.totaux.engage) }}</td>
        <td style="text-align: right">{{ $filters.money(synthesis.synthesis.totaux.effectue) }}</td>
      </tr>
      </tbody>
      <tbody>
      <tr v-if="synthesis.synthesis['N.B'].total != 0">
        <th>
          <small><i class="icon-attention"></i> Hors-masse</small>
          <a href="#repport-nb" class="label label-info">{{ synthesis.synthesis['N.B'].nbr}}</a>
        </th>
        <td style="text-align: right">{{ $filters.money(synthesis.synthesis['N.B'].total_engage) }}</td>
        <td style="text-align: right">{{ $filters.money(synthesis.synthesis['N.B'].total_effectue) }}</td>
      </tr>
      </tbody>
    </table>

    <div v-if="manageRecettes">
      <h3><i class="icon-calculator"></i>Recettes</h3>
      <table class="table table-condensed card synthesis" v-if="spentlines">
        <tbody>
        <tr>
          <th>Recette <a class="label label-info xs" href="#repport-1">{{ synthesis.synthesis['1'].nbr}}</a></th>
          <td style="text-align: right">{{ $filters.money(synthesis.synthesis['1'].total)}}</td>
        </tr>
        </tbody>
      </table>
    </div>

    <div v-if="manageIgnored && synthesis.synthesis['0'].total != 0">
      <a href="#" @click.prevent="displayIgnored = !displayIgnored">
        <span v-if="displayIgnored"><i class="icon-eye-off"></i> Cacher</span>
        <span v-else><i class="icon-eye"></i> Montrer</span>
        les données ignorées
      </a>
      <table class="table table-condensed card synthesis" v-if="spentlines && displayIgnored">
        <tbody>
        <tr>
          <th>
            Ignorées
            <a class="label label-info" href="#repport-0">{{ synthesis.synthesis['0'].nbr}}</a>
          </th>
          <td style="text-align: right">{{ $filters.money(synthesis.synthesis['0'].total)}}</td>
        </tr>
        </tbody>
      </table>
    </div>

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
      masses: [],
      synthesis: null
    }
  },

  methods: {
    fetch(){
      this.pendingMsg = "Chargement des données financières";
      axios.get(this.url).then(
          ok => {
            this.synthesis = ok.data.synthesis;
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