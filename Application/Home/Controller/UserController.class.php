<?php

// 声明当前文件的命名空间
namespace Home\Controller;

// 导入基础控制器类
use Think\Controller;

/**
 * 前台模块的 用户控制器类文件
 */
class UserController extends Controller
{
	
	public  function indexAction()
	
	{
		$uid=$_GET['id'];
		$where=['uid'=>$uid];
		//获取用户的个人详细信息
		$m_userinfo=M('Userinfo');
		$userinfo=$m_userinfo->where($where)->find();
		
		//获取用户发布的微博列表
		$m_weibo=D('Weibo');
		$where=['uid'=>$uid];
		$count=$m_weibo->where($where)->count('weibo.id');
		//实例分页对象
		$Page=new \Think\Page($count,4);
		$Page->setConfig('theme',"共 %TOTAL_ROW% 条记录  %FIRST% %UP_PAGE%  %LINK_PAGE% %DOWN_PAGE% %END% ");
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $firstRow=$Page->firstRow;
        $listRows=$Page->listRows;
        $weibo=$m_weibo->getAll($where,$firstRow,$listRows);
                
        //获取点击用户的关注列表
         $m_follow=M('Follow');
         $field=['follow'];
         $where=['fans'=>$uid];
         $follows=$m_follow->field($field)->where($where)->select();
		foreach ($follows as $value) {
			$follow_list[]=$value['follow'];
		}
		
		//去除重复关注的用户
		$follow_list=array_unique($follow_list);
		
		$where=['uid'=>['IN',$follow_list]];

		$field=['username','face80','uid'];
		$follow_list=$m_userinfo->field($field)->where($where)->select();

		//获取点击用户的粉丝列表
		 
         $field=['fans'];
         $where=['follow'=>$uid];
         $fans=$m_follow->field($field)->where($where)->select();
		foreach ($fans as $value) {
			$fans_list[]=$value['fans'];
		}
		
		//去除重复关注的用户
		$fans_list=array_unique($fans_list);
		
		$where=['uid'=>['IN',$fans_list]];

		$field=['username','face80','uid'];
		$fans_list=$m_userinfo->field($field)->where($where)->select();
		
		
		$this->userinfo=$userinfo;
		$this->weibo=$weibo;
		$this->follow=$follow_list;
		$this->fans=$fans_list;
		$this->count=$count;
		$this->page=$Page->show();
		$this->display();

	}
	//展示所有的收藏列表
	public function keepAction()

	{
		$m_keep=M('keep');
		$where=['uid'=>session('uid')];
		
		$count=$m_keep->where($where)->count('id');
		$limit=$page->firstRow.','.$page->listRows;
		//视图模型对象
		$mv_keep=D('Keep');
		$where=['keep.uid'=>session('uid')];
		$page=new \Think\Page($count,4);
		$page->setConfig('theme',"共 %TOTAL_ROW% 条记录  %FIRST% %UP_PAGE%  %LINK_PAGE% %DOWN_PAGE% %END% ");
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
		
		$keep_info=$mv_keep->getKeep($where,$limit);
		
		$this->keep_info=$keep_info;
		$this->count=$count;
		$this->page=$page->show();
		$this->display('weiboList');

}
	//异步取消收藏
	public  function  cancelKeepAction()

	{
		if(!IS_AJAX){
			$this->error('页面不存在');
		}
		$m_keep=M('Keep');
		$where=['id'=>I('kid')];
		$cancel=$m_keep->where($where)->delete();

		if($cancel){
			//收藏表删除成功之后，对应的微博表的收藏数应对应-1
			$m_weibo=M('Weibo');
			$where=['id'=>I('wid')];
			$res=$m_weibo->where($where)->setDec('keep');
			
			echo 1;
		}else{

			echo 0;
		}
	}

	public function followListAction(){
		
 		$uid = I('uid','','intval');
 		//区分关注 与 粉丝（1：关注，0：粉丝）
 		$type = I('type','','intval');
 		$db = M('follow');
 		
 		//根据type参数不同，读取用户关注与粉丝ID
 		$where = $type ? array('fans'=>$uid) : array('follow' => $uid);
 		$field = $type ? 'follow' : 'fans';
 		$count = $db->where($where)->count();
 		$Page  = new \Think\Page($count,4);// 实例化分页类 传入总记录数和每页显示的记录数(4)
 		$Page->setConfig('theme',"共 %TOTAL_ROW% 条记录  %FIRST% %UP_PAGE%  %LINK_PAGE% %DOWN_PAGE% %END% ");
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
 		$limit=$Page->firstRow.','.$Page->listRows;
 		
 		$uids = $db->field($field)->where($where)->limit($limit)->select();
 		
		if ($uids){
			//把用户关注或者粉丝ID重组为一维数组
			foreach ($uids as $k => $v){
				$uids[$k]= $type ? $v['follow'] : $v['fans'];
			}
			
			//提取用户个人信息
			$where = array('uid' => array('IN',$uids));
			$field = array('face50'=>'face','username','location','follow','fans','weibo','uid','sex');
			$users = M('userinfo')->where($where)->field($field)->select();
			
			//分配用户信息到视图
			$this->users=$users;
		}
		
		$where= array('fans'=>session('uid'));
		$follow=$db->field('follow')->where($where)->select();
		if($follow){
			foreach ($follow as $k=>$v){
				$follow[$k] = $v['follow'];
			}
		}
		
		$where=array('follow'=>session('uid'));
		$fans = $db->field('fans')->where($where)->select();
		
		if($fans){
			foreach ($fans as $k=>$v){
				$fans[$k] = $v['fans'];
			}
		}

		$this->type =$type;
		$this->count =$count;
		$this->follow=$follow;
		$this->page=$Page->show();
		$this->fans=$fans;
		$this->display();
 		
	}

