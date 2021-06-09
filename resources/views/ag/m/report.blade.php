@extends('ag.m.layout')

@section('content')
    @php
    $time_flag = request()->input('time_flag',1);
    $phone = request()->input('phone','');
    @endphp
    <div data-v-0e27e65a="" class="proxyReport viewBox paddingBottom0">
        <div data-v-0e27e65a="">
            <div data-v-0a31de89="" data-v-0e27e65a="" class="search">
                <div data-v-0a31de89="" class="vancell van-cell van-field">
                    <div class="van-cell__value van-cell__value--alone">
                        <div class="van-field__body">
                            <input value="{{$phone}}" type="text" name="phone" placeholder="{{trans('ag.report_ipt_search')}}" class="van-field__control">
                        </div>
                    </div>
                </div>
                <span data-v-0a31de89="" class="search_arrow">
				<i data-v-0a31de89="" class="_ico van-icon van-icon-arrow"></i>
			</span>
            </div>
            <div data-v-0e27e65a="" class="van-grid van-hairline--top">
                <div data-v-0e27e65a="" class="van-grid-item" style="flex-basis: 33.3333%;">
                    <div class="van-grid-item__content van-grid-item__content--center van-hairline">
                        <p data-v-0e27e65a="" class="van-ellipsis">
                            {{$data['member']}}
                        </p>
                        <p data-v-0e27e65a="">
                            {{trans('ag.report_detail1')}}
                        </p>
                    </div>
                </div>
                <div data-v-0e27e65a="" class="van-grid-item" style="flex-basis: 33.3333%;">
                    <div class="van-grid-item__content van-grid-item__content--center van-hairline">
                        <p data-v-0e27e65a="" class="van-ellipsis">
                            {{$data['balance']}}
                        </p>
                        <p data-v-0e27e65a="">
                            {{trans('ag.report_detail2')}}
                        </p>
                    </div>
                </div>
                <div data-v-0e27e65a="" class="van-grid-item" style="flex-basis: 33.3333%;">
                    <div class="van-grid-item__content van-grid-item__content--center van-hairline">
                        <p data-v-0e27e65a="" class="van-ellipsis">
                            {{$data['betting_money']}}
                        </p>
                        <p data-v-0e27e65a="">
                            {{trans('ag.report_detail3')}}
                        </p>
                    </div>
                </div>
                <div data-v-0e27e65a="" class="van-grid-item" style="flex-basis: 33.3333%;">
                    <div class="van-grid-item__content van-grid-item__content--center van-hairline">
                        <p data-v-0e27e65a="" class="van-ellipsis">
                            {{$data['win_money']}}
                        </p>
                        <p data-v-0e27e65a="">
                            {{trans('ag.report_detail4')}}
                        </p>
                    </div>
                </div>
                <div data-v-0e27e65a="" class="van-grid-item" style="flex-basis: 33.3333%;">
                    <div class="van-grid-item__content van-grid-item__content--center van-hairline">
                        <p data-v-0e27e65a="" class="van-ellipsis">
                            {{$data['commission']}}
                        </p>
                        <p data-v-0e27e65a="">
                            {{trans('ag.report_detail5')}}
                        </p>
                    </div>
                </div>
                <div data-v-0e27e65a="" class="van-grid-item" style="flex-basis: 33.3333%;">
                    <div class="van-grid-item__content van-grid-item__content--center van-hairline">
                        <p data-v-0e27e65a="" class="van-ellipsis">
                            {{$data['profit']}}
                        </p>
                        <p data-v-0e27e65a="">
                            {{trans('ag.report_detail6')}}
                        </p>
                    </div>
                </div>
                <div data-v-0e27e65a="" class="van-grid-item" style="flex-basis: 33.3333%;">
                    <div class="van-grid-item__content van-grid-item__content--center van-hairline">
                        <p data-v-0e27e65a="" class="van-ellipsis">
                            {{$data['betting_member']}}
                        </p>
                        <p data-v-0e27e65a="">
                            {{trans('ag.report_detail7')}}
                        </p>
                    </div>
                </div>
                <div data-v-0e27e65a="" class="van-grid-item" style="flex-basis: 33.3333%;">
                    <div class="van-grid-item__content van-grid-item__content--center van-hairline">
                        <p data-v-0e27e65a="" class="van-ellipsis">
                            {{$data['first_recharge']}}
                        </p>
                        <p data-v-0e27e65a="">
                            {{trans('ag.report_detail8')}}
                        </p>
                    </div>
                </div>
                <div data-v-0e27e65a="" class="van-grid-item" style="flex-basis: 33.3333%;">
                    <div class="van-grid-item__content van-grid-item__content--center van-hairline">
                        <p data-v-0e27e65a="" class="van-ellipsis">
                            {{$data['register_member']}}
                        </p>
                        <p data-v-0e27e65a="">
                            {{trans('ag.report_detail9')}}
                        </p>
                    </div>
                </div>
                <div data-v-0e27e65a="" class="van-grid-item" style="flex-basis: 33.3333%;">
                    <div class="van-grid-item__content van-grid-item__content--center van-hairline">
                        <p data-v-0e27e65a="" class="van-ellipsis">
                            {{$data['recharge']}}
                        </p>
                        <p data-v-0e27e65a="">
                            {{trans('ag.report_detail10')}}
                        </p>
                    </div>
                </div>
                <div data-v-0e27e65a="" class="van-grid-item" style="flex-basis: 33.3333%;">
                    <div class="van-grid-item__content van-grid-item__content--center van-hairline">
                        <p data-v-0e27e65a="" class="van-ellipsis">
                            {{$data['withdraw']}}
                        </p>
                        <p data-v-0e27e65a="">
                            {{trans('ag.report_detail11')}}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div data-v-8e7e70dc="" data-v-0e27e65a="" class="model-wrap">
            <div class="van-overlay model" style="z-index: 2021; display: none;"></div>
            <div data-v-8e7e70dc="" class="model van-popup van-popup--bottom van-popup--safe-area-inset-bottom van-action-sheet" style="z-index: 2022; display: none;">
                <button data-v-8e7e70dc="" class="van-action-sheet__item van-hairline--top" data-idx="1">
                    <span data-v-8e7e70dc="" class="van-action-sheet__name @if($time_flag == 1) cur-day @endif">
                        {{trans('ag.today')}}
                    </span>
                </button>
                <button data-v-8e7e70dc="" class="van-action-sheet__item van-hairline--top" data-idx="2">
                    <span data-v-8e7e70dc="" class="van-action-sheet__name @if($time_flag == 2) cur-day @endif">
                        {{trans('ag.yesterday')}}
                    </span>
                </button>
                <button data-v-8e7e70dc="" class="van-action-sheet__item van-hairline--top" data-idx="3">
                    <span data-v-8e7e70dc="" class="van-action-sheet__name @if($time_flag == 3) cur-day @endif">
                        {{trans('ag.cur_month')}}
                    </span>
                </button>
                <button data-v-8e7e70dc="" class="van-action-sheet__item van-hairline--top" data-idx="4">
                    <span data-v-8e7e70dc="" class="van-action-sheet__name @if($time_flag == 4) cur-day @endif">
                        {{trans('ag.last_month')}}
                    </span>
                </button>
                <button data-v-8e7e70dc="" class="van-action-sheet__cancel">
                    {{trans('ag.cancel')}}
                </button>
            </div>
            <span data-v-8e7e70dc="" class="absRight top_right">
                <span data-v-8e7e70dc="" class="day-txt"></span>
                <i data-v-8e7e70dc="" class="_ico van-icon van-icon-arrow-down"></i>
            </span>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $('.van-action-sheet__cancel').on('click', function(){
            hideModel()
        })

        function hideModel(){
            $('.model-wrap').removeClass('active');
        }

        $('.absRight').on('click', function(){
            showModel()
        })

        function showModel(){
            $('.model-wrap').addClass('active');
        }

        $('.search_arrow').on('click', function(){
            var phone = $("input[name=phone]").val()
            var time_flag = '{{$time_flag}}'
            window.location.href = `{{url('ag/m-report')}}?phone=${phone}&time_flag=${time_flag}`
        })

        $('.van-action-sheet__item').on('click', function(){
            var time_flag = $(this).data('idx')
            window.location.href = `{{url('ag/m-report')}}?time_flag=${time_flag}`
        })

        $(function(){
            $('.day-txt').text($('.cur-day').text())
        })
    </script>
@endsection

@section('style')
    <style>
        .active .model{
            display:block !important;
        }
    </style>
@endsection
