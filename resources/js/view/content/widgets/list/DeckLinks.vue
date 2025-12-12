<template>
  <div
    class="card card-custom card-shadowless gutter-b card-stretch card-shadowless p-0"
  >
    <!--begin::Nav Tabs-->
    <ul
      class="dashboard-tabs nav nav-pills nav-danger row row-paddingless m-0 p-0 flex-column flex-sm-row"
      role="tablist"
    >
      <!--begin::Item-->
      <li
        class="nav-item d-flex col flex-grow-1 flex-shrink-0 mr-3 mb-3 mb-lg-0"
      >
        <router-link
          class="nav-link border py-10 d-flex flex-grow-1 rounded flex-column align-items-center"
          to="/companies"
        >
          <span class="nav-icon py-2 w-auto">
            <span class="svg-icon svg-icon-3x">
              <!--begin::Svg Icon | path:assets/media/svg/icons/Home/Library.svg-->
               <inline-svg src="/media/svg/icons/Home/Building.svg" />
              <!--end::Svg Icon-->
            </span>
          </span>
          <span class="nav-text font-size-lg py-2 font-weight-bold text-center">
            Компании
          </span>
        </router-link>
      </li>
      <!--end::Item-->
      <!--begin::Item-->
      <li
        class="nav-item d-flex col flex-grow-1 flex-shrink-0 mr-3 mb-3 mb-lg-0"
      >
        <router-link
          class="nav-link border py-10 d-flex flex-grow-1 rounded flex-column align-items-center"
          to="/projects"
        >
          <span class="nav-icon py-2 w-auto">
            <span class="svg-icon svg-icon-3x">
              <!--begin::Svg Icon | path:assets/media/svg/icons/Home/Library.svg-->
               <inline-svg src="/media/svg/icons/Shopping/Box2.svg" />
              <!--end::Svg Icon-->
            </span>
          </span>
          <span class="nav-text font-size-lg py-2 font-weight-bold text-center"
            >Проекты</span
          >
        </router-link>
      </li>
      <!--end::Item-->
      <!--begin::Item-->
      <li
        class="nav-item d-flex col flex-grow-1 flex-shrink-0 mr-3 mb-3 mb-lg-0"
      >
        <router-link
          class="nav-link border py-10 d-flex flex-grow-1 rounded flex-column align-items-center"
          to="/users"
        >
          <span class="nav-icon py-2 w-auto">
            <span class="svg-icon svg-icon-3x">
              <!--begin::Svg Icon | path:assets/media/svg/icons/Layout/Layout-4-blocks.svg-->
               <inline-svg src="/media/svg/icons/Communication/Group.svg" />
              <!--end::Svg Icon-->
            </span>
          </span>
          <span
            class="nav-text font-size-lg py-2 font-weight-bolder text-center"
            >Пользователи</span
          >
        </router-link>
      </li>
      <!--end::Item-->
      <!--begin::Item-->
      <li
        class="nav-item d-flex col flex-grow-1 flex-shrink-0 mr-3 mb-3 mb-lg-0"
      >
        <router-link
          class="nav-link border py-10 d-flex flex-grow-1 rounded flex-column align-items-center"
          to="/daily-reports"
        >
          <span class="nav-icon py-2 w-auto">
            <span class="svg-icon svg-icon-3x">
              <!--begin::Svg Icon | path:assets/media/svg/icons/Home/Library.svg-->
               <inline-svg src="/media/svg/icons/Shopping/Box2.svg" />
              <!--end::Svg Icon-->
            </span>
          </span>
          <span
            class="nav-text font-size-lg py-2 font-weight-bolder text-center"
            >Проекты для ЕО</span
          >
        </router-link>
      </li>
      <!--end::Item-->
    </ul>
    <!--end::Nav Tabs-->
    <!--begin::Nav Content-->
    <div class="tab-content m-0 p-0">
      <div
        class="tab-pane active"
        id="forms_widget_tab_1"
        role="tabpanel"
      ></div>
      <div class="tab-pane" id="forms_widget_tab_2" role="tabpanel"></div>
      <div class="tab-pane" id="forms_widget_tab_3" role="tabpanel"></div>
      <div class="tab-pane" id="forms_widget_tab_4" role="tabpanel"></div>
      <div class="tab-pane" id="forms_widget_tab_6" role="tabpanel"></div>
    </div>
    <!--end::Nav Content-->
  </div>
</template>

<script>
export default {
  name: "DeckLinks",
};
</script>
