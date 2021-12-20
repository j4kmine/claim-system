<template>
    <div>
        <CCard v-if="user.company != null">
            <CCardHeader>
                Company Profile
            </CCardHeader>
            <CCardBody>
                <CRow>
                    <CCol md="6">
                        <CInput
                            disabled
                            name="company-name"
                            label="Company Name"
                            :value.sync="user.company.name"
                        />
                    </CCol>
                    <CCol md="6">
                        <CInput
                            disabled
                            name="code"
                            label="Code"
                            :value.sync="user.company.code"
                        />
                    </CCol>
                </CRow>
                <CRow>
                    <CCol md="6">
                        <CInput
                            disabled
                            name="acra"
                            label="UEN No."
                            :value.sync="user.company.acra"
                        />
                    </CCol>
                    <CCol md="6">
                        <CInput
                            :disabled="user.role != 'admin'"
                            name="address"
                            label="Address"
                            :value="company_address"
                        />
                    </CCol>
                </CRow>
                <CRow>
                    <CCol md="6">
                        <CInput
                            :disabled="user.role != 'admin'"
                            name="contact-person"
                            label="Contact Person"
                            :value.sync="contact_person"
                        />
                    </CCol>
                    <CCol md="6">
                        <CInput
                            :disabled="user.role != 'admin'"
                            name="contact-number"
                            label="Contact Number"
                            :value="contact_no"
                        />
                    </CCol>
                </CRow>
                <CRow>
                    <CCol md="6">
                        <CInput
                            :disabled="user.role != 'admin'"
                            name="contact-email"
                            label="Contact Email"
                            :value.sync="contact_email"
                        />
                    </CCol>
                </CRow>
                <CTextarea
                    :disabled="user.role != 'admin'"
                    label="Description"
                    :value.sync="description"
                    rows="5"
                />
                <CRow v-if="user.role == 'admin'">
                    <CCol col="12">
                        <CButton @click="changeCompanyInfo()" color="primary">Save</CButton>
                    </CCol>
                </CRow>
            </CCardBody>
        </CCard>
        <CCard>
            <CCardHeader>
                User Profile
            </CCardHeader>
            <CCardBody>
                <CRow>
                    <CCol md="6">
                        <CInput
                            required
                            name="name"
                            label="Name"
                            :value.sync="name"
                        />
                    </CCol>
                    <CCol md="6">
                        <CInput
                            disabled
                            name="email"
                            label="Email"
                            :value.sync="email"
                        />
                    </CCol>
                </CRow>
                <CRow>
                    <CCol md="6">
                        <CInput
                            class="capitalize"
                            disabled
                            name="category"
                            label="Category"
                            :value="$helpers.unslugify(user.category)"
                        />
                    </CCol>
                    <CCol md="6">
                        <CInput
                            disabled
                            name="company"
                            label="Company Name"
                            :value="user.company_name"
                        />
                    </CCol>
                </CRow>
                <CRow>
                    <CCol md="6">
                        <CInput
                            class="capitalize"
                            disabled
                            name="role"
                            label="Role"
                            :value="$helpers.unslugify(user.role)"
                        />
                    </CCol>
                </CRow>
                <CRow>
                    <CCol col="12">
                        <CButton @click="changeProfile()" color="primary">Save</CButton>
                    </CCol>
                </CRow>
            </CCardBody>
        </CCard>
        <CCard>
            <CCardHeader>
                Change Password
            </CCardHeader>
            <CCardBody>
                <CRow>
                    <CCol col="12">
                        <CInput
                            required
                            type="password"
                            name="old_password"
                            label="Old Password"
                            :value.sync="old_password"
                        />
                    </CCol>
                </CRow>
                <CRow>
                    <CCol col="12">
                        <CInput
                            required
                            type="password"
                            name="new_password"
                            label="New Password"
                            :value.sync="new_password"
                        />
                    </CCol>
                </CRow>
                <CRow>
                    <CCol col="12">
                        <CInput
                            required
                            type="password"
                            name="confirm_password"
                            label="Confirm Password"
                            :value.sync="confirm_password"
                        />
                    </CCol>
                </CRow>
                <CRow>
                    <CCol col="12">
                        <CButton @click="changePassword()" color="primary">Save</CButton>
                    </CCol>
                </CRow>
            </CCardBody>
        </CCard>
    </div>
</template>
<script>
import { mapGetters } from "vuex";
export default {
    data() {
        return {
            old_password: "",
            new_password: "",
            confirm_password: "",
            name: "",
            email: "",
            company_address: "",
            contact_person: "",
            contact_no: "",
            contact_email: "",
            description: ""
        }
    },
    watch: {
        user: function (val) {
            this.name = val.name;
            this.email = val.email;
            if(val.company != null){
                this.company_address = val.company.address;
                this.contact_person = val.company.contact_person;
                this.contact_no = val.company.contact_no;
                this.contact_email = val.company.contact_email;
                this.description = val.company.description;
            }
        }
    },
    methods: {
        changePassword: function () {
            var inputs = {};
            inputs.method = "post";
            inputs.url = "/api/changePassword";
            inputs.old_password = this.old_password;
            inputs.password = this.new_password;
            inputs.password_confirmation = this.confirm_password;
            this.$store.dispatch('API', inputs).then((data)=> {
                this.old_password = "";
                this.new_password = "";
                this.confirm_password = "";
            });
        },
        changeCompanyInfo: function(){
            var inputs = {};
            inputs.method = "post";
            inputs.url = "/api/changeCompanyInfo";
            inputs.company_address = this.company_address;
            inputs.contact_person = this.contact_person;
            inputs.contact_no = this.contact_no;
            inputs.contact_email = this.contact_email;
            inputs.description = this.description;
            this.$store.dispatch('API', inputs).then((data)=> {
                this.$store.dispatch('GET_USER');
            });
        },
        changeProfile: function () {
            var inputs = {};
            inputs.method = "post";
            inputs.url = "/api/changeProfile";
            inputs.email = this.email;
            inputs.name = this.name;
            this.$store.dispatch('API', inputs).then((data)=> {
                this.$store.dispatch('GET_USER');
            });;
        }
    },
    computed: {
        ...mapGetters(["user"]),
    },
}
</script>