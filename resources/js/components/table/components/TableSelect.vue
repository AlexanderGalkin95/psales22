<script>
import "@artamas/vue-select/src/scss/vue-select.scss";
export default {
  name: "TableSelect",
  props: {
    isHeader: {
      type: Boolean,
      default: true
    },
    col: {
      type: Object
    },
    cclass: {
       type: String,
       default: ''
    }
  },
  data(){
    return{}
  },

  computed: {
      table() {
          return this.$parent
      },
      options () {
          return this.table.dicts[this.col.search_dict]
      }
  }
}
</script>

<template>
  <v-select
      v-if="isHeader"
      v-model="table.local_filters[col.name]"
      :options="options"
      :class="cclass"
      :disabled="col.filter_disable"
      :selectable="(o) => o.disabled !== true"
      :reduce="(o) => o.value"
  >
    <template slot="no-options">Ничего не найдено</template>
  </v-select>
</template>
