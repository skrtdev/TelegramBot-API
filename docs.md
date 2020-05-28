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
[sendMessage](#sendMessage)

[forwardMessage](#sendMessage)

#### sendMessage
sendMessage can be used directly as a method of the main class, or as a metod of a Chat Object

```php

// main class
$Bot->sendMessage([
    "chat_id" => "01234567",
    "text" => "message_text"
]);

// Chat object
$chat->sendMessage([
    "text" => "message_text"
]);
```
