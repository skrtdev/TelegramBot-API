<?php

class TelegramBot {
    public function __construct(string $token, bool $read_update = true) {
        $this->token = $token;

        if($read_update){
            $this->update = json_decode(file_get_contents("php://input"), true);
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

            $this->update = $this->JSONToTelegramObject((array) $this->update, "Update");
        }
        $this->json = json_decode(implode(file("json.json")), true);
    }


    public function __call(string $name, array $arguments){
        return $this->APICall($name, $arguments[0]);
    }

    public function APICall(string $method, array $data, string $token = null){
        if(!isset($this->json)) $this->json = json_decode(implode(file("json.json")), true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot'.( isset($token) ? $token : $this->token ).'/'.$method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        $decoded =  json_decode($output, TRUE);
        if(!$decoded['ok']) return (object) $decoded;
        if(isset($this->json['available_methods'][$method]['returns'])) return $this->JSONToTelegramObject($decoded['result'], $this->json['available_methods'][$method]['returns']);
    }

    private function getObjectType(string $parameter_name){
        return isset($this->json['available_types'][$parameter_name]) ? $this->json['available_types'][$parameter_name] : false;
        return $this->json[$parameter_name];
    }

    private function JSONToTelegramObject(array $json, string $parameter_name){
        foreach($json as $key => $value){
            $valuetype = gettype($value);

            if($valuetype === "array"){
                if($this->getObjectType($key)){
                    $json[$key] = $this->JSONToTelegramObject($value, $this->getObjectType($key));
                }
            }
        }

        return new TelegramObject($parameter_name, $json, $this->token);
    }
}

class TelegramObject extends TelegramBot{
    public function __construct(string $type, array $json, string $token){

        $this->type = $type;
        $this->token = $token;

        foreach ($json as $key => $value) {
            $this->$key = $value;
        }
        $this->$config = json_decode(implode(file("json.json")), true);

        if(isset($this->$config['types_methods'][$type])){

            foreach($this->$config['types_methods'][$type] as $key => $value){

                $this->{$key."_json"} = $value;

            }

        }

        $this->$config = json_decode(implode(file("json.json")));
    }
    public function __call(string $name, array $arguments){

        $this_method = $this->$config->types_methods->{$this->type}->{$name};

        $presets = $this_method->presets;
        $data = [];

        if(isset($presets)) foreach ($presets as $key => $value) {
            $data[$key] = $this->presetToValue($value);
        }
        foreach ($arguments[0] as $key => $value) {
            $data[$key] = $value;
        }

        return TelegramBot::APICall($this_method->alias, $data, $this->token);
    }

    private function presetToValue(string $preset){
        $obj = $this;
        foreach(explode("/", $preset) as $key) $obj = $obj->$key;
        return $obj;
    }
}

?>
