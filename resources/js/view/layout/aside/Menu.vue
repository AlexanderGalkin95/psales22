<template>
  <ul class="menu-nav">
    <router-link
      to="/dashboard"
      v-slot="{ href, navigate, isActive, isExactActive }"
      custom
    >
      <li
        aria-haspopup="true"
        data-menu-toggle="hover"
        class="menu-item"
        :class="[
          isActive && 'menu-item-active',
          isExactActive && 'menu-item-active'
        ]"
      >
        <a :href="href" class="menu-link" @click="navigate">
          <i class="menu-icon flaticon2-architecture-and-city"></i>
          <span class="menu-text">Главная</span>
        </a>
      </li>
    </router-link>

    <router-link
      to="/companies"
      v-slot="{ href, navigate, isActive, isExactActive }"
      custom
    >
      <li
        aria-haspopup="true"
        data-menu-toggle="hover"
        class="menu-item"
        :class="[
          isActive && 'menu-item-active',
          isExactActive && 'menu-item-active'
        ]"
      >
        <a :href="href" class="menu-link" @click="navigate">
          <i class="menu-icon flaticon-layers"></i>
          <span class="menu-text">Компании</span>
        </a>
      </li>
    </router-link>

    <router-link
      to="/projects"
      v-slot="{ href, navigate, isActive, isExactActive }"
      custom
    >
      <li
        aria-haspopup="true"
        data-menu-toggle="hover"
        class="menu-item"
        :class="[
          isActive && 'menu-item-active',
          isExactActive && 'menu-item-active'
        ]"
      >
        <a :href="href" class="menu-link" @click="navigate">
          <i class="menu-icon flaticon-analytics"></i>
          <span class="menu-text">Проекты</span>
        </a>
      </li>
    </router-link>

    <router-link
      to="/users"
      v-slot="{ href, navigate, isActive, isExactActive }"
      custom
    >
      <li
        aria-haspopup="true"
        data-menu-toggle="hover"
        class="menu-item"
        :class="[
          isActive && 'menu-item-active',
          isExactActive && 'menu-item-active'
        ]"
      >
        <a :href="href" class="menu-link" @click="navigate">
          <i class="menu-icon flaticon-users"></i>
          <span class="menu-text">Пользователи</span>
        </a>
      </li>
    </router-link>

    <router-link
      to="/daily-reports"
      v-slot="{ href, navigate, isActive, isExactActive }"
      custom
    >
      <li
        aria-haspopup="true"
        data-menu-toggle="hover"
        class="menu-item"
        :class="[
          isActive && 'menu-item-active',
          isExactActive && 'menu-item-active'
        ]"
      >
        <a :href="href" class="menu-link" @click="navigate">
          <i class="menu-icon flaticon-analytics"></i>
          <span class="menu-text">Проекты для ЕО</span>
        </a>
      </li>
    </router-link>

    <router-link
        to="/log-events"
        v-slot="{ href, navigate, isActive, isExactActive }"
        custom
    >
      <li
          aria-haspopup="true"
          data-menu-toggle="hover"
          class="menu-item"
          :class="[
          isActive && 'menu-item-active',
          isExactActive && 'menu-item-active'
        ]"
      >
        <a :href="href" class="menu-link" @click="navigate">
          <i class="menu-icon flaticon-analytics"></i>
          <span class="menu-text">Лог событий</span>
        </a>
      </li>
    </router-link>
  </ul>
</template>

<script>
export default {
  name: "KTMenu",
  methods: {
    hasActiveChildren(match) {
      return this.$route["path"].indexOf(match) !== -1;
    }
  }
};
</script>
