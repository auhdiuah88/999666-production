@extends('ag.m.layout')

@section('content')
    @php
        $time_flag = request()->input('time_flag',1);
        $phone = request()->input('phone','');
        $type = request()->input('type',-1);
    @endphp
    <div data-v-18ac4b44="" class="bettingDetail viewBox paddingBottom0">
        <div data-v-18ac4b44="">
            <div data-v-18ac4b44="" class="tabs-list">
                <div data-v-0a31de89="" data-v-18ac4b44="" class="search">
                    <div data-v-0a31de89="" class="vancell van-cell van-field">
                        <div class="van-cell__value van-cell__value--alone">
                            <div class="van-field__body">
                                <input value="{{$phone}}" type="text" name="phone" placeholder="{{trans('ag.betting_ipt_holder')}}" class="van-field__control">
                            </div>
                        </div>
                    </div>
                    <span data-v-0a31de89="" class="search_arrow">
					<i data-v-0a31de89="" class="_ico van-icon van-icon-arrow">
						<!---->
					</i>
				</span>
                </div>
                <div data-v-fe847ec4="" data-v-18ac4b44="">
                    <div data-v-fe847ec4="" class="van-tabs van-tabs--line">
                        <div>
                            <div class="van-sticky">
                                <div class="van-tabs__wrap van-hairline--top-bottom">
                                    <div role="tablist" class="van-tabs__nav van-tabs__nav--line">
                                        <div role="tab" class="van-tab @if($type == -1) van-tab--active @endif" data-type="-1" data-line="0">
                                            <span class="van-ellipsis">
                                                {{trans('ag.all')}}
                                            </span>
                                        </div>
                                        <div role="tab" class="van-tab @if($type == 1) van-tab--active @endif" data-type="1" data-line="1">
                                            <span class="van-ellipsis">
                                                {{trans('ag.win')}}
                                            </span>
                                        </div>
                                        <div role="tab" class="van-tab @if($type == 2) van-tab--active @endif" data-type="2" data-line="2">
                                            <span class="van-ellipsis">
                                                {{trans('ag.not_win')}}
                                            </span>
                                        </div>
                                        <div role="tab" class="van-tab @if($type == 0) van-tab--active @endif" data-type="0" data-line="3">
                                            <span class="van-ellipsis">
                                                {{trans('ag.wait_prize')}}
                                            </span>
                                        </div>
                                        <div class="van-tabs__line" style="width: 25%; transition-duration: 0.1s;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="van-tabs__content">
                            @if($data['list']->isEmpty())
                                <div data-v-fe847ec4="" role="tabpanel" class="van-tab__pane">
                                    <div data-v-fe847ec4="" class="van-pull-refresh">
                                        <div class="van-pull-refresh__track" style="transition-duration: 0ms;">
                                            <div class="van-pull-refresh__head">
                                            </div>
                                            <!---->
                                            <div data-v-fe847ec4="">
                                                <div data-v-38100e0a="" data-v-fe847ec4="" class="noData">
                                                    <i data-v-38100e0a="" class="iconfont img icon iconfont img icon-wei"
                                                       style="font-size: 100px;">
                                                        <!---->
                                                    </i>
                                                    <div data-v-38100e0a="" class="text">
                                                        {{trans('ag.empty')}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                            <div style="padding:10px;background: #fff;border-radius: 5px;">
                                @foreach($data['list'] as $item)
                                <div data-v-92a4e448="" class="mar van-row" style="background: rgb(241, 244, 248);">
                                    <div class="row-left">
                                        <div class="per-attr">
                                            <span>{{trans('ag.phone')}}</span>
                                            <span>{{$item['users']['phone']}}</span>
                                        </div>
                                        <div class="per-attr">
                                            <span>{{trans('ag.game_type')}}</span>
                                            <span>{{$item['game_name']['name']}}</span>
                                        </div>
                                        <div class="per-attr">
                                            <span>{{trans('ag.period')}}</span>
                                            <span>{{$item['game_play']['number']}}</span>
                                        </div>
                                        <div class="per-attr">
                                            <span>{{trans('ag.result')}}</span>
                                            <span>{{$item['status']==0 ? trans('ag.wait_prize') : ($item['status']==1? trans('ag.win') : trans('ag.not_win'))}}</span>
                                        </div>
                                    </div>
                                    <div class="row-right">
                                        <div class="per-attr">
                                            <span>{{trans('ag.betting_number')}}</span>
                                            <span>{{$item['game_c_x']['name'] == "奇数" ? "Odd" : ($item['game_c_x']['name'] == "偶数" ? "Even" : ($item['game_c_x']['name'] == "幸运"? "Lucky" : $item['game_c_x']['name']))}}</span>
                                        </div>
                                        <div class="per-attr">
                                            <span>{{trans('ag.betting_money')}}</span>
                                            <span>{{$item['money']}}</span>
                                        </div>
                                        <div class="per-attr">
                                            <span>{{trans('ag.prize_number')}}</span>
                                            <span>{{$item['game_play']['prize_number']}}</span>
                                        </div>
                                        <div class="per-attr">
                                            <span>{{trans('ag.betting_time')}}</span>
                                            <span>{{date('Y-m-d H:i:s', $item['betting_time'])}}</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                            <div data-v-52f556ac="" data-v-6fab7655="" class="showAllTip">
                                {{ $data['list']->appends(['type'=>$type, 'time_flag'=>$time_flag, 'phone'=>$phone])->links() }}
                            </div>
                        </div>
                    </div>
                </div>
                <!---->
            </div>
            <!---->
            <div data-v-8e7e70dc="" data-v-18ac4b44="" class="model-wrap">
                <div class="van-overlay model" style="z-index: 2015; display: none;">
                </div>
                <div data-v-8e7e70dc="" class="model van-popup van-popup--bottom van-popup--safe-area-inset-bottom van-action-sheet"
                     style="z-index: 2016; display: none;">
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
                            {{trans('ag.seven_day')}}
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
            var type = getType()
            window.location.href = `{{url('ag/m-betting')}}?phone=${phone}&time_flag=${time_flag}&type=${type}`
        })

        $('.van-action-sheet__item').on('click', function(){
            var time_flag = $(this).data('idx')
            var type = getType()
            window.location.href = `{{url('ag/m-betting')}}?time_flag=${time_flag}&type=${type}`
        })

        $(function(){
            $('.day-txt').text($('.cur-day').text())
        })

        $('.van-tab').on('click', function(){
            var type = $(this).data('type')
            var time_flag = '{{$time_flag}}'
            window.location.href = `{{url('ag/m-betting')}}?time_flag=${time_flag}&type=${type}`
        })

        function tabLineRemoveClass(){
            $('.van-tabs__line').removeClass('van-tabs__line_'+$('.van-tab--active').data('line'))
        }
        function tabLineAddClass(){
            $('.van-tabs__line').addClass('van-tabs__line_'+$('.van-tab--active').data('line'))
        }

        function getType(){
            return $('.van-tab--active').data('type')
        }

        $(function(){
            tabLineRemoveClass()
            tabLineAddClass()
        })
    </script>
