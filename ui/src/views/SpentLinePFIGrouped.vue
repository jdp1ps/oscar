<template>
    <div>
        <table class="list table table-condensed table-bordered table-condensed card" v-if="Object.keys(lines).length">
            <thead>
            <tr>
                <th>N°</th>
                <th>Ligne(s)</th>
                <th>Statut</th>
                <th>Type</th>
                <th>Description</th>
                <th style="width: 8%">Montant</th>
                <th style="width: 8%">Compte Budgetaire</th>
                <th style="width: 8%">Compte</th>
                <th style="width: 8%">Date Comptable</th>
                <th style="width: 8%">Date paiement</th>
                <th style="width: 8%">Année</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="l in lines">
                <td>{{ l.numpiece }}</td>
                <td>
                    <button @click="$emit('detailsline', l)" class="btn btn-default xs">{{ l.details.length }}
                    </button>
                </td>
                <td>
                  <span v-if="l.btart == '0250'">
                    <i class="icon-calculator" ></i>
                    Payé
                  </span>
                  <span v-else-if="l.btart == '0100'">
                    <i class="icon-bank" ></i>
                    Engagé
                  </span>
                  <span v-else>
                    <i class="icon-attention-1" ></i>
                    Inconnu
                  </span>
                  {{ l.btart }}
                </td>
                <td><small>{{ l.types ? l.types.join(',') : '' }}</small></td>
                <td><small>{{ l.text.join(', ') }}</small></td>
                <td style="text-align: right"><strong>{{ $filters.money(l.montant) }}</strong></td>
                <td>{{ l.compteBudgetaires.join(', ') }}</td>
                <td>
                    <span v-for="c in l.comptes" class="cartouche default xs" style="white-space: nowrap" @click="handlerEditCompte(c)">
                        {{ c }}
                        <i class="icon-edit"></i>
                    </span>
                </td>
                <td>{{ l.dateComptable }}</td>
                <td>{{ l.datePaiement }}</td>
                <td>{{ l.annee }}</td>
            </tr>
            </tbody>
            <tfoot>
            <tr style="font-weight: bold; font-size: 1.2em">
                <td colspan="4" style="text-align: right">Total :</td>
                <td style="text-align: right">{{ total | money }}</td>
                <td colspan="2">&nbsp;</td>
            </tr>
            </tfoot>
        </table>
        <div v-else class="alert alert-info">
            Aucune entrée
        </div>
    </div>
</template>
<script>

    // Filtre pour ne garder les comptes que en un exemplaire
    function unique(value, index, self) {
        return self.indexOf(value) === index;
    }

    export default {
        props: {
            lines: { required: true },
            total: { required: true }
        },
        methods: {
            handlerEditCompte( compteNum ){
                this.$emit('editcompte', compteNum);
            }
        }
    }
</script>