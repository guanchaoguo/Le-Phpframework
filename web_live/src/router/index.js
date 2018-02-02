import Vue from 'vue'
import Router from 'vue-router'
import Login from 'pages/login'
import Index from 'pages/index'
import AgentM from 'pages/accountManage/agentManager'
import MainhallM from 'pages/accountManage/mainhallManager'
import PlayerM from 'pages/accountManage/playerManager'
import GameL from 'pages/gameManage/gamelimit'
import GameList from 'pages/gameManage/gameList'
import OrderS from 'pages/gameManage/ordersearch'
import SearchG from 'pages/formSystem/searchGame'
import langS from 'pages/systemManage/langset'
import menuM from 'pages/systemManage/menuManage'
import roleM from 'pages/systemManage/roleManage'
import echartS from 'pages/echarts/echart'
Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/Login',
      name: 'Login',
      component: Login,
      hidden: true
    },
    {
      path: '/Index',
      name: '首页',
      component: Index,
      iconCls: 'el-icon-menu'//图标样式class,
      // children:[{
      //       path: '/index',
      //       component: echartS,
      //       name: '首 页',
      //       iconCls: 'el-icon-menu',//图标样式class,
      //   }]
    },
    {
        path: 'accountManage',
        component: Index,
        name: '账户管理',
        iconCls: 'el-icon-menu',//图标样式class
        children: [
            { path: '/MainhallM', component:MainhallM, iconCls: 'fa fa-user-o', name: '厅主管理'},
            { path: '/AgentM', component: AgentM, iconCls: 'fa fa-user-o', name: '代理商管理' },
            { path: '/PlayerM', component: PlayerM, iconCls: 'fa fa-user-o', name: '玩家管理' }
        ]
    },
    {
        path: 'gameManage',
        component: Index,
        name: '游戏管理',
        iconCls: 'fa fa-cog',//图标样式class
        children: [
            { path: '/GameList', component:GameList, iconCls: 'fa fa-user-o', name: '游戏列表'},
            { path: '/GameL', component: GameL, iconCls: 'fa fa-user-o', name: '游戏限额-厅限额' },
            { path: '/OrderS', component: OrderS, iconCls: 'fa fa-user-o', name: '注单查询' }
        ]
    },
    {
        path: 'formSystem',
        component: Index,
        name: '报表统计',
        iconCls: 'fa fa-cog',//图标样式class
        children: [
            { path: '/SearchG', component:SearchG, iconCls: 'fa fa-user-o', name: '游戏查询'}
        ]
    },
    {
        path: 'systemManage',
        component: Index,
        name: '系统管理',
        iconCls: 'fa fa-cog',//图标样式class
        children: [
            { path: '/menuM', component: menuM, iconCls: 'fa fa-user-o', name: '菜单管理'},
            { path: '/roleM', component: roleM, iconCls: 'fa fa-user-o', name: '角色管理' },
            { path: '/langS', component: langS, iconCls: 'fa fa-user-o', name: '语言设置' }
        ]
    }
  ]
})
