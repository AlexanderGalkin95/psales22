<template>
  <!-- begin:: Footer -->
  <div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
    <!--begin::Container-->
    <div
      class="d-flex flex-column flex-md-row align-items-center justify-content-between"
      v-bind:class="{ 'container-fluid': widthFluid, container: !widthFluid }"
    >
      <!--begin::Copyright-->
      <div class="text-dark order-2 order-md-1">
          <span class="text-muted font-weight-bold mr-2">Copyright &copy; 2021</span><a
          href="#"
          target="_blank"
          class="text-dark-75 text-hover-primary"
          >Pinscher Sales. Все права защищены</a
        >
      </div>
      <!--end::Copyright-->
    </div>
    <!--end::Container-->
  </div>
  <!-- end:: Footer -->
</template>

<script>
import { mapGetters } from "vuex";

export default {
  name: "KTFooter",
  computed: {
    ...mapGetters(["layoutConfig"]),

    /**
     * Check if footer container is fluid
     */
    widthFluid() {
      return this.layoutConfig("footer.width") === "fluid";
    }
  }
};
</script>
