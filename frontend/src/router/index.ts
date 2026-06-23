/**
 * router/index.ts
 *
 * Manual routes for ./src/views/*.vue
 */

// Composables
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/LoginView/LoginView.vue'),
      meta: { public: true },
    },
    {
      path: '/',
      redirect: '/dashboard',
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/DashboardView/DashboardView.vue'),
    },
    {
      path: '/reports',
      name: 'reports',
      component: () => import('@/views/ReportsView/ReportsView.vue'),
    },
    {
      path: '/users',
      name: 'users',
      component: () => import('@/views/users/UsersView/UsersView.vue'),
      meta: { adminOnly: true },
    },
    {
      path: '/users/new',
      name: 'users.new',
      component: () => import('@/views/users/UserFormView/UserFormView.vue'),
      meta: { adminOnly: true },
    },
    {
      path: '/users/:id/edit',
      name: 'users.edit',
      component: () => import('@/views/users/UserFormView/UserFormView.vue'),
      props: true,
      meta: { adminOnly: true },
    },
    {
      path: '/products',
      name: 'products',
      component: () => import('@/views/products/ProductsView/ProductsView.vue'),
    },
    {
      path: '/products/new',
      name: 'products.new',
      component: () => import('@/views/products/ProductFormView/ProductFormView.vue'),
      meta: { adminOnly: true },
    },
    {
      path: '/products/:id/edit',
      name: 'products.edit',
      component: () => import('@/views/products/ProductFormView/ProductFormView.vue'),
      props: true,
      meta: { adminOnly: true },
    },
  ],
})

router.beforeEach(to => {
  const auth = useAuthStore()

  if (!to.meta.public && !auth.isAuthenticated) {
    return { name: 'login' }
  }

  if (to.name === 'login' && auth.isAuthenticated) {
    return { path: '/' }
  }

  if (to.meta.adminOnly && !auth.isAdmin) {
    return { path: '/dashboard' }
  }
})

export default router
