<script>
export default {
  name: "TableBadge",
  props: {
    isHeader: {
      type: Boolean,
      default: true
    },
    col: {
      type: Object
    },
    item: {
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
  <span v-if="!isHeader" :class="{'loading-row': table.loading}">
    <b v-if="table.showByRule(col, item)"
       :class="'badge badge-'+(col.color===undefined ? 'red' : col.color)+' bounceIn animated'">
       <span v-html="table.displayCell(col, item)"></span>
    </b>
    <span v-else><span v-html="table.displayCell(col, item)"></span> </span>
  </span>
</template>
