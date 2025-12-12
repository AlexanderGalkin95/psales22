<script>
export default {
  name: "TableHref",
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
  <a :class="{'loading-row': table.loading}" v-if="!isHeader" :href="table.createHref(col.href, item, col.hash_params)"
     :target="col.target ? col.target : col.name">
    <img v-if="col.img !== undefined && col.filename !== undefined"
         :src="col.img+item[col.filename]"
         width='20px' style="margin-right: 3px;">
    <span :style="(col.img !== undefined && col.filename !== undefined) ? 'display: inline' : ''"
          v-html="table.displayCell(col, item)"></span>
  </a>
</template>