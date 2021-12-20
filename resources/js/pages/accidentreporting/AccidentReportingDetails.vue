<template>
  <div>
    <CButton @click="generatePdf()" class="btn-right" color="primary"
      >Download PDF</CButton
    >
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
          <vue-html2pdf
            :show-layout="false"
            :filename="filenamePDF"
            :float-layout="true"
            :paginate-elements-by-height="800"
            :pdf-quality="2"
            ref="html2Pdf"
          >
            <section slot="pdf-content">
              <CCard>
                <CCardHeader class="font-weight-bold">
                  Vehicle Info
                </CCardHeader>
                <CCardBody>
                  <div
                    v-for="vehicleInfo in VehiclesInfo"
                    :key="vehicleInfo.id"
                  >
                    <CRow>
                      <CCol md="6" class="font-weight-bold">
                        {{ vehicleInfo.firstLabel }}
                      </CCol>
                      <CCol md="6" class="font-weight-bold">
                        {{ vehicleInfo.secondLabel }}
                      </CCol>
                    </CRow>
                    <CRow>
                      <CCol md="6" class="value-content">
                        {{ vehicleInfo.firstValue }}
                      </CCol>
                      <CCol md="6" class="value-content">
                        {{ vehicleInfo.secondValue }}
                      </CCol>
                    </CRow>
                  </div>
                </CCardBody>
              </CCard>
              <CCard>
                <CCardHeader class="font-weight-bold"
                  >Accident Info</CCardHeader
                >
                <CCardBody>
                  <div
                    v-for="accidentInfo in AccidentsInfo"
                    :key="accidentInfo.id"
                  >
                    <CRow>
                      <CCol md="6" class="font-weight-bold">
                        {{ accidentInfo.firstLabel }}
                      </CCol>
                      <CCol md="6" class="font-weight-bold">
                        {{ accidentInfo.secondLabel }}
                      </CCol>
                    </CRow>
                    <CRow>
                      <CCol md="6" class="value-content">
                        {{ accidentInfo.firstValue }}
                      </CCol>
                      <CCol md="6" class="value-content">
                        {{ accidentInfo.secondValue }}
                      </CCol>
                    </CRow>
                  </div>
                </CCardBody>
              </CCard>
              <CCard>
                <CCardHeader class="font-weight-bold">Driver Info</CCardHeader>
                <CCardBody>
                  <div v-for="driverInfo in DriversInfo" :key="driverInfo.id">
                    <CRow>
                      <CCol md="6" class="font-weight-bold">
                        {{ driverInfo.firstLabel }}
                      </CCol>
                      <CCol md="6" class="font-weight-bold">
                        {{ driverInfo.secondLabel }}
                      </CCol>
                    </CRow>
                    <CRow>
                      <CCol md="6" class="value-content">
                        {{ driverInfo.firstValue }}
                      </CCol>
                      <CCol md="6" class="value-content">
                        {{ driverInfo.secondValue }}
                      </CCol>
                    </CRow>
                  </div>
                </CCardBody>
              </CCard>
            </section>
          </vue-html2pdf>

          <CCard>
            <CCardHeader class="font-weight-bold"> Vehicle Info </CCardHeader>
            <CCardBody>
              <div v-for="vehicleInfo in VehiclesInfo" :key="vehicleInfo.id">
                <CRow>
                  <CCol md="6" class="font-weight-bold">
                    {{ vehicleInfo.firstLabel }}
                  </CCol>
                  <CCol md="6" class="font-weight-bold">
                    {{ vehicleInfo.secondLabel }}
                  </CCol>
                </CRow>
                <CRow>
                  <CCol md="6" class="value-content">
                    {{ vehicleInfo.firstValue }}
                  </CCol>
                  <CCol md="6" class="value-content">
                    {{ vehicleInfo.secondValue }}
                  </CCol>
                </CRow>
              </div>
            </CCardBody>
          </CCard>
          <CCard>
            <CCardHeader class="font-weight-bold">Accident Info</CCardHeader>
            <CCardBody>
              <div v-for="accidentInfo in AccidentsInfo" :key="accidentInfo.id">
                <CRow>
                  <CCol md="6" class="font-weight-bold">
                    {{ accidentInfo.firstLabel }}
                  </CCol>
                  <CCol md="6" class="font-weight-bold">
                    {{ accidentInfo.secondLabel }}
                  </CCol>
                </CRow>
                <CRow>
                  <CCol md="6" class="value-content">
                    {{ accidentInfo.firstValue }}
                  </CCol>
                  <CCol md="6" class="value-content">
                    {{ accidentInfo.secondValue }}
                  </CCol>
                </CRow>
              </div>
            </CCardBody>
          </CCard>
          <CCard>
            <CCardHeader class="font-weight-bold">Driver Info</CCardHeader>
            <CCardBody>
              <div v-for="driverInfo in DriversInfo" :key="driverInfo.id">
                <CRow>
                  <CCol md="6" class="font-weight-bold">
                    {{ driverInfo.firstLabel }}
                  </CCol>
                  <CCol md="6" class="font-weight-bold">
                    {{ driverInfo.secondLabel }}
                  </CCol>
                </CRow>
                <CRow>
                  <CCol md="6" class="value-content">
                    {{ driverInfo.firstValue }}
                  </CCol>
                  <CCol md="6" class="value-content">
                    {{ driverInfo.secondValue }}
                  </CCol>
                </CRow>
              </div>
            </CCardBody>
          </CCard>
          <CCard>
            <CCardHeader class="font-weight-bold">Accident Photos</CCardHeader>
            <CCardBody>
              <CRow v-for="(item, key) in details.documents" :key="key">
                <CCol md="11" style="color: blue">
                  <ListAccidentPhotos
                    :type="key"
                    :documents="getAccidentByType(key)"
                  ></ListAccidentPhotos>
                </CCol>
                <CCol md="1" class="text-right"> ({{ item.length }}) </CCol>
              </CRow>
            </CCardBody>
          </CCard>
          <CCard>
            <CCardHeader class="font-weight-bold"
              >Servicing Inspection</CCardHeader
            >
            <CCardBody>
              <div v-for="inspection in InspectionsInfo" :key="inspection.id">
                <CRow>
                  <CCol md="6" class="font-weight-bold">
                    {{ inspection.firstLabel }}
                  </CCol>
                  <CCol md="6" class="font-weight-bold">
                    {{ inspection.secondLabel }}
                  </CCol>
                </CRow>
                <CRow>
                  <CCol md="6" class="value-content">
                    {{ inspection.firstValue }}
                  </CCol>
                  <CCol md="6" class="value-content">
                    {{ inspection.secondValue }}
                  </CCol>
                </CRow>
              </div>
              <CRow style="margin-bottom: 2rem">
                <CCol md="6" style="margin: 4px 0px; height: 24px">
                  <CSelect
                    :disabled="true"
                    label="Status"
                    :value.sync="details.status"
                    :options="statusOptions"
                    placeholder="Please select"
                  />
                </CCol>
              </CRow>
            </CCardBody>
          </CCard>
          <CCard>
            <CCardHeader class="font-weight-bold">
              Report & Feedback
            </CCardHeader>
            <CCardBody>
              <CRow
                v-if="
                  user.category == 'all_cars' ||
                  (user.category == 'workshop' && details.status == 'completed')
                "
              >
                <CCol md="12" class="font-weight-bold">Accident Report</CCol>
                <div v-if="accident_reports.length > 0">
                  <div
                    v-for="inspection_report in accident_reports"
                    :key="inspection_report.id"
                  >
                    <CCol md="12" class="value-content">
                      <a :href="`${inspection_report.view}`" target="_blank">{{
                        inspection_report.name
                      }}</a>
                    </CCol>
                  </div>
                </div>
                <CCol md="12" v-else class="value-content">-</CCol>
                <CCol md="12" class="font-weight-bold">Inspection Report</CCol>
                <div v-if="inspection_reports.length > 0">
                  <div
                    v-for="inspection_report in inspection_reports"
                    :key="inspection_report.id"
                  >
                    <CCol md="12" class="value-content">
                      <a :href="`${inspection_report.view}`" target="_blank">{{
                        inspection_report.name
                      }}</a>
                    </CCol>
                  </div>
                </div>
                <CCol md="12" v-else class="value-content">-</CCol>
                <CCol md="12" class="font-weight-bold">Remarks</CCol>
                <CCol md="12" class="value-content">{{
                  all_cars_remarks ? all_cars_remarks : "-"
                }}</CCol>
              </CRow>
              <CRow v-else>
                <CCol md="12">
                  <DDFileUpload
                    name="accident_report"
                    label="Accident Report"
                    type="accident_report"
                    :url="`/api/accidents/${id}/documents`"
                    ref="accident_report"
                    v-if="user.role == 'admin'"
                    withValidation
                  />
                </CCol>
                <CCol md="12">
                  <DDFileUpload
                    name="inspection_report"
                    label="Inspection Report"
                    type="inspection_report"
                    :url="`/api/accidents/${id}/documents`"
                    ref="inspection_report"
                    withValidation
                  />
                </CCol>
              </CRow>
            </CCardBody>
          </CCard>
          <CRow>
            <CCol md="12" class="font-weight-bold">
              <CTextarea
                required
                :label="
                  user.category == 'all_cars'
                    ? 'Allcars Remarks'
                    : 'Workshop Remarks'
                "
                :value.sync="remarks"
              ></CTextarea>
            </CCol>
            <CCol md="12" style="margin-bottom: 1rem">
              <div class="form-actions text-center">
                <CButton type="submit" color="primary">Save changes</CButton>
                <CButton color="secondary" @click="cancel()">Cancel</CButton>
              </div>
            </CCol>
          </CRow>
        </form>
      </template>
      <template v-else>
        <HistoryAccident :id="id"></HistoryAccident>
      </template>
    </template>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
