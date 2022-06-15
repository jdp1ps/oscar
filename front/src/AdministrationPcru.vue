<template>
  <section style="position: relative; min-height: 100px">
    <div v-if="configuration" class="container">

      <div class="overlay" v-if="loading">
        <div class="overlay-content" :class="{ 'text-success bold': success}">
          <i class="animate-spin icon-spinner"></i>
          {{ loading }}
        </div>
      </div>


      <form action="" method="post">
        <div class="row">
          <div class="col-md-3">
            Module <strong>{{ configuration.pcru_enabled ? 'Actif' : 'Inactif' }}</strong>
          </div>
          <div class="col-md-9">
            <div class="material-switch">
              <input id="pcru_enabled" name="pcru_enabled" type="checkbox" v-model="configuration.pcru_enabled"/>
              <label for="pcru_enabled" class="label-primary"></label>
            </div>
          </div>
        </div>

        <section :class="configuration.pcru_enabled ? 'enabled' : 'disabled'">

          <div class="row">
            <div class="col-md-6">
              <h2>
                <i class="icon-upload"></i>
                Accès FTP</h2>

              <div class="alert alert-info">
                <i class="icon-info-circled"></i>
                Le transfert FTP n'est pas encore activé dans cette version
              </div>

              <div class="row">
                <div class="col-md-8 col-md-push-1">
                  <div class="form-group">
                    <label class="sr-only" for="host"></label>
                    <div class="input-group input-lg">
                      <div class="input-group-addon">
                        <i class="glyphicon icon-building"></i> <strong>Hôte</strong>
                      </div>
                      <input type="text" id="host" name="host" v-model="configuration.pcru_host" class="form-control"/>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-8 col-md-push-1">
                  <div class="form-group">
                    <label class="sr-only" for="port"></label>
                    <div class="input-group input-lg">
                      <div class="input-group-addon">
                        <i class="glyphicon icon-logout"></i> <strong>Port</strong>
                      </div>
                      <input type="text" id="port" name="port" v-model="configuration.pcru_port" class="form-control"/>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-8 col-md-push-1">
                  <div class="form-group">
                    <label class="sr-only" for="user"></label>
                    <div class="input-group input-lg">
                      <div class="input-group-addon">
                        <i class="glyphicon icon-user"></i> <strong>Identifiant</strong>
                      </div>
                      <input type="text" id="user" name="user" v-model="configuration.pcru_user" class="form-control"/>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-8 col-md-push-1">
                  <password-field :value="configuration.pcru_pass"
                                  :name="'pass'"
                                  :text="'Mot de passe'"
                                  @change="configuration.pcru_pass = $event"/>
                </div>
              </div>

              <div class="row">
                <div class="col-md-8 col-md-push-1">
                  <div class="form-group">
                    <label class="sr-only" for="ssh"></label>
                    <div class="input-group input-lg">
                      <div class="input-group-addon">
                        <i class="glyphicon icon-plug"></i> <strong>Clef SSH</strong>
                      </div>
                      <input type="text" id="ssh" name="ssh" v-model="configuration.pcru_ssh" class="form-control"/>
                    </div>
                  </div>
                </div>
              </div>

            </div>

            <div class="col-md-6">
              <h2>
                <i class="icon-cog"></i>
                Options</h2>

              <div class="row">
                <div class="col-md-10 col-md-push-1">
                  <div class="form-group">
                    <label class="sr-only" for="user"></label>

                    <div class="input-group input-lg">
                      <div class="input-group-addon">
                        <i class="glyphicon icon-user"></i>
                        <strong>Type pour le contrat signé</strong>
                      </div>

                      <select name="" id="" class="form-control" v-model="configuration.pcru_contract_type">
                        <option
                            :value="type" v-for="type in configuration.contract_types"
                            :key="type">{{ type }}
                        </option>
                      </select>
                    </div>
                    <p class="alert alert-info">
                      <i class="icon-info-circled"></i>
                      Le type de contrat <strong>{{ configuration.pcru_contract_type }}</strong> sera utilisé par oscar pour selectionner le document à utiliser pour les données PCRU</p>
                  </div>
                  <hr>

                  <div class="form-group">
                    <label class="sr-only" for="user"></label>

                    <div class="input-group input-lg">
                      <div class="input-group-addon">
                        <i class="glyphicon icon-user"></i>
                        <strong>Responsable scientifique</strong>
                      </div>

                      <select name="" id="" class="form-control" v-model="configuration.pcru_incharge_role">
                        <option
                            :value="role" v-for="role in configuration.incharge_roles"
                            :key="role">{{ role }}
                        </option>
                      </select>
                    </div>
                    <p class="alert alert-info">
                      <i class="icon-info-circled"></i>
                      Oscar selectionnera les personnes avec le rôle <strong>{{ configuration.pcru_incharge_role }}</strong> de la fiche activité pour extraire le <em>Responsable scientifique côté PCRU</em>.</p>
                  </div>
                  <hr>

                  <div class="form-group">
                    <label class="sr-only" for="user"></label>
                    <div class="input-group input-lg">
                      <div class="input-group-addon">
                        <i class="glyphicon icon-user"></i>
                        <strong>Partenaire(s)</strong>
                      </div>

                      <div class="form-check" v-for="role,index in configuration.partner_roles">
                        <input type="checkbox"
                               :value="role"
                               v-model="configuration.pcru_partner_roles"
                               :id="'partner_role_option_' + index"
                        />
                        <label :for="'partner_role_option_' + index" class="form-check-label">{{ role }}</label>
                      </div>
                    </div>
                    <p class="alert alert-info">
                      <i class="icon-info-circled"></i>
                      Oscar utilisera le(s) rôle(s) <strong>{{ configuration.pcru_partner_roles.join(", ") }}</strong> des oragnisations de la fiche activité pour extraire le(s) partenaire(s) (les codes SIRET/EN doivent être renseignés).</p>
                  </div>

                  <hr>

                  <div class="form-group">
                    <label class="sr-only" for="user"></label>
                    <div class="input-group input-lg">
                      <div class="input-group-addon">
                        <i class="glyphicon icon-user"></i>
                        <strong>Unité(s)</strong>
                      </div>

                      <div class="form-check" v-for="role,index in configuration.unit_roles">
                        <input type="checkbox"
                               :value="role"
                               v-model="configuration.pcru_unit_roles"
                               :id="'unit_role_option_' + index"
                        />
                        <label :for="'unit_role_option_' + index" class="form-check-label">{{ role }}</label>
                      </div>
                    </div>
                  </div>
                  <p class="alert alert-info">
                    <i class="icon-info-circled"></i>
                    Oscar utilisera le(s) rôle(s) <strong>{{ configuration.pcru_unit_roles.join(", ") }}</strong> des oragnisations de la fiche activité pour extraire le code URM.</p>
                </div>
              </div>
            </div>

          </div>
        </section>
        <nav class="buttons text-center">
          <button type="button" class="btn btn-primary" @click="performEdit">
            <i class="icon-floppy"></i>
            Enregistrer
          </button>
        </nav>
      </form>
    </div>
  </section>
