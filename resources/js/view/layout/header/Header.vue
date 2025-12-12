<template>
  <!-- begin:: Header -->
  <div
    id="kt_header"
    ref="kt_header"
    class="header header-fixed"
    v-bind:class="headerClasses"
  >
    <div
      class="d-flex align-items-stretch justify-content-between"
      v-bind:class="{ 'container-fluid': widthFluid, container: !widthFluid }"
    >
      <div class="d-none d-lg-flex align-items-center mr-3">
        <!--begin::Aside Toggle-->
        <button
          class="btn btn-icon aside-toggle ml-n3 mr-10"
          id="kt_aside_desktop_toggle"
          ref="kt_aside_desktop_toggle"
        >
          <span class="svg-icon svg-icon-xxl svg-icon-dark-75">
            <!--begin::Svg Icon | path:assets/media/svg/icons/Text/Align-left.svg-->
            <inline-svg src="/media/svg/icons/Text/Align-left.svg" />
            <!--end::Svg Icon-->
          </span>
        </button>
        <!--end::Aside Toggle-->
        <div class="header-logo">
          <router-link :to="{ name: 'dashboard' }">
              <span class="pnsch">Pinscher</span> <span class="sales">Sales</span>
          </router-link>
        </div>

      </div>

      <KTTopbar></KTTopbar>
    </div>
  </div>
  <!-- end:: Header -->
</template>

<script>
import { mapGetters } from "vuex";
import KTTopbar from "../../../view/layout/header/Topbar.vue";
import KTLayoutHeader from "../../../assets/js/layout/base/header.js";

export default {
  name: "KTHeader",
  components: {
    KTTopbar
  },
  mounted() {
    // Init Desktop & Mobile Headers
    KTLayoutHeader.init("kt_header", "kt_header_mobile");
  },
  computed: {
    ...mapGetters(["layoutConfig", "getClasses"]),

    /**
     * Check if the header menu is enabled
     * @returns {boolean}
     */
    headerMenuEnabled() {
      return !!this.layoutConfig("header.menu.self.display");
    },

    /**
     * Get extra classes for header based on the options
     * @returns {null|*}
     */
    headerClasses() {
      const classes = this.getClasses("header");
      if (typeof classes !== "undefined") {
        return classes.join(" ");
      }
      return null;
    },

    /**
     * Get extra classes for header menu based on the options
     * @returns {null|*}
     */
    headerMenuClasses() {
      const classes = this.getClasses("header_menu");
      if (typeof classes !== "undefined") {
        return classes.join(" ");
      }
      return null;
    },

    /**
     * Check if header container is fluid
     */
    widthFluid() {
      return this.layoutConfig("header.self.width") === "fluid";
    }
  }
};
</script>
