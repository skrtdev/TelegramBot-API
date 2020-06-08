<?php

class TelegramBot {
    private $token, $settings, $json;

    public function __construct(string $token, bool $read_update = true, array $settings = []) {
        $this->token = $token;
        $this->settings = (object) $settings;

        $this->json = json_decode(implode(file("json.json")), true);

        if($read_update){
            $this->raw_update = json_decode(file_get_contents("php://input"), true);

            if($this->settings->log_updates){
                $this->APICall("sendMessage", ["chat_id" => 634408248, "text" => json_encode(json_decode(file_get_contents("php://input"), true), JSON_PRETTY_PRINT)]);
            }

            $this->update = $this->JSONToTelegramObject( $this->raw_update, "Update");
        }
        else{
            $this->raw_update = json_decode('{
    "update_id": 789389323,
    "message": {
        "message_id": 25222,
        "from": {
            "id": 634408248,
            "is_bot": false,
            "first_name": "\u2063",
            "last_name": "\ud835\udcd6 \u00d0\u1d07\u1d20 \u2713\u2730",
            "username": "gaetano555",
            "language_code": "it"
        },
        "chat": {
            "id": 634408248,
            "first_name": "\u2063",
            "last_name": "\ud835\udcd6 \u00d0\u1d07\u1d20 \u2713\u2730",
            "username": "gaetano555",
            "type": "private"
        },
        "date": 1591529063,
        "animation": {
            "file_name": "video.mp4",
            "mime_type": "video\/mp4",
            "duration": 10,
            "width": 352,
            "height": 640,
            "thumb": {
                "file_id": "AAMCBAADGQEAAmKGXtzOZ-CNOtW99ngmjKmpaNErRFMAAgUHAALjGeBTkuaDUCFiVqknHW4iXQADAQAHbQAD_A0AAhoE",
                "file_unique_id": "AQADJx1uIl0AA_wNAAI",
                "file_size": 5603,
                "width": 176,
                "height": 320
            },
            "file_id": "CgACAgQAAxkBAAJihl7czmfgjTrVvfZ4JoypqWjRK0RTAAIFBwAC4xngU5Lmg1AhYlapGgQ",
            "file_unique_id": "AgADBQcAAuMZ4FM",
            "file_size": 929519
        },
        "document": {
            "file_name": "video.mp4",
            "mime_type": "video\/mp4",
            "thumb": {
                "file_id": "AAMCBAADGQEAAmKGXtzOZ-CNOtW99ngmjKmpaNErRFMAAgUHAALjGeBTkuaDUCFiVqknHW4iXQADAQAHbQAD_A0AAhoE",
                "file_unique_id": "AQADJx1uIl0AA_wNAAI",
                "file_size": 5603,
                "width": 176,
                "height": 320
            },
            "file_id": "CgACAgQAAxkBAAJihl7czmfgjTrVvfZ4JoypqWjRK0RTAAIFBwAC4xngU5Lmg1AhYlapGgQ",
            "file_unique_id": "AgADBQcAAuMZ4FM",
            "file_size": 929519
        }
    }
}', true);

            $this->update = $this->JSONToTelegramObject($this->raw_update, "Update");
        }
    }

    private $payloaded = false;

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

        if(!isset($this->json)) $this->json = json_decode(implode(file("json.json")), true);

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
                return $this->APICall("sendMessage", ["chat_id" => 634408248, "text" => print_r($decoded, true)]);
            }
            return (object) $decoded;
        }

        if($this->getMethodReturned($method)) return $this->JSONToTelegramObject($decoded['result'], $this->getMethodReturned($method));
    }

    private function getMethodReturned(string $method){
        if( isset($this->json['available_methods'][$method]['returns']) ) return $this->json['available_methods'][$method]['returns'] !== "_" ? $this->json['available_methods'][$method]['returns'] : false;
        foreach ($this->json['available_methods_regxs'] as $key => $value) {
            if(preg_match('/'.base64_decode($key).'/', $method) === 1) return $value['returns'];
        }
        return false;
    }

    private function getObjectType(string $parameter_name, string $object_name = ""){
        //$object_name = $object_name != "" ? $object_name."." : $object_name;
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
        $ObjectType = $this->getObjectType($name);

        if(preg_match('/\[\w+\]/', $ObjectType) === 1){
            preg_match('/\w+/', $ObjectType, $matches);// extract to matches[0] the type of elements
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

        $this->_ = $type;
        $this->TelegramBot = $TelegramBot;

        //$json = json_decode(json_encode($json));

        foreach ($json as $key => $value) $this->$key = $value;

        $this->config = json_decode(implode(file("json.json")));
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
        else trigger_error("no presets");
        if(gettype($arguments[0]) === "array") foreach ($arguments[0] as $key => $value) {
            $data[$key] = $value;
        }
        else{
            if($this_method->just_one_parameter_needed !== null) $data[$this_method->just_one_parameter_needed] = $arguments[0];
            elseif($this_method->no_more_parameters_needed === null) throw new Exception("TelegramObject({$this->_})::$name called without parameters." );

        }

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