</template>
<script>
/******************************************************************************************************************/
/* ! DEVELOPPEUR
Depuis la racine OSCAR :

cd front

Pour compiler en temps réél :
node node_modules/.bin/vue-cli-service build --name AdministrationPcru --dest ./../public/js/oscar/dist/ --no-clean --formats umd,umd-min --target lib ./src/AdministrationPcru.vue --watch

Pour compiler :
node node_modules/.bin/vue-cli-service build --name AdministrationPcru --dest ./../public/js/oscar/dist/ --no-clean --formats umd,umd-min --target lib ./src/AdministrationPcru.vue

 */

import PasswordField from "./components/PasswordField";

function flashMessage() {
}

export default {

  components: {
    "password-field": PasswordField
  },

  props: {
    url: {required: true}
  },

  data() {
    return {
      formData: null,
      configuration: null,
      loading: null,
      success: false
    }
  },

  methods: {


    performEdit() {

      this.loading = "Enregistrement de la configuration";

      let formData = new FormData();
      formData.append('pcru_enabled', this.configuration.pcru_enabled);
      formData.append('host', this.configuration.pcru_host);
      formData.append('port', this.configuration.pcru_port);
      formData.append('user', this.configuration.pcru_user);
      formData.append('pass', this.configuration.pcru_pass);
      formData.append('ssh', this.configuration.pcru_ssh);
      formData.append('pcru_partner_roles', this.configuration.pcru_partner_roles);
      formData.append('pcru_unit_roles', this.configuration.pcru_unit_roles);
      formData.append('pcru_incharge_role', this.configuration.pcru_incharge_role);
      formData.append('pcru_contract_type', this.configuration.pcru_contract_type);

      this.$http.post(this.url, formData).then(ok=>{
        this.fetch();
      })
    },

    handlerSuccess(success) {
      let data = success.data;
      this.configuration = data.configuration_pcru;
    },

    fetch() {
      this.success = "";
      this.loading = "Chargement de la configuration";
      this.$http.get(this.url).then(
          ok => {
            this.handlerSuccess(ok)
          }
      ).then(foo => {
        this.success = true;
        this.loading = "Chargement terminé";
        setInterval(function(){
          this.loading = "";
        }.bind(this), 1000)
      })
    }
  },

  mounted() {
    this.fetch();
  }

}
</script>