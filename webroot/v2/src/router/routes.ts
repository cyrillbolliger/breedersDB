import { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      {
        path: '',
        component: () => import('pages/Index.vue'),
      },
      {
        path: 'marks/select-form',
        component: () => import('pages/marks/SelectForm.vue')
      },
      {
        path: 'marks/set-meta',
        component: () => import('pages/marks/SetMeta.vue')
      },
      {
        path: 'marks/select-tree',
        component: () => import('pages/marks/SelectTree.vue')
      },
      {
        path: 'marks/mark-tree',
        component: () => import('pages/marks/MarkTree.vue')
      }
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/Error404.vue'),
  },
];

export default routes;
