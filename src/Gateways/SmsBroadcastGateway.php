<?php

namespace Softon\Sms\Gateways;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SmsBroadcastGateway implements SmsGatewayInterface  {

    protected $gwvars = array();
    protected $url = 'https://api.smsbroadcast.com.au';
    protected $request = '';
    public $status = false;
    public $response = '';
    public $countryCode='';

    function __construct()
    {
        $this->gwvars['username'] = Config::get('sms.smsbroadcast.username');
        $this->gwvars['password'] = Config::get('sms.smsbroadcast.password');
        $this->gwvars['from'] = Config::get('sms.smsbroadcast.from');
        $this->gwvars['maxsplit'] = Config::get('sms.smsbroadcast.maxsplit');
        $this->countryCode = Config::get('sms.countryCode');
    }

    function getUrl()
    {
        return $this->url;
    }

    public function sendSms($mobile,$message)
    {
        $mobile = $this->addCountryCode($mobile);

        if(is_array($mobile)){
            $mobile = $this->composeBulkMobile($mobile);
        }

        $this->gwvars['to'] = $mobile;
        $this->gwvars['message'] = $message;
        $client = new \GuzzleHttp\Client();
        $this->response = $client->post($this->getUrl(),['body'=>$this->gwvars])->getBody()->getContents();
        Log::info('SMS Broadcast Response: '.$this->response);
        return $this;
    }

    /**
     * Create Send to Mobile for Bulk Messaging
     * @param $mobile
     * @return string
     */
    private function composeBulkMobile($mobile)
    {
        return implode(',',$mobile);
    }

    /**
     * Prepending Country Code to Mobile Numbers
     * @param $mobile
     * @return array|string
     */
    private function addCountryCode($mobile)
    {
        if(is_array($mobile)){
            array_walk($mobile, function(&$value, $key) { $value = $this->countryCode.$value; });
            return $mobile;
        }

        return $this->countryCode.$mobile;
    }



    /**
     * Check Response
     * @return array
     */
    public function response(){
        return $this->response;
    }
}