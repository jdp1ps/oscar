<template>
    <article class="card xs payment" :class="cssClass">
        <div class="heading">
            <strong class="amount">
                <i class="icon-attention-1" v-if="late" title="Ce versment prévisionnel est en retard" style="color: darkred"></i>
                {{ payment.amount }} {{ payment.currency.symbol }}
            </strong>

            <div class="date">

                    <time v-if="useDate" :datetime="useDate.date" class="date">
                        <i class="icon-calendar"></i> {{ useDate | moment }}</time>
                    <span class="error" v-else="">
                        Problème avec la date <code>{{ useDate }}</code>
                    </span>
                <br>
                N° <strong>{{ payment.codeTransaction }}</strong>
            </div>
            <nav>
                <a href="#" class="btn-delete" @click.prevent="$emit('delete', payment)">
                    <i class="icon-trash"></i>
                </a>
                <a href="#" class="btn-edit" @click.prevent="$emit('edit', payment)">
                    <i class="icon-pencil"></i>
                </a>
            </nav>
        </div>
        <p class="comment">{{ payment.comment }}</p>
    </article>
</template>
<script>
    export default {
        props: ['payment', 'moment'],

        data(){
            return {

            }
        },

        computed: {
            late(){
                if( this.payment.status == 1 ){
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