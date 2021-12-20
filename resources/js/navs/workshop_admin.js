export default [
  {
    _name: 'CSidebarNav',
    _children: [
      {
        _name: 'CSidebarNavItem',
        name: 'Dashboard',
        to: '/dashboard',
        icon: 'cil-home'
        /*
        badge: {
          color: 'primary',
          text: 'NEW'
        }*/
      },
      {
        _name: 'CSidebarNavItem',
        name: 'Accident Reporting',
        to: '/accidentReport/list',
        icon: 'cil-notes'
      },
      {
        _name: 'CSidebarNavItem',
        name: 'Servicing',
        to: '/servicing',
        icon: 'cil-garage'
      },
      {
        _name: 'CSidebarNavDropdown',
        name: 'Warranty Claims',
        icon: 'cil-folder',
        _attrs: { id: 'claims-tab' },
        items: [
          {
            name: 'Create Claim',
            to: '/claims/create'
          },
          {
            name: 'All Claims',
            to: '/claims'
          },
          {
            name: 'Claim Drafts',
            to: '/claims/drafts'
          },
          {
            name: 'Claim Draft Archive',
            to: '/claims/archives'
          }
        ]
      },
      {
        _name: 'CSidebarNavItem',
        name: 'Customers',
        icon: 'cil-user',
        to: '/customers/profiles',
      },
      {
        _name: 'CSidebarNavItem',
        name: 'Company Profile',
        icon: 'cil-people',
        to: '/companyProfile',
      },
      {
        _name: 'CSidebarNavItem',
        name: 'Reports',
        to: '/reports',
        icon: 'cil-chart-pie'
      },
      {
        _name: 'CSidebarNavItem',
        name: 'Profile',
        to: '/profile',
        icon: 'cil-address-book'
      },
      {
        _name: 'CSidebarNavItem',
        name: 'Logout',
        to: '/logout',
        icon: 'cil-account-logout'
      }
    ]
  }
]