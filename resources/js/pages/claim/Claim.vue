<template>
    <div v-if="claim != null">
        <CNav variant="tabs" class="dashboard-tab">
            <CNavItem :active="tab == 'info'">
                <div @click="changeTab('info')">Info</div>
            </CNavItem>
            <CNavItem :active="tab == 'history'">
                <div @click="changeTab('history')">History</div>
            </CNavItem>
        </CNav>
        <template v-if="claim != null && claim.status == 'draft' && user.category == 'workshop'">
            <template v-if="tab =='info'">
                <CreateClaim></CreateClaim>
            </template>
            <template v-else>
                <History :id="id" type="claims"></History>
            </template>
        </template>
        <template v-else>
            <template v-if="tab =='info'">
                <ClaimInfo></ClaimInfo>
            </template>
            <template v-else>
                <History :id="id" type="claims"></History>
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
            id: ""
        }
    },
    methods: {
        changeTab(tab){
            this.tab = tab;
        }
    },
    mounted(){ 
        this.id = window.location.pathname.split("/").pop();
        this.$store.dispatch('GET_CLAIM', this.id);
    },
    computed: {
        ...mapGetters(["user", "claim"]),
    },
}
</script>