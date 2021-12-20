<template>
  <div>
    <CCard>
      <CCardHeader class="font-weight-bold"> Claim Summary </CCardHeader>
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
              <strong>III Claim No.</strong>
            </h6>
            <span>{{ insurer_ref_no }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Workshop</strong>
            </h6>
            <span>{{ workshop }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Insurer</strong>
            </h6>
            <span>{{ insurer }}</span>
          </CCol>
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
        <CRow v-if="status == 'pending_payment'">
          <CCol md="6">
            <h6>
              <strong>Claim payment to workshop</strong>
            </h6>
            <CDropdown
              v-if="user.category == 'workshop'"
              size="sm"
              :toggler-text="
                allcars_to_workshop_payment == 0
                  ? 'Pending Payment'
                  : 'Received Payment'
              "
              color="primary"
              class="m-0 d-inline-block"
            >
              <CDropdownItem @click="allCarsPayment(0)"
                >Pending Payment</CDropdownItem
              >
              <CDropdownItem @click="allCarsPayment(1)"
                >Received Payment</CDropdownItem
              >
            </CDropdown>
            <template v-else>
              <span v-if="allcars_to_workshop_payment == 0"
                >Pending Payment</span
              >
              <span v-else>Received Payment</span>
            </template>
          </CCol>
          <CCol
            col="12"
            v-if="user.category == 'all_cars' && user.role == 'support_staff'"
            style="margin-bottom: 0 !important"
          >
            <h6>
              <strong>AllCars Remarks</strong>
            </h6>
            <CTextarea
              name="allcars_remarks"
              rows="5"
              :value.sync="allcars_remarks"
            />
          </CCol>
          <CCol
            col="12"
            v-if="
              user.category == 'workshop' ||
              (user.category == 'all_cars' && user.role == 'support_staff')
            "
            style="margin-bottom: 0 !important"
          >
            <h6>
              <strong>Additional Remarks</strong>
            </h6>
            <CTextarea name="remarks" rows="5" :value.sync="remarks" />
          </CCol>
          <CCol
            md="12"
            v-if="
              user.category == 'workshop' ||
              (user.category == 'all_cars' && user.role == 'support_staff')
            "
          >
            <CButton color="success" @click="payment()">Submit</CButton>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>
    <CCard>
      <CCardHeader class="font-weight-bold"> Claim Details </CCardHeader>
      <CCardBody class="details">
        <CRow>
          <CCol md="6">
            <h6>
              <strong>Date of Notification</strong>
            </h6>
            <span>{{ date_of_notification }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Date of Loss</strong>
            </h6>
            <span>{{ date_of_loss }}</span>
          </CCol>
        </CRow>
        <CRow>
          <CCol md="6">
            <h6>
              <strong>Details of Loss</strong>
            </h6>
            <span>{{ cause_of_damage }}</span>
          </CCol>
        </CRow>
        <template v-if="inputRecommendation()">
          <CRow class="claim-header">
            <CCol col="6">
              <h6>
                <strong>Claim Item</strong>
              </h6>
            </CCol>
            <CCol col="3">
              <h6>
                <strong>Price</strong>
              </h6>
            </CCol>
            <CCol col="3">
              <h6>
                <strong>Recommended Price</strong>
              </h6>
            </CCol>
          </CRow>
          <CRow
            v-for="item in item_claims"
            :key="item.id"
            style="margin-bottom: 0rem"
          >
            <CCol col="6" class="item item-col">
              <span>{{ item.item_id }}. {{ item.item }}</span>
            </CCol>
            <CCol col="3" class="item item-col">
              <span>${{ item.amount }}</span>
            </CCol>
            <CCol col="3" class="item-col">
              <CInput
                type="number"
                v-on:input="updateRecommended(item, $event)"
                :value.sync="item.recommended"
                placeholder="Item Amount"
              >
                <template #prepend-content
                  ><CIcon name="cil-dollar"
                /></template>
              </CInput>
            </CCol>
          </CRow>
          <CRow class="claim-header">
            <CCol>
              <h6>
                <strong>Claim Labour</strong>
              </h6>
            </CCol>
          </CRow>
          <CRow
            v-for="labour in labour_claims"
            :key="labour.id"
            style="margin-bottom: 0rem"
          >
            <CCol col="6" class="item item-col">
              <span>{{ labour.item_id }}. {{ labour.item }}</span>
            </CCol>
            <CCol col="3" class="item item-col">
              <span>${{ labour.amount }}</span>
            </CCol>
            <CCol col="3" class="item-col">
              <CInput
                type="number"
                v-on:input="updateRecommended(labour, $event)"
                :value.sync="labour.recommended"
                placeholder="Item Amount"
              >
                <template #prepend-content
                  ><CIcon name="cil-dollar"
                /></template>
              </CInput>
            </CCol>
          </CRow>

          <CRow>
            <CCol col="6" class="item last-item">
              <h6 style="margin-bottom: 0">
                <strong>Total Claims Amount</strong>
              </h6>
            </CCol>
            <CCol col="3" class="item last-item">
              <span>{{ total_claim_amount }}</span>
            </CCol>
            <CCol col="3" class="last-item">
              <CInput
                type="number"
                :value.sync="total_new_claim_amount"
                disabled
                placeholder="Item Amount"
              >
                <template #prepend-content
                  ><CIcon name="cil-dollar"
                /></template>
              </CInput>
            </CCol>
          </CRow>
          <CRow
            v-if="user.category == 'all_cars' && user.role == 'support_staff'"
          >
            <CCol style="margin-bottom: 0 !important">
              <h6>
                <strong>AllCars Remarks</strong>
              </h6>
              <CTextarea
                name="allcars_remarks"
                rows="5"
                :value.sync="allcars_remarks"
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

          <div class="form-actions">
            <CButton @click="approve()" color="primary" :disabled="altered"
              >Approve</CButton
            >
            <CButton
              v-if="user.category == 'all_cars'"
              @click="send()"
              color="secondary"
              >Send to Insurer</CButton
            >
            <CButton
              v-if="user.category == 'insurer' && status != 'insurer_approval'"
              @click="send()"
              color="secondary"
              >Send to Surveyor</CButton
            >
            <CButton @click="recommend()" color="danger">Reject</CButton>
          </div>
        </template>
        <template v-else>
          <CRow class="claim-header">
            <CCol col="6">
              <h6>
                <strong>Claim Item</strong>
              </h6>
            </CCol>
            <CCol col="3">
              <h6>
                <strong>Price</strong>
              </h6>
            </CCol>
            <CCol col="3">
              <h6>
                <strong>Recommended Price</strong>
              </h6>
            </CCol>
          </CRow>

          <template v-if="item_claims.length > 0">
            <CRow v-for="item in item_claims" :key="item.id">
              <CCol col="6">
                <span>{{ item.item_id }}. {{ item.item }}</span>
              </CCol>
              <CCol col="3">
                <span>${{ item.amount }}</span>
              </CCol>
              <CCol col="3">
                <span>${{ item.recommended }}</span>
              </CCol>
            </CRow>
          </template>
          <CRow v-else>
            <CCol col="6">
              <span>None</span>
            </CCol>
          </CRow>

          <CRow class="claim-header">
            <CCol col="12">
              <h6>
                <strong>Claim Labour</strong>
              </h6>
            </CCol>
          </CRow>

          <template v-if="labour_claims.length > 0">
            <CRow v-for="labour in labour_claims" :key="labour.id">
              <CCol col="6">
                <span>{{ labour.item_id }}. {{ labour.item }}</span>
              </CCol>
              <CCol col="3">
                <span>${{ labour.amount }}</span>
              </CCol>
              <CCol col="3">
                <span>${{ labour.recommended }}</span>
              </CCol>
            </CRow>
          </template>
          <CRow v-else>
            <CCol col="6">
              <span>None</span>
            </CCol>
          </CRow>

          <CRow>
            <CCol col="6">
              <h6 style="margin-bottom: 0">
                <strong>Total Claims Amount</strong>
              </h6>
            </CCol>
            <CCol col="3">
              <span>{{ total_claim_amount }}</span>
            </CCol>
            <CCol col="3">
              <span>${{ total_new_claim_amount }}</span>
            </CCol>
          </CRow>

          <CRow
            v-if="user.category == 'all_cars' && user.role == 'support_staff'"
          >
            <CCol>
              <h6>
                <strong>AllCars Remarks</strong>
              </h6>
              <span>{{ allcars_remarks }}</span>
            </CCol>
          </CRow>
          <CRow>
            <CCol>
              <h6>
                <strong>Additional Remarks</strong>
              </h6>
              <span>{{ remarks }}</span>
            </CCol>
          </CRow>
        </template>
      </CCardBody>
    </CCard>
    <CCard
      v-if="
        (status == 'repair_in_progress' || status == 'approved') &&
        user.category == 'workshop'
      "
    >
      <CCardHeader class="font-weight-bold">
        Job Completion Attachments
      </CCardHeader>
      <CCardBody>
        <DDFileUpload
          name="supplier"
          label="Supplier Invoice"
          type="supplier"
          url="/api/claims/upload"
          ref="supplier"
        />

        <DDFileUpload
          name="note"
          label="Discharge Voucher/Satisfactory Note"
          type="note"
          url="/api/claims/upload"
          ref="note"
        />

        <DDFileUpload
          name="tax"
          label="Workshop Tax Invoice"
          type="tax"
          url="/api/claims/upload"
          ref="tax"
        />

        <div class="form-actions">
          <CButton @click="complete()" color="success">Complete</CButton>
          <CButton @click="reject()" color="danger">Reject</CButton>
        </div>
      </CCardBody>
    </CCard>
    <CCard v-if="jobCompletionAttachment() == true">
      <CCardHeader class="font-weight-bold">
        Job Completion Attachments
      </CCardHeader>
      <CCardBody class="details">
        <CRow>
          <CCol>
            <h6><strong>Supplier Invoice</strong></h6>
            <div
              v-for="supplier in supplier_files"
              :key="supplier.id + '-complete-files'"
            >
              <a :href="supplier.view" target="_blank">{{ supplier.name }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow>
          <CCol>
            <h6><strong>Discharge Voucher/Satisfactory Note</strong></h6>
            <div v-for="note in note_files" :key="note.id + '-complete-files'">
              <a :href="note.view" target="_blank">{{ note.name }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow>
          <CCol>
            <h6><strong>Workshop Tax Invoice</strong></h6>
            <div v-for="tax in tax_files" :key="tax.id + '-complete-files'">
              <a :href="tax.view" target="_blank">{{ tax.name }}</a>
            </div>
          </CCol>
        </CRow>
        <CRow>
          <CCol style="margin-bottom: 0 !important">
            <h6>
              <strong>III Claim No.</strong>
            </h6>
            <CInput name="insurer_ref_no" :value.sync="insurer_ref_no" />
          </CCol>
        </CRow>
        <CRow>
          <CCol style="margin-bottom: 0 !important">
            <h6>
              <strong>AllCars Remarks</strong>
            </h6>
            <CTextarea
              name="allcars_remarks"
              rows="5"
              :value.sync="allcars_remarks"
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
        <div class="form-actions">
          <CButton @click="approve()" color="success">Approve</CButton>
          <CButton @click="reject()" color="danger">Reject</CButton>
        </div>
      </CCardBody>
    </CCard>
    <CCard>
      <CCardHeader class="font-weight-bold">
        Vehicle & Policy Information
      </CCardHeader>
      <CCardBody class="details">
        <CRow>
          <CCol md="6">
            <h6>
              <strong>Car Plate</strong>
            </h6>
            <span>{{ vehicle_no }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Chassis Number</strong>
            </h6>
            <span>{{ vehicle_chassis_no }}</span>
          </CCol>
        </CRow>
        <CRow>
          <CCol md="6">
            <h6>
              <strong>Make & Model</strong>
            </h6>
            <span>{{ vehicle_make }} {{ vehicle_model }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Mileage</strong>
            </h6>
            <span>{{ vehicle_mileage }}</span>
          </CCol>
        </CRow>
        <CRow>
          <CCol md="6">
            <h6>
              <strong>Owner NRIC/UEN Number</strong>
            </h6>
            <span>{{ vehicle_nric_uen }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Policy Certificate Number</strong>
            </h6>
            <span>{{ policy_certificate_no }}</span>
          </CCol>
        </CRow>
        <CRow>
          <CCol md="6">
            <h6>
              <strong>Policy Coverage Period</strong>
            </h6>
            <span>{{ policy_coverage_from }} to {{ policy_coverage_to }}</span>
          </CCol>
          <CCol md="6">
            <h6>
              <strong>Insured Name</strong>
            </h6>
            <span>{{ policy_name }}</span>
          </CCol>
        </CRow>
        <CRow>
          <CCol md="6">
            <h6>
              <strong>Insured NRIC/UEN Number</strong>
            </h6>
            <span>{{ policy_nric_uen }}</span>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>
    <template
      v-if="
        damage_files.length > 0 ||
        quotation_files.length > 0 ||
        service_files.length > 0
      "
    >
      <CCard>
        <CCardHeader class="font-weight-bold">
          Initial Claim Attachments
        </CCardHeader>
        <CCardBody class="details">
          <CRow v-if="damage_files.length > 0">
            <CCol>
              <h6><strong>Details of Loss Attachment</strong></h6>
              <div v-for="damage in damage_files" :key="damage.id + '-files'">
                <a :href="damage.view" target="_blank">{{ damage.name }}</a>
              </div>
            </CCol>
          </CRow>
          <CRow v-if="quotation_files.length > 0">
            <CCol>
              <h6><strong>Quotation Attachment</strong></h6>
              <div
                v-for="quotation in quotation_files"
                :key="quotation.id + '-files'"
              >
                <a :href="quotation.view" target="_blank">{{
                  quotation.name
                }}</a>
              </div>
            </CCol>
          </CRow>
          <CRow v-if="service_files.length > 0">
            <CCol>
              <h6><strong>Service Record Attachment</strong></h6>
              <div
                v-for="service in service_files"
                :key="service.id + '-files'"
              >
                <a :href="service.view" target="_blank">{{ service.name }}</a>
              </div>
            </CCol>
          </CRow>
        </CCardBody>
      </CCard>
      <CCard
        v-if="
          (supplier_files.length > 0 ||
            note_files.length > 0 ||
            tax_files.length > 0) &&
          jobCompletionAttachment() == false
        "
      >
        <CCardHeader class="font-weight-bold">
          Job Completion Attachments
        </CCardHeader>
        <CCardBody class="details">
          <CRow v-if="supplier_files.length > 0">
            <CCol>
              <h6><strong>Supplier Invoice</strong></h6>
              <div
                v-for="supplier in supplier_files"
                :key="supplier.id + '-complete-files'"
              >
                <a :href="supplier.view" target="_blank">{{ supplier.name }}</a>
              </div>
            </CCol>
          </CRow>
          <CRow v-if="note_files.length > 0">
            <CCol>
              <h6><strong>Discharge Voucher/Satisfactory Note</strong></h6>
              <div
                v-for="note in note_files"
                :key="note.id + '-complete-files'"
              >
                <a :href="note.view" target="_blank">{{ note.name }}</a>
              </div>
            </CCol>
          </CRow>
          <CRow v-if="tax_files.length > 0">
            <CCol>
              <h6><strong>Workshop Tax Invoice</strong></h6>
              <div v-for="tax in tax_files" :key="tax.id + '-complete-files'">
                <a :href="tax.view" target="_blank">{{ tax.name }}</a>
              </div>
            </CCol>
          </CRow>
        </CCardBody>
      </CCard>
    </template>
    <CCard
      v-if="
        (user.category == 'all_cars' || user.category == 'insurer') &&
        status == 'doc_verification'
      "
    >
      <CCardHeader class="font-weight-bold">Insurer Invoice</CCardHeader>
      <CCardBody>
        <div v-if="show_insurer_invoices.length > 0">
          <CCol
            md="12"
            class="value-content"
            v-for="show_insurer_invoice in show_insurer_invoices"
            :key="show_insurer_invoice.id"
          >
            <a :href="`${show_insurer_invoice.view}`" target="_blank">{{
              show_insurer_invoice.name
            }}</a>
          </CCol>
        </div>
        <div v-else>
          <DDFileUpload
            name="insurer_invoice"
            label=""
            type="insurer-invoice"
            url="/api/claims/upload"
            ref="insurer_invoice"
            v-if="user.category == 'all_cars'"
          />

          <CCol
            md="12"
            style="margin-bottom: 1rem"
            v-if="user.category == 'all_cars'"
          >
            <div class="form-actions text-center">
              <CButton
                type="submit"
                color="primary"
                @click="saveInsurerInvoice()"
                >Save changes</CButton
              >
              <CButton color="secondary" @click="cancel()">Cancel</CButton>
            </div>
          </CCol>
        </div>
      </CCardBody>
    </CCard>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
export default {
  data() {
    return {
      insurer_invoice: [],
      show_insurer_invoices: [],
      id: "",
      ref_no: "",
      status: "",
      workshop: "",
      insurer: "",
      insurer_ref_no: "",
      created_at: "",
      vehicle_no: "",
      vehicle_chassis_no: "",
      vehicle_make: "",
      vehicle_model: "",
      vehicle_mileage: "",
      vehicle_nric_uen: "",
      policy_certificate_no: "",
      policy_coverage_from: "",
      policy_coverage_to: "",
      policy_name: "",
      policy_nric_uen: "",
      date_of_loss: "",
      date_of_notification: "",
      cause_of_damage: "",
      total_claim_amount: "",
      remarks: "",
      allcars_remarks: "",
      total_new_claim_amount: "",
      insurer_to_allcars_payment: "",
      allcars_to_workshop_payment: "",
      damage_files: [],
      quotation_files: [],
      service_files: [],
      supplier_files: [],
      insurer_invoice_files: [],
      note_files: [],
      tax_files: [],
      labour_claims: [],
      item_claims: [],
      file_paths: [],
      multiple: true,
      altered: false,
    };
  },
  mounted() {
    this.setDetails(this.claim);
  },
  watch: {
    claim: function (val) {
      this.setDetails(val);
    },
  },
  methods: {
    cancel() {
      this.$router.go(-1);
    },
    changeTab(tab) {
      this.tab = tab;
    },
    saveInsurerInvoice() {
      var inputs = {};
      inputs.id = window.location.pathname.split("/").pop();
      inputs.insurer_invoice = [];

      this.addFileToDocuments("insurer_invoice", inputs, "insurer_invoice");

      inputs.method = "post";
      inputs.ref_no = this.ref_no;
      inputs.url = "/api/claims/upload-insurer-invoice/" + inputs.id;

      this.$store.dispatch("API", inputs).then(() => {
        this.$router.push("/claims");
      });
    },
    jobCompletionAttachment() {
      return (
        this.status == "doc_verification" &&
        this.user.category == "all_cars" &&
        this.user.role == "support_staff"
      );
    },
    inputRecommendation() {
      return (
        (this.status == "allCars_review" &&
          this.user.category == "all_cars" &&
          this.user.role != "admin") ||
        (this.status == "surveyor_review" &&
          this.user.category == "surveyor") ||
        ((this.status == "insurer_review" ||
          this.status == "insurer_approval") &&
          this.user.category == "insurer") ||
        (this.status == "allCars_review" &&
          this.user.category == "all_Cars" &&
          this.user.role == "support_staff")
      );
    },
    updateRecommended: function (item, event) {
      this.altered = true;
      this.total_new_claim_amount = 0;
      for (var i = 0; i < this.item_claims.length; i++) {
        if (this.item_claims[i].id == item.id && item.type == "item") {
          this.total_new_claim_amount += parseFloat(event);
        } else {
          this.total_new_claim_amount += parseFloat(
            this.item_claims[i].recommended
          );
        }
      }
      for (var i = 0; i < this.labour_claims.length; i++) {
        if (this.labour_claims[i].id == item.id && item.type == "labour") {
          this.total_new_claim_amount += parseFloat(event);
        } else {
          this.total_new_claim_amount += parseFloat(
            this.labour_claims[i].recommended
          );
        }
      }
      this.total_new_claim_amount = this.total_new_claim_amount.toFixed(2);
    },
    send() {
      var inputs = {};
      inputs.method = "post";
      if (this.user.category == "insurer") {
        inputs.url = "/api/claims/sendSurveyor";
      } else {
        inputs.allcars_remarks = this.allcars_remarks;
        inputs.url = "/api/claims/sendInsurer";
      }
      inputs.items = this.formatItems();
      inputs.id = this.id;
      inputs.remarks = this.remarks;
      this.$store.dispatch("API", inputs).then((data) => {
        this.getDetails();
      });
    },
    approve() {
      var inputs = {};
      inputs.method = "post";
      if (this.user.category == "surveyor") {
        inputs.url = "/api/claims/surveyorApprove";
      } else if (this.user.category == "insurer") {
        inputs.url = "/api/claims/insurerApprove";
      } else if (this.jobCompletionAttachment() == true) {
        inputs.insurer_ref_no = this.insurer_ref_no;
        inputs.allcars_remarks = this.allcars_remarks;
        inputs.remarks = this.remarks;
        inputs.url = "/api/claims/approveDocuments";
      } else {
        inputs.allcars_remarks = this.allcars_remarks;
        inputs.url = "/api/claims/approve";
      }
      inputs.id = this.id;
      this.$store.dispatch("API", inputs).then((data) => {
        this.getDetails();
      });
    },
    recommend() {
      var inputs = {};
      inputs.method = "post";
      if (this.user.category == "surveyor") {
        inputs.url = "/api/claims/surveyorReject";
      } else if (this.user.category == "insurer") {
        inputs.url = "/api/claims/insurerReject";
      } else {
        inputs.url = "/api/claims/reject";
      }
      inputs.items = this.formatItems();
      inputs.id = this.id;
      inputs.remarks = this.remarks;
      this.$store.dispatch("API", inputs).then((data) => {
        this.getDetails();
      });
    },
    formatItems() {
      var format = [];
      for (var i = 0; i < this.item_claims.length; i++) {
        format.push(this.item_claims[i]);
      }
      for (var i = 0; i < this.labour_claims.length; i++) {
        format.push(this.labour_claims[i]);
      }
      return format;
    },
    setDetails(val) {
      this.damage_files = [];
      this.quotation_files = [];
      this.service_files = [];
      this.tax_files = [];
      this.supplier_files = [];
      this.note_files = [];
      this.file_paths = [];
      this.labour_claims = [];
      this.item_claims = [];
      if (val.id != null) {
        this.id = val.id;
        this.ref_no = val.ref_no;
        this.policy_certificate_no = val.policy_certificate_no;
        this.policy_coverage_from = val.format_policy_coverage_from;
        this.policy_coverage_to = val.format_policy_coverage_to;
        this.policy_name = val.policy_name;
        this.status =
          val.status == "repair_in_progress" ? "approved" : val.status;
        this.show_insurer_invoices = val.documents.filter(
          (filter) => filter.type == "insurer-invoice"
        );
        this.policy_nric_uen = val.policy_nric_uen;
        this.date_of_loss = val.format_date_of_loss;
        this.workshop = val.workshop.name;
        this.insurer = val.insurer_extend.company.name;
        this.date_of_notification = val.format_date_of_notification;
        this.cause_of_damage = val.cause_of_damage;
        this.created_at = val.created_at;
        this.vehicle_no = val.vehicle.registration_no;
        this.vehicle_chassis_no = val.vehicle.chassis_no;
        this.vehicle_make = val.vehicle.make;
        this.vehicle_model = val.vehicle.model;
        this.vehicle_mileage = val.mileage;
        this.vehicle_nric_uen = val.vehicle.nric_uen;
        this.remarks = val.remarks;
        this.allcars_remarks = val.allcars_remarks;
        this.total_claim_amount = val.total_claim_amount;
        this.total_new_claim_amount = 0;
        this.insurer_to_allcars_payment = val.insurer_to_allcars_payment;
        this.allcars_to_workshop_payment = val.allcars_to_workshop_payment;
        this.insurer_ref_no = val.insurer_ref_no;
        for (var i = 0; i < val.items.length; i++) {
          this.total_new_claim_amount += parseFloat(val.items[i].recommended);
          this[val.items[i].type + "_claims"].push(val.items[i]);
        }
        this.total_new_claim_amount = this.total_new_claim_amount.toFixed(2);

        for (var i = 0; i < val.documents.length; i++) {
          if (val.documents[i].type != "insurer-invoice") {
            this[val.documents[i].type + "_files"].push(val.documents[i]);
          }
        }
      }
    },
    getDetails() {
      this.$store.dispatch("GET_CLAIM", this.id);
    },
    addFileToDocuments(ref, inputs, name) {
      // check ref
      if (this.$refs[ref]) {
        const data = this.$refs[ref].$data;

        for (var i = 0; i < data.file_paths.length; i++) {
          if (data.file_paths[i] != null) {
            for (var z = 0; z < data.files.length; z++) {
              if (data.files[z].id == data.file_paths[i].id) {
                inputs[name].push(data.file_paths[i]);
              }
            }
          }
        }

        for (var z = 0; z < data.files.length; z++) {
          if (data.files[z].blob == null) {
            inputs[name].push(data.files[z]);
          }
        }
      }
    },
    addFileToDocumentsArray(ref, inputs) {
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
    complete() {
      var inputs = {};
      inputs.method = "post";
      inputs.url = "/api/claims/workshopApprove";
      inputs.id = this.id;
      inputs.documents = [];

      this.addFileToDocumentsArray("supplier", inputs);
      this.addFileToDocumentsArray("note", inputs);
      this.addFileToDocumentsArray("tax", inputs);

      this.$store.dispatch("API", inputs).then((data) => {
        this.getDetails();
      });
    },
    reject() {
      var inputs = {};
      inputs.method = "post";
      inputs.id = this.id;
      if (this.jobCompletionAttachment() == true) {
        inputs.insurer_ref_no = this.insurer_ref_no;
        inputs.allcars_remarks = this.allcars_remarks;
        inputs.remarks = this.remarks;
        inputs.url = "/api/claims/rejectDocuments";
      } else {
        inputs.url = "/api/claims/workshopReject";
      }
      this.$store.dispatch("API", inputs).then((data) => {
        this.getDetails();
      });
    },
    insurerPayment(val) {
      this.insurer_to_allcars_payment = val;
    },
    allCarsPayment(val) {
      this.allcars_to_workshop_payment = val;
    },
    payment() {
      var inputs = {};
      inputs.method = "post";
      inputs.id = this.id;
      inputs.url = "/api/claims/completed";
      inputs.allcars_remarks = this.allcars_remarks;
      inputs.remarks = this.remarks;
      inputs.allcars_to_workshop_payment = this.allcars_to_workshop_payment;
      this.$store.dispatch("API", inputs).then((data) => {
        this.getDetails();
      });
    },
  },
  computed: {
    ...mapGetters(["user", "claim"]),
  },
};
</script>
