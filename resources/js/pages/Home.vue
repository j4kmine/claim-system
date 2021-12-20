<template>
  <div class="c-app c-legacy-theme">
    <Sidebar/>
    <CWrapper>
      <Header/>
      <div class="c-body">
        <main class="c-main">
          <CContainer fluid>
            <router-view></router-view>
          </CContainer>
        </main>
      </div>
      <Footer/>
    </CWrapper>
  </div>
</template>
<script>
export default {
  mounted(){
    if(this.$router.history.current.path == '/'){
      this.$router.replace('/dashboard');
    }
  },
  watch: {
    $route(to, from) {
      // react to route changes...
      if(to.path == '/'){
        this.$router.replace('/dashboard');
      }
    }
  }
}
</script>