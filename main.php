<?php

class TelegramBot {
    //private $token, $settings, $json;
    private $token, $settings;

    public function __construct(string $token, bool $read_update = true, array $settings = []) {
        $this->token = $token;
        $this->settings = (object) $settings;

        $this->json = json_decode(implode(file("json.json")), true);

        if($read_update){
            $this->update = json_decode(file_get_contents("php://input"), true);

            if($this->settings->log_updates){
                $this->APICall("sendMessage", ["chat_id" => 634408248, "text" => json_encode(json_decode(file_get_contents("php://input"), true), JSON_PRETTY_PRINT)]);
            }

            $this->update = $this->JSONToTelegramObject( $this->update, "Update");
        }
        else{
            $this->update = json_decode('{
                "update_id": 789388580,
                "callback_query": {
                    "id": "2724762678824821309",
                    "from": {
                        "id": 634408248,
                        "is_bot": false,
                        "first_name": "\u2063",
                        "last_name": "\ud835\udcd6 \u00d0\u1d07\u1d20 \u2713\u2730",
                        "username": "gaetano555",
                        "language_code": "it"
                    },
                    "message": {
                        "message_id": 23740,
                        "from": {
                            "id": 957563264,
                            "is_bot": true,
                            "first_name": "NewDoge Click Bot",
                            "username": "NewDogeClickBot"
                        },
                        "chat": {
                            "id": 634408248,
                            "first_name": "\u2063",
                            "last_name": "\ud835\udcd6 \u00d0\u1d07\u1d20 \u2713\u2730",
                            "username": "gaetano555",
                            "type": "private"
                        },
                        "date": 1591440290,
                        "text": "you say a",
                        "reply_markup": {
                            "inline_keyboard": [
                                [
                                    {
                                        "text": "you say ",
                                        "url": "http:\/\/google.it\/"
                                    },
                                    {
                                        "text": "you say ",
                                        "callback_data": "google"
                                    }
                                ]
                            ]
                        }
                    },
                    "chat_instance": "-8969329729616106374",
                    "data": "google"
                }
            }', true);

            $this->update = $this->JSONToTelegramObject($this->update, "Update");
        }
    }

    private $payloaded = false;

    public function __call(string $name, array $arguments){
        return $this->APICall($name, $arguments[0]);
    }

    public function APICall(string $method, array $data, string $token = null){
        if($this->settings->json_payload and !$this->payloaded){
            $this->payloaded = true;
            $data['method'] = $method;
            echo json_encode($data);
            return true;
        }

        if(!isset($this->json)) $this->json = json_decode(implode(file("json.json")), true);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot'.( isset($token) ? $token : $this->token ).'/'.$method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        $decoded =  json_decode($output, TRUE);

        if($decoded['ok'] !== true){
            if($this->settings->debug){
                return $this->APICall("sendMessage", ["chat_id" => 634408248, "text" => print_r($decoded, true)], $token);
            }
            return (object) $decoded;
        }

        if($this->getMethodReturned($method)) return $this->JSONToTelegramObject($decoded['result'], $this->getMethodReturned($method));
    }

    private function getMethodReturned(string $method){
        if( isset($this->json['available_methods'][$method]['returns']) ) return $this->json['available_methods'][$method]['returns'];
        foreach ($this->json['available_methods_regxs'] as $key => $value) {
            if(preg_match('/'.base64_decode($key).'/', $method) === 1) return $value['returns'];
        }
        return false;
    }

    private function getObjectType(string $parameter_name, string $object_name = ""){
        $object_name = $object_name != "" ? $object_name."." : $object_name;
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
        $ObjectType = $this->getObjectType($name);

        if(preg_match('/\[\w+\]/', $ObjectType) === 1){
            preg_match('/\w+/', $ObjectType, $matches);// matches[0] is the new field type i think
            foreach($json as $key => $value){
                if(gettype($value) === "array") $json[$key] = $this->TelegramObjectArrayToTelegramObject($value, $matches[0]);
            }
        }

        return new TelegramObject($name, $json, $this);

    }

    public function __debugInfo() {
        $result = get_object_vars($this);
        foreach(['json', 'config', 'TelegramBot', 'settings', 'payloaded'] as $key) unset($result[$key]);
        return $result;
    }
}

class TelegramObject {
    private $TelegramBot, $config;
    public function __construct(string $type, array $json, TelegramBot $TelegramBot){

        $this->type = $type;
        $this->TelegramBot = $TelegramBot;

        //$json = json_decode(json_encode($json));

        foreach ($json as $key => $value) {
            $this->$key = $value;
        }

        $this->config = json_decode(implode(file("json.json")));
    }
    public function __call(string $name, array $arguments){
        $this_method = $this->config->types_methods->{$this->type}->{$name};

        $presets = $this_method->presets;
        $data = [];
        if(isset($presets)) foreach ($presets as $key => $value) {
            $data[$key] = $this->presetToValue($value);
        }
        else trigger_error("no presets");
        if(gettype($arguments[0]) === "array") foreach ($arguments[0] as $key => $value) {
            $data[$key] = $value;
        }
        else{
            if($this_method->just_one_parameter_needed !== null) $data[$this_method->just_one_parameter_needed] = $arguments[0];
            elseif($this_method->no_more_parameters_needed === null) throw new Exception("TelegramObject::$name called without parameters." );

        }

        return $this->TelegramBot->APICall($this_method->alias, $data);
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
