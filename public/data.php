
<?php

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$num = mt_rand(1000, 9999);

//必须以data:开头，\n\n结尾
echo "data: {$num}\n\n";

//刷新缓存
ob_flush();
flush();