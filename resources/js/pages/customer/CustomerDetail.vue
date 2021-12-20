<template>
  <form @submit.prevent="save()">
    <CRow>
      <CCol lg="12">
        <CCard>
          <CCardHeader class="font-weight-bold">
            Personal Info
            <CButton
              class="btn float-right btn-primary btn-sm"
              @click="goToEdit()"
              >Edit</CButton
            >
          </CCardHeader>
          <CCardBody>
            <div v-for="personalInfo in personalsInfo" :key="personalInfo.id">
              <CRow>
                <CCol md="6" class="font-weight-bold">
                  {{ personalInfo.fLabel }}
                </CCol>
                <CCol md="6" class="font-weight-bold">
                  {{ personalInfo.sLabel }}
                </CCol>
              </CRow>
              <CRow>
                <CCol md="6" class="value-content">
                  {{
                    personalInfo.fValue === null || personalInfo.fValue == ""
                      ? "-"
                      : personalInfo.fValue
                  }}
                </CCol>
                <CCol md="6" class="value-content">
                  {{
                    personalInfo.sValue === null || personalInfo.sValue == ""
                      ? "-"
                      : personalInfo.sValue
                  }}
                </CCol>
              </CRow>
            </div>
            <CRow>
              <CCol md="6">
                <CSelect
                  :disabled="
                    user.role == 'admin' && user.category == 'all_cars'
                      ? false
                      : true
                  "
                  label="Status"
                  :value.sync="details.status"
                  :options="activeOptions"
                  placeholder="Please select"
                />
              </CCol>
            </CRow>

            <div
              class="form-actions"
              v-if="user.role == 'admin' && user.category == 'all_cars'"
            >
              <CButton type="submit" color="primary">Save changes</CButton>
              <CButton color="secondary" @click="cancel()">Cancel</CButton>
            </div>
          </CCardBody>
        </CCard>
        <CCard>
          <CCardHeader class="font-weight-bold">Contact</CCardHeader>
          <CCardBody>
            <CRow>
              <CCol md="6" class="font-weight-bold">Mobile Number</CCol>
              <CCol md="6" class="font-weight-bold">Email Address</CCol>
            </CRow>
            <CRow>
              <CCol md="6" class="value-content">{{
                details.phone === null || details.phone == ""
                  ? "-"
                  : details.phone
              }}</CCol>
              <CCol md="6" class="value-content">{{
                details.email === null || details.email == ""
                  ? "-"
                  : details.email
              }}</CCol>
            </CRow>
          </CCardBody>
        </CCard>
        <CCard>
          <CCardHeader class="font-weight-bold">Driving License</CCardHeader>
          <CCardBody>
            <CRow>
              <CCol md="6" class="font-weight-bold"
                >Qualified Driving License Validity</CCol
              >
              <CCol md="6" class="font-weight-bold"
                >Qualified Driving License Class</CCol
              >
            </CRow>
            <CRow>
              <CCol md="6" class="value-content">{{
                details.driving_license_validity === null ||
                details.driving_license_validity == ""
                  ? "-"
                  : details.driving_license_validity
              }}</CCol>
              <CCol md="6" class="value-content">{{
                details.driving_license_class === null ||
                details.driving_license_class
                  ? "-"
                  : details.driving_license_class
              }}</CCol>
            </CRow>
          </CCardBody>
        </CCard>
        <CCard>
          <CCardHeader class="font-weight-bold">Vehicle Info</CCardHeader>
          <CCardBody>
            <data-table
              ref="dataTable"
              :perPage="perPage"
              :url="url"
              :columns="columns"
              :headers="headers"
            >
            </data-table>
          </CCardBody>
        </CCard>
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
      id: "",
      email: "",
      url: "",
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
      activeOptions: [
        { label: "Active", value: "active" },
        { label: "Inactive", value: "inactive" },
      ],
    };
  },
  mounted() {
    this.$refs.dataTable.$children[0].tableData.length = 20;

    var id = window.location.pathname.split("/").pop();
    this.id = id;
    var inputs = {};
    inputs.method = "get";
    inputs.id = id;
    inputs.url = "/api/customers/" + inputs.id;
    this.$store.dispatch("API", inputs).then((data) => {
      this.data = data.vehicles;
      this.details = data;
      this.name = data.name;
      this.email = data.email;
    });
    this.url =
      window.location.protocol +
      "//" +
      window.location.hostname +
      "/api/vehicles/customer/" +
      id;
  },
  methods: {
    save() {
      var inputs = {};
      inputs.status = this.details.status;
      inputs.method = "put";
      inputs.id = this.id;
      inputs.url = "/api/customers/" + inputs.id + "/update";
      this.$store.dispatch("API", inputs).then(() => {
        this.$router.push("/customers");
      });
    },
    goToEdit() {
      this.$router.push("/customers/profiles/edit/" + this.id);
    },
  },
  computed: {
    ...mapGetters(["user"]),
    personalsInfo() {
      if (this.details) {
        var details = this.details;
        return [
          {
            fLabel: "NRIC",
            fValue: details.nric_uen,
            sLabel: "Name",
            sValue: details.name,
          },
          {
            fLabel: "Date Of Birth",
            fValue: details.date_of_birth,
            sLabel: "Gender",
            sValue: details.gender,
          },
          {
            fLabel: "Country Of Birth",
            fValue: details.format_residential,
            sLabel: "Nationality",
            sValue: details.format_nationality,
          },
        ];
      } else {
        return [];
      }
    },
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
