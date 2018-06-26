<?php
namespace App\Console\Commands\util;

class BaiduSitePusher {
  const MAX = 2000;

  protected $urls = [];

  public static function push($url)
  {
    array_push($this->urls, $url);
    if (count($this->urls) > static::MAX) {
      self::submit($this->urls);
      $this->urls = [];
    }
  }

  protected static function submit($urls)
  {
    var_dump($urls);exit;
    
    $api = 'http://data.zz.baidu.com/urls?site=www.usleju.com&token=fDGbZU9vzaxnryzi';
    $ch = curl_init();
    $options =  array(
        CURLOPT_URL => $api,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => implode("\n", $urls),
        CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
    );
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    //echo $result;
  }
}
