<template>
  <!--begin::Advance Table Widget 10-->
  <div class="card card-custom gutter-b">
    <!--begin::Header-->
    <div class="card-header border-0 py-5">
      <h3 class="card-title align-items-start flex-column">
        <span class="card-label font-weight-bolder text-dark"
          >Звонки</span
        >
        <span class="text-muted mt-3 font-weight-bold font-size-sm"
          >Список звонков для оценки</span
        >
      </h3>
    </div>
    <!--end::Header-->
    <!--begin::Body-->
    <div class="card-body py-0">
      <!--begin::Table-->
      <div class="table-responsive">
        <table
          class="table table-head-custom table-vertical-center"
          id="kt_advance_table_widget_4"
        >
          <thead>
            <tr class="text-left">
              <th class="pl-0" style="min-width: 120px">ДАТА</th>
              <th style="min-width: 110px">ТИП</th>
              <th style="min-width: 110px">СОБЫТИЕ</th>
              <th style="min-width: 120px">ДЛИНА</th>
              <th style="min-width: 120px">СТАТУС</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, callIndex) in localCalls" :key="callIndex">
              <td class="pl-0">
                <a
                  href="#"
                  class="
                    text-dark-75
                    font-weight-bolder
                    text-hover-primary
                    font-size-lg
                  "
                >
                  <span
                    class="font-weight-bolder d-block font-size-lg"
                    >№ {{ item.call.id }}
                  </span>
                  <span class="text-muted font-weight-bold">{{ customDate(item.call.record_created_at) }}</span>
                </a>
              </td>
              <td>
                <span class="text-dark-75 font-weight-bolder d-block font-size-lg">
                  <span v-if="item.call.record_event_type === 'inbound'" class="mdi mdi-phone-incoming mdi-18px text-success"></span>
                  <span v-else class="mdi mdi-phone-outgoing mdi-18px text-muted"></span>
                </span>
              </td>
              <td>
                <span class="font-weight-normal d-block font-size-lg">
                  {{
                    item.call.record_event_type === 'outbound'
                      ? `${item.call.record_responsible_name} на ${item.call.record_element_name}`
                      : `${item.call.record_element_name} на ${item.call.record_responsible_name}`
                  }}
                </span>
              </td>
              <td>
                <span class="text-dark-75 font-weight-normal d-block font-size-lg">
                  {{ item.call.record_duration }}
                </span>
              </td>
              <td class="pr-0 text-right">
                <template v-if="item.call.status">
                  <span
                    class="label label-lg label-inline"
                    v-bind:class="`label-light-${item.class}`"
                  >Оценено
                  </span>
                </template>
                <template v-else>
                  <a
                    :id="`audio_${callIndex}`"
                    href="#"
                    class="btn btn-icon btn-light btn-hover-primary btn-sm"
                    @click="audioSelected(`audio_${callIndex}`, item)"
                  >
                    <span class="svg-icon svg-icon-md svg-icon-primary">
                      <inline-svg
                        v-if="item.audioIsPlaying"
                        src="/media/svg/icons/Media/Pause.svg"
                      ></inline-svg>
                      <inline-svg
                        v-else
                        src="/media/svg/icons/Media/Play.svg"
                      ></inline-svg>
                    </span>
                  </a>
                  <a
                    href="#"
                    class="btn btn-icon btn-light btn-hover-primary btn-sm mx-3"
                    @click="openRatingModal(item)"
                  >
                    <span class="svg-icon svg-icon-md svg-icon-primary">
                      <inline-svg
                        src="/media/svg/icons/General/Like.svg"
                      ></inline-svg>
                    </span>
                  </a>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!--end::Table-->
    </div>
    <b-popover
      title=""
      custom-class="audio-popover"
      :show="popover"
      :target="audioTarget"
    >
      <audio-player
        :src="audioSource"
        autoplay
        style="width: 400px;"
        @audio-play="handleAudioPlay(1)"
        @audio-paused="handleAudioPlay(0)"
      />
    </b-popover>

    <call-rating
      v-if="selectedItem"
      :project-id="selectedItem.project_id"
      :item="selectedItem.call"
      @saved="handleSaved"
      @closed="selectedItem = null"
    />
    <!--end::Body-->
  </div>
  <!--end::Advance Table Widget 10-->
</template>
  
<script>
import srx from "@/functions";
import AudioPlayer from "@/components/vue-audio/components/player.vue"
import CallRating from "@/components/CallRating.vue"
import { cloneDeep } from 'lodash'

export default {
  name: "TaskCalls",
  components: { AudioPlayer, CallRating },
  props: {
    calls: {
      type: Array,
      default: () => ([])
    }
  },
  data() {
    return {
      popover: false,
      audioItem: null,
      audioTarget: '',
      audioSource: '',
      selectedItem: null,
      refreshTable: '',
      localCalls: [],
    };
  },
  watch: {
    calls: {
      handler(newVal) {
        this.localCalls = cloneDeep(newVal)
      }
    }
  },
  mounted() {
    this.localCalls = cloneDeep(this.calls)
  },
  methods: {
    handleAudioPlay(value) {
      const elem = this.localCalls.find(item => item.id === this.audioItem?.id)
      if (elem) elem.audioIsPlaying = value
    },
    customDate(date) {
      return srx.customDate(date)
    },
    async audioSelected(elementId, taskCall) {
      this.popover = false
      if (this.audioItem?.id === taskCall.id) {
        taskCall.audioIsPlaying = 0
        this.audioItem = null
        this.audioTarget = ''
        this.audioSource = ''
        return
      }
      let audioTarget = elementId
      if (audioTarget !== this.audioTarget) {
        if (this.audioItem) this.audioItem.audioIsPlaying = 0
        if (taskCall.call['record_source'] === 'Bitrix24') {
          await this.$http.get(`/api/projects/${taskCall.call['project_id']}/record/${taskCall.call['id']}?no_download=1`)
              .then(response => {
                taskCall.call['record_link'] = response.data.link
              })
        }
        this.audioItem = taskCall
        this.audioTarget = audioTarget
        this.audioSource = taskCall.call.record_link
        setTimeout(() => {
          taskCall.audioIsPlaying = 1
          this.popover = true
        }, 500)
      } else {
        this.audioItem = null
        this.audioTarget = ''
      }
    },
    openRatingModal(item) {
      this.popover = false
      item.audioIsPlaying = 0
      this.selectedItem = item
    },
    handleSaved() {
      this.refreshTable = Math.random().toString(36).substring(2, 15)
    },
  },
};
</script>
  
<style lang="scss" scoped>
.audio-popover {
  max-width: unset!important;
}
</style>