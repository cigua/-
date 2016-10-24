<?php
return array(
	//'配置项'=>'配置值'
	'URL_MODEL' => 2, // 重写模式, 生成的URL中不包含index.php
	'URL_ROUTER_ON'         =>  true,   // 是否开启URL路由
    'URL_ROUTE_RULES'       =>  array(
    	// 什么样的URL 对应 哪个控制器的动作
    	'register' 		=> 		'Login/register',
    	'checkverify' 	=> 	    'Login/checkVerify',
    	'checkAccount' 	=> 	    'Login/checkAccount',
    	'checkUname' 	=> 	    'Login/checkUname',
        ':id\d'         =>      'User/index',
        'follow/:uid\d' =>      array('User/followList','type=1'),
        'fans/:uid\d'   =>      array('User/followList','type=0')

               
    	
    ), // 默认路由规则 针对模块
);