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

            $this->update = $this->JSONToTelegramObject($this->update, "Update");
        }
        $this->json = json_decode(implode(file("json.json")), true);
    }

    //private $json = implode(file("json.json"));

    public function APICall(string $method, array $data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot'.$this->token.'/'.$method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, TRUE);
    }

    private function getObjectType(string $parameter_name){
        $this->json = json_decode(implode(file("json.json")), true);
        echo "getObjectType $parameter_name";
        var_dump($this->json['available_types'][$parameter_name]);
        //var_dump($this->json);
        return isset($this->json['available_types'][$parameter_name]) ? $this->json['available_types'][$parameter_name] : false;
        return $this->json[$parameter_name];

    }
    //public $tryP = "func";
    private function JSONToTelegramObject(array $json, string $parameter_name){
        echo "JSONToTelegramObject\n";
        foreach($json as $key => $value){
            echo "$key => $value \n";
            $valuetype = gettype($value);


            if($valuetype === "array"){
                if($this->getObjectType($key)){
                    $json[$key] = $this->JSONToTelegramObject($value, $this->getObjectType($key));
                }
            }
        }

        return new TelegramObject($parameter_name, $json);
    }
}

class TelegramObject extends TelegramBot{
    public function __construct(string $type, array $json){
        $this->type = $type;
        $this->json = $json;
        var_dump(new TelegramBot("a", false));
        //return $this;
    }
}

?>
