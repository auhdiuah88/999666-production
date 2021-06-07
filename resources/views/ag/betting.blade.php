@extends('ag.main')

@section('content')
    @php
        $game_id = request()->input('game_id', 0);
        $type = request()->input('type', -1);
    @endphp
    <div ng-if="tab_type === 0" class="ng-scope">
        <form class="todayView search-wrapper" style="text-align: left;">
            <em>{{trans('ag.phone')}}：</em>
            <input type="text" value="{{request()->input('phone', '')}}" name="phone" placeholder="{{trans('ag.betting_ipt_holder')}}" class="userInput ng-pristine ng-untouched ng-valid ng-empty">&nbsp;
            <em>{{trans('ag.game_type')}}：</em>
            <select name="game_id">
                <option value="0" class="ng-binding ng-scope" @if($game_id == 0) selected @endif>
                    {{trans('ag.all')}}
                </option>
                @foreach($data['game'] as $g)
                    <option value="{{$g['id']}}" class="ng-binding ng-scope" @if($game_id == $g['id']) selected @endif>
                        {{$g['name']}}
                    </option>
                @endforeach
            </select>
            <em style="margin-left: 10px;">{{trans('ag.prize_type')}}：</em>
            <select name="type">
                <option value="-1" class="ng-binding ng-scope" @if($type == -1) selected @endif>
                    {{trans('ag.all')}}
                </option>
                <option value="1" class="ng-binding ng-scope" @if($type == 1) selected @endif>
                    {{trans('ag.win')}}
                </option>
                <option value="2" class="ng-binding ng-scope" @if($type == 2) selected @endif>
                    {{trans('ag.not_win')}}
                </option>
                <option value="0" class="ng-binding ng-scope" @if($type == 0) selected @endif>
                    {{trans('ag.wait_prize')}}
                </option>
            </select>
            <button type="submit" class="submitBtn">{{trans('ag.search')}}</button>
        </form>
        <table class="table-content">
            <thead class="t-head">
            <tr>
                <th>{{trans('ag.phone')}}</th>
                <th>{{trans('ag.game_type')}}</th>
                <th>{{trans('ag.period')}}</th>
                <th>{{trans('ag.betting_number')}}</th>
                <th>{{trans('ag.betting_money')}}</th>
                <th>{{trans('ag.prize_number')}}</th>
                <th>{{trans('ag.result')}}</th>
                <th>{{trans('ag.betting_time')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data['list'] as $item)
            <tr>
                <th>{{$item['users']['phone']}}</th>
                <th>{{$item['game_name']['name']}}</th>
                <th>{{$item['game_play']['number']}}</th>
                <th>{{$item['game_c_x']['name'] == "奇数" ? "Odd" : ($item['game_c_x']['name'] == "偶数" ? "Even" : ($item['game_c_x']['name'] == "幸运"? "Lucky" : $item['game_c_x']['name']))}}</th>
                <th>{{$item['money']}}</th>
                <th>{{$item['game_play']['prize_number']}}</th>
                <th>{{$item['status']==0 ? trans('ag.wait_prize') : ($item['status']==1? trans('ag.win') : trans('ag.not_win'))}}</th>
                <th>{{date('Y-m-d H:i:s', $item['betting_time'])}}</th>
            </tr>
            @endforeach
            @if($data['list']->isEmpty())
            <tr>
                <td colspan="100" class="ng-scope">
                    <div class="notContent no-record">
                        <span>trans('ag.empty')</span>
                    </div>
                </td>
            </tr>
            @endif
            </tbody>

        </table>
        <div class="page">
            {{ $data['list']->appends(['phone'=>request()->input('phone',''), 'type'=>$type, 'game_id'=>$game_id])->links() }}
        </div>
        <div class="userTip mg30">
            <p>
                <i>!</i>{{trans('ag.betting_notice')}}
            </p>
        </div>
    </div>
@endsection

@section('js2')
    <script>

    </script>
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
