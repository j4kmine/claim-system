export default [
    {
        label: "Date",
        name: "created_at",
        orderable: true,
    },
    {
        label: "Type",
        name: "driver.occupations",
        columnName: 'drivers.occupations',
        orderable: false,
    },
    {
        label: "Dealer*",
        name: "dealer.name",
        columnName: 'companies.name',
        orderable: true,
    },
    {
        label: "Name*",
        name: "driver.name",
        columnName: 'drivers.name',
        orderable: false,
    },
    {
        label: "Vehicle No",
        name: "vehicle.registration_no",
        columnName: "vehicles.registration_no",
        orderable: true,
    },
    {
        label: "Policy No*",
        name: "policy_no",
        orderable: true,
    },
    {
        label: "Policy Expiry Date",
        name: "format_expiry_date",
        columnName: 'expiry_date',
        orderable: true,
    },
    {
        label: "Contact No.",
        name: "driver.contact_number",
        columnName: 'drivers.contact_number',
        orderable: false,
    },
    {
        label: "Email Address",
        name: "driver.email",
        columnName: 'drivers.email',
        orderable: false,
    },
    {
        label: "Insurer",
        name: "insurer.name",
        columnName: 'companies.name',
        orderable: true,
    },
    {
        label: "Current NCD",
        name: "driver.ncd",
        columnName: 'drivers.ncd',
        orderable: false,
    },
    {
        label: "Type of Cover & Plan",
        name: "vehicle.chassis_no",
        columnName: 'vehicles.chassis_no',
        orderable: true,
    },
    {
        label: "Payment Date",
        name: "vehicle.capacity",
        columnName: 'vehicles.capacity',
        orderable: true,
        transform: () => `-`
    },
    {
        label: "Premium",
        name: "price",
        orderable: true,
    },
    {
        label: "GST",
        name: "point",
        orderable: false,
        transform: ({data}) => parseFloat((data['price'] * 0.07)).toFixed(2)
    },
    {
        label: "Total (incl GST)",
        name: "ref_no",
        orderable: false,
        transform: ({data}) => (parseFloat(data['price']) + parseFloat((data['price'] * 0.07))).toFixed(2)
    },
    {
        label: "Date of Birth*",
        name: "driver.dob",
        columnName: "drivers.dob",
        orderable: false
    },
    {
        label: "Vehicle Type",
        name: "vehicle.type",
        columnName: 'vehicles.type',
        orderable: true
    },
    {
        label: "First Registration Date",
        name: "format_start_date",
        columnName: 'start_date',
        orderable: true
    },
    {
        label: "Open Market Value",
        name: "dealer.name",
        orderable: false,
        transform: () => `-`
    },
    {
        label: "Propellant",
        name: "dealer.address",
        orderable: false,
        transform: () => `-`
    },
    {
        label: "Road Tax Expiry Date",
        name: "vehicle.tax_expiry_date",
        columnName: 'vehicles.tax_expiry_date',
        orderable: true
    },
]