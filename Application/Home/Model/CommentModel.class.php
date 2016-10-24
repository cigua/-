<?php

	 namespace Home\Model;
	 use Think\Model\ViewModel;

	 class CommentModel  extends viewModel{
	 	

	 	public  $viewFields=array(

	 					'comment'=>array('id','content','time','wid','uid','_type'=>'LEFT'),
	 					'userinfo'=>array('username','face50','uid','_on'=>'comment.uid=userinfo.uid')
	 		);


	 	public  function getComment($where,$limit)

	 	{

	 		$result=$this->where($where)->order('time DESC')->limit($limit)->select();


	 		return $result;
	 	}

	 }

