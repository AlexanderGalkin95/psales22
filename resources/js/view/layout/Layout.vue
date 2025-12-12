<template>
    <div class="d-flex flex-column flex-root">
        <iframe
            :src="'/blank?' + hash"
            style="height: 1px; width: 1px; border:none; position:absolute"
            onload="{
                if (String(this.contentWindow.location).includes('login')) {
                    window.location.reload();
                }
            }"
        ></iframe>
        <!-- begin:: Header Mobile -->
        <KTHeaderMobile></KTHeaderMobile>
        <!-- end:: Header Mobile -->

        <Loader v-if="loaderEnabled" v-bind:logo="loaderLogo"></Loader>

        <div class="d-flex flex-row flex-column-fluid page">
            <div id="kt_wrapper" class="d-flex flex-column flex-row-fluid wrapper">
                <!-- begin:: Header -->
                <KTHeader></KTHeader>
                <!-- end:: Header -->
                <div id="sessionExpired" class="modal fade">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title"><i class="fa fa-exclamation-triangle" aria-hidden="true"
                                        style="color:red"></i> Внимание!</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"
                                    @click="prolongSession">×</button>

                            </div>
                            <div class="modal-body" style="text-align: center">
                                <span v-if="sessionTimeout > 0">
                                    Ваша сессия истекает через {{ sessionTimeout }} секунд.<br />
                                    Если вы хотите остаться в системе, то нажмите кнопку "Остаться в системе".<br />
                                </span>
                                <span v-else class="text-danger">
                                    Ваша сессия истекла!
                                </span>
                                <form action="/logout" method="post"></form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-dismiss="modal"
                                    @click="prolongSession">{{ sessionTimeout > 0 ? 'Остаться в системе' : 'Закрыть' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- begin:: Content -->
                <div id="kt_content" class="content d-flex flex-column flex-column-fluid">
                    <!-- begin:: Content Body -->
                    <div class="d-flex flex-column-fluid">
                        <div :class="{
                                'container-fluid': contentFluid,
                                container: !contentFluid
                            }">
                            <div class="d-lg-flex flex-row-fluid">
                                <!-- begin:: Aside Left -->
                                <KTAside v-if="asideEnabled"></KTAside>
                                <!-- end:: Aside Left -->
                                <div class="content-wrapper flex-row-fluid">
                                    <transition name="fade-in-up">
                                        <router-view />
                                    </transition>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <KTFooter></KTFooter>
            </div>
        </div>

        <KTScrollTop></KTScrollTop>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import KTAside from "../../view/layout/aside/Aside.vue";
import KTHeader from "../../view/layout/header/Header.vue";
import KTHeaderMobile from "../../view/layout/header/HeaderMobile.vue";
import KTFooter from "../../view/layout/footer/Footer.vue";
import HtmlClass from "../../core/services/htmlclass.service";
import KTStickyToolbar from "../../view/layout/extras/StickyToolbar.vue";
import KTScrollTop from "../../view/layout/extras/ScrollTop";
import Loader from "../../view/content/Loader.vue";
import {
    ADD_BODY_CLASSNAME,
    REMOVE_BODY_CLASSNAME
} from "../../core/services/store/htmlclass.module.js";
import router from "../../router";
import store from "../../core/services/store/index";
import moment from "moment";

export default {
    name: "Layout",
    components: {
        KTAside,
        KTHeader,
        KTHeaderMobile,
        KTFooter,
        KTStickyToolbar,
        KTScrollTop,
        Loader
    },
    data() {
        return {
            hash: '',
            sessionTimeout: 60,
            sessionLifetime: 60,
            sessionTick: null,
            sessionCountDown: null,
        }
    },
    beforeMount() {
        // initialize html element classes
        HtmlClass.init(this.layoutConfig());
    },
    beforeRouteEnter(to, from, next) {
        if (!from.name) {
            // show page loading when loading the page for the first time
            store.dispatch(ADD_BODY_CLASSNAME, "page-loading")
        }
        next()
    },
    mounted() {
        router.beforeEach((to, from, next) => {
            this.hash = Math.random().toString().slice(2, 11);
            this.updateSession();
            next();
        });
        this.updateSession();

        // Simulate the delay page loading
        setTimeout(() => {
            // Remove page loader after some time
            this.$store.dispatch(REMOVE_BODY_CLASSNAME, "page-loading");
        }, 500);
    },
    methods: {
        addMinutes(date, minutes) {
            date.setMinutes(date.getMinutes() + minutes);

            return date;
        },
        updateSession() {
            clearInterval(this.sessionTick);
            clearInterval(this.sessionCountDown);

            this.sessionTimeout = (window.sessionLifeTime || 1) * 60;
            this.sessionLifetime = this.addMinutes(new Date(), this.sessionTimeout / 60).getTime();

            localStorage.setItem('__session_lifetime', this.sessionLifetime);
            
            this.refreshSessionTick();
        },
        refreshSessionTick() {
            clearInterval(this.sessionTick);
            this.sessionTick = setInterval(() => {
                this.sessionTimeout--
                if (this.isSessionExpiring()) {
                    clearInterval(this.sessionTick);
                    this.handleExpiringSession();
                }
            }, 1000);
        },
        isSessionExpiring() {
            return moment(this.sessionLifetime).diff(new Date(), 'second') <= 60;
        },
        handleExpiringSession() {
            $("#sessionExpired").modal({ backdrop: 'static', keyboard: false });
            this.countDown();
        },
        countDown() {
            clearInterval(this.sessionCountDown);
            this.sessionCountDown = setInterval(() => {
                if (this.sessionTimeout <= 0) {
                    // $("#sessionExpired").modal('hide');
                    clearInterval(this.sessionCountDown);
                }
                this.sessionTimeout--;
            }, 1000);

        },
        prolongSession() {
            //перегенерация /blank?.....
            this.hash = Math.random().toString().slice(2, 11);
            this.updateSession();
        },
        footerLayout(type) {
            return this.layoutConfig("footer.layout") === type;
        }
    },
    computed: {
        ...mapGetters([
            "isAuthenticated",
            "breadcrumbs",
            "pageTitle",
            "layoutConfig"
        ]),

        /**
         * Check if the page loader is enabled
         * @returns {boolean}
         */
        loaderEnabled() {
            return !/false/.test(this.layoutConfig("loader.type"));
        },

        /**
         * Check if container width is fluid
         * @returns {boolean}
         */
        contentFluid() {
            return this.layoutConfig("content.width") === "fluid";
        },

        /**
         * Page loader logo image using require() function
         * @returns {string}
         */
        loaderLogo() {
            return '<span class="pnsch">Pinscher</span> <span class="sales">Sales</span>';
        },

        /**
         * Check if the left aside menu is enabled
         * @returns {boolean}
         */
        asideEnabled() {
            return !!this.layoutConfig("aside.self.display");
        },

        /**
         * Set the right toolbar display
         * @returns {boolean}
         */
        toolbarDisplay() {
            // return !!this.layoutConfig("toolbar.display");
            return true;
        },

        /**
         * Set the subheader display
         * @returns {boolean}
         */
        subheaderDisplay() {
            return !!this.layoutConfig("subheader.display");
        },
    }
};
</script>
<style>
.pnsch {
    color: #F64E60;
    font-size: 40px;
    font-weight: bold;
}

.sales {
    color: #aaaaaa;
    font-size: 40px;
    font-weight: bold;
}
</style>
