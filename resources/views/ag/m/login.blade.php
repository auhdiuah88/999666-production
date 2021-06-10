<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{$title}}</title>
    <link href="{{asset('static/m/css/tailwind.min.css')}}" rel="stylesheet">
    <style>
        .dlbox .weixin, .dlbox .weibo {
            display: inline-block;
            width: 32px;
            height: 32px;
            background-size: cover;
        }

        .dlbox .weixin {
            background-image: url("{{asset('static/m/images/weixin.png')}}");
        }

        .dlbox .weibo {
            background-image: url("{{asset('static/m/images/weibo.png')}}");
        }
        .bg-indigo-100 {
            background-color: #ebf4ff;
        }.border-gray-200 {
             border-color: #edf2f7;
         }.bg-gray-100 {
              background-color: #f7fafc;
          }.bg-indigo-500 {
               background-color: #667eea;
           }
    </style>
</head>

<body class="min-h-screen bg-gray-100 text-gray-900 flex justify-center dlbox">
<div class="max-w-screen-xl m-0 sm:m-20 bg-white shadow sm:rounded-lg flex justify-center flex-1">
    <div class="lg:w-1/2 xl:w-5/12 p-6 sm:p-12">
        <div class="mt-12 flex flex-col items-center">
            <h1 class="text-2xl xl:text-3xl font-extrabold">{{trans('ag.agent_login')}}</h1>
            <div class="w-full flex-1 mt-8">

{{--                <div class="flex flex-col items-center">--}}
{{--                    <button class="w-full max-w-xs font-bold shadow-sm rounded-lg py-3 bg-indigo-100 text-gray-800 flex items-center justify-center ease-in-out focus:outline-none hover:shadow focus:shadow-sm focus:shadow-outline">--}}
{{--                        <div class="weixin"></div>--}}
{{--                        <span class="ml-4">使用微信登录</span>--}}
{{--                    </button>--}}
{{--                    <button class="w-full max-w-xs font-bold shadow-sm rounded-lg py-3 bg-indigo-100 text-gray-800 flex items-center justify-center ease-in-out focus:outline-none hover:shadow focus:shadow-sm focus:shadow-outline mt-5">--}}
{{--                        <div class="weibo"></div>--}}
{{--                        <span class="ml-4">使用微博登录</span>--}}
{{--                    </button>--}}
{{--                </div>--}}

{{--                <div class="my-12 border-b text-center">--}}
{{--                    <div class="leading-none px-2 inline-block text-sm text-gray-600 tracking-wide font-medium bg-white transform translate-y-1/2">或者使用电子邮箱注册</div>--}}
{{--                </div>--}}

                <div class="mx-auto max-w-xs">
                    <input name="phone" class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white" type="text" placeholder="{{trans('ag.phone')}}">
                    <input name="password" class="w-full px-8 py-4 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white mt-5" type="password" placeholder="{{trans('ag.password')}}">
                    <button class="mt-5 tracking-wide font-semibold bg-indigo-500 text-gray-100 w-full py-4 rounded-lg hover:bg-indigo-700 ease-in-out flex items-center justify-center focus:shadow-outline focus:outline-none btn-login">
                        <span class="ml-3">{{trans('ag.login')}}</span>
                    </button>
                    <button class="mt-5 tracking-wide font-semibold bg-indigo-500 text-gray-100 w-full py-4 rounded-lg hover:bg-indigo-700 ease-in-out flex items-center justify-center focus:shadow-outline focus:outline-none btn-return">
                        <span class="ml-3">{{trans('ag.return_index')}}</span>
                    </button>
                    <p class="mt-6 text-xs text-gray-600 text-center">{{trans('ag.agree_rule')}}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="flex-1 bg-indigo-100 text-center hidden lg:flex">
        <div class="m-12 xl:m-16 w-full bg-contain bg-center bg-no-repeat" style="background-image: url('{{asset("static/m/images/dlbox.svg")}}');"></div>
    </div>
</div>
<script type="text/javascript" src="{{asset('static/js/jquery-3.6.0.min.js')}}"></script>
<script>
    $('.btn-login').on('click', function(){
        var phone = $("input[name=phone]").val();
        var pwd = $("input[name=password]").val();
        if($.trim(phone).length < 8 || $.trim(pwd).length < 6)
            return false;
        $.post("{{url('ag/login')}}", {phone,pwd}, function(res){
            if(res.code === 200){
                window.location.href = "{{url('ag/m-index')}}"
            }else{
                alert(res.msg)
            }
        }, 'json')
    })

    $('.btn-return').on('click', function(){
        var url = "{{env('SHARE_URL','')}}"
        if(url){
            window.location.href = url;
        }
    })
</script>

</body>
</html>
