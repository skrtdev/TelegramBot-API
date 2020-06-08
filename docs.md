# Documentation
--------
> All the BOTApi's methods can be used as methods of the TelegramBot class.
> There are only this library own methods
--------

## Creating the class
Create a variable (in this Documentation it's called $Bot) and instanciate the TelegramBot Class. Parameters are:
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
In this example, the settings array contains a key `json_payload` set to `true`. Doing so, the first API Call with 2nd parameter set to true will be print as payload, and afterwards processed by Telegram, making the bot **faster**  

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
$user = $message->from;
```

## All Methods
How to use any BOTApi Method:
```php
$Bot->METHOD_NAME([
    "field1_name" => "field1_value",
    "field2_name" => "field2_value"
])
```

### Available Methods
   * [reply](#reply)
   * [sendMessage](#sendMessage)
   * [forwardMessage](#forwardMessage)
   * [deleteMessage](#deleteMessage)
   * [answerCallbackQuery](#answerCallbackQuery)
   * [editMessageText](#editMessageText)
   * [sendChatAction](#sendChatAction)
   * [getUserProfilePhotos](#getUserProfilePhotos)


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
sendMessage can be used directly as a method of the main class, or as a method of a Chat Object.

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
forwardMessage can be used directly as a method of the main class, as a method of a Message Object (just forwards that message) or as a method of a Chat Object, (as forwardTo method), in order to forward in that Chat.

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

### answerCallbackQuery
answerCallbackQuery can be used directly as a method of the main class or as a method of a CallbackQuery Object, (as answer method), in order to answer that CallbackQuery.

```php
$CallbackQuery = $update->callback_query;

// main class
$Bot->answerCallbackQuery([
    "callback_query_id" => 012345678901234567,
    "text" => "some text"
]);

// CallbackQuery object
$CallbackQuery->answer(); // just answer
$CallbackQuery->answer("text"); // just text

$CallbackQuery->answer([
    "text" => "some text",
    "show_alert" => true
]);
```

### editMessageText
editMessageText can be used directly as a method of the main class or as a method of a Message Object, (as editText method), in order to edit that Message.

```php
// main class
$Bot->editMessageText([
    "chat_id" => 01234567,
    "message_id" => 0123456789,
    "text" => "new text"
]);

// Message object
$message->editText("new text"); // just text

$message->editText([
    "text" => "<b>new text</b>",
    "parse_mode" => "html"
]);
```

### sendChatAction
sendChatAction can be used directly as a method of the main class or as a method of a Chat Object, (as sendAction method), in order to send an Action that Chat.

```php
// main class
$Bot->sendChatAction([
    "chat_id" => 01234567,
    "action" => "typing"
]);

// Chat object
$chat->sendAction("typing"); // just action
```

### getUserProfilePhotos
getUserProfilePhotos can be used directly as a method of the main class or as a method of a Chat Object, (as getProfilePhotos method),, in order to get Profile Photos of that User.

```php
// main class
$Bot->getUserProfilePhotos([
    "user_id" => 01234567,
    "limit" => 10
]);

// User object
$user->getProfilePhotos(10); // just limit

$user->getProfilePhotos([
    "limit" => 10,
    "offset" => 5
]);
```
