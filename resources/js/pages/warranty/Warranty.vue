<template>
  <div v-if="warranty != null">
    <CNav variant="tabs" class="dashboard-tab">
      <CNavItem :active="tab == 'info'">
        <div @click="changeTab('info')">Info</div>
      </CNavItem>
      <CNavItem :active="tab == 'history'">
        <div @click="changeTab('history')">History</div>
      </CNavItem>
    </CNav>
    <template v-if="warranty.status == 'draft' && user.category == 'dealer'">
      <template v-if="tab == 'info'">
        <CreateWarranty></CreateWarranty>
      </template>
      <template v-else>
        <History :id="id" type="warranties"></History>
      </template>
    </template>
    <template v-else>
      <template v-if="tab == 'info'">
        <WarrantyInfo></WarrantyInfo>
      </template>
      <template v-else>
        <History :id="id" type="warranties"></History>
      </template>
    </template>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
export default {
  data() {
    return {
      tab: "info",
      id: "",
    };
  },
  methods: {
    changeTab(tab) {
      this.tab = tab;
    },
  },
  mounted() {
    this.id = window.location.pathname.split("/").pop();
    this.$store.dispatch("GET_WARRANTY", this.id);
  },
  computed: {
    ...mapGetters(["user", "warranty"]),
  },
};
</script>
