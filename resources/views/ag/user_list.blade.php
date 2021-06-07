@extends('ag.main')

@section('content')
    <form method="get" action="{{url('ag/member')}}" class="searchOpt" style="padding-top: 15px">
        {{trans('ag.phone')}}：
        <input type="text" name="phone" class="userInput w90 ng-pristine ng-untouched ng-valid ng-empty" value="{{request()->input('phone','')}}">
        &nbsp; {{trans('ag.user_type')}}：
        <ins class="selectIcon">
            @php
            $user_type = request()->input('user_type',0)
            @endphp
            <select name="user_type" class="userSelect ng-pristine ng-untouched ng-valid ng-not-empty">
                <option value="0" class="ng-binding ng-scope" @if($user_type == 0) selected @endif>
                    {{trans('ag.all')}}
                </option>
                <option value="1" class="ng-binding ng-scope" @if($user_type == 1) selected @endif>
                    {{trans('ag.user_type1')}}
                </option>
                <option value="2" class="ng-binding ng-scope" @if($user_type == 2) selected @endif>
                    {{trans('ag.user_type2')}}
                </option>
            </select>
            <em></em>
        </ins>
        &nbsp;
        <button type="submit" class="submitBtn">{{trans('ag.search')}}</button>
    </form>
    <div class="accountDetail">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="ty_table3">
            <tbody>
            <tr>
                <th>{{trans('ag.phone')}}</th>
                <th>{{trans('ag.user_type')}}</th>
                <!-- <th>下级人数</th>
                <th>余额</th>
                <th>最后登录</th>
                <th>注册时间</th>
                <th>操作</th> -->
                <th> {{trans('ag.next_member')}}</th>
                <th>{{trans('ag.balance')}}</th>
                <th>{{trans('ag.last_login')}}</th>
                <th>{{trans('ag.register')}}</th>
            </tr>
            </tbody>
            <tbody>
            @foreach($data as $user)
            <tr class="recordTr">
                <th>{{$user['phone']}}</th>
                <th>{{$user['user_type'] == 1? trans('ag.user_type1') : trans('ag.user_type2')}}</th>
                <th>{{$user['member']}}</th>
                <th width="150">{{$user['balance']}}</th>
                <th width="180">{{date('Y-m-d H:i:s', $user['last_time'])}}</th>
                <th width="180">{{date('Y-m-d H:i:s', $user['reg_time'])}}</th>
            </tr>
            @endforeach
{{--            <tr class="recordTr">--}}
{{--                <td colspan="100" ng-if="memberList.length === 0" class="ng-scope">--}}
{{--                    <div class="notContent record">--}}
{{--                        <span>暂无记录</span>--}}
{{--                    </div>--}}
{{--                </td>--}}
{{--            </tr>--}}
            </tbody>
        </table>
        <div class="page">
            {{ $data->appends(['phone'=>request()->input('phone',''), 'user_type'=>request()->input('user_type',0)])->links() }}
        </div>
    </div>
@endsection

@section('style')
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
