<?php

	  namespace Home\Model;
	  use 		Think\Model\ViewModel;

	  /**
	   * 构建收藏与微博/用户表的是视图模型
	   */
	  class KeepModel extends ViewModel
	  {
	  	public  $viewFields=[

		  		'keep'     =>['id'=>'k_id','time'=>'k_time','wid','uid','_type'=>'inner'],
		  		'weibo'    =>['id'=>'w_id','content','time'=>'w_time','comment','uid','_on'=>'keep.wid=weibo.id','_type'=>'left'],
		  		'picture'  =>['mini','max','_on'=>'weibo.id=picture.wid','_type'=>'left'],
		  		
		  		'userinfo' =>['username','face50','_type'=>'weibo.uid=userinfo.uid','_on'=>'weibo.uid=userinfo.uid']

		  					];

		/**
		 *返回查询的数据
		 */
		public  function getKeep($where,$limit)

		{
			$result=$this->where($where)->order('k_time DESC')->limit($limit)->select();

		    return $result;
		}

	  }