@extends('ag.main')

@section('content')
    <div class="newTab">
        <a class="curr nav-tab" href="{{url('ag/invite')}}?tab=1">{{trans('ag.invite_tab1')}}</a>
        <a class="nav-tab" href="{{url('ag/invite')}}?tab=2">{{trans('ag.invite_tab2')}}</a>
    </div>
    @php
        $user = \Illuminate\Support\Facades\Cache::get('user')
    @endphp
    <div class="TabLi ng-scope">
        <ul class="searchFirst" id="userType">
            <li>
                <span>{{trans('ag.user_type')}}：</span>
                <a class="userSearch" data-usertype="1">{{trans('ag.user_type1')}}</a>
                <a class="userSearch active" data-usertype="2">{{trans('ag.user_type2')}}</a>
                <br> {{trans('ag.set_rate_notice')}}
                <a class="rebateDesLink" target="_blank" href="{{url('ag/odds_table')}}">{{trans('ag.click_see')}}</a>
            </li>
        </ul>
        <div class="bonusTable">
            <ul class="clearfix ng-scope" ng-repeat="item in lotteryPoint track by $index" id="inputNum">
                <li class="ng-binding">{{trans('ag.red_green')}}</li>
                <li>
                    <input type="number" name="honglv" min="0.1" step="0.1" max="{{$user['rebate_rate']}}" class="userInput mgl20">
                    <span class="ng-binding">（{{trans('ag.self_rate')}}{{$user['rebate_rate']}}，{{trans('ag.member_rate')}}0.1-{{$user['rebate_rate']}}）</span>
                </li>
            </ul>
        </div>
        <a class="submitBtn">{{trans('ag.create_link')}}</a>
        <div class="userTip mg30">
            <p>※ {{trans('ag.invite_notice')}}：
                <br> {{trans('ag.invite_notice1')}}
                <br> {{trans('ag.invite_notice2')}}
                <br> {{trans('ag.invite_notice3')}}
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
                alert('{{trans("ag.wrong_rate")}}')
                return false;
            }
            if(rate < 0.1 || rate > {{$user['rebate_rate']}})
            {
                alert('{{trans("ag.wrong_rate")}}')
                return false;
            }
            $.post("{{url('ag/add_link')}}", {user_type, rate}, function(res){
                if(res.code === 200){
                    alert('{{trans("ag.create_success")}}');
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
