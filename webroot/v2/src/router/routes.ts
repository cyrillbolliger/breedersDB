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
        path: 'marks/:type/select-form',
        component: () => import('pages/marks/SelectForm.vue')
      },
      {
        path: 'marks/:type/set-meta',
        component: () => import('pages/marks/SetMeta.vue')
      },
      {
        path: 'marks/tree/select-tree',
        component: () => import('pages/marks/SelectTree.vue')
      },
      {
        path: 'marks/tree/mark-tree',
        component: () => import('pages/marks/MarkTree.vue')
      },
      {
        path: 'queries',
        component: () => import('pages/queries/QueriesIndex.vue')
      },
      {
        path: 'queries/:id',
        component: () => import('pages/queries/TheQuery.vue'),
        props: true,
      }
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/CustomError404.vue'),
  },
];

export default routes;
