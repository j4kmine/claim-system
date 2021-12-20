<template>
  <div>
    <CCard v-if="user.company != null">
      <CCardHeader class="font-weight-bold">
        Create Servicing Slots
      </CCardHeader>
      <CCardBody>
        <form @submit.prevent="save()">
          <label class="font-weight-bold">Days Service Available</label>
          <CRow>
            <CCol md="4"> </CCol>
            <CCol md="2">
              <label>Start Time</label>
            </CCol>
            <CCol md="2">
              <label>End Time</label>
            </CCol>
            <CCol md="2">
              <label>Each Session</label>
            </CCol>
            <CCol md="2">
              <label>Per Interval</label>
            </CCol>
          </CRow>
          <CRow v-for="dataForm in dataForms" :key="dataForm.label">
            <CCol md="4">
              <CInputCheckbox
                :label="dataForm.day"
                :checked.sync="dataForm.is_checked"
                :value="dataForm.label"
                v-if="!loading"
              />
              <Loader v-else margin="10px 0px" />
            </CCol>
            <CCol md="2">
              <CInput
                type="time"
                :required="false"
                step="1800"
                placeholder="Start Time"
                :value.sync="dataForm.time_start"
                v-if="!loading"
              />
              <Loader v-else />
            </CCol>
            <CCol md="2">
              <CInput
                type="time"
                :required="false"
                step="1800"
                placeholder="End Time"
                :value.sync="dataForm.time_end"
                v-if="!loading"
              />
              <Loader v-else />
            </CCol>
            <CCol md="2">
              <CSelect
                :options="[30, 60, 90, 120]"
                v-if="!loading"
                :value.sync="dataForm.interval"
              />
              <Loader v-else />
            </CCol>
            <CCol md="2">
              <CInput
                type="number"
                placeholder="10 slots"
                :value.sync="dataForm.slots_per_interval"
                v-if="!loading"
              />
              <Loader v-else />
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
      loading: false,

      dataForms: [
        {
          day: "Monday",
          is_checked: true,
          time_start: "",
          time_end: "",
          interval: "",
          slots_per_interval: "",
        },
        {
          day: "Tuesday",
          is_checked: false,
          time_start: "",
          time_end: "",
          interval: "",
          slots_per_interval: "",
        },
        {
          day: "Wednesday",
          is_checked: false,
          time_start: "",
          time_end: "",
          interval: "",
          slots_per_interval: "",
        },
        {
          day: "Thursday",
          is_checked: false,
          time_start: "",
          time_end: "",
          interval: "",
          slots_per_interval: "",
        },
        {
          day: "Friday",
          is_checked: false,
          time_start: "",
          time_end: "",
          interval: "",
          slots_per_interval: "",
        },
        {
          day: "Saturday",
          is_checked: false,
          time_start: "",
          time_end: "",
          interval: "",
          slots_per_interval: "",
        },
        {
          day: "Sunday",
          is_checked: false,
          time_start: "",
          time_end: "",
          interval: "",
          slots_per_interval: "",
        },
      ],
      nric: "",
      email: "",
      vehicle_number: "",
      car_make: "",
      car_model: "",
      remarks: "",

      role: "",
      category: "",
      company: "",
      status: "",
      servicesOptions: [{ label: "General", value: "general" }],
      carMakeOptions: [{ label: "MERCEDES BENZ", value: "mercedes_benz" }],
      carModelOptions: [{ label: "G350D", value: "g350d" }],

      categoryOptions: [
        { label: "All Cars", value: "all_cars" },
        { label: "Dealer", value: "dealer" },
        { label: "Insurer", value: "insurer" },
        { label: "Surveyor", value: "surveyor" },
        { label: "Workshop", value: "workshop" },
      ],
      roleOptions: [
        { label: "Support Staff", value: "support_staff" },
        { label: "Admin", value: "admin" },
      ],
      dealerRoleOptions: [
        { label: "Salesperson", value: "salesperson" },
        { label: "Admin", value: "admin" },
      ],
      companyOptions: [],
      activeOptions: [
        { label: "Active", value: "active" },
        { label: "Inactive", value: "inactive" },
      ],
    };
  },
  watch: {
    user: function (newUser) {
      if (this.$route.name != "Edit User" && newUser.category != "all_cars") {
        this.category = this.user.category;
        this.company = this.user.company_id;
      }
    },
  },
  methods: {
    save() {
      var filterChecked = this.dataForms.map((el) => {
        if (el.is_checked)
          return {
            ...el,
            status: "active",
          };
        else
          return {
            ...el,
            status: "inactive",
          };
      });
      // { value: 'Option2', label: 'Custom label'}

      filterChecked.url = "/api/companies/slots";
      filterChecked.method = "post";
      this.$store.dispatch("API", filterChecked).then(() => {
        this.$router.push("/companyProfile");
      });
    },
    cancel() {
      this.$router.push("/companyProfile");
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
    inputs.method = "get";
    inputs.url = "/api/companies/slots";
    this.loading = true;
    this.$store.dispatch("API", inputs).then(async (data) => {
      this.dataForms = data;

      this.dataForms = this.dataForms.map((element) => {
        return {
          ...element,
          is_checked: element.status == "active",
        };
      });

      this.loading = false;
    });
  },
  computed: {
    ...mapGetters(["user"]),
  },
};
</script>
