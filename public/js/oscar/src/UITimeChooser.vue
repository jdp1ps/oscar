<template>
    <div class="ui-timechooser">
        <div class="percents">
            <span @click.prevent.stop="applyPercent(100)">100%</span>
            <span @click.prevent.stop="applyPercent(75)">75%</span>
            <span @click.prevent.stop="applyPercent(50)">50%</span>
            <span @click.prevent.stop="applyPercent(25)">25%</span>
        </div>
        <div class="hours">

            <span class="hour sel" @mousewheel="crementHours">
                <span @click.prevent.stop="moreHours()"><i class="icon-angle-up"></i></span>
                {{ hours }}
                <span @click.prevent.stop="lessHours()"><i class="icon-angle-down"></i></span>
            </span>

            <span class="separator">:</span>

            <span class="minutes sel">
                <span @click.prevent.stop="moreMinutes()"><i class="icon-angle-up"></i></span>
                {{ minutes }}
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
                &:hover {
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
            pas: { default: 10 }
        },
        data(){
            return {
                hours: 0.0,
                minutes: 0.0
            }
        },

        methods: {
            moreMinutes(){
                console.log(this.minutes, this.pas);
                this.minutes += this.pas;
                if( this.minutes > 60 ){
                    this.minutes -= 70;
                    this.moreHours();
                } else {this.emitUpdate();}
            },

            lessMinutes(){
                this.minutes -= this.pas;
                if( this.minutes < 0 ){
                    this.minutes = 60 + this.minutes;
                    this.lessHours();
                } else {
                    this.emitUpdate();
                }

            },

            moreHours(){
                this.hours++;
                this.emitUpdate();
            },

            lessHours(){
                if( this.hours > 0 ){
                    this.hours--;
                    this.emitUpdate();
                }
            },

            crementHours(e){
               console.log(e);
               if( e.deltaY > 0 && this.hours > 0 ){
                   this.hours -= 1;
               }
               else if( e.deltaY < 0 ){
                   this.hours += 1;
               }
            },

            roundMinutes(minutes){
                return Math.round(minutes/this.pas) * this.pas;
            },

            applyPercent(percent){
                console.log(this.baseTime);
                let t = this.baseTime * percent / 100;
                this.hours = Math.floor(t);
                this.minutes = Math.round((t - this.hours)*60);
                this.emitUpdate();
            },

            emitUpdate(){
                this.$emit('timeupdate', { h: this.hours, m: 1/60*this.minutes })
            }
        }
    }
</script>