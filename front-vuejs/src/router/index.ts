import Vue from 'vue'
import VueRouter from 'vue-router'
import { isAuthenticated } from '../services/auth'

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

  if (to.name === 'login' && isAuthenticated()) {
    next({ name: 'home' })
    return
  }

  next()
})

export default router
