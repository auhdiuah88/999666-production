<?php
return [
    //V8配置参数
    //测试
    "v8" => [
        "game_name" => "v8",
        "url" => 'https://wc2-api.twow42.com/channelHandle',//接口地址
        "agent" => "70241",//代理ID
        "deskey" => "276701EA9E3785BA",//des加密KEY
        "md5key" => "2B038373A767578B",//MD5加密KEY
    ],
    //正式
//    "v8" => [
//          "game_name" => "v8",
//        "url" => 'https://api.zms32.com/channelHandle',//接口地址
//        "agent" => "80349",//代理ID
//        "deskey" => "A58E5F1B0B7BA299",//des加密KEY
//        "md5key" => "096730E63E1AA00B",//MD5加密KEY
//    ],

    //pg
    //测试
    "pg" => [
        "game_name" => "pg",
        "PgSoftAPIDomain" => "https://api.pg-bo.me/external",//用户相关
        "operator_token" => "41f5815fe61969ec245e32b07639de7f",
        "secret_key" => "399c4eeae8536b606659981006b2bd47",
    ],

    //ICG
    //测试
    "icg" => [
        "game_name" => "icg",
        "url" => "https://admin-stage.iconic-gaming.com/service/",
        "username" => "ICGx68TBSAPI1688",
        "password" => "123456",
    ],

    //WBET
    //测试
    "wbet" => [
        "game_name" => "wbet",
        "url" => "https://wbapi.uat0011.com/",
        "providercode" => "WB",
        "Key" => "7EDB68B0112940F786A2015A5693E935",
        "operator_id" => "gstsseAB",
    ],
];
