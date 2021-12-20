<template>
  <div>
    <CCard v-if="user.company != null">
      <CCardHeader class="font-weight-bold"> Create Services </CCardHeader>
      <CCardBody>
        <form @submit.prevent="save()">
          <CRow>
            <CCol md="6">
              <CInput
                class="font-weight-bold"
                required
                label="Service"
                type="text"
                :value.sync="name"
              />
            </CCol>
            <CCol md="6">
              <CSelect
                class="font-weight-bold"
                required
                name="status"
                label="Status"
                :value.sync="status"
                :options="status_options"
                placeholder="Please select"
              />
            </CCol>
          </CRow>
          <CRow>
            <CCol md="12">
              <CTextarea
                label="Description"
                name="description"
                class="font-weight-bold"
                rows="5"
                :value.sync="description"
              />
            </CCol>
          </CRow>
          <CRow>
            <CCol md="12">
              <div>
                <strong>Color</strong>
              </div>
              <div style="display: flex">
                <button
                  v-bind:style="{ backgroundColor: color.hex }"
                  class="button-color-picker"
                  type="button"
                  @click="dialogColor = !dialogColor"
                ></button>
                <color-picker v-model="color" v-show="dialogColor" />
              </div>
            </CCol>
          </CRow>

          <div
            class="form-actions"
            v-if="user.role == 'admin'"
            style="margin-top: 10px"
          >
            <CButton type="submit" color="primary">Save changes</CButton>
            <CButton color="secondary" @click="cancel()">Cancel</CButton>
          </div>
        </form>
      </CCardBody>
    </CCard>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
import { Chrome } from "vue-color";

export default {
  components: {
    "color-picker": Chrome,
  },
  props: ["companyId", "companyUsers"],
  data() {
    return {
      id: "",
      name: "",
      url: "",
      method: "",
      status: "",
      status_options: [
        { label: "Active", value: "active" },
        { label: "Inactive", value: "inactive" },
      ],
      description: "",
      color: {},
      dialogColor: false,
    };
  },
  methods: {
    cancel() {
      this.$router.go(-1);
    },
    save() {
      // { value: 'Option2', label: 'Custom label'}
      var inputs = {};
      inputs.name = this.name;
      inputs.status = this.status;
      inputs.description = this.description;
      inputs.color = this.color.hex;
      inputs.url = this.url;
      inputs.method = this.method;
      this.$store.dispatch("API", inputs).then(() => {
        this.$router.push("/companyProfile");
      });
    },
  },
  mounted() {
    this.id = window.location.pathname.split("/").pop();
    if (this.id == "createServiceTypes") {
      this.method = "post";
      this.url = "/api/service-types";
    } else {
      this.method = "put";
      this.url = `/api/service-types/${this.id}/update`;

      // get form data
      var inputs = {};
      inputs.url = `/api/service-types/${this.id}`;
      inputs.method = "get";
      this.$store.dispatch("API", inputs).then((response) => {
        this.name = response.name;
        this.status = response.status;
        this.description = response.description;
        this.color.hex = response.color ?? "#FFFFFF";
      });
    }
  },
  computed: {
    ...mapGetters(["user"]),
  },
};
</script>

<style lang="css">
.button-color-picker {
  width: 100px;
  outline: none;
  border-radius: 4px;
  height: 35px;
  margin-right: 10px;
}
</style>
