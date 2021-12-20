<template>
  <div>
    <CCard v-if="user.company != null">
      <CCardHeader class="font-weight-bold"> New User </CCardHeader>
      <CCardBody>
        <form @submit.prevent="save()">
          <CRow>
            <CCol md="6">
              <CInput class="font-weight-bold" label="Name" type="text" :value.sync="name" />
            </CCol>
            <CCol md="6">
                <CInput type="email" class="font-weight-bold" label="Email" :value.sync="email" />
            </CCol>
          </CRow>
          <CRow>
            <CCol md="6">
              <CSelect
                class="font-weight-bold"
                required
                name="role"
                label="Role"
                :value.sync="role"
                :options="role_options"
                placeholder="Select"
            />
            </CCol>
            <CCol md="6">
                <CInput class="font-weight-bold" type="text" readonly label="Company Name" :value.sync="company_name" />
            </CCol>
          </CRow>
          <div class="form-actions" v-if="user.role == 'admin'">
            <CButton type="submit" color="primary">Save changes</CButton>
            <CButton color="secondary" @click="cancel()">Cancel</CButton>
          </div>
        </form>
      </CCardBody>
    </CCard>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
export default {
  props: ["companyId", "companyUsers"],
  data() {
    return {
      name: "",
      email:"",
      role: "",
      company_id: "",
      role_options: [{ label: "Admin", value: "admin" }, { label: "Support Staff", value: "support_staff" }, ],
      description: "",
      company_name: ""
    };
  },
  methods: {
    save(){
            var inputs = {};
            inputs.name = this.name;
            inputs.role = this.role;
            inputs.email = this.email
            inputs.url = "/api/companies/users"
            inputs.method = "post";
            this.$store.dispatch('API', inputs).then(()=>{
              this.$router.push('/companyProfile');
            }); 
    },
    cancel() {
        this.$router.go(-1);
    },
  },
  mounted() {
    if (this.companyId == null) {
      this.url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/users";
      this.servicing_slots_url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/service-types";
    } else {
      this.url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/users/" +
        this.companyId;
      this.servicing_slots_url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/service-types";
    }
    var inputs = {};
        inputs.method = 'post';
        inputs.url = '/api/companies';
        inputs.category = 'workshop';
        this.$store.dispatch('API', inputs).then((data)=>{
          if (data.companies){
            if (data.companies.length > 0){
              this.company_name = data.companies[0].name;
              this.company_id = data.companies[0].id;
            }
          }
        });
  },
  computed: {
    ...mapGetters(["user"]),
  },
};
</script>