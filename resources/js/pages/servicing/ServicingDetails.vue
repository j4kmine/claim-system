<template>
  <div>
    <CNav variant="tabs" class="dashboard-tab">
      <CNavItem :active="tab == 'info'">
        <div @click="changeTab('info')">Info</div>
      </CNavItem>
      <CNavItem :active="tab == 'history'">
        <div @click="changeTab('history')">History</div>
      </CNavItem>
    </CNav>

    <template>
      <template v-if="tab == 'info'">
        <form @submit.prevent="submit()">
          <CCard>
            <CCardHeader class="font-weight-bold">
              Servicing Appointment
              <CButton
                class="btn float-right btn-danger btn-sm"
                v-if="user.role == 'admin' && user.category != 'all_cars'"
                @click="cancel('/companyProfile/createUser')"
                >Cancel</CButton
              >
            </CCardHeader>
            <CCardBody>
              <div v-for="dataDetail in detailsData" :key="dataDetail.id">
                <CRow>
                  <CCol md="6" class="font-weight-bold">
                    {{ dataDetail.firstLabel }}
                  </CCol>
                  <CCol md="6" class="font-weight-bold">
                    {{ dataDetail.secondLabel }}
                  </CCol>
                </CRow>
                <CRow>
                  <CCol md="6" class="value-content">
                    {{
                      dataDetail.firstValue === null ||
                      dataDetail.firstValue == ""
                        ? "-"
                        : dataDetail.firstValue
                    }}
                  </CCol>
                  <CCol md="6" class="value-content">
                    {{
                      dataDetail.secondValue === null ||
                      dataDetail.secondValue == ""
                        ? "-"
                        : dataDetail.secondValue
                    }}
                  </CCol>
                </CRow>
              </div>
              <CRow>
                <CCol md="6" class="font-weight-bold">Remarks</CCol>
                <CCol md="6" class="font-weight-bold">Status</CCol>
              </CRow>
              <CRow>
                <CCol md="6" class="value-content">
                  {{
                    details.remarks === null || details.remarks == ""
                      ? "-"
                      : details.remarks
                  }}</CCol
                >
                <CCol md="6" style="margin: 4px 0px; height: 24px">
                  <CSelect
                    :disabled="
                      user.category != 'workshop' ||
                      details.status == 'completed' ||
                      details.status == 'cancelled'
                    "
                    label=""
                    :value.sync="statusValue"
                    :options="statusOptions"
                    placeholder="Please select"
                  />
                </CCol>
              </CRow>
              <div
                v-if="user.category == 'workshop' && details.status == 'open'"
              >
                <DDFileUpload
                  name="documents"
                  label="Service Report"
                  type="documents"
                  :url="`/api/servicing/${id}/reports/documents`"
                  ref="documents"
                  withValidation
                  @remove-action="removeFile($event, 'documents')"
                />

                <DDFileUpload
                  name="invoices"
                  label="Invoices"
                  type="invoices"
                  :url="`/api/servicing/${id}/reports/invoices`"
                  ref="invoices"
                  withValidation
                  @remove-action="removeFile($event, 'invoices')"
                />

                <CRow>
                  <CCol
                    md="10"
                    class="font-weight-bold"
                    style="margin-bottom: 1rem"
                  >
                    <span style="margin-left: 1rem">Total Invoice Amount</span>
                  </CCol>
                  <CCol md="2">
                    <CInput
                      label=""
                      type="number"
                      step=".01"
                      :value.sync="total_amount"
                    >
                      <template #prepend-content
                        ><CIcon name="cil-dollar"
                      /></template>
                    </CInput>
                  </CCol>
                </CRow>
                <CRow>
                  <CCol md="12">
                    <CTextarea
                      class="font-weight-bold"
                      label="Workshop Remarks"
                      :value.sync="workshop_remarks"
                    ></CTextarea>
                  </CCol>
                </CRow>
              </div>
            </CCardBody>
          </CCard>
          <CCard
            v-if="
              user.role == 'admin' &&
              (user.category == 'all_cars' || user.category == 'workshop') &&
              details.status == 'completed'
            "
          >
            <CCardHeader class="font-weight-bold"> Documents </CCardHeader>
            <CCardBody>
              <CRow>
                <CCol md="12" class="font-weight-bold">Servicing Report</CCol>
                <div v-if="completed_documents.length > 0">
                  <CCol
                    md="12"
                    class="value-content"
                    v-for="completed_document in completed_documents"
                    :key="completed_document.id"
                  >
                    <a :href="`${completed_document.view}`" target="_blank">{{
                      completed_document.label
                    }}</a>
                  </CCol>
                </div>
                <div v-else>
                  <CCol md="12" class="value-content">-</CCol>
                </div>
                <CCol md="12" class="font-weight-bold">Invoice</CCol>
                <div v-if="completed_invoices.length > 0">
                  <CCol
                    md="12"
                    class="value-content"
                    v-for="completed_invoice in completed_invoices"
                    :key="completed_invoice.id"
                  >
                    <a :href="`${completed_invoice.view}`" target="_blank">{{
                      completed_invoice.label
                    }}</a>
                  </CCol>
                </div>
                <div v-else>
                  <CCol md="12" class="value-content">-</CCol>
                </div>
                <CCol md="12" class="font-weight-bold">Total Amount</CCol>
                <div v-if="completed_total_amounts.length > 0">
                  <CCol
                    md="12"
                    class="value-content"
                    v-for="completed_total_amount in completed_total_amounts"
                    :key="completed_total_amount"
                  >
                    {{
                      completed_total_amount == "0" ||
                      completed_total_amount == ""
                        ? "TBD"
                        : completed_total_amount | toCurrency
                    }}
                  </CCol>
                </div>
                <div v-else>
                  <CCol md="12" class="value-content">-</CCol>
                </div>
                <CCol md="12" class="font-weight-bold">Workshop Remarks</CCol>
                <CCol
                  md="12"
                  class="value-content"
                  v-for="completed_workshop_remark in completed_workshop_remarks"
                  :key="completed_workshop_remark"
                >
                  {{ completed_workshop_remark }}
                </CCol>
                <CCol md="12" class="font-weight-bold">All Cars Remarks</CCol>
                <CCol
                  md="12"
                  class="value-content"
                  v-for="completed_all_cars_remark in completed_all_cars_remarks"
                  :key="completed_all_cars_remark"
                >
                  {{ completed_all_cars_remark }}
                </CCol>
              </CRow>
            </CCardBody>
          </CCard>
          <CRow>
            <CCol
              md="12"
              class="font-weight-bold"
              v-if="
                user.role == 'admin' &&
                user.category == 'all_cars' &&
                details.status == 'completed'
              "
            >
              <CTextarea
                label="Allcars Remarks"
                :value.sync="all_cars_remarks"
              ></CTextarea>
            </CCol>
            <CCol
              md="12"
              style="margin-bottom: 1rem"
              v-if="
                (user.category == 'all_cars' &&
                  details.status == 'completed' &&
                  user.role == 'admin') ||
                (user.category == 'workshop' && details.status != 'completed')
              "
            >
              <div class="form-actions text-center">
                <CButton type="submit" color="primary">Save changes</CButton>
                <CButton color="secondary" @click="backButton()">
                  Cancel
                </CButton>
              </div>
            </CCol>
          </CRow>
        </form>
      </template>
      <template v-else>
        <ServiceHistory :id="id"></ServiceHistory>
      </template>
    </template>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
