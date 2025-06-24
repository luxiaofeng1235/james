<?php
/*
 * 服务层主要处理一些通用的配置设置信息，获取配置路由转发等 
 *
 * Copyright (c) 2017 - 真一网络
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 2.1
 *
 */
    
class CommonService{

	//测试方法
	public static function testarr(){
		return 333;
	}


	/**
	* @note 获取采集列表的采集规则
	* @param  $story_link string 地址
	* @return array
	*/
	public static function collectListRule($source_url){
		if( !$source_url || $source_url == ''){
			return false;
		}
		//获取相关的来源
		$source_ref = NovelModel::getSourceUrl($source_url);
		if(!$source_ref){
			return false;
		}
		global $urlRules;

		$sourceList= [
			'twking'		=>	$urlRules[Env::get('TWCONFIG.XSW_SOURCE')]['detail_info'], #台湾站列表
			'xuges'			=>	$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['xuges_info'], #虚阁网小说
			'xingyueboke'	=>	$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['world_info'],#星月小说
			'xbiqiku2'		=>	$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['xbiqiku2_info'],
			#xbiku2小说
			'banjiashi'		=>	$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['banjiashi_info'],#佳士小说
			'douyinxs'		=>	$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['douyin_info'],#抖音的列表
			'bqg24'			=>	$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['bqg24_info'],#笔趣阁24
			'27k'			=>	$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['27k_info'],#乐阅小说
			'siluke520'		=>	$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['siluke520_info'], #思路客
		];
		//判断当前的来源是否满足存在的条件，如果有直接返回么有就返回给一个默认的
		if (isset($sourceList[$source_ref])){
			return $sourceList[$source_ref];
		}else{
			#跑书吧的列表采集规则和一些通用网站
			return  $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['default_info'];
		}
	}

	/**
	* @note 采集小说正文规则
	* @param $source_url string 网站来源
	* @return array
	*/
	public static function collectContentRule($source_url){
		if( !$source_url || $source_url == ''){
			return false;
		}
		//获取相关的来源
		$source_ref = NovelModel::getSourceUrl($source_url);
		global $urlRules;
		if(!$source_ref){
			return false;
		}
		$sourceList= [
			'twking'	=>	$urlRules[Env::get('TWCONFIG.XSW_SOURCE')]['content'],#台湾站内容
			'xingyueboke'	=> $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['world_content'], #星月小说内容
			'xuges'	=>	$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['xuges_content'],#星月小说内容
			'banjiashi'	=>$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['banjiashi_content'],#佳士小说内容
			'xbiqiku2'	=>$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['default_content'] , #xbqiku2小说
			'bqg24'	=>	$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['bqg24_content'],#笔趣阁内容
			'27k'	=>$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['27k_content'],#27内容
			'siluke520'=>	$urlRules[Env::get('APICONFIG.PAOSHU_STR')]['siluke520_content'],#思路客
			'92yanqing'	=> $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['92yanqing_content'],#思路客
		];	
		//查看是否满足当前的条件，如果满足就用这个，不满足就用其他的进行配置
		if(isset($sourceList[$source_ref])){
			return $sourceList[$source_ref];
		}else{
			#泡书吧的采集规则和一些通用采集规则默认采用
			return $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['default_content'];
		}
	}


	/**
	* @note 导入采集重复数据失败的id校验规则
	* @param  $referer string 采集源url
	* @return array
	*/
	public static function importCollectTagId($referer = ''){
		if(!$referer){
			return false;
		}
		$source_ref = NovelModel::getSourceUrl($referer);
		$content_reg = ''; //校验规则
		switch ($source_ref) {
			case 'twking':#台湾网站的验证规则
				 $content_reg = '/id="article"/';
				break;
			case 'xingyueboke':#星空小说的验证规则
				 $content_reg = '/id="nr1"/';
				break;
			case 'xuges':#虚阁网小说验证规则
				 $content_reg ='/class="jz"/';
				break;
			case 'banjiashi': #佳士小说验证规则
				$content_reg= '/class="title"/';
				break;
			case '27k': #27K验证规则
				$content_reg    ='/class="txtnav"/';//27k小说
				break;
			case 'siluke520':#思路客验证规则
				$content_reg = '/id="htmlContent"/'; 
				break;
			case 'bqg24':#笔趣阁24的验证规则
				$content_reg = '/id="htmlContent"/'; 
				break;
			case '92yanqing':#就爱言情网
				$content_reg = '/id="booktxt"/'; 
				break;
			default:#默认跑书吧和带默认标签的验证
				$content_reg = '/id="content"/';
				break;
		}
		return $content_reg;
	}

	/**
	* @note 获取采集的小说ID信息
	* @param $story_link string 根据连接地址解析获取目标网站ID
	* @return string
	*
	*/
    public static function getCollectWebId($story_link= ""){
        if(!$story_link){
            return 0;
        }
        $hostData= parse_url($story_link);

        if(strstr($story_link, 'xuges')){
        	  $path = str_replace('/index.htm', '', $hostData['path']);
        	  $third_novel_id = substr($path, 1);
        	  $third_novel_id = str_replace('/','-',$third_novel_id);
        	  return $third_novel_id;
        }else{
        	$pathData = explode('/',$hostData['path']);
	        $pathData = array_filter($pathData);
	        $pathData = array_values($pathData);
	        if(count($pathData)>1){
	            //使用后缀里的第二个
	            $third_novel_id = $pathData[1] ?? 0;
	        }else{
	            //如果某学里面没有这个就用默认的第一个
	            $third_novel_id = $pathData[0] ?? 0;
	        }
	        return $third_novel_id;
        }
     
    }
}
?>