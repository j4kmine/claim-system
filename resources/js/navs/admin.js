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
          _name: 'CSidebarNavDropdown',
          name: 'Warranty Insurance',
          icon: 'cil-folder',
          _attrs: { id: 'warranties-tab' },
          items: [
            {
              name: 'All Warranty Orders',
              to: '/warranties'
            },
            {
              name: 'Warranty Drafts',
              to: '/warranties/drafts'
            },
            {
              name: 'Warranty Draft Archive',
              to: '/warranties/archives'
            }
          ]
        },
        {
          _name: 'CSidebarNavDropdown',
          name: 'Motor Insurance',
          icon: 'cil-folder',
          _attrs: { id: 'motors-tab' },
          items: [
            {
              name: 'All Motor Orders',
              to: '/motors'
            },
            {
              name: 'Motor Drafts',
              to: '/motors/drafts'
            },
            {
              name: 'Motor Draft Archive',
              to: '/motors/archives'
            }
          ]
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
          name: 'Claims',
          icon: 'cil-folder',
          _attrs: { id: 'claims-tab' },
          items: [
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
          _name: 'CSidebarNavDropdown',
          name: 'Customers',
          icon: 'cil-user',
          _attrs: { id: 'claims-tab' },
          items: [
            {
              name: 'Profiles',
              to: '/customers/profiles'
            },
            {
              name: 'Vehicles',
              to: '/customers/vehicles'
            }
          ]
        },
        {
          _name: 'CSidebarNavDropdown',
          name: 'Partners',
          icon: 'cil-people',
          _attrs: { id: 'partners-tab' },
          items: [
            {
              name: 'Users',
              to: '/users',
            },
            {
              name: 'Dealers',
              to: '/dealers'
            },
            {
              name: 'Insurers',
              to: '/insurers'
            },
            {
              name: 'Surveyors',
              to: '/surveyors'
            },
            {
              name: 'Workshops',
              to: '/workshops'
            }
          ]
        },
        {
          _name: 'CSidebarNavItem',
          name: 'Warranty Prices',
          to: '/warrantyPrices',
          _attrs: { id: 'prices-tab' },
          icon: 'cil-money'
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