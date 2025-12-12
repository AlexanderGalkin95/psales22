<script>
import Table from "../../components/table/Table.vue";
import {mapGetters} from "vuex";

export default {
    name: "LogEvents",
    components: { Table },
    data() {
        return {
            settings: {
                global_search: false
            },
            filters: {},
            dicts: {},
            loaded: true,
        };
    },
    computed: {
        ...mapGetters(["currentUser"]),
        current_user() {
            return this.currentUser;
        },
        user_role() {
            return this.current_user.role_name;
        },
        columns() {
            let $this = this;
            return [
                {
                    name: "id",
                    title: "ID",
                    sortable: false,
                },
                {
                    name: "type_name",
                    title: "Тип",
                    format: "text",
                    align: "left",
                    sortable: false,
                },
                {
                    name: "description",
                    title: "Описание",
                    format: "text",
                    align: "left",
                    sortable: false,
                },
                // {
                //     name: "user_name",
                //     title: "Пользователь",
                //     format: "text",
                //     breakpoints: "xs",
                //     align: "left",
                //     sortable: false,
                // },
                {
                    name: "created_at",
                    title: "Дата",
                    format: "text",
                    breakpoints: "",
                    align: "left",
                    sortable: false,
                },
            ];
        },
    },
    created() {

    },
    methods: {
        setData: function (items) {
            this.loaded = true;
        },
    },
};
</script>
<template>
    <div class="row m-0">
        <div style="margin: 0 0 15px 19px">
            <div style="display: inline-block">
                <h3>
                    <i class="flaticon-analytics" aria-hidden="true"></i> Лог событий
                </h3>
            </div>
        </div>
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div v-show="!loaded" v-if="!loaded" class="card card-custom">
                <div
                    class="card-body loading"
                    style="padding-top: 110px; min-height: 350px"
                >
          <span
              class="fa fa-spinner fa-3x fa-spin"
              style="position: absolute; top: 99px; left: 50%"
          ></span>
                </div>
            </div>
            <div v-show="loaded" class="card card-custom">
                <div>
                    <div class="card-body">
                        <Table
                            id="call-ratings-log"
                            data_prop_name="data"
                            :data_src="'/api/log-events'"
                            :columns="columns"
                            :filters="filters"
                            :settings="settings"
                            :dicts="dicts"
                            @loaded="setData"
                        />
                    </div>
                </div>
            </div>
        </article>
    </div>
</template>
<style scoped>
</style>
