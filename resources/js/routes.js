import Home from './pages/Home';
import Dashboard from './pages/Dashboard';
import Login from './pages/auth/Login';
import ForgotPassword from './pages/auth/ForgotPassword';
import ResetPassword from './pages/auth/ResetPassword';
import CreateClaim from './pages/claim/CreateClaim';
import Claims from './pages/claim/Claims';
import Claim from './pages/claim/Claim';
import CreateMotor from './pages/motor/CreateMotor';
import Motors from './pages/motor/Motors';
import Motor from './pages/motor/Motor';
import CreateWarranty from './pages/warranty/CreateWarranty';
import Warranties from './pages/warranty/Warranties';
import Warranty from './pages/warranty/Warranty';
import CreateWarrantyPrice from './pages/warranty/CreateWarrantyPrice';
import WarrantyPrices from './pages/warranty/WarrantyPrices';
import CreateUser from './pages/user/CreateUser';
import Users from './pages/user/Users';
import CreateCustomer from './pages/customer/CreateCustomer';
import CustomerDetail from './pages/customer/CustomerDetail';
import Customers from './pages/customer/Customers';
import VehicleDetails from './pages/customer/VehicleDetails';
import Vehicles from './pages/customer/Vehicles';
import CreateCompany from './pages/company/CreateCompany';
import Companies from './pages/company/Companies';
import Reports from './pages/Reports';
import Profile from './pages/Profile';
import ServicingList from './pages/servicing/ServicingList';
import CreateServicing from './pages/servicing/CreateServicing';
import CompanyProfile from './pages/companyprofile/CompanyProfile';
import CreateServicingSlots from './pages/companyprofile/CreateServicingSlots';
import CreateServiceTypes from './pages/companyprofile/CreateServiceTypes';
import CreateCompanyUser from './pages/companyprofile/CreateCompanyUser';
import ServicingDetails from './pages/servicing/ServicingDetails'
import AccidentReportingList from './pages/accidentreporting/AccidentReportingList'
import AccidentReportingDetails from './pages/accidentreporting/AccidentReportingDetails'

