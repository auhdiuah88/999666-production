<!DOCTYPE html>
<html ng-app="main" lang="zh-CN">
<head>
    <meta charset="UTF-8">

    <title></title>
    <!-- <link rel="stylesheet" href=""> -->

    <!--[if IE 9]>
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/ie.css">
    <![endif]-->
    <link href="{{asset('static/css/21ea59.app.css')}}" rel="stylesheet">

    <style>
        @yield('style')
    </style>

</head>
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
                    @php
                    $user = \Illuminate\Support\Facades\Cache::get('user')
                    @endphp
                    @if(!$user)
                    <div class="login">
                        <form method="POST" id="login_form" action="{{url('ag/login')}}" class="ng-pristine ng-valid" onsubmit="return checkLogin()">
                            <div class="username left">
                                <i class="iconfont icon-mail"></i>
                                <input type="text" placeholder="手机号" name="phone" id="username" autocomplete="off" class="ng-pristine ng-untouched ng-valid ng-not-empty">
                            </div>
                            <div class="password left">
                                <div class="password left">
                                    <i class="iconfont icon-mima"></i>
                                    <input name="pwd" type="password" placeholder="密码" autocomplete="off" class="ng-pristine ng-untouched ng-valid ng-empty">
                                </div>
                                <!-- ngIf: data.isCode -->
                                <div class="login-btn-right">
                                    <button type="submit" class="left btn-default login-btn btn">登录</button>

{{--                                    <button type="button" class="left d-btn reg-btn btn" name="register" id="register">注册</button>--}}
                                    <!-- ngIf: is_open_guest == 1 -->
                                </div>
                            </div>
                        </form>
                    </div>
                    @else
                    <div class="logined">
                        <em>您好，</em>
                        <span class="username">{{$user['phone']}}</span>
                        <em>可用余额:</em>
                        <span class="money ng-binding ng-scope">{{$user['balance']}}</span>
{{--                        <span class="eye_open ng-scope"></span>--}}
                        <a href="javascript:;" style="margin-left: 30px">刷新</a>
{{--                        <a ng-click="checkStatus('充值')" style="color: #E94335;" target="_blank">充值</a>--}}
{{--                        <a ng-click="checkStatus('提现')" target="_blank">提现</a>--}}
{{--                        <a ng-href="/member/member_transaction" target="_blank" href="/member/member_transaction">投注记录</a>--}}
                        <a class="btn-quit">退出</a>
                    </div>
                    @endif
                </div>
            </div>
        </div></div>
    <!-- 中心内容 -->
    <div class="main usercenter ng-scope">
        <!--主体开始-->
        <div class="user_usersafe">
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
<script type="text/javascript" src="{{asset('static/js/jquery-3.6.0.min.js')}}"></script>
</body>
</html>

<script type="text/javascript">
    function checkLogin()
    {
        var phone = $("input[name=phone]").val();
        var pwd = $("input[name=pwd]").val();
        if($.trim(phone) == "")
        {
            alert("请输入手机号");
            return false;
        }
        if($.trim(phone).length < 8)
        {
            alert("请输入正确格式手机号");
            return false;
        }
        if($.trim(pwd) == '')
        {
            alert("请输入登录密码");
            return false;
        }
        if($.trim(pwd).length < 6)
        {
            alert("登录密码长度至少6位");
            return false;
        }
        $.post("{{url('ag/login')}}", {phone, pwd}, function(res){
            if(res.code === 200)
            {
                location.reload();
            }else{
                alert(res.msg);
            }
        }, 'json')
        return false;
    }

    $('.btn-quit').on('click', function(){
        $.post("{{url('ag/logout')}}",{},function(res){
            if(res.code === 200){
                window.location.href = "{{url('ag/index')}}"
            }else{
                alert('退出登录失败')
            }
        },'json')
    })

    @yield('js')
</script>

@yield('js2')
