<script>
import _ from 'lodash'
import moment from 'moment'
import VSpreadLoading from "./VSpreadLoading"
import AudioPlayer from "../vue-audio/components/player"
import ScrollLeft from "../../view/layout/extras/ScrollLeft";
import ScrollRight from "../../view/layout/extras/ScrollRight";

export default {
  name: "VSpreadsheet",
  components: {ScrollRight, ScrollLeft, VSpreadLoading, AudioPlayer },
  data() {
    return {
      id: 'v-spreadsheet',
      mode: 'read', // edit | read
      columns: [],
      resource: [],
      dataSrc: '',
      dataSrcName: 'ratings',
      filters: {},
      sortCol: '',
      sortDir: '',
      searchQuery: '',
      headers: [],
      total: 0,
      step: 10,
      page: 1,
      route: '',
      showAlpha: false,
      loading: false,
      showScrollLeft: false,
      showScrollRight: false,
    }
  },
  computed: {
    q_slow: function () {
      let q = ''
      let q_arr = []
      _.forEach(this.filters, (v, k) => {
        if (v !== null && v !== '' && v !== undefined) {
          let c_obj_k = _.find(this.columns, column => {
            return column.name === k
          });
          if (c_obj_k === undefined) return
          if (c_obj_k.search_dict === undefined && c_obj_k.format !== 'date' && c_obj_k.format !== 'datetimefull') {
            q_arr.push(k.replace('.', '_') + ' eq ' + '\'' + v + '\'')
          } else {
            if (c_obj_k && (c_obj_k.format === 'date' || c_obj_k.format === 'datetimefull')) {
              let dt = moment(v, 'DD.MM.YYYY')
              v = (dt === "Invalid date") ? v : dt.format('YYYY-MM-DD')
            }

            q_arr.push(k.replace('.', '_') + ' = ' + v)
          }
        }
      });
      if (this.searchQuery !== null && this.searchQuery !== '') {
        q += '&search=' + encodeURIComponent(this.searchQuery)
      }
      if (q_arr.length > 0) {
        q += '&$filter=' + encodeURIComponent(q_arr.join(' $and$ '))
      }
      return q
    },
    q_fast: function () {
      let q = 'limit=' + this.step + '&offset=' + (this.page - 1) * this.step
      if (!_.isEmpty(this.sortCol)) {
        q += '&$orderBy=' + encodeURIComponent(this.sortCol.replace('.', '_') + ' ' + this.sortDir)
      }
      return '?' + q
    },
    filters_change: function () {
      let q = '';
      _.forEach(this.filters, function (v, k) {
        if (v !== null && v !== '') {
          q += '&' + k + '=' + encodeURIComponent(v);
        }
      });
      return q;
    },
    refresh_default: function () {
      if (!_.isEmpty(this.settings) && this.settings.refresh_default) {
        return this.settings.refresh_default;
      } else {
        return false;
      }
    },
    alphaCriteriaBlock() {
      return _.filter(this.headers['criteria_block'], item => item.type !== 'hidden')
    },
    alphaCrmBlock() {
      return _.filter(this.headers['crm_block'], item => item.type !== 'hidden')
    },
    criteriaBlockLength () {
      return this.headers ? _.size(_.filter(this.headers['criteria_block'], item => item.type !== 'hidden')) : 1
    },
    staticBlockLength () {
      return this.headers ? _.size(_.filter(this.headers['static_block'], item => item.type !== 'hidden')) - 3 : 1
    },
    objectionBlockLength () {
      return this.headers ? _.size(this.headers['objection_block']) : 1
    },
    crmBlockLength () {
      return this.headers ? _.size(_.filter(this.headers['crm_block'], item => item.type !== 'hidden')) : 1
    },
    endingBlockLength () {
      return this.headers ? _.size(_.filter(this.headers['ending_block'], item => item.type !== 'hidden')) : 1
    },
  },
  watch: {
    filters_change: _.debounce(function (newVal, oldVal) {
      if (newVal !== oldVal) {
        localStorage.setItem(this.storageIndex('filters'), JSON.stringify(this.filters));
        this.page = 1;
      }
    }, 10),
    step: function (newVal, oldVal) {
      this.step = newVal
      localStorage.setItem(this.storageIndex('step'), newVal)
    },
    q_slow: _.debounce(function (newVal, oldVal) {
      if (newVal !== oldVal) {
        this.refresh();
      }
    }, 10),
    q_fast: _.debounce(function (newVal, oldVal) {
      if (newVal !== oldVal) {
        this.refresh();
      }
    }, 10),
    route: _.debounce(function(newVal) {
      this.refreshImmediately()
    }, 500),
  },
  created() {
    this.step = localStorage.getItem(this.storageIndex('step')) || this.step
    this.sortCol = localStorage.getItem(this.storageIndex('sortCol')) || this.sortCol
    this.sortDir = localStorage.getItem(this.storageIndex('sortDir')) || this.sortDir
    let savedFiltersJson = localStorage.getItem(this.storageIndex('filters'));
    this.filters = JSON.parse(savedFiltersJson) || {}
  },
  mounted() {
    $(document).on("click", ".spread-header-alpha .dropdown-menu", function(e) {
      e.stopPropagation()
    })
    let $this = this
    let element = document.querySelector('#v-spreadsheet-table')
    element.addEventListener('scroll', function (event) {
      setTimeout(() => {
        $this.handleScroll(event.target)
      }, 500)
    })
    window.addEventListener('resize', function (event) {
      setTimeout(() => {
        $this.handleScroll(element)
      }, 500)
    })
    this.dataSrc = `/api/projects/${this.$route.params.projectId}/call_ratings`
    this.refresh()
  },
  methods: {
    onClick(item, selCell, rIndex) {
      _.forEach(this.resource, item => {
        _.each(item.data, (cell, key) => {
          cell.selected = false
        })
      })

      selCell.selected = true
      this.rowSelected(rIndex + 1)
    },
    rowSelected(rIndex) {
      _.forEach(this.resource, (item, index) => {
        item.selected = (index + 1) === rIndex
      })
    },
    storageIndex: function (name) {
      return 'PincherSpreadsheet_' + this.id + '_' + name;
    },
    switchSort: function (event, header) {
      this.sortCol = header.name;
      localStorage.setItem(this.storageIndex('sortDir'), this.sortDir);
      localStorage.setItem(this.storageIndex('sortCol'), this.sortCol);
    },
    refresh: function () {
      this.route = this.dataSrc + this.q_fast + this.q_slow
    },
    refreshDefault: function () {
      this.filters = {}

      let old_route = this.route
      this.page = 1
      this.sortCol = ''
      this.sortDir = ''
      this.loading = true
      this.searchQuery = null
      this.refresh()
      if (old_route === this.route) {
        this.refreshImmediately()
      }
    },
    refreshImmediately: function() {
      this.loading = true
      let items = []
      if (_.isEmpty(this.dataSrc)) {
        this.loading = false
        return
      }
      this.route = this.dataSrc + this.q_fast + this.q_slow

      this.$http.get(this.route)
          .then(response => {
            items = response.data[this.dataSrcName]
            this.total = response.data.total
            this.headers = response.data.headers
            this.resource = _.map(_.cloneDeep(items), item => {
              _.each(item, (cell, key) => {
                cell.selected = false
                cell.tooltip = false
                if (cell.type === 'buttons') {
                  _.each(cell.buttons, (button, bKey) => {
                    if (button.type === 'audio') {
                      button.popover = false
                    }
                  })
                }
              })
              return {
                selected: false,
                data: item
              }
            })
            if (this.resource) {
              setTimeout(() => {
                let element = document.querySelector('#v-spreadsheet-table')
                this.handleScroll(element)
              }, 500)
            }

            this.loading = false
          });
    },
    clearFilters: function() {
      this.refreshDefault();
    },
    numberToLetter (input){
      let s = '', t;

      while (input > 0) {
        t = (input - 1) % 26;
        s = String.fromCharCode(65 + t) + s;
        input = (input - t)/26 | 0;
      }
      return s || undefined;
    },
    showTooltip(event, cell) {
      if (event.target.offsetWidth > 200) {
        cell.tooltip = true
      }
    },
    showPopover(event, cell, button) {
      if (button.popover === true) {
        button.popover = false
        return
      }
      _.forEach(this.resource, item => {
        _.each(item.data, (cell, key) => {
          if (cell.type === 'buttons') {
            _.each(cell.buttons, (button, bKey) => {
              if (button.type === 'audio') {
                button.popover = false
                button.icon = 'fa-play'
              }
            })
          }
        })
      })
      if (button.recordId) {
        this.$http.get(`/api/projects/${this.$route.params.projectId}/record/${button.recordId}?no_download=1`)
            .then(response => {
              button.text = response.data.link
              setTimeout(() => {
                button.popover = true
                button.icon = 'fa-stop'
              }, 500)
            })
      } else {
        setTimeout(() => {
          button.popover = true
          button.icon = 'fa-stop'
        }, 500)
      }
    },
    hideTooltip(event, cell) {
      cell.tooltip = false
    },
    handleScroll(element) {
      this.showScrollRight = !(element && element.scrollWidth === (element.scrollLeft + element.offsetWidth))
      this.showScrollLeft = !(element && element.scrollLeft === 0)
    },
    scrollTableToRight(event) {
      let table = document.querySelector('#v-spreadsheet-table')
      table.scrollTo({
        top: 0,
        left: table.scrollLeft + 100,
        behavior: 'smooth'
      });
    },
    scrollTableToLeft(event) {
      let table = document.querySelector('#v-spreadsheet-table')
      table.scrollTo({
        top: 0,
        left: table.scrollLeft - 100,
        behavior: 'smooth'
      });
    },
  },
}
</script>

