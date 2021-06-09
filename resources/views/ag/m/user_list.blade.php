@extends('ag.m.layout')

@section('content')
    <div data-v-51881861="" class="memberManage viewBox paddingBottom0">
        <div data-v-92a4e448="" data-v-51881861="">

            <div data-v-92a4e448="" class="mar van-row" style="background: #fff;border-bottom: 1px solid #ddd;">
                <div data-v-92a4e448="" class="list_item van-col van-col--6">
                    {{trans('ag.phone')}}
                </div>
                <div data-v-92a4e448="" class="list_item van-col van-col--6">
                    {{trans('ag.user_type')}}
                </div>
                <div data-v-92a4e448="" class="list_item van-col van-col--6">
                    {{trans('ag.last_login')}}
                </div>
                <div data-v-92a4e448="" class="list_item van-col van-col--6">
                    {{trans('ag.next_member')}}
                </div>
            </div>

            @foreach($data as $user)
            <div data-v-92a4e448="" class="mar van-row" style="background: rgb(241, 244, 248);">
                <div data-v-92a4e448="" class="list_item van-col van-col--6">
                    {{$user['phone']}}
                </div>
                <div data-v-92a4e448="" class="list_item van-col van-col--6">
                    {{$user['user_type'] == 1? trans('ag.user_type1') : trans('ag.user_type2')}}
                </div>
                <div data-v-92a4e448="" class="list_item van-col van-col--6">
                    {{date('Y-m-d', $user['last_time'])}}
                </div>
                <div data-v-92a4e448="" class="list_item van-col van-col--6">
                    {{$user['member']}}
                </div>
            </div>
            @endforeach

            @if($data->isEmpty())
            <div data-v-92a4e448="" class="van-pull-refresh">
                <div class="van-pull-refresh__track" style="transition-duration: 300ms;">
                    <div class="van-pull-refresh__head">
                    </div>
                    <div data-v-92a4e448="" role="feed" class="van-list">
                        <div class="van-list__finished-text">
                            {{trans('ag.nomore')}}
                        </div>
                        <div class="van-list__placeholder">
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div data-v-52f556ac="" data-v-6fab7655="" class="showAllTip">
                {{ $data->links() }}
            </div>
            @endif
        </div>
    </div>
@endsection

@section('js')
<script>

</script>
@endsection

@section('style')
    <style>
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
