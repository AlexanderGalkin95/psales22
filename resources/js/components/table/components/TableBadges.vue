<script>
export default {
  name: "TableBadges",
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
  <span v-if="!isHeader" :class="{'loading-row': table.loading}" style="display: inline-flex;right: -7px;position: relative;">
    <span v-for="badge in col.items">
        <b v-if="table.showByRule(badge, item) && badge.badge !== false"
           :class="'badge badge-'+(badge.color === undefined ? 'danger' : badge.color)+' bounceIn animated'"
        v-html="table.displayCell(badge, item)">
        </b>
        <span v-if="table.showByRule(badge, item) && badge.badge === false"
              v-html="table.displayCell(badge, item)">
        </span>
    </span>
  </span>
</template>