/** When your routing table is too long, you can split it into small modules**/
import Layout from '@/layout';

const transactionRoutes = {
  path: '/transaction',
  component: Layout,
  redirect: '/transaction/',
  children: [
    /** transaction managements */
    {
      path: 'detail/:id(\\d+)',
      component: () => import('@/views/transactions/Detail'),
      name: 'TransactionDetail',
      meta: { title: 'transactionDetail', noCache: true, permissions: ['manage user'] },
      hidden: true,
    },
    {
      path: 'transaction',
      component: () => import('@/views/transactions/List'),
      name: 'Transaction',
      meta: { title: 'Transaction', icon: 'list', permissions: ['view menu transaction'] },
    },
  ],
};

export default transactionRoutes;
