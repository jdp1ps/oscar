<template>
  <article class="card" :class="'activity-item-' +activity.statutId" @click.shift="handlerDebug(activity)">
    <h3 class="card-title">
            <span class="picto" :class="'status-'+activity.statutId">
                <i class="icon"></i>
                <span class="text">{{ activity.statut }}</span>
            </span>
      <small>{{ activity.typeOscar }} </small>

      <span>
        <a :href="'/activites-de-recherche/fiche-detaillee/' + activity.id">
          <strong class="text-light" v-if="activity.projectacronym">[{{ activity.projectacronym }}] / </strong>
          <strong>{{ activity.numOscar }}</strong> :
          {{ activity.label }}
        </a>

        <a :href="'/activites-de-recherche/fiche-detaillee/' + activity.id" class="more">Fiche</a>
      </span>


      <span class="montant recette" v-if="activity.amount">
          <span class="currency" :title="activity.amount.value +' ' + activity.amount.currency">
              <span class="value">{{ activity.amount.value | money}}</span>
              <span class="currency">{{ activity.amount.currency }}</span>
          </span>
      </span>
    </h3>

    <div class="card-content">

      <div class="row metas">
        <div class="col-md-12">
          <span class="number">
            <small class="key number-label">N°OSCAR</small>
            <strong class="value number-value"> {{ activity.numOscar }} </strong>
          </span>
          <span class="number" v-if="activity.PFI">
            <small class="key number-label">PFI</small>
            <strong class="value number-value"> {{ activity.PFI }} </strong>
          </span>
          <span class="number" v-for="value,key in activity.numbers">
            <small class="key number-label">{{ key }}</small>
            <strong class="value number-value"> {{ value }} </strong>
          </span>
          <span v-if="activity.has_workpackages" class="cartouche secondary1">
              <i class="icon-calendar"></i>
              Soumis aux feuille de temps
          </span>
        </div>
        <div class="col-sm-12">
          Signature :
          <strong v-if="activity.dateSigned">{{ activity.dateSigned | fullDate }}</strong>
          <strong v-else>Non signé</strong>
           ~
          <span v-if="activity.dateStart || activity.dateEnd">
              Active
              <span v-if="activity.dateStart">
                  du <time>{{ activity.dateStart | fullDate }}</time>
              </span>

              <span v-if="activity.dateEnd">
                  au <time>{{ activity.dateEnd | fullDate }}</time>
              </span>
          </span>
          <br>
          Créé le
          <time>{{ activity.dateCreated | fullDate }}</time>
          Dernière mise à jour :
          <time>{{ activity.dateUpdated | fullDate }}</time>
        </div>
      </div>
      <div class="row metas" v-if="!compact">
        <div class="col-sm-6">
          <div v-for="persons, role in activity.persons_primary">
            <i :class="'icon-' + role | slugify"></i>{{ role }} :
            <a v-for="person in persons" :href="person.url"
               @click="handlerClickPerson($event, person)"
               :class="{'unclickable': !person.url}"
               class="person" :title="person.affectation">
              <i :class="'icon-' + (person.spot == 'activity' ? 'cube' : 'cubes')"></i>
              {{ person.displayName }}
            </a>
          </div>
        </div>

        <div class="col-sm-6">
          <div v-for="organizations, role in activity.organizations_primary">
            <i :class="'icon-' + role | slugify"></i>{{ role }} :
            <a v-for="organization in organizations" :href="organization.url"
               class="organization" @click="handlerClickOrganization($event, organization)"
               :class="{'unclickable': !organization.url}">
              <i :class="'icon-' + (organization.spot == 'activity' ? 'cube' : 'cubes')"></i>
              {{ organization.displayName }}
            </a>
          </div>
        </div>
      </div>

      <p class="text-highlight" v-if="activity.persons && !compact">
        <i class="icon-user grey"></i>Membres :
        <span v-for="persons, role in activity.persons">
            <a v-for="person in persons" class="person cartouche xs" :title="person.affectation"
               :href="person.url"
               @click="handlerClickPerson($event, person)"
               :class="{'unclickable': !person.url}"
            >
                <i :class="'icon-' + (person.spot == 'activity' ? 'cube' : 'cubes')"></i>
                {{ person.displayName }}
                <span class="addon">{{ role }}</span>
            </a>
        </span>
      </p>

      <p class="text-highlight" v-if="activity.organizations && !compact">
        <i class="icon-building-filled grey"></i>Partenaires :
        <span v-for="organizations, role in activity.organizations">
            <a v-for="organization in organizations" class="organization cartouche xs"
               @click="handlerClickOrganization($event, organization)"
               :href="organization.url"
               :class="{'unclickable': !organization.url}">
                <i :class="'icon-' + (organization.spot == 'activity' ? 'cube' : 'cubes')"></i>
                {{ organization.displayName }}
                <span class="addon">{{ role }}</span>
            </a>
        </span>
      </p>

      <div class="content-expand" v-if="!compact">
        <div class="text-highlight" v-if="activity.project">
          Project <span><a :href="'/project/show/' + activity.project.id" class="project">
                  <i class="icon-cubes"></i>{{ activity.project.displayName }} </a></span>
        </div>
        <div v-else>
          <i class="icon-attention-1"></i> Cette activité n'a pas de projet
        </div>
      </div>
    </div>
  </article>
</template>
<script>
export default {
  props: {
    activity: {required: true},
    compact: {type: Boolean, default: false},
    person_url: {type: Boolean, default: true},
    organization_url: {type: Boolean, default: false}
  },
  methods: {
    handlerDebug(dt){
      console.log(dt);
      this.$emit('debug', dt);
    },
    handlerClickPerson(evt, person){
      if( !person.url ){
        evt.preventDefault();
      }
    },
    handlerClickOrganization(evt, organization){
      if( !organization.url ){
        evt.preventDefault();
      }
    }
  }
}
</script>