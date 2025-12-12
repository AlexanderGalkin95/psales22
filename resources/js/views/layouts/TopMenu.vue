<template>
    <main class="">
        <div class="relative items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0">
            <div style="height: 40px; background:white;"></div>
            <div class="w-100 top-0 right-0 px-6 sm:block" style="background: #6300bf; text-align: right;">
                <span class="text-sm text-white ml-3" style="float: left;font-size: 28px;font-weight: bold;">Pinscher Sales</span>

                <div class="py-2">
                    <nav class="navbar navbar-expand-lg navbar-light bg-light" style="background-color: #6300bf!important;padding: 0 10px;">
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar1" aria-controls="navbar1" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse" id="navbar1" style="margin-right: 24px; text-align: left;">
                            <ul class="navbar-nav mr-auto">
                                <li class="nav-item dropdown">
                                    <router-link to="/dashboard" :class="{ active : $route.name==='dashboard'}">Главная</router-link>
                                </li>
                                <li class="nav-item dropdown">
                                    <router-link to="/companies" :class="{ active : $route.name==='companies'}">Компании</router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/projects" :class="{ active : $route.name==='projects'}">Проект</router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/users" :class="{ active : $route.name==='users'}">Пользователи</router-link>
                                </li>
                            </ul>
                        </div>

                        <!-- Right Side Of Navbar -->
                        <div class="navbar-nav ml-auto text-sm text-white underline mr-2" style=" float: right;">
                            <i class="fa fa-sign-out" aria-hidden="true" style="font-size: 19px;"></i> Выход
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
export default {
    name: "TopMenu",
    data() {
        return {
            user: {
                name: 'Test',
                surname: 'Test',
                avatar: '/images/logo.png'
            }
        }
    }
}
</script>

<style scoped>
.navbar .nav-item a.active{
    color:white;
}
.pt-4, .py-4 {
    padding-top: 0 !important;
}
li .dropdown-submenu a:hover {
    background: #f8f8f8;
}
.v-select .dropdown-toggle::after{
    display: none;
}
.v-select .selected-tag{
    position: absolute;
}
.v-select .dropdown-toggle{
    border: none;
}
.dropdown-toggle .vs__actions{
    padding-right: 10px;
}
.nav-item .profile-image .dropdown-toggle::after{
    display:none;
}
.nav-item .profile-image .badge{
    position: absolute;
    display: inline-block;
    right: 22px;
    top: 24px;
    border-radius: 100%;
    background: blue;
    width: 16px;
    height: 16px;
}
.clear .v-select .selected-tag {
    position: absolute;
}
/*.v-select .dropdown-toggle .clear {*/
/*    margin-bottom: 5px!important;*/
/*}*/
/*.v-select.form-control i {*/
/*    margin-bottom: 8px!important;*/
/*}*/
.v-select.single .selected-tag {
    /*margin-top: 9px!important;*/
    white-space: nowrap;
}
.v-select .dropdown-toggle input[type=search] {
    margin-top: 0;
}
/*.v-select .dropdown-toggle .vs__actions {*/
/*    padding-bottom: 0!important;*/
/*}*/

</style>
