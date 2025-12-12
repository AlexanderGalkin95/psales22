<template>
    <div class="topbar-item mr-4">
        <div
            class="btn btn-icon btn-sm btn-clean btn-text-dark-75"
            id="kt_quick_user_toggle"
        >
      <span class="svg-icon svg-icon-lg">
        <inline-svg src="/media/svg/icons/General/User.svg"/>
      </span>
        </div>

        <div
            id="kt_quick_user"
            ref="kt_quick_user"
            class="offcanvas offcanvas-right p-10"
        >
            <!--begin::Header-->
            <div
                class="offcanvas-header d-flex align-items-center justify-content-between pb-5"
            >
                <h3 class="font-weight-bold m-0">
                    Профиль
                </h3>
                <a
                    href="#"
                    class="btn btn-xs btn-icon btn-light btn-hover-primary"
                    id="kt_quick_user_close"
                >
                    <i class="ki ki-close icon-xs text-muted"></i>
                </a>
            </div>
            <!--end::Header-->

            <!--begin::Content-->
            <perfect-scrollbar
                class="offcanvas-content pr-5 mr-n5 scroll"
                style="max-height: 90vh; position: relative;"
            >
                <!--begin::Header-->
                <div v-if="user" class="d-flex align-items-center mt-5">
                    <div class="symbol symbol-100 mr-5">
                        <img class="symbol-label" :src="picture" alt=""/>
                        <i class="symbol-badge bg-success"></i>
                    </div>
                    <div class="d-flex flex-column">
                        <a
                            href="#"
                            class="font-weight-bold font-size-h5 text-dark-75 text-hover-primary"
                        >
                            {{ user.name }}
                        </a>
                        <div class="text-muted mt-1">{{ user.role }}</div>
                        <div class="navi mt-2">
                            <a href="#" class="navi-item">
                <span class="navi-link p-0 pb-2">
                  <span class="navi-icon mr-1">
                    <span class="svg-icon svg-icon-lg svg-icon-primary">
                      <!--begin::Svg Icon-->
                      <inline-svg
                          src="/media/svg/icons/Communication/Mail-notification.svg"
                      />
                        <!--end::Svg Icon-->
                    </span>
                  </span>
                  <span class="navi-text text-muted text-hover-primary">
                    {{ user.email }}
                  </span>
                </span>
                            </a>
                        </div>
                        <button class="btn btn-light-primary btn-bold" @click="onLogout">
                            Выйти
                        </button>
                        <!--              <a class="btn btn-light-primary btn-bold" href="{{ url('/logout') }}">-->
                        <!--                  Выйти-->
                        <!--              </a>-->
                    </div>
                </div>
                <!--end::Header-->
                <div class="separator separator-dashed mt-8 mb-5"></div>
                <!--begin::Nav-->
            </perfect-scrollbar>
            <!--end::Content-->
        </div>
    </div>
</template>

<style lang="scss" scoped>
#kt_quick_user {
    overflow: hidden;
}
</style>

<script>
import {LOGOUT, SET_AUTH} from "../../../../core/services/store/auth.module";
import KTLayoutQuickUser from "../../../../assets/js/layout/extended/quick-user.js";
import KTOffcanvas from "../../../../assets/js/components/offcanvas.js";
import {mapGetters} from "vuex";

export default {
    name: "KTQuickUser",
    data() {
        return {
            list: [
                {
                    title: "Another purpose persuade",
                    desc: "Due in 2 Days",
                    alt: "+28%",
                    svg: "/media/svg/icons/Home/Library.svg",
                    type: "warning"
                },
                {
                    title: "Would be to people",
                    desc: "Due in 2 Days",
                    alt: "+50%",
                    svg: "/media/svg/icons/Communication/Write.svg",
                    type: "success"
                },
                {
                    title: "Purpose would be to persuade",
                    desc: "Due in 2 Days",
                    alt: "-27%",
                    svg: "/media/svg/icons/Communication/Group-chat.svg",
                    type: "danger"
                },
                {
                    title: "The best product",
                    desc: "Due in 2 Days",
                    alt: "+8%",
                    svg: "/media/svg/icons/General/Attachment2.svg",
                    type: "info"
                }
            ],
            user: null
        };
    },
    mounted() {
        //Init Quick User Panel
        KTLayoutQuickUser.init(this.$refs["kt_quick_user"]);
        if (window.Laravel) {
            this.$http.get('/api/user')
                .then(response => {
                    if (response.status === 200) {
                        this.user = response.data.user;
                        this.$store.commit(
                            SET_AUTH,
                            this.user
                        )
                    }
                });
        }
    },
    methods: {
        onLogout() {
            this.$store
                .dispatch(LOGOUT)
                .then(() => {
                    this.$http.post('/logout')
                        .then(response => {
                            window.location.href = "/login"
                        });

                });
        },
        closeOffcanvas() {
            new KTOffcanvas(KTLayoutQuickUser.getElement()).hide();
        }
    },
    watch: {
        currentUser: {
            handler(newVal) {
                this.user = newVal;
            },
            deep: true,
        },
    },
    computed: {
        ...mapGetters(['currentUser']),
        picture() {
            return "/images/profile.png";
        }
    }
};
</script>
