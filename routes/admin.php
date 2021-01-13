<?php

use Illuminate\Http\Request;
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


//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
//


// 登录退出接口
Route::any('/admin_login', 'Admin\AdminController@Login')->middleware(['admin_handle']);
Route::post("/admin_out", 'Admin\AdminController@Out')->middleware(['admin_handle']);


// 实时更新最新数据
Route::get("/period/newests", "Admin\PeriodController@syncInRealtime");
Route::get("/betting/newests", "Admin\BettingController@syncInRealtime");
Route::get("/withdrawal/auditlist", "Admin\WithdrawalController@syncInRealtime");
Route::get("/withdrawal/auditnotice", "Admin\WithdrawalController@syncInRealtimeNotice");

Route::group(['middleware' => ['token', "auth"]], function () {
    //文件上传
    Route::post("/upload", "Admin\UploadController@upload");
});

Route::group(['middleware' => ['token', "auth", 'admin_handle']], function () {
    //操作日志查询
    Route::get("/log/adminList", "Admin\AdminLogController@list");

    Route::post('/get_prize_opening_data', "Game\GameController@Get_Prize_Opening_Data");
    Route::post('/sd_pize_opening', "Game\GameController@Sd_Prize_Opening");

    // 角色管理相关接口
    Route::get("/role/findAll", "Admin\RoleController@FindAll");
    Route::get("/role/findById", "Admin\RoleController@FindById");
    Route::post("/role/add", "Admin\RoleController@Add");
    Route::post("/role/edit", "Admin\RoleController@Edit");
    Route::post("/role/del", "Admin\RoleController@Del");

    // 管理员管理相关接口
    Route::get("/admin/findAll", "Admin\AdminController@FindAll");
    Route::get("/admin/findById", "Admin\AdminController@FindById");
    Route::post("/admin/add", "Admin\AdminController@Add");
    Route::post("/admin/edit", "Admin\AdminController@Edit");
    Route::post("/admin/del", "Admin\AdminController@Del");
    Route::post("/admin/prohibition", "Admin\AdminController@Prohibition");
    Route::post("/admin/relieve", "Admin\AdminController@Relieve");
    Route::get("/menu", "Admin\AdminController@Menu");
    Route::post("/admin/customer", "Admin\AdminController@UpdateCustomerStatus");


    // 权限管理相关接口
    Route::get("/right/findAll", "Admin\JurisdictionController@FindAll");
    Route::get("/right/findById", "Admin\JurisdictionController@FindById");
    Route::post("/right/add", "Admin\JurisdictionController@Add");
    Route::post("/right/edit", "Admin\JurisdictionController@Edit");
    Route::get("/right/all", "Admin\JurisdictionController@All");
    Route::get("/right_all", "Admin\JurisdictionController@RightAll");

    Route::group(["namespace" => "Admin"], function () {
        // 提现记录管理
        Route::group(["prefix" => "withdrawal"], function () {
            Route::get("/findAll", "WithdrawalController@findAll");
            Route::post("/audit", "WithdrawalController@auditRecord");
            Route::post("/search", "WithdrawalController@searchRecord");
            Route::post("/failure", "WithdrawalController@batchFailureRecord");
            Route::post("/pass", "WithdrawalController@batchPassRecord");
            Route::post("/cancel", "WithdrawalController@cancellationRefund");
        });

        // 银行卡管理
        Route::group(["prefix" => "bank"], function () {
            Route::get("/findAll", "BankController@findAll");
            Route::get("/findById", "BankController@findById");
            Route::post("/add", "BankController@addBank");
            Route::post("/edit", "BankController@editBank");
            Route::post("/del", "BankController@delBank");
            Route::post("/search", "BankController@searchBank");
        });

        // 用户管理
        Route::group(["prefix" => "user"], function () {
            Route::get("/findAll", "UserController@findAll");
            Route::get("/findById", "UserController@findById");
            Route::post("/add", "UserController@addUser");
            Route::post("/edit", "UserController@editUser");
            Route::post("/del", "UserController@delUser");
            Route::post("/search", "UserController@searchUser");
            Route::post("/modify", "UserController@batchModifyRemarks");
            Route::get("/customer", "UserController@getCustomerService");
            Route::post("/customer/modify", "UserController@modifyCustomerService");
            Route::post("/status", "UserController@modifyUserStatus");
            Route::get("/recommend", "UserController@getRecommenders");
            Route::post("/gift", "UserController@giftMoney");
            Route::post("/up", "UserController@upperSeparation");
            Route::post("/down", "UserController@downSeparation");
            Route::post("/logs", "UserController@getBalanceLogs");

            Route::get("/findCustomerServiceByPhone", "UserController@findCustomerServiceByPhone");
            Route::post("/editFakeBettingMoney", "UserController@editFakeBettingMoney");
            Route::group(["prefix" => "groupLeader"], function () {
                Route::post("/add", "LeaderController@add");
                Route::get("/list", "LeaderController@list");
                Route::post("/del", "LeaderController@logicDel");
                Route::post("/edit", "LeaderController@edit");
                Route::get("/searchAccount", "LeaderController@searchAccount");
                Route::post("/bindAccount", "LeaderController@bindAccount");
            });
        });

        // 用户下注信息
        Route::group(["prefix" => "wager"], function () {
            Route::get("/findAll", "WagerController@findAll");
            Route::post("/search", "WagerController@searchWager");
        });

        // 首充列表
        Route::group(["prefix" => "firstCharge"], function () {
            Route::get("/findAll", "FirstChargeController@findAll");
            Route::post("/search", "FirstChargeController@searchChargeLogs");
        });

        // 充值列表
        Route::group(["prefix" => "recharge"], function () {
            Route::get("/findAll", "RechargeController@findAll");
            Route::post("/search", "RechargeController@searchRechargeLogs");
            Route::post("/user", "RechargeController@getUser");
        });

        // 分佣列表
        Route::group(["prefix" => "charge"], function () {
            Route::get("/findAll", "ChargeController@findAll");
            Route::post("/search", "ChargeController@searchChargeLogs");
        });

        // 提佣列表
        Route::group(["prefix" => "commission"], function () {
            Route::get("/findAll", "CommissionController@findAll");
            Route::post("/search", "CommissionController@searchCommission");
        });

        // 签到列表
        Route::group(["prefix" => "sign"], function () {
            Route::get("/findAll", "SignController@findAll");
            Route::post("/search", "SignController@searchSignLogs");
        });

        // 裂变红包列表
        Route::group(["prefix" => "envelope"], function () {
            Route::get("/findAll", "EnvelopeController@findAll");
            Route::post("/search", "EnvelopeController@searchEnvelope");
        });

        // 活动列表
        Route::group(["prefix" => "period"], function () {
//            Route::get("/newests", "PeriodController@syncInRealtime");
            Route::get("/findAll", "PeriodController@findAll");
            Route::get("/findById", "PeriodController@findById");
            Route::post("/search", "PeriodController@searchPeriod");
        });

        // 订单列表
        Route::group(["prefix" => "betting"], function () {
            Route::get("/findAll", "BettingController@findAll");
            Route::post("/search", "BettingController@searchBettingLogs");
            Route::get("/statistics", "BettingController@statisticsBettingLogs");
        });

        // 会员当日利差
        Route::group(["prefix" => "spread"], function () {
            Route::get("/profit", "SpreadController@getProfitList");
            Route::get("/loss", "SpreadController@getLossList");
        });

        // 客服业绩报表
        Route::group(["prefix" => "report"], function () {
            Route::get("/findAll", "ReportController@findAll");
            Route::post("/search", "ReportController@searchReport");
        });

        // 账号管理
        Route::group(["prefix" => "account"], function () {
            Route::get("/findAll", "AccountController@findAll");
            Route::get("/findById", "AccountController@findById");
            Route::post("/add", "AccountController@addAccount");
            Route::post("/edit", "AccountController@editAccount");
            Route::post("/bind", "AccountController@bindAccount");
            Route::post("/del", "AccountController@delAccount");
            Route::post("/search", "AccountController@searchAccount");
            Route::post("/showData", "AccountController@showData");
            Route::post("/frozen", "AccountController@frozenAccount");
            Route::post("/disFrozen", "AccountController@disFrozenAccount");
        });

        // ip白名单管理
        Route::group(["prefix" => "ip"], function () {
            Route::get("/findAll", "WhiteListController@findAll");
            Route::get("/findById", "WhiteListController@findById");
            Route::post("/add", "WhiteListController@addIp");
            Route::post("/edit", "WhiteListController@editIp");
            Route::post("/del", "WhiteListController@delIp");
        });

        // 首页统计
        Route::group(["prefix" => "home"], function () {
            Route::get("/findAll", "HomeController@findAll");
            Route::post("/search", "HomeController@searchHome");
            Route::post("/systemTime", "HomeController@getSystemTime");
        });

        // 系统配置
        Route::group(["prefix" => "system"], function () {
            Route::get("/findAll", "SystemController@findAll");
            Route::post("/edit", "SystemController@editSystem");
            Route::get("/staffRole","SettingController@staffId");
            Route::post("/staffRole","SettingController@setStaffId");
            Route::get("/gameRule","SettingController@gameRule");
            Route::post("/gameRule","SettingController@setGameRule");
            Route::get("/withdrawConfig","SettingController@withdrawConfig");
            Route::post("/withdrawConfig","SettingController@setWithdrawConfig");
            Route::get("/rechargeConfig","SettingController@rechargeConfig");
            Route::post("/rechargeConfig","SettingController@setRechargeConfig");
            Route::get("/leaderRole","SettingController@getGroupLeaderRoleId");
            Route::post("/leaderRole","SettingController@saveGroupLeaderRoleId");
            Route::get("/h5Alert","SettingController@h5AlertContent");
            Route::post("/h5Alert","SettingController@setH5AlertContent");
            Route::post("/service","SettingController@serviceEdit");
            Route::get("/service","SettingController@getService");
            Route::post("/crisp","SettingController@crispSave");
            Route::get("/crisp","SettingController@getCrisp");
        });

        // 后台赠金记录列表
        Route::group(["prefix" => "gift"], function () {
            Route::get("/findAll", "GiftController@findAll");
            Route::post("/search", "GiftController@searchGiftLogs");
        });

        // 上下分记录列表
        Route::group(["prefix" => "portion"], function () {
            Route::get("/findAll", "UpDownController@findAll");
            Route::post("/search", "UpDownController@searchUpAndDownLogs");
        });

        //代理=staff
        Route::group(["prefix" => "agent"], function(){
            Route::get("/home","agent\AgentDataController@index");
            Route::group(["prefix" => "user"], function(){
                Route::post("/search","agent\AgentUserController@index");
                Route::post("/firstRecharge","agent\AgentUserController@firstRechargeList");
                Route::post("/orderInfo","agent\AgentUserController@orderInfo");
            });
            Route::get("/backCards","agent\AgentBankCardController@backCardList");
            //财务信息
            Route::group(['prefix' => 'finance'], function(){
                Route::post("/recharge","agent\AgentFinanceController@rechargeList");
                Route::post("/withdraw","agent\AgentFinanceController@withdrawList");
                Route::post("/commission","agent\AgentFinanceController@commissionList");
                Route::post("/signIn","agent\AgentFinanceController@signInList");
                Route::post("/envelope","agent\AgentFinanceController@envelopeList");
                Route::post("/bonus","agent\AgentFinanceController@bonusList");
                Route::post("/upAndDown","agent\AgentFinanceController@upAndDownList");
            });
            //统计报表
            Route::group(['prefix' => 'statistical'], function(){
                Route::post('dailyWinRank', "agent\AgentStatisticalReportController@dailyWinRank");
                Route::post('dailyLoseRank', "agent\AgentStatisticalReportController@dailyLoseRank");
            });
            Route::group(["prefix" => "order"], function(){
                Route::get("/index","agent\AgentBettingController@orders");
                Route::get("/statistic","agent\AgentBettingController@statistic");
            });

            Route::get("/inviteInfo","agent\AgentDataController@inviteInfo");

            Route::get("/staffLists","agent\AgentStaffController@staffLists");
        });

        // banner设置
        Route::group(["prefix" => "banner"], function () {
            Route::get("/index", "BannerController@index");
            Route::post("/add", "BannerController@add");
            Route::post("/del", "BannerController@del");
            Route::post("/save", "BannerController@save");
        });

        Route::group(["prefix" => "goods"], function(){
            Route::post("/add","ProductController@add");
            Route::get("/","ProductController@lists");
            Route::post("/update","ProductController@update");
            Route::post("/edit","ProductController@edit");
            Route::get("/detail","ProductController@detail");
            Route::post("/delete","ProductController@del");
        });
    });
});

