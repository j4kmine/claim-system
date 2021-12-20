<template>
    <div class="user-container">
        <div class="user-circle">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="20" r="20" fill="#868D90"/>
            </svg>
            <div class="initials">{{ name.charAt(0) }}</div>
        </div>
        <div class="user-info">
            <div class="name">{{ name }}</div>
            <div class="email">{{ this.data.email }}</div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        data: {}
    },
    computed:{
        name(){
            if (this.data.name){
                return this.data.name;
            }else if(this.data.email){
                return this.data.email;
            }else{
                return "-";
            }
        }
    }
}
</script>