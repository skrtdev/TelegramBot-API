# TelegramBot-API
An elegant, Object-Oriented, reliable PHP Telegram Bot Interface

## Deal with it
In order to start using ---it---, you just need to create the class
```
$Bot = new TelegramBot("YOUR_TOKEN");
```

## Example
An example code of a simple bot that just forwards back what you send.

```php
header('Content-Type: application/json');
require("main.php");

$Bot = new TelegramBot("YOUR_TOKEN", [
    "json_payload" => true
]);
$update = $Bot->update;
$update->message->forward($update->message->chat->id, true);
```

Using `"json_payload" => true` and `true` in forward method, the api call will be print as payload, making the bot faster. Only one Api Call can use json payload

More info in the [Documentation](docs.md)
