<template>
    <div
      class="card card-custom gutter-b"
    >
      <!--begin::Body-->
      <div class="card-body d-flex flex-column p-0">
        <!--begin::Stats-->
        <div class="card-spacer bg-white card-rounded flex-grow-1">
          <!--begin::Row-->
          <div class="row m-0">
            <div class="col px-8 py-6 mr-8">
              <div class="font-size-sm text-muted font-weight-bold">
                Общий объем (мин)
              </div>
              <div class="font-size-h4 font-weight-bolder">
                {{ totalDuration }}
              </div>
            </div>
            <div class="col px-8 py-6">
              <div class="font-size-sm text-muted font-weight-bold">
                Общий объем (звонки)
              </div>
              <div class="font-size-h4 font-weight-bolder">
                {{ totalCalls }}
              </div>
            </div>
          </div>
          <!--end::Row-->
          <!--begin::Row-->
          <div class="row m-0">
            <div class="col px-8 py-6 mr-8">
              <div class="font-size-sm text-muted font-weight-bold">
                Отработанные минуты
              </div>
              <div class="font-size-h4 font-weight-bolder">
                {{ processed }}
              </div>
            </div>
            <div class="col px-8 py-6">
              <div class="font-size-sm text-muted font-weight-bold">
                Прослушанные звонки
              </div>
              <div class="font-size-h4 font-weight-bolder">
                {{ processedCalls }}
              </div>
            </div>
          </div>
          <!--end::Row-->
          <!--begin::Row-->
          <div class="row m-0">
            <div class="col px-8 py-6 mr-8">
              <div class="font-size-sm text-muted font-weight-bold">
                Оставшиеся минуты
              </div>
              <div class="font-size-h4 font-weight-bolder text-primary">
                {{ totalDuration - processed }}
              </div>
            </div>
            <div class="col px-8 py-6">
              <div class="font-size-sm text-muted font-weight-bold">
                Оставшиеся звонки
              </div>
              <div class="font-size-h4 font-weight-bolder text-primary">
                {{ totalCalls - processedCalls }}
              </div>
            </div>
          </div>
          <!--end::Row-->
        </div>
        <!--end::Stats-->
      </div>
      <!--end::Body-->
    </div>
  </template>
  
  <script>  
  export default {
    name: "TaskOverview",
    props: {
      totalDuration: {
        type: Number,
        default: 0
      },
      totalCalls: {
        type: Number,
        default: 0
      },
      processed: {
        type: Number,
        default: 0
      },
      processedCalls: {
        type: Number,
        default: 0
      },
              
    },
  }
  </script>
  