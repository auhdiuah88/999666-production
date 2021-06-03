<!DOCTYPE html>
<html ng-app="main" lang="zh-CN">
<head>
    <meta charset="UTF-8">

    <title></title>
    <!-- <link rel="stylesheet" href=""> -->

    <!--[if IE 9]>
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/ie.css">
    <![endif]-->
    <link href="{{asset('static/css/21ea59.app.css')}}" rel="stylesheet"></head>
<body>
<!--[if lte IE 9]><h1>你正在使用的是IE9版本以下的浏览器，建议更换成Chrome内核或者IE9及更高版本的浏览器</h1><![endif]-->
<!--
                   _ooOoo_
                  o8888888o
                  88" . "88
                  (| -_- |)
                  O\  =  /O
               ____/`---'\____
             .'  \\|     |//  `.
            /  \\|||  :  |||//  \
           /  _||||| -:- |||||-  \
           |   | \\\  -  /// |   |
           | \_|  ''\---/''  |   |
           \  .-\__  `-`  ___/-. /
         ___`. .'  /--.--\  `. . __
      ."" '<  `.___\_<|>_/___.'  >'"".
     | | :  `- \`.;`\ _ /`;.`/ - ` : | |
     \  \ `-.   \_ __\ /__ _/   .-` /  /
======`-.____`-.___\_____/___.-`____.-'======
                   `=---='

^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
           佛祖保佑       永无BUG
           心外无法       法外无心
-->
<ui-view style="" class="ng-scope"><!-- ngInclude: TPL_ROOT + 'member_head/member_head.tpl.html' --><div ng-include="TPL_ROOT + 'member_head/member_head.tpl.html'" class="ng-scope"><div class="header ng-scope">
            <div class="main main_btm_border">
                <div class="left logo" style="min-height: 68px; max-height: 110px;">
                    <a ng-href="/" class="logo-link" id="" href="/">
                        <img width="227px" alt="" ng-src="https://www.huanrezhijia.com/uploads/1/20201213/477696589588991fb4c16c982ecd2d83.png" src="https://www.huanrezhijia.com/uploads/1/20201213/477696589588991fb4c16c982ecd2d83.png">
                    </a>
                </div>

                <div class="right login_and_userinfo ng-scope" ng-if="!isLogin">
                    {{--                    <div class="login">--}}
                    {{--                        <form method="POST" id="login_form" class="ng-pristine ng-valid">--}}
                    {{--                            <div class="username left">--}}
                    {{--                                <i class="iconfont icon-mail"></i>--}}
                    {{--                                <input type="text" ng-model="data.username" placeholder="用户名" name="user_name" id="username" autocomplete="off" class="ng-pristine ng-untouched ng-valid ng-not-empty">--}}
                    {{--                            </div>--}}
                    {{--                            <div class="password left">--}}
                    {{--                                <div class="password left">--}}
                    {{--                                    <i class="iconfont icon-mima"></i>--}}
                    {{--                                    <input type="password" placeholder="密码" ng-model="pwd.pwd" autocomplete="off" class="ng-pristine ng-untouched ng-valid ng-empty">--}}
                    {{--                                </div>--}}
                    {{--                                <!-- ngIf: data.isCode -->--}}
                    {{--                                <div class="login-btn-right">--}}
                    {{--                                    <button type="button" class="left btn-default login-btn btn" name="login" ng-click="login()">登录</button>--}}

                    {{--                                    <button type="button" class="left d-btn reg-btn btn" name="register" id="register" ng-click="goPath('register')">注册</button>--}}
                    {{--                                    <!-- ngIf: is_open_guest == 1 -->--}}
                    {{--                                </div>--}}
                    {{--                        </form>--}}
                    {{--                    </div>--}}
                    <div class="logined">
                        <em>您好，</em>
                        <span class="username"><a href="/member/user_center" class="ng-binding">qwe123ert</a></span>
                        <em>可用余额:</em>
                        <span class="money ng-binding ng-scope" ng-if="isMoneyShow">1000000</span>
                        <span class="eye_open ng-scope" ng-click="showMoney()" ng-if="isMoneyShow"></span>
                        <a href="javascript:;" ng-click="refresh()" style="margin-left: 30px">刷新</a>
                        <a ng-href="/member/user_center" target="_blank" ng-click="checkStatus('会员中心')" href="/member/user_center"><span class="newMsg ng-scope" ng-if="showMsgFlag"></span><!-- end ngIf: showMsgFlag -->会员中心</a>
                        <a ng-click="checkStatus('充值')" style="color: #E94335;" target="_blank">充值</a>
                        <a ng-click="checkStatus('提现')" target="_blank">提现</a>
                        <a ng-href="/member/member_transaction" target="_blank" href="/member/member_transaction">投注记录</a>
                        <a ng-click="logout()">退出</a>
                    </div>
                </div>
            </div>
        </div></div>
    <!-- 中心内容 -->
    <div class="main usercenter ng-scope">
        <!--主体开始-->
        <div class="user_usersafe">
            <!-- 左侧菜单 -->
{{--            <div ng-include="TPL_ROOT + 'member_nav/member_nav.tpl.html'" class="ng-scope">--}}
{{--                <div class="user_menu left ng-scope" ng-controller="Member_navCtrl">--}}
{{--                    <h2>会员中心</h2>--}}
{{--                    <ul>--}}
{{--                        <li ng-class="{hover: path == '/member/user_center'}" class="">--}}
{{--                            <i class="iconfont"></i>--}}
{{--                            <a ng-href="/member/user_center" href="/member/user_center">个人中心</a>--}}
{{--                        </li>--}}
{{--                        <li ng-class="{hover: path == '/member/member_top_up' || path == '/member/member_withdraw' || path == '/member/member_top_up_record' || path == '/member/member_withdraw_record'}" class="">--}}
{{--                            <i class="iconfont"></i>--}}
{{--                            <a ng-click="checkStatus()">财务中心</a>--}}
{{--                        </li>--}}
{{--                        <li ng-class="{hover: path == '/member/member_transaction'}" class="">--}}
{{--                            <i class="iconfont"></i>--}}
{{--                            <a ng-href="/member/member_transaction" href="/member/member_transaction">交易记录</a>--}}
{{--                        </li>--}}
{{--                        <li ng-class="{hover: path == '/member/member_detail_set'}" class="">--}}
{{--                            <i class="iconfont"></i>--}}
{{--                            <a ng-href="/member/member_detail_set" href="/member/member_detail_set">详细设定</a>--}}
{{--                        </li>--}}
{{--                        <li ng-class="{hover: path == '/member/member_notice'}">--}}
{{--                            <i class="iconfont"></i>--}}
{{--                            <a ng-href="/member/member_notice" href="/member/member_notice">信息公告</a>--}}
{{--                        </li>--}}
{{--                        <!-- <li class="agencyLi"--}}
{{--                             ng-if='status!="试玩"'--}}
{{--                            ng-class="{hover: path == '/member/new_agent_center' || path == '/member/new_agent_explain' || path == '/member/new_agent_next_report' || path == '/member/new_agent_next_account' || path == '/member/new_agent_management' || path == '/member/new_agent_devote' || path == '/member/new_agent_transaction' || path.indexOf('/member/new_agent_devote_detail') !== -1}">--}}
{{--                            <i class="icon-dl"></i>--}}
{{--                            <a ng-href="/member/new_agent_explain">代理中心</a>--}}
{{--                        </li> -->--}}
{{--                        <!-- ngIf: playerStatus!="试玩" &&isAgent=="true" --><li class="agencyLi ng-scope hover" ng-if="playerStatus!=&quot;试玩&quot; &amp;&amp;isAgent==&quot;true&quot;" ng-class="{hover: path == '/member/new_agent_center' || path == '/member/new_agent_explain' || path == '/member/new_agent_next_report' || path == '/member/new_agent_next_account' || path == '/member/new_agent_management' || path == '/member/new_agent_devote' || path == '/member/new_agent_transaction' || path.indexOf('/member/new_agent_devote_detail') !== -1}">--}}
{{--                            <i class="icon-dl"></i>--}}
{{--                            <a ng-href="/member/new_agent_explain" href="/member/new_agent_explain">代理中心</a>--}}
{{--                        </li><!-- end ngIf: playerStatus!="试玩" &&isAgent=="true" -->  <!--&& isAgent==true -->--}}
{{--                        <li ng-show="status" ng-class="{hover: path == '/member/member_mail'}" ng-click="toMember_mail()" class="">--}}
{{--                            <i class="icon-mail1"></i>--}}
{{--                            <a>站内信</a>--}}
{{--                            <!-- ngIf: showMsgFlag --><span class="newMsgs ng-scope" ng-if="showMsgFlag"></span><!-- end ngIf: showMsgFlag -->--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                    <!--<h2>代理中心</h2>-->--}}
{{--                    <!--<ul>-->--}}
{{--                    <!--<li ng-if="!isProxy" ng-class="{hover: path == '/member/agent_apply'}">-->--}}
{{--                    <!--<i class="iconfont">&#xe624;</i>-->--}}
{{--                    <!--<a ng-href="/member/agent_apply">代理申请</a>-->--}}
{{--                    <!--</li>-->--}}
{{--                    <!--<li ng-if="isProxy" ng-class="{hover: path == '/member/agent_info'}">-->--}}
{{--                    <!--<i class="iconfont">&#xe624;</i>-->--}}
{{--                    <!--<a ng-href="/member/agent_info">代理信息</a>-->--}}
{{--                    <!--</li>-->--}}
{{--                    <!--<li ng-if="isProxy" ng-class="{hover: path == '/member/agent_report'}">-->--}}
{{--                    <!--<i class="iconfont">&#xe626;</i>-->--}}
{{--                    <!--<a ng-href="/member/agent_report">代理报表</a>-->--}}
{{--                    <!--</li>-->--}}
{{--                    <!--<li ng-if="isProxy" ng-class="{hover: path == '/member/agent_user'}">-->--}}
{{--                    <!--<i class="iconfont">&#xe658;</i>-->--}}
{{--                    <!--<a ng-href="/member/agent_user">下级用户</a>-->--}}
{{--                    <!--</li>-->--}}
{{--                    <!--<li ng-if="isProxy" ng-class="{hover: path == '/member/agent_account_detail'}">-->--}}
{{--                    <!--<i class="iconfont">&#xe938;</i>-->--}}
{{--                    <!--<a ng-href="/member/agent_account_detail">账户明细</a>-->--}}
{{--                    <!--</li>-->--}}
{{--                    <!--<li ng-if="isProxy" ng-class="{hover: path == '/member/agent_bet_record'}">-->--}}
{{--                    <!--<i class="iconfont">&#xe6bf;</i>-->--}}
{{--                    <!--<a ng-href="/member/agent_bet_record">投注记录</a>-->--}}
{{--                    <!--</li>-->--}}
{{--                    <!--<li ng-if="isProxy" ng-class="{hover: path == '/member/agent_charge'}">-->--}}
{{--                    <!--<i class="iconfont">&#xe6bf;</i>-->--}}
{{--                    <!--<a ng-href="/member/agent_charge">代理佣金</a>-->--}}
{{--                    <!--</li>-->--}}
{{--                    <!--</ul>-->--}}
{{--                </div></div>--}}
            <ui-view class="ng-scope">
                <div class="user_right right ng-scope">
                    <div class="user_main">
                        <div class="user_header">
                            <div ng-include="TPL_ROOT + 'new_agent_nav/new_agent_nav.tpl.html'" class="ng-scope" >
                                <div class="user_header_nav ng-scope">
                                    <ul>
                                        <li ng-class="{hover: path=='{{url('ag/index')}}'}" @if($idx == 1) class="hover" @endif>
                                            <a href="{{url('ag/index')}}">代理说明</a>
                                        </li>
                                        <li ng-class="{hover: path=='{{url('ag/report')}}'}" @if($idx == 2) class="hover" @endif>
                                            <a href="{{url('ag/report')}}">代理报表</a>
                                        </li>
                                        <li ng-class="{hover: path=='{{url('ag/ag_report')}}'}" @if($idx == 3) class="hover" @endif>
                                            <a href="{{url('ag/ag_report')}}">下级报表</a>
                                        </li>
                                        <li ng-class="{hover: path=='{{url('ag/invite')}}'}" @if($idx == 4) class="hover" @endif>
                                            <a href="{{url('ag/invite')}}">下级开户</a>
                                        </li>
                                        <li ng-class="{hover: path=='{{url('ag/member')}}'}" @if($idx == 5) class="hover" @endif>
                                            <a href="{{url('ag/member')}}">会员管理</a>
                                        </li>
                                        <li ng-class="{hover: path=='{{url('ag/betting_records')}}'}" @if($idx == 6) class="hover" @endif>
                                            <a href="{{url('ag/betting_records')}}">投注记录</a>
                                        </li>
                                        <li ng-class="{hover: path=='{{url('ag/balance_log')}}'}" @if($idx == 7) class="hover" @endif>
                                            <a href="{{url('ag/balance_log')}}">交易明细</a>
                                        </li>
                                    </ul>
                                </div></div>
                        </div>
                        <div class="agent_body" style="padding-top: 0">
                            @yield('content')
                        </div>
                    </div>
                </div></ui-view>
        </div>

    </div>

</ui-view>
<script type="text/javascript" src="{{asset('static/js/7a0e9b.config.js')}}"></script>
<script type="text/javascript" src="{{asset('static/js/a4cc4a.vendor.js')}}"></script>
<script type="text/javascript" src="{{asset('static/js/21ea59.app.js')}}"></script>
</body>
</html>

<script type="text/javascript">
    function AddFavorite(url, title) {
        try {
            window.external.addFavorite(url, title);
        }
        catch (e) {
            try {
                window.sidebar.addPanel(title, url, "");
            }
            catch (e) {
                alert("抱歉，您所使用的浏览器无法完成此操作。\n\n加入收藏失败，请使用Ctrl+D进行添加");
            }
        }
    }
</script>
