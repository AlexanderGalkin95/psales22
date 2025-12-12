<template>
  <div class="d-flex flex-row">
    <!--begin::Content-->
    <div class="flex-row-fluid">
       <!--begin::Row-->
       <div class="row">
            <div class="col-lg-6">
              <b-overlay :show="isTaskLoading">
                <TaskOverview
                  :total-duration="totalDuration"
                  :total-calls="totalCalls"
                  :processed="processed"
                  :processed-calls="processedCalls"
                />
              </b-overlay>
            </div>
            <div class="col-lg-6">
              <b-overlay :show="isTaskHistoryLoading">
                <TaskNotifications :history="loadedTaskHistory" />
              </b-overlay>
            </div>
          </div>
          <!--end::Row-->
          <b-overlay :show="isTaskLoading">
            <TaskCalls :calls="calls" />
          </b-overlay>
    </div>
    <!--end::Content-->
  </div>
</template>

<script>
import TaskNotifications from "./TaskNotifications.vue";
import TaskOverview from "./TaskOverview.vue";
import TaskCalls from './TaskCalls.vue';

export default {
  name: "TaskProfile",
  data() {
    return {
      isTaskLoading: false,
      isTaskHistoryLoading: false,
      loadedTask: null,
      loadedTaskHistory: [],
    };
  },
  components: {
    TaskOverview,
    TaskNotifications,
    TaskCalls,
  },
  computed: {
    totalDuration() {
      return this.loadedTask?.total_duration
    },
    totalCalls() {
      return this.loadedTask?.calls.length
    },
    processed() {
      return this.loadedTask?.processed
    },
    processedCalls() {
      return this.loadedTask?.processed_calls
    },
    calls() {
      return this.loadedTask?.calls.map(item => {
        item.audioIsPlaying = 0
        return item
      })
    }
  },
  mounted() {
    this.isTaskLoading = true
    this.isTaskHistoryLoading = true
    this.$http.get(`/api/tasks/${this.$route.params.taskId}`)
      .then(response => {
        if (response.data.status === 'success') {
          this.loadedTask = response.data.task
        }
        this.isTaskLoading = false
      })
    this.$http.get(`/api/tasks/${this.$route.params.taskId}/history`)
      .then(response => {
        this.loadedTaskHistory = response.data.history
        this.isTaskHistoryLoading = false
      })
  },
};
</script>
