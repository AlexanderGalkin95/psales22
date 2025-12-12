<script>
export default {
  name: "TableCallback",
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
  <a v-if="!isHeader" v-show="table.showByRule(col, item)" href="#"
     @click="table.buttonCallback($event, col, item)"
     :class="table.merge((col.class === undefined ? '' : col.class), {'loading-row': table.loading})">
    <span v-html="table.displayCell(col, item)"></span>
  </a>
</template>