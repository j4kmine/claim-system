<template>
    <form @submit.prevent="save()">
        <CRow>
            <CCol lg="12">
                <CCard>
                    <CCardHeader class="font-weight-bold">
                        {{ $route.name }}
                    </CCardHeader>
                    <CCardBody>
                        <CRow>
                            <CCol md="6">
                                <CInput
                                    :disabled="user.role != 'admin'"
                                    label="Name"
                                    class="font-weight-bold"
                                    :value.sync="name"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    :disabled="this.$route.name == 'Edit User'"
                                    label="Email"
                                    type="email"
                                    class="font-weight-bold"
                                    :value.sync="email"
                                />
                            </CCol>
                            <CCol md="6">
                                <CSelect
                                    :disabled="this.$route.name == 'Edit User' || this.user.category != 'all_cars'"
                                    label="Category"
                                    class="font-weight-bold"
                                    :value.sync="category"
                                    :options="categoryOptions"
                                    placeholder="Please select"
                                />
                            </CCol>
                            <CCol md="6" v-if="companyOptions.length > 0">
                                <CSelect
                                    :disabled="this.$route.name == 'Edit User' || this.user.category != 'all_cars'"
                                    label="Company"
                                    class="font-weight-bold"
                                    :value.sync="company"
                                    :options="companyOptions"
                                    placeholder="Please select"
                                />
                            </CCol>
                            <CCol md="6">
                                <CSelect
                                    :disabled="this.$route.name == 'Edit User'"
                                    label="Role"
                                    class="font-weight-bold"
                                    :value.sync="role"
                                    :options="category != 'dealer' ? roleOptions : dealerRoleOptions"
                                    placeholder="Please select"
                                />
                            </CCol>
                            <CCol md="6">
                                <CSelect
                                    :disabled="user.role != 'admin'"
                                    label="Status"
                                    class="font-weight-bold"
                                    :value.sync="status"
                                    :options="activeOptions"
                                    placeholder="Please select"
                                />
                            </CCol>
                        </CRow>
                        <CRow>
                            <CCol md="6" v-if="user.role == 'admin'">
                                <CInputCheckbox class="font-weight-bold" label="Receive Notification" :checked.sync="notification" value="1" />
                            </CCol>
                        </CRow>
                        
                        <div class="form-actions" v-if="user.role == 'admin'" style="margin-top:1rem;">
                            <CButton type="submit" color="primary">Save changes</CButton>
                            <CButton color="secondary" @click="cancel()">Cancel</CButton>
                        </div>
                    </CCardBody>
                </CCard>
                </transition>
            </CCol>
        </CRow>
    </form>
</template>
<script>
import { mapGetters } from "vuex";
export default {
    data() {
        return {
            notification: 0,
            name: "",
            email: "",
            role: "",
            category: "",
            company: "",
            status: "",
            categoryOptions: [
                { label: 'All Cars', value: 'all_cars' },
                { label: 'Dealer', value: 'dealer' },
                { label: 'Insurer', value: 'insurer' },
                { label: 'Surveyor', value: 'surveyor' },
                { label: 'Workshop', value: 'workshop' }
            ],
            roleOptions: [
                { label: 'Support Staff', value: 'support_staff' },
                { label: 'Admin', value: 'admin' }
            ],
            dealerRoleOptions: [
                { label: 'Salesperson', value: 'salesperson' },
                { label: 'Admin', value: 'admin' }
            ],
            companyOptions: [],
            activeOptions: [
                { label: 'Active', value: 'active' },
                { label: 'Inactive', value: 'inactive' }
            ],
        }
    },
    mounted(){ 
        if(this.$route.name == "Edit User"){
            var inputs = {};
            inputs.method = 'get';
            inputs.url = "/api/users/edit";
            inputs.id = window.location.pathname.split("/").pop();
            this.$store.dispatch('API', inputs).then((data)=>{
                this.name = data.user.name;
                this.email = data.user.email;
                this.role = data.user.role;
                this.category = data.user.category;
                this.company = data.user.company_id;
                this.status = data.user.status;
                this.notification = data.user.notification_email;
            }); 
        } else {
            if(this.user.category != 'all_cars'){
                this.category = this.user.category;
                this.company = this.user.company_id;
            }
        }
    },
    watch: {
        category: function (newCategory) {
            if(newCategory != null){
                this.companyOptions = [];
                var inputs = {};
                inputs.method = 'post';
                inputs.url = '/api/companies';
                inputs.category = newCategory;
                this.$store.dispatch('API', inputs).then((data)=>{
                    for(var i = 0 ; i < data.companies.length; i++){
                        this.companyOptions.push({ label: data.companies[i].name, value: data.companies[i].id });
                    }
                });
            }
        },
        user: function (newUser) {
            if(this.$route.name != "Edit User" && newUser.category != 'all_cars'){
                this.category = this.user.category;
                this.company = this.user.company_id;
            }
        }
    },
    methods: {
        save() {
            // { value: 'Option2', label: 'Custom label'}
            var inputs = {};
            inputs.name = this.name;
            inputs.role = this.$helpers.slugify(this.role);
            inputs.email = this.email;
            inputs.category = this.$helpers.slugify(this.category);
            inputs.method = "post";
            inputs.company_id = this.company;
            inputs.status = this.status;
            inputs.notification_email = this.notification;
            if(this.$route.name == "Edit User"){
                inputs.id = window.location.pathname.split("/").pop();
                inputs.url = "/api/users/edit";
            } else {
                inputs.url = "/api/users/create";
            }
            this.$store.dispatch('API', inputs).then(()=>{
                if (this.user.company_id!=null){
                    this.$router.push('/companyProfile');
                }else{
                    this.$router.push('/users');
                }
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