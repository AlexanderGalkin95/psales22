<script>
export default {
  name: "TableCheckbox",
  props: {
    isHeader: {
      type: Boolean,
      default: true
    },
    col: {
      type: Object,
    },
    item: {
      type: Object,
    },
  },
  computed: {
    table() {
      return this.$parent
    }
  }
}
</script>

<template>
  <div v-if="isHeader">
    <div class="smart-form table-checkbox-field">
      <label class="checkbox checkbox-label">
        <input tabindex='-1' :class="{ part: table.selected_not_all_items }" v-model="table.select_all_items" type="checkbox">
        <i data-swchon-text="YES" data-swchoff-text="NO"></i>
      </label>
    </div>
  </div>
  <div v-else>
    <div class="smart-form" style="margin-top: -12px;margin-left: 5px;">
      <label class="checkbox">
        <input type="checkbox"
               v-show="table.showByRule(col,item)"
               :disabled="!table.showByRule(col,item)"
               v-model="item.is_selected" />
        <i data-swchon-text="YES" data-swchoff-text="NO"></i>
      </label>
    </div>
  </div>
</template>

<style scoped>
.table-checkbox-field {
  min-height: 40px;
  width: 10px;
  margin-top: 4px;
  margin-left: 5px;
}
.table-checkbox-field .checkbox-label {
  padding-left: 7px;
  max-width: 10px;
  margin: -20px auto auto;
  top:16px;
}
</style>