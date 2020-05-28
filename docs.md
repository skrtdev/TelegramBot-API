# Documentation


## Available Methods
[sendMessage](#sendMessage)
[forwardMessage](#sendMessage)

## sendMessage
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
