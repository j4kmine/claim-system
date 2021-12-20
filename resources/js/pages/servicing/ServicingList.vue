<template>
  <div>
    <!-- only admin -->
    <CRow>
      <CCol md="2" class="font-weight-bold" style="margin-bottom: 5px">
        Workshop
      </CCol>
    </CRow>
    <div v-show="icons.calendar">
      <CRow>
        <CCol md="3" v-if="user.category == 'all_cars'">
          <CSelect
            :value.sync="workshopValue"
            class="font-weight-bold"
            :options="workshopOptions"
            @change="getServicingByWorkshop()"
          />
        </CCol>
        <CCol :md="2">
          <span @click="setActiveClass('list')">
            <CIcon
              class="icons-servicing"
              :class="{ active: icons.list == true }"
              name="cil-list"
            />
          </span>
          <span @click="setActiveClass('calendar')">
            <CIcon
              class="icons-servicing"
              :class="{ active: icons.calendar == true }"
              name="cil-calendar"
            />
          </span>
        </CCol>
        <CCol md="3" v-if="user.category == 'workshop'">
          <CSelect
            :value.sync="serviceTypeValue"
            :options="serviceTypeOptions"
            @change="searchByServiceType()"
            :key="getData"
            placeholder="Select Type of Service"
          />
        </CCol>
        <CCol :md="{ size: '6', offset: '1' }" class="text-right">
          <CSelect
            :options="dayWeekMonthOptions"
            :value.sync="dayWeekMonthValue"
            @change="changeAgenda()"
            style="width: 50%; display: inline-block"
          />
          <CButton color="light" @click="prevCalendar()">
            &#60; Previous</CButton
          >
          <CButton color="light" @click="nextCalendar()">Next > </CButton>
        </CCol>
      </CRow>
    </div>

    <label
      v-show="icons.calendar && dayWeekMonthValue == 'agendaDay'"
      class="font-weight-bold"
      >{{ currentDate.label }}</label
    >
    <data-table
      @row-clicked="displayRow"
      :columns="columns"
      v-if="
        !icons.calendar || (icons.calendar && dayWeekMonthValue == 'agendaDay')
      "
      order-by="id"
      order-dir="desc"
      :url="url"
      :filters="filterData"
      class="datatable-servicing"
      :classes="classes"
      :headers="headers"
      :debounce-delay="debounceDelay"
      :key="keyComponent"
    >
      <div slot="filters" slot-scope="{ tableFilters, perPage }">
        <CRow v-if="!icons.calendar">
          <CCol md="3" v-if="user.category == 'all_cars'">
            <CSelect
              :value.sync="workshopValue"
              class="font-weight-bold"
              :options="workshopOptions"
              @change="getServicingByWorkshop()"
            />
          </CCol>
          <CCol :md="2">
            <span @click="setActiveClass('list')">
              <CIcon
                class="icons-servicing"
                :class="{ active: icons.list == true }"
                name="cil-list"
              />
            </span>
            <span @click="setActiveClass('calendar')">
              <CIcon
                class="icons-servicing"
                :class="{ active: icons.calendar == true }"
                name="cil-calendar"
              />
            </span>
          </CCol>
          <CCol md="3" v-if="user.category == 'workshop'">
            <CSelect
              :value.sync="serviceTypeValue"
              :options="serviceTypeOptions"
              @change="searchByServiceType()"
              :key="getData"
              placeholder="Select Type of Service"
            />
          </CCol>
          <CCol :md="{ size: '4', offset: '3' }" class="text-right">
            <CInput
              type="text"
              placeholder="Search"
              name="name"
              v-model="tableFilters.search"
            />
          </CCol>
        </CRow>
        <input
          type="hidden"
          name=""
          :value="tableFilters.filters.appointment_date"
        />
      </div>
    </data-table>
    <div class="container" v-show="calendarShow">
      <full-calendar
        id="fullCalendar"
        :events="eventsCalendar"
        :header="headerCalendar"
        @event-selected="eventSelected"
        ref="fullCalendar"
        :config="config"
        style="margin-bottom: 2rem"
        :sync="true"
      >
      </full-calendar>
    </div>
  </div>
