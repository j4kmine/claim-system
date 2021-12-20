<template>
  <form>
    <CRow>
      <CCol lg="12">
        <CCard>
          <CCardHeader>
            {{ $route.name }}
            <span
              style="text-transform: capitalize"
              v-if="claim != null && $route.name != 'Create Claim'"
            >
              - {{ claim.status }}
            </span>
          </CCardHeader>
          <CCardBody>
            <CRow>
              <CCol md="6">
                <CInput
                  required
                  name="registration_no"
                  label="Vehicle Car Plate"
                  :value.sync="registration_no"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  required
                  name="nric_uen"
                  label="Vehicle Owner NRIC/UEN Number"
                  :value.sync="nric_uen"
                />
              </CCol>
            </CRow>
            <CRow>
              <CCol md="6">
                <CInput
                  required
                  name="make"
                  label="Vehicle Make"
                  :value.sync="make"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  required
                  name="model"
                  label="Vehicle Model"
                  :value.sync="model"
                />
              </CCol>
            </CRow>
            <CRow>
              <CCol md="6">
                <CInput
                  required
                  name="chassis_no"
                  label="Vehicle Chassis Number"
                  :value.sync="chassis_no"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  required
                  name="mileage"
                  label="Vehicle Mileage (km)"
                  :value.sync="mileage"
                />
              </CCol>
            </CRow>
            <CRow>
              <CCol md="6">
                <CSelect
                  required
                  name="insurer_id"
                  label="Insurer"
                  placeholder="Please select"
                  :value.sync="insurer_id"
                  :options="insurer_options"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  required
                  name="policy_certificate_no"
                  label="Policy Certificate Number"
                  :value.sync="policy_certificate_no"
                />
              </CCol>
            </CRow>
            <CRow>
              <CCol md="6">
                <CInput
                  type="date"
                  required
                  name="policy_coverage_from"
                  label="Policy Coverage From"
                  :value.sync="policy_coverage_from"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  type="date"
                  required
                  name="policy_coverage_to"
                  label="Policy Coverage To"
                  :value.sync="policy_coverage_to"
                />
              </CCol>
            </CRow>
            <CRow>
              <CCol md="6">
                <CInput
                  required
                  name="policy_name"
                  label="Insured Name"
                  :value.sync="policy_name"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  required
                  name="policy_nric_uen"
                  label="Insured NRIC/UEN Number"
                  :value.sync="policy_nric_uen"
                />
              </CCol>
            </CRow>
            <CRow>
              <CCol md="6">
                <CInput
                  type="date"
                  required
                  name="date_of_notification"
                  label="Date of Notification"
                  :value.sync="date_of_notification"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  type="date"
                  required
                  name="date_of_loss"
                  label="Date of Loss"
                  :value.sync="date_of_loss"
                />
              </CCol>
            </CRow>
            <template v-if="claim != null">
              <CRow>
                <CCol col="5" style="margin-top: 6px">
                  <label>Claim Item</label>
                </CCol>
                <CCol col="3" style="margin-top: 6px">
                  <label>Price</label>
                </CCol>
                <CCol col="3" style="margin-top: 6px">
                  <label>Recommended Price</label>
                </CCol>
              </CRow>
              <CRow
                v-for="(item, index) in items"
                :key="'item-' + item.item_id"
              >
                <CCol col="5" class="item-container">
                  <div class="item-id">{{ item.item_id }}.</div>
                  <CInput class="item-input" disabled :value.sync="item.item" />
                </CCol>
                <CCol col="3">
                  <CInput
                    v-on:input="updateAmount(item.item_id, $event, 'item')"
                    :value.sync="item.amount"
                  >
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
                <CCol col="3">
                  <CInput disabled :value.sync="item.recommended">
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
                <CCol
                  col="1"
                  style="padding-left: 0; cursor: pointer"
                  @click="minusItem(item.item_id)"
                >
                  <CInput class="plus">
                    <template #prepend-content
                      ><CIcon name="cil-minus"
                    /></template>
                  </CInput>
                </CCol>
              </CRow>

              <CRow>
                <CCol col="5" class="item-container">
                  <div class="item-id">{{ items.length + 1 }}.</div>
                  <CInput
                    class="item-input"
                    placeholder="Item"
                    :value.sync="item_input"
                  />
                </CCol>
                <CCol col="3">
                  <CInput
                    v-on:input="updateAmount(0, $event, 'item')"
                    :value.sync="item_amount"
                    type="number"
                    placeholder="Item Amount"
                    min="0"
                  >
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
                <CCol col="3"> </CCol>
                <CCol
                  col="1"
                  style="padding-left: 0; cursor: pointer"
                  @click="addItem()"
                >
                  <CInput class="plus">
                    <template #prepend-content
                      ><CIcon name="cil-plus"
                    /></template>
                  </CInput>
                </CCol>
              </CRow>

              <CRow>
                <CCol col="12" style="margin-top: 6px">
                  <label>Claim Labour</label>
                </CCol>
              </CRow>
              <CRow
                v-for="(labour, index) in labours"
                :key="'labour-' + labour.item_id"
              >
                <CCol col="5" class="item-container">
                  <div class="item-id">{{ labour.item_id }}.</div>
                  <CInput
                    class="item-input"
                    disabled
                    :value.sync="labour.item"
                  />
                </CCol>
                <CCol col="3">
                  <CInput
                    v-on:input="updateAmount(labour.item_id, $event, 'labour')"
                    :value.sync="labour.amount"
                  >
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
                <CCol col="3">
                  <CInput disabled :value.sync="labour.recommended">
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
                <CCol
                  col="1"
                  style="padding-left: 0; cursor: pointer"
                  @click="minusLabour(labour.item_id)"
                >
                  <CInput class="plus">
                    <template #prepend-content
                      ><CIcon name="cil-minus"
                    /></template>
                  </CInput>
                </CCol>
              </CRow>

              <CRow>
                <CCol col="5" class="item-container">
                  <div class="item-id">{{ labours.length + 1 }}.</div>
                  <CInput
                    class="item-input"
                    placeholder="Item"
                    :value.sync="labour_input"
                  />
                </CCol>
                <CCol col="3">
                  <CInput
                    v-on:input="updateAmount(0, $event, 'labour')"
                    :value.sync="labour_amount"
                    type="number"
                    placeholder="Item Amount"
                    min="0"
                  >
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
                <CCol col="3"> &nbsp; </CCol>
                <CCol
                  col="1"
                  style="padding-left: 0; cursor: pointer"
                  @click="addLabour()"
                >
                  <CInput class="plus">
                    <template #prepend-content
                      ><CIcon name="cil-plus"
                    /></template>
                  </CInput>
                </CCol>
              </CRow>

              <CRow>
                <CCol col="5" style="margin-top: 6px">
                  Total Claim Amount
                </CCol>
                <CCol col="3">
                  <CInput disabled :value.sync="total_claim_amount">
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
                <CCol col="3">
                  <CInput disabled :value.sync="total_new_claim_amount">
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
              </CRow>
            </template>
            <template v-else>
              <CRow>
                <CCol col="7" style="margin-top: 6px">
                  <label>Claim Item</label>
                </CCol>
                <CCol style="margin-top: 6px">
                  <label>Price</label>
                </CCol>
              </CRow>
              <CRow
                v-for="(item, index) in items"
                :key="'item-' + item.item_id"
              >
                <CCol col="7" class="item-container">
                  <div class="item-id">{{ item.item_id }}.</div>
                  <CInput class="item-input" disabled :value.sync="item.item" />
                </CCol>
                <CCol col="4">
                  <CInput disabled :value.sync="item.amount">
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
                <CCol
                  col="1"
                  style="padding-left: 0; cursor: pointer"
                  @click="minusItem(item.item_id)"
                >
                  <CInput class="plus">
                    <template #prepend-content
                      ><CIcon name="cil-minus"
                    /></template>
                  </CInput>
                </CCol>
              </CRow>

              <CRow>
                <CCol col="7" class="item-container">
                  <div class="item-id">{{ items.length + 1 }}.</div>
                  <CInput
                    class="item-input"
                    name="claim-item"
                    placeholder="Item"
                    :value.sync="item_input"
                  />
                </CCol>
                <CCol col="4">
                  <CInput
                    v-on:input="updateAmount(0, $event, 'item')"
                    name="claim-item-amount"
                    :value.sync="item_amount"
                    type="number"
                    placeholder="Item Amount"
                    min="0"
                  >
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
                <CCol
                  col="1"
                  style="padding-left: 0; cursor: pointer"
                  @click="addItem()"
                >
                  <CInput class="plus">
                    <template #prepend-content
                      ><CIcon name="cil-plus"
                    /></template>
                  </CInput>
                </CCol>
              </CRow>

              <CRow>
                <CCol col="12" style="margin-top: 6px">
                  <label>Claim Labour</label>
                </CCol>
              </CRow>
              <CRow
                v-for="(labour, index) in labours"
                :key="'labour-' + labour.item_id"
              >
                <CCol col="7" class="item-container">
                  <div class="item-id">{{ labour.item_id }}.</div>
                  <CInput
                    class="item-input"
                    disabled
                    :value.sync="labour.item"
                  />
                </CCol>
                <CCol col="4">
                  <CInput disabled :value.sync="labour.amount">
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
                <CCol
                  col="1"
                  style="padding-left: 0; cursor: pointer"
                  @click="minusLabour(labour.item_id)"
                >
                  <CInput class="plus">
                    <template #prepend-content
                      ><CIcon name="cil-minus"
                    /></template>
                  </CInput>
                </CCol>
              </CRow>

              <CRow>
                <CCol col="7" class="item-container">
                  <div class="item-id">{{ labours.length + 1 }}.</div>
                  <CInput
                    class="item-input"
                    placeholder="Item"
                    name="claim-labour"
                    :value.sync="labour_input"
                  />
                </CCol>
                <CCol col="4">
                  <CInput
                    v-on:input="updateAmount(0, $event, 'labour')"
                    name="claim-labour-amount"
                    :value.sync="labour_amount"
                    type="number"
                    placeholder="Item Amount"
                    min="0"
                  >
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
                <CCol
                  col="1"
                  style="padding-left: 0; cursor: pointer"
                  @click="addLabour()"
                >
                  <CInput class="plus">
                    <template #prepend-content
                      ><CIcon name="cil-plus"
                    /></template>
                  </CInput>
                </CCol>
              </CRow>

              <CRow>
                <CCol col="7" style="margin-top: 6px">
                  Total Claim Amount
                </CCol>
                <CCol col="4">
                  <CInput disabled :value.sync="total_claim_amount">
                    <template #prepend-content
                      ><CIcon name="cil-dollar"
                    /></template>
                  </CInput>
                </CCol>
              </CRow>
            </template>
            <CTextarea
              name="cause_of_damage"
              label="Details of Loss"
              :value.sync="cause_of_damage"
              rows="5"
            />

            <CTextarea
              name="remarks"
              label="Additional Remarks"
              :value.sync="remarks"
              rows="5"
            />

            <DDFileUpload
              name="damage"
              label="Upload image(s) to state details of loss"
              type="damage"
              url="/api/claims/upload"
              ref="damage"
            />

            <DDFileUpload
              name="quotation"
              label="Supplier Invoice(s)"
              type="quotation"
              url="/api/claims/upload"
              ref="quotation"
            />

            <DDFileUpload
              name="service"
              label="Service Record(s)"
              type="service"
              url="/api/claims/upload"
              ref="service"
            />

            <div class="form-actions">
              <CButton @click="submit()" color="primary">Submit</CButton>
              <CButton @click="draft()" color="success">Save Draft</CButton>
              <CButton
                v-if="this.claim != null"
                @click="archive()"
                color="danger"
                >Archive</CButton
              >
              <CButton @click="cancel()" color="secondary">Cancel</CButton>
            </div>
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
      damage_files: [],
      quotation_files: [],
      service_files: [],
      file_paths: [],
      labours: [],
      items: [],
      insurer_options: [],
      registration_no: "",
      chassis_no: "",
      model: "",
      make: "",
      mileage: "",
      nric_uen: "",
      insurer_id: "",
      policy_name: "",
      policy_certificate_no: "",
      policy_coverage_from: "",
      policy_coverage_to: "",
      policy_nric_uen: "",
      date_of_notification: "",
      date_of_loss: "",
      cause_of_damage: "",
      total_claim_amount: "0",
      total_new_claim_amount: "0",
      labour_input: "",
      labour_amount: "",
      item_input: "",
      item_amount: "",
      remarks: "",
      multiple: true,
    };
  },
  mounted() {
    if (this.$route.name != "Create Claim" && this.claim != null) {
      this.policy_certificate_no = this.claim.policy_certificate_no;
      this.policy_coverage_from = this.claim.policy_coverage_from;
      this.policy_coverage_to = this.claim.policy_coverage_to;
      this.policy_name = this.claim.policy_name;
      this.policy_nric_uen = this.claim.policy_nric_uen;
      this.insurer_id = this.claim.insurer_id;
      this.date_of_loss = this.claim.date_of_loss;
      this.date_of_notification = this.claim.date_of_notification;
      this.cause_of_damage = this.claim.cause_of_damage;
      this.created_at = this.claim.created_at;
      this.registration_no = this.claim.vehicle.registration_no;
      this.chassis_no = this.claim.vehicle.chassis_no;
      this.make = this.claim.vehicle.make;
      this.model = this.claim.vehicle.model;
      this.mileage = this.claim.vehicle.mileage;
      this.nric_uen = this.claim.vehicle.nric_uen;
      this.status = this.$helpers.unslugify(this.claim.status);
      this.remarks = this.claim.remarks;
      this.total_claim_amount = this.claim.total_claim_amount;
      this.total_new_claim_amount = 0;
      for (var i = 0; i < this.claim.items.length; i++) {
        this.total_new_claim_amount += parseFloat(
          this.claim.items[i].recommended
        );
        this[this.claim.items[i].type + "s"].push(this.claim.items[i]);
      }
      this.total_new_claim_amount = this.total_new_claim_amount.toFixed(2);
      for (var i = 0; i < this.claim.documents.length; i++) {
        this.$refs[this.claim.documents[i].type].$data.files.push(
          this.claim.documents[i]
        );
      }
    } else {
      this.$store.commit("SET", ["claim", null]);
    }

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
    chassis_no: function (val) {},
    policy_nric_uen: function (val) {
      // CARFRENCLAIMS
    },
    items: function (val) {
      //this.total_claim_amount = 0;
      this.total_new_claim_amount = 0;
      for (var i = 0; i < this.labours.length; i++) {
        //this.total_claim_amount += parseFloat(this.labours[i].amount);
        this.total_new_claim_amount += parseFloat(this.labours[i].recommended);
      }
      for (var i = 0; i < val.length; i++) {
        //this.total_claim_amount += parseFloat(val[i].amount);
        this.total_new_claim_amount += parseFloat(val[i].recommended);
      }
      this.total_new_claim_amount = this.total_new_claim_amount.toFixed(2);
    },
    labours: function (val) {
      //this.total_claim_amount = 0;
      this.total_new_claim_amount = 0;
      for (var i = 0; i < val.length; i++) {
        //this.total_claim_amount += parseFloat(val[i].amount);
        this.total_new_claim_amount += parseFloat(val[i].recommended);
      }
      for (var i = 0; i < this.items.length; i++) {
        //this.total_claim_amount += parseFloat(this.items[i].amount);
        this.total_new_claim_amount += parseFloat(this.items[i].recommended);
      }
      //this.total_claim_amount = this.total_claim_amount.toFixed(2);
      this.total_new_claim_amount = this.total_new_claim_amount.toFixed(2);
    },
  },
  methods: {
    addFileToDocuments(ref, inputs) {
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
    },
    updateAmount(item_id, value, type) {
      this.total_claim_amount = 0;
      for (var i = 0; i < this.labours.length; i++) {
        if (type == "labour" && item_id == this.labours[i].item_id) {
          this.total_claim_amount += parseFloat(value);
        } else {
          this.total_claim_amount += parseFloat(this.labours[i].amount);
        }
      }
      for (var i = 0; i < this.items.length; i++) {
        if (type == "item" && item_id == this.items[i].item_id) {
          this.total_claim_amount += parseFloat(value);
        } else {
          this.total_claim_amount += parseFloat(this.items[i].amount);
        }
      }

      if (item_id == 0) {
        if (!isNaN(parseFloat(value))) {
          this.total_claim_amount += parseFloat(value);
        }
        if (type == "labour") {
          if (!isNaN(parseFloat(this.item_amount))) {
            this.total_claim_amount += parseFloat(this.item_amount);
          }
        } else {
          if (!isNaN(parseFloat(this.labour_amount))) {
            this.total_claim_amount += parseFloat(this.labour_amount);
          }
        }
      } else {
        if (!isNaN(parseFloat(this.labour_amount))) {
          this.total_claim_amount += parseFloat(this.labour_amount);
        }
        if (!isNaN(parseFloat(this.item_amount))) {
          this.total_claim_amount += parseFloat(this.item_amount);
        }
      }
    },
    minusItem(id) {
      var arr = [];
      var tempId = 1;
      for (var i = 0; i < this.items.length; i++) {
        if (this.items[i].item_id != id) {
          var input = {};
          input.item_id = tempId;
          input.item = this.items[i].item;
          input.amount = this.items[i].amount;
          input.recommended = this.items[i].recommended;
          input.type = "item";
          arr.push(input);
          tempId++;
        }
      }
      this.items = arr;
    },
    addItem() {
      if (this.item_input == "" || this.item_amount == "") {
        Vue.toasted.error("Please fill in item and amount.");
        return;
      }
      var item = {};
      item.item_id = this.items.length + 1;
      item.item = this.item_input;
      item.amount = this.item_amount;
      item.recommended = 0;
      item.type = "item";
      this.items.push(item);
      this.item_input = "";
      this.item_amount = "";
    },
    minusLabour(id) {
      var arr = [];
      var tempId = 1;
      for (var i = 0; i < this.labours.length; i++) {
        if (this.labours[i].item_id != id) {
          var input = {};
          input.item_id = tempId;
          input.item = this.labours[i].item;
          input.amount = this.labours[i].amount;
          input.recommended = this.labours[i].recommended;
          input.type = "labour";
          arr.push(input);
          tempId++;
        }
      }
      this.labours = arr;
    },
    addLabour() {
      if (this.labour_input == "" || this.labour_amount == "") {
        Vue.toasted.error("Please fill in item and amount.");
        return;
      }
      var labour = {};
      labour.item_id = this.labours.length + 1;
      labour.item = this.labour_input;
      labour.amount = this.labour_amount;
      labour.recommended = 0;
      labour.type = "labour";
      this.labours.push(labour);
      this.labour_input = "";
      this.labour_amount = "";
    },
    submit() {
      var inputs = this.formatInput();
      if (this.claim != null) {
        inputs.id = this.claim.id;
      }
      inputs.url = "/api/claims/create";

      this.$store.dispatch("API", inputs).then(() => {
        this.$router.push("/claims");
      });
    },
    archive() {
      if (this.claim != null) {
        var inputs = {};
        inputs.method = "post";
        inputs.id = this.claim.id;
        inputs.url = "/api/claims/archive";
        this.$store.dispatch("API", inputs).then(() => {
          this.$router.push("/claims/archives");
        });
      }
    },
    draft() {
      var inputs = this.formatInput();
      if (this.claim != null) {
        inputs.id = this.claim.id;
      }
      inputs.url = "/api/claims/draft";
      this.$store.dispatch("API", inputs).then(() => {
        this.$router.push("/claims/drafts");
      });
    },
    formatInput() {
      var inputs = {};
      inputs.method = "post";
      inputs.registration_no = this.registration_no;
      inputs.chassis_no = this.chassis_no;
      inputs.make = this.make;
      inputs.model = this.model;
      inputs.mileage = this.mileage;
      inputs.nric_uen = this.nric_uen;
      inputs.policy_name = this.policy_name;
      inputs.policy_certificate_no = this.policy_certificate_no;
      inputs.policy_coverage_from = this.policy_coverage_from;
      inputs.policy_coverage_to = this.policy_coverage_to;
      inputs.policy_nric_uen = this.policy_nric_uen;
      inputs.date_of_notification = this.date_of_notification;
      inputs.date_of_loss = this.date_of_loss;
      inputs.cause_of_damage = this.cause_of_damage;
      inputs.total_claim_amount = this.total_claim_amount;
      inputs.insurer_id = this.insurer_id;
      inputs.remarks = this.remarks;
      inputs.documents = [];
      inputs.items = [];

      this.addFileToDocuments("damage", inputs);
      this.addFileToDocuments("quotation", inputs);
      this.addFileToDocuments("service", inputs);

      if (
        (this.item_input == "" && this.item_amount != "") ||
        (this.labour_input == "" && this.labour_amount != "")
      ) {
        return inputs;
      }

      if (this.item_input != "" && this.item_amount != "") {
        this.addItem();
      }
      for (var i = 0; i < this.items.length; i++) {
        inputs.items.push(this.items[i]);
      }
      if (this.labour_input != "" && this.labour_amount != "") {
        this.addLabour();
      }
      for (var i = 0; i < this.labours.length; i++) {
        inputs.items.push(this.labours[i]);
      }

      return inputs;
    },
    cancel() {
      this.$router.go(-1);
    },
  },
  computed: {
    ...mapGetters(["claim"]),
  },
};
</script>
