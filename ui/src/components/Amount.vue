<template>
  <input
      type="text"
      v-model="displayValue"
      @focus="startEditing"
      @blur="finishEditing"
      @input="updateRawValue"
      class="form-control text-right"
  />
</template>

<script>
export default {
  props: {
    modelValue: Number
  },
  data() {
    return {
      isEditing: false,
      rawValue: '',
      formattedValue: ''
    }
  },
  computed: {
    displayValue: {
      get() {
        return this.isEditing ? this.rawValue : this.formattedValue
      },
      set(value) {
        this.rawValue = value
      }
    }
  },
  watch: {
    modelValue: {
      immediate: true,
      handler(newValue) {
        if (!this.isEditing) {
          this.formattedValue = this.formatCurrency(newValue)
          this.rawValue = (newValue ?? '').toString().replace('.', ',')
        }
      }
    }
  },
  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
      }).format(value)
    },
    startEditing() {
      this.isEditing = true
      this.rawValue = (this.modelValue ?? '').toString().replace('.', ',')
    },
    finishEditing() {
      this.isEditing = false
      let numericValue = parseFloat(this.rawValue.replace(',', '.')) || 0
      this.$emit('update:modelValue', numericValue)
    },
    updateRawValue(event) {
      this.rawValue = event.target.value
    }
  }
}
</script>