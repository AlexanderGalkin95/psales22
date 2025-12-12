<template>
  <div>
    <!--begin::Dashboard-->
    <div class="row">
      <div class="col-xl-8">
        <ListWidget1></ListWidget1>
      </div>
    </div>
  </div>
</template>

<script>
import { SET_BREADCRUMB } from "../../core/services/store/breadcrumbs.module";
import AdvancedTableWidget2 from "../../view/content/widgets/advance-table/Widget2.vue";
import EngageWidget1 from "../../view/content/widgets/engage/Widget1.vue";
import ListWidget1 from "../content/widgets/list/DeckLinks.vue";
import ListWidget2 from "../../view/content/widgets/list/Widget2.vue";
import ListWidget3 from "../../view/content/widgets/list/Widget3.vue";
import ListWidget5 from "../../view/content/widgets/list/Widget5.vue";
import ListWidget8 from "../../view/content/widgets/list/Widget8.vue";
import ListWidget10 from "../../view/content/widgets/list/Widget10.vue";
import ListWidget11 from "../../view/content/widgets/list/Widget11.vue";
import ListWidget12 from "../../view/content/widgets/list/Widget12.vue";

export default {
  name: "dashboard",
  components: {
    ListWidget1,
  },
  mounted() {
    this.$store.dispatch(SET_BREADCRUMB, [{ title: "Dashboard" }]);
  },
  methods: {
    setActiveTab1(event) {
      this.tabIndex = this.setActiveTab(event);
    },
    setActiveTab2(event) {
      this.tabIndex2 = this.setActiveTab(event);
    },
    /**
     * Set current active on click
     * @param event
     */
    setActiveTab(event) {
      // get all tab links
      const tab = event.target.closest('[role="tablist"]');
      const links = tab.querySelectorAll(".nav-link");
      // remove active tab links
      for (let i = 0; i < links.length; i++) {
        links[i].classList.remove("active");
      }

      // set current active tab
      event.target.classList.add("active");

      // set clicked tab index to bootstrap tab
      return parseInt(event.target.getAttribute("data-tab"));
    }
  }
};
</script>