export default {
  data() {
    return {
      tab: "info",
      id: "",
      details: "",
      inspection_reports: [],
      accident_reports: [],
      accident_report_documents: [],
      inspection_report_documents: [],
      multiple: true,
      all_cars_remarks: "",
      file_paths: [],
      isActive: false,
      accident_photos: [],
      remarks: "",
      statusOptions: [
        { label: "Pending", value: "pending" },
        { label: "Completed", value: "completed" },
      ],
    };
  },
  watch: {
    user: function (newUser) {
      if (newUser.category == "all_cars" || newUser.category == "workshop") {
        // if allowed access
        if (this.$route.name != "Edit User" && newUser.category != "all_cars") {
          this.category = this.user.category;
          this.company = this.user.company_id;
        }
        this.id = window.location.pathname.split("/").pop();
        var inputs = {};
        inputs.method = "get";
        inputs.url = "/api/accidents/" + this.id;
        inputs.id = this.id;
        this.$store.dispatch("API", inputs).then((data) => {
          this.details = data;
          this.accident_photos = data.documents;
          this.details.documents = _.mapValues(
            _.groupBy(data.documents, "type")
          );
          if (data.reports.length > 0) {
            this.inspection_reports = data.reports[0].documents.filter(
              (filterInspection) => filterInspection.type == "inspection_report"
            );
            this.accident_reports = data.reports[0].documents.filter(
              (filterInspection) => filterInspection.type == "accident_report"
            );
            this.all_cars_remarks = data.reports[0].all_cars_remarks;
          }
        });
      } else {
        // if not allowed redirect to dashboard
        this.$router.push("/dashboard");
      }
    },
  },
  mounted() {},
  computed: {
    ...mapGetters(["user"]),
    VehiclesInfo() {
      var details = this.details;
      return [
        {
          firstLabel: "Vehicle Number",
          firstValue: details.vehicle ? details.vehicle.registration_no : "-",
          secondLabel: "Vehicle Make",
          secondValue: details.vehicle_make,
        },
        {
          firstLabel: "Vehicle Model",
          firstValue: details.vehicle_model,
          secondLabel: "Insurance Company",
          secondValue: details.insured_name,
        },
        {
          firstLabel: "Policy Cert No.",
          firstValue: details.certificate_number,
          secondLabel: "Insurec NRIC",
          secondValue: details.insured_nric,
        },
        {
          firstLabel: "Insured Name",
          firstValue: details.insured_name,
          secondLabel: "Insurec Contact No.",
          secondValue: details.insured_contact_number,
        },
      ];
    },
    AccidentsInfo() {
      var details = this.details;
      return [
        {
          firstLabel: "Date Of Accident",
          firstValue: details.format_accident_date_web,
          secondLabel: "Time of Accident (24HR)",
          secondValue: details.format_accident_time_web,
        },
        {
          firstLabel: "Location of Accident",
          firstValue: details.location_of_accident,
          secondLabel: "Weather & Road Surface",
          secondValue: details.weather_road_condition,
        },
        {
          firstLabel: "Reporting Type",
          firstValue: details.reporting_type,
          secondLabel: "No. of Passengers (Including Driver)",
          secondValue: details.number_of_passengers,
        },
        {
          firstLabel: "Was there any video captured by car camera?",
          firstValue: details.is_video_captured ? "Yes" : "No",
          secondLabel: "Purspose which vehicle was being used for",
          secondValue: details.purpose_of_use,
        },
        {
          firstLabel: "Details",
          firstValue: details.details,
          secondLabel: "",
          secondValue: "",
        },
      ];
    },
    DriversInfo() {
      var details = this.details;
      if (details) {
        var driver = this.details.driver;
        var vehicle_involve = false;
        var vehicle_driver = false;
        if (this.details.vehicle_involve) {
          vehicle_involve = this.details.vehicle_involve;
          vehicle_driver = this.details.vehicle_involve.driver;
        }
        return [
          {
            firstLabel: "Was the vehicle driven by Owner of vehicle",
            firstValue: details.is_owner_drives ? "Yes" : "No",
            secondLabel: "Relationship of Owner & Driver",
            secondValue: details.owner_driver_relationship,
          },
          {
            firstLabel: "Driver Name",
            firstValue: driver ? driver.name : details.driver_name,
            secondLabel: "Driver NRIC/UEN No.",
            secondValue: driver ? driver.nric : details.driver_nric,
          },
          {
            firstLabel: "Driver Date of Birth",
            firstValue: driver ? driver.dob : details.driver_dob,
            secondLabel: "Driver License Pass Date",
            secondValue: driver
              ? driver.license_pass_date
              : details.driver_license,
          },
          {
            firstLabel: "Driver Address",
            firstValue: driver ? driver.address : details.driver_address,
            secondLabel: "Driver Contact No.",
            secondValue: driver
              ? driver.contact_number
              : details.driver_contact_no,
          },
          {
            firstLabel: "Driver Email",
            firstValue: driver ? driver.email : details.driver_email,
            secondLabel: "Driver Occupation",
            secondValue: driver
              ? driver.occupations
              : details.driver_occupation,
          },
          {
            firstLabel: "Was another vehicle involved in the accident",
            firstValue: vehicle_involve ? "Yes" : "No",
            secondLabel: "Other Vehicle Carplate No.",
            secondValue: vehicle_involve ? vehicle_involve.plate_number : "-",
          },
          {
            firstLabel: "Other Vehicle Make",
            firstValue: vehicle_involve ? vehicle_involve.vehicle_make : "-",
            secondLabel: "Other Vehicle Model",
            secondValue: vehicle_involve ? vehicle_involve.model : "-",
          },
          {
            firstLabel: "Other Driver Name",
            firstValue: vehicle_involve ? vehicle_driver.name : "-",
            secondLabel: "Other Driver NRIC",
            secondValue: vehicle_involve ? vehicle_driver.nric : "-",
          },
          {
            firstLabel: "Other Driver's Contact No.",
            firstValue: vehicle_involve ? vehicle_driver.contact_number : "-",
            secondLabel: "Other Driver Address",
            secondValue: vehicle_involve ? vehicle_driver.address : "-",
          },
        ];
      }
    },
    InspectionsInfo() {
      var details = this.details;
      if (this.details) {
        return [
          {
            firstLabel: "Date",
            firstValue: details.format_appointment_date,
            secondLabel: "Time",
            secondValue: details.format_appointment_time,
          },
          {
            firstLabel: "Workshop",
            firstValue: details.workshop.name,
            secondLabel: "Service",
            secondValue: "Accident Inspection",
          },
          {
            firstLabel: "Vehicle Number",
            firstValue: details.vehicle.registration_no,
            secondLabel: "Car Make",
            secondValue: details.vehicle_make,
          },
          {
            firstLabel: "Car Make & Model",
            firstValue: `${details.vehicle_make} ${details.vehicle_model} `,
            secondLabel: "Remarks",
            secondValue:
              details.reports.length > 0 ? details.reports[0].remarks : "-",
          },
        ];
      }
    },
    filenamePDF() {
      return "accident_report_" + this.details.ref_no;
    },
  },
  methods: {
    getAccidentByType(type) {
      var accident_by_type = this.accident_photos.filter(
        (documents) => documents.type == type
      );
      return accident_by_type;
    },
    toggle() {
      this.isActive = !this.isActive;
    },
    generatePdf() {
      this.$refs.html2Pdf.generatePdf();
    },
    changeTab(tab) {
      this.tab = tab;
    },
    formatInput() {
      var inputs = {};
      inputs.method = "post";
      inputs.accident_status = this.details.status;
      inputs.remarks = this.remarks;
      return inputs;
    },
    submit() {
      var inputs = this.formatInput();
      if (this.id != null) {
        inputs.id = this.id;
        inputs.url = `/api/accidents/${this.id}/reports`;
        this.$store.dispatch("API", inputs).then(() => {
          this.$router.push("/accidentReport/list");
        });
      }
    },
    cancel() {
      this.$router.push("/accidentReport/list");
    },
    async accidentReportAction(file, component) {
      return this.customAction(file, component, "accident_report");
    },
    async inspectionReportAction(file, component) {
      return this.customAction(file, component, "inspection_report");
    },
    customAction(file, component, type) {
      var formData = new FormData();
      formData.append("file", file.file);
      formData.append("type", type);
      return axios({
        method: "post",
        url: `/api/accidents/${this.id}/documents`,
        data: formData,
      }).then((response) => {
        file["dataId"] = response.data.id;
        this.file_paths.push({
          id: file.id,
          name: file.name,
          url: response.data.url,
          type: type,
        });
      });
    },
    inputFilter(newFile, oldFile, prevent) {
      if (newFile && !oldFile) {
        if (/(\/|^)(Thumbs\.db|desktop\.ini|\..+)$/.test(newFile.name)) {
          return prevent();
        }

        if (/\.(php5?|html?|jsx?)$/i.test(newFile.name)) {
          return prevent();
        }
      }
      if (newFile && (!oldFile || newFile.file !== oldFile.file)) {
        newFile.blob = "";
        let URL = window.URL || window.webkitURL;
        if (URL && URL.createObjectURL) {
          newFile.blob = URL.createObjectURL(newFile.file);
        }

        newFile.thumb = "";
        if (newFile.blob && newFile.type.substr(0, 6) === "image/") {
          newFile.thumb = newFile.blob;
        }
      }
    },

    inputAccidentReport(newFile, oldFile) {
      this.inputFile(newFile, oldFile, this.$refs.accident_report_ref);
    },
    inputInspectionReport(newFile, oldFile) {
      this.inputFile(newFile, oldFile, this.$refs.inspection_report_ref);
    },
    removeAccidentReport(file) {
      this.$refs.accident_report_ref.remove(file);
      this.removeFileFromDb(file.dataId);
      for (var z = 0; z < this.accident_report_documents.length; z++) {
        if (this.accident_report_documents[z].id == file.id) {
          this.accident_report_documents.splice(z, 1);
          break;
        }
      }
    },
    removeInspectionReport(file) {
      this.$refs.inspection_report_ref.remove(file);
      this.removeFileFromDb(file.dataId);
      for (var z = 0; z < this.inspection_report_documents.length; z++) {
        if (this.inspection_report_documents[z].id == file.id) {
          this.inspection_report_documents.splice(z, 1);
          break;
        }
      }
    },
    removeFileFromDb(fileId) {
      var inputs = {};
      inputs.url = "/api/accident-reports/documents/" + fileId;
      inputs.method = "delete";
      this.$store.dispatch("API", inputs).then((data) => {});
    },
    inputFile(newFile, oldFile, ref) {
      if (newFile && oldFile) {
        // update
        if (newFile.active && !oldFile.active) {
          // beforeSend
          // min size
          if (
            newFile.size >= 0 &&
            this.minSize > 0 &&
            newFile.size < this.minSize
          ) {
            ref.update(newFile, { error: "size" });
          }
        }
        if (newFile.progress !== oldFile.progress) {
          // progress
        }
        if (newFile.error && !oldFile.error) {
          // error
        }
        if (newFile.success && !oldFile.success) {
          // success
        }
      }
      if (!newFile && oldFile) {
        // remove
        if (oldFile.success && oldFile.response.id) {
          // $.ajax({
          //   type: 'DELETE',
          //   url: '/upload/delete?id=' + oldFile.response.id,
          // })
        }
      }
      ref.active = true;
    },

    bytesToSize(bytes) {
      var sizes = ["Bytes", "KB", "MB", "GB", "TB"];
      if (bytes == 0) return "0 Byte";
      var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
      return Math.round(bytes / Math.pow(1024, i), 2) + " " + sizes[i];
    },
  },
};
</script>
<style scoped>
.value-content {
  color: #007bff;
  margin: 4px 0px;
  padding-left: 27px;
  height: 24px;
}
.top-buffer {
  margin-top: 20px;
}
</style>

