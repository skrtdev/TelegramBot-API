# Documentation

All the methods explained here suppose to be in a script with this setup:
```php
header('Content-Type: application/json');
require("main.php");

$Bot = new TelegramBot("YOUR_TOKEN", true, [
    "json_payload" => true
]);

$update = $Bot->update;
$message = $update->message;
$chat = $message->chat;
$from = $message->from;
```

### Available Methods
   * [sendMessage](#sendMessage)
   * [forwardMessage](#forwardMessage)

### sendMessage
sendMessage can be used directly as a method of the main class, or as a metod of a Chat Object.

```php

// main class
$Bot->sendMessage([
    "chat_id" => 01234567,
    "text" => "message_text"
]);

// Chat object
$chat->sendMessage([
    "text" => "message_text"
]);
```

### forwardMessage
forwardMessage can be used directly as a method of the main class, as a method of a Message Object (just forwards that message) or as a metod of a Chat Object, (as forwardTo method), in order to forward in that Chat.

```php

// main class
$Bot->forwardMessage([
    "chat_id" => 01234567,
    "text" => "message_text"
]);

// Message object
$message->forward(01234567); // just the chat_id of the target chat

// Chat object (forwardTo)
$chat->forwardTo([
    "from_chat_id": 01234567,
    "message_id" => 0123456789
]);
```
