<template>
  <CHeader fixed with-subheader light>
    <CSubheader>
      <CToggler
        in-header
        class="d-lg-none"
        @click="$store.commit('TOGGLE_SIDEBAR_MOBILE')"
      />
      <CToggler
        in-header
        class="d-md-down-none"
        @click="$store.commit('TOGGLE_SIDEBAR_DESKTOP')"
      />
      <CBreadcrumbRouter v-if="
      !$route.name.includes('Edit') 
      && !$route.name.includes('Details') 
      || $route.name.includes('Customer') 
      || $route.path.includes('companyProfile') 
      || $route.path.includes('accidentReport')
      || $route.path.includes('customers')
      || $route.path.includes('servicing')
      " class="border-0 mb-0">
      </CBreadcrumbRouter>
      <ol v-else class="breadcrumb border-0 mb-0">
        <li role="presentation" class="breadcrumb-item">
          <a href="/" class="router-link-active" target="_self"> Home </a>
        </li>
        <li role="presentation" class="breadcrumb-item">
            <template v-if="$route.name.includes('Claim')">
              <router-link v-if="bread == '' || bread.includes('All')" to='/claims' class="router-link-active">
                All Claims
              </router-link>
              <router-link v-else :to='path' class="router-link-active">
                {{ bread }}
              </router-link>
            </template>
            <template v-else-if="$route.name.includes('Price')">
              <router-link v-if="bread == '' || bread.includes('All')" to='/warrantyPrices' class="router-link-active">
                All Warranty Prices
              </router-link>
              <router-link v-else :to='path' class="router-link-active">
                {{ bread }}
              </router-link>
            </template>
            <template v-else-if="$route.name.includes('Warrant')">
              <router-link v-if="bread == '' || bread.includes('All')" to='/warranties' class="router-link-active">
                All Warranty Orders
              </router-link>
              <router-link v-else :to='path' class="router-link-active">
                {{ bread }}
              </router-link>
            </template>
            <template v-else-if="$route.name.includes('Motor')">
              <router-link v-if="bread == '' || bread.includes('All')" to='/motors' class="router-link-active">
                All Motor Orders
              </router-link>
              <router-link v-else :to='path' class="router-link-active">
                {{ bread }}
              </router-link>
            </template>
            <template v-else-if="$route.name.includes('User')">
              <router-link v-if="bread == '' || bread.includes('All')" to='/users' class="router-link-active">
                All Users
              </router-link>
              <router-link v-else :to='path' class="router-link-active">
                {{ bread }}
              </router-link>
            </template>
            <template v-else-if="$route.name.includes('Dealer')">
              <router-link v-if="bread == '' || bread.includes('All')" to='/dealers' class="router-link-active">
                All Dealers
              </router-link>
              <router-link v-else :to='path' class="router-link-active">
                {{ bread }}
              </router-link>
            </template>
            <template v-else-if="$route.name.includes('Insurer')">
              <router-link v-if="bread == '' || bread.includes('All')" to='/insurers' class="router-link-active">
                All Insurers
              </router-link>
              <router-link v-else :to='path' class="router-link-active">
                {{ bread }}
              </router-link>
            </template>
            <template v-else-if="$route.name.includes('Surveyor')">
              <router-link v-if="bread == '' || bread.includes('All')" to='/surveyors' class="router-link-active">
                All Surveyors
              </router-link>
              <router-link v-else :to='path' class="router-link-active">
                {{ bread }}
              </router-link>
            </template>
            <template v-else-if="$route.name.includes('Workshop')">
              <router-link v-if="bread == '' || bread.includes('All')" to='/workshops' class="router-link-active">
                All Workshops
              </router-link>
              <router-link v-else :to='path' class="router-link-active">
                {{ bread }}
              </router-link>
            </template>
            <template v-else>
              <router-link :to='path' class="router-link-active">
                {{ bread }}
              </router-link>
            </template>
        </li>
        <li role="presentation" class="active breadcrumb-item">
          <span>{{ $route.name }}</span>
        </li>
      </ol>
      <template v-if="user.role == 'admin'">
        <CButton v-if="$route.name == 'All Users'" @click="create('users')" class="btn-right" color="primary">Create User</CButton>
        <CButton v-else-if="$route.name == 'Company Profile'" @click="create('users')" class="btn-right" color="primary">Create User</CButton>
        <CButton v-else-if="$route.name == 'All Dealers'" @click="create('dealers')" class="btn-right" color="primary">Create Dealer</CButton>
        <CButton v-else-if="$route.name == 'All Insurers'" @click="create('insurers')" class="btn-right" color="primary">Create Insurer</CButton>
        <CButton v-else-if="$route.name == 'All Workshops'" @click="create('workshops')" class="btn-right" color="primary">Create Workshop</CButton>
        <CButton v-else-if="$route.name == 'All Surveyors'" @click="create('surveyors')" class="btn-right" color="primary">Create Surveyor</CButton>
        <!--<CButton v-else-if="$route.name == 'All Warranty Prices'" @click="create('warrantyPrices')" class="btn-right" color="primary">Create Warranty Price</CButton>-->
      </template>
      <template v-if="(user.role == 'admin' || user.role=='support_staff') && user.category=='workshop'">
        <CButton v-if="$route.path == '/servicing'" @click="create('servicing')" class="btn-right" color="primary">Create Appointment</CButton>
      </template>
    </CSubheader>
  </CHeader>
</template> 
<script>
import { mapGetters } from "vuex";
export default {
    data: function () {
      return {
        bread: '',
        path: ''
      }
    },
    methods: {
        create(model) {
          this.$router.push('/'+model+'/create');
        },
    },
    watch:{
        $route(to, from){
          this.bread = from.name;
          this.path = from.path;
        }
    },
    computed: {
        ...mapGetters(["user"]),
    },
}
</script>
