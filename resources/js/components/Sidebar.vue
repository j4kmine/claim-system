<template>
  <CSidebar 
    fixed 
    :minimize="sidebarMinimize"
    :show="sidebarShow"
    @update:show="(value) => $store.commit('SET', ['sidebarShow', value])"
  >
    <CSidebarBrand class="d-md-down-none" to="/dashboard">
      <img 
        class="c-sidebar-brand-full" 
        name="logo" 
        size="custom-size" 
        src="/images/vue/logo.png"
        :height="35" 
        viewBox="0 0 556 134"
      />
      <img
        class="c-sidebar-brand-minimized" 
        name="logo" 
        size="custom-size" 
        src="/images/vue/logo.png"
        :height="35" 
        viewBox="0 0 110 134"
      />
      <div v-if="!sidebarMinimize">
        Welcome <span class="capitalize" v-if="user.role != null">{{ $helpers.unslugify(user.role) }}</span>!<br>
        {{ user.name }}
      </div>
    </CSidebarBrand>

    <CRenderFunction flat :content-to-render="$options.nav"/>
    <CSidebarMinimizer
      class="d-md-down-none"
      @click.native="$store.commit('SET', ['sidebarMinimize', !sidebarMinimize])"
    />
  </CSidebar>
</template>

<script>
import nav from '../navs/nav'
import admin from '../navs/admin'
import supportStaff from '../navs/support_staff'
import workshopSupportStaff from '../navs/workshop_support_staff'
import workshopAdmin from '../navs/workshop_admin'
import surveyorSupportStaff from '../navs/surveyor_support_staff'
import surveyorAdmin from '../navs/surveyor_admin'
import insurerSupportStaff from '../navs/insurer_support_staff'
import insurerAdmin from '../navs/insurer_admin'
import dealerSalesperson from '../navs/dealer_salesperson'
import dealerAdmin from '../navs/dealer_admin'

import { mapGetters } from "vuex";
export default {
  nav,
  mounted(){
  },
  watch: {
      user: function (val) {
        if (this.user.category == "workshop") {
          if(this.user.role == "admin"){
            this.$options.nav = workshopAdmin;
          } else {
            this.$options.nav = workshopSupportStaff;
          }
        } else if (this.user.category == "surveyor") {
          if(this.user.role == "admin"){
            this.$options.nav = surveyorAdmin;
          } else {
            this.$options.nav = surveyorSupportStaff;
          }
        } else if (this.user.category == "insurer") {
          if(this.user.role == "admin"){
            this.$options.nav = insurerAdmin;
          } else {
            this.$options.nav = insurerSupportStaff;
          }
        } else if (this.user.category == "all_cars") {
          if(this.user.role == "admin"){
            this.$options.nav = admin;
          } else {
            this.$options.nav = supportStaff;
          }
        } else if (this.user.category == "dealer") {
          if(this.user.role == "admin"){
            this.$options.nav = dealerAdmin;
          } else {
            this.$options.nav = dealerSalesperson;
          }
        }
      }
  },
  computed: {
    ...mapGetters(["sidebarShow", "sidebarMinimize", "user"]),
  }
}
</script>
