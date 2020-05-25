<?php

class TelegramBot {
    public function __construct(string $token) {
        $this->token = $token;
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

        $this->update = $this->JSONToTelegramObject($this->update, "update");

        $this->json = json_decode(implode(file("json.json")), true);
    }


    private function getObjectType(string $parameter_name){
        $this->json = json_decode(implode(file("json.json")), true);
        return isset($this->json['available_types'][$parameter_name]) ? $this->json['available_types'][$parameter_name] : false;

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

        return new TelegramObject($this->getObjectType($parameter_name), $json);
    }
}

class TelegramObject extends TelegramBot{
    public function __construct(string $type, array $json){
        $this->type = $type;
        $this->json = $json;
        return $this;
    }
}

?>
