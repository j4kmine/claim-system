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
                                    label="Date"
                                    type="date"
                                    class="font-weight-bold"
                                    max="9999-01-01"
                                    :value.sync="date"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    label="Time"
                                    type="time"
                                    class="font-weight-bold"
                                    :value.sync="time"
                                />
                            </CCol>
                            <CCol md="6" v-if="companyOptions.length > 0">
                                <CSelect
                                    :disabled="this.$route.name == 'Edit Appointment' || this.user.category != 'all_cars'"
                                    label="Workshop"
                                    :value.sync="company"
                                    class="font-weight-bold"
                                    @change="getServiceTypes()"
                                    :options="companyOptions"
                                    placeholder="Please select"
                                />
                            </CCol>
                            <CCol md="6">
                                <CSelect
                                    :disabled="this.$route.name == 'Edit Appointment' || (this.user.category == 'all_cars' && this.company == '')"
                                    label="Services"
                                    class="font-weight-bold"
                                    :value.sync="services"
                                    :options="servicesOptions"
                                    placeholder="Please select"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    label="Customer"
                                    class="font-weight-bold"
                                    :value.sync="customer"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    label="Phone"
                                    class="font-weight-bold"
                                    :value.sync="phone"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    label="NRIC"
                                    class="font-weight-bold"
                                    :value.sync="nric"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    label="Email"
                                    type="email"
                                    class="font-weight-bold"
                                    :value.sync="email"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    label="Vehicle Number"
                                    class="font-weight-bold"
                                    :value.sync="vehicle_number"
                                />
                            </CCol>
                            <CCol md="6">
                                <div role="group" class="form-group">
                                    <strong for="exampleDataList">Vehicle Make</strong>
                                    <input class="form-control" autocomplete="off" v-model="car_make" id="make">
                                </div>
                            </CCol>
                            <CCol md="6">
                                <div role="group" class="form-group">
                                    <strong for="exampleDataList">Vehicle Model</strong>
                                    <input class="form-control" autocomplete="off" v-model="car_model">
                                </div>
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    label="Remarks"
                                    class="font-weight-bold"
                                    :value.sync="remarks"
                                />
                            </CCol>
                        </CRow>
                        <div class="form-actions">
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
      date: "",
      time: "",
      services: "",
      customer: "",
      phone: "",
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
      servicesOptions: [],
      carMakeOptions: [],
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
  mounted() {},
  watch: {
    category: function (newCategory) {
      this.companyOptions = [];
      if (newCategory != null) {
        var inputs = {};
        inputs.method = "post";
        inputs.url = "/api/companies";
        inputs.category = newCategory;
        this.$store.dispatch("API", inputs).then((data) => {
          for (var i = 0; i < data.companies.length; i++) {
            this.companyOptions.push({
              label: data.companies[i].name,
              value: data.companies[i].id,
            });
          }
        });
      } else {
        var inputs = {};
        inputs.url = "/api/workshops";
        this.$store.dispatch("API", inputs).then((data) => {
          this.companyOptions = data.data.map((dataWorkshop) => {
            return { label: dataWorkshop.name, value: dataWorkshop.id };
          });
        });
      }
    },
    user: function (newUser) {
      if (this.$route.name != "Edit User" && newUser.category != "all_cars") {
        this.category = this.user.category;
        this.company = this.user.company_id;

        var inputs = {};
        inputs.url = `/api/workshops/${this.company}/service-types`;
        this.$store.dispatch("API", inputs).then((data) => {
          return (this.servicesOptions = data.map((dataMap) => {
            return { label: dataMap.name, value: dataMap.id };
          }));
        });
      } else {
        this.category = null;
      }
    },
  },
  methods: {
    save() {
      // { value: 'Option2', label: 'Custom label'}
      var inputs = {};
      inputs.date = this.date;
      inputs.time = this.time;
      inputs.workshop_id = this.company;
      inputs.service_type_id = this.services;
      inputs.customer_name = this.customer;
      inputs.phone = this.phone;
      inputs.nric = this.nric;
      inputs.email = this.email;
      inputs.vehicle_number = this.vehicle_number;
      inputs.vehicle_make = this.car_make;
      inputs.vehicle_model = this.car_model;
      inputs.remarks = this.remarks;
      inputs.method = "post";
      inputs.url = "/api/servicing";
      this.$store.dispatch("API", inputs).then(() => {
        this.$router.push("/servicing");
      });
    },
    cancel() {
      this.$router.go(-1);
    },
    getServiceTypes() {
      if (this.user.category == "all_cars") {
        var inputs = {};
        inputs.url = `/api/workshops/${this.company}/service-types`;
        this.$store.dispatch("API", inputs).then((data) => {
          return (this.servicesOptions = data.map((dataMap) => {
            return { label: dataMap.name, value: dataMap.id };
          }));
        });
      }
    },
  },
  computed: {
    ...mapGetters(["user"]),
  },
};
</script>
