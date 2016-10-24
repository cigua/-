<?php

	 namespace Home\Controller;
	 use Think\Controller;

	 class UserSettingController extends  Controller

	 {
	 	//获取用户的个人信息，并展示页面需要的字段
	 	public  function  indexAction()

	 	{
	 		$m_userinfo=M('Userinfo');
	 		$where=['uid'=>$_SESSION['uid']];
	 		/*var_dump($where);*/
	 		$field=['id','username','truename','sex','location','constellation','intro','face180'];
	 		$user=$m_userinfo->where($where)->field($field)->find();
	 		/*header('content-type:text/html;charset=utf8');
	 		var_dump($user);		
	 		var_dump($_SESSION['uid']);
	 		exit;*/
	 		$this->user=$user;
	 		$this->display();
	 	}

	 	public  function editAction()

	 	{
	 		$uid=$_SESSION['uid'];
	 		$province=I('province');
	 		$city=I('city');
	 		$location=$province.'|'.$city;
	 		$data=
	 			[
	 				'username' 		=>I('nickname'),
	 				'truename' 		=>I('truename'),
	 				'sex'			=>I('sex'),
	 				'location' 		=>$location,
	 				'constellation' =>I('night'),
	 				'intro'		=>I('intro'),
	 				'uid'			=>$uid
	 			];
	 		
	 		$m_userinfo=M('userinfo');
	 		$where=['uid'=>$uid]; 		
	 		$ui_id=$m_userinfo->where($where)->save($data);
	 		/*header('content-type:text/html;charset=utf8');
	 		echo $m_userinfo->getLastSQL();
	 		exit;*/
	 		if($ui_id){

	 			redirect(U('index'));
	 		}else{

	 			$this->error('资料更新失败');
	 		}
	 	}

	 	public function editPwdAction()

	 	{
	 		$uid=$_SESSION['uid'];

	 		$m_user=M('User');

	 		$where=['id'=>$uid];

	 		$user_pdw=$m_user->where($where)->field('password')->find();

	 		$old_pdw=md5(I('old'));
	 		
	 		if(!($user_pdw['password'] == $old_pdw)){
	 			$this->error('密码错误请重新输入');
	 		} 		
	 		$new_pdw=md5(I('new'));
	 		$newed_pdw=md5(I('newed'));
	 		if(!($new_pdw ==$newed_pdw)){
	 			$this->error('两次输入的密码不一致，请重新输入');
	 		}
	 		$data=['password'=>$new_pdw];
	 		$where=['id'=>$uid];
	 		
	 		
	 		$res=$m_user->where($where)->save($data);
	 		
	 		if($res){

	 			$this->success('密码修改成功',U('UserSetting/index'));
	 		}else{

	 			$this->error('密码修改失败',U('UserSetting/index'));
	 		}
	 		
	 	}


	/*
	 * 修改用户头像
	*/
	public function editFaceAction(){
		if(!IS_POST)$this->error('页面不存在');
		$db = M('userinfo');
		$where = array('uid'=>session('uid'));
		$field = array('face50','face80','face180');
		$old=$db->where($where)->field($field)->find();
		if ($db->where($where)->save($_POST)){
			if (!empty($old['face180'])){
				@unlink('./Uploads/Face/'.$old['face180']);
				@unlink('./Uploads/Face/'.$old['face80']);
				@unlink('./Uploads/Face/'.$old['face50']);
			}
			$this->success('修改成功',U('index'));
		}else {
			$this->error('修改失败，请重试...');
		}
	}


	}
