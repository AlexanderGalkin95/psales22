<script>
import { isObject } from 'lodash';

export default {
  name: "DropdownList",
  components: {},
  props: {
    variant: {
      type: String,
      default: 'outline-light'
    },
    text: {
      type: String,
      default: 'Toggle'
    },
    boundary: {
      type: String,
      default: 'viewport'
    },
    popperOpts: {
      type: Object,
      default: () => ({})
    },
    toggleClass: {
      type: [String, Object],
      default: 'btn btn-transparent btn-sm font-weight-bolder px-5'
    },
    multiselect: {
      type: Boolean,
      default: false
    },
    items: {
      type: Array,
      default: () => [],
    },
    value: {
      type: [String, Number, Array]
    }
  },
  data: () => ({
    localValue: []
  }),
  watch: {
    value: {
      handler(newVal) {
        this.localValue = newVal
      }
    }
  },
  methods: {
    getItemLabel(item) {
      return isObject(item) ? item.label : item
    },
    handleInput() {
      this.$emit('input', this.localValue)
    },
    handleSelected(item) {
      this.$emit('input', [item])
    }
  }
}
</script>

<template>
  <b-dropdown
    size="sm"
    :toggle-class="toggleClass"
    :variant="variant"
    :text="text"
    :boundary="boundary"
    :popper-opts="popperOpts"
  >
    <template #button-content>
      <slot name="button-content">
        <span>{{ text }}</span>
      </slot>
    </template>
    <div class="navi navi-hover min-w-md-200px">
      <b-dropdown-text v-if="$slots['header']" tag="div" class="navi-header pb-1">
        <slot name="header"></slot>
      </b-dropdown-text>
      <b-dropdown-text v-if="!items.length" tag="div" class="navi-item pb-1 text-center">
        <slot name="no-option">
          <span class="navi-text"> Ничего не найдено </span>
        </slot>
      </b-dropdown-text>
      <b-form-checkbox-group
        v-model="localValue"
        stacked
        @input="handleInput"
      >
        <b-dropdown-text
          v-for="(item, iIdx) in items"
          :key="iIdx"
          class="navi-item cursor-pointer"
        >
          <slot name="option" :item="item">
            <a class="navi-link">
              <b-form-checkbox
                v-if="multiselect"
                size="md"
                class="w-100 text-nowrap text-hover-primary material-checkbox"
                :name="`item_${iIdx}`"
                :value="item"
              >
                {{ getItemLabel(item) }}
              </b-form-checkbox>
              <span v-else class="w-100" @click="handleSelected(item)">{{ getItemLabel(item) }}</span>
            </a>
          </slot>
        </b-dropdown-text>
      </b-form-checkbox-group>
    </div>
  </b-dropdown>
</template>
  
<style lang="scss">
.dropdown-menu {
  outline: none;
}
.b-dropdown-text {
  padding: 0;
}
</style>
