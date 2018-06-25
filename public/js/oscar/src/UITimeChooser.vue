<template>
    <div class="ui-timechooser">
        <div class="percents">
            <span @click.prevent.stop="applyPercent(100)">100%</span>
            <span @click.prevent.stop="applyPercent(75)">75%</span>
            <span @click.prevent.stop="applyPercent(50)">50%</span>
            <span @click.prevent.stop="applyPercent(25)">25%</span>
        </div>
        <div class="hours">
            <span class="hour" @mousewheel="crementHours">{{ hours }}</span>:<span class="minutes">{{ minutes }}</span>
        </div>
        <pre>
            {{ duration }}
        </pre>
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
            div {
                flex: 1 1 auto;
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
            pas: 10
        },
        data(){
            return {
                hours: 0,
                minutes: 0
            }
        },

        methods: {
            crementHours(e){
               console.log(e);
               if( e.deltaY > 0 && this.hours > 0 ){
                   this.hours -= 1;
               }
               else if( e.deltaY < 0 ){
                   this.hours += 1;
               }
            },

            applyPercent(percent){
                let t = this.baseTime * percent / 100;
                this.hours = Math.floor(t);
                this.minutes = Math.ceil((t - this.hours)*60);
                this.$emit('timeupdate', { h: this.hours, m: 1/60*this.minutes })
            }
        }
    }
</script>