<!DOCTYPE html>
<!-- saved from url=(0025)https://cpzj158.com/proxy -->
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no,minimal-ui">
    <meta name="format-detection" content="telephone=no">
    <meta name="format-detection" content="email=no">
    <meta name="renderer" content="webkit">
    <meta name="HandheldFriendly" content="true">
    <meta name="screen-orientation" content="portrait">
    <meta name="MobileOptimized" content="320">
    <meta name="x5-orientation" content="portrait">
    <meta name="full-screen" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="x5-fullscreen" content="true">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="red">
    <meta name="browsermode" content="application">
    <meta name="x5-page-mode" content="app">
    <meta name="MobileOptimized" content="320">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="screen-orientation" content="portrait">
    <meta name="apple-mobile-web-app-title" content="" id="appleName">
    <link rel="apple-touch-icon" sizes="114x114" href="" id="appleIcon">
    <link rel="icon" href="" id="linkIcon">
    <script type="text/javascript" src="{{asset('static/m/js/config.js')}}">
    </script>
    <title>
        {{$title}}
    </title>
    <link href="{{asset('static/m/css/styles.326bb4e1.css')}}" rel="preload"
          as="style">
    <link href="{{asset('static/m/js/app.4eefb7fa.js')}}" rel="preload" as="script">
    <link href="{{asset('static/m/css/styles.326bb4e1.css')}}" rel="stylesheet">
    @yield('style')
</head>
<body class="">
<noscript>
    <strong>
        We're sorry but gch5 doesn't work properly without JavaScript enabled.
        Please enable it to continue.
    </strong>
</noscript>
<div id="app">
    <div data-v-08acf61a="" class="van-nav-bar van-nav-bar--fixed" style="z-index: 1;">
        @if(isset($prev) && $prev == 1)
        <div data-v-08acf61a="" class="van-nav-bar__left">
            <i data-v-08acf61a="" class="van-icon van-icon-arrow-left van-nav-bar__arrow"></i>
        </div>
        @endif
        <div data-v-08acf61a="" class="van-nav-bar__title van-ellipsis">
					<span data-v-08acf61a="">
						{{$title}}
					</span>
        </div>
        <div data-v-08acf61a="" class="van-nav-bar__right">
        </div>
    </div>
    @yield('content')
</div>
<script src="{{asset('static/m/js/styles.34361615.js')}}"></script>
<script src="{{asset('static/m/js/app.4eefb7fa.js')}}"></script>
<script type="text/javascript" src="{{asset('static/js/jquery-3.6.0.min.js')}}"></script>
<script type="text/javascript" src="{{asset('static/m/js/clipboard.min.js')}}"></script>
@yield('js')
<script>
    $('.van-nav-bar__left').on('click', function(){
        window.location.href = "{{url('ag/m-index')}}"
    })
</script>
</body>

</html>
