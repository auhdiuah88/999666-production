@extends('ag.main')

@section('content')
    <div class="newTab">
        <a class="curr nav-tab" href="{{url('ag/invite')}}?tab=1">下级开户</a>
        <a class="nav-tab" href="{{url('ag/invite')}}?tab=2">邀请码管理</a>
    </div>
    @php
        $user = \Illuminate\Support\Facades\Cache::get('user')
    @endphp
    <div class="TabLi ng-scope">
        <ul class="searchFirst" id="userType">
            <li>
                <span>开户类型：</span>
                <a class="userSearch" data-usertype="1">代理</a>
                <a class="userSearch active" data-usertype="2">玩家</a>
                <br> 返点设置：请先为下级设置返点。
{{--                <a class="rebateDesLink" target="_blank" href="/odds_table">点击查看返点赔率表</a>--}}
            </li>
        </ul>
        <div class="bonusTable">
            <ul class="clearfix ng-scope" ng-repeat="item in lotteryPoint track by $index" id="inputNum">
                <li class="ng-binding">红绿</li>
                <li>
                    <input type="number" name="honglv" min="0.1" step="0.1" max="{{$user['rebate_rate']}}" class="userInput mgl20">
                    <span class="ng-binding">（自身返点{{$user['rebate_rate']}}，可为下级设置返点范围0.1-{{$user['rebate_rate']}}）</span>
                </li>
            </ul>
        </div>
        <a class="submitBtn">生成邀请链接</a>
        <div class="userTip mg30">
            <p>※ 温馨提示：
                <br> 1、不同的返点赔率不同，返点越高赔率越高。
                <br> 2、代理可获得的佣金等于代理自身返点与下级返点的差值，如代理自身返点6，下级返点4，代理可获得下级投注金额的2%，即下级下注100元，代理可获得2元。
                <br> 3、下级返点值设得越低，下级的赔率就越低，建议给下级设置的返点不要过低。
            </p>
        </div>
    </div>
@endsection

@section('js2')
    <script>

        $('.userSearch').on('click', function(){
            $(this).addClass('active').siblings().removeClass('active');
        })

        $('.submitBtn').on('click', function(){
            var user_type = $("#userType .active").data('usertype')
            var rate = $("input[name=honglv]").val()
            rate = parseFloat(rate)
            if(isNaN(rate))
            {
                alert('请输入正确的返点')
                return false;
            }
            if(rate < 0.1 || rate > {{$user['rebate_rate']}})
            {
                alert('请输入正确的返点')
                return false;
            }
            $.post("{{url('ag/add_link')}}", {user_type, rate}, function(res){
                if(res.code === 200){
                    alert('生成成功');
                    window.location.reload()
                }else{
                    alert(res.msg)
                }
            }, 'json')
        })
    </script>
@endsection

@section('style')
    .tab-hide{
        display: none;
    }
@endsection
