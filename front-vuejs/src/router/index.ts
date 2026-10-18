import Vue from 'vue'
import VueRouter from 'vue-router'
import { isAdmin, isAuthenticated } from '../services/auth'

Vue.use(VueRouter)

const router = new VueRouter({
  mode: 'history',
  base: import.meta.env.BASE_URL,
  routes: [
    {
      path: '/',
      name: 'login',
      component: () => import('../views/LoginView.vue')
    },
    {
      path: '/home',
      name: 'home',
      component: () => import('../views/HomeView.vue')
    },
    {
      path: '/patients',
      name: 'patients',
      component: () => import('../views/PatientsView.vue')
    },
    {
      path: '/patients/create',
      name: 'patient-create',
      component: () => import('../views/PatientCreateView.vue')
    },
    {
      path: '/patients/:id',
      name: 'patient-edit',
      component: () => import('../views/PatientCreateView.vue')
    },
    {
      path: '/users',
      name: 'users',
      component: () => import('../views/UsersView.vue'),
      meta: { requiresAdmin: true }
    },
    {
      path: '/users/create',
      name: 'user-create',
      component: () => import('../views/UserFormView.vue'),
      meta: { requiresAdmin: true }
    },
    {
      path: '/users/:id',
      name: 'user-edit',
      component: () => import('../views/UserFormView.vue'),
      meta: { requiresAdmin: true }
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue')
    },
    {
      path: '*',
      redirect: '/'
    }
  ]
})

router.beforeEach((to, _from, next) => {
  if (to.name !== 'login' && !isAuthenticated()) {
    next({ name: 'login' })
    return
  }

  if (to.matched.some((record) => record.meta.requiresAdmin) && !isAdmin()) {
    next({ name: 'home' })
    return
  }

  if (to.name === 'login' && isAuthenticated()) {
    next({ name: 'home' })
    return
  }

  next()
})

export default router
