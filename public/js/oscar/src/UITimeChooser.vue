<template>
    <div class="ui-timechooser">
        <div class="percents">
            <span @click.prevent.stop="applyPercent(100)">100%</span>
            <span @click.prevent.stop="applyPercent(75)">75%</span>
            <span @click.prevent.stop="applyPercent(50)">50%</span>
            <span @click.prevent.stop="applyPercent(25)">25%</span>
        </div>
        <div class="hours">
            <div class="hour">{{ hours }}</div>
            <div class="minutes">{{ minutes }}</div>
        </div>
    </div>
</template>
<style scoped lang="scss">
    .ui-timechooser {
        background: red;
        .percents {
            display: flex;
            span {
                flex: 1 1 auto;
                padding: 2px 4px;
                &:hover {
                    background-color: #0b58a2;
                    color: white;
                }
            }
        }
        .hours {
            display: flex;
            div {
                flex: 1 1 auto;
                text-align: center;
                font-size: 32px;
            }
        }
    }
</style>
<script>
    export default {
        props: {
            baseTime: { default: 8.5 }
        },
        data(){
            return {
                hours: 0,
                minutes: 0
            }
        },

        methods: {
            applyPercent(percent){
                let t = this.baseTime * percent / 100;
                this.hours = Math.floor(t);
                this.minutes = Math.ceil((t - this.hours)*60);
                this.$emit('timeupdate', { h: this.hours, m: 1/60*this.minutes })
            }
        }
    }
</script>