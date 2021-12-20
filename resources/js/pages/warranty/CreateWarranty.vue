<template>
  <form>
    <CRow>
      <CCol lg="12">
        <CCard>
          <CCardHeader>
            {{ $route.name }}
            <span
              style="text-transform: capitalize"
              v-if="warranty != null && $route.name != 'Create Warranty Order'"
            >
              - {{ warranty.status }}
            </span>
          </CCardHeader>
          <CCardBody>
            <CRow>
              <CCol md="6">
                <div role="group" class="form-group">
                  <label for="exampleDataList">Vehicle Make</label>
                  <input
                    class="form-control"
                    autocomplete="off"
                    v-model="make"
                    list="makeListOptions"
                    id="make"
                    placeholder="Type to search..."
                  />
                  <datalist id="makeListOptions">
                    <template v-for="(row, i) in makes">
                      <option :value="row.make" :key="i"></option>
                    </template>
                  </datalist>
                </div>
              </CCol>
              <CCol md="6">
                <div role="group" class="form-group">
                  <label for="exampleDataList">Vehicle Model</label>
                  <input
                    class="form-control"
                    autocomplete="off"
                    v-model="model"
                    list="modelListOptions"
                    id="model"
                    placeholder="Type to search..."
                  />
                  <datalist id="modelListOptions">
                    <template v-for="(row, i) in models">
                      <option :value="row.model" :key="i"></option>
                    </template>
                  </datalist>
                </div>
              </CCol>
            </CRow>
            <CRow>
              <CCol md="6">
                <label>Vehicle Type</label>
                <br />
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="type"
                    class="form-check-input"
                    id="new"
                    type="radio"
                    value="new"
                    name="type"
                  />
                  <label class="form-check-label" for="new">New</label>
                </div>
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="type"
                    class="form-check-input"
                    id="preowned"
                    type="radio"
                    value="preowned"
                    name="type"
                  />
                  <label class="form-check-label" for="preowned"
                    >Preowned</label
                  >
                </div>
              </CCol>
              <CCol md="6">
                <label>Vehicle Fuel</label>
                <br />
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="fuel"
                    class="form-check-input"
                    id="non-hybrid"
                    type="radio"
                    value="non_hybrid"
                    name="fuel-type"
                  />
                  <label class="form-check-label" for="non-hybrid"
                    >Non-Hybrid</label
                  >
                </div>
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="fuel"
                    class="form-check-input"
                    id="hybrid"
                    type="radio"
                    value="hybrid"
                    name="fuel-type"
                  />
                  <label class="form-check-label" for="hybrid">Hybrid</label>
                </div>
              </CCol>
            </CRow>
            <CRow class="mt-3 mb-3">
              <CCol class="text-center">
                <b v-if="warranty_prices.length > 0">Select Warranty Plan</b>
                <b v-else-if="make != '' && model != ''" style="color: red"
                  >Select Warranty Plan (Vehicle not found)</b
                >
                <b v-else>No Warranty Plan</b>
              </CCol>
            </CRow>
            <CRow class="mb-3" style="justify-content: center">
              <template v-if="warranty_prices.length > 0">
                <CCol
                  md="4"
                  v-for="row in warranty_prices"
                  :key="row.id"
                  @click="
                    selectWarranty(
                      row.warranty_duration,
                      row.package,
                      row.mileage_coverage,
                      row
                    )
                  "
                >
                  <CCard
                    :class="{
                      'border-primary':
                        row.warranty_duration == selected_warranty &&
                        row.package == package,
                    }"
                    class="warranty-selection"
                  >
                    <CCardHeader class="text-center">{{
                      extended && type == "new" ? "III x CarFren+" : row.package
                    }}</CCardHeader>
                    <CCardBody>
                      <p class="price">
                        {{
                          extended && type == "new"
                            ? "$" + (row.price * 1.1).toFixed(2)
                            : row.format_price
                        }}
                      </p>
                      <p class="mileage-coverage">
                        {{ row.warranty_period }} warranty
                      </p>
                      <p class="mileage-coverage">
                        Mileage Coverage {{ row.format_mileage_coverage }}
                      </p>
                      <p class="claim">
                        {{ row.format_max_claim }} max claim per year
                      </p>
                    </CCardBody>
                  </CCard>
                </CCol>
              </template>
              <template v-else-if="make != '' && model != ''">
                <template v-for="(row, i) in packages">
                  <template v-if="type == row.type">
                    <CCol
                      :key="i"
                      md="4"
                      @click="
                        selectWarranty(
                          row.duration,
                          row.name,
                          row.mileage_coverage
                        )
                      "
                    >
                      <CCard
                        v-if="row.type == type"
                        :class="{
                          'border-primary':
                            selected_warranty == row.duration &&
                            package == row.name,
                        }"
                        class="warranty-selection"
                      >
                        <CCardHeader class="text-center">{{
                          extended && type == "new"
                            ? "III x CarFren+"
                            : row.name
                        }}</CCardHeader>
                        <CCardBody>
                          <p class="price">Request for Quote</p>
                          <p class="mileage-coverage">
                            {{ row.package_period }} warranty
                          </p>
                          <p class="mileage-coverage">
                            Mileage Coverage {{ row.format_mileage_coverage }}
                          </p>
                        </CCardBody>
                      </CCard>
                    </CCol>
                  </template>
                </template>
              </template>
            </CRow>
            <CRow
              v-if="
                (warranty_prices.length > 0 || (make != '' && model != '')) &&
                type == 'new'
              "
              style="margin-bottom: 20px"
            >
              <CCol md="6">
                <div class="form-check checkbox">
                  <input
                    class="form-check-input"
                    :disabled="user.company.extended_warranty == 1"
                    v-model="extended"
                    id="extended"
                    type="checkbox"
                    value=""
                  />
                  <label class="" for="extended"
                    >Add CarFren+ Extended Warranty
                    <a href="/documents/extended.pdf" target="_blank"
                      ><img src="/images/vue/question.png" /></a
                  ></label>
                </div>
              </CCol>
            </CRow>
            <template v-if="selected_warranty != null">
              <CRow>
                <CCol md="6">
                  <CInput
                    type="date"
                    required
                    name="start_date"
                    label="Proposed Warranty Start Date"
                    :value.sync="start_date"
                  />
                </CCol>
              </CRow>
              <CRow>
                <CCol md="6">
                  <CInput
                    required
                    name="registration_no"
                    label="Vehicle Number"
                    :value.sync="registration_no"
                  />
                </CCol>
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
                    name="engine_no"
                    label="Vehicle Engine Number"
                    :value.sync="engine_no"
                  />
                </CCol>
                <CCol md="6">
                  <CInput
                    required
                    :disabled="price != '' && price != null"
                    name="capacity"
                    label="Vehicle Capacity"
                    :value.sync="capacity"
                  />
                </CCol>
                <CCol md="6">
                  <CInput
                    required
                    name="manufacture_year"
                    label="Vehicle Manufacture Year"
                    :value.sync="manufacture_year"
                  />
                </CCol>
                <template v-if="type == 'preowned'">
                  <CCol md="6">
                    <CInput
                      required
                      name="mileage"
                      label="Vehicle Mileage (KM)"
                      :value.sync="mileage"
                    />
                  </CCol>
                  <CCol md="6">
                    <CInput
                      type="date"
                      required
                      name="registration_date"
                      label="Vehicle Registration Date"
                      :value.sync="registration_date"
                    />
                  </CCol>
                </template>
                <template v-else>
                  <CCol md="6">
                    <CInput
                      type="date"
                      required
                      name="registration_date"
                      label="Vehicle Registration Date"
                      :value.sync="registration_date"
                    />
                  </CCol>
                </template>
              </CRow>
              <CRow>
                <CCol md="6">
                  <CSelect
                    required
                    name="salutation"
                    label="Customer Salutation"
                    :value.sync="salutation"
                    :options="salutation_options"
                    placeholder="Please select"
                  />
                </CCol>
                <CCol md="6">
                  <CInput
                    required
                    name="nric_uen"
                    label="Customer NRIC/UEN Number"
                    :value.sync="nric_uen"
                  />
                </CCol>
              </CRow>
              <CRow>
                <CCol md="6">
                  <CInput
                    required
                    name="name"
                    label="Customer Name"
                    :value.sync="name"
                  />
                </CCol>
                <CCol md="6">
                  <CInput
                    required
                    name="address"
                    label="Customer Address"
                    :value.sync="address"
                  />
                </CCol>
              </CRow>
              <CRow>
                <CCol md="6">
                  <CInput
                    type="email"
                    required
                    name="email"
                    label="Customer Email"
                    :value.sync="email"
                  />
                </CCol>
                <CCol md="6">
                  <CInput
                    required
                    prepend="+65"
                    name="phone"
                    label="Customer Phone"
                    :value.sync="phone"
                  />
                </CCol>
              </CRow>

              <CTextarea
                name="remarks"
                label="Additional Remarks"
                :value.sync="remarks"
                rows="5"
              />

              <DDFileUpload
                name="log"
                label="Log Card"
                type="log"
                url="/api/warranties/upload"
                ref="log"
              />

              <template v-if="type == 'preowned'">
                <DDFileUpload
                  name="assessment"
                  label="Car Assessment Report"
                  type="assessment"
                  url="/api/warranties/upload"
                  ref="assessment"
                />
              </template>
              <template v-if="price != ''">
                <label>Signature</label>
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
              </template>
              <div class="form-actions mt-3">
                <CButton @click="submit()" color="primary">Submit</CButton>
                <CButton @click="draft()" color="success">Save Draft</CButton>
                <CButton
                  v-if="warranty != null"
                  @click="archive()"
                  color="danger"
                  >Archive</CButton
                >
                <CButton @click="cancel()" color="secondary">Cancel</CButton>
              </div>
            </template>
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
      firstLoad: true,
      type: "new",
      fuel: "non_hybrid",
      start_date: "",
      file_paths: [],
      log_files: [],
      assessment_files: [],
      warranty_prices: [],
      selected_warranty: null,
      registration_no: "",
      engine_no: "",
      registration_date: "",
      vehicle_id: "",
      customer_id: "",
      chassis_no: "",
      model: "",
      make: "",
      price: "",
      max_claim: "",
      mileage_coverage: "",
      warranty_duration: "",
      manufacture_year: "",
      makes: [],
      models: [],
      packages: [],
      mileage: "",
      capacity: "",
      category: "",
      nric_uen: "",
      salutation: "",
      name: "",
      address: "",
      email: "",
      phone: "",
      insurer_id: "",
      remarks: "",
      package: "",
      extended: false,
      multiple: true,
      salutation_options: [
        { label: "Mr", value: "Mr" },
        { label: "Ms", value: "Ms" },
        { label: "Mrs", value: "Mrs" },
        { label: "Mdm", value: "Mdm" },
        { label: "Company", value: "Company" },
      ],
    };
  },
  mounted() {
    if (this.$route.name != "Create Warranty Order" && this.warranty != null) {
      this.registration_no = this.warranty.vehicle.registration_no;
      this.chassis_no = this.warranty.vehicle.chassis_no;
      this.make = this.warranty.vehicle.make;
      this.model = this.warranty.vehicle.model;
      this.type = this.$helpers.slugify(this.warranty.vehicle.type);
      this.fuel = this.$helpers.slugify(this.warranty.vehicle.fuel);
      this.mileage = this.warranty.vehicle.mileage;
      this.nric_uen = this.warranty.vehicle.nric_uen;
      this.engine_no = this.warranty.vehicle.engine_no;
      this.manufacture_year = this.warranty.vehicle.manufacture_year;
      this.capacity = this.warranty.vehicle.capacity;
      this.registration_date = this.warranty.vehicle.registration_date;
      this.category = this.warranty.vehicle.category;
      this.vehicle_id = this.warranty.vehicle_id;
      this.remarks = this.warranty.remarks;
      this.package = this.warranty.package;
      this.proposer_id = this.warranty.proposer_id;
      this.nric_uen = this.warranty.nric_uen;
      this.price = this.warranty.price;
      this.max_claim = this.warranty.max_claim;
      this.extended = this.warranty.extended;
      this.mileage_coverage = this.warranty.mileage_coverage;
      this.warranty_duration = this.warranty.warranty_duration;
      this.start_date = this.warranty.start_date;
      this.name = this.warranty.proposer.name;
      this.salutation = this.warranty.proposer.salutation;
      this.nric_uen = this.warranty.proposer.nric_uen;
      this.address = this.warranty.proposer.address;
      this.email = this.warranty.proposer.email;
      this.phone =
        this.warranty.proposer.phone != null
          ? this.warranty.proposer.phone
              .split("+65")
              .join("")
              .split("+65 ")
              .join("")
          : this.warranty.proposer.phone;
      this.insurer_id = this.warranty.insurer_id;
      this.selected_warranty = parseFloat(this.warranty.warranty_duration);
      //if(this.warranty.price == null){
      //  this.selected_warranty = parseFloat(this.warranty.warranty_duration * -1);
      //}

      setTimeout(() => {
        for (var i = 0; i < this.warranty.documents.length; i++) {
          if (this.warranty.documents[i].type != "signature") {
            this.$refs[this.warranty.documents[i].type].$data.files.push(
              this.warranty.documents[i]
            );
          }
        }
      }, 500);

      this.status = this.$helpers.unslugify(this.warranty.status);
    } else {
      this.$store.commit("SET", ["warranty", null]);
    }
    this.getMakes();
    this.getPackages();
  },
  watch: {
    user: function (val) {
      if (val.company != null) {
        this.extended = val.company.extended_warranty;
      }
    },
    make: function (val) {
      this.getModels(val);
      this.getWarrantyPrices("make", val);
    },
    model: function (val) {
      this.getWarrantyPrices("model", val);
    },
    type: function (val) {
      this.getWarrantyPrices("type", val);
    },
    fuel: function (val) {
      this.getWarrantyPrices("fuel", val);
    },
  },
  methods: {
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
    undo() {
      this.$refs.signaturePad.undoSignature();
    },
    getPackages() {
      var inputs = {};
      inputs.url = "/api/packages";
      this.$store.dispatch("API", inputs).then((response) => {
        this.packages = response.packages;
      });
    },
    getMakes() {
      var inputs = {};
      inputs.url = "/api/warranty/makes";
      this.$store.dispatch("API", inputs).then((response) => {
        this.makes = response.makes;
      });
    },
    getModels(val) {
      if (val != "") {
        if (!this.firstLoad) {
          this.models = [];
          this.model = "";
        }
        var inputs = {};
        inputs.url = "/api/warranty/models";
        inputs.make = val;
        inputs.method = "POST";
        this.$store.dispatch("API", inputs).then((response) => {
          this.models = response.models;
        });
      }
    },
    getWarrantyPrices(param, val) {
      this.warranty_prices = [];
      if (!this.firstLoad) {
        this.selected_warranty = null;
      }
      var inputs = {};
      inputs.make = this.make;
      inputs.model = this.model;
      inputs.type = this.type;
      inputs.fuel = this.fuel;
      inputs[param] = val;
      inputs.url = "/api/warranties/searchPrices";
      inputs.method = "POST";
      if (
        inputs.make != "" &&
        inputs.model != "" &&
        inputs.type != "" &&
        inputs.fuel != ""
      ) {
        this.$store.dispatch("API", inputs).then((response) => {
          this.warranty_prices = response.prices;
          this.firstLoad = false;
        });
      }
    },
    selectWarranty(duration, packageName, mileage_coverage, row = null) {
      this.selected_warranty = duration;
      this.package = packageName;
      this.mileage_coverage = mileage_coverage;
      if (row == null) {
        this.warranty_duration = duration;
        this.price = "";
        this.max_claim = "";
        this.capacity = "";
        this.category = "";
        this.insurer_id = "";
      } else {
        this.warranty_duration = row.warranty_duration;
        this.price = row.price;
        this.max_claim = row.max_claim;
        this.capacity = row.capacity;
        this.category = row.category;
        this.insurer_id = row.insurer_id;
      }
    },
    submit() {
      var inputs = this.formatInput();
      if (this.warranty != null) {
        inputs.id = this.warranty.id;
      }
      if (this.price != "") {
        const { isEmpty, data } = this.$refs.signaturePad.saveSignature();
        inputs.signature = data;
      }
      inputs.url = "/api/warranties/create";
      this.$store.dispatch("API", inputs).then((res) => {
        this.$router.push("/warranties/details/" + res.id);
      });
    },
    archive() {
      if (this.warranty != null) {
        var inputs = {};
        inputs.method = "post";
        inputs.id = this.warranty.id;
        inputs.url = "/api/warranties/archive";
        this.$store.dispatch("API", inputs).then(() => {
          this.$router.push("/warranties/archives");
        });
      }
    },
    draft() {
      var inputs = this.formatInput();
      if (this.warranty != null) {
        inputs.id = this.warranty.id;
      }
      inputs.url = "/api/warranties/draft";
      this.$store.dispatch("API", inputs).then(() => {
        this.$router.push("/warranties/drafts");
      });
    },
    formatInput() {
      var inputs = {};
      inputs.method = "post";
      inputs.registration_no = this.registration_no;
      inputs.chassis_no = this.chassis_no;
      inputs.engine_no = this.engine_no;
      inputs.make = this.make;
      inputs.model = this.model;
      inputs.mileage = this.mileage;
      inputs.capacity = this.capacity;
      inputs.category = this.category;
      inputs.registration_date = this.registration_date;
      inputs.type = this.type;
      inputs.fuel = this.fuel;
      inputs.price = this.price;
      inputs.manufacture_year = this.manufacture_year;
      inputs.max_claim = this.max_claim;
      inputs.mileage_coverage = this.mileage_coverage;
      inputs.warranty_duration = this.warranty_duration;
      inputs.remarks = this.remarks;
      inputs.package = this.package;
      inputs.start_date = this.start_date;
      inputs.name = this.name;
      inputs.nric_uen = this.nric_uen;
      inputs.salutation = this.salutation;
      inputs.address = this.address;
      inputs.email = this.email;
      inputs.phone = "+65 " + this.phone;
      inputs.insurer_id = this.insurer_id;
      inputs.vehicle_id = this.vehicle_id;
      inputs.proposer_id = this.proposer_id;
      inputs.extended = this.extended;
      inputs.documents = [];

      this.addFileToDocuments("log", inputs);
      this.addFileToDocuments("assessment", inputs);

      return inputs;
    },
    cancel() {
      this.$router.go(-1);
    },
  },
  computed: {
    ...mapGetters(["warranty", "user"]),
  },
};
</script>
