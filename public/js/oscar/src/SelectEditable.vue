<template>
    <div>
        <select v-model="selectedValue" @change="onSelectChange" class="form-control">
            <option v-for="choose in chooses" :value="choose">{{ choose }}</option>
            <option value="FREE">Autre&hellip;</option>
        </select>
        <input v-show="selectedValue == 'FREE'" v-model="valueIn" @input="onInput" class="form-control" />
    </div>
</template>

<script>
    export default {
        props: {
            'value': {
                default: ''
            },
            'chooses': {
                default(){
                    return ["A", "B", "C"]
                }
            }
        },

        data(){
            return {
                valueIn: this.value,
                editMode: false
            }
        },

        computed: {
            selectedValue(){
                if (this.chooses.indexOf(this.valueIn) >= 0) {
                    return this.valueIn;
                } else {
                    return 'FREE';
                }
            }
        },

        watch: {
            value(newV, oldV){
                this.valueIn = newV;
            }
        },

        methods: {
            onInput(){
                this.$emit('input', this.valueIn, this.model);
            },
            onSelectChange(e){
                if (e.target.value == "FREE") {
                    this.valueIn = "";
                } else {
                    this.valueIn = e.target.value;
                }
                this.onInput();
            }
        }
    }
</script>