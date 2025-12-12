<template>
  <div class="card card-custom card-stretch gutter-b">
    <!--begin::Header-->
    <div class="card-header border-0">
      <h3 class="card-title font-weight-bolder text-dark">История активности по заданию</h3>
      <div class="card-toolbar">
        <!-- <Dropdown2><i class="ki ki-bold-more-hor"></i></Dropdown2> -->
      </div>
    </div>
    <!--end::Header-->
    <!--begin::Body-->
    <div class="card-body pt-0">
      <perfect-scrollbar
        ref="prefectScrollbar"
        class="scroll overflow-y-auto max-h-200px"
        :options="{ suppressScrollX: true, railBorderYWidth: 8 }"
      >
        <div v-for="(item, hIndex) in history" :key="hIndex" class="mb-6">
          <!--begin::Content-->
          <div class="d-flex align-items-center flex-grow-1">
            <!--begin::Section-->
            <div
              class="
                d-flex
                flex-wrap
                align-items-center
                justify-content-between
                w-100
              "
            >
              <!--begin::Info-->
              <div class="d-flex flex-column align-items-cente py-2 w-75">
                <!--begin::Title-->
                <a
                  href="#"
                  class="
                    text-dark-75
                    font-weight-bold
                    text-hover-primary
                    font-size-lg
                    mb-1
                  "
                  >{{ item.description }}</a
                >
                <!--end::Title-->
                <!--begin::Data-->
                <span class="text-muted font-weight-bold">{{ customDate(item.created_at) }}</span>
                <!--end::Data-->
              </div>
              <!--end::Info-->
              <!--begin::Label-->
              <span
                class="label label-lg label-inline font-weight-bold py-4"
                v-bind:class="`label-light-${item.class}`"
                >{{
                  item.author.name
                }}</span
              >
              <!--end::Label-->
            </div>
            <!--end::Section-->
          </div>
          <!--end::Content-->
        </div>
        <!--begin::Item-->
        <!--end::Item-->
      </perfect-scrollbar>
    </div>
    <!--end: Card Body-->
  </div>
</template>
  
<script>
import srx from "@/functions";

export default {
  name: "Widget13",
  props: {
    history: {
      type: Array,
      default: () => ([])
    }
  },
  methods: {
    customDate(date) {
      return srx.customDate(date)
    },
    getAuthorName(item) {
      return item.author?.name || 'System'
    }
  }
};
</script>
  