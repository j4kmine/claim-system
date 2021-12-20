<template>
    <router-view></router-view>
</template>

<script>
    export default {
        mounted() {
            if (!this.$route.path.includes('/password')) {
                this.$store.dispatch('GET_USER');
            }
        },
        watch:{
            $route(to, from){
                if (to.path == "/logout"){
                    this.$store.dispatch('LOGOUT');
                //} else if (to.path == "/login" || to.path == "/dashboard" || to.path == "/reports" || to.path == '/insurers/create' || to.path == '/profile'){
                } else if (!to.path.includes('/password')) {
                    this.$store.dispatch('GET_USER');
                }
    
                if(!to.path.includes('/users') && !to.path.includes('/insurers') && !to.path.includes('/workshops') && !to.path.includes('/surveyors') && !to.path.includes('/dealers')){
                    if($("#partners-tab.c-show a").length > 0){
                        $("#partners-tab.c-show a")[0].click();
                    }
                }

                if(!to.path.includes('/claims')){
                    if($("#claims-tab.c-show a").length > 0){
                        $("#claims-tab.c-show a")[0].click();
                    }
                }

                if(!to.path.includes('/warranties')){
                    if($("#warranties-tab.c-show a").length > 0){
                        $("#warranties-tab.c-show a")[0].click();
                    }
                }

                if(!to.path.includes('/motors')){
                    if($("#motors-tab.c-show a").length > 0){
                        $("#motors-tab.c-show a")[0].click();
                    }
                }
                
            }
        }
    }
</script>
