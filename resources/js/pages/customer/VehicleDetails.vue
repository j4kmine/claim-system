<template>
  <div>
        <form @submit.prevent="submit()">
        <CCard>
            <CCardHeader class="font-weight-bold"> 
                Vehicle Info
            </CCardHeader>
            <CCardBody>
                <div v-for="vehicleInfo in VehiclesInfo" :key="vehicleInfo.id">
                    <CRow>
                        <CCol md="6" class="font-weight-bold">
                            {{ vehicleInfo.firstLabel }}
                        </CCol>
                        <CCol md="6" class="font-weight-bold">
                            {{ vehicleInfo.secondLabel }}
                        </CCol>
                    </CRow>
                    <CRow>
                        <CCol md="6" class="value-content">
                            {{ vehicleInfo.firstValue }}
                        </CCol>
                        <CCol md="6" class="value-content">
                            {{ vehicleInfo.secondValue }}
                        </CCol>
                    </CRow>
                </div>
                <CRow>
                    <CCol md="6" class="font-weight-bold">Insurance Validity</CCol>
                    <CCol md="6" class="font-weight-bold">Status</CCol>
                </CRow>
                <CRow>
                    <CCol md="6" class="font-weight-bold">{{}}</CCol>
                    <CCol md="6" class="font-weight-bold">
                        <CSelect
                            :disabled="true"
                            :value.sync="details.status"
                            :options="statusOptions"
                            placeholder="Please select"
                        />
                    </CCol>
                </CRow>
            </CCardbody>
        </CCard>
        <UserAccess :idVehicle="id"></UserAccess>   
        <InsurancePolicy :idVehicle="id"></InsurancePolicy>
        <WarrantyPolicy :idVehicle="id"></WarrantyPolicy>
        <ServicingHistory :idVehicle="id"></ServicingHistory>
        <ClaimsHistory :idVehicle="id"></ClaimsHistory>
        </form>
    </div>
</template>
<script>
import { mapGetters } from "vuex";
export default {
    data(){
        return {
            id: "",
            details: "",
            last_servicing: [],
            inspection_reports: [],
            remarks: "",
            statusOptions: [
                {label: "Pending", value: "pending"},
                {label: "Completed", value: "completed"}
            ]
        }
    },
    watch: {
        user: function (newUser) {
            if (this.$route.name != "Edit User" && newUser.category != "all_cars") {
                this.category = this.user.category;
                this.company = this.user.company_id;
            }
        },
    },
    mounted() {
        this.id = window.location.pathname.split("/").pop();
        var inputs = {};
        inputs.method = "get";
        inputs.url = "/api/vehicles/" + this.id;
        inputs.id = this.id;
        this.$store.dispatch("API", inputs).then((data) => {
            this.details = data;  
            this.last_servicing = data.services.filter(services => services.status == 'completed').sort(function(a, b){
                return b.id - a.id
            });
        });
    },
    computed: {
    ...mapGetters(["user"]),
    VehiclesInfo(){
        var details = this.details;
        return [
            { firstLabel: "Vehicle Number", firstValue: details.registration_no, secondLabel: "Vehicle Make", secondValue: details.make },            
            { firstLabel: "Vehicle Model", firstValue: details.model, secondLabel: "Vehicle First Reg. Date", secondValue: details.format_registration_date },            
            { firstLabel: "COE Expiry.", firstValue: details.coe_expiry_date, secondLabel: "Road Tax Expiry", secondValue: details.tax_expiry_date },            
            { firstLabel: "Warranty Validity", firstValue: details.warranty ? details.warranty.expiry_date : "-", secondLabel: "Last Servicing", secondValue: this.last_servicing.length > 0 ? this.last_servicing[0].format_appointment_date : "-"},            
        ]
    },
    },
    methods:{
        formatInput(){
            var inputs = {};
            inputs.method = "post";
            inputs.accident_status = this.details.status;
            inputs.remarks = this.remarks;
            return inputs;
        },
        submit(){
            var inputs = this.formatInput();
            if(this.id != null){
                inputs.id = this.id;
            }
            inputs.url = `/api/customers/${this.id}/reports`
            this.$store.dispatch('API', inputs).then(()=>{
                this.$router.push('/accidentReport/list');
            }); 
        },
        cancel(){
            this.$router.push('/accidentReport/list');
        },
    }
    
}
</script>
<style scoped>
.value-content{
    color: #007BFF;
    margin: 4px 0px;
    padding-left: 27px;
    height: 24px;
}
.top-buffer { margin-top:20px; }
</style>