@extends('ag.m.layout')

@section('content')
    <div data-v-24479cb0="" class="Box viewBox paddingBottom0">
        <div data-v-24479cb0="" class="_img" style="background:url({{asset('static/m/images/desc.jpg')}}) 50%/cover no-repeat">
        </div>
        <div data-v-24479cb0="" class="content_box">
					<span data-v-24479cb0="" class="text_top">
						{{trans('ag.index1')}}
					</span>
            <div data-v-24479cb0="" class="list_item">
                <h3 data-v-24479cb0="">
							<span data-v-24479cb0="" class="_icon">
								<i data-v-24479cb0="" class="iconfont icon iconfont icon-zhengfangxing"
                                   style="font-size: 12px;">
									<!---->
								</i>
							</span>
                    <span data-v-24479cb0="" class="_title">
								{{trans('ag.index_title1')}}
							</span>
                </h3>
                <p data-v-24479cb0="">
                    {{trans('ag.index2')}}
                </p>
{{--                <p data-v-24479cb0="">--}}
{{--                    点击下级开户，可查看自身返点，也可为下级设置返点。--}}
{{--                </p>--}}
            </div>
            <div data-v-24479cb0="" class="list_item">
                <h3 data-v-24479cb0="">
							<span data-v-24479cb0="" class="_icon">
								<i data-v-24479cb0="" class="iconfont icon iconfont icon-wode-active"
                                   style="font-size: 12px;">
									<!---->
								</i>
							</span>
                    <span data-v-24479cb0="" class="_title">
								{{trans('ag.index_title2')}}
							</span>
                </h3>
                <p data-v-24479cb0="">
                    {{trans('ag.index3')}}
                </p>
{{--                <p data-v-24479cb0="">--}}
{{--                    如果您为下级设置的是代理类型的账号，那么您的下级就能继续发展下级，如果设置的是玩家类型，那么您的下级只能投注，不能再发展下级，也看不到代理中心；--}}
{{--                </p>--}}
            </div>
            <div data-v-24479cb0="" class="list_item">
                <h3 data-v-24479cb0="">
							<span data-v-24479cb0="" class="_icon">
								<i data-v-24479cb0="" class="iconfont icon iconfont icon-aixin" style="font-size: 12px;">
									<!---->
								</i>
							</span>
                    <span data-v-24479cb0="" class="_title">
								{{trans('ag.index_title3')}}
							</span>
                </h3>
                <p data-v-24479cb0="">
                    {{trans('ag.index4')}}
                </p>
                <p data-v-24479cb0="">
                    {{trans('ag.index5')}}
                </p>
                <p data-v-24479cb0="">
                    {{trans('ag.index6')}}
                </p>
                <p data-v-24479cb0="">
                    {{trans('ag.index7')}}
                </p>
            </div>
            <div data-v-24479cb0="" class="list_item">
                <h3 data-v-24479cb0="">
							<span data-v-24479cb0="" class="_icon">
								<i data-v-24479cb0="" class="iconfont icon iconfont icon-aixin" style="font-size: 12px;">
									<!---->
								</i>
							</span>
                    <span data-v-24479cb0="" class="_title">
								{{trans('ag.index_title4')}}
							</span>
                </h3>
                <p data-v-24479cb0="">
                    {{trans('ag.index8')}}
                </p>
                <p data-v-24479cb0="">
                    {{trans('ag.index9')}}
                </p>
                <p data-v-24479cb0="">
                    {{trans('ag.index10')}}
                </p>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>

    </script>
@endsection