<template>
  <div id="v-spreadsheet">
    <b-row class="justify-content-center">
      <div class="spread-edit-panel" style="flex: 1 1 auto"></div>

      <div class="form-group margin-right-5" style="flex: 0 0 auto;">
        <a class="btn btn-primary clear-filters-text btn-small"
           style="min-width: 105px; margin-left: 10px;"
           @click="clearFilters">
          <i class="flaticon-refresh"></i> Очистить</a>
      </div>

      <div style="flex: 1 1 auto">
        <div class="form-inline float-right">
          <label class="control-label hidden-xs text-right" style="margin-right: 10px;">
            Показать записи
          </label>
          <select id="pagesize"
                  v-model.number="step"
                  class="form-control input-sm"
                  style="max-width: 115px"
                  name="pagesize">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
            <option :value="9999">Все</option>
          </select>
        </div>
      </div>
    </b-row>
    <b-row id="v-spreadsheet-table" class="overflow-auto">
      <v-spread-loading v-show="loading"></v-spread-loading>
      <scroll-left v-show="showScrollLeft" @click="scrollTableToLeft" />
      <scroll-right v-show="showScrollRight" @click="scrollTableToRight" />
      <table class="table-bordered table-responsive-md" style="width: 100%">
        <thead>
        <tr v-show="showAlpha">
          <th style="min-width: 50px">
            <span>&nbsp;</span>
          </th>
          <th v-for="(header, cIndex) in headers['static_block']"
              v-if="header.type !== 'hidden'"
              :key="cIndex"
              style="min-width: 50px;" class="spread-header-alpha">
            <span>{{ numberToLetter(cIndex) }}</span>
            <div class="dropdown">
              <a href="javascript:void(0)"
                 class="changeType dropdown-toggle"
                 data-toggle="dropdown"
                 aria-haspopup="true"
                 aria-expanded="false">
              </a>
              <div class="dropdown-menu">
                <div class="row no-gutters form-group">
                  <span class="font-weight-normal">Сортировка:</span>
                  <select class="outline-none"
                          @change="switchSort($event, header)">
                    <option value="">-- Выбрать --</option>
                    <option value="asc">По возрастанию</option>
                    <option value="desc">По убыванию</option>
                  </select>
                </div>
                <div class="row no-gutters form-group">
                  <span class="font-weight-normal">Поиск по значению:</span>
                  <input
                         type="text"
                         class="outline-none"
                         placeholder="Поиск">
                </div>
              </div>
            </div>
          </th>
          <th v-for="(header, cIndex) in headers['criteria_block']"
              v-if="header.type !== 'hidden'"
              :key="'criteria_' + cIndex"
              style="min-width: 50px;" class="spread-header-alpha">
            <span>{{ numberToLetter(cIndex) }}</span>
            <div class="dropdown">
              <a href="javascript:void(0)"
                 class="changeType dropdown-toggle"
                 data-toggle="dropdown"
                 aria-haspopup="true"
                 aria-expanded="false">
              </a>
              <div class="dropdown-menu">
                <div class="row no-gutters form-group">
                  <span class="font-weight-normal">Сортировка:</span>
                  <select class="outline-none"
                          @change="switchSort($event, header)">
                    <option value="">-- Выбрать --</option>
                    <option value="asc">По возрастанию</option>
                    <option value="desc">По убыванию</option>
                  </select>
                </div>
                <div class="row no-gutters form-group">
                  <span class="font-weight-normal">Поиск по значению:</span>
                  <input
                         type="text"
                         class="outline-none"
                         placeholder="Поиск">
                </div>
              </div>
            </div>
          </th>
          <th v-for="(header, cIndex) in headers['objection_block']"
              v-if="header.type !== 'hidden'"
              :key="cIndex"
              style="min-width: 50px;" class="spread-header-alpha">
            <span>{{ numberToLetter(cIndex) }}</span>
            <div class="dropdown">
              <a href="javascript:void(0)"
                 class="changeType dropdown-toggle"
                 data-toggle="dropdown"
                 aria-haspopup="true"
                 aria-expanded="false">
              </a>
              <div class="dropdown-menu">
                <div class="row no-gutters form-group">
                  <span class="font-weight-normal">Сортировка:</span>
                  <select class="outline-none"
                          @change="switchSort($event, header)">
                    <option value="">-- Выбрать --</option>
                    <option value="asc">По возрастанию</option>
                    <option value="desc">По убыванию</option>
                  </select>
                </div>
                <div class="row no-gutters form-group">
                  <span class="font-weight-normal">Поиск по значению:</span>
                  <input
                         type="text"
                         class="outline-none"
                         placeholder="Поиск">
                </div>
              </div>
            </div>
          </th>
          <th v-for="(header, cIndex) in headers['crm_block']"
              v-if="header.type !== 'hidden'"
              :key="'crm_' + cIndex"
              style="min-width: 50px;" class="spread-header-alpha">
            <span>{{ numberToLetter(cIndex) }}</span>
            <div class="dropdown">
              <a href="javascript:void(0)"
                 class="changeType dropdown-toggle"
                 data-toggle="dropdown"
                 aria-haspopup="true"
                 aria-expanded="false">
              </a>
              <div class="dropdown-menu">
                <div class="row no-gutters form-group">
                  <span class="font-weight-normal">Сортировка:</span>
                  <select class="outline-none"
                          @change="switchSort($event, header)">
                    <option value="">-- Выбрать --</option>
                    <option value="asc">По возрастанию</option>
                    <option value="desc">По убыванию</option>
                  </select>
                </div>
                <div class="row no-gutters form-group">
                  <span class="font-weight-normal">Поиск по значению:</span>
                  <input
                         type="text"
                         class="outline-none"
                         placeholder="Поиск">
                </div>
              </div>
            </div>
          </th>
          <th v-for="(header, cIndex) in headers['ending_block']"
              v-if="header.type !== 'hidden'"
              :key="cIndex"
              style="min-width: 50px;" class="spread-header-alpha">
            <span>{{ numberToLetter(cIndex) }}</span>
            <div class="dropdown">
              <a href="javascript:void(0)"
                 class="changeType dropdown-toggle"
                 data-toggle="dropdown"
                 aria-haspopup="true"
                 aria-expanded="false">
              </a>
              <div class="dropdown-menu">
                <div class="row no-gutters form-group">
                  <span class="font-weight-normal">Сортировка:</span>
                  <select class="outline-none"
                          @change="switchSort($event, header)">
                    <option value="">-- Выбрать --</option>
                    <option value="asc">По возрастанию</option>
                    <option value="desc">По убыванию</option>
                  </select>
                </div>
                <div class="row no-gutters form-group">
                  <span class="font-weight-normal">Поиск по значению:</span>
                  <input
                         type="text"
                         class="outline-none"
                         placeholder="Поиск">
                </div>
              </div>
            </div>
          </th>
        </tr>
        <tr>
          <th style="background-color: #f3f6f9"><span></span></th>
          <th colspan="3">
            <img alt=""
                 height="48"
                 src="/media/sheet_company_logo.png">
          </th>
          <th :colspan="staticBlockLength"
              class="font-weight-bolder"
              style="background-color: #F6A205FF">
            <span class="text-white"></span>
          </th>
          <th :colspan="criteriaBlockLength"
              class="font-weight-bolder text-center"
              style="background-color: #F6A205FF">
            <span class="text-white"> КРИТЕРИИ </span>
          </th>
          <th :colspan="objectionBlockLength"
              class="font-weight-bolder text-center"
              style="background-color: #F6A205FF">
            <span class="text-white text-center"> ВОЗРАЖЕНИЯ </span>
          </th>
          <th :colspan="crmBlockLength"
              class="font-weight-bolder text-center"
              style="background-color: #F6A205FF">
            <span class="text-white"> CRM </span>
          </th>
          <th :colspan="endingBlockLength"
              class="font-weight-bolder text-center"
              style="background-color: #F6A205FF">
            <span class="text-white"> ОСТАЛЬНОЕ </span>
          </th>
        </tr>
        <tr>
          <th style="background-color: #f3f6f9"></th>
          <th v-for="(header, hIndex) in headers['static_block']"
              v-if="header.type !== 'hidden'"
              :key="'static_' + hIndex"
              class="font-weight-bolder spread-header">
            <span class="p-1" :class="header.class">{{ header.text }}</span>
          </th>
          <th v-for="(header, hIndex) in headers['criteria_block']"
              v-if="header.type !== 'hidden'"
              :key="'criteria_' + hIndex"
              class="font-weight-bolder spread-header"
              v-b-tooltip.hover.bottom="{ title: header.tooltip ? header.tooltip : '', boundary: 'window', customClass: 'tooltip-class' }">
            <span class="text-vertical p-1" :class="header.class">
              {{ header.text }}
            </span>
          </th>
          <th v-for="(header, hIndex) in headers['objection_block']"
              :key="'objection_' + hIndex"
              class="font-weight-bolder spread-header">
            <span :id="hIndex"
                  class="text-vertical">
              {{ header.text }}
            </span>
          </th>
          <th v-for="(header, hIndex) in headers['crm_block']"
              v-if="header.type !== 'hidden'"
              :key="'crm_' + hIndex"
              class="font-weight-bolder spread-header">
            <span class="text-vertical p-1" :class="header.class">
              {{ header.text }}
            </span>
          </th>
          <th v-for="(header, hIndex) in headers['ending_block']"
              v-if="header.type !== 'hidden'"
              :key="'ending_' + hIndex"
              class="font-weight-bolder spread-header">
            <span class="p-1">
              {{ header.text }}
            </span>
          </th>
        </tr>
        </thead>

        <tbody>
        <tr v-for="(item, rIndex) in resource"
            :key="'resource_' + rIndex"
            :class="{'row-selected': item.selected}">
          <td class="text-right" style="background-color: #f3f6f9">
            <span class="p-5">{{ rIndex + 1 }}</span>
          </td>
          <td :id="`resource_${rIndex}_${cIndex}`"
              v-for="(cell, cIndex) in item.data"
              v-if="cell.type !== 'hidden'"
              :key="cIndex"
              class="cell"
              :class="[{'cell-selected': cell.selected}, cell.class]"
              :style="cell.style"
              v-b-tooltip.hover.topright="{ title: cell.tooltip ? cell.text : '', customClass: 'tooltip-class' }"
              @click="onClick(item, cell, rIndex)">
            <div class="cell-content">
              <template v-if="cell.type === 'buttons'">
                <template v-for="(button, bIndex) in cell.buttons">
                  <a v-if="button.type === 'link'"
                     :href="button.text"
                     target="_blank"
                     class="btn"
                     style="padding: 0 1rem;"
                     :class="button.class || 'btn-primary'">
                    <span class="fa" :class="button.icon"></span>
                  </a>
                  <a v-if="button.type === 'tooltip'"
                     href="javascript:void(0)"
                     v-b-tooltip.hover.top="{ title: button.text, interactive: false}"
                     class="btn"
                     style="padding: 0 1rem;"
                     :class="button.class || 'btn-primary'">
                    <span class="fa" :class="button.icon"></span>
                  </a>
                  <template v-if="button.type === 'audio'">
                    <a href="javascript:void(0)"
                       :id="`button_audio_${button.text}`"
                       class="btn"
                       style="padding: 0 1rem;"
                       :class="button.class || 'btn-primary'"
                       @click="showPopover($event, item, button)">
                      <span class="fa" :class="button.icon"></span>
                    </a>
                    <b-popover custom-class="audio-popover"
                               title=""
                               :show="button.popover"
                               triggers="manual"
                               :target="`button_audio_${button.text}`">
                      <audio-player v-if="button.type === 'audio'"
                                    :ref="`audioPlay${rIndex}${bIndex}`"
                                    :src="button.text"
                                    autoplay
                                    style="width: 400px;"
                                    @audio-play="button.icon = 'fa-stop'"
                                    @audio-ended="button.icon = 'fa-play'"/>
                    </b-popover>
                  </template>
                </template>
              </template>
              <span v-else-if="cell.text === 'outbound'" class="mdi mdi-phone-outgoing mdi-18px text-muted"></span>
              <span v-else-if="cell.text === 'inbound'" class="mdi mdi-phone-incoming mdi-18px"></span>
              <span v-else-if="cell.type === 'icon'" class="p-1" :class="cell.icon"></span>
              <span v-else
                    class="p-1"
                    @mouseenter="showTooltip($event, cell)">
              {{ cell ? cell.text : '' }}
            </span>
            </div>
          </td>
        </tr>
        </tbody>
      </table>
    </b-row>
    <b-row class="justify-content-end">
      <b-pagination
          v-model="page"
          :total-rows="total"
          :per-page="step"
          aria-controls="v-spreadsheet"
      ></b-pagination>
    </b-row>
    <b-card-footer class="text-left">
      <router-link tag="a" class="btn btn-light"
                :to="{ name: 'projects.list' }">
        <i class="fa fa-arrow-left"></i>
        Список проектов
      </router-link>
    </b-card-footer>
  </div>
