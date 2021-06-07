@extends('ag.main')

@section('content')
    <div class="u_codeedit">
        @php
        $time_flag = request()->input('time_flag',1);
        $phone = request()->input('phone','');
        @endphp
        <div class="newTab">
            <a href="{{url('ag/report')}}?time_flag=1" @if($time_flag == 1) class="curr" @endif>{{trans('ag.today')}}</a>
            <a href="{{url('ag/report')}}?time_flag=2" @if($time_flag == 2) class="curr" @endif>{{trans('ag.yesterday')}}</a>
            <a href="{{url('ag/report')}}?time_flag=3" @if($time_flag == 3) class="curr" @endif>{{trans('ag.cur_month')}}</a>
            <a href="{{url('ag/report')}}?time_flag=4" @if($time_flag == 4) class="curr" @endif>{{trans('ag.last_month')}}</a>
        </div>
        <form class="todayView mgb10" action="" method="get">
            <input value="{{$time_flag}}" name="time_flag" type="hidden" />
            <input value="{{$phone}}" name="phone" type="text" placeholder="{{trans('ag.report_ipt_search')}}" class="userInput ng-pristine ng-untouched ng-valid ng-empty">
            <button class="submitBtn ClickShade">{{trans('ag.search')}}</button>
        </form>
        <div class="code_cont" style="">
            <ul class="plMore">
                <li>
                    <em class="ng-binding">{{$data['member']}}</em>
                    <span>{{trans('ag.report_detail1')}}</span>
                </li>
                <li>
                    <em class="ng-binding">{{$data['balance']}}</em>
                    <span>{{trans('ag.report_detail2')}}</span>
                </li>
                <li>
                    <em></em>
                    <span></span>
                </li>
                <li>
                    <em></em>
                    <span></span>
                </li>
                <li>
                    <em></em>
                    <span></span>
                </li>
            </ul>
            <ul class="plMore">
                <li>
                    <em class="ng-binding">{{$data['betting_money']}}</em>
                    <span>{{trans('ag.report_detail3')}}</span>
                </li>
                <li>
                    <em class="ng-binding">{{$data['win_money']}}</em>
                    <span>{{trans('ag.report_detail4')}}</span>
                </li>
                <li>
                    <em class="ng-binding">{{$data['commission']}}</em>
                    <span>{{trans('ag.report_detail5')}}</span>
                </li>
                <li>
                    <em class="ng-binding">{{$data['profit']}}</em>
                    <span>{{trans('ag.report_detail6')}}</span>
                </li>
                <li>
                    <em class="ng-binding">{{$data['betting_member']}}</em>
                    <span>{{trans('ag.report_detail7')}}</span>
                </li>
            </ul>
            <ul class="plMore">
                <li>
                    <em class="ng-binding">{{$data['first_recharge']}}</em>
                    <span>{{trans('ag.report_detail8')}}</span>
                </li>
                <li>
                    <em class="ng-binding">{{$data['register_member']}}</em>
                    <span>{{trans('ag.report_detail9')}}</span>
                </li>
                <li>
                    <em class="ng-binding">{{$data['recharge']}}</em>
                    <span>{{trans('ag.report_detail10')}}</span>
                </li>
                <li>
                    <em class="ng-binding">{{$data['withdraw']}}</em>
                    <span>{{trans('ag.report_detail11')}}</span>
                </li>
                <li>
                    <em></em>
                    <span></span>
                </li>
            </ul>
        </div>
    </div>
@endsection
