<?php

	namespace Home\Controller;
	use Think\Controller;
	

	class LoginController extends Controller

	{	
		//展示注册页面
		public  function  registerAction()
		
		{
			$this->display();
		}

		//验证注册数据
		public function  checkRegisAction()
		{

			if(!IS_POST){
				$this->error('页面不存在');
			}
			
			$m_user=D('User');

			$res=$m_user->create();

			$data=[

				'account' 	=>I('account'),
				'password' 	=>md5(I('pwd')),
				'registime' =>time(),
				'lock' 		=> 0,
				
			];	

			
			if($res){
				//数据添加成功
				/*$uid=$m_user->add($data);*/
				$uid=$m_user->insert($data);			
				if($uid){
					//保存用户登录ID到SESSION中方便后续数据的显示
					//登录成功则把用户的状态改成在线状态
					session('uid',$uid);
					$user_id=session('uid');

					$m_userinfo=M('Userinfo');
					$data=['uid'=>$user_id];
					
					$userinfo=$m_userinfo->add($data);
					


					/**
					 * 用户一注册就直接关注管理者
					 */
					$m_group=M('Group');
					$data=['name'=>'默认组','uid'=>$user_id];
					
					$gid=$m_group->add($data);

					$m_follow=M('Follow');
					$data=['follow'=>'1','fans'=>$user_id,'gid'=>$gid];
					$m_follow->add($data);
					$m_userinfo->where(['uid'=>$user_id])->setInc('follow');

					//管理者也同时关注注册者并给他发一条私信
					$data1=['follow'=>$user_id,'fans'=>'1','gid'=>'87'];
					$res=$m_follow->add($data1);
					$m_userinfo->where(['uid'=>$user_id])->setInc('fans');
					
					if($res){
					$m_letter=M('Letter');
					$data=['accepter'=>$user_id,
							'content'=>'欢迎大家来到本网站，经历了将近快1个月的开发时间，终于把网站给上线了，因为个人的精力有限，也没进行过多的测试，各位访客要是有发现问题或者用户体验不够友好，都可以在本条微博下评论，或者是在左上角给我账户发私信，感谢各位访客对本网站的技术指导',
							'time'=>time(),
							'uid'=>1
						];

					$m_letter->add($data);
					}
					redirect(U('index'),0,'注册成功秒之后跳转到登录页面');


				}
			}else{

				$this->error('用户注册失败<br>'.implode('<br>',$m_user->geterror()),U('register'));
			}


		}
		//展示登录页面
		public  function indexAction()

		{
			$this->display();
		
		}
		//验证登录信息
		public function loginAction()

		{
			if(!IS_POST){
				$this->error('页面不存在');
			}
			$account=I('account');
			$pwd=md5(I('pwd'));
			$where=['account'=>$account];
			$m_user=M('User');
			$user_info=$m_user->where($where)->find();
			
			if($user_info && $user_info['password']==$pwd){

				/*若账户以及密码都匹配的话，检验用户是否提交自动登录
				 *如果提交的话，就将用户的信息保存到COOKIE中，以便下
				 *次登录的时候，验证是否携带COOKIE且未过期
				 */
				
				if(isset($_POST['auto']))

				{
					$account=$user_info['account'];
					$ip=get_client_ip();
					$login_proof=$account.'|'.$ip;		

					$res=setcookie('auto',$login_proof,time()+3600*24*7,'/');
				}
						
				session('uid',$user_info);
				$m_user=M('User');
					$data=['on_line'=>1];
					$where=['id'=>$user_info['id']];
					$m_user->where($where)->save($data);
					/*echo $m_user->getLastSQL();
					exit;*/
				header('content-type:text/html; charset=utf-8');
				redirect(U('Index/index'),0,'登录成功3秒之后跳转到主页面');

			}else{
				$this->error('密码错误','index',3);
			}

		}

		public  function  verifyAction()

		{
			ob_clean();
			$config=['length'=>3];
			$captcha= new \Think\Verify($config);

			$captcha->entry();
		}


		//异步验证账户是否已经存在
		 public  function checkAccountAction()

		 {
		 	if(!IS_AJAX){
		 		$this->error('页面不存在');
		 	}

		 	$account=I('account');

		 	$where=['account'=>$account];
		 	$m_user=M('User');
		 	$uid=$m_user->where($where)->find();
		 	
		 	if($uid){
		 		echo 'false';
		 	}else{
		 		echo 'true';
		 	}
		 }
		 //验证昵称是否存在
		 public function checkUnameAction()

		{	
			if(!IS_AJAX){
				$this->error('页面不存在');
			}

			$uname=I('uname');
			$where=['uname' =>$uname];
			$m_user=M('Userinfo');
		 	$res=$m_user->where($where)->find();
		 	
			if($res){
				echo 'true';
			}else{
				echo 'false';
			}

			
		}



		//异步验证验证码是否是一致的
		public function checkVerifyAction()

		{	
			$verify=I('verify');

			$config=['reset' =>false];

			$captcha= new \Think\Verify($config);

			$res=$captcha->check($verify,$id='');
			if($res){
				echo 'true';
			}else{

				echo 'false';
			}
		}

	}