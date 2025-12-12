<template>
  <div class="topbar-item mr-4">
    <div id="kt_quick_actions_toggle">
      <a href="#" class="btn btn-icon btn-sm btn-clean btn-text-dark-75">
        <span class="svg-icon svg-icon-lg">
          <inline-svg src="/media/svg/icons/Layout/Layout-4-blocks.svg" />
        </span>
      </a>
    </div>

    <div
      id="kt_quick_actions"
      ref="kt_quick_actions"
      class="offcanvas offcanvas-right p-10"
    >
      <!--begin::Header-->
      <div
        class="offcanvas-header d-flex align-items-center justify-content-between pb-10"
      >
        <h3 class="font-weight-bold m-0">
          Быстрые Действия
          <small class="text-muted font-size-sm ml-2"
            >юзеры и проекты</small
          >
        </h3>
        <a
          href="#"
          class="btn btn-xs btn-icon btn-light btn-hover-primary"
          id="kt_quick_actions_close"
        >
          <i class="ki ki-close icon-xs text-muted"></i>
        </a>
      </div>
      <!--end::Header-->
      <!--begin::Content-->
      <div class="offcanvas-content pr-5 mr-n5">
        <div class="row gutter-b">
          <!--begin::Item-->
          <div class="col-6 mb-5">
            <router-link
              to="/companies"
              class="btn btn-block btn-light btn-hover-primary text-dark-50 text-center py-10 px-5"
            >
              <span class="svg-icon svg-icon-3x svg-icon-primary m-0">
                <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Group.svg-->
                <inline-svg src="/media/svg/icons/Home/Building.svg" />
                <!--end::Svg Icon-->
              </span>
              <span class="d-block font-weight-bold font-size-h6 mt-2"
                >Компании</span
              >
            </router-link>
          </div>
          <!--end::Item-->
          <!--begin::Item-->
          <div class="col-6 mb-5">
            <router-link
              to="/projects"
              class="btn btn-block btn-light btn-hover-primary text-dark-50 text-center py-10 px-5"
            >
              <span class="svg-icon svg-icon-3x svg-icon-primary m-0">
                <!--begin::Svg Icon | path:assets/media/svg/icons/Shopping/Box2.svg-->
                <inline-svg src="/media/svg/icons/Shopping/Box2.svg" />
                <!--end::Svg Icon-->
              </span>
              <span class="d-block font-weight-bold font-size-h6 mt-2"
                >Проекты</span
              >
            </router-link>
          </div>
          <!--end::Item-->
          <!--begin::Item-->
          <div class="col-6 mb-5">
            <router-link
              to="/users"
              class="btn btn-block btn-light btn-hover-primary text-dark-50 text-center py-10 px-5"
            >
              <span class="svg-icon svg-icon-3x svg-icon-primary m-0">
                <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Group.svg-->
                <inline-svg src="/media/svg/icons/Communication/Group.svg" />
                <!--end::Svg Icon-->
              </span>
              <span class="d-block font-weight-bold font-size-h6 mt-2"
                >Пользователи</span
              >
            </router-link>
          </div>
          <!--end::Item-->
          <!--begin::Item-->
          <div class="col-6 mb-5">
            <router-link
                :to="{ name: 'support' }"
                class="btn btn-block btn-light btn-hover-primary text-dark-50 text-center py-10 px-5"
            >
              <span class="svg-icon svg-icon-3x svg-icon-primary m-0">
                <!--begin::Svg Icon | path:assets/media/svg/icons/Design/Color-profile.svg-->
                <inline-svg src="/media/svg/icons/General/Settings-2.svg" />
                <!--end::Svg Icon-->
              </span>
              <span class="d-block font-weight-bold font-size-h6 mt-2">
                Помощь
              </span>
            </router-link>
          </div>
          <!--end::Item-->
        </div>
      </div>
      <!--end::Content-->
    </div>
  </div>
</template>

<script>
import KTLayoutQuickUser from "../../../../assets/js/layout/extended/quick-actions.js";

export default {
  name: "KTQuickActions",
  mounted() {
    // Init Quick User Panel
    KTLayoutQuickUser.init(this.$refs["kt_quick_actions"]);
  },
  methods: {},
  computed: {}
};
</script>
