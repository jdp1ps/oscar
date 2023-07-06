<template>
      <div class="oscar-selector organization-selector"
           @click="handlerClick"
           @mouseleave="handlerMouseLeave"
           @mouseenter="handlerMouseEnter">
        <div class="input-group" style="position: relative;">
          <div v-if="error" class="displayed-value text-danger" style="">
            <i class="icon-attention-1" />
            {{ error }}
            <i @click="error = ''" class="icon-cancel-circled-outline button-cancel-value"></i>
          </div>
          <div v-if="displayValue" class="displayed-value" style="">
            {{ selectedLabel }}
            <i v-if="selectedValue" @click="handlerUnselect" class="icon-cancel-circled-outline button-cancel-value"></i>
          </div>
          <span class="input-group-addon">
            <i v-show="loading" class="icon-spinner animate-spin"></i>
            <i v-show="!loading" class="icon-building-filled"></i>
          </span>
          <input type="text" v-model="searchFor" @keyup="handlerKeyUp"
                 placeholder="Rechercher une organisation..."
                 class="form-control"/>
        </div>
        <div class="options" v-show="showSelector && options.length">
          <header>
            Résultat(s) : {{ options.length }} /
            <label for="hidder">
              Afficher les structures fermées
              <input type="checkbox" id="hidder" value="on" v-model="hideOff" />
            </label>
          </header>
          <div class="option" v-for="o, i in optionsFiltered"
               @mouseover="highlightedIndex = i"
               @click.prevent.stop="handlerSelectIndex(o.id)"
               :id="'item_'+i"
               :class="{
                 'active': i == highlightedIndex,
                 'selected': o.id == selectedValue,
                 'closed': o.closed
               }">
            <div class="option-title">
              <em class="cartouche code">
                {{ o.code }}
              </em>
              <span class="fullname">
                <strong>
                  {{ o.shortname }}
                </strong>
                <em>
                  {{ o.longname }}
                </em>
              </span>
              <span v-if="o.type">
                ({{o.type}})
              </span>
            </div>
            <div class="option-infos" v-if="o.city || o.country">
              <small>
                <i class="icon-location"></i>
                {{ o.city }} - {{ o.country }}
              </small>
            </div>
          </div>
        </div>
      </div>
</template>
<script>

