<template>
    <div class="ui-timechooser">

        <div class="percents">
            <span @click.prevent.stop="applyDuration(fill)" v-if="fill > 0">Remplir</span>
            <span @click.prevent.stop="applyPercent(100)" :class="displayPercent == '100' ? 'selected' : ''">100%</span>
            <span @click.prevent.stop="applyPercent(75)" :class="displayPercent == '75' ? 'selected' : ''">75%</span>
            <span @click.prevent.stop="applyPercent(50)" :class="displayPercent == '50' ? 'selected' : ''">50%</span>
            <span @click.prevent.stop="applyPercent(25)" :class="displayPercent == '25' ? 'selected' : ''">25%</span>
        </div>
        <div class="hours" style="display: none">

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
<style scoped lang="scss">
    .ui-timechooser {
        background: white;
        max-width: 450px;
        margin: 0 auto;
        .percents {
            display: flex;
            span {
                flex: 1 1 auto;
                padding: 2px 4px;
                text-align: center;
                font-size: 2em;
                cursor: pointer;
                &:hover, &.selected {
                    background-color: #0b58a2;
                    color: white;
                }

            }
        }
        .hours {
            font-size: 48px;
            text-align: center;
            font-weight: 700;
            display: flex;
            justify-items: center;
            justify-content: center;
            font-size: 2em;
            .sel {
                border: thin solid #ddd;
                padding: 0;
                display: flex;
                flex-direction: column;
                i {
                    font-size: 1em;
                    cursor: pointer;
                    text-shadow: 0 0 4px rgba(0,0,0,.1);
                    &:hover {
                        background: #5c9ccc;
                        color: white;
                    }
                }
            }
            .separator {
                color: red;
            }
        }
    }
</style>
<script>
    export default {
        props: {
            duration: { default: 0 },
            baseTime: { default: 7.5 },
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
                return Math.floor((this.duration - this.displayHours)*60);
            },

            displayPercent(){
                return Math.round(100 / this.baseTime * this.duration);
            }
        },

        methods: {
            moreMinutes(){
                this.duration += 1/60*this.pas;
                this.emitUpdate();
            },

            lessMinutes(){
                this.duration -= 1/60*this.pas;
                if( this.duration < 0.0 )
                    this.duration = 0.0;

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