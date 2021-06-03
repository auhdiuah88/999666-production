@extends('ag.main')

@section('content')
    <div class="u_codeedit">
        <div class="newTab">
            <a ng-class="{curr: type === 0}" ng-click="toggleTime(0)" class="curr">今天</a>
            <a ng-class="{curr: type === 1}" ng-click="toggleTime(1)">昨天</a>
            <a ng-class="{curr: type === 2}" ng-click="toggleTime(2)">本月</a>
            <a ng-class="{curr: type === 3}" ng-click="toggleTime(3)">上月</a>
        </div>
        <ul class="todayView mgb10">
            <input type="text" placeholder="下级报表查询" class="userInput ng-pristine ng-untouched ng-valid ng-empty" ng-model="name.username">
            <a class="submitBtn ClickShade" ng-click="searchBtn(name.username)">搜索</a>
        </ul>
        <div class="code_cont" style="">
            <ul class="plMore">
                <li>
                    <em class="ng-binding">¥0.00</em>
                    <span>投注金额</span>
                </li>
                <li>
                    <em class="ng-binding">¥0.00</em>
                    <span>中奖金额</span>
                </li>
                <li>
                    <em class="ng-binding">¥0.00</em>
                    <span>活动礼金</span>
                </li>
                <li>
                    <em class="ng-binding">¥0.00</em>
                    <span>团队返点</span>
                </li>
                <li>
                    <em class="ng-binding">¥0.00</em>
                    <span>团队盈利</span>
                </li>
            </ul>
            <ul class="plMore">
                <li>
                    <em class="ng-binding">0人</em>
                    <span>投注人数</span>
                </li>
                <li>
                    <em class="ng-binding">0人</em><span>首充人数</span>
                </li>
                <li>
                    <em class="ng-binding">0人</em>
                    <span>注册人数</span>
                </li>
                <li>
                    <em class="ng-binding">0人</em>
                    <span>下级人数</span>
                </li>
                <li>
                    <em class="ng-binding">¥1000000.00</em>
                    <span>团队余额</span>
                </li>
            </ul>
            <ul class="plMore">
                <li>
                    <em class="ng-binding">¥0.00</em>
                    <span>充值金额</span>
                </li>
                <li>
                    <em class="ng-binding">¥0.00</em>
                    <span>提现金额</span>
                </li>
                <li>
                    <em class="ng-binding">¥0.00</em>
                    <span>代理返点</span>
                </li>
                <li>
                    <em class="ng-binding">¥0.00</em>
                    <span>代理返点</span>
                </li>
                <!-- ngIf: reportData.ky_win --><li ng-if="reportData.ky_win" class="ng-scope">
                    <em class="ng-binding">¥0.00</em>
                    <span>团队开元盈利</span>
                </li><!-- end ngIf: reportData.ky_win -->
                <!-- ngIf: !reportData.ky_win -->
            </ul>
            <!-- ngIf: reportData.ag_win --><ul class="plMore ng-scope" ng-if="reportData.ag_win">
                <li>
                    <em class="ng-binding">¥0.00</em>
                    <span>团队ag盈利</span>
                </li>
                <li>
                    <em></em>
                    <span></span>
                </li>
                <li>
                    <em></em>
                    <span></span>
                </li>
                <li>
                    <em></em>
                    <span></span>
                </li>
                <li>
                    <em></em>
                    <span></span>
                </li>
            </ul><!-- end ngIf: reportData.ag_win -->
        </div>
    </div>
@endsection
