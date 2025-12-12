<script>
export default {
  name: "TableSettings",
  computed: {
    table() {
      return this.$parent
    },
    globalSearch() {
      return this.table.settings && this.table.searchable !== false && this.table.settings.global_search !== false
    }
  }
}
</script>

<template>
  <div class="table-settings-wrapper">
    <template v-if="globalSearch">
      <div class="form-group margin-right-5" style="flex: 1 1 auto;">
        <div class="input-group">
            <div class="input-group-prepend">
                <label class="input-group-text"
                ><i class="flaticon-search" aria-hidden="true"></i
                ></label>
            </div>
          <input id="search_string" type="search" v-model="table.searchQuery" class="form-control" style="max-width: 355px" />
        </div>
      </div>
    </template>
    <template v-if="table.with_export">
      <div class="hidden-sm hidden-xs" style="flex: 1 1 auto;">
        <div>
          <label class="hidden-lg hidden-md control-label">&nbsp;</label>
          <a class="btn btn-info" style="min-width: 116px;" @click="table.exportData"><i class="fa fa-upload" aria-hidden="true"></i> Export</a>
        </div>
      </div>
    </template>
    <template>
      <div class="form-group margin-right-5" style="flex: 0 0 auto;">
        <a class="btn btn-primary clear-filters-text btn-small" style="min-width: 105px; margin-left: 10px;" @click="table.clearFilters">
          <i class="flaticon-refresh"></i> Очистить</a>
      </div>
    </template>
    <template>
      <div class="form-group margin-right-5" style="flex: 1 1 auto;">
        <div class="form-inline float-right">
          <label for="pagesize" class="control-label hidden-xs text-right" style="margin-right: 10px;">Показать записи</label>
          <select v-model.number="table.step" class="form-control input-sm" style="max-width: 115px" name="pagesize" id="pagesize">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
            <option :value="9999">Все</option>
          </select>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.table-settings-wrapper {
  display: flex;
  flex-flow: wrap;
}
</style>
