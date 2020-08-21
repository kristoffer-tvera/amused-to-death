import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
import Home from '../views/Home.vue'
import RaidEditor from "../views/CreateRaid.vue";

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/raids',
    name: 'Raids',
    component: () => import(/* webpackChunkName: "raidEditor" */ '../views/Raids.vue'),
  },
  {
    path: '/raid',
    name: 'Raid',
    component: () => import(/* webpackChunkName: "raidEditor" */ './Raid.vue'),
    children: [
      {
        path: '/raid/:id',
        name: 'Raid',
        component: () => import(/* webpackChunkName: "raidConfig" */ '../views/Raid.vue')
      },
      {
        path: '/raid/create',
        name: 'RaidCreator',
        component: () => import(/* webpackChunkName: "raidConfig" */ '../components/RaidEditor.vue')
      },
      {
        path: '/raid/edit/:id',
        name: "RaidEditor",
        component: () => import(/* webpackChunkName: "raidConfig" */ '../components/RaidEditor.vue')
      }
    ]
  }

]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
