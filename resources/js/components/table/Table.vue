<script>
import {gbNumber, isEmpty, isEmptyObj, getObjectVal} from '../../spa_helpers/common.js'
import {TableMixin} from './table_mixin'
import srx from "../../functions";
import moment from "moment";
import js_settings from "../../js_settings";
import { cloneDeep } from 'lodash'

export default {
  name: "Table",
  props: {
    id: {
      type: [Number, String],
      default: ''
    },
    data_src: {
      type: String,
      default: ''
    },
    data_prop_name: {
      type: String,
      default: ''
    },
    filters: {
      type: Object,
      default() {
        return {}
      }
    },
    filters_default: {
      type: Object,
      default() {
        return {}
      }
    },
    dicts: {
      type: Object,
      default() {
        return {}
      }
    },
    dataParams: {
      type: Object,
      default() {
        return {}
      }
    },
    with_export: {
      type: Boolean,
      default: false
    },
    with_master_store: {
      type: Boolean,
      default: false
    },
    master_field: {
      type: String,
      default: ''
    },
    reload: {
      type: String,
      default: ''
    },
    format: {
      type: String,
      default: ''
    },
    eventPrefix: {
      type: String,
      default() {
        return 'Table:'
      }
    },
    columns: {
      type: Array,
      default() {
        return []
      }
    },
    settings: {
      type: Object,
      default() {
        return {}
      }
    },
    master_store_callback: {
      type: Function,
      default: null
    },
    fetchCallback: {
      type: Function,
      default: undefined
    },
    isSaveEventFilter: {
      type: Boolean,
      default: true
    }
  },
  mixins: [ TableMixin ],
  data() {
    return {
      local_filters: this.filters,
      data_src_local: this.data_src,
      items: [],
      sortCol: null,
      sortDir: null,
      total: 0,
      step: 10,
      page: 0,
      searchQuery: null,
      value: null,
      loading: false,
      default_sort_col: '',
      default_sort_dir: '',
      default_filters: {},
      width: 1000,
      columns_as_object: {},
      collapsed: false,
      window_width: 10000,
      show_details: {},
      select_all_items: false,
      route: null,
      local_with_master_store: null,
      breakpoint: null,
      breakpointStyles: {
        'xl': 'd-xl-none',
        'lg': 'd-lg-none d-xl-none',
        'md': 'd-md-none d-lg-none d-xl-none',
        'sm': 'd-sm-none d-lg-none d-xl-none',
        'xs': 'd-block d-sm-none'
      }
    }
  },
  created: function () {
    let c_obj = this.columns_as_object;
    _.map(this.columns, function (item) {
      c_obj[item.name] = item;
    });
    this.local_with_master_store = !!this.with_master_store;
    if (this.local_with_master_store && !!this.master_field) {
      //
    }

    let _this = this;
    let filters_names = _.map(_.filter(this.columns, function(item){ return item.searchable === true}), 'name');
    let filters = {};

    let columns_with_dicts = _.filter(this.columns, function(item){ return item.search_dict});
    let dicts = {};

    _.forEach(columns_with_dicts, (dict) => {
      dicts[dict.name] = this.dicts[dict.search_dict];
    });

    let savedStep = localStorage.getItem(this.storageIndex('step'));
    if (savedStep !== null) {
      if (isEmpty(this.settings)) {
        this.settings = {
          refresh_default: false,
          step: savedStep
        };
      } else {
        if (isEmpty(this.settings.step)) {
          this.settings.step = savedStep
        }
      }
    } else {
      if (isEmpty(this.settings)) {
        this.settings = {
          refresh_default: false,
          step: this.step
        };
      } else {
        if (isEmpty(this.settings.step)) {
          this.settings.step = 10
        }
      }
    }

    this.step = this.settings.step;
    // Init sorting column and direction
    let savedSortCol = localStorage.getItem(this.storageIndex('sortCol'));
    if (savedSortCol !== null) {
      this.sortCol = savedSortCol;
      if (this.settings.sort_on_init === undefined) {
        this.default_sort_col = this.columns[0].name;
      } else {
        this.default_sort_col = this.columns[this.settings.sort_on_init.col].name;
      }
    } else {
      if (this.settings.sort_on_init === undefined) {
        this.sortCol = this.columns[0].name;
      }
      else {
        this.sortCol = this.columns[this.settings.sort_on_init.col].name;
      }
      this.default_sort_col = this.sortCol;
    }
    let savedSortDir = localStorage.getItem(this.storageIndex('sortDir'));
    if (savedSortDir !== null) {
      this.sortDir = savedSortDir;
      if (this.settings.sort_on_init === undefined) {
        this.default_sort_dir = this.columns[0].sortDir === undefined ? 'desc' : this.columns[0].sortDir;
      }
      else {
        this.default_sort_dir = this.settings.sort_on_init.dir === undefined ? 'desc' : this.settings.sort_on_init.dir;
      }
    } else {
      if (this.settings.sort_on_init === undefined) {
        this.sortDir = this.columns[0].sortDir === undefined ? 'desc' : this.columns[0].sortDir;
      }
      else {
        this.sortDir = this.settings.sort_on_init.dir === undefined ? 'desc' : this.settings.sort_on_init.dir;
      }
      this.default_sort_dir = this.sortDir;
    }

    // Init filtering
    let savedFiltersJson = localStorage.getItem(this.storageIndex('filters'));

    if (savedFiltersJson !== null) {
      let storageFilters = JSON.parse(savedFiltersJson);
      storageFilters = Object.assign(filters,storageFilters);
      _.forEach(storageFilters, (v,k) =>{
        if (!isEmpty(v)) {
          let finded = _.find(dicts, (i) => {
            return i.id == v;
          });
        }
      });
      if (isEmptyObj(this.local_filters) || this.local_filters === null) {
        this.local_filters = storageFilters;
      }
      else {
        _.each(storageFilters, (item, k) => {
          this.local_filters[k] = item;
        });
      }
    } else {
      if (this.filters_default === undefined) {
        this.default_filters = _.clone(this.local_filters);
      } else {
        this.default_filters = this.filters_default;
      }
    }
  },
  computed: {
    settings_step: function () {
      return this.settings ? this.settings.step : this.step;
    },
    q_params: function () {
      if (this.dataParams === null) return '';
      let result = '';
      _.forEach(this.dataParams, function (v, k) {
        result += '&' + k + '=' + v;
      });
      return result;
    },
    q_slow: function () {
      let q = '';
      let q_arr = [];
      _.forEach(this.local_filters, (v, k) => {
        let newV = _.cloneDeep(v)
        if (newV !== null && newV !== '' && newV !== undefined) {
          let c_obj_k = _.find(this.columns, column => {
            return column.name === k;
          });
          if (c_obj_k === undefined) return;
          if (c_obj_k.search_dict === undefined && !js_settings.formats.hasOwnProperty(c_obj_k.format)) {
            q_arr.push(k.replace('.', '_') + ' eq ' + '\'' + newV + '\'');
          } else {
            if (c_obj_k && js_settings.formats.hasOwnProperty(c_obj_k.format)) {
              if (Array.isArray(newV) && newV.length === 2) {
                _.forEach(newV, (it, key) => {
                  let dt = moment(it, js_settings.formats.date);
                  newV[key] = !dt.isValid() ? it : dt.format('YYYY-MM-DD');
                })
                newV = _.join(newV, '~')
                q_arr.push(k.replace('.', '_') + ' btw ' + newV);
              } else {
                let dt = moment(newV, js_settings.formats.date);
                newV = !dt.isValid() ? newV : dt.format('YYYY-MM-DD')
                q_arr.push(k.replace('.', '_') + ' = ' + newV)
              }
            } else {
              q_arr.push(k.replace('.', '_') + ' = ' + newV)
            }
          }
        }
      });
      if (this.searchQuery !== null && this.searchQuery !== '') {
        let dt = moment(this.searchQuery, js_settings.formats.date)
        q += '&search=' + encodeURIComponent(!dt.isValid() ? this.searchQuery : dt.format('YYYY-MM-DD'));
      }
      if (q_arr.length > 0) {
        q += '&$filter=' + encodeURIComponent(q_arr.join(' $and$ '));
      }
      return q;
    },
    q_fast: function () {
      let q = 'limit=' + this.step + '&offset=' + this.page * this.step;
      if (this.sortCol !== null) {
        q += '&$orderBy=' + encodeURIComponent(this.sortCol.replace('.', '_') + ' ' + this.sortDir);
      }
      return '?' + q;
    },
    filters_change: function () {
      let q = '';
      _.forEach(this.local_filters, function (v, k) {
        if (v !== null && v !== '') {
          q += '&' + k + '=' + encodeURIComponent(v);
        }
      });
      return q;
    },
    refresh_default: function () {
      if (!isEmpty(this.settings) && this.settings.refresh_default) {
        return this.settings.refresh_default;
      } else {
        return false;
      }
    },
    selected_items_count: function () {
      return _.filter(this.items, function (item) {
        return item.is_selected;
      }).length;
    },
    selected_not_all_items: function () {
      return (this.selected_items_count > 0 && this.selected_items_count < this.items.length);
    },
    selectable: function () {
      if (this.settings && this.settings.lock_show_entries) {
        return true;
      }
      let is_true = (_.filter(this.columns, function (col) {
        return col.type === 'checkbox';
      }).length > 0);
      if (is_true) {
        this.step = 9999;
      }
      return is_true;
    },
    getBreakpoint () {
      let breakpoints = {'xl':1, 'lg':2, 'md':3, 'sm':4, 'xs':5, 'xxx': 6},
          breakpoint = _.reduce(this.columns, (result, value, key) => {
            return value.breakpoints &&
              breakpoints[value.breakpoints] < breakpoints[result]
                ? value.breakpoints
                : result
      }, 'xxx')
      return breakpoint !== 'xxx' ? this.breakpointStyles[breakpoint] : 'd-none'
    },
    noItemsFound() {
      return !this.loading && _.isEmpty(this.items)
    },
  },
  watch: {
    filters: function (newVal) {
      this.local_filters = newVal;
    },
    route: _.debounce(function(newVal) {
      this.$emit('route',newVal.replace('api/','export/').replace('?','/csv?'));
      this.refreshImmediately();
    }, 500),
    data_src: function (newVal) {
      this.data_src_local = newVal;
    },
    settings_step: function (newVal) {
      this.step = newVal;
    },
    selected_not_all_items: function(newVal) {
      if (newVal === false) {
        this.select_all_items = (this.selected_items_count === this.items.length);
      }
    },
    select_all_items: function(newVal) {
      let _this = this;
      if (newVal === true) {
        _.forEach(_this.items, item => {
          if (_this.settings.selCallback !== undefined) {
            if (!_this.settings.selCallback(item)) {
              return;
            }
          }
          item.is_selected = true;
        });
      }
      else {
        _.forEach(_this.items, function(item) {
          item.is_selected = false;
        });
      }
    },
    refresh_default: function (newVal) {
      if (newVal) {
        this.refreshDefault();
      }
    },
    filters_change: _.debounce(function (newVal, oldVal) {
      const filtersToSave = cloneDeep(this.local_filters)
      if (!this.isSaveEventFilter) {
          delete filtersToSave.event
      }
      if (newVal !== oldVal) {
        localStorage.setItem(this.storageIndex('filters'), JSON.stringify(filtersToSave));
        this.page = 0;
      }
    }, 10),
    step: function (newVal, oldVal) {
      this.settings.step = newVal;
      localStorage.setItem(this.storageIndex('step'), newVal);
      this.page = Math.floor(oldVal * (this.page) / newVal);
      for (let i = 0; i < newVal - 1; i++) {
        this.show_details[i] = false;
      }

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
    reload: function (newVal, oldVal) {
      if (newVal && oldVal !== newVal) {
        this.refreshImmediately();
      }
    },
    items: function (newVal) {
      //
    }
  },
  methods: {
    isComponent (col, item) {
      if (item === null && col.searchable && col.search_dict) {
        switch(col.search_type) {
          case 'autocomplete':
            return 'Table-autocomplete'
          case 'select':
          default:
            return 'Table-select'
        }
      }
      if (item === null && col.searchable && !col.search_dict) {
        switch (col.format) {
          case 'datetimefull':
          case 'datetime':
          case 'datetimems':
          case 'longtime':
          case 'time':
          case 'date':
            return "TableDatepicker"
        }
      }
      if (col.type === 'callback') {
        return 'TableCallback'
      }
      if (col.type === 'button') {
        return 'TableButton'
      }
      if (col.type === 'buttons') {
        return 'TableButtons'
      }
      if (col.type === 'badge') {
        return 'TableBadge'
      }
      if (col.type === 'badges') {
        return 'TableBadges'
      }
      if (col.type === 'checkbox') {
        return 'TableCheckbox'
      }
      if (col.type === 'tableSwitch') {
        return 'tableSwitch'
      }
      if (item !== null && col.href !== undefined) {
        if (col.type === undefined && this.createHref(col.href, item, col.hash_params) !== '#') {
          return 'TableHref'
        }
      }
      if (item !== null && col.link !== undefined) {
        if (col.type === undefined && this.createHref(col.link, item, col.hash_params) !== '#') {
          return 'TableLink'
        }
      }

      return false
    },
    exportData: function() {
      location.href = this.route.replace('api/', 'export/').replace('?', '/csv?');
    },
    refreshImmediately: function() {
      let _this = this;
      if (this.settings.refresh_default_before_callback !== undefined) {
        this.settings.refresh_default_before_callback(this);
      }
      this.loading = true;
      let items = [];
      this.route = this.data_src_local + this.q_fast + this.q_slow + this.q_params;

      this.$http.get(this.route)
          .then(async response => {
            items = response.data[this.data_prop_name];
            items = _.map(items, function (item) {
              return _.merge(item, {vm_show_details: false, is_selected: false});
            });
            if (this.fetchCallback !== undefined) {
              items = await this.fetchCallback(items)
            }
            this.items = items;
            this.total = response.data.total;
            this.$emit('loaded', response.data);
            this.loading = false;
            this.select_all_items = false;

            if (this.local_with_master_store) {
              localStorage.setItem(this.id+'_master_storage',JSON.stringify({url: this.route, prop: this.data_prop_name}));
            }
          }, error => {
            this.loading = false
            this.$emit('error', error)
          })
    },
    storageIndex: function (name) {
      return 'PincherTable_' + this.id + '_' + name;
    },
    gbNumber: function (val) {
      return gbNumber(val);
    },
    displayCell: function (col, item) {
      if (col.display_callback !== undefined) {
        return col.display_callback(col, item, this);
      }
      if (col.type === 'button') {
        return;
      }
      if (col.value !== undefined) {
        return col.value;
      }
      if (col.dict_values !== undefined) {
        let dict = this.dicts[col.dict_values];
        return dict[item[col.name]];
      }

      let value = col.display !== undefined ? _.get(item, col.display) : _.get(item, col.name);

      if (value === null) {
        return (col.blank === undefined) ? '' : '(blank)';
      }

      if (col.format === 'date') {
        value = srx.customDate(value);
      } else if (col.format === 'datetime') {
        value = srx.customDateTime(value);
      } else if (col.format === 'datetimems') {
        value = srx.customDateTimeMS(value);
      } else if (col.format === 'date_or_time') {
        value = srx.messageDate(value);
      } else if (col.format === 'datetimefull') {
        value = srx.customDateTimeFull(value);
      } else if (col.format === 'bool') {
        value = (parseFloat(value) === 0) ? 'No' : 'Yes';
      } else if (col.value === false) {
        value = col.name;
      }

      if (col.format === 'text') {
        if ('display_trim' in col) {
          value = value.substring(0, col.display_trim);
        }
      }

      value = col.format === 'number' ? gbNumber(value) : value;
      value = col.format === 'currency' ? UMoney(value, false) : value;
      return value;
    },
    cellDetailsStyle: function (col, item) {
      let styleObj = {
        'red': col.rule === '!0' && item[col.name] !== 0 && item[col.name] !== '.00',
        'text-right': col.format === 'currency' || col.align === 'right',
        'text-center': col.align === 'center'
      };
      if (col.style_callback !== undefined) {
        styleObj = _.merge(styleObj, col.style_callback(col, item));
      }
        switch (col.breakpoints) {
            case 'lg':
                styleObj['d-lg-none'] = true;
                break;
            case 'md':
                styleObj['d-md-none'] = true;
                break;
            case 'sm':
                styleObj['d-sm-none'] = true;
                break;
            default: styleObj['d-none'] = true;
        }
      return styleObj;
    },
    cellStyle: function (col, item) {
      let styleObj = {
        'red': col.rule === '!0' && item[col.name] != 0 && item[col.name] != '.00',
        'text-right': col.format == 'currency' || col.align === 'right',
        'text-center': col.align === 'center',
      };
      if (col.style_callback !== undefined) {
        styleObj = _.merge(styleObj, col.style_callback(col, item));
      }
        switch (col.breakpoints) {
            case 'lg':
                styleObj['d-lg-table-cell'] = true;
                styleObj['d-none'] = true;
                break;
            case 'md':
                styleObj['d-md-table-cell'] = true;
                styleObj['d-none'] = true;
                break;
            case 'sm':
                styleObj['d-sm-table-cell'] = true;
                styleObj['d-none'] = true;
                break;
        }
      return styleObj;
    },
    UDate: function (d) {
      return UDate(d);
    },
    refresh: function () {
      this.loading = true;
      this.route = this.data_src_local + this.q_fast + this.q_slow + this.q_params;
      this.$emit('refresh');
    },
    showDetails: function (item) {
      item.vm_show_details = !item.vm_show_details;
    },
    clearFilters: function() {
      if (this.settings.clear_filters_before_callback !== undefined) {
        this.settings.clear_filters_before_callback(this);
      }
      this.refreshDefault();
    },
    refreshDefault: function () {
      if (this.settings.refresh_default_before_callback !== undefined) {
        this.settings.refresh_default_before_callback(this);
      }
      let columns = this.columns;
      let $filters_d = this.default_filters;
      let $filters = this.local_filters;
      _.forEach($filters, (v, k) => {
        let c = _.find(columns, (item) =>{
          return item.name === k;
        });
        if (c && c.hasOwnProperty('filter_disable') && c.filter_disable) {
          $filters[k] = v;
        }
        else {
          if (v !== null && v !== '') {
            if ($filters_d[k] === undefined) {
              $filters[k] = null;
            } else {
              $filters[k] = $filters_d[k];
            }
          }
        }
      });

      let old_route = this.route;
      this.page = 0;
      this.sortCol = this.default_sort_col;
      this.sortDir = this.default_sort_dir;
      this.loading = true;
      this.searchQuery = null;
      this.refresh();
      if (old_route === this.route) {
        this.refreshImmediately();
      }

      this.settings.refresh_default = false;
      if (this.settings.refresh_default_callback !== undefined) {
        this.settings.refresh_default_callback();
      }

    },
    switchSort: function (col) {
      if (col.sortable !== false) {
        if (col.type === 'buttons' || col.type === 'button' || col.format === 'toggle') {
          return;
        }
        if (this.sortCol === col.name) {
          this.sortDir = this.sortDir === 'desc' ? 'asc' : 'desc';
        } else {
          this.sortDir = 'desc';
        }
        this.sortCol = col.name;
        localStorage.setItem(this.storageIndex('sortDir'), this.sortDir);
        localStorage.setItem(this.storageIndex('sortCol'), this.sortCol);
      }
    },
    createHref: function (href, item, hash_params) {
      if (item[href] !== undefined) {
        return item[href].replace('api/', 'common/');
      }
      let arr = srx.explode('/', href);
      let q = '';
      let params = '';
      let error = false;
      _.forEach(arr, function (v, k) {
        if (v !== '') {
          params = v.match(/\{(.+?)\}/gi);
          if (params !== null) {
            arr[k] = v.replace(/\{(.+?)\}/gi, function(str, p1, p2, offset, s) {
              return getObjectVal(item, p1);
            });
            if (arr[k] === 'null') {
              error = true;
            }
          }
        }
      });
      let result = arr.length === 0 ? '' : arr.join('/');
      if (hash_params !== undefined) {
        let obj = {};
        _.forEach(hash_params, function(i){
          obj[i] = item[i];
        });
        result += ((-1 === result.indexOf('?')) ? '?' : '&')+'hash='+ btoa(JSON.stringify(obj));
      }
      return error ? '#' : result;
    },
    iconToggle: function (col, item) {
      return item[col.name] ? col.icon_true : col.icon_false;
    },
    showByRule: function (col, item) {
      if (col.show_callback !== undefined) {
        return col.show_callback(col, item)
      }
      if (col.show === undefined) {
        return true;
      }
      let result = true;
      let rules = srx.explode(' $and$ ', col.show);
      _.forEach(rules, function (rl) {
        if (result) {
          let rule = srx.explode(' ', rl);
          if (rule.length === 3) {
            if (rule[2] === 'null') rule[2] = null;
            switch (rule[1]) {
              case '>':
                result = item[rule[0]] > rule[2];
                break;
              case '<':
                result = item[rule[0]] < rule[2];
                break;
              case '=':
                result = item[rule[0]] == rule[2];
                break;
              case '!=':
                result = item[rule[0]] != rule[2];
                break;
            }
          }
        }
      });
      if (rules.length > 1) return result;
      rules =  srx.explode(' or ', col.show);
      if (rules.length) {
        result=false;
        _.forEach(rules, function (rl) {
          let rule = srx.explode(' ', rl);
          if (rule.length === 3) {
            if (rule[2] === 'null') rule[2] = null;
            switch (rule[1]) {
              case '>':
                result = !!(result + (item[rule[0]] > rule[2]));
                break;
              case '<':
                result = !!(result + (item[rule[0]] < rule[2]));
                break;
              case '=':
                result = !!(result + (item[rule[0]] == rule[2]));
                break;
              case '!=':
                result = !!(result + (item[rule[0]] != rule[2]));
                break;
            }
          }
        });
      }
      return result;
    },
    buttonCallback: function (event, col, item) {
      if (this.settings !== undefined && this.settings[col.id + '_callback'] !== undefined) {
        let func = this.settings[col.id + '_callback'];
        return func(col, item, this, event);
      }
    },
    onResize: function () {
      this.window_width = $(document).width();
    },
    getHeaderClass: function (col) {
      let header_class = {
        sorting: (this.sortCol !== col.name && col.sortable !== false),
        'sorting_asc': this.sortCol === col.name && this.sortDir === 'asc',
        'sorting_desc': this.sortCol === col.name && this.sortDir === 'desc',
        active: this.sortCol === col.name,
        'text-right': col.align === 'right' || col.format === 'currency',
        'text-center': col.align === 'center'
      };
      if (col.type === 'buttons' || col.type === 'button' || col.format === 'toggle' || col.type === 'checkbox') {
        header_class = { none: true };
      }
        switch (col.breakpoints) {
            case 'lg':
                header_class['d-none'] = true;
                header_class['d-lg-table-cell'] = true;
                break;
            case 'md':
                header_class['d-none'] = true;
                header_class['d-md-table-cell'] = true;
                break;
            case 'sm':
                header_class['d-none'] = true;
                header_class['d-sm-table-cell'] = true;
                break;
        }
      return header_class;
    },
    merge: function (target, source) {
      return _.merge(target, source)
    }
  }
}
</script>

<template>
  <div class="pinscher-table">
    <div class="table-wrapper vtable smart-style-2">
      <table-settings></table-settings>
      <div class="table-table-wrapper">
        <div v-show="loading" class="overlay">
          <span class="fa fa-spinner fa-3x fa-spin" style="position: absolute; top: 99px;left: 50%;"></span>
        </div>
        <table class="table table-bordered table-striped">
          <thead>
          <table-header></table-header>
          </thead>
          <tbody>
          <tr v-if="settings.searchable !== false">
            <td :class="`vtable-plus active ${getBreakpoint}`"></td>
            <template v-for="(col, colIndex) in columns">
              <td
                v-if="!col.hidden"
                :key="colIndex"
                :class="getHeaderClass(col)"
                style="padding-right:17px;background-color: #f5f5f5;"
              >
                <template v-if="isComponent(col, null)">
                  <component :is="isComponent(col, null)"
                            :key="colIndex"
                            :col="col"
                  ></component>
                </template>
                <template v-else-if="(col.searchable && !col.search_dict && col.format !=='date' && col.format !=='datetimefull')">
                  <input style="width:100%" class="form-control" :disabled="col.filter_disable" v-model="local_filters[col.name]" type="text">
                </template>
                <template v-else>
                  <span>&nbsp;</span>
                </template>
              </td>
            </template>

          </tr>
          </tbody>
          <tbody v-for="(item, index) in items" :key="index">
          <tr>
            <td :class="`vtable-plus ${getBreakpoint}`">
              <i
                :class="{
                  'fa fa-plus':(!item.vm_show_details),
                  'fa fa-minus' :item.vm_show_details
                }"
                @click="item.vm_show_details = !item.vm_show_details"
              ></i>
            </td>

            <template v-for="(col, cIndex) in columns">
              <td v-if="!col.hidden" :class="cellStyle(col, item)" :key="`${index}_${cIndex}`">
                <template v-if="col.format === 'toggle'">
                  <i :class="iconToggle(col, item)" aria-hidden="true"></i>
                </template>

                <template v-if="isComponent(col, item)">
                  <component :is="isComponent(col, item)"
                            :key="`${index}_${cIndex}`"
                            :is-header="false"
                            :col="col"
                            :item="item"
                            @switch-change="$emit('active-switch-change', $event)"
                  ></component>
                </template>

                <span v-show="showByRule(col,item)" :class="{'loading-row': loading}"
                      v-if="col.href === undefined && col.type === undefined  && col.format !== 'toggle' && col.link === undefined">
                  <img v-if="col.img !== undefined && col.filename !== undefined" :src="col.img+item[col.filename]" width='20px' style="margin-right: 3px;">
                  <span v-html="displayCell(col, item)" style="display: inline"></span>
                </span>
              </td>
            </template>
          </tr>
          <template v-if="item.vm_show_details">
            <tr :class="`${getBreakpoint}`">
              <td :colspan="(columns.length + 1)" style="background: whitesmoke;">
                <table style="margin-left: 22px;">
                  <template v-for="(col, cIndex) in columns">
                    <tr v-if="!col.hidden" :class="cellDetailsStyle(col, item)" :key="`${index}_${cIndex}`">
                      <th style="margin-top: 7px;" class="pull-right">{{col.title}}:</th>
                      <td style="padding-left: 10px; text-align:left;">
                        <i v-if="col.format === 'toggle'" :class="iconToggle(col, item)"
                          aria-hidden="true"></i>

                        <template v-if="isComponent(col, item)">
                          <component :is="isComponent(col, item)"
                                    :key="index"
                                    :is-header="false"
                                    :col="col"
                                    :item="item"
                          ></component>
                        </template>

                        <span v-show="showByRule(col,item)" :class="{'loading-row': loading}"
                              v-if="col.href === undefined && col.type === undefined  && col.format !== 'toggle' && col.link === undefined">
                          <span v-html="displayCell(col, item)"></span>
                        </span>
                      </td>
                    </tr>
                  </template>
                </table>
              </td>
            </tr>
          </template>
          </tbody>
          <tbody v-show="noItemsFound">
            <tr>
              <td colspan="100%">
                <b-card-body class="text-center text-muted">Записей не найдено!</b-card-body>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <table-pagination v-show="!noItemsFound"></table-pagination>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import "./sass/table.scss";
.pinscher-table .v-select {
    min-width: 95px!important;
}
</style>
