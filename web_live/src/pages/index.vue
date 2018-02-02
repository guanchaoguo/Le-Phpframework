<template>
    <el-row class="home"><!-- 顶部导航块 -->
        <el-col :span="24" class="header">
            <el-col :span="3" class="logo" style="background: #29292C;">
                <img src="" class="image" width="20" height="20">
                <strong style="color: #E5E9F2;" v-text="'后台管理系统'"></strong>
            </el-col>
            <el-col :span="12" class="logoIcon">
                <el-tooltip class="item" effect="dark" content="隐藏/显示导航栏" placement="right">
                    <a href="javascript:;"><i class="el-icon-menu" title=""></i></a>
                </el-tooltip>

            </el-col>
            <el-col :span="6" class="meg">
                <el-col :span="6">
                    <el-badge :value="3" class="item">
                        <el-button size="small" v-text="'线下存款'"></el-button>
                    </el-badge>
                </el-col>
            </el-col>
            <el-col :span="2" class="user">
                <el-dropdown trigger="click" class="dropdown">
                    <a href="javascript:;" class="el-dropdown-link" v-text="'偷茄子的猫'">
                        <!--<img src="../../assets/xlz.png" class="image" width="15" height="15">-->
                        <i class="el-icon-caret-bottom el-icon--right"></i>
                    </a>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item v-text="'设置'"></el-dropdown-item>
                        <el-dropdown-item @click="logout" v-text="'退出'"></el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </el-col>
        </el-col><!--  顶部导航/消息提示 结束  -->

        <el-col :span="24" class="main"><!--  主体内容块  -->
            <el-col :span="3" class="navLeft"> <!--  左侧导航栏  -->
                <el-menu default-active="/index" class="el-menu-vertical-demo navCont" theme="dark" router unique-opened>
                    <el-menu-item index="/index"><i :class="$router.options.routes[1].iconCls"></i><span v-text="$router.options.routes[1].name"></span>
                    </el-menu-item>
                    <template v-for="(item,index) in $router.options.routes" v-if="!item.hidden">
                        <el-submenu :index="index+''" v-if="item.name != '首页'">
                            <template slot="title" ><i :class="item.iconCls"></i><span v-text="item.name"></span></template>
                            <el-menu-item v-for="child in item.children" :index="child.path" class="child">
                                <i :class="child.iconCls"></i><span v-text="child.name"></span>
                            </el-menu-item>
                        </el-submenu>
                    </template>
                </el-menu>
            </el-col>
            <el-col :span="21" class="mainRight"><!--  右侧内容块  -->
                <el-col class="breadcrumb" :span="24"><!-- 面包屑导航块 -->
                    <el-breadcrumb separator="=>" class="Breadcrumb">
                        <el-breadcrumb-item :to="{ path: '/echarts' }">当前位置 :</el-breadcrumb-item>
                        <el-breadcrumb-item v-if="currentPathNameParent!=''">{{currentPathNameParent}}
                        </el-breadcrumb-item>
                        <el-breadcrumb-item v-if="currentPathName!=''">{{currentPathName}}</el-breadcrumb-item>
                    </el-breadcrumb>
                </el-col>
                <el-col class="navTitle" :span="24"> <!-- 导航标题 -->
                    <strong style="color: #45b1ed;"> | </strong><span>{{currentPathName}}</span>
                </el-col>
                <el-col :span="24" style="box-sizing: border-box;"><!-- router页面 -->
                    <transition name="fade" >
                        <router-view></router-view>
                    </transition>
                </el-col>
            </el-col>
        </el-col>
    </el-row>
