@extends('ag.m.layout')

@section('content')
    <div data-v-1f5930b0="" class="Box viewBox paddingBottom0">
        <div data-v-1f5930b0="" class="_top" style="background:url({{asset('static/m/images/index.jpg')}}) 0 0/100% 100% no-repeat;">
        </div>
        <div data-v-1f5930b0="" class="van-cell-group van-hairline--top-bottom">

            <div data-v-1f5930b0="" role="button" tabindex="0" class="van-cell van-cell--clickable" data-url="{{url('ag/m-desc')}}">
                <div data-v-1f5930b0="" class="van-cell__title">
                    <span data-v-1f5930b0="">
                        {{trans('ag.agent_desc')}}
                    </span>
                </div>
                <i data-v-1f5930b0="" class="van-icon van-icon-arrow van-cell__right-icon"></i>
            </div>
            <div data-v-1f5930b0="" role="button" tabindex="0" class="van-cell van-cell--clickable" data-url="{{url('ag/m-report')}}">
                <div data-v-1f5930b0="" class="van-cell__title">
                    <span data-v-1f5930b0="">
                        {{trans('ag.agent_report')}}
                    </span>
                </div>
                <i data-v-1f5930b0="" class="van-icon van-icon-arrow van-cell__right-icon"></i>
            </div>
            <div data-v-1f5930b0="" role="button" tabindex="0" class="van-cell van-cell--clickable" data-url="{{url('ag/m-invite')}}">
                <div data-v-1f5930b0="" class="van-cell__title">
                    <span data-v-1f5930b0="">
                        {{trans('ag.manage_link')}}
                    </span>
                </div>
                <i data-v-1f5930b0="" class="van-icon van-icon-arrow van-cell__right-icon"></i>
            </div>
            <div data-v-1f5930b0="" role="button" tabindex="0" class="van-cell van-cell--clickable" data-url="{{url('ag/m-member')}}">
                <div data-v-1f5930b0="" class="van-cell__title">
                    <span data-v-1f5930b0="">
                        {{trans('ag.member_center')}}
                    </span>
                </div>
                <i data-v-1f5930b0="" class="van-icon van-icon-arrow van-cell__right-icon"></i>
            </div>
            <div data-v-1f5930b0="" role="button" tabindex="0" class="van-cell van-cell--clickable" data-url="{{url('ag/m-betting')}}?time_flag=1">
                <div data-v-1f5930b0="" class="van-cell__title">
                    <span data-v-1f5930b0="">
                        {{trans('ag.betting_manage')}}
                    </span>
                </div>
                <i data-v-1f5930b0="" class="van-icon van-icon-arrow van-cell__right-icon"></i>
            </div>
            <div data-v-1f5930b0="" role="button" tabindex="0" class="van-cell btn-quit">
                <div data-v-1f5930b0="" class="van-cell__title">
                    <span data-v-1f5930b0="">
                        {{trans('ag.quit_login')}}
                    </span>
                </div>
                <i data-v-1f5930b0="" class="van-icon van-icon-arrow van-cell__right-icon"></i>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $('.van-cell--clickable').on('click', function(){
            window.location.href = $(this).data('url');
        })

        $('.btn-quit').on('click', function(){
            $.post("{{url('ag/logout')}}",{},function(res){
                if(res.code === 200){
                    window.location.href = "{{url('ag/m-index')}}"
                }else{
                    alert('{{trans("ag.logout_fail")}}')
                }
            },'json')
        })
    </script>
@endsection
