<script>
import {gbNumber} from '../../../spa_helpers/common.js'
export default {
  name: "TablePagination",
  computed: {
    table() {
      return this.$parent
    },
    total () {
      return parseInt(this.table.total)
    },
    step () {
      return parseInt(this.table.step)
    },
    pagination() {
      return this.total > this.step &&
          this.step > 0 &&
          this.step !== 9999
    },
    lastPage: function () {
      return Math.ceil(this.total / this.step) - 1;
    },
    currentPage () {
      return parseInt(this.table.page)
    },
    fromRec: function () {
      return _.min([this.currentPage * this.step + 1, this.total]);
    },
    toRec: function () {
      return _.min([this.currentPage * this.step + this.step, this.total]);
    },
  },
  methods: {
    _range: function (f, l) {
      return _.range(f, l);
    },
    loadPage(page) {
      this.table.page = page
    },
    gbNumber(num) {
      return gbNumber(num)
    }
  }
}
</script>

<template>
  <div class="row">
    <div class="col-12">&nbsp;</div>
    <template>
      <div class="col-sm-6 form-group">
        <b>
          <i v-if="step > 0">Показано с {{ gbNumber(fromRec) }} по {{ gbNumber(toRec) }} из {{
              gbNumber(total) }} записей</i>
          <i v-if="step < 0">Показано с {{ gbNumber(fromRec) }} по {{ gbNumber(total) }} из {{
              gbNumber(total) }} записей</i>
        </b>
      </div>
    </template>
    <template v-if="pagination">
      <div class="col-sm-6">
        <ul class="pagination pagination-circle pagination-alt  float-right">
          <li class="page-item previous">
            <a href="#" class="page-link" @click="loadPage(currentPage > 0 ? currentPage - 1 : 0)" aria-label="Previous"
               style="background:none;">
              <span style="padding-left: 12px;" aria-hidden="true">Предыдущая</span>
            </a>
          </li>
          <template v-if="lastPage < 10">
            <li class="page-item" :class="{ active: currentPage === i }" v-for="i in _range(0, lastPage + 1)"><a
                class="page-link" v-on:click="loadPage(i)" href="#">{{ i + 1 }}</a></li>
          </template>
          <template v-if="lastPage >= 10">
            <li class="page-item" :class="{ active: currentPage === 0 }"><a class="page-link" @click="loadPage(0)" href="#">{{ 1 }}</a></li>
            <template v-for="i in _range(1, 5)">
              <li class="page-item" :class="{ active: currentPage === i }" v-if="currentPage < 4"><a class="page-link" @click="loadPage(i)" href="#">{{ i + 1 }}</a></li>
            </template>
            <li><span class="ellipsis">...</span></li>
            <template v-for="i in _range(currentPage - 1, currentPage + 2)">
              <li :class="{ active: currentPage === i }" v-if="currentPage < lastPage - 3 && currentPage > 3"><a class="page-link" @click="loadPage(i)" href="#">{{ i + 1 }}</a></li>
            </template>
            <li class="page-item" v-if="currentPage < lastPage - 3 && currentPage > 3"><span class="ellipsis">...</span></li>
            <template v-for="i in _range(lastPage - 4, lastPage)">
              <li  class="page-item" :class="{ active: currentPage === i }" v-if="currentPage > lastPage - 4"><a class="page-link" @click="loadPage(i)" href="#">{{ i + 1 }}</a></li>
            </template>
            <li class="page-item" :class="{ active: currentPage === lastPage }"><a class="page-link" @click="loadPage(lastPage)" href="#">{{ lastPage + 1 }}</a></li>
          </template>
          <li class="page-item next">
            <a class="page-link" href="#" @click="loadPage(currentPage < lastPage ? currentPage + 1 : lastPage)" aria-label="Next" style="background:none;">
              <span aria-hidden="true">Следующая</span>
            </a>
          </li>
        </ul>
      </div>
    </template>
  </div>
</template>

<style scoped>

</style>