</template>

<style lang="scss" scoped>
.table-bordered td:not(:first-child) {
  border: 1px solid #5e6278;
}
.spread-edit-panel {
  min-width: 150px;
  padding: 5px 12px;
  margin-bottom: 22px;
}
.cell {
  max-width: 200px;
  line-height: normal;
  .cell-content {
    max-width: inherit;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    .btn {
      border-radius: 4px!important;
    }
  }
}
.cell-selected {
  border: 2px solid #0d8ddc !important;
}
.row-selected {
  td:first-child {
    background-color: #e4e6ef !important;
  }

  td {
    border-bottom: 1px solid #7e82996b;
  }
}
.cell-input {
  border: none;
  outline: none;
  background-color: inherit;
}
.mx-input-wrapper > .mx-input-custom {
  height: inherit !important;
  width: 100% !important;
  padding: 0 !important;
  line-height: 1.4 !important;
  outline: none !important;
  border: none !important;
  -webkit-box-shadow: none !important;
  box-shadow: none !important;
}
.spread-header {
  color: #f3f6f9;
  //padding-right: 17px;
  background-color: #131628;

  .text-vertical {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    max-height: 76px;
    overflow: hidden;
    white-space: nowrap;
  }
  .text-horizontal {
    writing-mode: horizontal-tb!important;
  }
}
.spread-header-alpha {
  background-color: #f3f6f9;

  .changeType {
    display: none;
    background: #eee;
    border-radius: 2px;
    border: 1px solid #bbb;
    color: #bbb;
    font-size: 9px;
    line-height: 9px;
    padding: 2px;
    margin: 3px 1px 0 5px;
  }

  &:hover {
    .changeType {
      display: block;

      &:hover {
        color: #2e2b2b;
      }
    }
  }

  .dropdown {
    position: relative;
    display: inline-block;
    float: right;
    .dropdown-toggle:after {
      border: none!important;
      vertical-align: initial;
      content: "\25BC";
    }
    .dropdown-menu {
      border-radius: unset;
      box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
      padding: 12px 16px;
      transform: translateY(20%);
      transition: all .2s;
      will-change: unset !important;
    }
  }
}
.outline-none {
  outline: none;
}
.audio-popover {
  max-width: unset!important;
}
.popover-body {
  padding: 0.25rem 0.25rem!important;
}
.popover .arrow {
    display: none!important;
}
</style>
<style>
.tooltip-class .tooltip-inner {
  max-height: 300px;
  overflow-y: auto;
}
.spread-header-alpha:not(:hover) .dropdown-menu {
  display: none;
}
.overlay {
  position: absolute;
  width: 100%;
  height: 100%;
  background: #dddddd24;
  z-index: 999;
  opacity: 1;
}
</style>