	//展示所有的评论列表
	public function commentAction()

	{
		$m_comment=M('Comment');
		$where=['uid'=>session('uid')];
		$count=$m_comment->where($where)->count('id');
		//实例化评论视图模型调用链接方法
		$mv_comment=D('Comment');
		$page=new \Think\Page($count,4);
		$where=['comment.uid'=>session('uid')];
		
		$limit=$page->firstRow.','.$page->listRows;
		$page->setConfig('theme',"共 %TOTAL_ROW% 条记录  %FIRST% %UP_PAGE%  %LINK_PAGE% %DOWN_PAGE% %END% ");
       	$page->setConfig('prev','上一页');
       	$page->setConfig('next','下一页');
		
		$comment_list=$mv_comment->getComment($where,$limit);
			
		$this->comment_list=$comment_list;
		$this->page=$page->show();
		$this->count=$count;
		$this->display();

	}
	//异步增加评论
	public function replyAction()

	{
		$m_comment=M('Comment');
		$data=['content'=>I('content'),'uid'=>session('uid'),'wid'=>I('wid'),'time'=>time()];	
		$comment_id=$m_comment->add($data);

		if($comment_id){
			$m_weibo=M('Weibo');
			$where=['id'=>I('wid')];
			$com_number=$m_weibo->where($where)->setInc('comment');
			if($com_number){
				echo 1;
			}

		}else{
			echo 0;
		}
	}

	//异步删除评论
	public  function delCommentAction()

	{
		if(!IS_AJAX){

			$this->error('页面不存在');
		}

		$m_comment=M('Comment');
		$where=['id'=>I('cid')];
		$comment_id=$m_comment->where($where)->delete();
		//评论删除成功的话，对应的微博的评论数也对应-1
		if($comment_id){
			$m_weibo=M('Weibo');
			$where=['id'=>I('wid')];
			$res=$m_weibo->where($where)->setDec('comment');
			
			echo 1;
		}else{

		echo 0;
		}
	}
	//展示私信主页
	//获取接受的私信内容
	public function letterAction()
	{
		$mv_letter=D('Letter');
		$where=['accepter'=>session('uid')];
		$count=$mv_letter->where($where)->count('letter.id');
		
		$page=new \Think\Page($count,4);
		$where=['accepter'=>session('uid')];
		$limit=$page->firstRow.','.$page->listRows;
		$page->setConfig('theme',"共 %TOTAL_ROW% 条记录  %FIRST% %UP_PAGE%  %LINK_PAGE% %DOWN_PAGE% %END% ");
       	$page->setConfig('prev','上一页');
       	$page->setConfig('next','下一页');
       	
       	$letter_info=$mv_letter->getLetter($where,$limit);


		
		$this->count=$count;
		$this->page=$page->show();
		$this->letter_info=$letter_info;
		$this->display();
	}
	//发布私信
	public  function letterSendAction(){

	$name=I('name');
	$where=['username'=>$name];
	$m_user=M('Userinfo');
	$field=['uid'];
	$res=$m_user->field($field)->where($where)->find();
	
	foreach ($res as $key => $value) {
		$accepter=$value['uid'];
	}
	if(!$res){
		$this->error('用户不存在，请核对');
	}else{
		$data=[

				
				'content'=>I('content'),
				'time'=>time(),
				'uid'=>session('uid'),
				'accepter'=>$res['uid'],

			];

		$m_letter=M('Letter');
		$letter=$m_letter->add($data);
		$sql=$m_letter->getLastSQL();
		
		if($letter){
			$this->success('发送成功',U('User/letter'),1);
			
		}else{
		
			$this->error('私信发送失败');
		}

	  }
	}

	//删除私信
	public  function delLetterAction()

	{
		if(!IS_AJAX){
			$this->error('页面不存在');
		}
		$m_letter=M('Letter');
		$where=['id'=>I('lid')];

		$letter_id=$m_letter->where($where)->delete();
		if($letter_id){

			echo 1;
		}else{
			echo 0;
		}


	}


	/*
	 * @提到我的
	 */
	public function atmeAction(){
		set_msg(session('uid'),3,true);
		$where = array('uid'=>session('uid'));
		$wid =M('atme')->where($where)->field('wid')->select();
		foreach ($wid as $k=>$v){
			$wid[$k]=$v['wid'];
		}
		$count =M('atme')->where($where)->count();
		$Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数(10)
		$limit=$Page->firstRow.','.$Page->listRows;
		$where = array('id'=>array('IN',$wid));
		$weibo =D('Weibo')->getAll($where,$limit);
		
		$Page->setConfig('theme',"共 %TOTAL_ROW% 条记录 %FIRST% %UP_PAGE% %NOW_PAGE% / %TOTAL_PAGE% %DOWN_PAGE% %END% ");
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		
		$this->weibo=$weibo;
		$this->page2= $Page->show();
		$this->atme=1;
		$this->display('weiboList');
	}
}