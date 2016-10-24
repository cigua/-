<?php

	 namespace Home\Model;
	 use  	   Think\Model\RelationModel;

	 class UserModel extends RelationModel

	 {
	 	protected  $patchValidate=true;

	 	protected $_validate =[

	 		['account','require','账户不允许为空',0],
	 		['account','5,18','账户长度应在5-18区间',0,'length'],
	 		['pwd','require','密码不能为空',0],
	 		['pwd','checkpwd','密码开头必须是字母，且是字母和数字的结合',0,'callback'],
	 		['pwded','pwd','密码必须保持一致',0,'confirm'],
	 		['pwded','require','请输入确认密码',0],
	 		['uname','require','昵称不能为空',0],
	 		['uname','3,10','昵称长度应在3-10字符区间',0,'length'],
	 		['verify','require','验证码不能为空',0]
	 	];
	 	//验证密码是否符合规则
	 	protected  function checkpwd($pwd)

	 	{	 		
	 		$pattern='/^[a-zA-Z][1-9_]{4-17}$/';

	 		if(preg_match($pattern, $pwd))

	 		{
	 			return true;
	 		}
	 	}



			 //定义主表名称
			Protected $tableName = 'user';
			
			//定义用户与用户信息处理表关系属性
			Protected $_link = array(
				'userinfo' =>array(
					'mapping_type'=>self::HAS_ONE,
					'foreign_key'=> 'uid',
				)	
			);
			
	 		public function insert($data=NULL)
	 	{
			$data= is_null($data) ? $_POST : $data;
			return $this->relation(true)->data($data)->add();
		}




	 }