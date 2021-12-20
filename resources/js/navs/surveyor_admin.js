export default [
  {
    _name: 'CSidebarNav',
    _children: [
      {
        _name: 'CSidebarNavItem',
        name: 'Dashboard',
        to: '/dashboard',
        icon: 'cil-home'
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
        ]
      },
      {
        _name: 'CSidebarNavItem',
        name: 'Company Profile',
        icon: 'cil-people',
        to: '/companyProfile',
      },
      {
        _name: 'CSidebarNavItem',
        name: 'Users',
        icon: 'cil-user',
        to: '/users',
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