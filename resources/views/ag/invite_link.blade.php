@extends('ag.main')

@section('content')
<div class="newTab">
    <a class="nav-tab" href="{{url('ag/invite')}}?tab=1">下级开户</a>
    <a class="curr nav-tab" href="{{url('ag/invite')}}?tab=2">邀请码管理</a>
</div>
@php
    $user = \Illuminate\Support\Facades\Cache::get('user')
@endphp

<div class="TabLi ng-scope">
    <div class="invitationCode">
        <table width="100%" border="0" margin="0" padding="0" cellspacing="0" cellpadding="0" class="manageInvite">
            <tbody>
            <tr>
                <th>注册链接</th>
                <th>用户类型</th>
                <th>返点</th>
                <th>生成时间</th>
                <th>操作</th>
            </tr>
            @foreach($data['data'] as $item)
            <tr ng-repeat="item in codeList track by $index" class="ng-scope">
                <td>
                    <input class="copyLink" type="text" value="{{env('SHARE_URL') . $item['link']}}">
{{--                    <span class="copybtn">复制</span>--}}
                </td>
                <td>
                    <em class="xem">{{$item['type']['text']}}</em>
                </td>
                <td>
                    <em class="xem">{{$item['rebate_percent']}}</em>
                </td>
                <td class="ng-binding">{{date('Y-m-d H:i:s', $item['created_at'])}}</td>
                <td>
                    <a class="del btn-del" data-id="{{$item['id']}}">删除</a>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        <div class="page" style="">
            <p>共<em id="codeCount" class="ng-binding">1</em>条记录</p>
            <div id="pageNav" class="pageNav">
                <ul class="pagination" style="display: none;">
                    <li style="display: none;"><a>上一页</a></li>
                    <li class="active"><a>1</a></li>
                    <li style="display: none;"><a>下一页</a></li>
                </ul>
            </div>
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
    $('.userSearch').on('click', function(){
        $(this).addClass('active').siblings().removeClass('active')
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

    $('.btn-del').on('click', function(){
        var id = $(this).data('id')
        $.post("{{url('ag/del_link')}}", {id}, function(res){
            if(res.code === 200){
                alert("{{trans('user.del_link_success')}}")
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
@endsection
