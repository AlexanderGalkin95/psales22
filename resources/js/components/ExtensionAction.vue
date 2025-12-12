<script>
import VSelect from './vue-select'
export default {
    name: "ExtensionAction",
    components: {
        VSelect
    },
    props: {
        item: {
            type: Object,
            default: () => {},
            required: true
        }
    },
    data() {
        return {
            selectedAction: null,
            busy: false,
        }
    },
  computed: {
    options () {
      switch (this.item.extension_state) {
        case 'active':
          this.selectedAction = 'block'
          return [
            { value: 'block', label: 'Заблокировать' },
            { value: 'reset', label: 'Удалить' },
          ]
        case 'blocked':
          this.selectedAction = 'unblock'
          return [
            { value: 'unblock', label: 'Разблокировать' },
            { value: 'reset', label: 'Удалить' },
          ]
        default:
          return []
      }
    },
  },
    methods: {
        send () {
            this.$refs.extensionAction.validate().then(result => {
                if (result) {
                  this.busy = true
                  if (this.selectedAction === 'reset') {
                    axios.delete(`/api/extension/${this.item.extension_id}/action/reset`)
                        .then(response => {
                          this.busy = false
                          this.$bvModal.hide('extension-action')
                          this.$emit('success', response.data)
                        }, error => {
                          this.busy = false
                          this.$emit('error', error)
                          srx.bootboxAlert(error.response.data.message)
                        })
                  } else {
                    axios.put(`/api/extension/${this.item.extension_id}/action/${this.selectedAction}`)
                        .then(response => {
                          this.busy = false
                          this.$bvModal.hide('extension-action')
                          this.$emit('success', response.data)
                        }, error => {
                          this.busy = false
                          this.$emit('error', error)
                          srx.bootboxAlert(error.response.data.message)
                        })
                  }
                }
            })
        }
    }
}
</script>

<template>
    <b-modal id="extension-action"
             title="Действие над расширением"
             ok-title="Сохранить"
             size="sm"
             ok-only
             centered
             :busy="busy"
             @hidden="selectedAction = null"
             @ok.prevent="send"
    >
        <validation-observer ref="extensionAction" tag="form" v-slot="{ invalid }">
            <v-select id="v-select-extension-action"
                      v-model="selectedAction"
                      name="Действие"
                      rules="required"
                      :clearable="true"
                      :reduce="(o) => o.value"
                      :selectable="(o) => o.disabled !== true"
                      :options="options"
            />
        </validation-observer>
    </b-modal>
</template>

<style scoped>

</style>