@extends('ag.main')

@section('content')
    <div class="newTab">
        <a ng-class="{curr: type === 0}" ng-click="toggleType(0)" class="curr">下级开户</a>
        <a ng-class="{curr: type === 1}" ng-click="toggleType(1)">邀请码管理</a>
    </div>
    <div class="TabLi ng-scope" ng-if="type === 0">
        <ul class="searchFirst">
            <li>
                <span>开户类型：</span>
                <a class="userSearch" ng-class="{active: accountType === 2}" ng-click="toggleAccountType(2)">代理</a>
                <a class="userSearch active" ng-class="{active: accountType === 1}" ng-click="toggleAccountType(1)">玩家</a>
                <br> 返点设置：请先为下级设置返点。
                <a class="rebateDesLink" ng-href="/odds_table" target="_blank" href="/odds_table">点击查看返点赔率表</a>
            </li>
        </ul>
        <div class="bonusTable">
            <!-- ngRepeat: item in lotteryPoint track by $index --><ul class="clearfix ng-scope" ng-repeat="item in lotteryPoint track by $index" id="inputNum">
                <li class="ng-binding">快3</li>
                <li>
                    <input type="number" name="k3" title="快3" min="0.1" step="0.1" max="8.5" class="userInput mgl20">
                    &nbsp;
                    <span class="ng-binding">（自身返点8.5，可为下级设置返点范围0.1-8.5）</span>
                </li>
            </ul><!-- end ngRepeat: item in lotteryPoint track by $index --><ul class="clearfix ng-scope" ng-repeat="item in lotteryPoint track by $index" id="inputNum">
                <li class="ng-binding">时时彩</li>
                <li>
                    <input type="number" name="ssc" title="时时彩" min="0.1" step="0.1" max="8.0" class="userInput mgl20">
                    &nbsp;
                    <span class="ng-binding">（自身返点8.0，可为下级设置返点范围0.1-8.0）</span>
                </li>
            </ul><!-- end ngRepeat: item in lotteryPoint track by $index --><ul class="clearfix ng-scope" ng-repeat="item in lotteryPoint track by $index" id="inputNum">
                <li class="ng-binding">11选5</li>
                <li>
                    <input type="number" name="11x5" title="11选5" min="0.1" step="0.1" max="7.5" class="userInput mgl20">
                    &nbsp;
                    <span class="ng-binding">（自身返点7.5，可为下级设置返点范围0.1-7.5）</span>
                </li>
            </ul><!-- end ngRepeat: item in lotteryPoint track by $index --><ul class="clearfix ng-scope" ng-repeat="item in lotteryPoint track by $index" id="inputNum">
                <li class="ng-binding">六合彩</li>
                <li>
                    <input type="number" name="lhc" title="六合彩" min="0.1" step="0.1" max="10.0" class="userInput mgl20">
                    &nbsp;
                    <span class="ng-binding">（自身返点10.0，可为下级设置返点范围0.1-10.0）</span>
                </li>
            </ul><!-- end ngRepeat: item in lotteryPoint track by $index --><ul class="clearfix ng-scope" ng-repeat="item in lotteryPoint track by $index" id="inputNum">
                <li class="ng-binding">pk拾</li>
                <li>
                    <input type="number" name="pk10" title="pk拾" min="0.1" step="0.1" max="8.0" class="userInput mgl20">
                    &nbsp;
                    <span class="ng-binding">（自身返点8.0，可为下级设置返点范围0.1-8.0）</span>
                </li>
            </ul><!-- end ngRepeat: item in lotteryPoint track by $index --><ul class="clearfix ng-scope" ng-repeat="item in lotteryPoint track by $index" id="inputNum">
                <li class="ng-binding">福彩3D</li>
                <li>
                    <input type="number" name="fc3d" title="福彩3D" min="0.1" step="0.1" max="7.5" class="userInput mgl20">
                    &nbsp;
                    <span class="ng-binding">（自身返点7.5，可为下级设置返点范围0.1-7.5）</span>
                </li>
            </ul><!-- end ngRepeat: item in lotteryPoint track by $index --><ul class="clearfix ng-scope" ng-repeat="item in lotteryPoint track by $index" id="inputNum">
                <li class="ng-binding">排列三</li>
                <li>
                    <input type="number" name="pl3" title="排列三" min="0.1" step="0.1" max="7.5" class="userInput mgl20">
                    &nbsp;
                    <span class="ng-binding">（自身返点7.5，可为下级设置返点范围0.1-7.5）</span>
                </li>
            </ul><!-- end ngRepeat: item in lotteryPoint track by $index -->
        </div>
        <a class="submitBtn" ng-click="submitBtn()">生成邀请码</a>
        <div class="userTip mg30">
            <p>※ 温馨提示：
                <br> 1、不同的返点赔率不同，返点越高赔率越高。
                <br> 2、代理可获得的佣金等于代理自身返点与下级返点的差值，如代理自身返点6，下级返点4，代理可获得下级投注金额的2%，即下级下注100元，代理可获得2元。
                <br> 3、下级返点值设得越低，下级的赔率就越低，建议给下级设置的返点不要过低。
            </p>
        </div>
    </div>
{{--    <div class="TabLi ng-scope" ng-if="type === 1">--}}
{{--        <ul class="searchFirst">--}}
{{--            <li>--}}
{{--                <span>开户类型：</span>--}}
{{--                <a class="userSearch" ng-class="{active: codeType === 2}" ng-click="toggleCodeType(2)">代理</a>--}}
{{--                <a class="userSearch active" ng-class="{active:codeType === 1}" ng-click="toggleCodeType(1)">玩家</a>--}}
{{--            </li>--}}
{{--        </ul>--}}
{{--        <div class="invitationCode">--}}
{{--            <table width="100%" border="0" margin="0" padding="0" cellspacing="0" cellpadding="0" class="manageInvite">--}}
{{--                <tbody><tr>--}}
{{--                    <th>邀请码</th>--}}
{{--                    <th>注册链接</th>--}}
{{--                    <th>备注</th>--}}
{{--                    <th>生成时间</th>--}}
{{--                    <th>状态</th>--}}
{{--                    <th>操作</th>--}}
{{--                </tr>--}}
{{--                <!-- ngRepeat: item in codeList track by $index --><tr ng-repeat="item in codeList track by $index" class="ng-scope">--}}
{{--                    <td>--}}
{{--                        <input type="text" class="code" value="82509635">--}}
{{--                        <span class="copybtn" ng-click="copyLink($event)">复制</span>--}}
{{--                    </td>--}}
{{--                    <td>--}}
{{--                        <input class="copyLink" type="text" value="cpzj158.com/register?intr=82509635">--}}
{{--                        <span class="copybtn" ng-click="copyLink($event)">复制</span>--}}
{{--                    </td>--}}
{{--                    <td>--}}
{{--                        <em class="xem">未设置</em>--}}
{{--                    </td>--}}
{{--                    <td class="ng-binding">2021-05-20 19:20:35</td>--}}
{{--                    <td class="ng-binding">注册(0)</td>--}}
{{--                    <td>--}}
{{--                        <a href="javascript:;" class="detail" ng-click="showDetail(item.rebate)">详情</a>--}}
{{--                        |--}}
{{--                        <a class="del" ng-click="delCode($event, item.invite_code)">删除</a>--}}
{{--                    </td>--}}
{{--                </tr><!-- end ngRepeat: item in codeList track by $index -->--}}
{{--                <!-- ngIf: codeList.length === 0 -->--}}
{{--                </tbody></table>--}}
{{--            <div class="page" style="">--}}
{{--                <p>共<em id="codeCount" class="ng-binding">1</em>条记录</p>--}}
{{--                <div id="pageNav" class="pageNav">--}}
{{--                    <ul class="pagination" style="display: none;">--}}
{{--                        <li style="display: none;"><a>上一页</a></li>--}}
{{--                        <li class="active"><a>1</a></li>--}}
{{--                        <li style="display: none;"><a>下一页</a></li>--}}
{{--                    </ul>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="userTip mg30">--}}
{{--                <p><i>!</i>温馨提示：“邀请码” 与 “注册链接” 功能一致，可以使用邀请码，也可以使用注册链接。</p>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
@endsection
