<template>
  <el-scrollbar wrap-class="scrollbar-wrapper">

    <img class="logo-login" :src="'/images/logo-menu.png'" >
    <el-menu
      :show-timeout="200"
      :default-active="$route.path"
      :collapse="isCollapse"
      mode="vertical"
      background-color="#ffffff"
      text-color="#A0A0A0"
      active-text-color="#088600"
    >
      <sidebar-item v-for="route in routes" :key="route.path" :item="route" :base-path="route.path" />
    </el-menu>
  </el-scrollbar>
</template>

<script>
import { mapGetters } from 'vuex';
import SidebarItem from './SidebarItem';

export default {
  components: { SidebarItem },
  computed: {
    ...mapGetters([
      'sidebar',
      'permission_routers',
    ]),
    routes() {
      return this.$store.state.permission.routes;
    },
    isCollapse() {
      return !this.sidebar.opened;
    },
  },
};
</script>
