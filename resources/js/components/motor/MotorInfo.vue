<template>
  <div>
    <CCard>
      <CCardHeader> Motor Summary </CCardHeader>
      <CCardBody class="details">
        <CRow>
          <CCol md="6">
            <h6>
              <strong>Ref No</strong>
            </h6>
            <span>{{ ref_no }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Dealer</strong>
            </h6>
            <span>{{ dealer }}</span>
          </CCol>
        </CRow>
        <CRow>
          <CCol md="6">
            <h6>
              <strong>Start Date</strong>
            </h6>
            <span>{{ start_date }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Expiry Date</strong>
            </h6>
            <span>{{ expiry_date }}</span>
          </CCol>
        </CRow>
        <CRow>
          <CCol md="6">
            <h6>
              <strong>Created At</strong>
            </h6>
            <span>{{ created_at }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Status</strong>
            </h6>
            <span style="text-transform: capitalize">{{
              $helpers.unslugify(status)
            }}</span>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>
    <CCard>
      <CCardHeader> Motor Details </CCardHeader>
      <CCardBody class="details">
        <CRow>
          <CCol md="6">
            <h6>
              <strong>CI No</strong>
            </h6>
            <span v-if="motor.ci_no != null">{{ motor.ci_no }}</span>
            <span v-else>TBD</span>
          </CCol>
          <template v-if="!pendingEnquiry() && !pendingAcceptance()">
            <CCol md="6">
              <h6>
                <strong>Price</strong>
              </h6>
              <span v-if="motor.price != null">{{ motor.format_price }}</span>
              <span v-else>TBD</span>
            </CCol>
          </template>
          <CCol md="6">
            <h6>
              <strong>Policyholder Driving</strong>
            </h6>
            <span v-if="motor.policyholder_driving">Yes</span>
            <span v-else>No</span>
          </CCol>
        </CRow>
        <template
          v-if="
            pendingAdminReview() ||
            pendingAcceptance() ||
            pendingLogCard() ||
            pendingCI()
          "
        >
          <CRow v-if="pendingAcceptance()">
            <CCol col="12" style="margin-bottom: 0 !important">
              <h6>
                <strong>Price</strong>
              </h6>
              <CInput
                disabled
                required
                name="quote_price"
                :value.sync="quote_price"
              >
                <template #prepend-content><CIcon name="cilDollar" /></template>
              </CInput>
            </CCol>
          </CRow>
          <CRow
            v-if="
              pendingAdminReview() && vehicle != null && vehicle.type == 'new'
            "
          >
            <CCol class="mb-1">
              <DDFileUpload
                name="note"
                label="Cover Note"
                type="note"
                url="/api/motors/upload"
                ref="note"
              />
            </CCol>
          </CRow>
          <CRow v-else-if="pendingLogCard()">
            <CCol col="12" class="mb-0">
              <CInput
                required
                name="registration_no"
                label="Vehicle Number"
                :value.sync="registration_no"
              />
            </CCol>
            <CCol class="mb-1">
              <DDFileUpload
                name="log"
                label="Log Card"
                type="log"
                url="/api/motors/upload"
                ref="log"
              />
            </CCol>
          </CRow>
          <CRow v-else-if="pendingCI()">
            <CCol class="mb-1">
              <DDFileUpload
                name="ci"
                label="CI Form"
                type="ci"
                url="/api/motors/upload"
                ref="ci"
              />
            </CCol>
          </CRow>
          <CRow>
            <CCol style="margin-bottom: 0 !important">
              <h6>
                <strong>Additional Remarks</strong>
              </h6>
              <CTextarea name="remarks" rows="5" :value.sync="remarks" />
            </CCol>
          </CRow>
          <CRow v-if="pendingAcceptance()">
            <CCol style="margin-bottom: 1rem">
              <h6>
                <strong>Signature</strong>
              </h6>
              <div style="border: 1px solid rgb(200, 206, 211)">
                <VueSignaturePad
                  width="100%"
                  height="200px"
                  ref="signaturePad"
                />
                <div
                  style="
                    border-top: 1px solid rgb(200, 206, 211);
                    padding: 12px;
                  "
                >
                  <CButton @click="undo()" color="danger">Undo</CButton>
                </div>
              </div>
            </CCol>
          </CRow>
          <CRow>
            <CCol>
              <div class="form-actions">
                <CButton @click="approve()" v-if="!submitting" color="primary">
                  Submit
                </CButton>
                <CButton v-else color="primary" disabled>
                  <div class="spinner-grow text-light" role="status">
                    <span class="sr-only">Loading...</span>
                  </div>
                  Submitting
                </CButton>
                <CButton
                  v-if="pendingAdminReview() || pendingAcceptance()"
                  @click="reject()"
                  :disabled="submitting"
                  color="danger"
                  >Reject</CButton
                >
              </div>
            </CCol>
          </CRow>
        </template>
        <template v-else-if="pendingEnquiry()">
          <CRow>
            <CCol col="12" style="margin-bottom: 0 !important">
              <h6>
                <strong>Price</strong>
              </h6>
              <CInput
                required
                name="quote_price"
                :value.sync="quote_price"
                type="number"
              >
                <template #prepend-content><CIcon name="cilDollar" /></template>
              </CInput>
            </CCol>
          </CRow>
          <CRow>
            <CCol style="margin-bottom: 0 !important">
              <h6>
                <strong>Additional Remarks</strong>
              </h6>
              <CTextarea name="remarks" rows="5" :value.sync="remarks" />
            </CCol>
          </CRow>
          <CRow>
            <CCol>
              <div class="form-actions">
                <CButton @click="quote()" color="primary">Quote</CButton>
              </div>
            </CCol>
          </CRow>
        </template>
        <template v-else>
          <CRow>
            <CCol>
              <h6>
                <strong>Additional Remarks</strong>
              </h6>
              <span>{{ motor.remarks }}</span>
            </CCol>
          </CRow>
        </template>
      </CCardBody>
    </CCard>
    <CCard v-if="vehicle != null">
      <CCardHeader> Vehicle Information </CCardHeader>
      <CCardBody class="details">
        <CRow>
          <CCol md="6" v-if="vehicle.registration_no != null">
            <h6>
              <strong>Car Plate</strong>
            </h6>
            <span>{{ vehicle.registration_no }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Chassis Number</strong>
            </h6>
            <span>{{ vehicle.chassis_no }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Make & Model</strong>
            </h6>
            <span>{{ vehicle.make }} {{ vehicle.model }}</span>
          </CCol>
          <CCol md="6" v-if="motor.mileage != null">
            <h6>
              <strong>Mileage</strong>
            </h6>
            <span>{{ motor.mileage }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Engine Number</strong>
            </h6>
            <span>{{ vehicle.engine_no }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Manufacture Year</strong>
            </h6>
            <span>{{ vehicle.manufacture_year }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Body Type</strong>
            </h6>
            <span>{{ vehicle.body_type }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Capacity</strong>
            </h6>
            <span>{{ vehicle.capacity }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Seating Capacity</strong>
            </h6>
            <span>{{ vehicle.capacity }}</span>
          </CCol>
          <CCol md="6" v-if="vehicle.category != null">
            <h6>
              <strong>Category</strong>
            </h6>
            <span>{{ vehicle.category }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Type</strong>
            </h6>
            <span style="text-transform: capitalize">{{
              $helpers.unslugify(vehicle.type)
            }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Off Peak</strong>
            </h6>
            <span v-if="vehicle.off_peak">Yes</span>
            <span v-else>No</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Modified</strong>
            </h6>
            <span v-if="vehicle.modified">Yes</span>
            <span v-else>No</span>
          </CCol>
          <CCol v-if="vehicle.modified" md="12">
            <h6>
              <strong>Modification Remarks</strong>
            </h6>
            <span>{{ vehicle.modified_remarks }}</span>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>
    <CCard v-if="proposer != null">
      <CCardHeader>
        Customer Details<span v-if="motor.policyholder_driving">
          ( Main Driver )</span
        >
      </CCardHeader>
      <CCardBody class="details">
        <CRow>
          <CCol md="6">
            <h6>
              <strong>NRIC/UEN Number</strong>
            </h6>
            <span>{{ proposer.nric_uen }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Salutation</strong>
            </h6>
            <span>{{ proposer.salutation }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Name</strong>
            </h6>
            <span>{{ proposer.name }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Nationality</strong>
            </h6>
            <span>{{ proposer.format_nationality }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Residential</strong>
            </h6>
            <span>{{ proposer.format_residential }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Gender</strong>
            </h6>
            <span>{{ proposer.gender }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Address</strong>
            </h6>
            <span>{{ proposer.address }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Occupation</strong>
            </h6>
            <span>{{ proposer.format_occupation }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Date of Birth</strong>
            </h6>
            <span>{{ proposer.format_dob }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Date of License</strong>
            </h6>
            <span>{{ driver.format_dol }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Email</strong>
            </h6>
            <span>{{ proposer.email }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Phone</strong>
            </h6>
            <span>{{ proposer.phone }}</span>
          </CCol>
          <template v-if="motor.policyholder_driving && driver != null">
            <CCol md="6">
              <h6>
                <strong>Occupation Type</strong>
              </h6>
              <span>{{ driver.occupations }}</span>
            </CCol>
            <CCol md="6">
              <h6>
                <strong>Number of Accident ( Past 3 Years )</strong>
              </h6>
              <span>{{ driver.no_of_accidents }}</span>
            </CCol>
            <CCol md="6">
              <h6>
                <strong>Total Claim Amount ( Past 3 Years )</strong>
              </h6>
              <span>${{ driver.total_claim }}</span>
            </CCol>
            <CCol md="6">
              <h6>
                <strong>No Claim Discount (NCD)</strong>
              </h6>
              <span>{{ driver.ncd }}%</span>
            </CCol>
            <CCol md="6">
              <h6>
                <strong>Serious Offences ( Past 3 Years )</strong>
              </h6>
              <span v-if="driver.serious_offence">Yes</span>
              <span v-else>No</span>
            </CCol>
            <CCol md="6">
              <h6>
                <strong>Physical Disability</strong>
              </h6>
              <span v-if="driver.physical_disable">Yes</span>
              <span v-else>No</span>
            </CCol>
            <CCol md="6">
              <h6>
                <strong>Declined Renewal</strong>
              </h6>
              <span v-if="driver.refused">Yes</span>
              <span v-else>No</span>
            </CCol>
            <CCol md="6">
              <h6>
                <strong>Insurance terminated ( Past 12 Months )</strong>
              </h6>
              <span v-if="driver.terminated">Yes</span>
              <span v-else>No</span>
            </CCol>
          </template>
        </CRow>
      </CCardBody>
    </CCard>
    <CCard v-if="!motor.policyholder_driving">
      <CCardHeader> Main Driver </CCardHeader>
      <CCardBody class="details">
        <CRow>
          <CCol md="6">
            <h6>
              <strong>NRIC/UEN Number</strong>
            </h6>
            <span>{{ driver.nric }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Name</strong>
            </h6>
            <span>{{ driver.name }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Nationality</strong>
            </h6>
            <span>{{ driver.format_nationality }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Residential</strong>
            </h6>
            <span>{{ driver.format_residential }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Gender</strong>
            </h6>
            <span>{{ driver.gender }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Date of Birth</strong>
            </h6>
            <span>{{ driver.format_dol }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Date of License</strong>
            </h6>
            <span>{{ driver.format_dob }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Occupation Type</strong>
            </h6>
            <span>{{ driver.occupations }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Number of Accident ( Past 3 Years )</strong>
            </h6>
            <span>{{ driver.no_of_accidents }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Total Claim Amount ( Past 3 Years )</strong>
            </h6>
            <span>${{ driver.total_claim }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Serious Offences ( Past 3 Years )</strong>
            </h6>
            <span v-if="driver.serious_offence">Yes</span>
            <span v-else>No</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Physical Disability</strong>
            </h6>
            <span v-if="driver.physical_disable">Yes</span>
            <span v-else>No</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Declined Renewal</strong>
            </h6>
            <span v-if="driver.refused">Yes</span>
            <span v-else>No</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Insurance terminated ( Past 12 Months )</strong>
            </h6>
            <span v-if="driver.terminated">Yes</span>
            <span v-else>No</span>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>
    <CCard v-for="(row, index) in drivers" :key="'driver-' + index">
      <CCardHeader> Additional Driver {{ index + 1 }} </CCardHeader>
      <CCardBody class="details">
        <CRow>
          <CCol md="6">
            <h6>
              <strong>NRIC/UEN Number</strong>
            </h6>
            <span>{{ row.driver.nric }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Name</strong>
            </h6>
            <span>{{ row.driver.name }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Nationality</strong>
            </h6>
            <span>{{ row.driver.format_nationality }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Residential</strong>
            </h6>
            <span>{{ row.driver.format_residential }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Gender</strong>
            </h6>
            <span>{{ row.driver.gender }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Date of Birth</strong>
            </h6>
            <span>{{ row.driver.format_dob }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Date of License</strong>
            </h6>
            <span>{{ row.driver.format_dol }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Number of Accident ( Past 3 Years )</strong>
            </h6>
            <span>{{ row.driver.no_of_accidents }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Total Claim Amount ( Past 3 Years )</strong>
            </h6>
            <span>${{ row.driver.total_claim }}</span>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>
    <CCard
      v-if="
        log_files.length > 0 ||
        note_files.length > 0 ||
        ci_files.length > 0 ||
        driving_license_files.length > 0
      "
    >
      <CCardHeader> Documents </CCardHeader>
      <CCardBody class="details">
        <CRow v-if="note_files.length > 0">
          <CCol>
            <h6><strong>Cover Note</strong></h6>
            <div v-for="note in note_files" :key="note.id + '-files'">
              <a :href="note.view" target="_blank">{{ note.name }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow v-if="log_files.length > 0">
          <CCol>
            <h6><strong>Log Card</strong></h6>
            <div v-for="log in log_files" :key="log.id + '-files'">
              <a :href="log.view" target="_blank">{{ log.name }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow v-if="ci_files.length > 0">
          <CCol>
            <h6><strong>CI Form</strong></h6>
            <div v-for="ci in ci_files" :key="ci.id + '-files'">
              <a :href="ci.view" target="_blank">{{ ci.name }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow v-if="debit_note_files.length > 0">
          <CCol>
            <h6><strong>Debit Note</strong></h6>
            <div
              v-for="debit_note in debit_note_files"
              :key="debit_note.id + '-files'"
            >
              <a :href="debit_note.view" target="_blank">{{
                debit_note.name
              }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow v-if="tax_invoice_files.length > 0">
          <CCol>
            <h6><strong>Tax Invoice</strong></h6>
            <div
              v-for="tax_invoice in tax_invoice_files"
              :key="tax_invoice.id + '-files'"
            >
              <a :href="tax_invoice.view" target="_blank">{{
                tax_invoice.name
              }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow v-if="schedule_files.length > 0">
          <CCol>
            <h6><strong>Schedule</strong></h6>
            <div
              v-for="schedule in schedule_files"
              :key="schedule.id + '-files'"
            >
              <a :href="schedule.view" target="_blank">{{ schedule.name }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow v-if="policy_files.length > 0">
          <CCol>
            <h6><strong>Policy</strong></h6>
            <div v-for="policy in policy_files" :key="policy.id + '-files'">
              <a :href="policy.view" target="_blank">{{ policy.name }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow v-if="driving_license_files.length > 0">
          <CCol>
            <h6><strong>Driving License</strong></h6>
            <div
              v-for="item in driving_license_files"
              :key="item.id + '-files'"
            >
              <a :href="item.view" target="_blank">{{ item.name }}</a>
            </div>
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
      submitting: false,
      id: "",
      ref_no: "",
      dealer: "",
      start_date: "",
      expiry_date: "",
      created_at: "",
      status: "",
      quote_price: "",
      registration_no: "",
      remarks: "",
      driver: null,
      vehicle: null,
      proposer: null,
      drivers: [],
      log_files: [],
      driving_license_files: [],
      note_files: [],
      ci_files: [],
      policy_files: [],
      debit_note_files: [],
      tax_invoice_files: [],
      schedule_files: [],
      file_paths: [],
      multiple: true,
      altered: false,
      loading: false,
    };
  },
  mounted() {
    this.setDetails(this.motor);
  },
  watch: {
    motor: function (val) {
      this.setDetails(val);
    },
  },
  methods: {
    undo() {
      this.$refs.signaturePad.undoSignature();
    },
    changeTab(tab) {
      this.tab = tab;
    },
    pendingEnquiry() {
      return (
        this.user.category == "all_cars" &&
        this.user.role == "support_staff" &&
        this.motor.status == "pending_enquiry"
      );
    },
    pendingAcceptance() {
      return (
        this.user.category == "dealer" &&
        this.motor.status == "pending_acceptance"
      );
    },
    pendingAdminReview() {
      return (
        this.user.category == "all_cars" &&
        this.user.role == "support_staff" &&
        this.motor.status == "pending_admin_review"
      );
    },
    pendingLogCard() {
      return (
        this.user.category == "dealer" &&
        this.motor.status == "pending_log_card"
      );
    },
    pendingCI() {
      return (
        this.user.category == "all_cars" &&
        this.user.role == "support_staff" &&
        this.motor.status == "pending_ci"
      );
    },
    quote() {
      var inputs = {};
      inputs.method = "post";
      inputs.url = "/api/motors/quote";
      inputs.quote_price = this.quote_price;
      inputs.id = this.id;
      inputs.remarks = this.remarks;

      this.$store.dispatch("API", inputs).then((data) => {
        this.getDetails();
      });
    },
    approve() {
      var inputs = {};
      inputs.method = "post";
      inputs.documents = [];
      this.submitting = true;

      if (this.pendingAcceptance()) {
        const { isEmpty, data } = this.$refs.signaturePad.saveSignature();
        inputs.signature = data;
        inputs.url = "/api/motors/dealerApprove";
      } else if (this.pendingLogCard()) {
        for (var z = 0; z < this.log_files.length; z++) {
          inputs.documents.push(this.log_files[z]);
        }
        inputs.registration_no = this.registration_no;
        inputs.url = "/api/motors/submitLog";
      } else if (this.pendingCI()) {
        for (var z = 0; z < this.ci_files.length; z++) {
          inputs.documents.push(this.ci_files[z]);
        }
        inputs.url = "/api/motors/submitCI";
      } else {
        inputs.url = "/api/motors/approve";
      }

      this.addFileToDocuments("log", inputs);
      this.addFileToDocuments("note", inputs);
      this.addFileToDocuments("ci", inputs);

      //   if (this.pendingAdminReview() && this.vehicle.type == "new") {
      //     for (var z = 0; z < this.note_files.length; z++) {
      //       inputs.documents.push(this.note_files[z]);
      //     }
      //   }

      //   for (var i = 0; i < this.file_paths.length; i++) {
      //     if (this.file_paths[i] != null) {
      //       if (this.pendingAdminReview() && this.vehicle.type == "new") {
      //         for (var z = 0; z < this.note_files.length; z++) {
      //           if (this.note_files[z].id == this.file_paths[i].id) {
      //             this.note_files[z].name = this.file_paths[i].name;
      //             this.note_files[z].url = this.file_paths[i].url;
      //             this.note_files[z].type = this.file_paths[i].type;
      //           }
      //         }
      //       } else if (this.pendingLogCard()) {
      //         for (var z = 0; z < this.log_files.length; z++) {
      //           if (this.log_files[z].id == this.file_paths[i].id) {
      //             this.log_files[z].name = this.file_paths[i].name;
      //             this.log_files[z].url = this.file_paths[i].url;
      //             this.log_files[z].type = this.file_paths[i].type;
      //           }
      //         }
      //       } else if (this.pendingCI()) {
      //         for (var z = 0; z < this.ci_files.length; z++) {
      //           if (this.ci_files[z].id == this.file_paths[i].id) {
      //             this.ci_files[z].name = this.file_paths[i].name;
      //             this.ci_files[z].url = this.file_paths[i].url;
      //             this.ci_files[z].type = this.file_paths[i].type;
      //           }
      //         }
      //       }
      //     }
      //   }

      inputs.id = this.id;
      inputs.remarks = this.remarks;
      this.$store
        .dispatch("API", inputs)
        .then((data) => {
          this.getDetails();
        })
        .finally(() => {
          this.submitting = false;
        });
    },
    reject() {
      var inputs = {};
      inputs.method = "post";
      if (this.pendingAcceptance()) {
        inputs.url = "/api/motors/dealerReject";
      } else {
        inputs.url = "/api/motors/reject";
      }
      inputs.id = this.id;
      inputs.remarks = this.remarks;
      this.$store.dispatch("API", inputs).then((data) => {
        this.getDetails();
      });
    },
    setDetails(val) {
      this.log_files = [];
      this.ci_files = [];
      this.driving_license_files = [];
      if (val.id != null) {
        console.log("val", val.documents);

        this.id = val.id;
        this.ref_no = val.ref_no;
        this.dealer = val.dealer.name;
        this.start_date = val.format_start_date;
        this.expiry_date = val.format_expiry_date;
        this.created_at = val.created_at;
        this.status = val.status;
        this.vehicle = val.vehicle;
        this.driver = val.driver;
        this.drivers = val.drivers;
        this.proposer = val.proposer;
        this.quote_price = parseFloat(val.price);
        this.quote_max_claim = val.max_claim;
        this.remarks = val.remarks;
        for (var i = 0; i < val.documents.length; i++) {
          if (val.documents[i].type != "signature") {
            if (this.$refs[val.documents[i].type]) {
              this.$refs[val.documents[i].type].$data.files.push(
                val.documents[i]
              );
            }
          }
        }

        for (var i = 0; i < val.documents.length; i++) {
          this[val.documents[i].type + "_files"].push(val.documents[i]);
        }
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
    getDetails() {
      this.$store.dispatch("GET_MOTOR", this.id);
    },
  },
  computed: {
    ...mapGetters(["user", "motor"]),
  },
};
</script>
