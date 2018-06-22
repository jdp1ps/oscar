<template>
    <div class="ui-timechooser">
        <div class="percents">
            <span>100%</span>
            <span>75%</span>
            <span>50%</span>
            <span>25%</span>
        </div>
        <div class="hours">
            <div class="hour">00</div>
            <div class="minutes">00</div>
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