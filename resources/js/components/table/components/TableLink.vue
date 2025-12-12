<script>
export default {
  name: "TableLink",
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
  <router-link :class="{'loading-row': table.loading}" v-if="!isHeader" :to="table.createHref(col.link, item, col.hash_params)"
     :target="col.target ? col.target : col.name">
    <img v-if="col.img !== undefined && col.filename !== undefined"
         :src="col.img+item[col.filename]"
         width='20px' style="margin-right: 3px;">
    <span :style="(col.img !== undefined && col.filename !== undefined) ? 'display: inline' : ''"
          v-html="table.displayCell(col, item)"></span>
  </router-link>
</template>