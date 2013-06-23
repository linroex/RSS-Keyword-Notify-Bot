<?php
	include('class/LIB_http.php');
	include('class/LIB_parse.php');
	include('class/LIB_rss.php');
	include('class/NexmoMessage.php');
	
	set_time_limit(300);
	
	//設定要處理的RSS地址
	$target=array(
		'http://www.nba.com/rss/nba_rss.xml'
	);
	
	//設定關鍵字
	$keywords=array(
		'taiwan',
		'悍創',
		'熱身賽',
		'preseason',
		'季前賽',
		'Taipei'
	);
	
	$sms=new NexmoMessage('key','secret');		//設定Nexmo的相關資訊
	$mongo=new MongoClient();
	$db=$mongo->nba;
	$rss_table=$db->rss;
	$find_table=$db->match;
	
	/*
	to->設定收簡訊的手機號碼，需含國碼
	from->設定簡訊寄送者，不可使用中文
	*/
	$sms_info=array('to'=>'+886123456789','from'=>'NAME');		
	
	$sms->sendText($sms_info['to'],$sms_info['from'],"runing");		//用來判斷程式是否有正確執行，當程式執行會先寄簡訊給使用者
	foreach($target as $rss_url){
		$data=download_parse_rss($rss_url);
		
		foreach($data['ILINK'] as $item_url){
			$item_url=trim($item_url);
			if($rss_table->findOne(array('url'=>$item_url))==NULL){
				$rss_table->save(array('url'=>$item_url,'readed'=>true));
				$text=strtolower(file_get_contents($item_url));
				foreach($keywords as $keyword){
					if(mb_strpos($text,strtolower($keyword),0,'utf-8')!=false){
						$find_table->save(array('url'=>$item_url,'keyword'=>$keyword));
						$sms->sendText($sms_info['to'],$sms_info['from'],"[$keyword]" . $item_url);
						break;
					}
				}
			}			
		}	
	}
	
	
?>