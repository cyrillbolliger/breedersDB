import {RouteRecordRaw} from 'vue-router';

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
        path: 'marks/:type',
        component: () => import('pages/marks/Layout.vue'),
        children: [
          {
            path: 'select-form',
            component: () => import('pages/marks/SelectForm.vue')
          },
          {
            path: 'set-meta',
            component: () => import('pages/marks/SetMeta.vue')
          },
          {
            path: 'select-tree',
            component: () => import('pages/marks/SelectTree.vue')
          },
          {
            path: 'select-variety',
            component: () => import('pages/marks/SelectVariety.vue')
          },
          {
            path: 'select-batch',
            component: () => import('pages/marks/SelectBatch.vue')
          },
          {
            path: 'mark-tree',
            component: () => import('pages/marks/MarkTree.vue')
          },
          {
            path: 'mark-variety',
            component: () => import('pages/marks/MarkVariety.vue')
          },
          {
            path: 'mark-batch',
            component: () => import('pages/marks/MarkBatch.vue')
          },
        ],
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
