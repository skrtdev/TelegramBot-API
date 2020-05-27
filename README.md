# TelegramBot-API
An elegant, Object-Oriented, reliable PHP Telegram Bot Interface


An example code of a simple bot that just forward you back what you send him.

```php
header('Content-Type: application/json');
require("main.php");

$Bot = new TelegramBot("957563264:AAHBVJFr0jwUnXDhKdpdC6Lu_1T63c23z2U", true, [
    "json_payload" => true
]);

$update = $Bot->update;

$update->message->forward([
    "chat_id" => $update->message->chat->id
]);
```

Using `"json_payload" => true` will print the first api call as payload, making it faster
