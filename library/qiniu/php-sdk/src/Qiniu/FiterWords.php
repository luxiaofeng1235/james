<?php
/**
 *  关键词过滤
 * Copyright (c) 2013/07 - Linktone
 * @author niu.zuo <niu.zuo@linktone.com>
 * @version 0.1
 */
class FilterWords {
    /*
     * content:需要检测过滤的文本
     * return:badword=>非法关键字。false:正常。
     */
    public function checkContent($content,$isDebug=false){
		//再增加一层直接的字符串匹配
		$filterWordKey="filter_bad_word_list";
		$filterWordList=array();
		$filterWordList=$this->cache->memcache->get($filterWordKey);
		if($isDebug){
			//需要测试的情况
			$filterWordList=array();//强制从数据库读取
		}
		if(!$filterWordList){
			//需要重新获取数据,确保1000个不要太多了,这些基本都是人工加上去的，为的是保证按字符匹配不出错
			$sql="select * from fmb_badwords where id>=776 limit 1500";
			$dataList=$this->db_slave->query($sql)->result_array();
			foreach($dataList as $xx){
				//注意要规避单竖号与处理其中的空格,这个地方的替换数组，在下面有一个地方也需要进行同步修改
				$filterWordList[]=preg_replace("/\s+/i","",str_ireplace(array("|","丨","/"), array(",",",",","),$xx['word']));
			}
			//存储5分钟
			$this->cache->memcache->save($filterWordKey, $filterWordList, 300);
		}
		/*if($isDebug){
			//显示过滤匹配词组
			echo "过滤词组:".implode("|", $filterWordList);
		}*/
		//echo implode("|", $filterWordList);
		if($isDebug){
			if(preg_match_all("/(".implode("|", $filterWordList).")/i", preg_replace("/\s+/i","",str_ireplace(array("|","丨","/"), array(",",",",","),strip_tags($content))),$result)){
				return array("badword"=>$result[0]);
			}
		}else{
			if(preg_match("/(".implode("|", $filterWordList).")/i", preg_replace("/\s+/i","",str_ireplace(array("|","丨","/"), array(",",",",","),strip_tags($content))),$result)){
				return array("badword"=>$result[0]);
			}
		}
		//exit;
    	//分词检测
        $wordArr=$this->segment_word->toSegment2(strip_tags($content));
        foreach($wordArr as $word){
            $memkey=$this->cache->memcache->get(trim($word));
            if($memkey){
                return array("badword"=>$word);
            }
        }
        //如果正常途径没有检测出来那么需要采用更强检查措施完全获取中文来处理：
        //获取到完整的中文
        preg_match_all("/[\x{4e00}-\x{9fa5}A-Za-z0-9_]+/u", strip_tags($content), $result, PREG_PATTERN_ORDER);
        $content = "";
        foreach ($result as $each) {
        	foreach ($each as $val) {
        		$content .= $val;
        	}
        }
        //同样的检测方法
        $wordArr=$this->segment_word->toSegment2($content);
        foreach($wordArr as $word){
        	$memkey=$this->cache->memcache->get(trim($word));
        	if($memkey){
        		return array("badword"=>$word);
        	}
        }
        //直接字符串过滤匹配
        if(preg_match("/(髮票|代開|髮票|醱票|蕟票|發票|代开)/i", $content,$result)){
        	return array("badword"=>$result[0]);
        }
        return false;
    }
    
    /**
     * 检查是否为恶意用户
     * @param number $uid
     */
    public function checkBadUser($uid=0){
    	if($uid<=0){
    		return "";
    	}
    	$startTime=date("Y-m-d 00:00:00");
    	$endTime=date("Y-m-d 23:59:59");
    	//检查同一个IP注册的注册数量
    	$regInfo=$this->db->query("select * from uc_members where uid=$uid")->first_row("array");
    	$regIp=$regInfo['regip'];
    	$ipInfo=$this->db->query("select count(*) as total from uc_members where regdate>=".strtotime($startTime)." and regdate<=".strtotime($endTime)." and regip='".$regIp."'")->first_row("array");
    	$ipTotal=$ipInfo['total'];
    	$check1=($ipTotal>10);//检查同一IP注册的用户数量
    	if($check1){
    		return "系统发现你所在的IP当天注册账号过多，限制发帖";
    	}
    
    	//检查注册之后是否立即发帖
    	$sql="select * from grp_topic where author_uid=$uid order by topic_id asc limit 1";
    	$topicInfo=$this->db->query($sql)->first_row("array");
    	$topicTime=$topicInfo['create_time'];
    	if(!empty($topicTime) && !empty($regInfo['regdate'])){
    		$check2=((strtotime($topicTime)-$regInfo['regdate'])<60*2);//发帖时间与注册时间相差小于2分钟
    		//同时还要比较最后发帖时间与当前时间的比较,如果超过2分钟那么也需要放行
    		$check22=(time()-(strtotime($topicTime))<60*2);
    		if($check2 && $check22){
    			return "系统发现你注册时间与发帖时间过短，限制发帖";
    		}
    	}
    	
    	
    	//最近十分钟发帖的次数
    	$sql="select count(*) as total from grp_topic where author_uid=$uid and create_time>='".date("Y-m-d H:i:s",time()-60*10)."'";
    	$numInfo=$this->db->query($sql)->first_row("array");
    	$publishNum=$numInfo['total'];
    	$check3=($publishNum>=4);
    	if($check3){
    		return "系统发现你发帖频率过快，限制发帖";
    	}
    	
    	//检查该IP在最近十分钟之内注册用户数
    	$ipInfo=$this->db->query("select count(*) as total from uc_members where regdate>=".(time()-10*60)." and regip='".$regIp."'")->first_row("array");
    	$ipTotal=$ipInfo['total'];
    	$check4=($ipTotal>3);//检查同一IP注册的用户数量
    	if($check4){
    		return "系统发现你所在的IP目前注册用户数过多，限制发帖";
    	}
 
    }
}
