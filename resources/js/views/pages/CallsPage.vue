<script>
import Table from '../../components/table/Table'
import AudioPlayer from "../../components/vue-audio/components/player"
import CallRating from "../../components/CallRating.vue"
import Swal from "sweetalert2"
import _ from 'lodash'
import { mapGetters } from "vuex"

export default {
  name: "CallsPage",
  components: {
    CallRating,
    AudioPlayer,
    Table
  },
  data() {
    return {
      columns: [
        { name: 'record_created_at', title: 'ДАТА ЗВОНКА', format: 'datetime', range: false, width: '20%', sortable: true, searchable: true, align: 'left' },
        { name: 'record_event_type', title: 'ТИП', format: 'text', search_dict: 'call_types', width: '5%', breakpoints: 'md', sortable: true, searchable: true, align: 'center',
          display_callback: function (col, item, $this) {
            return item['record_event_type'] === 'inbound'
                ? '<span class="mdi mdi-phone-incoming mdi-18px text-success"></span>'
                : '<span class="mdi mdi-phone-outgoing mdi-18px text-muted"></span>'
          },
        },
        { name: 'event', title: 'СОБЫТИЕ', format: 'text', width: '40%', sortable: true, searchable: true, align: 'left', search_dict: 'responsible_persons',
          display_callback: function (col, item) {
              return item.event
          }
        },
        { name: 'result', title: 'РЕЗУЛЬТАТ', format: 'text', width: '20%', breakpoints: 'sm', align: 'left' },
        { name: 'record_duration', title: 'ДЛИНА', format: 'longtime', range: false, width: '10%', breakpoints: 'md', sortable: true, searchable: true, align: 'left' },
        { name: 'status', title: 'СТАТУС', format: 'text', width: '10%', search_dict: 'statuses', sortable: true, searchable: true, align: 'center',
          display_callback: function (col, item, $this) {
            if (!item[col.name]) {
              return `<span class="fa fa-times text-muted" style="font-size: 1.25rem;"></span>`
            } else {
              return '<span class="fa fa-check text-success" style="font-size: 1.25rem;"></span>'
            }
          }
        },
        { type: 'buttons', align: 'left', title: '', width: '5%',
          items: [
            { type: 'button', class: 'btn-outline-primary', id: 'listen', icon: 'fa-play', tooltip: 'Прослушать запись звонка', width: '1%', show: 'audioIsPlaying = 0' },
            { type: 'button', class: 'btn-outline-primary', id: 'listen', icon: 'fa-stop', tooltip: 'Остановить', width: '1%', show: 'audioIsPlaying = 1' },
            { type: 'button', class: 'btn-secondary', id: 'download', icon: 'fa-download', tooltip: 'Скачать запись', width: '1%' },
            { type: 'button', class: 'btn-warning', id: 'rating', icon: 'fa-thumbs-up', tooltip: 'Оценить звонок', width: '1%', show: 'status = 0' },
            // rerating disabled { type: 'button', class: 'btn-danger', id: 'rerating', icon: 'fa-thumbs-up', tooltip: 'Переоценить звонок', width: '1%', show: 'status = 1' },
          ]
        },
      ],
      filters: {
        'record_created_at': null,
        'record_duration': null,
        'record_event_type': '',
        'event': '',
        'result': '',
        'status': '',
      },
      settings: {
        download_callback(col, item, $this, event) {
          $this.$emit('audio-download', event, item)
        },
        listen_callback(col, item, $this, event) {
          $this.$emit('audio-selected', event, item)
        },
        rating_callback(col, item, $this) {
          $this.$emit('open-rating-modal', item)
        },
        // rerating disabled
        // rerating_callback(col, item, $this) {
        //   $this.$emit('open-rating-modal', item)
        // },
      },
      dicts: {
        call_types: [
          { label: 'Исходящие', value: 'outbound' },
          { label: 'Входящие', value: 'inbound' },
        ],
        statuses: [
          { label: 'Оценено', value: 1 },
          { label: 'Не оценено', value: 0 },
        ],
        results: [],
        responsible_persons: []
      },
      projectId: null,
      isLoading: false,
      popover: false,
      audioItem: {},
      audioTarget: '',
      audioSource: '',
      selectedItem: null,
      refreshTable: '',
    }
  },
  computed: {
    ...mapGetters([
      'getProject',
    ]),
    callStatuses() {
      return this.getProject?.call_settings?.statuses || []
    },
  },
  watch: {
    callStatuses: {
      handler(newVal) {
        if (newVal.length > 1) {
          this.$http.get(`/api/dictionaries/call_statuses?projectId=${this.projectId}`).then(response => {
            if (response.status === 200) {
              this.dicts.results = _.filter(
                  response.data.call_statuses,
                  type => this.callStatuses.includes(type.system_name)
              )
              let columns = _.cloneDeep(this.columns)
              _.each(columns, col => {
                if (col.name === 'result') {
                  col.searchable = true
                  col.search_dict = 'results'
                }
              })
              this.columns = columns
            }
          })
        }
      },
      immediate: true,
    },
  },
  async mounted() {
    this.projectId = Number(this.$route.params.projectId)
    if (!this.projectId) {
      return
    }
    await this.$store.dispatch('LOAD_PROJECT', this.projectId)
    await this.loadResponsiblePersons(this.projectId)
    this.$store.dispatch('LOAD_CALL_TYPES', this.projectId)
    this.$store.dispatch('LOAD_HEAT_TYPES')
    this.$store.dispatch('LOAD_SETTINGS', this.projectId)
  },
  methods: {
    fetchCallback(items) {
      const payload = _.map(items, (item) => {
        return {
          ...item,
          ...item.call
        }
      })
      _.each(payload, item => {
        item.audioIsPlaying = 0
      })
      return payload
    },
    audioDownload(event, item) {
      item.audioIsPlaying = 0
      this.popover = false
      this.$http.get(`/api/projects/${item.project_id}/record/${item.id}`)
          .then(response => {
            item.record_link = response.data.link
            item.download_link = response.data.link
            let filename = item.record_link.substring(item.record_link.lastIndexOf("/") + 1).split("?")[0]
            if (item.record_source === 'Bitrix24') {
              filename = response.data.filename
              item.download_link = response.data.download_link
            }
            let xhr = new XMLHttpRequest()
            xhr.responseType = 'blob'
            xhr.onload = function() {
              let a = document.createElement('a')
              a.href = window.URL.createObjectURL(xhr.response)
              a.download = filename
              a.style.display = 'none'
              document.body.appendChild(a)
              a.click()
              a.remove()
            }
            xhr.open('GET', item.download_link);
            xhr.send()
          }, () => {
            Swal.fire({
              title: "",
              html: 'Запись не найдена!',
              icon: "error",
              showConfirmButton: true,
              timer: 3000,
            });
          })
    },
    async audioSelected(event, item) {
      this.popover = false
      if (this.audioItem.id === item.id) {
        this.audioItem = {}
        this.audioTarget = ''
        this.audioSource = ''
        return
      }
      let audioTarget = event.target.closest('span').id
      if (audioTarget !== this.audioTarget) {
        let filename = item.record_link.substring(item.record_link.lastIndexOf("/") + 1).split("?")[0]
        if (item.record_source === 'Bitrix24') {
          await this.$http.get(`/api/projects/${item.project_id}/record/${item.id}?no_download=1`)
              .then(response => {
                this.audioSource = response.data.link
                if (item.record_source === 'Bitrix24') {
                  filename = response.data.filename
                }
                item.filename = filename
              })
              .catch(() => {
                Swal.fire({
                  title: "",
                  html: 'Запись не найдена!',
                  icon: "error",
                  showConfirmButton: true,
                  timer: 3000,
                });
                this.audioSource = ''
              })
          if (!this.audioSource) return false
        } else {
          this.audioSource = item.record_link
        }

        this.audioItem = item
        this.audioTarget = audioTarget
        setTimeout(() => {
          this.popover = true
        }, 500)
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
    errorHandler(error) {
      if (error.status === 422) {
        Swal.fire({
          title: "",
          html: `Проект с таким идентификатором {${this.projectId}} не найден!`,
          icon: "error",
          showConfirmButton: true,
          timer: 3000,
        });
      } else {
        Swal.fire({
          title: "",
          html: 'Что-то пошло не так!',
          icon: "error",
          showConfirmButton: true,
          timer: 3000,
        });
      }
    },
    async loadResponsiblePersons (projectId) {
      axios.get(`/api/projects/${projectId}/responsible-persons`)
        .then(response => {
          this.dicts.responsible_persons = response.data.responsible_persons.map(person => {
            return {
              label: person.record_responsible_name,
              value: person.record_responsible_id
            }
          })
        })
        .catch(e => { throw new Error(e) })
      }
  },
}
</script>

<template>
  <div class="row m-0">
    <div style="margin: 0 0 15px 19px;">
      <i class="mdi mdi-phone-classic"
         aria-hidden="true"
         style="font-size: 21px;vertical-align: sub;">
      </i>
      <div style="display: inline-block;">
        <h4>Звонки</h4>
      </div>
    </div>
    <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div v-show="isLoading" class="card">
        <div class="card-body loading" style="padding-top:110px;min-height:350px;">
          <span class="fa fa-spinner fa-3x fa-spin" style="position: absolute; top: 99px;left: 50%;"></span>
        </div>
      </div>
      <b-card class="card-custom" no-body>
        <b-card-body>
          <Table
            id="project__calls"
            :data_src="`/api/projects/${projectId}/calls`"
            data_prop_name="calls"
            :columns="columns"
            :filters="filters"
            :dicts="dicts"
            :settings="settings"
            :reload="refreshTable"
            :is-save-event-filter="false"
            :fetch-callback="fetchCallback"
            @audio-selected="audioSelected"
            @audio-download="audioDownload"
            @open-rating-modal="openRatingModal"
            @error="errorHandler"
          ></Table>
        </b-card-body>
        <b-card-footer class="text-left">
          <router-link tag="a" class="btn btn-light"
                    :to="{ name: 'projects.list' }">
            <i class="fa fa-arrow-left"></i>
            Список проектов
          </router-link>
        </b-card-footer>
      </b-card>
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
          @audio-play="'fa-pause'"
          @audio-paused="'fa-play'"
          @audio-ended="'fa-play'"
        />
      </b-popover>
      <call-rating
        v-if="selectedItem"
        :project-id="projectId"
        :item="selectedItem"
        @saved="handleSaved"
        @closed="selectedItem = null"
      />
    </article>
  </div>
</template>

<style scoped>
.audio-popover {
  max-width: unset!important;
}
</style>
