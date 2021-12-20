export default [
    {
        label: "CI No",
        name: "ci_no",
        orderable: true,
    },
    {
        label: "Policy No",
        name: "vehicle.motor.policy_no",
        orderable: false,
    },
    {
        label: "Issuer",
        name: "insurer.name",
        columnName: 'companies.name',
        orderable: true,
    },
    {
        label: "Account No",
        name: "insurer.code",
        columnName: 'companies.code',
        orderable: true,
    },
    {
        label: "Endorsement applicable",
        name: "customer_id",
        orderable: false,
        transform: ({data, name}) => `Not Applicable`
    },
    {
        label: "Issue Date",
        name: "created_at",
        orderable: true,
    },
    {
        label: "Warranty Effective Date*",
        name: "end_date",
        orderable: false,
    },
    {
        label: "Warranty Expiry Date*",
        name: "end_date",
        orderable: false,
    },
    {
        label: "Owner's Name",
        name: "proposer.name",
        columnName: "proposers.name",
        orderable: true,
    },
    {
        label: "Vehicle Registration Number*",
        name: "vehicle.registration_no",
        columnName: "vehicles.registration_no",
        orderable: true,
    },
    {
        label: "Make",
        name: "vehicle.make",
        columnName: "vehicles.make",
        orderable: true,
    },
    {
        label: "Model",
        name: "vehicle.model",
        columnName: "vehicles.model",
        orderable: true,
    },
    {
        label: "Mileage Cover",
        name: "mileage_coverage",
        orderable: true,
    },
    {
        label: "Maximum claim limit per year",
        name: "max_claim",
        orderable: true,
    },
    {
        label: "Engine Number",
        name: "vehicle.engine_no",
        columnName: "vehicles.engine_no",
        orderable: true,
    },
    {
        label: "Chassis Number",
        name: "vehicle.chassis_no",
        columnName: "vehicles.chassis_no",
        orderable: true,
    },
    {
        label: "Vehicle Capacity",
        name: "vehicle.capacity",
        columnName: "vehicles.capacity",
        orderable: true,
    },
    {
        label: "Year of Manufacture",
        name: "vehicle.manufacture_year",
        columnName: "vehicles.manufacture_year",
        orderable: true,
    },
    {
        label: "Vehicle Registration Date",
        name: "vehicle.registration_date",
        columnName: "vehicles.registration_date",
        orderable: true,
    },
    {
        label: "Numbers of Warranty period*",
        name: "warranty_duration",
        orderable: true
    },
    {
        label: "Warranty commencement mileage",
        name: "mileage",
        orderable: true
    },
    {
        label: "Coverage Premium",
        name: "format_premium_per_year",
        orderable: false
    },
    {
        label: "Dealer's Name",
        name: "dealer.name",
        columnName: "companies.name",
        orderable: true
    },
    {
        label: "Dealer's Address",
        name: "dealer.address",
        columnName: "companies.address",
        orderable: true
    },
]