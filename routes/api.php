<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get("/test", "TestController@test2");
Route::get("/test2", "TestController@test");
Route::get("/testGame", "TestController@openGame");
Route::get("/openBetting", "TestController@openGameBetting");
Route::post("/makeSign", "TestController@makeSign");
//Route::post("/makeGameResult", "TestController@getGameResult");


Route::post("/login", "Api\UserController@Login");
Route::post("/register", "Api\UserController@Register");
Route::get('/settlement_queue', "Game\GameController@Settlement_Queue");
Route::get('/settlement_queue_test', "Game\GameController@Settlement_Queue_Test");

// 充值回调
Route::any('/recharge_callback', "Api\RechargeController@rechargeCallback");
// 提款回调
Route::any('/withdrawal_callback', "Api\WithdrawalController@withdrawalCallback");

Route::group(["namespace" => "Api"], function () {
    Route::post("/sendCode", "UserController@sendMessage");
    Route::post("/groupUrl", "SystemController@getWhatsAppGroupUrl"); // 获取群组URL，首页的客服按钮
    Route::post("/serviceUrl", "SystemController@getWhatsServiceUrl"); // 获取专属客服URL，个人中心客服按钮
});

Route::group(['middleware' => ['user_token']], function () {
    Route::post('/game_start', "Game\GameController@Game_Start");
    Route::post('/betting', "Game\GameController@Betting");
    Route::post('/betting_list', "Game\GameController@Betting_List");
    Route::post('/game_list', "Game\GameController@Game_List");

});

Route::group(["namespace" => "Api", 'middleware' => ['user_token']], function () {

    Route::group(["prefix" => "user"], function () {
        Route::get("/info", "InfoController@getInfo"); // 查询用户基本信息
        Route::post("/nickname", "InfoController@updateNickname"); // 修改用户昵称 参数: nickname
        Route::post("/password", "InfoController@updatePassword"); // 修改用户密码 参数: o_password f_password l_password

        Route::post("/rechargemethods", "RechargeController@rechargeMethods");  //  用户充值方式

        Route::post("/recharge", "RechargeController@recharge");        //  用户充值-得到充值链接
        Route::post("/rechargelog", "RechargeController@rechargeLog");  //  充值记录
        Route::post("/recommend", "AgentController@getAgentInformation"); // 查询代理
        Route::post("/withdrawal", "WithdrawalController@withdrawalBydai");        //  申请代付提现-请求出金订单
        Route::post("/withdrawalbyupi", "WithdrawalController@withdrawalByUpiID");  //  申请paytm-upi_id提现-请求出金订单
        Route::post("/extension", "AgentController@getExtensionUser");    // 促销记录
    });
    Route::group(["prefix" => "bank"], function () {
        Route::get("/findAll", "InfoController@getBanks"); // 查询用户银行卡
        Route::get("/findById", "InfoController@getBankById"); // 根据ID查询用户银行卡 参数: id(银行卡ID)
        Route::post("/add", "InfoController@addBank"); // 添加用户银行卡
        Route::post("/edit", "InfoController@editBank");// 编辑用户银行卡
        Route::post("/del", "InfoController@delBank"); // 删除用户银行卡
    });

    Route::group(["prefix" => "address"], function () {
        Route::get("/findAll", "AddressController@findAll"); // 查询用户所有地址
        Route::get("/findById", "AddressController@findById"); // 根据ID查询用户地址
        Route::post("/add", "AddressController@addAddress");// 添加用户地址
        Route::post("/edit", "AddressController@editAddress");// 编辑用户地址
        Route::post("/del", "AddressController@delAddress");// 删除用户地址
    });

    Route::group(["prefix" => "withdrawal"], function () {
        Route::get("/record", "WithdrawalController@getRecords");
        Route::post("/extract", "WithdrawalController@addRecord");
        Route::post("/message", "WithdrawalController@getMessage");
        Route::post("/agent/extract", "WithdrawalController@agentWithdrawal");
        Route::post("/agent/record", "WithdrawalController@getAgentWithdrawalRecord");
        Route::post("/agent/reward", "WithdrawalController@getAgentRewardRecord");
        Route::get("/type", "WithdrawalController@withdrawType");
    });

    // 活动
    Route::group(["prefix" => "activity"], function () {
        Route::get("tasks", "ActivityController@taskList");                     // 任务列表
        Route::post("task_reward_get", "ActivityController@taskRewardGet");     // 获得任务奖励 (红包礼金)
//        Route::get("finance/getbalance", "ActivityController@signInGetMoneyList"); // 我的余额
        Route::post("sign/info", "ActivityController@signInfo");                  // 我的签到包购买信息
        Route::get("sign/packages", "ActivityController@signInGetMoneyList");     // 每日签到回扣包项目列表
        Route::post("sign/buypackage", "ActivityController@buySignInGetMoney");   // 购买每日签到回扣包
        Route::post("sign/getmoney", "ActivityController@doGetMoney");            // 点击每日签到领取回扣
        Route::post("sign/getpackagereceiveinfo", "ActivityController@getPackageReceiveInfo"); // 最近的用户领取回扣记录
    });
});



