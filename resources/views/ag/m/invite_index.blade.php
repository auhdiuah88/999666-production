@extends('ag.m.layout')

@section('content')
    @php
        $user = $_SESSION['user'];
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
                    <div data-v-5a1bc432="" data-v-52f556ac="" class="_type" data-v-6fab7655="">
                        <div data-v-5a1bc432="" class="_item">
						<span data-v-5a1bc432="" class="span">
							{{trans('ag.user_type')}}
						</span>
                            <div data-v-5a1bc432="" role="radiogroup" class="radiogroup van-radio-group">
                                <div data-v-5a1bc432="" role="radio" tabindex="-1" aria-checked="false"
                                     class="van-radio">
                                    <div class="van-radio__icon van-radio__icon--round van-radio__icon--checked" data-usertype="1">
                                        <i class="van-icon van-icon-success" style="">
                                            <!---->
                                        </i>
                                    </div>
                                    <span class="van-radio__label">
									{{trans('ag.user_type1')}}&nbsp;&nbsp;&nbsp;&nbsp;
								</span>
                                </div>
                                <div data-v-5a1bc432="" role="radio" tabindex="0" aria-checked="true"
                                     class="van-radio">
                                    <div class="van-radio__icon van-radio__icon--round" data-usertype="2">
                                        <i class="van-icon van-icon-success" style="">
                                            <!---->
                                        </i>
                                    </div>
                                    <span class="van-radio__label">
									{{trans('ag.user_type2')}}
								</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div data-v-52f556ac="" data-v-6fab7655="" class="_item tip">
                        {{trans('ag.m_set_rate')}}
                        <a data-v-52f556ac="" data-v-6fab7655="" style="color: red;" href="{{url('ag/m-odds_table')}}">
						{{trans('ag.click_see')}}
					</a>
                    </div>
                    <div data-v-52f556ac="" data-v-6fab7655="" class="_form">
                        <div data-v-52f556ac="" data-v-6fab7655="" class="van-cell-group van-hairline--top-bottom">
                            <div data-v-52f556ac="" data-v-6fab7655="" class="_item _form_item">
                                <div data-v-52f556ac="" class="van-cell van-cell--required van-field"
                                     data-v-6fab7655="">
                                    <div class="van-cell__title van-field__label">
									<span>
										{{trans('ag.red_green')}}
									</span>
                                    </div>
                                    <div class="van-cell__value">
                                        <div class="van-field__body">
                                            <input name="rate" type="text" placeholder="{{trans('ag.rate')}}" class="van-field__control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="color: red;margin-left:20px;margin-top:10px;">{{trans('ag.self_rate')}}{{$user['rebate_rate']}}，{{trans('ag.member_rate')}}0.1-{{$user['rebate_rate']}}</div>
                        <div data-v-52f556ac="" data-v-6fab7655="" class="_btn">
                            <button data-v-52f556ac="" data-v-6fab7655="" class="van-button van-button--danger van-button--normal van-button--block btn-submit">
                                <span data-v-52f556ac="" data-v-6fab7655="" class="van-button__text">
                                    {{trans('ag.create_link')}}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                <!---->
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
    </script>
@endsection
