<template>
    <div class="ui-timechooser">
        <div class="percents">
            <span @click.prevent.stop="applyDuration(fill)" v-if="fill > 0">Remplir</span>
            <span @click.prevent.stop="applyPercent(100)" :class="displayPercent == '100' ? 'selected' : ''">100%</span>
            <span @click.prevent.stop="applyPercent(75)" :class="displayPercent == '75' ? 'selected' : ''">75%</span>
            <span @click.prevent.stop="applyPercent(50)" :class="displayPercent == '50' ? 'selected' : ''">50%</span>
            <span @click.prevent.stop="applyPercent(25)" :class="displayPercent == '25' ? 'selected' : ''">25%</span>
        </div>

        <div class="hours" style="" v-if="declarationInHours">
            <span class="hour sel">
                <span @click.prevent.stop="moreHours()"><i class="icon-angle-up"></i></span>
                {{ displayHours }}
                <span @click.prevent.stop="lessHours()"><i class="icon-angle-down"></i></span>
            </span>

            <span class="separator">:</span>

            <span class="minutes sel">
                <span @click.prevent.stop="moreMinutes()"><i class="icon-angle-up"></i></span>
                {{ displayMinutes }}
                <span @click.prevent.stop="lessMinutes()"><i class="icon-angle-down"></i></span>
            </span>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            duration: { default: 0 },
            baseTime: { default: 7.5 },
            declarationInHours: { required: true },
            // PAS en minutes
            pas: { default: 10 },
            fill: { default: 0 },
        },
        data(){
            return {
                hours: 0.0,
                minutes: 0.0
            }
        },

        computed: {
            displayHours(){
                return Math.floor(this.duration);
            },

            displayMinutes(){
                return Math.round(((this.duration - this.displayHours)*60));
            },

            displayPercent(){
                return Math.round(100 / this.baseTime * this.duration);
            }
        },

        methods: {
            /**
             * Uniformisation de la valeur.
             *
             * @param durationMinutes
             * @returns {number}
             */
            standardizeDuration(durationMinutes){
                let standardized = (Math.round(durationMinutes/this.pas) * this.pas)/60;
                return standardized;
            },

            moreMinutes(){
                this.duration = this.standardizeDuration(this.duration*60 + this.pas);
                this.emitUpdate();
            },

            lessMinutes(){
                this.duration = this.standardizeDuration(this.duration*60 - this.pas);
                this.emitUpdate();
            },

            moreHours(){
                this.duration += 1;
                this.emitUpdate();
            },

            lessHours(){
                this.duration -= 1;
                if( this.duration < 0.0 )
                    this.duration = 0.0;
                this.emitUpdate();
            },

            applyDuration(fill){
                this.duration = fill;
                this.emitUpdate();
            },

            roundMinutes(minutes){
                return Math.round(minutes/this.pas) * this.pas;
            },

            applyPercent(percent){
                this.duration = this.baseTime * percent / 100;
                this.emitUpdate();
            },

            emitUpdate(){
                let hours = Math.floor(this.duration);
                let minutes = this.duration - hours;
                console.log(this.duration, hours, minutes);
                this.$emit('timeupdate', { h: hours, m: minutes })
            }
        }
    }
</script>