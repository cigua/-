<?php

		namespace Home\Controller;

		use Think\Controller;

		class CommonController extends Controller

		{

			public function  _initialize()

			{
				/*
				 *获取用户的COOKIE值，与上次登录的IP进行匹配，成功则调到主页
				 *并获取的用户的信息，看是否账户以及账户是否被锁定
				 *
				 */
				if(isset($_COOKIE['auto']))

				{
					$loogin_poor=explode('|', $_COOKIE['auto']);
					$ip=get_client_ip();
					if($loogin_poor[1]==$ip)
					{

						$account=$loogin_poor[0];
						$where=['account'=>$account];
						$m_user=M('User');
						$user_info=$m_user->where($where)->field(['id','lock'])->find();

						if($user_info && !$user_info['lock'])
						{
							session('uid',$user_info['id']);
							return;

						}
					}
				}

				if(!isset($_SESSION['uid']))
				{
					redirect(U('Login/index'),0,'');

				}


			}

	/*
	 * 头像上传
	 */
	
	//创建异步分组
	public  function addGroupAction()
	{
		if(!IS_AJAX){
			$this->error('页面不存在');
		}

		$name=I('name');
		$m_group=M('group');
		$data=['name'=>$name,'uid'=>$_SESSION['uid']];

		$group_id=$m_group->add($data);
		if($group_id){
			$data=['status'=>1,'msg'=>'更新成功'];
			echo json_encode($data);
		}else{
			$data=['status'=>0,'msg'=>'更新失败'];
			echo json_encode($data);
		}
	}
	//异步好友关注
	public  function  addFollowAction()

	{
		if(!IS_AJAX){
			$this->error('页面不存在');
		}
		$data=['follow'=>I('follow','','intval'),
				'gid'=>I('gid','','intval'),
				'fans'=>$_SESSION['uid']
		];

		
		$m_follow=M('follow');
		if($m_follow->add($data)){

			$m_uinfo=M('userinfo');
			//给关注者粉丝数+1
			//给用户者关注数+1
			$m_uinfo->where(['uid'=>session('uid')])->setInc('follow');
			$m_uinfo->where(['uid'=>$data['follow']])->setInc('fans');
			echo json_encode(array('status'=>'1','msg'=>'关注成功'));
			
		}else{
			echo json_encode(array('status'=>'0','msg'=>'关注失败'));
			
		}
		

	}
	public function uploadFaceAction(){


		 if(!IS_POST)$this->error('页面不存在');

		 $upload =$this->_upload('Face','200,120,70','200,120,70');
		
		 echo json_encode($upload);
		 exit;
		 
	}


	public function uploadPicAction(){
		if(!IS_POST)$this->error('页面不存在');
		$data =$this->_upload('Pic','800,300,150','800,300,150');
		echo json_encode($data);
		exit;


	}
	
	/*
	 * 图片上传处理
	 * @param [String] $path [保存文件夹名称]
	 * @param [String] $width [缩略图宽度,多个用逗号隔开]
	 * @param [String] $height [缩略图宽度,多个用逗号隔开]
	 * @return [Array] $path [图片上传信息]
	 */
	private function _upload($path,$width,$height)
	{
		$upload = new \Think\Upload();// 实例化上传类
		//echo json_encode($upload);
		trace($upload,'状态');

		$upload->maxSize   =     C('UPLOAD_MAX_SIZE') ;				// 设置附件上传大小
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath  =     C('UPLOAD_PATH'). $path . '/'; 	// 设置附件上传根目录
		$upload->saveName  =	array('uniqid','');					//上传文件的保存名称
		$upload->replace   =	true;								//覆盖同名文件
		$upload->exts      =	C('UPLOAD_EXTS');					// 允许上传类型
		$upload->subName   =	array('date','Y_m');				//使用日期为子目录名称
		// 上传文件
		$info   =   $upload->upload();
		/*echo json_encode($info);
		trace($info,'调试');
		exit;*/

		/*echo json_encode($info);
		exit;*/
		if(!$info) {// 上传错误提示错误信息
			//p($upload->getError());
			/*$info=$upload->getError();
			echo $info;
			exit;*/
			return array('status'=>0,'msg'=>$upload->getError());
		}else{// 上传成功

			
 			$width=explode(',', $width);
 			$height=explode(',', $height);
 			$size=array(
 					'max',
 					'medium',
 					'mini'
 			);
 			$image = new \Think\Image();// 实例图片处理
 			$facepath=C('UPLOAD_PATH'). $path . '/'.$info['Filedata']['savepath'];
 			$open=$facepath.$info['Filedata']['savename'];
 			$image->open($open);
 			$sl=0;
 			$image_date=array();
 			while ($sl<count($width)){
 				$image->thumb($width[$sl], $height[$sl])->save($open.'_'.$width[$sl].'_'.$height[$sl].'.'.$info['Filedata']['ext']);
 				$image_date[$size[$sl]]=$info['Filedata']['savepath'].$info['Filedata']['savename'].'_'.$width[$sl].'_'.$height[$sl].'.'.$info['Filedata']['ext'];
 				$sl++;
 			}
 			//生成缩略图后，删除源文件
 			@unlink($open);

 			/*echo json_encode($image_date);
 			exit;*/

 			
 			
 			$data=['status'=>1,'path'=>$image_date];

 			/*echo json_encode($data);
 			exit;*/
 			return $data;
			
		}
	}

	/*
	 * 异步移除关注与粉丝
	 */
	
	public function delFollowAction(){
		if(!IS_AJAX)$this->error('页面不存在');
		
		$uid = I('uid','','intval');
		$type = I('type','','intval');
		
		$where = $type ? array('follow'=>$uid,'fans'=>session('uid')) :array('fans'=>$uid,'follow'=>session('uid'));
		
		if(M('follow')->where($where)->delete()){
			$db = M('userinfo');
			if($type){
				$db->where(array('uid'=>session('uid')))->setDec('follow');
				$db->where(array('uid'=>$uid))->setDec('fans');
			}else {
				$db->where(array('uid'=>session('uid')))->setDec('fans');
				$db->where(array('uid'=>$uid))->setDec('follow');
			}
		echo 1;
		}else{
			echo 0;
		}
	}

	/*
	 * 异步轮询推送消息
	*/
	public function getMsg(){
		if(!IS_AJAX)$this->error('页面不存在');
// 		echo json_encode(array(
// 			'status'=>1,
// 			'total'=>1,
// 			'type'=>1,
// 		));

		$uid = session('uid');
		$msg=S('usermsg'.$uid);
		
		if ($msg){
			if ($msg['comment']['status']){
				//$msg['comment']['status']=0;
				//S('usermsg'.$uid,$msg,0);//把$msg的状态推送进去
				echo json_encode(array(
					'status'=>1,
					'total'=>$msg['comment']['total'],
					'type'=>1
				));
				exit();
				}
			
			if ($msg['letter']['status']){
				//$msg['letter']['status']=0;
				//S('usermsg'.$uid,$msg,0);
				echo json_encode(array(
						'status'=>1,
						'total'=>$msg['letter']['total'],
						'type'=>2
				));
				exit();
				}
			
			if ($msg['atme']['status']){
				//$msg['atme']['status']=0;
				//S('usermsg'.$uid,$msg,0);
				echo json_encode(array(
						'status'=>1,
						'total'=>$msg['atme']['total'],
						'type'=>3
				));
				exit();
				}
		}
		echo json_encode(array('status'=>0));
	}
	




	}