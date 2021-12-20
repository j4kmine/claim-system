<template>
    <CCard>
        <CCardHeader class="font-weight-bold">Claims History</CCardHeader>
        <CCardBody>
            <data-table
                :columns="columns"
                :url="url"
                order-by="id"
                order-dir="desc"
                class="vehicledetail-datatable"
                :classes="classes"
                :headers="headers">
            </data-table>
        </CCardBody>
    </CCard>
</template>
<script>
export default {
  props: ["idVehicle"],
  data() {
    return {
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
          label: "Workshop",
          name: "workshop.name",
          columnName: "companies.name",
          orderable: true,
        },
        {
          label: "Insurer",
          name: "company.name",
          orderable: true,
        },
        {
          label: "Policy Cert No.",
          name: "policy_certificate_no",
          orderable: true,
        },
        {
          label: "Amount",
          name: "total_claim_amount",
          orderable: true,
        },
        {
          label: "Status",
          name: "status",
          orderable: true,
          component: "StatusCol",
        },
      ],
    };
  },
  mounted() {},
  computed: {
    url() {
      if (this.idVehicle != null && this.idVehicle != "") {
        return (
          window.location.protocol +
          "//" +
          window.location.hostname +
          "/api/vehicles/" +
          this.idVehicle +
          "/claims-history"
        );
      }
    },
  },
};
</script>
<style scoped>
.vehicledetail-datatable >>> .mb-3 {
  display: none;
}
</style>