export default {

  props: {
    value: {default: null}
  },
  emits: ['update:value'],

  components: {

  },

  data() {
    return {
      // Liste des structures
      options: [],

      lastUpdatedSearch: 0,
      delay: null,
      preloadedValue: false,
      selectedValue: null,
      selectedLabel : "",
      displayClosed: false,
      showSelector: false,
      searchFor: "",
      latency: null,
      highlightedIndex: null,
      loading: false,
      inside: false,
      displayValue: false,
      error: "",
      hideOff: false
    }
  },

  computed: {
    filteredOptions(){
      let opts = [];
      if( this.displayClosed ){
        return this.options;
      } else {
        this.options.forEach(item => {
          if( !item.closed ){
            opts.push(item);
          }
        });
        return opts;
      }
    },

    optionsFiltered(){
      if( this.hideOff ){
        return this.options;
      }
      else {
        let output = [];
        this.options.forEach(i => {
          if( !i.closed ){
            output.push(i);
          }
        });
        return output;
      }
    }
  },

  mounted() {
    // Détection d'un valeur initiale

    document.addEventListener('click', this.handlerGlobalClick, true);

    if (this.value) {
      //   this.selectedValue = this.value;
      //   this.options.push({
      //     id: this.value,
      //     label: "Waiting for data"
      //   })
      //   this.value = null;
      //   this.searchOrganization(null, 'id:' + this.selectedValue);
      // } else {
      //   this.preloadedValue = true;
    }
  },

  // todo : unmount

  methods: {
    handlerGlobalClick(evt){
      if( !this.inside ){
        this.showSelector = false;
        this.displayValue = true;
      } else {
        this.displayValue = false;
        this.showSelector = true;
      }
    },

    handlerMouseLeave(){
      this.inside = false;
    },

    handlerMouseEnter(){
      this.inside = true;
    },

    handlerClick(e){
      this.displayValue = false;
    },

    handlerUnselect(){
      this.selectedValue = null;
      this.selectedLabel = "";
      this.showSelector = false;
      this.displayValue = false;
      this.$emit('change', null );
      this.$emit('update:value', null);
      this.$emit('input', null);
    },

    handlerSelectIndex(id){
      this.options.forEach(i => {
        if( i.id == id ){
          this.selectedValue = i.id;
          this.selectedLabel = i.label;
          this.showSelector = false;
          this.displayValue = true;
        }
      });


      this.$emit('change', { id: this.selectedValue, label: this.selectedLabel} );
      this.$emit('update:value', this.selectedValue);
      this.$emit('input', this.selectedValue);
    },

    handlerSelectPrev( scroll = false ){
      if( this.highlightedIndex == 0 ){
        this.showSelector = false;
      }
      if( this.highlightedIndex > 0 ){
        if(!this.showSelector) this.showSelector = true;
        this.highlightedIndex--;
      }
      if( scroll == true ){
        let itemId = '#item_' + this.highlightedIndex;
        let item = this.$el.querySelector(itemId);
        let el = this.$el.querySelectorAll('.options')[0];
        el.scrollTop = item.offsetTop;
        console.log("SCROLL", el.scrollTop);
        console.log("ITEM", itemId, item);
      }
    },

    handlerSelectNext( scroll = false ){
      if(!this.showSelector) {
        this.showSelector = true;
      } else {
        if( this.highlightedIndex < this.options.length - 1 ){
          this.highlightedIndex++;
        }
      }
      if( scroll == true ){
        let itemId = '#item_' + this.highlightedIndex;
        let item = this.$el.querySelector(itemId);
        let el = this.$el.querySelectorAll('.options')[0];
        el.scrollTop = item.offsetTop;
        console.log("SCROLL", el.scrollTop);
        console.log("ITEM", itemId, item);
      }
    },

    updateSelectedDate(){

    },

    handlerKeyUp(e){
      switch(e.code){
        case "ArrowUp":
          this.handlerSelectPrev(true);
          break;

        case "ArrowDown":
          this.handlerSelectNext(true);
          break;

        case "ArrowLeft":
        case "ArrowRight":
          break;

        case "Enter":
          e.preventDefault();
          this.handlerSelectIndex(this.highlightedIndex);
          break;

        default:
          if( this.searchFor && this.searchFor.length > 1 ){
            this.handlerChange();
          }
      }
    },

    handlerChange(e){
      // Système de retardement Eco+
      if (this.latency != null) {
        clearTimeout(this.latency);
      }
      let delayFunction = function () {
        this.search();
        clearTimeout(this.latency);
      }.bind(this);
      this.latency = setTimeout(delayFunction, 1000);
    },

    search(){
      this.loading = true;
      this.showSelector = false;
      this.$http.get('/organization?l=m&q=' + encodeURI(this.searchFor)).then(
          ok => {
              this.options = ok.data.datas;
              if( this.options.length > 0 ){
                this.highlightedIndex = 0;
                this.showSelector = true;
              }
          },
          ko => {
            console.log(ko);
            switch(ko.status){
              case 401:
              case 403:
                this.error = "Vous avez été déconnecté (actualisé votre page pour vous reconnecter)";
                break;
              case 500:
                this.error = "La recherche a provoqué une erreur";
                break;
              default:
                this.error = "Un problème inconnu est survenu";
            }
          }
      ).then(foo => {
        this.loading = false;
      })
    },

    /**
     * Selection d'une option.
     *
     * @param selected
     */
    setSelected(selected) {
      this.value = selected;
      this.$emit('change', this.value);
      this.$emit('input', this.value);
    },

    /**
     * Déclenchement de la recherche (à la saisie).
     *
     * @param search
     * @param loading
     */
    handlerSearchOrganisation(search, loading) {
      if (search.length) {
        loading(true);
        // Système de retardement Eco+
        let delayFunction = function () {
          this.searchOrganization(loading, search, this);
          this.delay = null;
        }.bind(this);
        if (this.delay != null) {
          clearTimeout(this.delay);
        }
        this.delay = setTimeout(delayFunction, 1000);
      }
    }
  }
}
</script>