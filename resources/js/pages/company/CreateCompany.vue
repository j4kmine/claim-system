<template>
    <div>
        <form @submit.prevent="save()">
            <CCard>
                <CCardHeader>
                    {{ $route.name }}
                </CCardHeader>
                <CCardBody>
                    <CRow>
                        <CCol md="6">
                            <CInput
                                required
                                label="Company Name"
                                :value.sync="name"
                            />
                        </CCol>
                        <CCol md="6">
                            <CInput
                                required
                                label="Code"
                                :value.sync="code"
                            />
                        </CCol>
                    </CRow>
                    <CRow>
                        <CCol md="6">
                            <CInput
                                label="UEN No."
                                :value.sync="acra"
                            />
                        </CCol>
                        <CCol md="6"> 
                            <CInput
                                label="Address"
                                :value.sync="address"
                            />
                        </CCol>
                    </CRow>
                    <CRow>
                        <CCol md="6">
                            <CInput
                                required
                                label="Contact Person"
                                :value.sync="contact_person"
                            />
                        </CCol>
                        <CCol md="6"> 
                            <CInput
                                required
                                label="Contact Number"
                                :value.sync="contact_no"
                            />
                        </CCol>
                    </CRow>
                    <CRow>
                        <CCol md="6">
                            <CInput
                                label="Contact Email"
                                type="email"
                                :value.sync="contact_email"
                            />
                        </CCol>
                        <CCol md="6" v-if="adminCreateEditInsurer(user)">
                            <CSelect
                                label="Surveyor"
                                :value.sync="surveyor_id"
                                :options="surveyorOptions"
                            />
                        </CCol>
                        <CCol md="6">
                            <CSelect
                                label="Status"
                                :value.sync="status"
                                :options="activeOptions"
                                placeholder="Please select"
                            />
                        </CCol>
                        <CCol v-if="type == 'dealers'" md="6" style="margin-bottom: 10px;">
                            <div class="form-check checkbox">
                                <input class="form-check-input" v-model="extended_warranty" id="extended" type="checkbox" value="">
                                <label class="" for="extended">Mandatory Extended Warranty <a href="/documents/extended.pdf" target="_blank"><img src="/images/vue/question.png"></a></label>
                            </div>
                        </CCol>
                    </CRow>
                    
                    <CTextarea
                        label="Description"
                        :value.sync="description"
                        rows="5"
                    />
                    <div class="form-actions">
                        <CButton type="submit" color="primary">Save changes</CButton>
                        <CButton color="secondary" @click="cancel()">Cancel</CButton>
                    </div>
                </CCardBody>
            </CCard>
        </form>
        <template v-if="id != ''">
            <CCard v-if="adminEditSurveyor(user)">
                <CCardHeader>
                    Appointed By Insurers
                </CCardHeader>
                <CCardBody>
                    <Appointments :companyId="id"></Appointments>
                </CCardBody>
            </CCard>
            <CCard v-if="isEdit()">
                <CCardHeader>
                    Users
                </CCardHeader>
                <CCardBody>
                    <Users :companyId="id"></Users>
                </CCardBody>
            </CCard>    
        </template>
    </div>
</template>
<script>
import { mapGetters } from "vuex";
export default {
    data() {
        return {
            id: "",
            name: "",
            code: "",
            acra: "",
            address: "",
            contact_person: "",
            status: "active",
            contact_no: "",
            contact_email: "",
            description: "",
            type: "",
            extended_warranty: false,
            surveyor_id: "",
            surveyorOptions: [],
            activeOptions: [
                { label: 'Active', value: 'active' },
                { label: 'Inactive', value: 'inactive' }
            ],
        }
    },
    mounted(){ 
        this.type = this.$route.name.split(" ")[1].toLowerCase() + 's';
        if(this.isEdit()){
            this.company();
        }
    },
    watch:{
        user(val){
            if(this.adminCreateEditInsurer(val)){
                this.surveyors();
            }
        },
        $route(val){
            this.type = val.name.split(" ")[1].toLowerCase() + 's';
        }
    },
    methods: {
        isEdit(){
            return this.$route.name.includes("Edit");
        },
        adminCreateEditInsurer(val){
            return this.$route.name.includes("Insurer") && val.role == 'admin' && val.category == 'all_cars';
        },
        adminEditSurveyor(val){
            return this.$route.name == "Edit Surveyor" && val.role == 'admin' && val.category == 'all_cars';
        },
        surveyors(){
            var inputs = {};
            inputs.method = 'get';
            inputs.url = "/api/surveyors/options";
            this.surveyor_id = "";
            this.$store.dispatch('API', inputs).then((data)=>{
                this.surveyorOptions = [];
                for(var i = 0 ; i < data.surveyors.length; i++){
                    if( i == 0 ){
                        this.surveyor_id = data.surveyors[i].id;
                    }
                    this.surveyorOptions.push({ label: data.surveyors[i].name, value: data.surveyors[i].id });
                }
            });
        },
        company(){
            var inputs = {};
            inputs.method = 'get';
            inputs.url = "/api/" + this.type + "/edit";
            inputs.id = window.location.pathname.split("/").pop();
            this.$store.dispatch('API', inputs).then((data)=>{
                this.id = data.company.id;
                this.name = data.company.name;
                this.email = data.company.email;
                this.code = data.company.code;
                this.acra = data.company.acra;
                this.address = data.company.address;
                this.contact_person = data.company.contact_person;
                this.contact_no = data.company.contact_no;
                this.contact_email = data.company.contact_email;
                this.description = data.company.description;
                this.status = data.company.status;
                this.extended_warranty = data.company.extended_warranty;
                if(data.company.insurer != null){
                    this.surveyor_id = data.company.insurer.surveyor_id;
                } else {
                    this.surveyor_id = "";
                }
            });
        },
        save() {
            // { value: 'Option2', label: 'Custom label'}
            var inputs = {};
            inputs.method = "post";
            inputs.name = this.name;
            inputs.email = this.email;
            inputs.code = this.code;
            inputs.acra = this.acra;
            inputs.address = this.address;
            inputs.contact_person = this.contact_person;
            inputs.contact_no = this.contact_no;
            inputs.contact_email = this.contact_email;
            inputs.description = this.description;
            inputs.surveyor_id = this.surveyor_id;
            inputs.status = this.status;
            inputs.extended_warranty = this.extended_warranty;
            if(this.$route.name.includes("Edit")){
                inputs.id = window.location.pathname.split("/").pop();
                inputs.url = "/api/" + this.type + "/edit";
            } else {
                inputs.url = "/api/" + this.type + "/create";
            }
            this.$store.dispatch('API', inputs).then(()=>{
                this.$router.push('/' + this.type);
            }); 
        },
        cancel() {
            this.$router.go(-1);
        },
    },
    computed: {
        ...mapGetters(["user"]),
    },
}
</script>