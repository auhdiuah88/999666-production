@extends('ag.main')

@section('content')
<div class="newTab">
    <a class="nav-tab" href="{{url('ag/invite')}}?tab=1">{{trans('ag.invite_tab1')}}</a>
    <a class="curr nav-tab" href="{{url('ag/invite')}}?tab=2">{{trans('ag.invite_tab2')}}</a>
</div>
@php
    $user = \Illuminate\Support\Facades\Cache::get('user')
@endphp

<div class="TabLi ng-scope">
    <div class="invitationCode">
        <table width="100%" border="0" margin="0" padding="0" cellspacing="0" cellpadding="0" class="manageInvite">
            <tbody>
            <tr>
                <th>{{trans('ag.register_link')}}</th>
                <th>{{trans('ag.user_type')}}</th>
                <th>{{trans('ag.rate')}}</th>
                <th>{{trans('ag.create_time')}}</th>
                <th>{{trans('ag.handle')}}</th>
            </tr>
            @foreach($data as $item)
            <tr ng-repeat="item in codeList track by $index" class="ng-scope">
                <td>
                    <input class="copyLink" type="text" value="{{env('SHARE_URL') . $item['link']}}">
{{--                    <span class="copybtn">复制</span>--}}
                </td>
                <td>
                    <em class="xem">{{$item['type']['text'] == '代理' ? trans('ag.user_type1') : trans('ag.user_type2')}}</em>
                </td>
                <td>
                    <em class="xem">{{$item['rebate_percent']}}</em>
                </td>
                <td class="ng-binding">{{date('Y-m-d H:i:s', $item['created_at'])}}</td>
                <td>
                    <a class="del btn-del" data-id="{{$item['id']}}">{{trans('ag.delete')}}</a>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        <div class="page" style="">
            {{ $data->appends(['tab'=>2])->links() }}
{{--            <p>共<em id="codeCount" class="ng-binding">{{$data['total']}}</em>条记录</p>--}}
{{--            <div id="pageNav" class="pageNav">--}}
{{--                <ul class="pagination" style="display: none;">--}}
{{--                    @if($data['prev_page_url'])--}}
{{--                    <li><a href="{{$data['pages']['prev_page_url']}}">上一页</a></li>--}}
{{--                    @endif--}}
{{--                        @if($data['next_page_url'])--}}
{{--                    <li><a href="{{$data['next_page_url']}}">下一页</a></li>--}}
{{--                        @endif--}}
{{--                </ul>--}}
{{--            </div>--}}
        </div>
    </div>
</div>
@endsection

@section('js2')
<script>
    $('.nav-tab').on('click', function(){
        $(this).addClass('curr').siblings().removeClass('curr')
        $('.TabLi').addClass('tab-hide')
        $('.TabLi').eq($(this).index()).removeClass('tab-hide')
    })

    $('.btn-del').on('click', function(){
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

@endsection
