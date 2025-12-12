<script>
export default {
  name: 'TableSwitch',
  props: {
    id: {
      type: String
    },
    isHeader: {
      type: Boolean,
      default: true
    },
    item: {
      type: Object
    }
  },
  emits: ['switch-change'],
  computed: {
    isChecked() {
      return !!this.item?.is_active
    }
  },
  methods: {
    changeSwitch(event) {
      this.item.is_active = event
      this.$emit('switch-change', this.item)
    }
  },
}
</script>

<template>
  <b-form-checkbox
      v-if="!isHeader"
      :checked="isChecked"
      switch size="lg"
      @change="changeSwitch"
  />
</template>

<style scoped lang="scss">

</style>