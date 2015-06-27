<?php

namespace Core;

/**
 * 请求信息
 */
class Request
{
    public function isCli()
    {
        return !strcasecmp(php_sapi_name(), 'cli');
    }
    
    public function isGet()
    {
        return strcasecmp($this->getServer('REQUEST_METHOD'), 'GET');
    }
    
    public function isPost()
    {
        return strcasecmp($this->getServer('REQUEST_METHOD'), 'POST');
    }
    
    public function isPut()
    {
        return strcasecmp($this->getServer('REQUEST_METHOD'), 'PUT');
    }
    
    public function isDelete()
    {
        return strcasecmp($this->getServer('REQUEST_METHOD'), 'DELETE');
    }
    
    public static function isAjax()
    {
        return strcasecmp($this->getServer('REQUEST_METHOD'),'xmlhttprequest');
    }       

    public function isMoblie()
    {
        if($userAgent = $this->getServer('HTTP_USER_AGENT'))
        {
            $mobileType = array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi",
                "android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio",
                "au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-",
                "compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_",
                "fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc",
                "huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo",
                "lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator",
                "meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront",
                "newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm",
                "panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover",
                "sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens",
                "sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit",
                "tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda",
                "voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
            foreach($mobileType as $device)
            {
                if(stristr($userAgent, $device))
                {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    
    public function getQuery()
    {
        
    }
    
    public function getUri()
    {
        
    }
    
    public function getPost()
    {
        
    }
    
    public function getPut()
    {
        
    }
    
    public function getDelete()
    {
    }
     
    /**
     * 获取$_SERVER信息
     * @param <string> 下标值
     * @param <mixed> 不存在返回的值
     * @return <string>
     */
    public function getServer($index, $default=NULL)
    {
        return empty($_SERVER[$index]) ? $defualt : addslashes($_SERVER[$index]);
    }
    
    /**
     * 获取ip地址
     */
    public static function getIp()
    {
        if(IS_CLI)
        {
            return '127.0.0.1';
        }
    
        $ip = NULL;
        $source = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach($source as $from)
        {
            if($ip = getenv($from))
            {
                break;
            }
        }
    
        return $ip;
    }
    
    /**
     * 获取PUT和DELETE参数
     * @return array
     */
    private function getPhpInput()
    {
        parse_str(file_get_contents('php://input'), $_INPUT);
        return $_INPUT;
    }
}