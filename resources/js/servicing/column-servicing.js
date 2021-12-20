export default [
    {
        label: 'Appt Date',
        name: 'format_appointment_date',
        columnName: 'appointment_date',
        orderable: true,
    },
    {
        label: 'Appt Time',
        name: 'format_appointment_time',
        columnName: 'appointment_date',
        orderable: true,
    },
    {
        label: 'Workshop',
        name: 'workshop.name',
        columnName: 'companies.name',
        orderable: true,
    },
    {
        label: 'Type of Service',
        name: 'service_type.name',
        columnName: 'service_types.name',
        orderable: true,
        component: 'TypeOfServiceCol'
    },
    {
        label: 'Customer',
        name: 'customer.name',
        columnName: 'customers.name',
        orderable: true,
    },
    {
        label: 'Phone',
        name: 'customer.phone',
        columnName: 'customers.phone',
        orderable: true,
    },
    {
        label: 'Car Plate',
        name: 'vehicle.registration_no',
        columnName: 'vehicles.registration_no',
        orderable: true,
    },
    {
        label: 'Car Make',
        name: 'vehicle.make',
        columnName: 'vehicles.make',
        orderable: true,
    },
    {
        label: 'Status',
        name: 'status',
        orderable: true,
        component: 'StatusCol',
    }
]
