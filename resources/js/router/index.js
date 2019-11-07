import Vue from 'vue';
import Router from 'vue-router';

import Cookies from 'js-cookie';

/**
 * Layzloading will create many files and slow on compiling, so best not to use lazyloading on devlopment.
 * The syntax is lazyloading, but we convert to proper require() with babel-plugin-syntax-dynamic-import
 * @see https://doc.laravue.dev/guide/advanced/lazy-loading.html
 */

Vue.use(Router);

/* Layout */
import Layout from '@/layout';

/* Router for modules */
import elementUiRoutes from './modules/element-ui';
import componentRoutes from './modules/components';
import chartsRoutes from './modules/charts';
import tableRoutes from './modules/table';
import adminRoutes from './modules/admin';
import nestedRoutes from './modules/nested';
import errorRoutes from './modules/error';
import excelRoutes from './modules/excel';
import permissionRoutes from './modules/permission';
import transactionRoutes from './modules/transaction';

/**
 * Sub-menu only appear when children.length>=1
 * @see https://doc.laravue.dev/guide/essentials/router-and-nav.html
 **/

/**
* hidden: true                   if `hidden:true` will not show in the sidebar(default is false)
* alwaysShow: true               if set true, will always show the root menu, whatever its child routes length
*                                if not set alwaysShow, only more than one route under the children
*                                it will becomes nested mode, otherwise not show the root menu
* redirect: noredirect           if `redirect:noredirect` will no redirect in the breadcrumb
* name:'router-name'             the name is used by <keep-alive> (must set!!!)
* meta : {
    roles: ['admin', 'editor']   Visible for these roles only
    permissions: ['view menu zip', 'manage user'] Visible for these permissions only
    title: 'title'               the name show in sub-menu and breadcrumb (recommend set)
    icon: 'svg-name'             the icon show in the sidebar
    noCache: true                if true, the page will no be cached(default is false)
    breadcrumb: false            if false, the item will hidden in breadcrumb (default is true)
    affix: true                  if true, the tag will affix in the tags-view
  }
**/
let Routes = [];

if (Cookies.get('Role') == 'admin'){
  Routes = [
    {
      path: '/redirect',
      component: Layout,
      hidden: true,
      children: [
        {
          path: '/redirect/:path*',
          component: () => import('@/views/redirect/index'),
        },
      ],
    },
    {
      path: '/login',
      component: () => import('@/views/login/index'),
      hidden: true,
    },
    {
      path: '/auth-redirect',
      component: () => import('@/views/login/AuthRedirect'),
      hidden: true,
    },
    {
      path: '/404',
      redirect: { name: 'Page404' },
      component: () => import('@/views/error-page/404'),
      hidden: true,
    },
    {
      path: '/401',
      component: () => import('@/views/error-page/401'),
      hidden: true,
    },
    {
      path: '',
      component: Layout,
      redirect: 'dashboard',
      children: [
        {
          path: 'dashboard',
          component: () => import('@/views/dashboard/index'),
          name: 'Dashboard',
          meta: { title: 'dashboard', icon: 'dashboard', noCache: false },
        },
      ],
    },
    transactionRoutes,
    {
      path: '/masterdata',
      component: Layout,
      redirect: '/masterdata/',
      name: 'MasterData',
      alwaysShow: true,
      meta: {
        title: 'Master Data',
        icon: 'admin',
        permissions: ['view menu masterdata'],
      },
      children: [
        /** User managements */
        {
          path: 'item',
          component: () => import('@/views/item/List'),
          name: 'itemlist',
          meta: { title: 'Item', icon: 'clipboard', permissions: ['manage item'] },
        },
        {
          path: 'unit',
          component: () => import('@/views/unit/List'),
          name: 'unitlist',
          meta: { title: 'Unit', icon: 'layout', permissions: ['manage unit'] },
        },
        {
          path: 'vendor',
          component: () => import('@/views/vendors/List'),
          name: 'vendorlist',
          meta: { title: 'Vendor', icon: 'create-user', permissions: ['manage vendor'] },
        },
      ],
    },
    adminRoutes,
    // {
    //   path: '/documentation',
    //   component: Layout,
    //   redirect: '/documentation/index',
    //   children: [
    //     {
    //       path: 'index',
    //       component: () => import('@/views/documentation/index'),
    //       name: 'Documentation',
    //       meta: { title: 'documentation', icon: 'documentation', noCache: true },
    //     },
    //   ],
    // },
    // {
    //   path: '/guide',
    //   component: Layout,
    //   redirect: '/guide/index',
    //   children: [
    //     {
    //       path: 'index',
    //       component: () => import('@/views/guide/index'),
    //       name: 'Guide',
    //       meta: { title: 'guide', icon: 'guide', noCache: true },
    //     },
    //   ],
    // },
    // elementUiRoutes,
  ];
} else {
  Routes = [
    // {
    //   path: '/redirect',
    //   component: Layout,
    //   hidden: true,
    //   children: [
    //     {
    //       path: '/redirect/:path*',
    //       component: () => import('@/views/redirect/index'),
    //     },
    //   ],
    // },
    {
      path: '/login',
      component: () => import('@/views/login/index'),
      hidden: true,
    },
    {
      path: '/auth-redirect',
      component: () => import('@/views/login/AuthRedirect'),
      hidden: true,
    },
    {
      path: '/404',
      redirect: { name: 'Page404' },
      component: () => import('@/views/error-page/404'),
      hidden: true,
    },
    {
      path: '/401',
      component: () => import('@/views/error-page/401'),
      hidden: true,
    },
    {
      path: '',
      component: Layout,
      redirect: 'dashboard',
      children: [
        {
          path: 'dashboard',
          component: () => import('@/views/dashboard/index'),
          name: 'Dashboard',
          meta: { title: 'dashboard', icon: 'dashboard', noCache: false },
        },
      ],
    },
    transactionRoutes,
  ];
}

