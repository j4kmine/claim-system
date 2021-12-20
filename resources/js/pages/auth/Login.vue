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
                <CForm v-on:submit.prevent="login()">
                  <CInput
                    v-model="email"
                    placeholder="Email"
                    autocomplete="email"
                  >
                    <template #prepend-content><CIcon name="cilUser"/></template>
                  </CInput>
                  <CInput
                    v-model="password"
                    placeholder="Password"
                    type="password"
                    autocomplete="password"
                  >
                    <template #prepend-content><CIcon name="cilLockLocked"/></template>
                  </CInput>
                  <CRow>
                    <CCol col="6" class="text-left">
                      <CButton color="primary" class="px-4" type="submit">Login</CButton>
                    </CCol>
                    <CCol col="6" class="text-right">
                      <CButton color="link" class="px-0" @click="forgotPassword()">Forgot password?</CButton>
                    </CCol>
                  </CRow>
                  <CRow class="mt-2">
                    <CCol class="text-left">
                      By logging in, you agree to our <a href="/documents/terms.pdf" target="_blank">Terms & Conditions</a>, <a href="/documents/privacy.pdf" target="_blank">Privacy Policy</a> and <a href="/documents/dealer.pdf" target="_blank">Motor Dealers Agreement</a> (if applicable).
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
            password: "",
            remember_me: false
        }
    },
    methods: {
        login(){
            var credentials = {};
            credentials.email = this.email;
            credentials.password = this.password;
            credentials.remember_me = this.remember_me;
            this.$store.dispatch("LOGIN", credentials);
        },
        forgotPassword(){
          this.$router.push('/password/forgot');
        }
    },
};
</script>
