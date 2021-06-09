@extends('ag.m.layout')

@section('content')
    @php
        $user = \Illuminate\Support\Facades\Cache::get('user');
        $tab = request()->input('tab',1)
    @endphp
    <div data-v-52f556ac="" class="nextUser viewBox paddingBottom0">
        <div data-v-52f556ac="">
            <div data-v-6fab7655="" data-v-52f556ac="" class="proxyTab">
                <div data-v-6fab7655="" class="absRight _abs">
                    <div data-v-6fab7655="" class="_tab">
                        <div data-v-6fab7655="" class="tab_item @if($tab == 1) bg_c @endif" data-tab="1">
                            {{trans('ag.invite_tab1')}}
                        </div>
                        <div data-v-6fab7655="" class="tab_item @if($tab == 2) bg_c @endif" data-tab="2">
                            {{trans('ag.invite_tab2')}}
                        </div>
                    </div>
                </div>

                <div data-v-52f556ac="" data-v-6fab7655="">
                    <div data-v-79549ac8="" data-v-52f556ac="" data-v-6fab7655="">
                        <div data-v-79549ac8="">
                            @foreach($data as $item)
                            <div data-v-79549ac8="" class="box">
                                <div data-v-79549ac8="" class="top">
                                    <div data-v-79549ac8="">
                                        <div data-v-79549ac8="" class="invitecode">
                                            {{trans('ag.user_type')}}:{{$item['type']['text'] == '代理' ? trans('ag.user_type1') : trans('ag.user_type2')}}
                                        </div>
                                        <div data-v-79549ac8="" class="date">
                                            {{date('Y-m-d H:i:s', $item['created_at'])}}
                                        </div>
                                    </div>
                                    <div data-v-79549ac8="" class="top-left">
                                        <span data-v-79549ac8="" class="leftdesc">
                                            {{trans('ag.rate')}}({{$item['rebate_percent']}})
                                        </span>
                                        <i data-v-79549ac8="" class="delico van-icon van-icon-delete" data-id="{{$item['id']}}">
                                            <!---->
                                        </i>
                                    </div>
                                </div>
                                <div data-v-79549ac8="" class="foot">
                                    <ul data-v-79549ac8="">
{{--                                        <li data-v-79549ac8="">--}}
{{--                                            <span data-v-79549ac8="">--}}
{{--                                                查看返点--}}
{{--                                            </span>--}}
{{--                                        </li>--}}
                                        <li data-v-79549ac8="" class="btn-copy" data-link="{{env('SHARE_URL') . $item['link']}}">
                                            {{trans('ag.copy')}}
                                        </li>
{{--                                        <li data-v-79549ac8="">--}}
{{--                                            <span data-v-79549ac8="" class="iconfont sharepic">--}}
{{--                                                --}}
{{--                                            </span>--}}
{{--                                            &nbsp;--}}
{{--                                            <span data-v-79549ac8="">--}}
{{--                                                分享赚钱--}}
{{--                                            </span>--}}
{{--                                        </li>--}}
                                    </ul>
                                </div>
                            </div>
                            @endforeach
                            <input name="copy-content" value="" type="hidden" />
                        </div>
                    </div>
{{--                    <div data-v-52f556ac="" data-v-6fab7655="" class="showAllTip">--}}
{{--                        已显示全部数据--}}
{{--                    </div>--}}
                    <div data-v-52f556ac="" data-v-6fab7655="" class="showAllTip">
                        {{ $data->appends(['tab'=>2])->links() }}
                    </div>
                </div>

            </div>
            <!---->
        </div>
        <!---->
    </div>
@endsection

@section('js')
    <script>
        $('.van-radio__icon--round').on('click', function(){
            $('.van-radio__icon--round').removeClass('van-radio__icon--checked');
            $(this).addClass('van-radio__icon--checked');
        })

        $('.btn-submit').on('click', function(){
            var rate = $("input[name=rate]").val()
            var user_type = $('.van-radio__icon--checked').data('usertype')
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

        $(".tab_item").on('click', function(){
            var tab = $(this).data('tab')
            window.location.href = `{{url('ag/m-invite')}}?tab=${tab}`
        });

        $('.btn-copy').on('click', function(){
            $('input[name=copy-content]').val($(this).data('link'))
        })

        var clipboard = new Clipboard('.btn-copy', {
            text: function() {
                return $('input[name=copy-content]').val();
            }
        });
        clipboard.on('success', function(e) {
            alert("{{trans('ag.copy_success')}}");
        });

        $('.van-icon-delete').on('click', function(){
            var id = $(this).data('id')
            $.post("{{url('ag/del_link')}}", {id}, function(res){
                if(res.code === 200){
                    alert("{{trans('ag.del_link_success')}}")
                    window.location.reload();
                }else{
                    alert(res.msg)
                }
            }, 'json')
        })
    </script>
@endsection

@section('style')
    <style>
        .tab-hide{
            display: none;
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
    </style>
@endsection
