<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends 	CommonController {
    public function indexAction()
    {
       
       //获取用户关注的所有用户
       $m_follow=M('Follow');
       $where=['fans'=>session('uid')];
       //非含登陆者的所有关注用户组
       $f_list=$m_follow->field('follow')->where($where)->select();
       $u_list=[session('uid')];
       foreach ($f_list as $key => $value) {
           $u_list[]=$value['follow'];
       }
       
       //用户选择分组显示微博
       if(isset($_GET['gid'])){
        $u_list=null;
        $where=['gid'=>I('gid')];
        $m_follow=M('Follow');
        $field=['follow'];
        $follow_list=$m_follow->field($field)->where($where)->select();
        $u_list=[];
        foreach ($follow_list as  $value) {

          $u_list[]=$value['follow'];
        }

       }
       
       //过滤重复关注的ID号
        $u_list=array_unique($u_list);    
       //获取所有微博的总数
       $m_weibo=D('Weibo');  
       $where = array('uid'=>array('IN',$u_list));  
       $count= $m_weibo->where($where)->count('weibo.id');
       //实例化分页类对象，并设置了样式
       $Page=new \Think\Page($count,5);
       $Page->setConfig('theme',"共 %TOTAL_ROW% 条记录  %FIRST% %UP_PAGE%  %LINK_PAGE% %DOWN_PAGE% %END% ");
       $Page->setConfig('prev','上一页');
       $Page->setConfig('next','下一页');
      
       $where = array('weibo.uid'=>array('IN',$u_list));
       $firstRow=$Page->firstRow;
       $listRows=$Page->listRows;
       $weibo=$m_weibo->getAll($where,$firstRow,$listRows);
                
       //获取登录者的个人资料
        $m_userinfo=M('Userinfo');
        $where=['uid'=>session('uid')];
        $user_info=$m_userinfo->where($where)->find();

      /**
       *查询登陆者感兴趣的用户组
       *筛选条件(用户关注的那些用户的所有关注组)
       */
       if(!empty($f_list)){
        foreach ($f_list as  $value) {

          $list[]=$value['follow'];
        }
        $where=array('fans'=>array('IN',$list));
        $field=['follow'];
        //获取到所有关注组关注的用户集合
        $interest=$m_follow->field($field)->where($where)->select();

        foreach ($interest as  $value) {

          $ints_list[]=$value['follow'];
        }
        //去除重复ID，并过滤掉已经关注的用户
        $ints_list=array_unique($ints_list);
        
        $inst_diff=array_diff($ints_list,$list);
        //获取共同的好友个数
        $inster_list['common']=count(array_intersect($ints_list, $list));

        //获取感兴趣的用户组的个人信息
        $where=['uid'=>['IN',$inst_diff]];
        $field=['username','face50','uid'];
        $inster_list=$m_userinfo->field($field)->where($where)->limit(5)->select();
        

        $this->inster_list=$inster_list;
     }
      
      //获取所有用户中(微博+关注+FANS总和最多的排行)
      //
      $hot_user_list=$m_userinfo->order('weibo  desc')->limit(5)->select();
      

        
     //获取网站最新注册的会员
      $m_user=M('User');
      $new_user_list=$m_user->alias('u')->join('LEFT JOIN __USERINFO__  ui ON u.id=ui.uid')
             ->order('registime desc')->limit(5)->select();
      
     
       
       
       $this->new_user_list=$new_user_list;
       $this->hot_user_list=$hot_user_list;
       $this->count=$count;
       $this->page=$Page->show(); 
       $this->userinfo=$user_info;
       $this->weibo=$weibo;
       
       $this->display();
    }

    public  function  sendWeiboAction()

    {
        if(!IS_POST){
            $this->error('页面不存在');
        }
        $data=[
               'content' =>I('content'),
               'time'    =>time(),
               'uid'     =>session('uid')
        ];

        //处理@用户
        $this->_atmeHande($data['content'],$wid);

        $m_weibo=M('Weibo');
        $m_userinfo=M('Userinfo');

        $w_id=$m_weibo->add($data);

        if($w_id){
            //若发表的微博中含有图片的话，则一并将图片的地址一并入库
            if(isset($_POST['max'])){
                /*header('content-type:text/html; charset=utf8');
                echo '进来没？';
                exit;*/
                $m_pic=M('picture');
                $data=['mini'=>I('mini'),'medium'=>I('dedium'),'max'=>I('max'),'wid'=>$w_id];
                $m_pic->add($data);
            }
            //每次添加成功，给用户微博数增加1
            $m_userinfo->where(['uid'=>session('uid')])->setInc('weibo');
            redirect(U('index'),0,'发表成功...');

        }else{
            $this->error(U('index'),3,'发表失败');
        }
    }
    //@用户处理
    private function _atmeHande($content,$wid){
    $preg = '/@(\S+?)\s/is';
    preg_match_all($preg,$content,$arr);
    if(!empty($arr[1])){
      $db=M('userinfo');
      $atme=M('atme');
      foreach ($arr[1] as $v){
        $uid = $db->where(array('username'=>$v))->getField('uid');
        if($uid){
          $data = array(
            'wid'=>$wid,
            'uid'=>$uid,
          );
          //写入消息推送
          set_msg($uid,3);
          $atme->data($data)->add();
        }
      }
    }
  }
  

      
  public function commentAction(){
    if(!IS_POST)$this->error('页面不存在');
    //提取评论数据
    $data=array(
      'content'=>I('content'),
      'time'=>time(),
      'uid'=>session('uid'),
      'wid'=>I('wid','','intval'),
    );
    
    if (M('comment')->data($data)->add()){
    //读取评论用户信息
    $field=array('username','face50','uid');
    $where=array('uid'=>$data['uid']);
    $user=M('userinfo')->where($where)->field($field)->find();
    
    //被评论微博的发布者用户名
    $uid=I('uid','','intval');
    $username = M('userinfo')->where(array('uid'=>$uid))->getField('username');
    
    $db = M('weibo');
    //评论数+1
    $db->where(array('id'=>$data['wid']))->setInc('comment');
    
    //评论同时转发时处理
    if ($_POST['isturn']){
      //读取转发微博ID与内容
      $field = array('id','content','isturn');
      $weibo = $db->field($field)->find($data['wid']);
      $content = $weibo['isturn'] ? $data['content'] . '// @'.$username.' : '.$weibo['content'] : $data['content'];
      
      //同时转发到微博的数据
      $cons = array(
        'content'=>$content,
        'isturn'=>$weibo['isturn'] ? $weibo['isturn'] : $data['wid'],
        'time'=>$data['time'],
        'uid'=>$data['uid'],
      
      );
      if ($db->data($cons)->add()){
        $db->where(array('id'=>$weibo['id']))->setInc('turn');
      }
      
      echo 1;
     
    }
    
    //组合评论样式字符串返回
    $str= '';
    $str.= '<dl class="comment_content">';
    $str.='<dt><a href="'.U('/'.$data['uid']).'">';
    $str.='<img src="';
    $str.=__ROOT__;
    if ($user['face']){
      $str .='/Uploads/Face/'.$user['face'];
    }else {
      $str .='/Public/Images/noface.gif';
    }
    $str.='"alt="'.$user['username'].'" width="30" height="30"/>';
    $str.='</a></dt><dd>';
    $str.='<a href="'.$data['uid'].'" class="comment_name">';
    $str.=$user['username']." : ".replace_weibo($data['content']);
    $str.="&nbsp;&nbsp;(".time_format($data['time']).")";
    $str.='<div class="reply">';
    $str.='<a href="">回复</a>';
    $str.='</div></dd></dl>';
    
    set_msg($uid,1);
    echo $str;
    }else {
      echo 'false';
    }
    
  }

    //异步展示所有的评论列表
    public  function getCommentAction()

    {
      if(!IS_AJAX){

        $this->error('页面不存在');
      }

      $wid=I('wid');
      $m_comment=D('Comment');
      $where=['wid'=>$wid];
      $comment_list=$m_comment->where($where)->select();

      $str='';

      if($comment_list){

          foreach ($comment_list as $comment) {

          $str.= '<dl class="comment_content">';
          $str.='<dt><a href="'.U('/'.$comment['uid']).'">';
          $str.='<img src="';
          $str.=__ROOT__;
          if ($comment['face50']){
            $str .='/Uploads/Face/'.$comment['face50'];
          }else {
            $str .='/Public/Images/noface.gif';
          }
          $str.='"alt="'.$comment['username'].'" width="40" height="40"/>';
          $str.='</a></dt><dd>';
          $str.='<a href="'.$comment['uid'].'" class="comment_name">';
          $str.=$comment['username']." : ".replace_weibo($comment['content']);
          $str.="&nbsp;&nbsp;(".time_format($comment['time']).")";
          $str.='<div class="reply">';
          $str.='<a href="">回复</a>';
          $str.='</div></dd></dl>';
          
          set_msg($uid,1);
          }
          echo $str;
        }else {
            echo 'false';
          }  
      }
     
    
  /*
   * 收藏微博
   */
  public function keepAction(){
    if(!IS_POST)$this->error('页面不存在');
    
    $wid=I('wid');
    $uid = session('uid');
    
    $db = M('keep');
    
    //检测用户是否已经收藏微博
    $where = array('wid'=>$wid,'uid'=>$uid);
    if($db->where($where)->getField('id')){
      echo -1;
      exit;
    }
    
    //添加收藏
    $data =array(
    'uid'=>$uid,
    'time'=>$_SERVER['REQUEST_TIME'],
    'wid'=>$wid,
    );
    
    if($db->data($data)->add()){
      //收藏成功时对改微博的收藏数+1
      M('weibo')->where(array('id'=>$wid))->setInc('keep');
      echo 1;
    }else {
      echo 0;
    }   
  }
   // 异步删除微博
    public  function  delWeiboAction()
    {
      if(!IS_AJAX){
        $this->error('页面不存在');
      }
      $m_weibo=M('Weibo');
      $wid=I('wid','','intval');
      $del_weibo=$m_weibo->delete($wid);
      //查看该微博是否携带图片
      $m_pic=M('Picture');
      $where=['wid'=>$wid];
      $picture=$m_pic->where($where)->find();
    
      
      if($del_weibo){
         //删除磁盘携带的图片文件        
          if($picture){              
              @unlink('./Uploads/Pic/'.$picture['mini']);
              @unlink('./Uploads/Pic/'.$picture['medium']);
              @unlink('./Uploads/Pic/'.$picture['max']);

          }
          //删除成功的话，该用户的微博数-1
          $m_user=M('Userinfo');
          $where=['uid'=>session('uid')];
          $m_user->where($where)->setDec('weibo');
          
          
          //删除该微博下属的所有的评论
          $m_comment=M('Comment');
          $where=['wid'=>I('wid')];
          $m_comment->where($where)->delete();
          
          echo '1';

      }else{

          echo  '0';
      }
    }



  /*
   * 转发微博
   */
  
  Public function turnAction(){
    if(!IS_POST)$this->error('页面不存在');
    //p($_POST);
    //原微博ID
    $id = I('id','','intval');
    $tid= I('tid','','intval');
    $content=I('content');
    
    //提取插入数据
    $data=array(
      'content'=>$content,
      'isturn'=>$tid ? $tid : $id,
      'time'=>time(),
      'uid'=>session('uid'),
    );

    //插入数据至微博表
    $db = M('weibo');
    $wid = $db->data($data)->add();
    if($wid){
      //原微博转发数+1
      $db->where(array('id'=>$id))->setInc('turn');
      
      //转发+1
      if($tid){
        $db->where(array('tid'=>$tid))->setInc('turn');
      }
      //用户发布微博数+1
      M('userinfo')->where(array('uid'=>session('uid')))->setInc('weibo');
      
      //处理@用户
      $this->_atmeHande($data['content'],$wid);
      //如果点击了同时评论插入内容到评论表
      if(isset($_POST['becomment'])){
        $data=array(
          'content'=>$content,
          'time'=>time(),
          'uid'=>session('uid'),
          'wid'=>$id,
        );
      if(M('comment')->data($data)->add()){
        $db->where(array('id'=>$id))->setInc('comment');
      }
      }
      $this->success('转发成功...',$_SERVER['HTTP_REFERE']);
    }else {
      $this->error('转发失败，请重试！');
    }
  }


    public  function loginOutAction()

    {

    $m_user=M('User');
          $data=['on_line'=>0];
          $where=['id'=>session('uid')];
          $m_user->where($where)->save($data);

		  session('uid',null);
    	session_unset();
    	session_destroy();
    	setcookie('auto',time()-60,'/');

    	redirect(U('Login/index'),0,'');
    }
}