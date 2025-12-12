<script>
export default {
  name: "TableButton",
  props: {
    id: {
      type: String
    },
    isHeader: {
      type: Boolean,
      default: true
    },
    col: {
      type: Object
    },
    item: {
      type: Object
    },
    parent: {
      type: Object
    }
  },
  computed: {
    computedId() {
      return this.id || this.getRandomId()
    },
    table() {
      return this.parent || this.$parent
    }
  },
  methods: {
    getRandomId() {
      return Math.random().toString(36).substring(2, 15)
    }
  }
}
</script>

<template>
  <span :id="computedId" v-if="!isHeader" :class="{ 'loading-row': table.loading }">
    <a
      v-if="table.showByRule(col, item)"
      v-b-tooltip.hover.topleft="{
        html: true,
        interactive: false,
        title: col.tooltip || ''
      }"
      href="#"
      class="btn btn-group btn-xs"
      :class="col.class === undefined ? ' btn-primary' : ' ' + col.class"
      :disabled="col.disabled === true"
      @click="table.buttonCallback($event, col, item)"
    >
      <i :class="'fa ' + (col.icon !== undefined ? col.icon : '')"></i>
      {{ col.title }}
    </a>
  </span>
</template>

<style lang="scss" scoped>
::v-deep .btn {
  padding: 4px 0 4px 5px;
  font-size: 0.9rem;
  line-height: 1.6;
  border-radius: 4px;
  min-width: 27px;
}

::v-deep .btn-group {
    color:#FFF;
    display:inline-block;
    margin:0 3px!important;

    &.false {
      border:none;
      box-shadow:none;
      color:#000!important;
      cursor:default;
    }
  }
</style>