@extends('ag.main')

@section('content')
    <div class="userMain agentIntro">
        <img alt="{{trans('ag.nav1')}}" width="100%" height="160" src="{{asset('static/image/what_ag.jpg')}}">
        <em>{{trans('ag.index1')}}</em>
        <h3>{{trans('ag.index_title1')}}</h3>
        <p>{{trans('ag.index2')}}</p>
        <h3>{{trans('ag.index_title2')}}</h3>
        <p>{{trans('ag.index3')}}</p>
        <h3>{{trans('ag.index_title3')}}</h3>
        <p>
            {{trans('ag.index4')}}<br>
            {{trans('ag.index5')}}<br>
            {{trans('ag.index6')}}<br>
            {{trans('ag.index7')}}
        </p>
        <h3>
            {{trans('ag.index_title4')}}
        </h3>
        <p>{{trans('ag.index8')}}</p>
        <p>{{trans('ag.index9')}}</p>
        <p>{{trans('ag.index10')}}</p>
    </div>
@endsection
