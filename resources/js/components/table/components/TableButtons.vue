<script>
import Tablebutton from './TableButton.vue'

export default {
  name: "TableButtons",
  components: {
    Tablebutton
  },
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
    <span>
      <Tablebutton
        v-for="(btn, cIdx) in col.items" :key="cIdx"
        :isHeader="isHeader"
        :col="btn"
        :item="item"
        :parent="table"
      />
    </span>
</template>

<style lang="scss" scoped>
  .btn {
    padding: 4px 0 4px 5px;
    font-size: 0.9rem;
    line-height: 1.6;
    border-radius: 4px;
    min-width: 27px;
  }
  .btn-group {
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