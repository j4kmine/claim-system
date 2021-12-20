<template>
  <div>
    <CCard v-if="user.company != null">
      <CCardHeader class="font-weight-bold">
        Users
        <CButton
          class="btn float-right btn-primary btn-sm"
          @click="create('/companyProfile/createUser')"
        >
          <CIcon name="cil-plus"></CIcon> New User
        </CButton>
      </CCardHeader>
      <CCardBody>
        <CRow>
          <CCol md="12">
            <data-table
              :filters="filterData"
              @row-clicked="displayRow"
              :columns="columns"
              order-by="id"
              order-dir="desc"
              :url="url"
              class="companyprofile-datatable"
              :classes="classes"
              :headers="headers"
            >
            </data-table>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>

    <CCard v-if="user.role == 'admin' && user.category == 'workshop'">
      <CCardHeader class="font-weight-bold">
        Servicing Slots
        <CButton
          class="btn float-right btn-primary btn-sm"
          @click="create('/companyProfile/createServicingSlots')"
        >
          <CIcon name="cil-plus"></CIcon> New Service Slots
        </CButton>
      </CCardHeader>
      <CCardBody>
        <CRow>
          <CCol md="12">
            <BaseTable
              :url="servicing_slots_url"
              :headers="servicingSlotHeaders"
              @row-clicked="create('/companyProfile/createServicingSlots')"
            />
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>

    <CCard v-if="user.role == 'admin' && user.category == 'workshop'">
      <CCardHeader class="font-weight-bold">
        Types of Servicing
        <CButton
          class="btn float-right btn-primary btn-sm"
          @click="create('/companyProfile/createServiceTypes')"
        >
          <CIcon name="cil-plus"></CIcon> New Service
        </CButton>
      </CCardHeader>
      <CCardBody>
        <CRow>
          <CCol md="12">
            <data-table
              :filters="filterData"
              @row-clicked="displayTypesOfServicing"
              :columns="type_service"
              order-by="id"
              class="companyprofile-datatable"
              order-dir="desc"
              :url="type_service_url"
              :classes="classes"
              :headers="headers"
            >
            </data-table>
          </CCol>
        </CRow>
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
      servicingSlotHeaders: [
        { text: "Day", value: "day" },
        { text: "Time Start", value: "time_start" },
        { text: "Time End", value: "time_end" },
        { text: "Interval", value: "interval" },
        { text: "Slot Per Interval", value: "slots_per_interval" },
        { text: "Total Slot", value: "total_slot" },
      ],
      filterData: {
        search: 1,
        length: 20,
      },
      url: "",
      servicing_slots_url: "/api/companies/slots?active=1",
      type_service_url: "",
      classes: {
        table: {
          "table-striped": false,
        },
      },
      headers: {
        Authorization: window.axios.defaults.headers.common["Authorization"],
      },
      columns: [
        {
          label: "User",
          name: "name",
          orderable: true,
          component: "UserCol",
        },
        {
          label: "Role",
          name: "role",
          orderable: true,
          component: "RoleCol",
        },
        {
          label: "Status",
          name: "status",
          orderable: true,
          component: "StatusCol",
        },
      ],
      type_service: [
        {
          label: "Services",
          name: "name",
          orderable: true,
        },
        {
          label: "Color",
          name: "color",
          component: "ColorCol",
        },
        {
          label: "Status",
          name: "status",
          orderable: true,
          component: "StatusCol",
        },
      ],
    };
  },
  watch: {
    user: function (newVal) {
      if (
        this.user.category == "all_cars" ||
        this.user.role == "support_staff" ||
        this.user.role == "salesperson"
      ) {
        this.$router.push("/dashboard");
      }
    },
  },
  methods: {
    displayRow(data) {
      this.$router.push({ path: "/users/edit/" + data.id });
    },
    displayTypesOfServicing(data) {
      this.$router.push({
        path: "/companyProfile/editServiceTypes/" + data.id,
      });
    },
    create(model) {
      this.$router.push(model);
    },
  },
  mounted() {
    if (this.companyId == null) {
      this.url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/companies/users";
      this.type_service_url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/service-types";
    } else {
      this.url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/companies/users/" +
        this.companyId;
      this.type_service_url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/service-types";
    }
  },
  computed: {
    ...mapGetters(["user"]),
  },
};
</script>
<style scoped>
.companyprofile-datatable >>> .mb-3 {
  display: none;
}
</style>
