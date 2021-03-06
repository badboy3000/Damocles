import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

export default new Router({
  mode: 'hash',
  scrollBehavior: () => ({ y: 0 }),
  routes: [
    {
      path: '/',
      name: '首页',
      component: require('view/index').default
    },
    {
      path: '/image/loop',
      name: '首页轮播',
      component: require('view/image/loop').default
    },
    {
      path: '/image/banner',
      name: 'banner 图',
      component: require('view/image/banner').default
    },
    {
      path: '/bangumi/list',
      name: '番剧列表',
      component: require('view/bangumi/list').default
    },
    {
      path: '/bangumi/tag',
      name: '番剧标签',
      component: require('view/bangumi/tag').default
    },
    {
      path: '/bangumi/create',
      name: '创建番剧',
      component: require('view/bangumi/show').default
    },
    {
      path: '/bangumi/cartoon',
      name: '漫画列表',
      component: require('view/bangumi/cartoon').default
    },
    {
      path: '/bangumi/cartoon/edit/:id(\\d+)',
      name: '编辑漫画',
      component: require('view/bangumi/editCartoon').default
    },
    {
      path: '/bangumi/edit/:id(\\d+)',
      name: '编辑番剧',
      component: require('view/bangumi/show').default
    },
    {
      path: '/video/list',
      name: '视频列表',
      component: require('view/video/list').default
    },
    {
      path: '/video/create',
      name: '新建视频',
      component: require('view/video/create').default
    },
    {
      path: '/cartoonRole/list',
      name: '角色列表',
      component: require('view/cartoonRole/list').default
    },
    {
      path: '/cartoonRole/create',
      name: '创建角色',
      component: require('view/cartoonRole/show').default
    },
    {
      path: '/cartoonRole/edit/:id(\\d+)',
      name: '编辑角色',
      component: require('view/cartoonRole/show').default
    },
    {
      path: '/trail/words',
      name: '高危词',
      component: require('view/trail/words').default
    },
    {
      path: '/trail/users',
      name: '用户审核',
      component: require('view/trail/users').default
    },
    {
      path: '/trail/posts',
      name: '帖子审核',
      component: require('view/trail/posts').default
    },
    {
      path: '/trail/images',
      name: '图片审核',
      component: require('view/trail/images').default
    },
    {
      path: '/trail/comments',
      name: '评论审核',
      component: require('view/trail/comments').default
    },
    {
      path: '/admin/user',
      name: '管理员',
      component: require('view/admin/user').default
    },
    {
      path: '/user/list',
      name: '用户列表',
      component: require('view/user/list').default
    },
    {
      path: '/user/fakers',
      name: '运营号',
      component: require('view/user/fakers').default
    },
    {
      path: '/user/feedback',
      name: '用户反馈',
      component: require('view/user/feedback').default
    }
  ]
})
