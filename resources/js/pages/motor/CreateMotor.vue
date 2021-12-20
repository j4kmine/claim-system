<template>
  <form v-on:submit.prevent="submit()">
    <CRow>
      <CCol lg="12">
        <CCard>
          <CCardHeader>
            {{ $route.name }}
            <span
              style="text-transform: capitalize"
              v-if="motor != null && $route.name != 'Create Motor Order'"
            >
              - {{ motor.status }}
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
                <div
                  v-if="loading"
                  class="custom-center"
                  style="margin-top: 18px"
                >
                  <CSpinner color="info" />
                </div>
                <div v-else role="group" class="form-group">
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
                    <template v-for="row in models">
                      <option :value="row.model"></option>
                    </template>
                  </datalist>
                </div>
              </CCol>
            </CRow>
            <CRow
              v-if="point == '' && make != '' && model != ''"
              class="mt-1 mb-3"
            >
              <CCol class="text-center"
                ><b style="color: red">Vehicle Not Found</b></CCol
              >
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
                <label>Usage of Vehicle</label>
                <br />
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="usage"
                    class="form-check-input"
                    id="private"
                    type="radio"
                    value="private"
                    name="usage"
                  />
                  <label class="form-check-label" for="private">Private</label>
                </div>
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="usage"
                    class="form-check-input"
                    id="phv"
                    type="radio"
                    value="phv"
                    name="usage"
                  />
                  <label class="form-check-label" for="phv">PHV</label>
                </div>
              </CCol>
            </CRow>
            <CRow>
              <CCol md="6">
                <label>Off-Peak Car</label>
                <br />
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="off_peak"
                    class="form-check-input"
                    id="off_peak_yes"
                    type="radio"
                    :value="true"
                    name="off_peak"
                  />
                  <label class="form-check-label" for="off_peak_yes">Yes</label>
                </div>
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="off_peak"
                    class="form-check-input"
                    id="off_peak_no"
                    type="radio"
                    :value="false"
                    name="off_peak"
                  />
                  <label class="form-check-label" for="off_peak_no">No</label>
                </div>
              </CCol>
              <CCol md="6">
                <label>Has the vehicle been modified?</label>
                <br />
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="modified"
                    class="form-check-input"
                    id="modified_yes"
                    type="radio"
                    :value="true"
                    name="modified"
                  />
                  <label class="form-check-label" for="modified_yes">Yes</label>
                </div>
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="modified"
                    class="form-check-input"
                    id="modified_yes"
                    type="radio"
                    :value="false"
                    name="modified"
                  />
                  <label class="form-check-label" for="modified_no">No</label>
                </div>
              </CCol>
              <CCol md="12" v-if="modified == 'true'">
                <CTextarea
                  name="modification_remarks"
                  label="Modification Remarks"
                  :value.sync="modification_remarks"
                  rows="5"
                />
              </CCol>
            </CRow>
            <CRow>
              <CCol md="6">
                <CInput
                  type="date"
                  required
                  name="start_date"
                  label="Insurance Start Date"
                  :min="min_date"
                  :value.sync="start_date"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  type="date"
                  required
                  name="expiry_date"
                  label="Insurance Expiry Date"
                  :min="min_date"
                  :value.sync="expiry_date"
                />
              </CCol>
              <CCol v-if="type == 'preowned'" md="6">
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
                  :disabled="point != '' && point != null"
                  name="body_type"
                  label="Vehicle Body Type"
                  :value.sync="body_type"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  required
                  :disabled="point != '' && point != null"
                  name="capacity"
                  label="Vehicle Capacity"
                  :value.sync="capacity"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  required
                  name="seating_capacity"
                  label="Vehicle Seating Capacity"
                  :value.sync="seating_capacity"
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
            </CRow>
            <CRow>
              <CCol col="12" class="line-seperator">
                <hr />
                <div>Policyholder Info</div>
                <hr />
              </CCol>
              <CCol md="6">
                <CSelect
                  required
                  name="salutation"
                  label="Salutation"
                  :value.sync="salutation"
                  :options="salutation_options"
                  placeholder="Please select"
                />
              </CCol>
              <CCol md="6">
                <CSelect
                  required
                  name="nric_type"
                  label="ID Type"
                  :value.sync="nric_type"
                  :options="nric_type_options"
                  placeholder="Please select"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  required
                  name="nric_uen"
                  label="ID Number"
                  :value.sync="nric_uen"
                />
              </CCol>
              <CCol md="6">
                <CInput required name="name" label="Name" :value.sync="name" />
              </CCol>
              <CCol md="6">
                <CSelect
                  required
                  name="nationality"
                  label="Nationality"
                  :value.sync="nationality"
                  :options="nationality_options"
                  placeholder="Please select"
                />
              </CCol>
              <CCol md="6">
                <CSelect
                  required
                  name="residential"
                  label="Residential Status"
                  :value.sync="residential"
                  :options="residential_options"
                  placeholder="Please select"
                />
              </CCol>
              <CCol md="6">
                <label>Gender</label>
                <br />
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="gender"
                    class="form-check-input"
                    id="male"
                    type="radio"
                    value="M"
                    name="gender"
                  />
                  <label class="form-check-label" for="male">Male</label>
                </div>
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="gender"
                    class="form-check-input"
                    id="female"
                    type="radio"
                    value="F"
                    name="gender"
                  />
                  <label class="form-check-label" for="female">Female</label>
                </div>
              </CCol>
              <CCol md="6">
                <CSelect
                  required
                  name="occupatiion"
                  label="Occupation"
                  :value.sync="occupation"
                  :options="occupation_options"
                  placeholder="Please select"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  type="date"
                  required
                  name="date_of_birth"
                  label="Date of Birth"
                  :value.sync="date_of_birth"
                />
              </CCol>

              <CCol md="6">
                <CInput
                  required
                  name="address"
                  label="Address"
                  :value.sync="address"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  required
                  name="postal_code"
                  label="Postal Code"
                  :value.sync="postal_code"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  type="email"
                  required
                  name="email"
                  label="Email"
                  :value.sync="email"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  required
                  prepend="+65"
                  name="phone"
                  label="Phone"
                  :value.sync="phone"
                />
              </CCol>
              <CCol class="questions" col="6">
                <div>Is the policyholder driving?</div>
                <div>
                  <label class="c-switch c-switch-info">
                    <input
                      type="checkbox"
                      class="c-switch-input"
                      v-model="policyholder_driving"
                    />
                    <span class="c-switch-slider"></span>
                  </label>
                </div>
              </CCol>
            </CRow>
            <CRow>
              <CCol col="12" class="line-seperator">
                <hr />
                <div>Main Driver Info</div>
                <hr />
              </CCol>
              <CCol md="6">
                <CSelect
                  :disabled="policyholder_driving"
                  required
                  name="main_nric_type"
                  label="ID Type"
                  :value.sync="main_nric_type"
                  :options="nric_type_options"
                  placeholder="Please select"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  :disabled="policyholder_driving"
                  required
                  name="main_nric_uen"
                  label="ID Number"
                  :value.sync="main_nric_uen"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  :disabled="policyholder_driving"
                  required
                  name="main_name"
                  label="Name"
                  :value.sync="main_name"
                />
              </CCol>
              <CCol md="6">
                <CSelect
                  :disabled="policyholder_driving"
                  required
                  name="main_nationality"
                  label="Nationality"
                  :value.sync="main_nationality"
                  :options="nationality_options"
                  placeholder="Please select"
                />
              </CCol>
              <CCol md="6">
                <CSelect
                  :disabled="policyholder_driving"
                  required
                  name="main_residential"
                  label="Residential Status"
                  :value.sync="main_residential"
                  :options="residential_options"
                  placeholder="Please select"
                />
              </CCol>
              <CCol md="6">
                <label>Gender</label>
                <br />
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    :disabled="policyholder_driving"
                    v-model="main_gender"
                    class="form-check-input"
                    id="main_male"
                    type="radio"
                    value="M"
                    name="main_gender"
                  />
                  <label class="form-check-label" for="main_male">Male</label>
                </div>
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    :disabled="policyholder_driving"
                    v-model="main_gender"
                    class="form-check-input"
                    id="main_female"
                    type="radio"
                    value="F"
                    name="main_gender"
                  />
                  <label class="form-check-label" for="main_female"
                    >Female</label
                  >
                </div>
              </CCol>
              <CCol md="6">
                <CInput
                  :disabled="policyholder_driving"
                  type="date"
                  required
                  name="main_date_of_birth"
                  label="Date of Birth"
                  :value.sync="main_date_of_birth"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  type="date"
                  required
                  name="main_date_of_license"
                  label="Date of License"
                  :value.sync="main_date_of_license"
                />
              </CCol>
              <CCol md="6">
                <label>Occupation</label>
                <br />
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="main_occupation"
                    class="form-check-input"
                    id="indoor"
                    type="radio"
                    value="Indoor"
                    name="main_occupation"
                  />
                  <label class="form-check-label" for="indoor">Indoor</label>
                </div>
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="main_occupation"
                    class="form-check-input"
                    id="outdoor"
                    type="radio"
                    value="Outdoor"
                    name="main_occupation"
                  />
                  <label class="form-check-label" for="outdoor">Outdoor</label>
                </div>
              </CCol>
              <CCol md="6">
                <CInput
                  type="number"
                  required
                  name="main_no_of_accidents"
                  label="Number of Accident ( Past 3 Years )"
                  :value.sync="main_no_of_accidents"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  type="number"
                  prepend="$"
                  required
                  name="main_total_claim"
                  label="Total Claim Amount ( Past 3 Years )"
                  :value.sync="main_total_claim"
                />
              </CCol>
              <CCol md="6">
                <CSelect
                  required
                  name="main_ncd"
                  label="No Claim Discount (NCD)"
                  :value.sync="main_ncd"
                  :options="ncd_options"
                  placeholder="Please select"
                />
              </CCol>
              <CCol class="questions" col="12">
                <div>
                  Does the Main Driver have any serious traffic offences or has
                  had his/her license suspended or cancelled in the last 3
                  years?
                </div>
                <div>
                  <label class="c-switch c-switch-info">
                    <input
                      v-model="main_serious_offence"
                      type="checkbox"
                      class="c-switch-input"
                    />
                    <span class="c-switch-slider"></span>
                  </label>
                </div>
              </CCol>
              <CCol col="12">
                <hr />
              </CCol>
              <CCol class="questions" col="12">
                <div>
                  Does the Main Driver have any physical disability or suffer
                  from any illness that may impair driving?
                </div>
                <div>
                  <label class="c-switch c-switch-info">
                    <input
                      v-model="main_physical_disable"
                      type="checkbox"
                      class="c-switch-input"
                    />
                    <span class="c-switch-slider"></span>
                  </label>
                </div>
              </CCol>
              <CCol col="12">
                <hr />
              </CCol>
              <CCol class="questions" col="12">
                <div>
                  Have you been refused motor insurance or declined renewal at
                  any time?
                </div>
                <div>
                  <label class="c-switch c-switch-info">
                    <input
                      v-model="main_refused"
                      type="checkbox"
                      class="c-switch-input"
                    />
                    <span class="c-switch-slider"></span>
                  </label>
                </div>
              </CCol>
              <CCol col="12">
                <hr />
              </CCol>
              <CCol class="questions" col="12">
                <div>
                  Have you had any insurance terminated in the last 12 months
                  due to a breach of any premium payment condition?
                </div>
                <div>
                  <label class="c-switch c-switch-info">
                    <input
                      v-model="main_terminated"
                      type="checkbox"
                      class="c-switch-input"
                    />
                    <span class="c-switch-slider"></span>
                  </label>
                </div>
              </CCol>
              <CCol col="12">
                <hr />
              </CCol>
            </CRow>
            <CRow v-for="(driver, index) in drivers" :key="'driver-' + index">
              <CCol col="12" class="line-seperator">
                <hr />
                <div>Named Driver Info {{ index + 1 }}</div>
                <hr />
              </CCol>
              <CCol md="12 mb-3">
                <CButton @click="removeDriver(index)" color="danger"
                  >Remove Named Driver</CButton
                >
              </CCol>
              <CCol md="6">
                <CSelect
                  required
                  :name="'nric_type_' + index"
                  label="ID Type"
                  v-model="drivers[index].nric_type"
                  :options="nric_type_options"
                  placeholder="Please select"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  required
                  :name="'nric_uen_' + index"
                  label="ID Number"
                  v-model="drivers[index].nric_uen"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  required
                  :name="'name_' + index"
                  label="Name"
                  v-model="drivers[index].name"
                />
              </CCol>
              <CCol md="6">
                <CSelect
                  required
                  :name="'nationality_' + index"
                  label="Nationality"
                  v-model="drivers[index].nationality"
                  :options="nationality_options"
                  placeholder="Please select"
                />
              </CCol>
              <CCol md="6">
                <CSelect
                  required
                  :name="'residential_' + index"
                  label="Residential Status"
                  v-model="drivers[index].residential"
                  :options="residential_options"
                  placeholder="Please select"
                />
              </CCol>
              <CCol md="6">
                <label>Gender</label>
                <br />
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="drivers[index].gender"
                    class="form-check-input"
                    :id="'male_' + index"
                    type="radio"
                    value="M"
                    :name="'gender_' + index"
                  />
                  <label class="form-check-label" :for="'male_' + index"
                    >Male</label
                  >
                </div>
                <div class="form-check form-check-inline mr-1 mb-3">
                  <input
                    v-model="drivers[index].gender"
                    class="form-check-input"
                    :id="'female_' + index"
                    type="radio"
                    value="F"
                    :name="'gender_' + index"
                  />
                  <label class="form-check-label" :for="'female_' + index"
                    >Female</label
                  >
                </div>
              </CCol>
              <CCol md="6">
                <CInput
                  type="date"
                  required
                  :name="'date_of_birth' + index"
                  label="Date of Birth"
                  v-model="drivers[index].date_of_birth"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  type="date"
                  required
                  :name="'date_of_license' + index"
                  label="Date of License"
                  v-model="drivers[index].date_of_license"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  type="number"
                  required
                  :name="'no_of_accidents' + index"
                  label="Number of Accident ( Past 3 Years )"
                  v-model="drivers[index].no_of_accidents"
                />
              </CCol>
              <CCol md="6">
                <CInput
                  type="number"
                  prepend="$"
                  required
                  :name="'total_claim' + index"
                  label="Total Claim Amount ( Past 3 Years )"
                  v-model="drivers[index].total_claim"
                />
              </CCol>
            </CRow>
            <CRow>
              <CCol col="12" class="mb-3">
                <CButton @click="addDriver()" color="primary"
                  >Add Named Driver</CButton
                >
              </CCol>
            </CRow>

            <DDFileUpload
              name="driving_license"
              label="Driver License (Front & Back)"
              type="driving_license"
              url="/api/motors/upload"
              ref="driving_license"
            />

            <CTextarea
              name="remarks"
              label="Additional Remarks"
              :value.sync="remarks"
              rows="5"
            />
            <template v-if="type == 'preowned'">
              <DDFileUpload
                name="log"
                label="Log Card"
                type="log"
                url="/api/motors/upload"
                ref="log"
              />
            </template>
            <template v-if="point != ''">
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
              <CButton type="submit" v-if="!submitting" color="primary">
                Get Quote
              </CButton>
              <CButton v-else color="primary" disabled>
                <div class="spinner-grow text-light" role="status">
                  <span class="sr-only">Loading...</span>
                </div>
                Quoting
              </CButton>
              <CButton :disabled="submitting" @click="draft()" color="success"
                >Save Draft</CButton
              >
              <CButton
                :disabled="submitting"
                v-if="motor != null"
                @click="archive()"
                color="danger"
                >Archive</CButton
              >
              <CButton
                :disabled="submitting"
                @click="cancel()"
                color="secondary"
                >Cancel</CButton
              >
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
      // TODO :: Corporate PoliyHolder Particulars, Additional Benefits, TPFT, TPO
      makes: [],
      models: [],
      drivers: [],

      usage: "private",
      point: "",
      policyholder_driving: false,
      start_date: "",
      expiry_date: "",
      min_date: "",

      file_paths: [],
      log_files: [],
      assessment_files: [],
      motor_prices: [],
      driver_id: "",
      vehicle_id: "",
      proposer_id: "",

      registration_no: "",
      type: "new",
      model: "",
      make: "",
      engine_no: "",
      chassis_no: "",
      off_peak: false,
      modified: false,
      modification_remarks: "",
      manufacture_year: "",
      capacity: "",
      seating_capacity: "",
      body_type: "",

      salutation: "Mr",
      nric_type: "1",
      nric_uen: "",
      name: "",
      nationality: "SG",
      residential: "1",
      gender: "M",
      occupation: "",
      date_of_birth: "",
      address: "",
      postal_code: "",
      email: "",
      phone: "",

      main_nric_type: "1",
      main_nric_uen: "",
      main_name: "",
      main_nationality: "SG",
      main_residential: "1",
      main_gender: "M",
      main_occupation: "Indoor",
      main_date_of_birth: "",
      main_date_of_license: "",
      main_no_of_accidents: "0",
      main_total_claim: "0",
      main_ncd: "0",
      main_serious_offence: false,
      main_physical_disable: false,
      main_refused: false,
      main_terminated: false,

      remarks: "",

      loading: false,
      submitting: false,
      multiple: true,

      occupation_options: [
        { value: "1", label: "Acupuncturist" },
        { value: "2", label: "Admin" },
        { value: "3", label: "Air Crew / Pilot" },
        { value: "4", label: "Air Traffic Controller" },
        { value: "5", label: "Airline Officer" },
        { value: "6", label: "Architect" },
        { value: "7", label: "ArtHandicraft/Antique Dealer" },
        { value: "9", label: "Astronomer" },
        { value: "10", label: "Author" },
        { value: "11", label: "Baby Sitter" },
        { value: "12", label: "Baker" },
        { value: "13", label: "Barber" },
        { value: "14", label: "Barrister" },
        { value: "15", label: "Bartender" },
        { value: "16", label: "Beautician" },
        { value: "18", label: "Boilerman" },
        { value: "19", label: "Butcher" },
        { value: "20", label: "Car Dealer" },
        { value: "21", label: "Carpenter (Woodworking Machinery)" },
        { value: "22", label: "Cashier" },
        { value: "23", label: "Caterer" },
        { value: "24", label: "Chamber Maid/Caretaker" },
        { value: "25", label: "Chauffeur" },
        { value: "26", label: "Cheer Leader" },
        { value: "27", label: "Chef/Cook/Confectioner" },
        { value: "28", label: "Chemical Plant Worker" },
        { value: "29", label: "Chiropractor" },
        { value: "30", label: "Cleaner (Indoor)" },
        { value: "31", label: "Cleaner (Office)" },
        { value: "32", label: "Cleaner(Outdoor, Exclude Height Works)" },
        { value: "33", label: "Clerical" },
        { value: "34", label: "Coach" },
        { value: "35", label: "Construction Engineer" },
        { value: "36", label: "Construction Worker / Supervisor/Foreman" },
        { value: "37", label: "Counseller" },
        { value: "38", label: "Craftman" },
        { value: "39", label: "Crane Operator" },
        { value: "40", label: "Customer Service (Indoor)" },
        { value: "41", label: "Customer Service (Outdoor)" },
        { value: "42", label: "Dancer/Dance Instructor" },
        { value: "43", label: "Deliveryman" },
        { value: "44", label: "Dentist" },
        { value: "45", label: "Despatch Rider" },
        { value: "46", label: "Dietician" },
        { value: "48", label: "Diplomat" },
        { value: "49", label: "Disc Jockey" },
        { value: "50", label: "Diver" },
        { value: "51", label: "Domestic Helper" },
        { value: "52", label: "Domestic Maid" },
        { value: "53", label: "Doormen" },
        { value: "54", label: "Draughtman" },
        { value: "55", label: "Driver Cum Delivery Man" },
        { value: "56", label: "Driver/Despatch Rider" },
        { value: "57", label: "Driving Instructor" },
        { value: "58", label: "Economist" },
        { value: "59", label: "EditorCopy Writer" },
        { value: "60", label: "Engineer (DeskBound)" },
        { value: "61", label: "Engineer (Involves Use Of Tool)" },
        { value: "62", label: "Engineer (Outdoor)" },
        { value: "63", label: "Entertainer" },
        { value: "64", label: "Executive(Indoor)" },
        { value: "65", label: "Executive(Outdoor)" },
        { value: "66", label: "Factory Operator" },
        { value: "67", label: "Farmer" },
        { value: "68", label: "Fashion Designer" },
        { value: "69", label: "Finance &amp; Accounts" },
        { value: "70", label: "Financial Advisor" },
        { value: "71", label: "Fisherman" },
        { value: "72", label: "Fishmonger" },
        { value: "73", label: "Fitness Trainer" },
        { value: "74", label: "Fitter" },
        { value: "75", label: "Florist" },
        { value: "77", label: "Forklift Driver" },
        { value: "79", label: "Gardener" },
        { value: "80", label: "Geologist" },
        { value: "81", label: "Glazier" },
        { value: "82", label: "Godown Keeper" },
        { value: "83", label: "Grocer" },
        { value: "85", label: "Hairstylist" },
        { value: "86", label: "Hawker Inspector" },
        { value: "87", label: "Hawker/Stallholder" },
        { value: "88", label: "Health Inspector" },
        { value: "89", label: "Homemaker/Housewife" },
        { value: "90", label: "Housekeeper" },
        { value: "91", label: "Interior Designer" },
        { value: "92", label: "Janitor" },
        { value: "93", label: "Jeweller" },
        { value: "94", label: "Jockeys" },
        { value: "95", label: "Journalist Indoor" },
        { value: "96", label: "Judge" },
        { value: "97", label: "Kitchen Assistant" },
        { value: "98", label: "Laboratory Technician" },
        { value: "99", label: "Lawyer" },
        { value: "100", label: "Lecturer" },
        { value: "101", label: "Librarian" },
        { value: "102", label: "Life Guard" },
        { value: "103", label: "Lift Attendant" },
        { value: "105", label: "Locksmith" },
        { value: "106", label: "Logistic Assistant" },
        { value: "107", label: "Machinist" },
        { value: "108", label: "Machinist (Woodworking)" },
        { value: "109", label: "Magician" },
        {
          value: "110",
          label: "Maintenance Inspector / Supervisor(Max Height Works @ 3M)",
        },
        {
          value: "112",
          label: "Maintenance Technician  (Max Height Works @ 3M)",
        },
        { value: "113", label: "Management/Administration" },
        { value: "114", label: "Marine Salvage Crew" },
        { value: "115", label: "Martial Arts Instructor" },
        { value: "116", label: "Masseur/ Masseuse" },
        { value: "117", label: "Mechanic" },
        { value: "118", label: "Medical Practitioner" },
        { value: "119", label: "Merchant" },
        { value: "120", label: "Meteorologist" },
        { value: "121", label: "Meter Reader" },
        { value: "122", label: "Midwife" },
        { value: "123", label: "Minister Of Singapore Government" },
        { value: "124", label: "Model" },
        { value: "125", label: "Money Changer" },
        { value: "127", label: "Motor Engineer" },
        { value: "128", label: "Musician" },
        { value: "129", label: "National Servicemen" },
        { value: "131", label: "News Vendor" },
        { value: "132", label: "Newscaster (Indoor)" },
        { value: "133", label: "Newscaster (Outdoor)" },
        { value: "135", label: "Novelist" },
        { value: "137", label: "Nurse" },
        { value: "138", label: "Nurse (Outpatient Clinic)" },
        { value: "139", label: "Odd Job Labourer" },
        { value: "140", label: "Oil Rig Worker" },
        { value: "142", label: "Onboard Vessel (Admin/Service Crew)" },
        { value: "141", label: "Onboard Vessel Work (Manual Work)" },
        { value: "143", label: "Operations Executive / Manager" },
        { value: "144", label: "Operator/Production" },
        { value: "145", label: "Optician" },
        { value: "256", label: "Others" },
        { value: "146", label: "Outward Bound Trainer" },
        { value: "147", label: "Packer" },
        { value: "148", label: "Painter_Indoor (Max 3 Meter Above Ground)" },
        { value: "272", label: "Painter_Outdoor (Max 10 Meter Above Ground)" },
        { value: "149", label: "Paramedics" },
        { value: "150", label: "Parking Officer" },
        { value: "151", label: "Pawnbroker" },
        { value: "152", label: "Person Involved In Handling Of Explosives" },
        { value: "153", label: "Petrol Pump Attendant" },
        { value: "154", label: "Pharmacist" },
        { value: "155", label: "Photographer(Indoor)" },
        { value: "156", label: "Photographer(Outdoor)" },
        { value: "157", label: "Piano Tuner" },
        { value: "158", label: "Planter" },
        { value: "159", label: "Plumber" },
        { value: "161", label: "Porter" },
        { value: "162", label: "Postman" },
        { value: "164", label: "Prison Officer / Warden" },
        { value: "165", label: "Private Investigator" },
        { value: "166", label: "Professional Sportsperson" },
        { value: "167", label: "Publisher/Printer" },
        { value: "168", label: "Purchaser (Indoor)" },
        { value: "169", label: "Purchaser (Outdoor)" },
        { value: "170", label: "QC Inspector" },
        { value: "171", label: "Radio/Television Engineer" },
        { value: "172", label: "Radiologist" },
        { value: "173", label: "Real Estate Agent" },
        { value: "174", label: "Receptionist" },
        { value: "175", label: "Referee" },
        { value: "176", label: "Remisier" },
        { value: "177", label: "Renovations Contractor" },
        { value: "178", label: "Retiree" },
        { value: "180", label: "Safety Inspector" },
        { value: "181", label: "Sailor" },
        { value: "182", label: "School Teacher/Principal" },
        { value: "183", label: "Secretary" },
        { value: "184", label: "Security Personnel_Armed" },
        { value: "185", label: "Security Personnel_Unarmed" },
        { value: "218", label: "Self-Employed (Indoor)" },
        { value: "219", label: "Self-Employed (Outdoor)" },
        { value: "220", label: "Service Engineer" },
        { value: "221", label: "Ship Crew" },
        { value: "222", label: "Shipmaster" },
        { value: "223", label: "Signwriter" },
        { value: "179", label: "Singapore Armed Forces Personnel" },
        { value: "224", label: "Singapore Civil Defense Force" },
        { value: "226", label: "Singapore Police Force" },
        { value: "227", label: "Site Coordinator" },
        { value: "228", label: "Social Escort" },
        { value: "229", label: "Software Engineer" },
        { value: "230", label: "Sole Proprietor" },
        { value: "231", label: "Sole Proprietor (Outdoor)" },
        { value: "232", label: "Sole Proprietor(Indoor)" },
        { value: "234", label: "Statistician" },
        { value: "235", label: "Steerman" },
        { value: "236", label: "Stevedore /  Dockworkers" },
        { value: "238", label: "Stock Broker" },
        { value: "239", label: "Student &amp; Child In Singapore" },
        { value: "240", label: "Student_Full Time" },
        { value: "241", label: "Student_Outside Of Singapore" },
        { value: "242", label: "Student_Part Time" },
        { value: "243", label: "Tailor/Seamstress" },
        { value: "244", label: "Taxi Driver" },
        { value: "245", label: "Therapist" },
        { value: "246", label: "Tiler" },
        { value: "248", label: "Tour Consultant / Guide" },
        { value: "249", label: "Trader" },
        { value: "250", label: "Traffic Warden" },
        { value: "251", label: "Translator" },
        { value: "252", label: "Tutor" },
        { value: "253", label: "Typist" },
        { value: "254", label: "Undertaker" },
        { value: "255", label: "Unemployed" },
        { value: "257", label: "Usher" },
        { value: "258", label: "Vegetable Seller" },
        { value: "260", label: "Veterinary Surgeon" },
        { value: "261", label: "Waiter/Waitress" },
        { value: "263", label: "Watchman" },
        { value: "264", label: "Web Designer" },
        { value: "265", label: "Welder" },
        { value: "266", label: "Window Cleaner" },
        { value: "268", label: "Working Onboard Non-Sailing Vessel" },
        { value: "270", label: "Zookeeper" },
        { value: "271", label: "Zoologist" },
      ],
      residential_options: [
        { label: "Singapore", value: "1" },
        { label: "PR", value: "2" },
        { label: "Foreigner", value: "3" },
      ],
      nationality_options: [
        { value: "SG", label: "Singapore" },
        { value: "AF", label: "Afghanistan" },
        { value: "AL", label: "Albania" },
        { value: "DZ", label: "Algeria" },
        { value: "AS", label: "American Samoa" },
        { value: "AD", label: "Andorra" },
        { value: "AO", label: "Angola" },
        { value: "AI", label: "Anguilla" },
        { value: "AQ", label: "Antarctica" },
        { value: "AG", label: "Antigua and Barbuda" },
        { value: "AR", label: "Argentina" },
        { value: "AM", label: "Armenia" },
        { value: "AW", label: "Aruba" },
        { value: "AU", label: "Australia" },
        { value: "AT", label: "Austria" },
        { value: "AZ", label: "Azerbaijan" },
        { value: "BS", label: "Bahamas" },
        { value: "BH", label: "Bahrain" },
        { value: "BD", label: "Bangladesh" },
        { value: "BB", label: "Barbados" },
        { value: "BY", label: "Belarus" },
        { value: "BE", label: "Belgium" },
        { value: "BZ", label: "Belize" },
        { value: "BJ", label: "Benin" },
        { value: "BM", label: "Bermuda" },
        { value: "BT", label: "Bhutan" },
        { value: "BO", label: "Bolivia" },
        { value: "BA", label: "Bosnia and Herzegovina" },
        { value: "BW", label: "Botswana" },
        { value: "BR", label: "Brazil" },
        { value: "IO", label: "British Indian Ocean Territory" },
        { value: "VG", label: "British Virgin Islands" },
        { value: "BN", label: "Brunei" },
        { value: "BG", label: "Bulgaria" },
        { value: "BF", label: "Burkina Faso" },
        { value: "BI", label: "Burundi" },
        { value: "KT", label: "COTEDIVOIRE" },
        { value: "KH", label: "Cambodia" },
        { value: "CM", label: "Cameroon" },
        { value: "CA", label: "Canada" },
        { value: "CV", label: "Cape Verde" },
        { value: "KY", label: "Cayman Islands" },
        { value: "CF", label: "Central African Republic" },
        { value: "TD", label: "Chad" },
        { value: "CL", label: "Chile" },
        { value: "CN", label: "China" },
        { value: "CX", label: "Christmas Island" },
        { value: "CC", label: "Cocos Islands" },
        { value: "CO", label: "Colombia" },
        { value: "KM", label: "Comoros" },
        { value: "CK", label: "Cook Islands" },
        { value: "CR", label: "Costa Rica" },
        { value: "HR", label: "Croatia" },
        { value: "CU", label: "Cuba" },
        { value: "CW", label: "Curacao" },
        { value: "CY", label: "Cyprus" },
        { value: "CZ", label: "Czech Republic" },
        { value: "DD", label: "DDGK" },
        { value: "CD", label: "Democratic Republic of the Congo" },
        { value: "DK", label: "Denmark" },
        { value: "DJ", label: "Djibouti" },
        { value: "DM", label: "Dominica" },
        { value: "DO", label: "Dominican Republic" },
        { value: "TL", label: "East Timor" },
        { value: "EC", label: "Ecuador" },
        { value: "EG", label: "Egypt" },
        { value: "SV", label: "El Salvador" },
        { value: "GQ", label: "Equatorial Guinea" },
        { value: "ER", label: "Eritrea" },
        { value: "EE", label: "Estonia" },
        { value: "ET", label: "Ethiopia" },
        { value: "FK", label: "Falkland Islands" },
        { value: "FO", label: "Faroe Islands" },
        { value: "FJ", label: "Fiji" },
        { value: "FI", label: "Finland" },
        { value: "FR", label: "France" },
        { value: "PF", label: "French Polynesia" },
        { value: "GA", label: "Gabon" },
        { value: "GM", label: "Gambia" },
        { value: "GE", label: "Georgia" },
        { value: "DE", label: "Germany" },
        { value: "GH", label: "Ghana" },
        { value: "GI", label: "Gibraltar" },
        { value: "GR", label: "Greece" },
        { value: "GL", label: "Greenland" },
        { value: "GD", label: "Grenada" },
        { value: "GU", label: "Guam" },
        { value: "GT", label: "Guatemala" },
        { value: "GG", label: "Guernsey" },
        { value: "GN", label: "Guinea" },
        { value: "GW", label: "Guinea-Bissau" },
        { value: "GY", label: "Guyana" },
        { value: "HT", label: "Haiti" },
        { value: "HN", label: "Honduras" },
        { value: "HK", label: "Hong Kong" },
        { value: "HU", label: "Hungary" },
        { value: "IS", label: "Iceland" },
        { value: "IN", label: "India" },
        { value: "ID", label: "Indonesia" },
        { value: "IR", label: "Iran" },
        { value: "IQ", label: "Iraq" },
        { value: "IE", label: "Ireland" },
        { value: "IM", label: "Isle of Man" },
        { value: "IL", label: "Israel" },
        { value: "IT", label: "Italy" },
        { value: "CI", label: "Ivory Coast" },
        { value: "JM", label: "Jamaica" },
        { value: "JP", label: "Japan" },
        { value: "JE", label: "Jersey" },
        { value: "JO", label: "Jordan" },
        { value: "KZ", label: "Kazakhstan" },
        { value: "KE", label: "Kenya" },
        { value: "KI", label: "Kiribati" },
        { value: "XK", label: "Kosovo" },
        { value: "KW", label: "Kuwait" },
        { value: "KG", label: "Kyrgyzstan" },
        { value: "LA", label: "Laos" },
        { value: "LV", label: "Latvia" },
        { value: "LB", label: "Lebanon" },
        { value: "LS", label: "Lesotho" },
        { value: "LR", label: "Liberia" },
        { value: "LY", label: "Libya" },
        { value: "LI", label: "Liechtenstein" },
        { value: "LT", label: "Lithuania" },
        { value: "LU", label: "Luxembourg" },
        { value: "MO", label: "Macao" },
        { value: "MK", label: "Macedonia" },
        { value: "MG", label: "Madagascar" },
        { value: "MW", label: "Malawi" },
        { value: "MY", label: "Malaysia" },
        { value: "MV", label: "Maldives" },
        { value: "ML", label: "Mali" },
        { value: "MT", label: "Malta" },
        { value: "MH", label: "Marshall Islands" },
        { value: "MR", label: "Mauritania" },
        { value: "MU", label: "Mauritius" },
        { value: "YT", label: "Mayotte" },
        { value: "MX", label: "Mexico" },
        { value: "FM", label: "Micronesia" },
        { value: "MD", label: "Moldova" },
        { value: "MC", label: "Monaco" },
        { value: "MN", label: "Mongolia" },
        { value: "ME", label: "Montenegro" },
        { value: "MS", label: "Montserrat" },
        { value: "MA", label: "Morocco" },
        { value: "MZ", label: "Mozambique" },
        { value: "MM", label: "Myanmar" },
        { value: "NA", label: "Namibia" },
        { value: "NR", label: "Nauru" },
        { value: "NP", label: "Nepal" },
        { value: "NL", label: "Netherlands" },
        { value: "AN", label: "Netherlands Antilles" },
        { value: "NC", label: "New Caledonia" },
        { value: "NZ", label: "New Zealand" },
        { value: "NI", label: "Nicaragua" },
        { value: "NE", label: "Niger" },
        { value: "NG", label: "Nigeria" },
        { value: "NU", label: "Niue" },
        { value: "KP", label: "North Korea" },
        { value: "MP", label: "Northern Mariana Islands" },
        { value: "NO", label: "Norway" },
        { value: "ZZ", label: "OTHERS" },
        { value: "OM", label: "Oman" },
        { value: "PK", label: "Pakistan" },
        { value: "PW", label: "Palau" },
        { value: "PS", label: "Palestine" },
        { value: "PA", label: "Panama" },
        { value: "PG", label: "Papua New Guinea" },
        { value: "PY", label: "Paraguay" },
        { value: "PE", label: "Peru" },
        { value: "PH", label: "Philippines" },
        { value: "PN", label: "Pitcairn" },
        { value: "PL", label: "Poland" },
        { value: "PT", label: "Portugal" },
        { value: "PR", label: "Puerto Rico" },
        { value: "QA", label: "Qatar" },
        { value: "CG", label: "Republic of the Congo" },
        { value: "RE", label: "Reunion" },
        { value: "RO", label: "Romania" },
        { value: "RU", label: "Russia" },
        { value: "RW", label: "Rwanda" },
        { value: "BL", label: "Saint Barthelemy" },
        { value: "SH", label: "Saint Helena" },
        { value: "KN", label: "Saint Kitts and Nevis" },
        { value: "LC", label: "Saint Lucia" },
        { value: "MF", label: "Saint Martin" },
        { value: "PM", label: "Saint Pierre and Miquelon" },
        { value: "VC", label: "Saint Vincent and the Grenadines" },
        { value: "WS", label: "Samoa" },
        { value: "SM", label: "San Marino" },
        { value: "ST", label: "Sao Tome and Principe" },
        { value: "SA", label: "Saudi Arabia" },
        { value: "SN", label: "Senegal" },
        { value: "RS", label: "Serbia" },
        { value: "SC", label: "Seychelles" },
        { value: "SL", label: "Sierra Leone" },
        { value: "SX", label: "Sint Maarten" },
        { value: "SK", label: "Slovakia" },
        { value: "SI", label: "Slovenia" },
        { value: "SB", label: "Solomon Islands" },
        { value: "SO", label: "Somalia" },
        { value: "ZA", label: "South Africa" },
        { value: "KR", label: "South Korea" },
        { value: "SS", label: "South Sudan" },
        { value: "ES", label: "Spain" },
        { value: "LK", label: "Sri Lanka" },
        { value: "SD", label: "Sudan" },
        { value: "SR", label: "Suriname" },
        { value: "SJ", label: "Svalbard and Jan Mayen" },
        { value: "SZ", label: "Swaziland" },
        { value: "SE", label: "Sweden" },
        { value: "CH", label: "Switzerland" },
        { value: "SY", label: "Syria" },
        { value: "TW", label: "Taiwan" },
        { value: "TJ", label: "Tajikistan" },
        { value: "TZ", label: "Tanzania" },
        { value: "TH", label: "Thailand" },
        { value: "TG", label: "Togo" },
        { value: "TK", label: "Tokelau" },
        { value: "TO", label: "Tonga" },
        { value: "TT", label: "Trinidad and Tobago" },
        { value: "TN", label: "Tunisia" },
        { value: "TR", label: "Turkey" },
        { value: "TM", label: "Turkmenistan" },
        { value: "TC", label: "Turks and Caicos Islands" },
        { value: "TV", label: "Tuvalu" },
        { value: "VI", label: "U.S. Virgin Islands" },
        { value: "UG", label: "Uganda" },
        { value: "UA", label: "Ukraine" },
        { value: "AE", label: "United Arab Emirates" },
        { value: "GB", label: "United Kingdom" },
        { value: "US", label: "United States" },
        { value: "UY", label: "Uruguay" },
        { value: "UZ", label: "Uzbekistan" },
        { value: "VU", label: "Vanuatu" },
        { value: "VA", label: "Vatican" },
        { value: "VE", label: "Venezuela" },
        { value: "VN", label: "Vietnam" },
        { value: "WF", label: "Wallis and Futuna" },
        { value: "EH", label: "Western Sahara" },
        { value: "YU", label: "YUGOSLAVIA" },
        { value: "YE", label: "Yemen" },
        { value: "ZM", label: "Zambia" },
        { value: "ZW", label: "Zimbabwe" },
      ],
      nric_type_options: [
        { label: "NRIC", value: "1" },
        { label: "FIN", value: "2" },
        { label: "Passport", value: "3" },
        { label: "Birth Certificate", value: "4" },
        { label: "Others", value: "5" },
      ],
      ncd_options: [
        { label: "0%", value: "0" },
        { label: "10%", value: "10" },
        { label: "20%", value: "20" },
        { label: "30%", value: "30" },
        { label: "40%", value: "40" },
        { label: "50%", value: "50" },
      ],
      salutation_options: [
        { label: "Mr", value: "Mr" },
        { label: "Ms", value: "Ms" },
        { label: "Mrs", value: "Mrs" },
        { label: "Mdm", value: "Mdm" },
      ],
    };
  },
  mounted() {
    if (this.$route.name != "Create Motor Order" && this.motor != null) {
      setTimeout(() => {
        for (var i = 0; i < this.motor.documents.length; i++) {
          if (this.motor.documents[i].type != "signature") {
            this.$refs[this.motor.documents[i].type].$data.files.push(
              this.motor.documents[i]
            );
          }
        }
      }, 500);

      this.registration_no = this.motor.vehicle.registration_no;
      this.chassis_no = this.motor.vehicle.chassis_no;
      this.make = this.motor.vehicle.make;
      this.model = this.motor.vehicle.model;
      this.type = this.motor.vehicle.type;
      this.engine_no = this.motor.vehicle.engine_no;
      this.off_peak = this.motor.vehicle.off_peak;
      this.modified = this.motor.vehicle.modified;
      this.modification_remarks = this.motor.vehicle.modification_remarks;
      this.manufacture_year = this.motor.vehicle.manufacture_year;
      this.capacity = this.motor.vehicle.capacity;
      this.seating_capacity = this.motor.vehicle.seating_capacity;
      this.body_type = this.motor.vehicle.body_type;

      this.salutation = this.motor.proposer.salutation;
      this.nric_type = this.motor.proposer.nric_type;
      this.nric_uen = this.motor.proposer.nric_uen;
      this.name = this.motor.proposer.name;
      this.nationality = this.motor.proposer.nationality;
      this.residential = this.motor.proposer.residential;
      this.gender = this.motor.proposer.gender;
      this.occupation = this.motor.proposer.occupation;
      this.date_of_birth = this.motor.proposer.date_of_birth;
      this.address = this.motor.proposer.address;
      this.postal_code = this.motor.proposer.postal_code;
      this.email = this.motor.proposer.email;
      this.phone =
        this.motor.proposer.phone != null
          ? this.motor.proposer.phone
              .split("+65")
              .join("")
              .split("+65 ")
              .join("")
          : this.motor.proposer.phone;

      this.main_nric_type = this.motor.driver.nric_type;
      this.main_nric_uen = this.motor.motor.driver.nric_uen;
      this.main_name = this.motor.driver.name;
      this.main_nationality = this.motor.driver.nationality;
      this.main_residential = this.motor.driver.residential;
      this.main_gender = this.motor.driver.gender;
      this.main_occupation = this.motor.driver.occupation;
      this.main_date_of_birth = this.motor.driver.date_of_birth;
      this.main_date_of_license = this.motor.driver.date_of_license;
      this.main_no_of_accidents = this.motor.driver.no_of_accidents;
      this.main_total_claim = this.motor.driver.total_claim;
      this.main_ncd = this.motor.driver.ncd;
      this.main_serious_offence = this.motor.driver.serious_offence;
      this.main_physical_disable = this.motor.driver.physical_disable;
      this.main_refused = this.motor.driver.refused;
      this.main_terminated = this.motor.driver.terminated;
      this.drivers = this.motor.drivers;

      this.driver_id = this.motor.driver_id;
      this.vehicle_id = this.motor.vehicle_id;
      this.proposer_id = this.motor.proposer_id;

      this.remarks = this.motor.remarks;

      this.status = this.motor.status;
    } else {
      this.$store.commit("SET", ["motor", null]);
    }

    let today = new Date();
    this.min_date = this.formatDate(today);

    this.getMakes();
  },
  watch: {
    make: function (val) {
      this.getModels(val);
      this.getMotorCar("make", val);
    },
    model: function (val) {
      this.getMotorCar("model", val);
    },
    nric_type: function (val) {
      if (this.policyholder_driving) {
        this.main_nric_type = val;
      }
    },
    nric_uen: function (val) {
      if (this.policyholder_driving) {
        this.main_nric_uen = val;
      }
    },
    nric_name: function (val) {
      if (this.policyholder_driving) {
        this.main_nric_name = val;
      }
    },
    nationality: function (val) {
      if (this.policyholder_driving) {
        this.main_nationality = val;
      }
    },
    residential: function (val) {
      if (this.policyholder_driving) {
        this.main_residential = val;
      }
    },
    gender: function (val) {
      if (this.policyholder_driving) {
        this.main_gender = val;
      }
    },
    date_of_birth: function (val) {
      if (this.policyholder_driving) {
        this.main_date_of_birth = val;
      }
    },
    date_of_license: function (val) {
      if (this.policyholder_driving) {
        this.main_date_of_license = val;
      }
    },
    policyholder_driving: function (val) {
      if (val) {
        this.main_nric_type = this.nric_type;
        this.main_nric_uen = this.nric_uen;
        this.main_name = this.name;
        this.main_nationality = this.nationality;
        this.main_residential = this.residential;
        this.main_gender = this.gender;
        this.main_date_of_birth = this.date_of_birth;
      }
    },
  },
  methods: {
    undo() {
      this.$refs.signaturePad.undoSignature();
    },
    formatDate(date) {
      var d = new Date(date),
        month = "" + (d.getMonth() + 1),
        day = "" + d.getDate(),
        year = d.getFullYear();

      if (month.length < 2) month = "0" + month;
      if (day.length < 2) day = "0" + day;

      return [year, month, day].join("-");
    },
    removeDriver(index) {
      this.drivers.splice(index, 1);
    },
    addDriver() {
      this.drivers.push({
        name: "",
        nric_type: "1",
        nric_uen: "",
        nationality: "SG",
        residential: "1",
        gender: "M",
        date_of_birth: "",
        date_of_license: "",
        no_of_accidents: "0",
        total_claim: "0",
      });
    },
    getMakes() {
      var inputs = {};
      inputs.url = "/api/motor/makes";
      this.$store.dispatch("API", inputs).then((response) => {
        this.makes = response.makes;
      });
    },
    getModels(val) {
      if (val != "") {
        var inputs = {};
        inputs.url = "/api/motor/models";
        inputs.make = val;
        inputs.method = "POST";
        this.loading = true;
        this.$store
          .dispatch("API", inputs)
          .then((response) => {
            this.models = response.models;
          })
          .finally(() => {
            this.loading = false;
          });
      }
    },
    getMotorCar(param, val) {
      this.capacity = "";
      this.point = "";
      var inputs = {};
      inputs.make = this.make;
      inputs.model = this.model;
      inputs[param] = val;
      inputs.url = "/api/motors/searchCar";
      inputs.method = "POST";
      if (inputs.make != "" && inputs.model != "") {
        this.$store.dispatch("API", inputs).then((response) => {
          this.capacity = response.capacity;
          this.point = response.point;
          this.body_type = response.body_type;
        });
      }
    },
    submit() {
      this.submitting = true;
      var inputs = this.formatInput();
      if (this.motor != null) {
        inputs.id = this.motor.id;
      }
      if (this.point != "") {
        const { isEmpty, data } = this.$refs.signaturePad.saveSignature();
        inputs.signature = data;
      }
      inputs.url = "/api/motors/create";
      this.$store
        .dispatch("API", inputs)
        .then((res) => {
          this.$router.push("/motors/details/" + res.id);
        })
        .finally(() => {
          this.submitting = false;
        });
    },
    archive() {
      if (this.motor != null) {
        var inputs = {};
        inputs.method = "post";
        inputs.id = this.motor.id;
        inputs.url = "/api/motors/archive";
        this.$store.dispatch("API", inputs).then(() => {
          this.$router.push("/motors/archives");
        });
      }
    },
    draft() {
      var inputs = this.formatInput();
      if (this.motor != null) {
        inputs.id = this.motor.id;
      }
      inputs.url = "/api/motors/draft";
      this.$store.dispatch("API", inputs).then(() => {
        this.$router.push("/motors/drafts");
      });
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
      inputs.method = "post";

      inputs.usage = this.usage;
      inputs.point = this.point;
      inputs.policyholder_driving = this.policyholder_driving;
      inputs.start_date = this.start_date;
      inputs.expiry_date = this.expiry_date;

      inputs.registration_no = this.registration_no;
      inputs.type = this.type;
      inputs.model = this.model;
      inputs.make = this.make;
      inputs.engine_no = this.engine_no;
      inputs.chassis_no = this.chassis_no;
      inputs.off_peak = this.off_peak;
      inputs.modified = this.modified;
      inputs.modification_remarks = this.modification_remarks;
      inputs.manufacture_year = this.manufacture_year;
      inputs.capacity = this.capacity;
      inputs.seating_capacity = this.seating_capacity;
      inputs.body_type = this.body_type;

      inputs.salutation = this.salutation;
      inputs.nric_type = this.nric_type;
      inputs.nric_uen = this.nric_uen;
      inputs.name = this.name;
      inputs.nationality = this.nationality;
      inputs.residential = this.residential;
      inputs.gender = this.gender;
      inputs.occupation = this.occupation;
      inputs.date_of_birth = this.date_of_birth;
      inputs.date_of_license = this.date_of_license;
      inputs.address = this.address;
      inputs.postal_code = this.postal_code;
      inputs.email = this.email;
      inputs.phone = "+65 " + this.phone;

      inputs.main_nric_type = this.main_nric_type;
      inputs.main_nric_uen = this.main_nric_uen;
      inputs.main_name = this.main_name;
      inputs.main_nationality = this.main_nationality;
      inputs.main_residential = this.main_residential;
      inputs.main_gender = this.main_gender;
      inputs.main_occupation = this.main_occupation;
      inputs.main_date_of_birth = this.main_date_of_birth;
      inputs.main_date_of_license = this.main_date_of_license;
      inputs.main_no_of_accidents = this.main_no_of_accidents;
      inputs.main_total_claim = this.main_total_claim;
      inputs.main_ncd = this.main_ncd;
      inputs.main_serious_offence = this.main_serious_offence;
      inputs.main_physical_disable = this.main_physical_disable;
      inputs.main_refused = this.main_refused;
      inputs.main_terminated = this.main_terminated;

      inputs.remarks = this.remarks;

      inputs.vehicle_id = this.vehicle_id;
      inputs.proposer_id = this.proposer_id;
      inputs.drivers = this.drivers;

      inputs.documents = [];

      this.addFileToDocuments("log", inputs);
      this.addFileToDocuments("driving_license", inputs);

      return inputs;
    },
    cancel() {
      this.$router.go(-1);
    },
  },
  computed: {
    ...mapGetters(["motor"]),
  },
};
</script>
