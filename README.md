# TelegramBot-API
An elegant, Object-Oriented, reliable PHP Telegram Bot Interface


## Deal with it
In order to start using ---it---, you just need to create the class
`$Bot = new TelegramBot("YOUR_TOKEN");`


## Example
An example code of a simple bot that just forward you back what you send him.

```php
header('Content-Type: application/json');
require("main.php");

$Bot = new TelegramBot("YOUR_TOKEN", true, [
    "json_payload" => true
]);

$Bot->update->message->forward([
    "chat_id" => $update->message->chat->id
]);
```

Using `"json_payload" => true` will print the first api call as payload, making it faster

More info in the [Documentation](docs.md)