@endsection

@section('style')
    <style>
        .van-row{
            display: flex;
            flex-direction: row;
            justify-content: space-around;
            align-items: center;
            padding: 10px 5px;
            border-bottom: 2px solid #fff;
        }
        .row-left{
            width:45%;
        }
        .row-right{
            width:45%
        }
        .per-attr{
            line-height: 25px;
            color:#000;
            font-size:14px;
        }
        .per-attr span{
            margin-right: 5px;
        }


        .active .model{
            display:block !important;
        }
        #pull_right{
            text-align:center;
        }
        .pull-right {
            /*float: left!important;*/
        }
        .pagination {
            display: inline-block;
            padding-left: 0;
            margin: 20px 0;
            border-radius: 4px;
        }
        .pagination > li {
            display: inline;
        }
        .pagination > li > a,
        .pagination > li > span {
            position: relative;
            float: left;
            padding: 6px 12px;
            margin-left: -1px;
            line-height: 1.42857143;
            color: #428bca;
            text-decoration: none;
            background-color: #fff;
            border: 1px solid #ddd;
        }
        .pagination > li:first-child > a,
        .pagination > li:first-child > span {
            margin-left: 0;
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
        }
        .pagination > li:last-child > a,
        .pagination > li:last-child > span {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }
        .pagination > li > a:hover,
        .pagination > li > span:hover,
        .pagination > li > a:focus,
        .pagination > li > span:focus {
            color: #2a6496;
            background-color: #eee;
            border-color: #ddd;
        }
        .pagination > .active > a,
        .pagination > .active > span,
        .pagination > .active > a:hover,
        .pagination > .active > span:hover,
        .pagination > .active > a:focus,
        .pagination > .active > span:focus {
            z-index: 2;
            color: #fff;
            cursor: default;
            background-color: #428bca;
            border-color: #428bca;
        }
        .pagination > .disabled > span,
        .pagination > .disabled > span:hover,
        .pagination > .disabled > span:focus,
        .pagination > .disabled > a,
        .pagination > .disabled > a:hover,
        .pagination > .disabled > a:focus {
            color: #777;
            cursor: not-allowed;
            background-color: #fff;
            border-color: #ddd;
        }
        .clear{
            clear: both;
        }

        .van-tabs__line_0{
            transform: translateX(0%);
        }
        .van-tabs__line_1{
            transform: translateX(100%);
        }
        .van-tabs__line_2{
            transform: translateX(200%);
        }
        .van-tabs__line_3{
            transform: translateX(300%);
        }
        .van-tab--active{
            color: rgb(217, 57, 62);
        }
    </style>
@endsection
