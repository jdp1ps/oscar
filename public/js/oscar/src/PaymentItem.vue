<template>
    <article class="card xs payment" :class="cssClass">
        <div class="heading">
            <strong class="amount">
                <i class="icon-attention-1" v-if="late" title="Ce versement prévisionnel est en retard" style="color: darkred"></i>
                {{ payment.amount | money }} {{ payment.currency.symbol }}
                <div v-if="payment.currency.symbol != '€'">
                    <small style="font-weight: 100">soit <strong>{{ payment.amount / payment.rate | money }} €</strong></small>
                </div>
            </strong>

            <div class="date" v-if="payment.status == 3">
                Écart de paiement
            </div>
            <div class="date" v-else>
                    <time v-if="useDate" :datetime="useDate.date" class="date">
                        <i class="icon-calendar"></i> {{ useDate | moment }}</time>
                    <span class="error" v-else>
                        Problème avec la date <code>{{ useDate }}</code>
                    </span>
                <br>
                N° <strong>{{ payment.codeTransaction }}</strong>
            </div>

            <nav v-if="manage">
                <a href="#" class="btn-delete" @click.prevent="$emit('delete', payment)" title="Supprimer ce versement">
                    <i class="icon-trash"></i>
                </a>
                <a href="#" class="btn-edit" @click.prevent="$emit('edit', payment)" title="Modifier ce versement">
                    <i class="icon-pencil"></i>
                </a>
            </nav>
        </div>
        <p class="comment">{{ payment.comment }}</p>
    </article>
</template>
<script>
    export default {
        props: ['payment', 'moment', 'manage'],

        data(){
            return {

            }
        },

        computed: {
            late(){
                if( this.payment.status == 1 ){
                    if( !this.payment.datePredicted ) return true;
                    let now = this.moment().unix();
                    let predicted = this.moment(this.payment.datePredicted.date).unix();
                    return predicted < now;
                }
                return false;

            },
            useDate(){
                // Payment réalisé
                if( this.payment.status == 2 ){
                    return this.payment.datePayment;
                }
                if( this.payment.status == 1){
                    return this.payment.datePredicted;
                }
                return null;
            },

            cssClass(){
                var css = {
                    'past': this.late
                };
                css ['status-' + this.payment.status] = true;
                return css;
            },
            isPredicted(){
                return this.payment.status == 1;
            }
        },

        methods: {

        }
    }
</script>