</template>
<script>
    //import headSeach from '../../components/headSeach';
    import $ from 'jquery'
    export default {
        data() {
            return {
                currentPathName: '首页',
                currentPathNameParent: '',
                dialogVisible: false,
                tableData: [{
                    date: '2016-05-02',

                    address: ' 1.恭喜你中奖88888!!'
                }, {
                    date: '2016-05-04',

                    address: '2.恭喜你中奖88888!!'
                }, {
                    date: '2016-05-01',

                    address: '3.恭喜你中奖88888!!'
                }, {
                    date: '2016-05-03',

                    address: '4.恭喜你中奖88888!!'
                }],

            }
        },
        watch: {

            '$route' (to, from) {//监听路由改变
                this.currentPathName = to.name;
                this.currentPathNameParent = to.matched[0].name;
            }
        },
        methods: {
            //隐藏菜单
            hideMenu(){

            },
            //退出登录
            logout: function () {
                var _this = this;
                this.$confirm('确认退出吗?', '提示', {
                    //type: 'warning'
                }).then(() => {
                    _this.$router.replace('/login');
                }).catch(() => {

                });
            }
        },
        mounted(){
            $(function () {
                (function () {
                    var i = true;
                    var oNL = $('.navLeft').width();
                    console.log('5');
                    function selfAdaption(oNL) {
                        var oMinW = $('.main').width();
                        var oWid = oMinW - oNL;
                        $('.mainRight').css('width', oWid);
                        console.log(oMinW);
                    };
                    selfAdaption(oNL);
                    window.onresize = function () {
                        selfAdaption(oNL);
                    };
                    $('.logoIcon').find('a').click(function () {
                        var oNav = $('.navCont');
                        var oChild = $('.child');
                        console.log(oChild)
                        if (i) {
                            oNav.add(oNL).animate({width: '54px'});
                            oChild.css('padding-left', '20px');
                            $('.el-submenu .el-menu-item').css('padding-left', '20px');
                            $('.mainRight').animate({left: '54'});
                            $('.is-opened ,.el-icon-arrow-down').hide();
                            oNav.find('span').hide();
                            oNL = 54;
                            console.log(i);
                            selfAdaption(oNL);
                            i = false;
                        } else {
                            oNav.add(oNL).animate({width: '230px'});
                            oChild.css('padding-left', '40px');
                            $('.el-submenu .el-menu-item').css('padding', '0 45px');
                            $('.mainRight').animate({left: '230'});
                            $('.is-opened ,.el-icon-arrow-down').show(100);
                            oNav.find('span').show();
                            oNL = 230;
                            selfAdaption(oNL);
                            console.log(i);
                            i = true;
                        }
                    });
//                    $('.navCont').find('.el-menu-item,.el-submenu__title').hover(function () {
//                        i && $(this).css('width', '230px') || $(this).css({'z-index': '2'}).animate({width: '230px'}).find('span').show();
//                    }, function () {
//                        i && $(this).css('width', '230px') || $(this).animate({width: '54px'}).find('span').hide();
//                    });
                })();
                (function () {
                    //setInterval((function () {
                      //  var oTimer = new Date().toLocaleString() + ' 星期' + '日一二三四五六'.charAt(new Date().getDay())
                        //debugger;
//                        $('.timer').text(new Date().toLocaleString()+' 星期'+'日一二三四五六'.charAt(new Date().getDay()))
                      //  $('.timer').text(oTimer);
                       /// console.log(oTimer);
                    //}), 1000);

//                    setInterval("newTime.innerHTML=new Date().toLocaleString()+' 星期'+'日一二三四五六'.charAt(new Date().getDay());",1000);
                })();
            });
        }

    }
</script>

<style>
    
    .home {
        margin: 0 auto;
    }
    
    .home .header {
        height: 60px;
        line-height: 60px;
        min-width: 1380px;
        background: #29292C;
    }

    .home .header .user .dropdown {
        background: #E5E9F2;
        line-height: 58px;
        padding: 0 5px;
        border-radius: 5px;
        /*filter:alpha(opacity=50);*/
        /*-moz-opacity:0.5;*/
        /*-khtml-opacity: 0.5;*/
        /*opacity: 0.5;*/
    }

    .home .header .logo {
        max-width: 230px;
        background: #3573a5;
    }

    .home .header .image {
        vertical-align: middle;
        margin-left: 10px;
    }

    .home .header .logoIcon {
        text-align: left;
    }

    .home .header .logoIcon a {
        color: #E5E9F2;
    }

    .home .main {
        min-width: 1380px;
        background: #E5E9F2;
        height: 800px;
    }

    .home .main .navLeft {
        max-width: 230px;
        height: auto;
        overflow: hidden;
    }

    /*.home .main .child{overflow: hidden;}*/
    .home .main .navCont {
        min-height: 860px;
    }

    .home .main .mainRight {
        min-width: 1150px;
        position: absolute;
        left: 230px;
        top: 60px;
        padding: 3px 15px;
    }

    .home .main .mainRight .navTitle, .home .main .mainRight .breadcrumb {
        padding: 3px 10px;
    }

    .home .main .mainRight .navTitle {
        text-align: left;
        line-height: 24px;
        background: #C0CCDA;
        font-weight: 500;
        border-radius: 6px;
        margin-top: 10px;
        margin-bottom: 20px;
    }
</style>