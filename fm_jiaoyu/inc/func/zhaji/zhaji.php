<?php
use JPush\Client as JPush;

$path = dirname(__FILE__);

require_once( $path . '/autoload.php');


class zhaji{

    private $app_key        = '44c7922a0737eb35207d572c';
    private $master_secret  = '9976155cc5cd13214c9441eb';

    private $debug = false;

    
    public function get_push_code(){

    }

    public function set_debug($debug = true){
        $this->debug = $debug;
    }

    public function push($tag ,$msg){
        if(!$tag){
            return false;
        }


        $client = new JPush($this->app_key, $this->master_secret);

        $push_payload = $client->push()
            ->setPlatform('all')
            //->addAllAudience()
            ->addTag(array($tag))
            //->setNotificationAlert('opendoor');
            ->message($msg);
        try {
            $response = $push_payload->send();
            return true;
            //exit('开门成功！');
            //print_r($response);
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            if ($this->debug){
                echo "tag = ".$tag.'<br/>' ;
                echo $msg.'<br/>';
                exit;
            }
            // try something here
            //print $e;
            return false;
        } catch (\JPush\Exceptions\APIRequestException $e) {
            // try something here
            if ($this->debug){
                echo "tag = ".$tag.'<br/>' ;
                echo $msg.'<br/>';
                print $e;
                exit;
            }
            return false;
        }
        return false;
    }


}