</template>
<script>
import columnsServicing from "../../servicing/column-servicing";
import monthNames from "../../servicing/month-names";
import { mapGetters } from "vuex";
export default {
  data() {
    const month = new Date().getMonth();
    const year = new Date().getFullYear();
    return {
      debounceDelay: 250,
      headerCalendar: {
        left: "title",
        right: "",
      },
      keyComponent: 0,
      config: {
        defaultView: "agendaWeek",
      },
      workshopValue: "",
      workshopOptions: [],
      dayWeekMonthOptions: [
        { label: "Month", value: "month" },
        { label: "Week", value: "agendaWeek" },
        { label: "Day", value: "agendaDay" },
      ],
      dayWeekMonthValue: "agendaWeek",
      url: "",
      currentDate: {
        label: "",
        value: "",
      },
      filterData: {
        appointment_date: "",
        service_type_id: "",
        workshop: "",
        length: 20,
      },
      icons: {
        calendar: false,
        list: true,
      },
      classes: {
        table: {
          "table-striped": false,
        },
      },
      headers: {
        Authorization: window.axios.defaults.headers.common["Authorization"],
      },
      columns: columnsServicing,
      eventsCalendar: [],
      serviceTypeValue: "",
      serviceTypeOptions: [],
      color: ["red", "yellow", "blue"],
      eventSources: [
        {
          color: "yellow",
          textColor: "black",
        },
      ],
    };
  },
  mounted() {},
  watch: {
    calendarShow(value) {
      if (value) {
        this.prevCalendar();
        setTimeout(() => {
          this.nextCalendar();
        }, 100);
      }
    },
    user: function (val) {
      if (
        this.user.category == "all_cars" ||
        this.user.category == "workshop"
      ) {
        this.url =
          window.location.protocol +
          "//" +
          window.location.hostname +
          "/api/servicing";

        // get workshop
        var inputs = {};
        inputs.url = "/api/workshops?length=20";
        this.$store.dispatch("API", inputs).then(async (data) => {
          this.workshopOptions = await data.data.map((workshops) => {
            if (this.workshopValue == "") this.workshopValue = workshops.id;
            return { label: workshops.name, value: workshops.id };
          });
          this.getServicingByWorkshop();
        });
      } else {
        // redirect to dashboard if not allowed
        this.$router.push("/dashboard");
      }
    },
  },
  methods: {
    eventSelected(params) {
      this.$router.push({ path: "/servicing/details/" + params.id });
    },
    getServicingByWorkshop() {
      this.filterData.workshop = this.workshopValue;

      this.eventsCalendar = [];
      var inputs = {};
      inputs.url = `/api/servicing?is_calendar=true&workshop=${this.workshopValue}`;
      inputs.methods = "get";
      this.$store.dispatch("API", inputs).then((dataEvents) => {
        this.eventsCalendar = dataEvents.data.map((event) => {
          var customer_name = event.customer.name ? event.customer.name : "-";
          var registration_no = event.vehicle?.registration_no ?? "-";
          var service_name = event.service_type?.name ?? "-";
          return {
            title: `${customer_name} ${registration_no} ${service_name}`,
            start: new Date(event.appointment_date),
            id: event.id,
            color: event.service_type.color ?? "blue",
          };
        });
        this.$refs.fullCalendar.$emit("reload-events");
      });
      if (this.icons.list) {
        this.keyComponent = this.keyComponent + 1;
      }
      // this.keyComponent = this.keyComponent + 1;
    },
    displayRow(data) {
      this.$router.push({ path: "/servicing/details/" + data.id });
    },
    setActiveClass(params) {
      if (params == "list") {
        this.filterData.appointment_date = "";
      }
      this.icons = _.mapValues(this.icons, () => false);
      this.icons[params] = !this.icons[params];

      if (this.dayWeekMonthValue == "agendaDay" && this.icons.calendar) {
        this.filterData.appointment_date = this.currentDate.value;
        this.keyComponent += 1;
      }
    },
    searchByServiceType() {
      this.filterData.service_type_id = this.serviceTypeValue;
      this.eventsCalendar = [];
      var inputs = {};
      inputs.url = `/api/servicing?length=20&service_type_id=${this.serviceTypeValue}`;
      inputs.methods = "get";
      this.$store.dispatch("API", inputs).then((dataEvents) => {
        this.eventsCalendar = dataEvents.data.map((event) => {
          var customer_name = event.customer.name ? event.customer.name : "-";
          return {
            title: `${customer_name} ${event.vehicle.registration_no} ${event.service_type.name}`,
            start: new Date(event.appointment_date),
            id: event.id,
            color: event.service_type.color ?? "blue",
          };
        });
        this.$refs.fullCalendar.$emit("reload-events");
      });
      if (this.icons.list) {
        this.keyComponent = this.keyComponent + 1;
      }
    },
    changeAgenda() {
      const today = new Date();
      const value =
        today.getFullYear() +
        "-" +
        (today.getMonth() + 1) +
        "-" +
        today.getDate();
      const date =
        today.getDate() +
        "-" +
        monthNames[today.getMonth()] +
        "-" +
        today.getFullYear();
      this.currentDate.label = date;
      this.currentDate.value = value;
      this.filterData.appointment_date = value;
      this.$refs.fullCalendar.fireMethod("changeView", this.dayWeekMonthValue);
    },
    prevCalendar() {
      var date = new Date(this.currentDate.value);
      date.setDate(date.getDate() - 1);
      this.currentDate.label =
        date.getDate() +
        "-" +
        monthNames[date.getMonth()] +
        "-" +
        date.getFullYear();
      this.currentDate.value =
        date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
      this.filterData.appointment_date =
        date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
      this.keyComponent += 1;
      this.$refs.fullCalendar.fireMethod("prev");
    },
    nextCalendar() {
      var date = new Date(this.currentDate.value);
      date.setDate(date.getDate() + 1);
      this.currentDate.label =
        date.getDate() +
        "-" +
        monthNames[date.getMonth()] +
        "-" +
        date.getFullYear();
      this.currentDate.value =
        date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
      this.filterData.appointment_date =
        date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
      this.keyComponent += 1;
      this.$refs.fullCalendar.fireMethod("next");
    },
  },
  computed: {
    ...mapGetters(["user"]),
    getData() {
      if (this.user.category == "workshop") {
        var inputs = {};
        inputs.url = `/api/workshops/${this.user.company_id}/service-types`;
        this.$store.dispatch("API", inputs).then((dataServiceType) => {
          this.serviceTypeOptions = dataServiceType.map((serviceType) => {
            return { label: serviceType.name, value: serviceType.id };
          });
        });
      }
    },
    calendarShow() {
      return this.icons.calendar && this.dayWeekMonthValue != "agendaDay";
    },
  },
};
</script>
<style>
.icons-servicing {
  height: 34px !important;
  width: 2rem !important;
  border: 1px solid #e4e7ea;
  padding: 0px 4px;
  border-radius: 0.25rem;
}
.icons-servicing.active {
  color: blue;
}
.icons-servicing:hover {
  cursor: pointer;
}
.fc-left >>> h2 {
  font-size: 14px !important;
}

.fc-axis {
  width: 50px !important;
}

/* .fc-scroller {
  overflow: hidden scroll !important;
  height: 600px !important;
} */
</style>
