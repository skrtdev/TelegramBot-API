<?php

class TelegramBot {
    private $token, $settings, $json;


    public function __construct(string $token, bool $read_update = true, array $settings = []) {
        $this->token = $token;
        $this->settings = (object) $settings;

        $this->json = json_decode(implode(file("json.json")), true);

        if($read_update){
            $this->update = json_decode(file_get_contents("php://input"), true);

            $this->update = $this->JSONToTelegramObject( $this->update, "Update");
        }
        else{
            $this->update = json_decode('{
                                    "update_id": 143957909,
                                    "message": {
                                        "message_id": 210450,
                                        "from": {
                                            "id": 634408248,
                                            "is_bot": false,
                                            "first_name": "⁣",
                                            "last_name": "𝓖 Ðᴇᴠ ✓✰",
                                            "username": "gaetano555",
                                            "language_code": "it"
                                        },
                                        "chat": {
                                            "id": 634408248,
                                            "first_name": "⁣",
                                            "last_name": "𝓖 Ðᴇᴠ ✓✰",
                                            "username": "gaetano555",
                                            "type": "private"
                                        },
                                        "date": 1590425893,
                                        "text": "start?"
                                    }
                                }', true);

            $this->update = $this->JSONToTelegramObject( $this->update, "Update");
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

        if(isset($this->json['available_methods'][$method]['returns'])) return $this->JSONToTelegramObject($decoded['result'], $this->json['available_methods'][$method]['returns']);
    }

    private function getObjectType(string $parameter_name){
        return isset($this->json['available_types'][$parameter_name]) ? $this->json['available_types'][$parameter_name] : false;
    }

    private function JSONToTelegramObject(array $json, string $parameter_name){
        //echo "JSONToTelegramObject $parameter_name\n";
        foreach($json as $key => $value){
            //echo "$key => $value \n";
            $valuetype = gettype($value);

            if($valuetype === "array"){
                if($this->getObjectType($key)){
                    $json[$key] = $this->JSONToTelegramObject($value, $this->getObjectType($key));
                }
            }
        }

        return new TelegramObject($parameter_name, $json, $this);
    }
}

class TelegramObject extends TelegramBot{
    public function __construct(string $type, array $json, TelegramBot $TelegramBot){

        $this->type = $type;
        $this->TelegramBot = $TelegramBot;

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
}

?>
