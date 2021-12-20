<template>
    <form @submit.prevent="save()">
        <CRow>
            <CCol lg="12">
                <CCard>
                    <CCardHeader>
                        {{ $route.name }}
                    </CCardHeader>
                    <CCardBody>
                        <CRow>
                            <CCol md="6">
                                <CInput
                                    required
                                    label="Make"
                                    :value.sync="make"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    required
                                    label="Model"
                                    :value.sync="model"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    required
                                    label="Category"
                                    :value.sync="category"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    required
                                    label="Capacity"
                                    :value.sync="capacity"
                                />
                            </CCol>
                            <CCol md="6">
                                <CSelect
                                    required
                                    label="Type"
                                    :value.sync="type"
                                    :options="type_options"
                                    placeholder="Please select"
                                />
                            </CCol>
                            <CCol md="6">
                                <CSelect
                                    required
                                    label="Fuel"
                                    :value.sync="fuel"
                                    :options="fuel_options"
                                    placeholder="Please select"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    required
                                    label="Price"
                                    :value.sync="price"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    required
                                    label="Max Claim"
                                    :value.sync="max_claim"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    required
                                    label="Mileage Coverage (KM)"
                                    :value.sync="mileage_coverage"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput
                                    required
                                    label="Warranty Duration (years)"
                                    :value.sync="warranty_duration"
                                />
                            </CCol>
                            <CCol md="6">
                                <CSelect
                                    required
                                    name="insurer_id"
                                    label="Insurer"
                                    placeholder="Please select"
                                    :value.sync="insurer_id"
                                    :options="insurer_options"
                                />
                            </CCol>
                            <CCol md="6">
                                <CSelect
                                    required
                                    label="Status"
                                    :value.sync="status"
                                    :options="status_options"
                                    placeholder="Please select"
                                />
                            </CCol>
                        </CRow>
                        <div class="form-actions" v-if="user.role == 'admin'">
                            <CButton type="submit" color="primary">Save changes</CButton>
                            <CButton color="secondary" @click="cancel()">Cancel</CButton>
                        </div>
                    </CCardBody>
                </CCard>
                </transition>
            </CCol>
        </CRow>
    </form>
</template>
<script>
import { mapGetters } from "vuex";
export default {
    data() {
        return {
            make: "",
            model: "",
            category: "",
            capacity: "",
            type: "",
            fuel: "",
            price: "",
            max_claim : "",
            mileage_coverage: "",
            warranty_duration: "",
            status: "",
            type_options: [
                { label: 'New', value: 'new' },
                { label: 'Preowned', value: 'preowned' },
            ],
            fuel_options: [
                { label: 'Non-hybrid', value: 'non_hybrid' },
                { label: 'Hybrid', value: 'hybrid' },
            ],
            status_options: [
                { label: 'Active', value: 'active' },
                { label: 'Inactive', value: 'inactive' }
            ],
            insurer_options: []
        }
    },
    mounted(){ 
        if(this.$route.name == "Edit Warranty Price"){
            var inputs = {};
            inputs.method = 'get';
            inputs.url = "/api/warrantyPrices/edit";
            inputs.id = window.location.pathname.split("/").pop();
            this.$store.dispatch('API', inputs).then((data)=>{
                this.make = data.price.make;
                this.model = data.price.model;
                this.category = data.price.category;
                this.capacity = data.price.capacity;
                this.type = data.price.type;
                this.fuel = data.price.fuel;
                this.price = data.price.price;
                this.max_claim = data.price.max_claim;
                this.mileage_coverage  = data.price.mileage_coverage;
                this.warranty_duration  = data.price.warranty_duration;
                this.status = data.price.status;
            }); 
        }

        var inputs = {};
        inputs.method = 'post';
        inputs.url = '/api/companies';
        inputs.category = 'insurer';
        this.$store.dispatch('API', inputs).then((data)=>{
            for(var i = 0 ; i < data.companies.length; i++){
                this.insurer_options.push({ label: data.companies[i].name, value: data.companies[i].id });
            }
        });
    },
    methods: {
        save() {
            // { value: 'Option2', label: 'Custom label'}
            var inputs = {};
            inputs.make = this.make;
            inputs.model = this.model;
            inputs.category = this.category;
            inputs.capacity = this.capacity;
            inputs.type = this.type;
            inputs.fuel = this.fuel;
            inputs.price = this.price;
            inputs.max_claim = this.max_claim;
            inputs.mileage_coverage  = this.mileage_coverage;
            inputs.warranty_duration  = this.warranty_duration;
            inputs.status = this.status;
            inputs.method = "post";
            if(this.$route.name == "Edit Warranty Price"){
                inputs.id = window.location.pathname.split("/").pop();
                inputs.url = "/api/warrantyPrices/edit";
            } else {
                inputs.url = "/api/warrantyPrices/create";
            }
            this.$store.dispatch('API', inputs).then(()=>{
                this.$router.push('/warrantyPrices');
            }); 
        },
        cancel() {
            this.$router.go(-1);
        },
    },
    computed: {
        ...mapGetters(["user"]),
    },
}
</script>