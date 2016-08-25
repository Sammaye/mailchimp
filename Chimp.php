<?php

namespace sammaye\mailchimp;

use GuzzleHttp\Client;

use sammaye\mailchimp\exceptions\MailChimpException;

class Chimp
{
    public $apikey;
    
    public $endPoint = 'https://{$dc}.api.mailchimp.com/3.0';
    
    public $methods = [
        'GET' => 200,
        'PUT' => 200,
        'POST' => 200,
        'PATCH' => 200,
        'DELETE' => 204
    ];
    
    private $_client;
    
    private $_exceptions = [
        'BadRequest' => 'BadRequestException',
        'InvalidAction' => 'InvalidActionException',
        'InvalidResource' => 'InvalidResourceException',
        'JSONParseError' => 'JSONParseErrorException',
        'APIKeyMissing' => 'APIKeyMissingException',
        'APIKeyInvalid' => 'APIKeyInvalidException',
        'Forbidden' => 'ForbiddenException',
        'UserDisabled' => 'UserDisabledException',
        'WrongDatacenter' => 'WrongDatacenterException',
        'ResourceNotFound' => 'ResourceNotFoundException',
        'MethodNotAllowed' => 'MethodNotAllowedException',
        'ResourceNestingTooDeep' => 'ResourceNestingTooDeepException',
        'InvalidMethodOverride' => 'InvalidMethodOverrideException',
        'RequestedFieldsInvalid' => 'RequestedFieldsInvalidException',
        'TooManyRequests' => 'TooManyRequestsException',
        'InternalServerError' => 'InternalServerErrorException',
        'ComplianceRelated' => 'ComplianceRelatedException'
    ];
    
    public function __construct($params = [])
    {
        foreach($params as $k => $v){
            $this->$k = $v;
        }
        $this->_client = new Client();
    }
    
    public function getApiEndpoint()
    {
        return preg_replace(
            '#\{\$dc\}#', 
            substr($this->apikey, strpos($this->apikey, '-', 0) + 1), 
            $this->endPoint
        );
    }
    
    public function get($action, $params = [])
    {
        return $this->execute('GET', $action, $params, 'query');
    }
    
    public function post($action, $params = [])
    {
        return $this->execute('POST', $action, $params);
    }
    
    public function patch($action, $params = [])
    {
        return $this->execute('PATCH', $action, $params);
    }
    
    public function put($action, $params = [])
    {
        return $this->execute('PUT', $action, $params);
    }
    
    public function delete($action, $params = [])
    {
        return $this->execute('DELETE', $action, $params);
    }
    
    public function execute($method, $action, $params = [], $defaultType = 'json')
    {
        $ro = [
            'headers',
            'body',
            'json',
            'query',
            'auth'
        ];
        
        $isOptions = false;
        foreach($ro as $o){
            if(isset($params[$o])){
                $isOptions = true;
            }
        }
        
        if(!$isOptions && count($params) > 0){
            $params = [$defaultType => $params];
        }

        $res = $this
            ->_client
            ->request(
                $method, 
                rtrim($this->getApiEndpoint(), '/') . '/' . $action . '/', 
                array_merge(
                    [
                        'auth' => ['', $this->apikey], 
                        'http_errors' => false
                    ], 
                    $params
                )
            );
        
        $successCode = is_array($this->methods[$method]) 
            ? $this->methods[$method][0] 
            : $this->methods[$method];
        
        $body = json_decode($res->getBody());
        if($res->getStatusCode() !== $successCode){
            $ename = preg_replace('#\s+#', '', strtolower($body->title));
        
            // Let's raise an exception
            foreach($this->_exceptions as $k => $v){
                $name = preg_replace('#\s+#', '', strtolower($k));
                if($name == $ename){
                    $cname = '\sammaye\mailchimp\exceptions\\' . $v;
                    break;
                }
            }
            
            $message = $body->title . ': ' . rtrim($body->detail, '.') 
                . ' (' . $body->type . ')';
            
            if(isset($cname)){
                throw new $cname($message, $body->status);
            }else{
                throw new MailChimpException($message, $body->status);
            }
        }else{
            return $body;
        }
    }
}