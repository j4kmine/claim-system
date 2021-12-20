<template>
  <div>
    <CRow>
      <CCol md="3">
        <CSelect label="Status" :value.sync="status" :options="statusOptions" />
      </CCol>
      <CCol md="3">
        <CSelect
          v-if="
            user.category == 'all_cars' ||
            user.category == 'insurer' ||
            user.category == 'dealer'
          "
          label="Report Type"
          :value.sync="role"
          :options="roleOptions"
        />
      </CCol>
      <CCol md="3">
        <CSelect
          v-if="role == 'warrantyGeneral'"
          label="Vehicle Type"
          :value.sync="vehicleType"
          :options="vehicleTypeOptions"
        />
      </CCol>
    </CRow>
    <CRow>
      <CCol md="3">
        <CSelect label="Date Type" :value.sync="type" :options="typeOptions" />
      </CCol>
      <CCol md="3">
        <CInput
          label="From"
          type="date"
          name="from_date"
          :value.sync="fromDate"
        />
      </CCol>
      <CCol md="3">
        <CInput label="To" type="date" name="to_date" :value.sync="toDate" />
      </CCol>
      <CCol md="3">
        <CButton class="export-btn" color="success" @click="exportReport()"
          >Export</CButton
        >
      </CCol>
    </CRow>
    <CRow>
      <CCol>
        <hr style="margin-bottom: 30px" />
      </CCol>
    </CRow>
    <div v-if="role == 'warrantyGeneral'">
      <ReportsGWarranty
        :status="status"
        :headers="headers"
        :vehicleType="vehicleType"
        :classes="classes"
        :toDate="toDate"
        :fromDate="fromDate"
        :debounceDelay="debounceDelay"
        :dateType="type"
        :key="key"
      >
      </ReportsGWarranty>
    </div>
    <div v-else-if="role == 'motorGeneral'">
      <ReportsGMotor
        :status="status"
        :headers="headers"
        :vehicleType="vehicleType"
        :classes="classes"
        :debounceDelay="debounceDelay"
        :toDate="toDate"
        :fromDate="fromDate"
        :dateType="type"
        :key="key"
      >
      </ReportsGMotor>
    </div>
    <div v-else>
      <data-table
        ref="dataTable"
        class="dataTable"
        :perPage="perPage"
        @row-clicked="displayRow"
        @on-table-props-changed="reloadTable"
        :columns="columns"
        order-by="id"
        :debounce-delay="debounceDelay"
        order-dir="desc"
        :data="data"
        :classes="classes"
        :key="key"
        :headers="headers"
        addFiltersToUrl
        :tableProps="tableProps"
      >
      </data-table>
    </div>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