const routes = [
    { 
        path: '/login',
        name: 'Login',
        component: Login
    },
    /*
    {
        path: '/register',
        name: 'register',
        component: Register
    },*/
    {
        path: '/password/forgot',
        name: 'Forgot Password',
        component: ForgotPassword
    },
    {
        path: '/password/reset/*', 
        name: 'Reset Password',
        component: ResetPassword
    },

    {
        path: '/',
        name: 'Home', 
        component: Home,
        children: [
            {
                path: '/dashboard',
                name: 'Dashboard',
                component: Dashboard
            },
            {
                path: '/warranties/details/*',
                name: 'Warranty Details',
                component: Warranty
            },
            {
                path: '/warranties/create',
                name: 'Create Warranty Order',
                component: CreateWarranty
            },
            {
                path: '/warranties',
                name: 'All Warranty Orders',
                component: Warranties
            },
            {
                path: '/warranties/drafts',
                name: 'Warranty Drafts',
                component: Warranties
            },
            {
                path: '/warranties/archives',
                name: 'Warranty Archives',
                component: Warranties
            },
            {
                path: '/motors/details/*',
                name: 'Motor Details',
                component: Motor
            },
            {
                path: '/motors/create',
                name: 'Create Motor Order',
                component: CreateMotor
            },
            {
                path: '/motors',
                name: 'All Motor Orders',
                component: Motors
            },
            {
                path: '/motors/drafts',
                name: 'Motor Drafts',
                component: Motors
            },
            {
                path: '/motors/archives',
                name: 'Motor Archives',
                component: Motors
            },
            {
                path: 'accidentReport/',
                name: 'Accident Reporting',
                redirect: 'accidentReport/list',
                component:  {
                    render(c) {
                        return c('router-view')
                    },
                },
                children: [
                    {
                        path: 'list',
                        name: '',
                        component: AccidentReportingList
                    },
                    {
                        path: 'details/*',
                        name: 'Show Details',
                        component: AccidentReportingDetails
                    },
                ]
            },
            {
                path: '/claims/details/*',
                name: 'Claim Details',
                component: Claim
            },
            {
                path: '/claims/create',
                name: 'Create Claim',
                component: CreateClaim
            },
            {
                path: '/claims',
                name: 'All Claims',
                component: Claims
            },
            {
                path: '/claims/drafts',
                name: 'Claim Drafts',
                component: Claims
            },
            {
                path: '/claims/archives',
                name: 'Claim Archives',
                component: Claims
            },
            {
                path: '/users/create',
                name: 'Create User',
                component: CreateUser
            },
            {
                path: '/users/edit/*',
                name: 'Edit User',
                component: CreateUser
            },
            {
                path: '/users',
                name: 'All Users',
                component: Users
            },

            {
                path: '/customers/create',
                name: 'Create Customer',
                component: CreateCustomer
            },
            {
                path: 'customers',
                name: 'Customers',
                redirect: 'customers/profiles',
                component:  {
                    render(c) {
                        return c('router-view')
                    },
                },
                children: [
                    {
                        path: 'profiles',
                        name: 'Profiles',
                        redirect: '/customers/profiles',
                        component:  {
                            render(c) {
                                return c('router-view')
                            },
                        },
                        children: [
                            {
                                path: '',
                                name: '',
                                component: Customers
                            },
                            {
                                path: 'edit/*',
                                name: 'Edit Profiles',
                                component: CreateCustomer
                            },
                            {
                                path: 'details/*',
                                name: 'Profile Details',
                                component: CustomerDetail
                            },
                        ]
                    },
                    {
                        path: 'vehicles',
                        name: 'Vehicles',
                        redirect: '/customers/vehicles',
                        component:  {
                            render(c) {
                                return c('router-view')
                            },
                        },
                        children: [
                            {
                                path: '',
                                name: '',
                                component: Vehicles
                            },
                            {
                                path: 'details/*',
                                name: 'Vehicle Details',
                                component: VehicleDetails
                            }
                        ]
                    }

                ]
            },

            {
                path: '/dealers/create',
                name: 'Create Dealer',
                component: CreateCompany
            },
            {
                path: '/dealers/edit/*',
                name: 'Edit Dealer',
                component: CreateCompany
            },
            {
                path: '/dealers',
                name: 'All Dealers',
                component: Companies
            },
            {
                path: '/surveyors/create',
                name: 'Create Surveyor',
                component: CreateCompany
            },
            {
                path: '/surveyors/edit/*',
                name: 'Edit Surveyor',
                component: CreateCompany
            },
            {
                path: '/surveyors',
                name: 'All Surveyors',
                component: Companies
            },
            {
                path: '/insurers/create',
                name: 'Create Insurer',
                component: CreateCompany
            },
            {
                path: '/insurers/edit/*',
                name: 'Edit Insurer',
                component: CreateCompany
            },
            {
                path: '/insurers',
                name: 'All Insurers',
                component: Companies
            },
            {
                path: '/workshops/create',
                name: 'Create Workshop',
                component: CreateCompany
            },
            {
                path: '/workshops/edit/*',
                name: 'Edit Workshop',
                component: CreateCompany
            },
            {
                path: '/workshops',
                name: 'All Workshops',
                component: Companies
            },
            {
                path: '/warrantyPrices/create',
                name: 'Create Warranty Price',
                component: CreateWarrantyPrice
            },
            {
                path: '/warrantyPrices/edit/*',
                name: 'Edit Warranty Price',
                component: CreateWarrantyPrice
            },
            {
                path: '/warrantyPrices',
                name: 'All Warranty Prices',
                component: WarrantyPrices
            },
            {
                path: '/reports',
                name: 'Reports',
                component: Reports
            },
            {
                path: '/profile',
                name: 'Profile',
                component: Profile
            },
            {
                path: 'servicing',
                name: 'Servicing',
                redirect: 'servicing',
                component:  {
                    render(c) {
                        return c('router-view')
                    },
                },
                children: [
                    {
                        path: '',
                        name: '',
                        component: ServicingList
                    },
                    {
                        path: 'create',
                        name: 'Create Appointment',
                        component: CreateServicing
                    },
                    {
                        path: 'details/*',
                        name: 'Servicing Details',
                        component: ServicingDetails
                    },
                ]
            },
            {
                path: 'companyProfile',
                name: 'Company Profile',
                redirect: 'companyProfile',
                component:  {
                    render(c) {
                        return c('router-view')
                    },
                },
                children: [
                    {
                        path: '',
                        name: 'Show Details',
                        component: CompanyProfile
                    },
                    {
                        path: 'createServicingSlots',
                        name: 'Create Servicing Slots',
                        component: CreateServicingSlots
                    },
                    {
                        path: 'createServiceTypes',
                        name: 'Create Service',
                        component: CreateServiceTypes
                    },
                    {
                        path: 'editServiceTypes/*',
                        name: 'Edit Service Types',
                        component: CreateServiceTypes
                    },
                    {
                        path: 'createUser',
                        name: 'Create User',
                        component: CreateCompanyUser
                    }
                ]
            },
        ]
    },
];


export default routes