<template>
  <div>
    <CCard>
      <CCardHeader> Warranty Summary </CCardHeader>
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
              <strong>End Date</strong>
            </h6>
            <span>{{ end_date }}</span>
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
      <CCardHeader> Warranty Details </CCardHeader>
      <CCardBody class="details">
        <CRow>
          <CCol md="6">
            <h6>
              <strong>CI No</strong>
            </h6>
            <span v-if="warranty.ci_no != null">{{ warranty.ci_no }}</span>
            <span v-else>TBD</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong
                >Package
                <a href="/documents/extended.pdf" v-if="extended"
                  >( Extended )</a
                ></strong
              >
            </h6>
            <span>{{ warranty.package }}</span>
          </CCol>
          <template v-if="!pendingEnquiry() && !pendingAcceptance()">
            <CCol md="6">
              <h6>
                <strong>Price</strong>
              </h6>
              <span v-if="warranty.price != null">{{
                warranty.format_price
              }}</span>
              <span v-else>TBD</span>
            </CCol>

            <CCol md="6">
              <h6>
                <strong>Max Claim Per Year</strong>
              </h6>
              <span v-if="warranty.max_claim != null">{{
                warranty.format_max_claim
              }}</span>
              <span v-else>TBD</span>
            </CCol>
          </template>
          <CCol md="6">
            <h6>
              <strong>Mileage Coverage</strong>
            </h6>
            <span v-if="warranty.mileage_coverage != null">{{
              warranty.format_mileage_coverage
            }}</span>
            <span v-else>TBD</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Warranty Duration</strong>
            </h6>
            <span>{{ warranty.warranty_period }}</span>
          </CCol>
        </CRow>
        <template v-if="pendingAdminReview() || pendingAcceptance()">
          <CRow v-if="pendingAcceptance()">
            <CCol col="12" style="margin-bottom: 0 !important">
              <h6>
                <strong>Price</strong>
              </h6>
              <CInput
                disabled
                required
                name="quote_price"
                :value.sync="warranty.format_price"
              >
                <template #prepend-content><CIcon name="cilDollar" /></template>
              </CInput>
            </CCol>
            <CCol col="12" style="margin-bottom: 0 !important">
              <h6>
                <strong>Max Claim Per Year</strong>
              </h6>
              <CInput
                disabled
                required
                name="quote_max_claim"
                :value.sync="quote_max_claim"
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
                  Approve
                </CButton>
                <CButton v-else color="primary" disabled>
                  <div class="spinner-grow text-light" role="status">
                    <span class="sr-only">Loading...</span>
                  </div>
                  Approving
                </CButton>
                <CButton
                  :disabled="submitting"
                  v-if="pendingAcceptance()"
                  @click="reject()"
                  color="danger"
                  >Draft</CButton
                >
                <CButton
                  :disabled="submitting"
                  v-else
                  @click="reject()"
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
            <CCol col="12" style="margin-bottom: 0 !important">
              <h6>
                <strong>Max Claim Per Year</strong>
              </h6>
              <CInput
                required
                name="quote_max_claim"
                :value.sync="quote_max_claim"
                type="number"
              >
                <template #prepend-content><CIcon name="cilDollar" /></template>
              </CInput>
            </CCol>
          </CRow>
          <CRow>
            <CCol style="margin-bottom: 0 !important">
              <h6>
                <strong>Insurer</strong>
              </h6>
              <CSelect
                required
                name="insurer_id"
                :value.sync="insurer_id"
                :options="insurer_options"
                placeholder="Please select"
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
          <CRow>
            <CCol>
              <div class="form-actions">
                <CButton @click="quote()" color="primary">Quote</CButton>
                <CButton @click="reject()" color="danger">Reject</CButton>
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
              <span>{{ warranty.remarks }}</span>
            </CCol>
          </CRow>
        </template>
      </CCardBody>
    </CCard>
    <CCard v-if="vehicle != null">
      <CCardHeader> Vehicle Information </CCardHeader>
      <CCardBody class="details">
        <CRow>
          <CCol md="6">
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
          <CCol md="6" v-if="warranty.mileage != null">
            <h6>
              <strong>Mileage</strong>
            </h6>
            <span>{{ warranty.mileage }}</span>
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
              <strong>Registration Date</strong>
            </h6>
            <span>{{ vehicle.format_registration_date }}</span>
          </CCol>
          <CCol md="6" v-if="vehicle.capacity != null">
            <h6>
              <strong>Capacity</strong>
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
              <strong>Fuel</strong>
            </h6>
            <span style="text-transform: capitalize">{{
              $helpers.unslugify(vehicle.fuel)
            }}</span>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>
    <CCard v-if="proposer != null">
      <CCardHeader> Customer Details </CCardHeader>
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
        </CRow>
        <CRow>
          <CCol md="6">
            <h6>
              <strong>Name</strong>
            </h6>
            <span>{{ proposer.name }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Address</strong>
            </h6>
            <span>{{ proposer.address }}</span>
          </CCol>
        </CRow>
        <CRow>
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
        </CRow>
      </CCardBody>
    </CCard>
    <CCard
      v-if="
        log_files.length > 0 ||
        assessment_files.length > 0 ||
        form_files.length > 0 ||
        warranty_files.length > 0
      "
    >
      <CCardHeader> Documents </CCardHeader>
      <CCardBody class="details">
        <CRow v-if="log_files.length > 0">
          <CCol>
            <h6><strong>Log Card</strong></h6>
            <div v-for="log in log_files" :key="log.id + '-files'">
              <a :href="log.view" target="_blank">{{ log.name }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow v-if="assessment_files.length > 0">
          <CCol>
            <h6><strong>Car Assessment Report</strong></h6>
            <div
              v-for="assessment in assessment_files"
              :key="assessment.id + '-files'"
            >
              <a :href="assessment.view" target="_blank">{{
                assessment.name
              }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow v-if="form_files.length > 0">
          <CCol>
            <h6><strong>Salesperson Form</strong></h6>
            <div v-for="form in form_files" :key="form.id + '-files'">
              <a :href="form.view" target="_blank">{{ form.name }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow v-if="warranty_files.length > 0">
          <CCol>
            <h6><strong>Warranty Booklet</strong></h6>
            <div
              v-for="warranty in warranty_files"
              :key="warranty.id + '-files'"
            >
              <a :href="warranty.view" target="_blank">{{ warranty.name }}</a>
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
        <CRow v-if="coc_files.length > 0">
          <CCol>
            <h6><strong>COC Form</strong></h6>
            <div v-for="coc in coc_files" :key="coc.id + '-files'">
              <a :href="coc.view" target="_blank">{{ coc.name }}</a>
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
      end_date: "",
      created_at: "",
      status: "",
      quote_price: "",
      quote_max_claim: "",
      insurer_id: "",
      remarks: "",
      extended: false,
      vehicle: null,
      proposer: null,
      log_files: [],
      assessment_files: [],
      form_files: [],
      warranty_files: [],
      ci_files: [],
      coc_files: [],
      insurer_options: [],
      multiple: true,
      altered: false,
    };
  },
  mounted() {
    this.setDetails(this.warranty);
    var inputs = {};
    inputs.method = "post";
    inputs.url = "/api/companies";
    inputs.category = "insurer";
    this.$store.dispatch("API", inputs).then((data) => {
      for (var i = 0; i < data.companies.length; i++) {
        this.insurer_options.push({
          label: data.companies[i].name,
          value: data.companies[i].id,
        });
      }
    });
  },
  watch: {
    warranty: function (val) {
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
    pendingAcceptance() {
      return (
        this.user.category == "dealer" &&
        this.warranty.status == "pending_acceptance"
      );
    },
    pendingAdminReview() {
      return (
        this.user.category == "all_cars" &&
        this.user.role == "support_staff" &&
        this.warranty.status == "pending_admin_review"
      );
    },
    pendingEnquiry() {
      return (
        this.user.category == "all_cars" &&
        this.user.role == "support_staff" &&
        this.warranty.status == "pending_enquiry"
      );
    },
    quote() {
      var inputs = {};
      inputs.method = "post";
      inputs.url = "/api/warranties/quote";
      inputs.quote_price = this.quote_price;
      inputs.quote_max_claim = this.quote_max_claim;
      inputs.insurer_id = this.insurer_id;
      inputs.id = this.id;
      inputs.remarks = this.remarks;

      this.$store.dispatch("API", inputs).then((data) => {
        this.getDetails();
      });
    },
    approve() {
      var inputs = {};
      inputs.method = "post";
      this.submitting = true;
      if (this.pendingAcceptance()) {
        const { isEmpty, data } = this.$refs.signaturePad.saveSignature();
        inputs.signature = data;
        inputs.url = "/api/warranties/dealerApprove";
      } else {
        inputs.url = "/api/warranties/approve";
      }
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
        inputs.url = "/api/warranties/dealerReject";
      } else {
        inputs.url = "/api/warranties/reject";
      }
      inputs.id = this.id;
      inputs.remarks = this.remarks;
      this.$store.dispatch("API", inputs).then((data) => {
        this.getDetails();
      });
    },
    setDetails(val) {
      this.log_files = [];
      this.assessment_files = [];
      this.form_files = [];
      this.warranty_files = [];
      if (val.id != null) {
        this.id = val.id;
        this.ref_no = val.ref_no;
        this.dealer = val.dealer.name;
        this.start_date = val.format_start_date;
        this.end_date = val.end_date;
        this.created_at = val.created_at;
        this.status = val.status;
        this.vehicle = val.vehicle;
        this.proposer = val.proposer;
        this.quote_price = parseFloat(val.price);
        this.quote_max_claim = val.max_claim;
        this.extended = val.extended;
        this.remarks = val.remarks;
        for (var i = 0; i < val.documents.length; i++) {
          if (val.documents[i].type != "signature") {
            this[val.documents[i].type + "_files"].push(val.documents[i]);
          }
        }
      }
    },
    getDetails() {
      this.$store.dispatch("GET_WARRANTY", this.id);
    },
  },
  computed: {
    ...mapGetters(["user", "warranty"]),
  },
};
</script>
