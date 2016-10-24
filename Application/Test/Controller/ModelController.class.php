<?php

/**
 * 模型测试 控制器类
 */
namespace Test\Controller;

use Think\Controller;


class ModelController extends Controller
{

	/**
	 * 创建模型测试
	 * @return [type] [description]
	 */
	public function createAction()
	{
		// 内置模型
		$m_user_1 = M('User');
		$m_user_2 = M('User');

		// var_dump($m_user_1, $m_user_2);
		
		// 自定义模型
		$m_user_1 = D('User');
		$m_user_2 = D('User');
		var_dump($m_user_1);
		echo '<hr><hr>';
		var_dump($m_user_2);
	}


	public function crudAction()
	{

		$m_user = M('User');

		// 增
		// $data = [
		// 	'email'=>'hello@hello.com',
		// 	'telephone'=>'12345678', 
		// 	];

		// $user_id = $m_user->add($data);
		// var_dump($user_id);
		
 
		// 删
		// $result = $m_user->delete(1);
		// var_dump($result);
		// // 删除 newsletter字段为1记录
		// $result = $m_user
		// 	->where(['newsletter'=>1])
		// 	->delete();
		// var_dump($result);
		 
		 
		 
		// 修改
		// $data = [
		// 	'user_id'=>'3',// 主键
		// 	'telephone'=>'123456789', 
		// 	];
		// $result = $m_user->save($data);
		// var_dump($result);
		// 
		

		// 查询
		$rows = $m_user->select();
		var_dump($rows);
		echo '<hr>';
		$row = $m_user->find(3);
		var_dump($row);
	}


	public function whereAction()
	{


		// string
		$m_user = M('User');
		// $rows = $m_user
		// 		// ->where('newsletter=1')
		// 		// ->where("email='1'1'")
		// 		// ->where(['newsletter'=>'1'])
		// 		// ->where(['newsletter'=>"1'1"])
		// 		->where(['email'=>"1'1"])
		// 		->select();
		// var_dump($rows);

		// $cond['email'] = "hellokang@kang.com";
		// $cond['newsletter'] = 1;// ['eq', 1]
		// $cond['newsletter'] = ['gt', 1];
		// $cond['email'] = ['like', 'hello%'];
		// $cond['newsletter'] = ['between', [1, 10]];
		// $cond['user_id'] = ['in', [1, 2, 3, 4]];
		// $cond['_logic'] = 'OR';
		// 
		$cond1['email'] = ['like', 'hello%'];
		$cond1['newsletter'] = ['between', [1, 10]];
		$cond1['_logic'] = 'OR';

		$cond['user_id'] = ['in', [1, 2, 3, 4]];

		$cond['_complex'] = $cond1;
		$rows = $m_user
				->where($cond)
				->select();
	}
}