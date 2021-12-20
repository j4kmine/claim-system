<template>
  <div>
    <CRow>
      <CCol md="8">
        <ul class="nav nav-pills" id="pills-tab" role="tablist">
          <li class="nav-item" v-if="user.category != 'dealer'">
            <a
              class="nav-link"
              :class="
                parentTab == 'claims' || parentTab == null ? 'active' : ''
              "
              id="pills-claim-tab"
              data-toggle="pill"
              @click="setParentTab('claims')"
              href="#pills-claim"
              role="tab"
              aria-controls="pills-claim"
              :aria-selected="user.category != 'dealer' ? 'true' : 'false'"
            >
              Claims
              <CBadge
                class="dashboard-badge"
                :color="action == 0 ? 'success' : 'danger'"
              >
                <span>{{ action }}</span>
              </CBadge>
            </a>
          </li>
          <li
            class="nav-item"
            id="warranties-dashboard-tab"
            v-if="user.category != 'surveyor' && user.category != 'workshop'"
          >
            <a
              class="nav-link"
              :class="parentTab == 'warranty' ? 'active' : ''"
              id="pills-warranty-tab"
              data-toggle="pill"
              @click="setParentTab('warranty')"
              href="#pills-warranty"
              role="tab"
              aria-controls="pills-warranty"
              :aria-selected="user.category == 'dealer' ? 'true' : 'false'"
            >
              Warranties
              <CBadge
                class="dashboard-badge"
                :color="warrantyAction == 0 ? 'success' : 'danger'"
              >
                <span>{{ warrantyAction }}</span>
              </CBadge>
            </a>
          </li>
          <li
            class="nav-item"
            id="motors-dashboard-tab"
            v-if="user.category != 'surveyor' && user.category != 'workshop'"
          >
            <a
              class="nav-link"
              :class="parentTab == 'motor' ? 'active' : ''"
              id="pills-motor-tab"
              data-toggle="pill"
              @click="setParentTab('motor')"
              href="#pills-motor"
              role="tab"
              aria-controls="pills-motor"
              aria-selected="false"
            >
              Motors
              <CBadge
                class="dashboard-badge"
                :color="motorAction == 0 ? 'success' : 'danger'"
              >
                <span>{{ motorAction }}</span>
              </CBadge>
            </a>
          </li>
          <li
            class="nav-item"
            id="accidentReportings-dashboard-tab"
            v-show="user.category == 'all_cars' || user.category == 'workshop'"
          >
            <a
              class="nav-link"
              :class="parentTab == 'accidentReporting' ? 'active' : ''"
              id="pills-accidentReporting-tab"
              data-toggle="pill"
              @click="setParentTab('accidentReporting')"
              href="#pills-accidentReporting"
              role="tab"
              aria-controls="pills-accidentReporting"
              aria-selected="false"
            >
              Accident Reporting
              <CBadge
                class="dashboard-badge"
                :color="accidentReportingAction == 0 ? 'success' : 'danger'"
              >
                <span>{{ accidentReportingAction }}</span>
              </CBadge>
            </a>
          </li>
          <li
            class="nav-item"
            id="servicings-dashboard-tab"
            v-show="user.category == 'all_cars' || user.category == 'workshop'"
          >
            <a
              class="nav-link"
              :class="parentTab == 'servicing' ? 'active' : ''"
              id="pills-servicing-tab"
              data-toggle="pill"
              @click="setParentTab('servicing')"
              href="#pills-servicing"
              role="tab"
              aria-controls="pills-servicing"
              aria-selected="false"
            >
              Servicing
              <CBadge
                class="dashboard-badge"
                :color="servicingAction == 0 ? 'success' : 'danger'"
              >
                <span>{{ servicingAction }}</span>
              </CBadge>
            </a>
          </li>
        </ul>
      </CCol>
      <CCol md="4">
        <div style="display: flex; flex-wrap: wrap; align-items: center">
          <CInput
            label="From"
            type="date"
            name="from_date"
            style="margin-right: 10px"
            :value.sync="fromDate"
          />
          <CInput label="To" type="date" name="to_date" :value.sync="toDate" />
          <div style="margin: 10px 0px 0px 10px">
            <CButton color="info" @click="search"> Search </CButton>
          </div>
        </div>
        <!-- <CSelect
                    :value.sync="duration"
                    :options="durationOptions"
                /> -->
      </CCol>
    </CRow>
    <hr class="mt-0" />
    <div class="tab-content pt-0" id="pills-tabContent">
      <div
        class="tab-pane fade"
        :class="
          user.category != 'dealer' &&
          (parentTab == 'claims' || parentTab == null)
            ? 'show active'
            : ''
        "
        id="pills-claim"
        role="tabpanel"
        aria-labelledby="pills-claim-tab"
      >
        <CNav variant="tabs" class="dashboard-tab">
          <CNavItem
            v-if="user.category != 'insurer' && user.category != 'surveyor'"
            :active="tab == 'draft'"
          >
            <div @click="changeTab('draft')">
              Draft
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'workshop' && count.draft != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="count.draft != null">{{ count.draft }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem
            v-if="user.category != 'surveyor'"
            :active="tab == 'allCars_review'"
          >
            <div @click="changeTab('allCars_review')">
              AllCars Review
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'all_cars' && count.allCars_review != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="count.allCars_review != null">{{
                  count.allCars_review
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem
            v-if="user.category != 'surveyor'"
            :active="tab == 'insurer_review'"
          >
            <div @click="changeTab('insurer_review')">
              Insurer Review
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'insurer' && count.insurer_review != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="count.insurer_review != null">{{
                  count.insurer_review
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="tab == 'surveyor_review'">
            <div @click="changeTab('surveyor_review')">
              Surveyor Review
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'surveyor' && count.surveyor_review != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="count.surveyor_review != null">{{
                  count.surveyor_review
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem
            v-if="user.category != 'surveyor'"
            :active="tab == 'insurer_approval'"
          >
            <div @click="changeTab('insurer_approval')">
              Insurer Approval
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'insurer' && count.insurer_approval != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="count.insurer_approval != null">{{
                  count.insurer_approval
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem
            v-if="user.category != 'surveyor'"
            :active="tab == 'approved'"
          >
            <div @click="changeTab('approved')">
              Approved
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'workshop' &&
                  (count.approved != null || count.repair_in_progress != null)
                    ? 'danger'
                    : 'success'
                "
                ><span
                  v-if="
                    count.approved != null || count.repair_in_progress != null
                  "
                  >{{
                    parseInt(count.approved ? count.approved : 0) +
                    parseInt(
                      count.repair_in_progress ? count.repair_in_progress : 0
                    )
                  }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem
            v-if="user.category != 'surveyor'"
            :active="tab == 'doc_verification'"
          >
            <div @click="changeTab('doc_verification')">
              Doc Verification
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'all_cars' && count.doc_verification != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="count.doc_verification != null">{{
                  count.doc_verification
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem
            v-if="user.category != 'surveyor'"
            :active="tab == 'pending_payment'"
          >
            <div @click="changeTab('pending_payment')">
              Pending Payment
              <CBadge
                class="dashboard-badge"
                :color="
                  (user.category == 'all_cars' ||
                    user.category == 'workshop') &&
                  count.pending_payment != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="count.pending_payment != null">{{
                  count.pending_payment
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem
            v-if="user.category != 'surveyor'"
            :active="tab == 'completed'"
          >
            <div @click="changeTab('completed')">
              Completed
              <CBadge class="dashboard-badge" color="success"
                ><span v-if="count.completed != null">{{
                  count.completed
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
        </CNav>
        <data-table
          ref="claimDataTable"
          @row-clicked="displayRow"
          @on-table-props-changed="reloadTableClaims"
          :columns="columns"
          order-by="id"
          order-dir="desc"
          :data="dataClaims"
          :debounce-delay="debounceDelay"
          :classes="classes"
          :key="keyClaims"
          :headers="headers"
          :perPage="perPage"
        >
        </data-table>
      </div>
      <div
        class="tab-pane fade"
        :class="
          user.category == 'dealer' || parentTab == 'warranty'
            ? 'show active'
            : ''
        "
        id="pills-warranty"
        role="tabpanel"
        aria-labelledby="pills-warranty-tab"
      >
        <CNav variant="tabs" class="dashboard-tab">
          <CNavItem :active="warrantyTab == 'draft'">
            <div @click="changeWarrantyTab('draft')">
              Draft
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'dealer' && warrantyCount.draft != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="warrantyCount.draft != null">{{
                  warrantyCount.draft
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="warrantyTab == 'pending_enquiry'">
            <div @click="changeWarrantyTab('pending_enquiry')">
              Pending Enquiry
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'all_cars' &&
                  warrantyCount.pending_enquiry != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="warrantyCount.pending_enquiry != null">{{
                  warrantyCount.pending_enquiry
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="warrantyTab == 'pending_acceptance'">
            <div @click="changeWarrantyTab('pending_acceptance')">
              Pending Acceptance
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'dealer' &&
                  warrantyCount.pending_acceptance != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="warrantyCount.pending_acceptance != null">{{
                  warrantyCount.pending_acceptance
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="warrantyTab == 'pending_admin_review'">
            <div @click="changeWarrantyTab('pending_admin_review')">
              Pending Admin Review
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'all_cars' &&
                  warrantyCount.pending_admin_review != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="warrantyCount.pending_admin_review != null">{{
                  warrantyCount.pending_admin_review
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="warrantyTab == 'completed'">
            <div @click="changeWarrantyTab('completed')">
              Completed
              <CBadge class="dashboard-badge" color="success"
                ><span v-if="warrantyCount.completed != null">{{
                  warrantyCount.completed
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
        </CNav>
        <data-table
          ref="warrantyDataTable"
          @row-clicked="displayWarrantyRow"
          @on-table-props-changed="reloadTableWarranty"
          :columns="warrantyColumns"
          order-by="id"
          order-dir="desc"
          :data="dataWarranty"
          :debounce-delay="debounceDelay"
          :classes="classes"
          :headers="headers"
          :perPage="perPage"
        >
        </data-table>
      </div>
      <div
        class="tab-pane fade"
        :class="parentTab == 'motor' ? 'show active' : ''"
        id="pills-motor"
        role="tabpanel"
        aria-labelledby="pills-motor-tab"
      >
        <CNav variant="tabs" class="dashboard-tab">
          <CNavItem :active="motorTab == 'draft'">
            <div @click="changeMotorTab('draft')">
              Draft
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'dealer' && motorCount.draft != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="motorCount.draft != null">{{
                  motorCount.draft
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="motorTab == 'pending_enquiry'">
            <div @click="changeMotorTab('pending_enquiry')">
              Pending Enquiry
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'all_cars' &&
                  motorCount.pending_enquiry != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="motorCount.pending_enquiry != null">{{
                  motorCount.pending_enquiry
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="motorTab == 'pending_acceptance'">
            <div @click="changeMotorTab('pending_acceptance')">
              Pending Acceptance
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'dealer' &&
                  motorCount.pending_acceptance != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="motorCount.pending_acceptance != null">{{
                  motorCount.pending_acceptance
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="motorTab == 'pending_admin_review'">
            <div @click="changeMotorTab('pending_admin_review')">
              Pending Admin Review
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'all_cars' &&
                  motorCount.pending_admin_review != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="motorCount.pending_admin_review != null">{{
                  motorCount.pending_admin_review
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="motorTab == 'pending_log_card'">
            <div @click="changeMotorTab('pending_log_card')">
              Pending Log Card
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'dealer' &&
                  motorCount.pending_log_card != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="motorCount.pending_log_card != null">{{
                  motorCount.pending_log_card
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="motorTab == 'pending_ci'">
            <div @click="changeMotorTab('pending_ci')">
              Pending CI
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'all_cars' && motorCount.pending_ci != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="motorCount.pending_ci != null">{{
                  motorCount.pending_ci
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="motorTab == 'completed'">
            <div @click="changeMotorTab('completed')">
              Completed
              <CBadge class="dashboard-badge" color="success"
                ><span v-if="motorCount.completed != null">{{
                  motorCount.completed
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
        </CNav>
        <data-table
          ref="motorDataTable"
          @row-clicked="displayMotorRow"
          @on-table-props-changed="reloadTableMotor"
          :columns="warrantyColumns"
          order-by="id"
          order-dir="desc"
          :data="dataMotor"
          :debounce-delay="debounceDelay"
          :classes="classes"
          :headers="headers"
          :perPage="perPage"
        >
        </data-table>
      </div>
      <div
        class="tab-pane fade"
        :class="parentTab == 'accidentReporting' ? 'show active' : ''"
        id="pills-accidentReporting"
        role="tabpanel"
        aria-labelledby="pills-accidentReporting-tab"
      >
        <CNav variant="tabs" class="dashboard-tab">
          <CNavItem :active="accidentReportingTab == 'pending'">
            <div @click="changeAccidentReportingTab('pending')">
              Pending Inspection
              <CBadge
                class="dashboard-badge"
                :color="
                  user.category == 'all_cars' &&
                  accidentReportingCount.pending != null
                    ? 'danger'
                    : 'success'
                "
                ><span v-if="accidentReportingCount.pending != null">{{
                  accidentReportingCount.pending
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="accidentReportingTab == 'completed'">
            <div @click="changeAccidentReportingTab('completed')">
              Completed
              <CBadge class="dashboard-badge" color="success"
                ><span v-if="accidentReportingCount.completed != null">{{
                  accidentReportingCount.completed
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
        </CNav>
        <data-table
          ref="accidentDataTable"
          @row-clicked="displayAccidentReportingRow"
          @on-table-props-changed="reloadTableAccidentReporting"
          :columns="accidentReportingColumn"
          order-by="id"
          :debounce-delay="debounceDelay"
          order-dir="desc"
          :data="dataAccidentReporting"
          :classes="classes"
          :headers="headers"
          :perPage="perPage"
        >
        </data-table>
      </div>
      <div
        class="tab-pane fade"
        id="pills-servicing"
        :class="parentTab == 'servicing' ? 'show active' : ''"
        role="tabpanel"
        aria-labelledby="pills-servicing-tab"
      >
        <CNav variant="tabs" class="dashboard-tab">
          <CNavItem :active="servicingTab == 'upcoming'">
            <div @click="changeServicingTab('upcoming')">
              Upcoming<CBadge
                class="dashboard-badge"
                :color="servicingCount.upcoming != null ? 'danger' : 'success'"
                ><span v-if="servicingCount.upcoming != null">{{
                  servicingCount.upcoming
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="servicingTab == 'open'">
            <div @click="changeServicingTab('open')">
              Servicing In Progress
              <CBadge
                class="dashboard-badge"
                :color="servicingCount.open != null ? 'danger' : 'success'"
                ><span v-if="servicingCount.open != null">{{
                  servicingCount.open
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="servicingTab == 'pending'">
            <div @click="changeServicingTab('pending')">
              Pending Collection
              <CBadge
                class="dashboard-badge"
                :color="servicingCount.pending != null ? 'danger' : 'success'"
                ><span v-if="servicingCount.pending != null">{{
                  servicingCount.pending
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
          <CNavItem :active="servicingTab == 'completed'">
            <div @click="changeServicingTab('completed')">
              Completed
              <CBadge class="dashboard-badge" color="success"
                ><span v-if="servicingCount.completed != null">{{
                  servicingCount.completed
                }}</span
                ><span v-else>0</span></CBadge
              >
            </div>
          </CNavItem>
        </CNav>
        <data-table
          ref="servicingDataTable"
          @row-clicked="displayServicingRow"
          @on-table-props-changed="reloadTableServicing"
          :columns="servicingColumn"
          order-by="id"
          :debounce-delay="debounceDelay"
          order-dir="desc"
          :data="dataServicing"
          :classes="classes"
          :headers="headers"
          :perPage="perPage"
        >
        </data-table>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
export default {
  data() {
    const year = new Date().getFullYear();

    return {
      fromDate: `${year}-01-01`,
      toDate: `${year}-12-31`,
      keyClaims: 0,
      debounceDelay: 250,
      url: "",
      parentTab: "",
      warrantyUrl: "",
      motorUrl: "",
      tab: "",
      motorTab: "",
      accidentReportingTab: "",
      servicingTab: "",
      accidentReportingUrl: "",
      servicingUrl: "",
      warrantyTab: "",
      duration: "day",
      warrantyDuration: "day",
      motorDuration: "day",
      action: "0",
      warrantyAction: "0",
      motorAction: "0",
      accidentReportingAction: "0",
      servicingAction: "0",
      durationOptions: [
        { label: "Today", value: "day" },
        { label: "This Week", value: "week" },
        { label: "This Month", value: "month" },
        { label: "This Year", value: "year" },
        { label: "Last Year", value: "last_year" },
      ],
      count: [],
      warrantyCount: [],
      motorCount: [],
      accidentReportingCount: [],
      servicingCount: [],
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
          label: "Ref No.",
          name: "ref_no",
          orderable: true,
        },
        {
          label: "III Claim No.",
          name: "insurer_ref_no",
          orderable: true,
        },
        {
          label: "Car Plate",
          name: "vehicle.registration_no",
        },
        {
          label: "Workshop",
          name: "workshop.name",
        },
        {
          label: "Insurer",
          name: "insurer_extend.company.name",
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
          label: "Status",
          name: "status",
          orderable: true,
          transform: ({ data, name }) =>
            data[name] == "repair_in_progress" ? "approved" : data[name],
          component: "StatusCol",
        },
        {
          label: "Created At",
          name: "created_at",
          orderable: true,
        },
      ],
      accidentReportingColumn: [
        {
          label: "Ref No",
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
          label: "Customer",
          name: "customer.name",
          columnName: "customers.name",
          orderable: true,
        },
        {
          label: "Make",
          name: "vehicle.make",
          columnName: "vehicles.make",
          orderable: true,
        },
        {
          label: "Model",
          name: "vehicle.model",
          columnName: "vehicles.model",
          orderable: true,
        },
        {
          label: "Status",
          name: "status",
          orderable: true,
          component: "StatusCol",
        },
        {
          label: "Created At",
          name: "created_at",
          columnName: "created_at",
          orderable: true,
        },
      ],
      servicingColumn: [
        {
          label: "Ref No",
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
          label: "Customer",
          name: "customer.name",
          columnName: "customers.name",
          orderable: true,
        },
        {
          label: "Make",
          name: "vehicle.make",
          columnName: "vehicles.make",
          orderable: true,
        },
        {
          label: "Model",
          name: "vehicle.model",
          columnName: "vehicles.model",
          orderable: true,
        },
        {
          label: "Status",
          name: "status",
          orderable: true,
          component: "ServicingStatusCol",
        },
        {
          label: "Created At",
          name: "format_created_at_date",
          columnName: "created_at",
          orderable: true,
        },
      ],
      warrantyColumns: [
        {
          label: "Ref No",
          name: "ref_no",
          orderable: true,
        },
        {
          label: "CI Number",
          name: "ci_no",
          orderable: true,
        },
        {
          label: "Car Plate",
          name: "vehicle.registration_no",
          columnName: "vehicles.registration_no",
          orderable: true,
        },
        {
          label: "Customer",
          name: "proposer.name",
          columnName: "proposers.name",
          orderable: true,
        },
        {
          label: "Phone",
          name: "proposer.phone",
          columnName: "proposers.phone",
          orderable: true,
        },
        {
          label: "Make",
          name: "vehicle.make",
          columnName: "vehicles.make",
          orderable: true,
        },
        {
          label: "Model",
          name: "vehicle.model",
          columnName: "vehicles.model",
          orderable: true,
        },
        {
          label: "Dealer",
          name: "dealer.name",
          columnName: "companies.name",
          orderable: true,
        },
        {
          label: "Status",
          name: "status",
          orderable: true,
          component: "StatusCol",
        },
        {
          label: "Created At",
          name: "created_at",
          orderable: true,
        },
      ],
      claimsProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
      warrantyProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
      motorProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
      accidentReportingProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
      servicingProps: {
        search: "",
        length: 20,
        column: "id",
        dir: "desc",
        page: 1,
        draw: 0,
      },
      dataClaims: {},
      dataWarranty: {},
      dataMotor: {},
      dataAccidentReporting: {},
      dataServicing: {},
      perPage: [10, 20, 30, 40, 50],
    };
  },
  mounted() {
    var getDuration = localStorage.getItem("dashboardDuration");
    var tabClaims = localStorage.getItem("dashboardTabClaims");
    var tabWarranty = localStorage.getItem("dashboardTabWarranty");
    var tabMotor = localStorage.getItem("dashboardTabMotor");
    var tabAccidentReporting = localStorage.getItem("dashboardTabAccidentReporting");
    var tabServicing = localStorage.getItem("dashboardTabServicing");
    if (getDuration != null) {
      this.changeDuration(getDuration);
    }

    this.changeTab(tabClaims ? tabClaims : "draft");
    this.changeWarrantyTab(tabWarranty ? tabWarranty : "draft");
    this.changeMotorTab(tabMotor ? tabMotor : "draft");
    this.changeAccidentReportingTab(
      tabAccidentReporting ? tabAccidentReporting : "pending"
    );
    this.changeServicingTab(tabServicing ? tabServicing : "upcoming");
    this.parentTab = localStorage.getItem("parentTab");
    this.getCount();
    this.getWarrantyCount();
    this.getMotorCount();
    this.getAccidentReportingCount();
    this.getServicingCount();
  },
  watch: {
    duration(val) {
      localStorage.setItem("dashboardDuration", val);

      // claims section
      var metaClaims = JSON.parse(
        localStorage.getItem(`claimsDashboardMeta_${this.tab}`)
      );
      this.url =
        window.location.href.split("/dashboard")[0] +
        "/api/claims/" +
        val +
        "/" +
        this.tab;
      this.getData(
        this.url,
        metaClaims ? metaClaims : this.claimsProps,
        "claims"
      );

      // warranty section
      var warrantyMeta = JSON.parse(
        localStorage.getItem(`warrantyDashboardMeta_${this.warrantyTab}`)
      );
      this.warrantyUrl =
        window.location.href.split("/dashboard")[0] +
        "/api/warranties/" +
        val +
        "/" +
        this.warrantyTab;
      this.getData(
        this.warrantyUrl,
        warrantyMeta ? warrantyMeta : this.warrantyProps,
        "warranty"
      );

      // motor section
      var motorMeta = JSON.parse(
        localStorage.getItem(`motorDashboardMeta_${this.motorTab}`)
      );
      this.motorUrl =
        window.location.href.split("/dashboard")[0] +
        "/api/motors/" +
        val +
        "/" +
        this.motorTab;
      this.getData(
        this.motorUrl,
        motorMeta ? motorMeta : this.motorProps,
        "motor"
      );

      // accident reporting section
      var accidentReportingMeta = JSON.parse(
        localStorage.getItem(
          `accidentReportingDashboardMeta_${this.accidentReportingTab}`
        )
      );
      this.accidentReportingUrl =
        window.location.href.split("/dashboard")[0] +
        "/api/dashboard/accidents/" +
        val +
        "/" +
        this.accidentReportingTab;
      this.getData(
        this.accidentReportingUrl,
        accidentReportingMeta
          ? accidentReportingMeta
          : this.accidentReportingProps,
        "accidentReporting"
      );

      // servicing section
      var servicingMeta = JSON.parse(
        localStorage.getItem(`servicingDashboardMeta_${this.servicingTab}`)
      );
      this.servicingUrl =
        window.location.href.split("/dashboard")[0] +
        "/api/dashboard/servicing/" +
        val +
        "/" +
        this.servicingTab;
      this.getData(
        this.servicingUrl,
        servicingMeta ? servicingMeta : this.servicingProps,
        "servicing"
      );

      this.getCount();
      this.getWarrantyCount();
      this.getMotorCount();
      this.getAccidentReportingCount();
      this.getServicingCount();
    },
    parentTab(val) {},
    user(val) {
      var tabClaims = localStorage.getItem("dashboardTabClaims");
      var tabMotor = localStorage.getItem("dashboardTabMotor");
      var tabWarranty = localStorage.getItem("dashboardTabWarranty");
      var tabAccidentReporting = localStorage.getItem(
        "dashboardTabAccidentReporting"
      );
      var tabServicing = localStorage.getItem("dashboardTabServicing");
      var parentTab = localStorage.getItem("parentTab");
      if (tabWarranty == null) this.warrantyTab = "draft";
      if (tabMotor == null) this.motorTab = "draft";
      if (val.category == "insurer" || val.category == "surveyor") {
        if (tabClaims == null) this.tab = "surveyor_review";
      } else {
        // check tab
        if (tabClaims == null) this.tab = "draft";
      }
      if (parentTab == null) {
        if (val.category != "dealer") {
          this.parentTab = "claims";
        } else {
          this.parentTab = "warranty";
        }
      }
      if (tabAccidentReporting == null) this.accidentReportingTab = "pending";
      if (tabServicing == null) this.servicingTab = "upcoming";
    },
    tab(val) {
      var tabClaims = localStorage.setItem("dashboardTabClaims", val);
      var getMeta = JSON.parse(
        localStorage.getItem(`claimsDashboardMeta_${val}`)
      );
      this.url = `${window.location.href.split("/dashboard")[0]}/api/claims/${
        this.fromDate
      }/${this.toDate}/${val}`;
      this.getData(this.url, getMeta ? getMeta : this.claimsProps, "claims");
    },
    warrantyTab(val) {
      if (val != "null") {
        var tabWarranty = localStorage.setItem("dashboardTabWarranty", val);
        var getMeta = JSON.parse(
          localStorage.getItem(`warrantyDashboardMeta_${val}`)
        );
        this.warrantyUrl =
          window.location.href.split("/dashboard")[0] +
          "/api/warranties/" +
          this.fromDate +
          "/" +
          this.toDate +
          "/" +
          val;

        this.getData(
          this.warrantyUrl,
          getMeta ? getMeta : this.warrantyProps,
          "warranty"
        );
      }
    },
    motorTab(val) {
      if (val != "null") {
        var tabMotor = localStorage.setItem("dashboardTabMotor", val);
        var getMeta = JSON.parse(
          localStorage.getItem(`motorDashboardMeta_${val}`)
        );
        this.motorUrl =
          window.location.href.split("/dashboard")[0] +
          "/api/motors/" +
          this.fromDate +
          "/" +
          this.toDate +
          "/" +
          val;
        this.getData(
          this.motorUrl,
          getMeta ? getMeta : this.motorProps,
          "motor"
        );
      }
    },
    accidentReportingTab(val) {
      if (val != "null") {
        var tabAccidentReporting = localStorage.setItem(
          "dashboardTabAccidentReporting",
          val
        );
        this.accidentReportingUrl =
          window.location.href.split("/dashboard")[0] +
          "/api/dashboard/accidents/" +
          this.fromDate +
          "/" +
          this.toDate +
          "/" +
          val;
        var getMeta = JSON.parse(
          localStorage.getItem(`accidentReportingDashboardMeta_${val}`)
        );
        this.getData(
          this.accidentReportingUrl,
          getMeta ? getMeta : this.accidentReportingProps,
          "accidentReporting"
        );
      }
    },
    servicingTab(val) {
      if (val != "null") {
        var tabServicing = localStorage.setItem("dashboardTabServicing", val);
        var getMeta = JSON.parse(
          localStorage.getItem(`servicingDashboardMeta_${val}`)
        );
        this.servicingUrl =
          window.location.href.split("/dashboard")[0] +
          "/api/dashboard/servicing/" +
          this.fromDate +
          "/" +
          this.toDate +
          "/" +
          val;
        this.getData(
          this.servicingUrl,
          getMeta ? getMeta : this.servicingProps,
          "servicing"
        );
      }
    },
  },
  methods: {
    search() {
      //   if (this.formDate == null && this.toDate == null) return;

      let val = "week";
      localStorage.setItem("dashboardDuration", val);
      localStorage.setItem("dashboardFromDate", this.fromDate);
      localStorage.setItem("dashboardToDate", this.toDate);

      // claims section
      var metaClaims = JSON.parse(
        localStorage.getItem(`claimsDashboardMeta_${this.tab}`)
      );
      this.url = `${window.location.href.split("/dashboard")[0]}/api/claims/${
        this.fromDate
      }/${this.toDate}/${this.tab}`;

      this.getData(
        this.url,
        metaClaims ? metaClaims : this.claimsProps,
        "claims"
      );

      // warranty section
      var warrantyMeta = JSON.parse(
        localStorage.getItem(`warrantyDashboardMeta_${this.warrantyTab}`)
      );
      this.warrantyUrl =
        window.location.href.split("/dashboard")[0] +
        "/api/warranties/" +
        this.fromDate +
        "/" +
        this.toDate +
        "/" +
        this.warrantyTab;
      this.getData(
        this.warrantyUrl,
        warrantyMeta ? warrantyMeta : this.warrantyProps,
        "warranty"
      );

      // motor section
      var motorMeta = JSON.parse(
        localStorage.getItem(`motorDashboardMeta_${this.motorTab}`)
      );
      this.motorUrl =
        window.location.href.split("/dashboard")[0] +
        "/api/motors/" +
        this.fromDate +
        "/" +
        this.toDate +
        "/" +
        this.motorTab;
      this.getData(
        this.motorUrl,
        motorMeta ? motorMeta : this.motorProps,
        "motor"
      );

      // accident reporting section
      var accidentReportingMeta = JSON.parse(
        localStorage.getItem(
          `accidentReportingDashboardMeta_${this.accidentReportingTab}`
        )
      );
      this.accidentReportingUrl =
        window.location.href.split("/dashboard")[0] +
        "/api/dashboard/accidents/" +
        this.fromDate +
        "/" +
        this.toDate +
        "/" +
        this.accidentReportingTab;
      this.getData(
        this.accidentReportingUrl,
        accidentReportingMeta
          ? accidentReportingMeta
          : this.accidentReportingProps,
        "accidentReporting"
      );

      // servicing section
      var servicingMeta = JSON.parse(
        localStorage.getItem(`servicingDashboardMeta_${this.servicingTab}`)
      );
      this.servicingUrl =
        window.location.href.split("/dashboard")[0] +
        "/api/dashboard/servicing/" +
        this.fromDate +
        "/" +
        this.toDate +
        "/" +
        this.servicingTab;

      this.getData(
        this.servicingUrl,
        servicingMeta ? servicingMeta : this.servicingProps,
        "servicing"
      );

      this.getCount();
      this.getWarrantyCount();
      this.getMotorCount();
      this.getAccidentReportingCount();
      this.getServicingCount();
    },
    getCount() {
      var inputs = {};
      inputs.method = "post";
      inputs.url = "/api/claims/count";
      inputs.from_date = this.fromDate;
      inputs.to_date = this.toDate;
      this.$store.dispatch("API", inputs).then((data) => {
        this.count = [];
        this.action = data.action;
        for (var i = 0; i < data.count.length; i++) {
          this.count[data.count[i].status] = data.count[i].total;
        }
        this.count.splice(this.count.length);
      });
    },
    getWarrantyCount() {
      var inputs = {};
      inputs.method = "post";
      inputs.url = "/api/warranties/count";
      inputs.from_date = this.fromDate;
      inputs.to_date = this.toDate;
      this.$store.dispatch("API", inputs).then((data) => {
        this.warrantyCount = [];
        this.warrantyAction = data.action;
        for (var i = 0; i < data.count.length; i++) {
          this.warrantyCount[data.count[i].status] = data.count[i].total;
        }
        this.warrantyCount.splice(this.warrantyCount.length);
      });
    },
    getMotorCount() {
      var inputs = {};
      inputs.method = "post";
      inputs.url = "/api/motors/count";
      inputs.from_date = this.fromDate;
      inputs.to_date = this.toDate;
      this.$store.dispatch("API", inputs).then((data) => {
        this.motorCount = [];
        this.motorAction = data.action;
        for (var i = 0; i < data.count.length; i++) {
          this.motorCount[data.count[i].status] = data.count[i].total;
        }
        this.motorCount.splice(this.motorCount.length);
      });
    },
    getAccidentReportingCount() {
      var inputs = {};
      inputs.method = "post";
      inputs.url = "/api/dashboard/accidents/count";
      inputs.from_date = this.fromDate;
      inputs.to_date = this.toDate;
      this.$store.dispatch("API", inputs).then((data) => {
        this.accidentReportingCount = [];
        this.accidentReportingAction = data.action;
        for (var i = 0; i < data.count.length; i++) {
          this.accidentReportingCount[data.count[i].status] =
            data.count[i].total;
        }
        this.accidentReportingCount.splice(this.accidentReportingCount.length);
      });
    },
    getServicingCount() {
      var inputs = {};
      inputs.method = "post";
      inputs.url = "/api/dashboard/servicing/count";
      inputs.from_date = this.fromDate;
      inputs.to_date = this.toDate;
      this.$store.dispatch("API", inputs).then((data) => {
        this.servicingCount = [];
        this.servicingAction = data.action;
        for (var i = 0; i < data.count.length; i++) {
          this.servicingCount[data.count[i].status] = data.count[i].total;
        }
        this.servicingCount.splice(this.servicingCount.length);
      });
    },
    setParentTab(val) {
      localStorage.setItem("parentTab", val);
    },
    changeDuration(val) {
      this.duration = val;
    },
    changeTab(val) {
      this.tab = val;
    },
    changeWarrantyTab(val) {
      this.warrantyTab = val;
    },
    changeMotorTab(val) {
      this.motorTab = val;
    },
    changeAccidentReportingTab(val) {
      this.accidentReportingTab = val;
    },
    changeServicingTab(val) {
      this.servicingTab = val;
    },
    displayRow(data, type) {
      this.$router.push({ path: "/claims/details/" + data.id });
    },
    displayWarrantyRow(data, type) {
      this.$router.push({ path: "/warranties/details/" + data.id });
    },
    displayMotorRow(data, type) {
      this.$router.push({ path: "/motors/details/" + data.id });
    },
    displayAccidentReportingRow(data, type) {
      this.$router.push({ path: "/accidentReport/details/" + data.id });
    },
    displayServicingRow(data, type) {
      this.$router.push({ path: "/servicing/details/" + data.id });
    },
    getData(url = this.url, options = {}, type = null) {
      axios
        .get(url, {
          params: options,
        })
        .then((response) => {
          if (typeof response.data === "object") {
            if (type == "claims") {
              this.dataClaims = response.data;
            } else if (type == "warranty") {
              this.dataWarranty = response.data;
            } else if (type == "motor") {
              this.dataMotor = response.data;
            } else if (type == "accidentReporting") {
              this.dataAccidentReporting = response.data;
            } else if (type == "servicing") {
              this.dataServicing = response.data;
            }
          }
        })
        .catch((errors) => {});
    },
    reloadTableClaims(tableProps) {
      localStorage.setItem(
        `claimsDashboardMeta_${this.tab}`,
        JSON.stringify(tableProps)
      );
      this.getData(this.url, tableProps, "claims");
    },
    reloadTableWarranty(tableProps) {
      localStorage.setItem(
        `warrantyDashboardMeta_${this.warrantyTab}`,
        JSON.stringify(tableProps)
      );
      this.getData(this.warrantyUrl, tableProps, "warranty");
    },
    reloadTableMotor(tableProps) {
      localStorage.setItem(
        `motorDashboardMeta_${this.motorTab}`,
        JSON.stringify(tableProps)
      );
      this.getData(this.motorUrl, tableProps, "motor");
    },
    reloadTableAccidentReporting(tableProps) {
      localStorage.setItem(
        `accidentReportingDashboardMeta_${this.accidentReportingTab}`,
        JSON.stringify(tableProps)
      );
      this.getData(this.accidentReportingUrl, tableProps, "accidentReporting");
    },
    reloadTableServicing(tableProps) {
      localStorage.setItem(
        `servicingDashboardMeta_${this.servicingTab}`,
        JSON.stringify(tableProps)
      );
      this.getData(this.servicingUrl, tableProps, "servicing");
    },
  },
  computed: {
    ...mapGetters(["user"]),
  },
};
</script>
