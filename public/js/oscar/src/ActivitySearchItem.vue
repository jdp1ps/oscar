<template>
    <article class="card" :class="'activity-item-' +activity.statutId">
        <h3 class="card-title">
            <span class="picto" :class="'status-'+activity.statutId">
                <i class="icon"></i>
                <span class="text">{{ activity.statut }}</span>
            </span>
            <small>{{ activity.typeOscar }}  </small>
            <span>
                <strong class="text-light" v-if="activity.projectacronym">[{{ activity.projectacronym }}] / </strong>
                <strong>{{ activity.numOscar }}</strong> :
                {{ activity.label }}

                <a :href="'/activites-de-recherche/fiche-detaillee/' + activity.id" class="more">Fiche</a>

                <span v-if="activity.has_workpackages" class="cartouche blue xs">
                    <i class="icon-calendar"></i>
                    Soumis aux feuille de temps
                </span>

                <!--<a data-confirm="Supprimer définitivement cette activité ?" data-href="/activites-de-recherche/delete/10609" class="del">Supprimer</a>-->
            </span>


            <span class="montant recette" v-if="activity.amount">
                <span class="currency" :title="activity.amount.value +' ' + activity.amount.currency">
                    <span class="value">{{ activity.amount.value | money}}</span>
                    <span class="currency">{{ activity.amount.currency }}</span>
                </span>
            </span>
        </h3>amount

        <div class="card-content">
            <div class="row metas">
                <div class="col-sm-4">
                    Signature :
                    <strong v-if="activity.dateSigned">{{ activity.dateSigned | fullDate }}</strong>
                    <strong v-else>Non signé</strong>
                    <br />

                    <span v-if="activity.dateStart || activity.dateEnd">
                        Active
                        <span v-if="activity.dateStart">
                            du <time>{{ activity.dateStart | fullDate }}</time>
                        </span>

                        <span v-if="activity.dateEnd">
                            au <time>{{ activity.dateEnd | fullDate }}</time>
                        </span>
                        <br />
                    </span>

                    Créé le <time>{{ activity.dateCreated | fullDate }}</time><br>
                    Dernière mise à jour : <time>{{ activity.dateUpdated | fullDate }}</time>
                </div>

                <div class="col-sm-4">
                    <div v-for="persons, role in activity.persons_primary">
                        <i :class="'icon-' + role | slugify"></i>{{ role }} :
                        <a v-for="person in persons" :href="'/person/show/' +person.id" class="person" :title="person.affectation">
                            <i :class="'icon-' + (person.spot == 'activity' ? 'cube' : 'cubes')"></i>
                            {{ person.displayName }}
                        </a>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div v-for="organizations, role in activity.organizations_primary">
                        <i :class="'icon-' + role | slugify"></i>{{ role }} :
                        <a v-for="organization in organizations" :href="'/organization/show/' +organization.id" class="organization">
                            <i :class="'icon-' + (organization.spot == 'activity' ? 'cube' : 'cubes')"></i>
                            {{ organization.displayName }}
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-highlight"  v-if="activity.persons">
                <i class="icon-user grey"></i>Membres :
                <span v-for="persons, role in activity.persons">
                    <span v-for="person in persons" class="person cartouche xs" :title="person.affectation">
                        <i :class="'icon-' + (person.spot == 'activity' ? 'cube' : 'cubes')"></i>
                        {{ person.displayName }}
                        <span class="addon">{{ role }}</span>
                    </span>
                </span>
            </p>

            <p class="text-highlight" v-if="activity.organizations"><i class="icon-building-filled grey"></i>Partenaires :
                <span v-for="organizations, role in activity.organizations">
                    <span v-for="organization in organizations"   class="organization cartouche xs">
                        <i :class="'icon-' + (organization.spot == 'activity' ? 'cube' : 'cubes')"></i>
                        {{ organization.displayName }}
                        <span class="addon">{{ role }}</span>
                    </span>
                </span>
            </p>

            <div class="text-highlight" v-if="activity.project">
                Project <span><a :href="'/project/show/' + activity.project.id" class="project">
                <i class="icon-cubes"></i>{{ activity.project.displayName }} </a></span>
            </div>
            <div v-else>
                <i class="icon-attention-1"></i> Cette activité n'a pas de projet
            </div>
        </div>
    </article>
</template>
<script>
    export default {
        props: {
            activity: { required: true }
        }
    }
</script>