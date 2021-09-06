<?php
return [
    //V8配置参数
    //测试
//    "v8" => [
//        "game_name" => "v8",
//        "url" => 'https://wc2-api.twow42.com/channelHandle',//接口地址
//        "agent" => "70241",//代理ID
//        "deskey" => "276701EA9E3785BA",//des加密KEY
//        "md5key" => "2B038373A767578B",//MD5加密KEY
//    ],
    //正式
    "v8" => [
        "game_name" => "v8",
        "url" => 'https://api.zms32.com/channelHandle',//接口地址
        "agent" => "80349",//代理ID
        "deskey" => "A58E5F1B0B7BA299",//des加密KEY
        "md5key" => "096730E63E1AA00B",//MD5加密KEY
    ],

    //pg
    //测试
//    "pg" => [
//        "game_name" => "pg",
//        "PgSoftAPIDomain" => "https://api.pg-bo.me/external/",
//        "DataGrabAPIDomain" => "https://api.pg-bo.me/external-datagrabber/",
//        "PgSoftPublicDomain" => "https://m.pg-redirect.net/",
//        "operator_token" => "28f461b94907603fc57272a9bfdc3610",
//        "secret_key" => "9ab57402eddd0237865545da062abb08",
//        "salt" => "da73af656d9f64038b7e9141d03fc98e"
//    ],
    //正式
    "pg" => [
        "game_name" => "pg",
        "PgSoftAPIDomain" => "https://api.pg-bo.net/external/",
        "DataGrabAPIDomain" => "https://api.pg-bo.net/external-datagrabber/",
        "PgSoftPublicDomain" => "https://m.pgjksonc.club/",
        "operator_token" => "8AF88208-8A51-4EE3-803C-F2FCBEAB91BC",
        "secret_key" => "CD9CFC07D28B40F09B77C617B2A17840",
        "salt" => "98D32026428B436AA138D6FF8300ED"
    ],

    //ICG
    //测试
//    "icg" => [
//        "game_name" => "icg",
//        "url" => "https://admin-stage.iconic-gaming.com/service/",
//        "username" => "ICGx68TBSAPI1688",
//        "password" => "123456",
//    ],
    //正式
    "icg" => [
        "game_name" => "icg",
        "url" => "https://admin.iconic-gaming.com/service/",
        "username" => "ICGqqwwee1688",
        "password" => "zzxxcc1688S",
    ],

    //WBET
    //测试
    "wbet" => [
        "game_name" => "wbet",
        "url" => "https://wbapi.uat0011.com/",
        "providercode" => "WB",
        "Key" => "7EDB68B0112940F786A2015A5693E935",
        "operator_id" => "gstsseab",
    ],
    //正式
//    "wbet" => [
//        "game_name" => "wbet",
//        "url" => "https://wbapi.uat0011.com/",
//        "providercode" => "WB",
//        "Key" => "7EDB68B0112940F786A2015A5693E935",
//        "operator_id" => "gstsseab",
//    ],
];