export default {
  props: ["companyId", "companyUsers"],
  data() {
    return {
      tab: "info",
      id: "",
      service_report_documents: [],
      service_report_invoices: [],
      role: "",
      total_amount: "0",
      multiple: true,
      file_paths: [],
      all_cars_remarks: "",
      workshop_remarks: "",
      category: "",
      completed_documents: [],
      completed_invoices: [],
      completed_total_amounts: [],
      completed_workshop_remarks: [],
      completed_all_cars_remarks: [],
      documents_web: true,
      invoices_web: true,
      company: "",
      statusValue: "",
      statusOptions: [
        { label: "Open", value: "open" },
        { label: "Completed", value: "completed" },
        { label: "Cancelled", value: "cancelled" },
      ],
      details: "",
    };
  },
  watch: {
    user: function (newUser) {
      if (
        this.user.category == "all_cars" ||
        this.user.category == "workshop"
      ) {
        this.id = window.location.pathname.split("/").pop();
        var inputs = {};
        inputs.method = "get";
        inputs.url = "/api/servicing/" + this.id;
        this.$store.dispatch("API", inputs).then((data) => {
          this.details = data;
          this.statusValue = data.status;
          const documents = data.servicing_reports[0].documents;
          const invoices = data.servicing_reports[0].invoices;
          setTimeout(() => {
            for (var i = 0; i < documents.length; i++) {
              this.$refs.documents.$data.files.push(documents[i]);
            }

            for (var i = 0; i < invoices.length; i++) {
              this.$refs.invoices.$data.files.push(invoices[i]);
            }
          }, 500);
          if (data.status == "completed") {
            data.servicing_reports.map((servicingReports) => {
              this.completed_documents = servicingReports.documents.map(
                (reportsDocument) => {
                  return {
                    id: reportsDocument.id,
                    label: reportsDocument.name,
                    url: reportsDocument.url,
                    view: reportsDocument.view,
                  };
                }
              );
              this.completed_invoices = servicingReports.invoices.map(
                (reportInvoice) => {
                  return {
                    id: reportInvoice.id,
                    label: reportInvoice.name,
                    url: reportInvoice.url,
                    view: reportInvoice.view,
                  };
                }
              );
              this.total_amount = servicingReports.total_amount;
              this.workshop_remarks = servicingReports.workshop_remarks;
              this.completed_total_amounts.push(servicingReports.total_amount);
              this.completed_workshop_remarks.push(
                servicingReports.workshop_remarks
              );
              this.completed_all_cars_remarks.push(
                servicingReports.all_cars_remarks
              );
            });
          }
        });

        if (this.$route.name != "Edit User" && newUser.category != "all_cars") {
          this.category = this.user.category;
          this.company = this.user.company_id;
        }
      } else {
        // redirect to dashboard if not allowed
        this.$router.push("/dashboard");
      }
    },
  },
  methods: {
    changeTab(tab) {
      this.tab = tab;
    },
    removeFile({ file, file_paths }, type) {
      if (typeof file.id == "number") {
        this.$store.dispatch("API", {
          url: `/api/servicing-reports/${type}/${file.id}`,
          method: "delete",
        });
      } else {
        const idFile = file_paths.find((a) => a.name == file.name).id;

        this.$store.dispatch("API", {
          url: `/api/servicing-reports/${type}/${idFile}`,
          method: "delete",
        });
      }
    },
    addFileToDocuments(ref, inputs) {
      // check ref
      if (this.$refs[ref]) {
        const data = this.$refs[ref].$data;

        for (var i = 0; i < data.file_paths.length; i++) {
          if (data.file_paths[i] != null) {
            for (var z = 0; z < data.files.length; z++) {
              if (data.files[z].id == data.file_paths[i].id) {
                inputs.documents.push(data.file_paths[i]);
              }
            }
          }
        }

        for (var z = 0; z < data.files.length; z++) {
          if (data.files[z].blob == null) {
            inputs.documents.push(data.files[z]);
          }
        }
      }
    },

    formatInput() {
      var inputs = {};
      inputs.servicing_status = this.statusValue;
      inputs.total_amount = this.total_amount;
      inputs.remarks = this.workshop_remarks;
      inputs.documents = [];
      inputs.invoices = [];

      this.addFileToDocuments("documents", inputs);
      this.addFileToDocuments("invoices", inputs);

      if (this.details.status == "completed") {
        var inputs = {};
        inputs.all_cars_remarks = this.all_cars_remarks;
      }
      inputs.method = "post";

      return inputs;
    },
    submit() {
      if (this.statusValue == "upcoming") {
        return Vue.toasted.error("Servicing status has to be Open");
      }
      var inputs = this.formatInput();
      if (this.id != null) {
        inputs.id = this.id;
      }
      inputs.documents_web = this.documents_web;
      inputs.invoices_web = this.invoices_web;
      inputs.servicing_status = this.statusValue;
      inputs.url = `/api/servicing/${this.id}/reports`;
      this.$store.dispatch("API", inputs).then(() => {
        this.$router.push("/servicing");
      });
    },
    cancel() {
      var inputs = {};
      inputs.status = "cancelled";
      inputs.method = "put";
      inputs.url = `/api/servicing/${this.id}/update`;
      this.$store.dispatch("API", inputs).then(() => {
        this.$router.push("/servicing");
      });
    },
    backButton() {
      this.$router.go(-1);
    },
    bytesToSize(bytes) {
      var sizes = ["Bytes", "KB", "MB", "GB", "TB"];
      if (bytes == 0) return "0 Byte";
      var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
      return Math.round(bytes / Math.pow(1024, i), 2) + " " + sizes[i];
    },
  },
  mounted() {},
  computed: {
    ...mapGetters(["user"]),
    detailsData() {
      return [
        {
          firstLabel: "Created At",
          firstValue: this.details.format_created_at,
          secondLabel: "Appointment Date & Time",
          secondValue:
            this.details.format_appointment_date +
            " " +
            this.details.format_appointment_time,
        },
        {
          firstLabel: "Workshop",
          firstValue: this.details.workshop ? this.details.workshop.name : "-",
          secondLabel: "Service",
          secondValue: this.details.service_type
            ? this.details.service_type.name
            : "-",
        },
        {
          firstLabel: "Customer",
          firstValue: this.details.customer ? this.details.customer.name : "-",
          secondLabel: "Phone",
          secondValue: this.details.customer
            ? this.details.customer.phone
            : "-",
        },
        {
          firstLabel: "NRIC",
          firstValue: this.details.customer
            ? this.details.customer.nric_uen
            : "-",
          secondLabel: "Email",
          secondValue: this.details.customer
            ? this.details.customer.email
            : "-",
        },
        {
          firstLabel: "Vehicle Number",
          firstValue: this.details.vehicle
            ? this.details.vehicle.registration_no
            : "-",
          secondLabel: "Car Make & Model",
          secondValue: this.details.vehicle
            ? `${this.details.vehicle.make} - ${this.details.vehicle.model}`
            : "-",
        },
      ];
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
