/**
 * Created by jacksay on 17-05-12.
 */
import Vue from 'vue';

var KeySelectEditor = {
    props: ['values', 'datas', 'name', 'autocomplete'],

    data(){
        return {
            newData: ""
        }
    },

    'template': `<section>
    <hr>
    <article class="card card-xs" v-for="value, key in values">
        Clef : {{ key }} = {{ value }}
        <select v-model="value" @change="handlerUpdate(key, $event)" :name="name+'[' +key +']'">
            <option value="">Ignorer</option>
            <option value="" v-for="v,l in datas" :value="l">{{ v }}</option>
        </select>
        <button class="btn btn-default" @click="handlerDelete(key)" type="button">Supprimer cette correspondance</button>
    </article>
    <pre>
        {{ autocomplete }}
    </pre>
    <select v-model="newData">
        <option value="" v-for="v,l in autocomplete" :value="l">{{ v }}</option>
    </select>
    <input type="text" v-model="newData" placeholder="Nouvelle clef..." />
    <button class="btn btn-default" type="button" @click="handlerAddKey">Ajouter une correspondance</button>
    </section>
    `,

    methods: {
        handlerUpdate(key, event){
            this.values[key] = event.target.value;
        },

        handlerDelete(key){
            console.log( this, key);
            Vue.delete(this.values, key);
        },

        handlerAddKey(){
            console.log( this.newData );
            Vue.set(this.values, this.newData, "");
            this.newData = "";
        }
    }
};

export default KeySelectEditor;