export default {
  data() {
    return {
      perPage: [10, 20, 30, 40, 50],
      debounceDelay: 250,
      key: 0,
      url: "",
      type: "created_at",
      role: "rest",
      vehicleType: "new",
      fromDate: "",
      toDate: "",
      status: "",
      data: {},
      tableProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
      },
      classes: {
        table: {
          "table-striped": false,
        },
      },
      headers: {
        Authorization: window.axios.defaults.headers.common["Authorization"],
      },
      columns: [],
      restColumns: [
        {
          label: "Ref No.",
          name: "ref_no",
          orderable: true,
        },
        {
          label: "Car Plate",
          name: "vehicle.registration_no",
          columnName: "vehicles.registration_no",
          orderable: true,
        },
        {
          label: "Chassis No.",
          name: "vehicle.chassis_no",
          columnName: "vehicles.chassis_no",
          orderable: true,
        },
        {
          label: "Insurer",
          name: "company.name",
          columnName: "companies.name",
          orderable: true,
        },
        {
          label: "Policy Certificate No.",
          name: "policy_certificate_no",
          orderable: true,
        },
        {
          label: "Claim Item (1)",
          name: "items",
          component: "ItemCol",
        },
        {
          label: "Total Claim Amount",
          name: "total_claim_amount",
          orderable: true,
        },
        {
          label: "Date of Loss",
          name: "date_of_loss",
          orderable: true,
        },
        {
          label: "Creation Date",
          name: "created_at",
          orderable: true,
        },
        {
          // Pending Confirmation
          label: "Claim Approval Date",
          name: "approved_at",
          orderable: true,
        },
        {
          // Pending Verification
          label: "Repair Completion Date",
          name: "repaired_at",
          orderable: true,
        },
        {
          label: "Status",
          name: "status",
          orderable: true,
          component: "StatusCol",
        },
      ],
      surveyorColumns: [
        {
          label: "Ref No.",
          name: "ref_no",
          orderable: true,
        },
        {
          label: "Car Plate",
          name: "vehicle.registration_no",
          columnName: "vehicles.registration_no",
          orderable: true,
        },
        {
          label: "Chassis No.",
          name: "vehicle.chassis_no",
          columnName: "vehicles.chassis_no",
          orderable: true,
        },
        {
          label: "Insurer",
          name: "company.name",
          columnName: "companies.name",
          orderable: true,
        },
        {
          label: "Policy Certificate No.",
          name: "policy_certificate_no",
          orderable: true,
        },
        {
          label: "No. of Reviews",
          name: "surveyor_review_count",
          orderable: true,
        },
        {
          label: "Status",
          name: "status",
          orderable: true,
          component: "StatusCol",
        },
      ],
      roleOptions: [
        { label: "Claims Report - General", value: "rest" },
        { label: "Claims Report - Surveyor", value: "surveyor" },
        { label: "Warranty Report - General", value: "warrantyGeneral" },
        { label: "Motor Report - General", value: "motorGeneral" },
      ],
      vehicleTypeOptions: [
        { label: "New", value: "new" },
        { label: "Preowned", value: "preowned" },
      ],
      typeOptions: [
        { label: "Created", value: "created_at" },
        { label: "Approved", value: "approved_at" },
        { label: "Repaired", value: "repaired_at" },
      ],
      statusOptions: [
        { label: "All", value: "" },
        { label: "Draft", value: "draft" },
        { label: "Surveyor Review", value: "surveyor_review" },
        { label: "Insurer Review", value: "insurer_review" },
        { label: "AllCars Review", value: "allCars_review" },
        { label: "Doc Verification", value: "doc_verification" },
        { label: "Pending Confirmation", value: "confirmation" },
        { label: "Pending Verification", value: "verification" },
        { label: "Pending Payment", value: "pending_payment" },
        { label: "Completed", value: "completed" },
      ],
    };
  },
  mounted() {
    setTimeout(() => {
      document.getElementsByClassName(
        "dataTable"
      )[0].children[0].children[0].children[0].selectedIndex = 1;
      this.$refs.dataTable.tableProps.length = 20;
    }, 100);

    if (this.user != null) {
      if (this.user.category == "surveyor") {
        this.role = "surveyor";
        this.columns = this.surveyorColumns;
        this.key += 1;
      } else {
        this.role = "rest";
        this.columns = this.restColumns;
        this.key += 1;
      }
    }
    var getPage = JSON.parse(localStorage.getItem("reportsMeta"));
    if (getPage != null) {
      if (getPage.page != 1) {
        this.tableProps = getPage;
        this.url =
          window.location.protocol +
          "//" +
          window.location.hostname +
          "/api/reports";
        this.getData(this.url, this.tableProps);
      } else {
        this.url =
          window.location.protocol +
          "//" +
          window.location.hostname +
          "/api/reports";
        this.getData(this.url);
      }
    } else {
      this.url =
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/reports";
      this.getData(this.url);
    }
  },
  watch: {
    user: function (val) {
      if (
        this.user.category == "all_cars" ||
        (this.user.category == "dealer" && this.user.role == "admin") ||
        (this.user.category == "insurer" && this.user.role == "admin") ||
        (this.user.category == "surveyor" && this.user.role == "admin") ||
        (this.user.category == "workshop" && this.user.role == "admin")
      ) {
        if (val.category == "surveyor") {
          this.role = "surveyor";
        } else {
          this.role = "rest";
        }
      } else {
        this.$router.push("/dashboard");
      }
    },
    status(val) {
      var inputs = this.tableProps;
      inputs.type = this.type;
      inputs.role = this.role;
      inputs.fromDate = this.fromDate;
      inputs.toDate = this.toDate;
      inputs.status = val;
      this.key += 1;
      this.getData(this.url, inputs);
    },
    type(val) {
      var inputs = this.tableProps;
      inputs.type = val;
      inputs.role = this.role;
      inputs.fromDate = this.fromDate;
      inputs.toDate = this.toDate;
      inputs.status = this.status;
      this.getData(this.url, inputs);
    },
    role(val) {
      var inputs = this.tableProps;
      inputs.type = this.type;
      inputs.role = val;
      inputs.fromDate = this.fromDate;
      inputs.toDate = this.toDate;
      inputs.status = this.status;
      if (val == "surveyor") {
        this.columns = this.surveyorColumns;
      } else if (val == "rest") {
        this.columns = this.restColumns;
      } else if (val == "warrantyGeneral") {
        this.statusOptions = [
          { label: "All", value: "" },
          { label: "Pending", value: "pending" },
          { label: "Completed", value: "completed" },
        ];
        this.typeOptions = [{ label: "Created", value: "created_at" }];
      } else if (val == "motorGeneral") {
        this.statusOptions = [
          { label: "All", value: "" },
          { label: "Open", value: "open" },
          { label: "Draft", value: "draft" },
          { label: "Pending Enquiry", value: "pending_enquiry" },
          { label: "Pending Acceptance", value: "pending_acceptance" },
          { label: "Pending Admin Review", value: "pending_admin_review" },
          { label: "Pending Log Card", value: "pending_log_card" },
          { label: "Pending CI", value: "pending_ci" },
          { label: "Completed", value: "completed" },
        ];
        this.typeOptions = [{ label: "Created", value: "created_at" }];
      }
      this.getData(this.url, inputs);
    },
    vehicleType(val) {
      this.vehicleType = val;
      this.key += 1;
    },
    fromDate(val) {
      var inputs = this.tableProps;
      inputs.type = this.type;
      inputs.role = this.role;
      inputs.fromDate = val;
      inputs.toDate = this.toDate;
      inputs.status = this.status;
      this.key += 1;
      this.getData(this.url, inputs);
    },
    toDate(val) {
      var inputs = this.tableProps;
      inputs.type = this.type;
      inputs.role = this.role;
      inputs.fromDate = this.fromDate;
      inputs.toDate = val;
      inputs.status = this.status;
      this.key += 1;
      this.getData(this.url, inputs);
    },
  },
  methods: {
    displayRow(data) {
      this.$router.push({ path: "/claims/details/" + data.id });
    },
    getData(url = this.url, options = this.tableProps) {
      axios
        .get(url, {
          params: options,
        })
        .then((response) => {
          // this.key +=1;
          this.data = response.data;
        })
        .catch((errors) => {});
    },
    reloadTable(tableProps) {
      localStorage.setItem("reportsMeta", JSON.stringify(tableProps));
      this.getData(this.url, tableProps);
    },
    exportReport() {
      var url = "/api/reports/export";
      if (this.role == "warrantyGeneral") {
        url = "/api/reports-warranties/export";
      } else if (this.role == "motorGeneral") {
        url = "/api/reports-motors/export";
      }
      axios({
        method: "post",
        url: url,
        responseType: "arraybuffer",
        data: {
          role: this.role,
          vehicleType: this.vehicleType,
          type: this.type,
          fromDate: this.fromDate,
          toDate: this.toDate,
          status: this.status,
        },
      }).then((response) => {
        var today = new Date();
        var date =
          today.getFullYear() +
          "-" +
          ("0" + (today.getMonth() + 1)).slice(-2) +
          "-" +
          ("0" + today.getDate()).slice(-2);
        var status = this.status == "" ? "all" : this.status;
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", `report_${date}.xlsx`); //or any other extension
        document.body.appendChild(link);
        link.click();
      });
    },
  },
  computed: {
    ...mapGetters(["user"]),
  },
};
</script>
