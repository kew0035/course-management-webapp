import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '../components/LoginPage.vue';

//  Dynamically import components
const RoleDashboard = (role) => () => import(`../components/${role}Dashboard.vue`);

// Permission Configuration
const authConfig = {
  public: ['/'],
  roles: {
    lecturer: '/lecturer',
    student: '/student',
    advisor: '/advisor',
    admin: '/admin'
  }
};

// Generate dynamic routes
const dynamicRoutes = Object.entries(authConfig.roles).map(([role, path]) => ({
  path,
  name: `${role}Dashboard`,
  component: RoleDashboard(role.charAt(0).toUpperCase() + role.slice(1)),
  meta: {
    requiresAuth: true,
    allowedRoles: [role]
  },
  props: (route) => ({ 
    name: route.query.name || JSON.parse(sessionStorage.getItem('userData'))?.name 
  })
}));

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: authConfig.public[0],
      name: 'Login',
      component: LoginPage
    },
    ...dynamicRoutes
  ]
});

router.beforeEach((to, from, next) => {
  const userData = JSON.parse(sessionStorage.getItem('userData'));
  
  // Direct release on public paths
  if (authConfig.public.includes(to.path)) {
    return next();
  }

  // Verify login status
  if (!userData) {
    return next({ path: '/' });
  }

  // Verify role permissions
  const requiredRoles = to.meta.allowedRoles;
  if (requiredRoles && !requiredRoles.includes(userData.role)) {
    return next({ path: getFallbackPath(userData.role) });
  }

  next();
});

// Get the role fallback path
function getFallbackPath(role) {
  return authConfig.roles[role] || '/';
}

export default router;