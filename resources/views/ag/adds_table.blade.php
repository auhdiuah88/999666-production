<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">

    <title></title>
    <!-- <link rel="stylesheet" href=""> -->

<!--[if IE 9]>
    <link rel="stylesheet" type="text/css" href="{{asset('static/css/ie.css')}}">
    <![endif]-->
    <link href="{{asset('static/css/21ea59.app.css')}}" rel="stylesheet">

</head>
<body>
<!--[if lte IE 9]><h1>{{trans('ag.system_err')}}</h1><![endif]-->
<ui-view style="" class="ng-scope">

    <div class="rebateDes ng-scope" style="width: 1250px !important;">
        <div class="rebateNav">
            <!-- ngRepeat: item in bonusType track by $index -->
            <a ng-repeat="item in bonusType track by $index" ng-class="{active: lotteryType === item.k}" ng-click="toggleLotteryType(item.k)" class="ng-binding ng-scope active">{{trans('ag.red_green')}}</a>
        </div>
        <div class="rebateContent">
            <ul class="rebateTitle" id="textWrap">
                <li>
                    <span>{{trans('ag.result_type')}}</span>
                    <span></span>
                    <span>{{trans('ag.rate')}}</span></li>
                <!-- ngRepeat: item in allData[0].data track by $index -->
                <li ng-repeat="item in allData[0].data track by $index" class="ng-binding ng-scope">0,5</li>
                <!-- end ngRepeat: item in allData[0].data track by $index -->
                <li ng-repeat="item in allData[0].data track by $index" class="ng-binding ng-scope">{{trans('ag.result_lucky')}}</li>
                <!-- end ngRepeat: item in allData[0].data track by $index -->
                <li ng-repeat="item in allData[0].data track by $index" class="ng-binding ng-scope">{{trans('ag.result_number')}}</li>
                <!-- end ngRepeat: item in allData[0].data track by $index -->
                <li ng-repeat="item in allData[0].data track by $index" class="ng-binding ng-scope">{{trans('ag.result_odd_even')}}</li>
            </ul>
            <div class="rebateTableCon clearfix" id="wrap" style="width: 1070px !important;">
                <div class="rebateTable fix" id="content">
                    <!-- ngRepeat: items in allData track by $index -->
                    <!-- ngIf: !isShow -->
                    <ul class="isClass ng-scope" ng-repeat="items in allData track by $index">
                        <li class="ng-binding">8.5</li>
                        <li class="ng-binding ng-scope">1.49</li>
                        <li class="ng-binding ng-scope">4.49</li>
                        <li class="ng-binding ng-scope">8.99</li>
                        <li class="ng-binding ng-scope">1.99</li>
                    </ul>
                    <ul class="isClass ng-scope" ng-repeat="items in allData track by $index">
                        <li class="ng-binding">8</li>
                        <li class="ng-binding ng-scope">1.48</li>
                        <li class="ng-binding ng-scope">4.48</li>
                        <li class="ng-binding ng-scope">8.98</li>
                        <li class="ng-binding ng-scope">1.98</li>
                    </ul>
                    <ul class="isClass ng-scope" ng-repeat="items in allData track by $index">
                        <li class="ng-binding">7</li>
                        <li class="ng-binding ng-scope">1.47</li>
                        <li class="ng-binding ng-scope">4.47</li>
                        <li class="ng-binding ng-scope">8.97</li>
                        <li class="ng-binding ng-scope">1.97</li>
                    </ul>
                    <ul class="isClass ng-scope" ng-repeat="items in allData track by $index">
                        <li class="ng-binding">6</li>
                        <li class="ng-binding ng-scope">1.46</li>
                        <li class="ng-binding ng-scope">4.46</li>
                        <li class="ng-binding ng-scope">8.96</li>
                        <li class="ng-binding ng-scope">1.96</li>
                    </ul>
                    <ul class="isClass ng-scope" ng-repeat="items in allData track by $index">
                        <li class="ng-binding">5</li>
                        <li class="ng-binding ng-scope">1.45</li>
                        <li class="ng-binding ng-scope">4.45</li>
                        <li class="ng-binding ng-scope">8.95</li>
                        <li class="ng-binding ng-scope">1.95</li>
                    </ul>
                    <ul class="isClass ng-scope" ng-repeat="items in allData track by $index">
                        <li class="ng-binding">4</li>
                        <li class="ng-binding ng-scope">1.44</li>
                        <li class="ng-binding ng-scope">4.44</li>
                        <li class="ng-binding ng-scope">8.94</li>
                        <li class="ng-binding ng-scope">1.94</li>
                    </ul>
                    <ul class="isClass ng-scope" ng-repeat="items in allData track by $index">
                        <li class="ng-binding">3</li>
                        <li class="ng-binding ng-scope">1.43</li>
                        <li class="ng-binding ng-scope">4.43</li>
                        <li class="ng-binding ng-scope">8.93</li>
                        <li class="ng-binding ng-scope">1.93</li>
                    </ul>
                    <ul class="isClass ng-scope" ng-repeat="items in allData track by $index">
                        <li class="ng-binding">2</li>
                        <li class="ng-binding ng-scope">1.42</li>
                        <li class="ng-binding ng-scope">4.42</li>
                        <li class="ng-binding ng-scope">8.92</li>
                        <li class="ng-binding ng-scope">1.92</li>
                    </ul>
                    <ul class="isClass ng-scope" ng-repeat="items in allData track by $index">
                        <li class="ng-binding">1</li>
                        <li class="ng-binding ng-scope">1.41</li>
                        <li class="ng-binding ng-scope">4.41</li>
                        <li class="ng-binding ng-scope">8.91</li>
                        <li class="ng-binding ng-scope">1.91</li>
                    </ul>
                    <ul class="isClass ng-scope" ng-repeat="items in allData track by $index">
                        <li class="ng-binding">0</li>
                        <li class="ng-binding ng-scope">1.40</li>
                        <li class="ng-binding ng-scope">4.40</li>
                        <li class="ng-binding ng-scope">8.90</li>
                        <li class="ng-binding ng-scope">1.90</li>
                    </ul>
                </div>
                <div id="sliderWrap">
                    <div id="slider"></div>
                </div>
            </div>
        </div>
    </div>

</ui-view>
<script type="text/javascript" src="{{asset('static/js/7a0e9b.config.js')}}"></script>
<script type="text/javascript" src="{{asset('static/js/a4cc4a.vendor.js')}}"></script>
<script type="text/javascript" src="{{asset('static/js/21ea59.app.js')}}"></script>
<script type="text/javascript" src="{{asset('static/js/jquery-3.6.0.min.js')}}"></script>
</body>
</html>

<script type="text/javascript">

</script>
