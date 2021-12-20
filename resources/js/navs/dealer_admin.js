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
              name: 'Create Warranty',
              to: '/warranties/create'
            },
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
              name: 'Create Motor',
              to: '/motors/create'
            },
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
              to: '/vehicles'
            }
          ]
        },
        {
          _name: 'CSidebarNavItem',
          name: 'Users',
          icon: 'cil-user',
          to: '/users',
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