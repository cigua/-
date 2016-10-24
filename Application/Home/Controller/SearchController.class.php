<?php

		namespace Home\Controller;
		use  Think\Controller;

		class SearchController extends Controller

		{
			public function sechUserAction()

			{
				$keyword=I('keyword');

				$m_ui=M('userinfo');
				//模糊查询条件改用字符串的形式
				$where="username like '%$keyword%' ";
				header('content-Type:text/html;charset=utf-8');
				$count=$m_ui->where($where)->count('id');
				//实例化分页类对象，并设置了样式
				$Page=new \Think\Page($count,3);
				$Page->setConfig('theme',"共 %TOTAL_ROW% 条记录  %FIRST% %UP_PAGE%  %LINK_PAGE% %DOWN_PAGE% %END% ");
				$Page->setConfig('prev','上一页');
				$Page->setConfig('next','下一页');
				
				
				$field=['username','face80','fans','weibo','follow','location','intro','uid'];
				//获取到检索的条件用户
				$result=$m_ui->where($where)->field($field)->order('weibo')->limit($Page->firstRow.','.$Page->listRows)->select();
				/*echo '<pre>';
				var_dump($result);*/
				$result=$this->_getMutual($result);
				
				/*var_dump($result);
				exit;*/
				$this->result=$result;
				$this->count=$count;
				$this->keyword = $keyword;
				$this->page=$Page->show();				
				$this->display();

			}


			Private function _getMutual($result){
				if(!$result) return false;//没有结果集，就返回 false;
				$db = M('follow');
				foreach ($result as $k => $v){
					//是否互相关注
					$sql = '(SELECT `follow` FROM `wb_follow` WHERE `follow` = '.$v['uid'].' 
							AND `fans` = '.session('uid').') UNION (SELECT `follow` FROM `wb_follow` WHERE 
							`follow` = '.session('uid').' AND `fans` ='.$v['uid'].')';
					$mutual = $db->query($sql);

					/*var_dump($mutual);*/
					if(count($mutual) == 2){
						$result[$k]['mutual'] = 1;
						$result[$k]['followed'] = 1;
					}else {
						$result[$k]['mutual'] = 0;
						//未互相关注是检索是否已关注（有没有关注我）
						$where = array(
								'follow'=>$v['uid'],
								'fans'=>session('uid')//我（粉丝）关注了他
						);
						$result[$k]['followed'] = $db->where($where)->count();
					}
				}
				return $result;
			}



		}