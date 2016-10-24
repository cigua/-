<?php

	  namespace Home\Model;
	  use \Think\Model\ViewModel;

	  class LetterModel extends  ViewModel

	  {
	  	 public $viewFields=[

	  	 				'letter'=>['id','accepter','content','uid','time','_type'=>'left'],
	  	 				'userinfo'=>['face50','_on'=>'letter.uid=userinfo.uid']

	  	 ];

	  	 //获取所有的私信内容
	  	 public  function  getLetter($where,$limit)

	  	 {

	  	 	$res=$this->where($where)->order('time DESC')->limit($limit)->select();
	  	 	return  $res;
	  	 }


	  }

