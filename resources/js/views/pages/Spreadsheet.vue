<script>
import VSpreadsheet from '../../components/VSpreadsheet/VSpreadsheet'
import { mapGetters } from 'vuex'
import {RESET_LAYOUT_CONFIG, SET_LAYOUT_CONFIG} from '../../core/services/store/config.module'

export default {
  name: "Spreadsheet",
  components: {
    VSpreadsheet
  },
  data() {
    return {

    }
  },
  computed: {
    ...mapGetters([
      "layoutConfig"
    ]),
    contentFluid() {
      return this.layoutConfig()
    },
  },
  beforeMount() {
    let config = _.cloneDeep(this.contentFluid)
    config.content.width = 'fluid'
    this.$store.dispatch(SET_LAYOUT_CONFIG, config)
  },
  beforeDestroy() {
    this.$store.dispatch(RESET_LAYOUT_CONFIG)
  },
  mounted() {

  }
}
</script>

<template>
  <v-spreadsheet></v-spreadsheet>
</template>

<style scoped>
</style>
