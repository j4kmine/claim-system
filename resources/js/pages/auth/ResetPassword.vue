<template>
  <div class="c-app flex-row align-items-center">
    <CContainer>
      <CRow class="justify-content-center">
        <CCol md="8">
          <CCardGroup>
            <CCard class="p-4">
              <CCardBody>
                <div style="text-align : center; margin-bottom: 2rem;">
                  <img src="/images/vue/login.png">
                </div>
                <CForm v-on:submit.prevent="reset()">
                  <CInput
                    v-model="email"
                    placeholder="Email"
                    autocomplete="email"
                  >
                    <template #prepend-content><CIcon name="cilUser"/></template>
                  </CInput>
                  <CInput
                    v-model="password"
                    placeholder="New Password"
                    type="password"
                    autocomplete="password"
                  >
                    <template #prepend-content><CIcon name="cilLockLocked"/></template>
                  </CInput>
                  <CInput
                    v-model="confirm_password"
                    placeholder="Confirm Password"
                    type="password"
                    autocomplete="confirm_password"
                  >
                    <template #prepend-content><CIcon name="cilLockLocked"/></template>
                  </CInput>
                  <CRow>
                    <CCol col="6" class="text-left">
                      <CButton color="primary" class="px-4" type="submit">Reset</CButton>
                    </CCol>
                    <CCol col="6" class="text-right">
                      <CButton color="link" class="px-0" @click="back()">Back to login</CButton>
                    </CCol>
                  </CRow>
                </CForm>
              </CCardBody>
            </CCard>
          </CCardGroup>
        </CCol>
      </CRow>
    </CContainer>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
export default {
    data() {
        return {
            email: "",
            confirm_password: "",
            password: ""
        }
    },
    methods: {
        reset(){
            var inputs= {};
            inputs.method = 'post';
            inputs.url = "/api/resetPassword";
            inputs.email = this.email;
            inputs.password = this.password;
            inputs.password_confirmation = this.confirm_password;
            inputs.token = window.location.pathname.split("/").pop();
            this.$store.dispatch("API", inputs).then(() => {
              this.$store.commit('RESET');
              this.back();
            });
        },
        back(){
          this.$router.replace('/login');
        },
    },
};
</script>
