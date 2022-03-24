<template>
    <div class="form-group vue-ui-textarea" style="position: relative" :class="{ 'has-error': hasError, 'is-valid': isValid }">
        <label :for="id" v-if="label">
            {{ label }}
            <strong v-if="required"><sup>*</sup></strong>
        </label>
        {{ errors }}
        <div class="vue-ui-content" style="position: relative">
            <div class="error-messages" style="position: absolute; right: 0; top: 0;" v-show="hasError">
                <ul>
                    <li v-for="e in errors">{{ e }}</li>
                </ul>
            </div>
            <textarea :id="id" class="form-control" :rows="raw" :name="name" v-model="value"></textarea>
            <small style="display: block; text-align: right" v-if="displayLeft">{{ left }}</small>
        </div>
    </div>
</template>
<script>

    export default {
        props: {
            class: { default: 'form-control' },
            required: { default: false },
            value: { default: "" },
            label: { default: "" },
            name: { require: true },
            raw: { default: 3 },
            maxlength: { default: 0 },
            id: { require: true },
            placeholder: { default: "" },
            tooLongMessage: { default: "Le contenu est trop long."},
            requiredMessage: { default: "Vous devez remplir ce champ."}
        },

        computed: {
            displayLeft(){
                return this.maxlength != null;
            },
            left(){
                if( this.value.length < this.maxlength )
                    return (this.maxlength - this.value.length) + " caractère(s) restant.";
                else if (this.value.length == this.maxlength )
                    return "Pas plus"
                else {
                    return "Trop long"
                }
            },
            /** Le contenu saisi est trop long */
            tooLong(){
                return this.maxlength > 0 && this.value.length > this.maxlength;
            },
            errors(){
                let errors = [];
                if( this.tooLong ){
                    errors.push(this.tooLongMessage)
                }
                if( this.required == true && this.value.length == 0 ){
                    errors.push(this.requiredMessage)
                }
                return errors;
            },
            isValid(){
                return this.errors.length == 0;
            },
            hasError(){
                return this.errors.length > 0;
            }
        },

        data: {
            error: ""
        },

        watch: {
            value(val){
                this.$emit('change', val);
                this.$emit('input', val);
                if( val.length > this.maxlength ){
                    console.log("Trop long");
                    this.error = this.maxlength + " caractère(s) maximum";
                } else {
                    this.error = "";
                }
            }
        },

        methods: {

        }
    }
</script>