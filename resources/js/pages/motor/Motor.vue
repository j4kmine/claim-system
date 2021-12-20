<template>
    <div v-if="motor != null">
        <CNav variant="tabs" class="dashboard-tab">
            <CNavItem :active="tab == 'info'">
                <div @click="changeTab('info')">Info</div>
            </CNavItem>
            <CNavItem :active="tab == 'history'">
                <div @click="changeTab('history')">History</div>
            </CNavItem>
        </CNav>
        <template v-if="motor.status == 'draft' && user.category == 'dealer'">
            <template v-if="tab =='info'">
                <CreateMotor></CreateMotor>
            </template>
            <template v-else>
                <History :id="id" type="warranties"></History>
            </template>
        </template>
        <template v-else>
            <template v-if="tab =='info'">
                <MotorInfo></MotorInfo>
            </template>
            <template v-else>
                <History :id="id" type="motors"></History>
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
        this.$store.dispatch('GET_MOTOR', this.id);
    },
    computed: {
        ...mapGetters(["user", "motor"]),
    },
}
</script>