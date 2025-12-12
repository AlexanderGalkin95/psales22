<script>
export default {
  name: "TableAutocomplete",
  props: {
    isHeader: {
      type: Boolean,
      default: true
    },
    col: {
      type: Object
    }
  },
  computed: {
    table() {
      return this.$parent
    }
  }
}
</script>

<template>
  <v-select v-if="isHeader" class="input-sm"
            style="width: 100%;background:white"
            :disabled="col.filter_disable"
            v-model="table.local_filters[col.name]" :options="table.dicts[col.search_dict]"></v-select>
</template>

<style scoped>

</style>