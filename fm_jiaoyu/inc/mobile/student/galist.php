<?php
/**
 * 微教育模块
 *
 * @author 高贵血迹
 */
        global $_W, $_GPC;
        $weid = $_W['uniacid'];
		$schoolid = intval($_GPC['schoolid']);
		$openid = $_W['openid'];
        $getMy = $_GPC['getMy'];
   		if(!empty($_SESSION['user'])){
       		$userid = $_SESSION['user'];
   		}
   		$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where openid = :openid AND schoolid=:schoolid   AND weid=:weid", array(':openid' => $openid,':schoolid'=> $schoolid,':weid'=> $weid ));
   		if(!empty($it)){
	   		
	   	
   		}else{
	       	session_destroy();
		    $stopurl = $_W['siteroot'] .'app/'.$this->createMobileUrl('bangding', array('schoolid' => $schoolid));
			header("location:$stopurl");
			exit;
       		}
	       
     
        //查询是否用户登录	
        if(!empty($_GPC['userid'])){
	         $userid = $_GPC['userid'];	
        }
       
		$school = pdo_fetch("SELECT * FROM " . tablename($this->table_index) . " where weid = :weid AND id=:id ", array(':weid' => $weid, ':id' => $schoolid));
				
			
		$galisttemp =  pdo_fetchall("SELECT * FROM " . tablename($this->table_groupactivity) . " where weid = '{$weid}' And schoolid = '{$schoolid}'  AND type=1  ORDER BY starttime DESC");
		$galistdone = array();
		//var_dump($galistdone);
		//die();
		if($_GPC['op'] == 'signup'){
			$iii = 0 ;
			$it = pdo_fetch("SELECT * FROM " . tablename($this->table_user) . " where id = :id AND tid =:tid ", array(':id' => $userid,':tid'=> 0 ));
      		$students = pdo_fetch("SELECT * FROM " . tablename($this->table_students) . " where weid = :weid AND id = :id", array(':weid' => $weid, ':id' => $it['sid']));
		foreach( $galisttemp as $key => $value ){
		      		$checksignup =  pdo_fetch("SELECT * FROM " . tablename($this->table_groupsign) . " where weid = :weid AND gaid = :gaid AND sid =:sid", array(':weid' => $weid, ':gaid' => $value['id'] ,':sid'=>$it['sid']));
		      		//var_dump($checksignup);
					$bjarray =  explode(',', $value['bjarray']);
					if(in_array($students['bj_id'],$bjarray)){
						$galistdone[$iii] = $value ;
						if($checksignup){
							$galistdone[$iii]['issign'] = 1 ;
						}
						$iii++;
					}
				}
			}else{
				$galistdone = $galisttemp ;
			}
		
		$thistime = trim($_GPC['limit']);
		if($thistime){
			$thistime = $thistime +1 ;
			$thistime1 = $thistime + 11 ;
			$mygalist1 =array_slice($galistdone,$thistime,10);

			include $this->template('comtool/galist');	 
		}else{
			
				$mygalist =array_slice($galistdone,0,10);

				
			include $this->template(''.$school['style2'].'/galist');	
		}				        		
           
?>