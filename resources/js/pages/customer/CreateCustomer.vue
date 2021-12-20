<template>
  <form @submit.prevent="save()">
    <CRow>
      <CCol lg="12">
        <CCard v-if="user.role == 'admin' && user.category == 'all_cars'">
          <CCardHeader class="font-weight-bold">
            {{ $route.name }}
          </CCardHeader>
          <CCardBody>
            <CRow>
              <CCol md="6">
                <CInput
                  :disabled="user.role != 'admin'"
                  label="Name"
                  required
                  :value.sync="name"
                />
              </CCol>
              <CCol md="6" v-if="$route.name == 'Edit Profiles'">
                <CInput
                  :disabled="user.role != 'admin'"
                  label="NRIC"
                  required
                  :value.sync="nric_uen"
                />
              </CCol>
              <CCol md="6" v-if="$route.name == 'Edit Profiles'">
                <CInput
                  :disabled="user.role != 'admin'"
                  label="Date of birth"
                  required
                  type="date"
                  :value.sync="date_of_birth"
                />
              </CCol>
              <CCol md="6" v-if="$route.name == 'Edit Profiles'">
                <CInput
                  :disabled="user.role != 'admin'"
                  label="Mobile Number"
                  required
                  :value.sync="mobile_number"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  :disabled="user.role != 'admin'"
                  label="Email"
                  type="email"
                  required
                  :value.sync="email"
                />
              </CCol>
              <CCol md="6">
                <CSelect
                  :disabled="user.role != 'admin'"
                  label="Status"
                  :value.sync="status"
                  :options="activeOptions"
                  placeholder="Please select"
                />
              </CCol>
            </CRow>
            <div class="form-actions" v-if="user.role == 'admin'">
              <CButton type="submit" color="primary">Save changes</CButton>
              <CButton color="secondary" @click="cancel()">Cancel</CButton>
            </div>
          </CCardBody>
        </CCard>
        <div
          v-if="
            user.category == 'all_cars' &&
            user.role == 'admin' &&
            $route.name == 'Edit Profiles'
          "
        >
          <data-table
            ref="dataTable"
            :perPage="perPage"
            :url="url"
            :columns="columns"
            :headers="headers"
          >
          </data-table>
        </div>
      </CCol>
    </CRow>
  </form>
</template>
<script>
import { mapGetters } from "vuex";
export default {
  data() {
    return {
      perPage: [10, 20, 30, 40, 50],

      details: [],
      name: "",
      url: "",
      email: "",
      nric_uen: "",
      date_of_birth: "",
      mobile_number: "",
      data: {},
      status: "",
      activeOptions: [
        { label: "Active", value: "active" },
        { label: "Inactive", value: "inactive" },
      ],
      columns: [
        {
          label: "Car Plate",
          name: "vehicle.registration_no",
          orderable: true,
        },
        {
          label: "Vehicle Make",
          name: "vehicle.make",
          orderable: true,
        },
        {
          label: "Vehicle Model",
          name: "vehicle.model",
          orderable: true,
        },
        {
          label: "COE Expiry",
          name: "vehicle.coe_expiry_date",
          orderable: true,
        },
        {
          label: "Road Tax Expiry",
          name: "vehicle.tax_expiry_date",
          orderable: true,
        },
        {
          label: "Warranty Validity",
          name: "vehicle.warranty",
          orderable: true,
        },
        {
          label: "Insurance Validity",
          name: "vehicle.type",
          orderable: true,
        },
        {
          label: "Last Servicing",
          name: "appointment_date",
          orderable: true,
        },
      ],

      headers: {
        Authorization: window.axios.defaults.headers.common["Authorization"],
      },
    };
  },
  mounted() {
    this.$refs.dataTable.$children[0].tableData.length = 20;

    var id = window.location.pathname.split("/").pop();
    if (this.$route.name == "Edit Profiles") {
      var inputs = {};
      inputs.method = "get";
      inputs.id = id;
      inputs.url = "/api/customers/" + inputs.id;
      this.$store.dispatch("API", inputs).then((data) => {
        this.data = data.vehicles;
        this.details = data;
        this.name = data.name;
        this.email = data.email;
        this.status = data.status;
        this.nric_uen = data.nric_uen;
        this.date_of_birth = data.date_of_birth;
        this.mobile_number = data.phone;
      });
      this.url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/vehicles/customer/" +
        id;
    }
  },
  watch: {
    user: function (val) {},
  },
  methods: {
    save() {
      // { value: 'Option2', label: 'Custom label'}
      var inputs = {};
      inputs.name = this.name;
      inputs.email = this.email;
      inputs.method = "post";
      inputs.company_id = this.company;
      inputs.status = this.status;
      if (this.$route.name == "Edit Profiles") {
        inputs.nric_uen = this.nric_uen;
        inputs.date_of_birth = this.date_of_birth;
        inputs.phone = this.mobile_number;
        inputs.id = window.location.pathname.split("/").pop();
        inputs.url = "/api/customers/" + inputs.id + "/update";
        inputs.method = "put";
      } else {
        inputs.url = "/api/customers/create";
      }
      this.$store.dispatch("API", inputs).then(() => {
        this.$router.push("/customers");
      });
    },
    cancel() {
      this.$router.go(-1);
    },
  },
  computed: {
    ...mapGetters(["user"]),
  },
};
</script>
<style scoped>
.value-content {
  color: #007bff;
  margin: 4px 0px;
  padding-left: 27px;
  height: 30px;
}
.top-buffer {
  margin-top: 20px;
}
</style>
