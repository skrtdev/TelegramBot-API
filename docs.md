# Documentation
--------
> All the BOTApi's methods can be used as methods of the TelegramBot class.
> There are only this library own methods
--------

## Creating the class
Create a such variable (in this Documentation it's called Bot) and instanciate the TelegramBot Class. Parameters are:
   * token (string)
   * read_update (boolean)
   * settings (array)

A simple example:
```php
header('Content-Type: application/json');
require("main.php");

$Bot = new TelegramBot("YOUR_TOKEN", true, [
    "json_payload" => true
]);
```
In this example, the settings array contains a key `json_payload` set to `true`. Doing so, the first API Call made will be print as payload, and afterwards processed by Telegram, making the bot **faster**  

### Available Methods
   * [reply](#reply)
   * [sendMessage](#sendMessage)
   * [forwardMessage](#forwardMessage)
   * [deleteMessage](#deleteMessage)

#### Setup Script
All the methods explained here are supposed to be in a script with this setup:
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

### reply
reply can be used only as a method of an Update Object.
reply acts just like sendMessage, sending a message in the Update chat with the specified text.

```php
// Update object
$update->reply([
    "text" => "message_text"
]);

/* or simply */

// Update object
$update->reply("message_text"); // just the text of the message
```


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

/* or simply */

// Chat object with just text
$chat->sendMessage("message_text");
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
    "from_chat_id" => 01234567,
    "message_id" => 0123456789
]);
```

### deleteMessage
deleteMessage can be used directly as a method of the main class, as a method of a Message Object (just delete that message) or as a metod of a Chat Object, in order to delete a message in that Chat.

```php
// main class
$Bot->deleteMessage([
    "chat_id" => 01234567,
    "message_id" => 0123456789
]);

// Message object
$message->delete(); // just delete

// Chat object
$chat->deleteMessage([
    "message_id" => 0123456789
]);

/* or simply */

// Chat object
$chat->deleteMessage(0123456789);```
