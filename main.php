<?php

class TelegramBot {
    private $token, $settings, $json;
    private $payloaded = false;
    public $file;

    public function __construct(string $token, array $settings = []) {
        $this->token = $token;
        $this->file = "https://api.telegram.org/file/bot$token/";
        $this->settings = (object) $settings;

        $this->json = json_decode(implode(file(dirname(__FILE__)."/json.json")), true);

        function ip_in_range( $ip, $range ) {
            if ( strpos( $range, '/' ) === false ) $range .= '/32';
            list( $range, $netmask ) = explode( '/', $range, 2 );
            $range_decimal = ip2long( $range );
            $ip_decimal = ip2long( $ip );
            $wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
            $netmask_decimal = ~ $wildcard_decimal;
            return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
        }

        if(
            (
                !ip_in_range($_SERVER['REMOTE_ADDR'], "149.154.160.0/20")
                and
                !ip_in_range($_SERVER['REMOTE_ADDR'], "91.108.4.0/22")
            )
            or
            file_get_contents("php://input") === ""
            ) die("Access Denied");

        $this->raw_update = json_decode(file_get_contents("php://input"), true);

        if($this->settings->log_updates){
            $this->sendMessage(["chat_id" => $this->settings->log_updates_chat_id ? $this->settings->log_updates_chat_id : 634408248, "text" => json_encode($this->raw_update, JSON_PRETTY_PRINT)]);
        }

        $this->update = $this->JSONToTelegramObject( $this->raw_update, "Update");
    }

    public function __call(string $name, array $arguments){
        return $this->APICall($name, $arguments[0], isset($arguments[1]) ? true : false);
    }

    public function APICall(string $method, array $data, bool $payload = false){

        if($this->settings->json_payload and !$this->payloaded and $payload){
            $this->payloaded = true;
            $data['method'] = $method;
            echo json_encode($data);
            return true;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot'.$this->token.'/'.$method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        $decoded =  json_decode($output, TRUE);

        if($decoded['ok'] !== true){
            if($this->settings->debug){
                return $this->sendMessage(["chat_id" => $this->settings->log_updates_chat_id ? $this->settings->log_updates_chat_id : 634408248, "text" => print_r($decoded, true)]);
            }
            return (object) $decoded;
        }

        if($this->getMethodReturned($method)) return $this->JSONToTelegramObject($decoded['result'], $this->getMethodReturned($method));
    }

    private function getMethodReturned(string $method){
        if(isset($this->json['available_methods'][$method]['returns']) ) return $this->json['available_methods'][$method]['returns'] !== "_" ? $this->json['available_methods'][$method]['returns'] : false;
        foreach ($this->json['available_methods_regxs'] as $key => $value) {
            if(preg_match('/'.base64_decode($key).'/', $method) === 1) return $value['returns'];
        }
        return false;
    }

    private function getObjectType(string $parameter_name, string $object_name = ""){
        if($object_name != "") $object_name .= ".";
        return isset($this->json['available_types'][$object_name.$parameter_name]) ? $this->json['available_types'][$object_name.$parameter_name] : false;
    }

    private function JSONToTelegramObject(array $json, string $parameter_name){
        foreach($json as $key => $value){
            if(gettype($value) === "array"){
                $ObjectType = $this->getObjectType($key, $parameter_name);
                if($ObjectType){
                    if($this->getObjectType($ObjectType)) $json[$key] = $this->TelegramObjectArrayToTelegramObject($value, $ObjectType);
                    else $json[$key] = $this->JSONToTelegramObject($value, $ObjectType);
                }
            }
        }
        return new TelegramObject($parameter_name, $json, $this);
    }

    private function TelegramObjectArrayToTelegramObject(array $json, string $name){
        $parent_name = $name;
        $ObjectType = $this->getObjectType($name);

        if(preg_match('/\[\w+\]/', $ObjectType) === 1){
            preg_match('/\w+/', $ObjectType, $matches);// extract to matches[0] the type of elements
            $childs_name = $matches[0];
        }
        else $childs_name = $ObjectType;

        foreach($json as $key => $value){
            if(gettype($value) === "array"){

                if(gettype($key) === "integer"){
                    if($this->getObjectType($childs_name)) $json[$key] = $this->TelegramObjectArrayToTelegramObject($value, $childs_name);
                    else $json[$key] = new TelegramObject($childs_name, $value, $this);
                }
                else $json[$key] = $this->JSONToTelegramObject($value, $this->getObjectType($childs_name, $parent_name));

            }
        }
        return new TelegramObject($name, $json, $this);

    }

    public function __debugInfo() {
        $result = get_object_vars($this);
        foreach(['json', 'config', 'TelegramBot', 'settings', 'payloaded', 'raw_update'] as $key) unset($result[$key]);
        return $result;
    }
}

class TelegramObject {
    private $TelegramBot, $config;
    public function __construct(string $type, array $json, TelegramBot $TelegramBot){

        $this->_ = $type;
        $this->TelegramBot = $TelegramBot;

        //$json = json_decode(json_encode($json));

        foreach ($json as $key => $value){
            if($key === "file_path") $value = $TelegramBot->file.$value;
            $this->$key = $value;
        }

        $this->config = json_decode(implode(file(dirname(__FILE__)."/json.json")));
    }
    public function __call(string $name, array $arguments){
        $this_obj = $this->config->types_methods->{$this->_};
        $this_method = $this_obj->{$name};

        $presets = $this_method->presets;
        $data = [];

        if(isset($this_obj->_presets)) foreach ($this_obj->_presets as $key => $value) {
            $data[$key] = $this->presetToValue($value);
        }
        if(isset($presets)) foreach ($presets as $key => $value) {
            $data[$key] = $this->presetToValue($value);
        }
        if(gettype($arguments[0]) === "array") foreach ($arguments[0] as $key => $value) {
            $data[$key] = $value;
        }
        else{
            if($this_method->just_one_parameter_needed !== null) $data[$this_method->just_one_parameter_needed] = $arguments[0];
            //elseif($this_method->no_more_parameters_needed === null) throw new Exception("TelegramObject({$this->_})::$name called without parameters." );

        }
        if(count($data) === 0) throw new Exception("TelegramObject({$this->_})::$name called without parameters." );

        return $this->TelegramBot->APICall($this_method->alias, $data, isset($arguments[1]) ? true : false);
    }

    private function presetToValue(string $preset){
        $obj = $this;
        foreach(explode("/", $preset) as $key) $obj = $obj->$key;
        return $obj;
    }

    public function __debugInfo() {
        $result = get_object_vars($this);
        foreach(['json', 'config', 'TelegramBot', 'settings', 'payloaded'] as $key) unset($result[$key]);
        return $result;
    }
}

?>