export const constantRoutes = Routes;

export const asyncRoutes = [
  {
    path: '/pdf/transactionLUNAS/:vendor_id',
    component: () => import('@/views/dashboard/admin/pdf/TransactionLUNAS'),
    hidden: true,
  },
  {
    path: '/pdf/transactionBELUMLUNAS/:vendor_id',
    component: () => import('@/views/dashboard/admin/pdf/transactionBELUMLUNAS'),
    hidden: true,
  },
];

// export const asyncRoutes = [
//   permissionRoutes,
//   componentRoutes,
//   chartsRoutes,
//   nestedRoutes,
//   tableRoutes,
//   {
//     path: '/theme',
//     component: Layout,
//     redirect: 'noredirect',
//     children: [
//       {
//         path: 'index',
//         component: () => import('@/views/theme/index'),
//         name: 'Theme',
//         meta: { title: 'theme', icon: 'theme' },
//       },
//     ],
//   },
//   {
//     path: '/clipboard',
//     component: Layout,
//     redirect: 'noredirect',
//     meta: { permissions: ['view menu clipboard'] },
//     children: [
//       {
//         path: 'index',
//         component: () => import('@/views/clipboard/index'),
//         name: 'ClipboardDemo',
//         meta: { title: 'clipboardDemo', icon: 'clipboard', roles: ['admin', 'manager', 'editor', 'user'] },
//       },
//     ],
//   },
//   errorRoutes,
//   excelRoutes,
//   {
//     path: '/zip',
//     component: Layout,
//     redirect: '/zip/download',
//     alwaysShow: true,
//     meta: { title: 'zip', icon: 'zip', permissions: ['view menu zip'] },
//     children: [
//       {
//         path: 'download',
//         component: () => import('@/views/zip'),
//         name: 'ExportZip',
//         meta: { title: 'exportZip' },
//       },
//     ],
//   },
//   {
//     path: '/pdf',
//     component: Layout,
//     redirect: '/pdf/index',
//     meta: { title: 'pdf', icon: 'pdf', permissions: ['view menu pdf'] },
//     children: [
//       {
//         path: 'index',
//         component: () => import('@/views/pdf'),
//         name: 'Pdf',
//         meta: { title: 'pdf' },
//       },
//     ],
//   },
//   {
//     path: '/pdf/download',
//     component: () => import('@/views/pdf/Download'),
//     hidden: true,
//   },
//   {
//     path: '/pdf/transactionLUNAS/:vendor_id',
//     component: () => import('@/views/dashboard/admin/pdf/TransactionLUNAS'),
//     hidden: true,
//   },
//   {
//     path: '/pdf/transactionBELUMLUNAS/:vendor_id',
//     component: () => import('@/views/dashboard/admin/pdf/transactionBELUMLUNAS'),
//     hidden: true,
//   },
//   {
//     path: '/i18n',
//     component: Layout,
//     meta: { permissions: ['view menu i18n'] },
//     children: [
//       {
//         path: 'index',
//         component: () => import('@/views/i18n'),
//         name: 'I18n',
//         meta: { title: 'i18n', icon: 'international' },
//       },
//     ],
//   },
//   {
//     path: 'external-link',
//     component: Layout,
//     children: [
//       {
//         path: 'https://github.com/tuandm/laravue',
//         meta: { title: 'externalLink', icon: 'link' },
//       },
//     ],
//   },
//   { path: '*', redirect: '/404', hidden: true },
// ];

const createRouter = () => new Router({
  // mode: 'history', // require service support
  scrollBehavior: () => ({ y: 0 }),
  routes: constantRoutes,
});

const router = createRouter();

// Detail see: https://github.com/vuejs/vue-router/issues/1234#issuecomment-357941465
export function resetRouter() {
  const newRouter = createRouter();
  Routes = [];
  router.matcher = newRouter.matcher; // reset router
}

export default router;
