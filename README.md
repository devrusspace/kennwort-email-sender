<h1>
Kennwort email sender
</h1>

<p>

[![Latest Stable Version](https://poser.pugx.org/devrusspace/kennwort-email-sender/version)](//packagist.org/packages/devrusspace/kennwort-email-sender)
[![Latest Unstable Version](https://poser.pugx.org/devrusspace/kennwort-email-sender/v/unstable)](https://packagist.org/packages/devrusspace/kennwort-email-sender)
[![License](https://poser.pugx.org/devrusspace/kennwort-email-sender/license)](https://packagist.org/packages/devrusspace/kennwort-email-sender)
</p>

Отправка транзакционных и тригерных e-mail сообщений с использованием шаблонов созданных в личном кабинете сервиса https://kennwort.ru. Шаблоны используют механизм layout, что позволяет просто вносить изменения в ваши тригерные и транзакционные шаблоны писем.

Документация по api размещена по адресу https://api.kennwort.ru/doc.

Установка
------------

Предпочтительный способ установки этого расширения - через [composer](http://getcomposer.org/download/).

Выполните

```
php composer.phar require devrusspace/kennwort-email-sender "^1.0"
```

или добавьте

```
"devrusspace/kennwort-email-sender": "^1.0"
```

в секцию require вашего файла `composer.json` и выполните `php composer.phar install`.

Использование
-----
[Создадим токен приложения](https://merchant.kennwort.ru/user-advertiser-api-token/create). Обратимся к компоненте, с передачей созданного токена из вашего личного кабинета.
```php
<?php
$token = 'токен_вашего_приложения_сгенерированный_в_личном_кабинете';
$client = new \Devrusspace\Kennwort\ApiClient($token);
```
### Отправка письма на основе шаблона
Шаблон письма предварительно должен быть создан в личном кабинете. Имя и e-mail отправителя указываются при создании/редактировании шаблона.
```php
<?php  
$template = 'user/registration';
$mailTo = ['test@test.com' => 'Имя получателя'];
$params = ['var1' => 'значение переменной для подстановки в шаблон'];
$result = $client->sendEmail($template, $mailTo, $params);
$emailId = $result->email->id; // идентификатор отправленного сообщения
```
Пример ответа:
```php
print_r($result);

stdClass Object
(
    [result] => 1
    [email] => stdClass Object
        (
            [id] => 42bbd42d6dafddb160d61530f8ce9bb2
        )
)
```

Идентификатор письма полученный в ответе вы можете использовать для получения расширенной информации по отправленному письму.

### Отправка произвольного сообщения
Отправка письма, сгенерированного на стороне вашего приложения  :
```php
<?php  
$senderId = 'идентификатор_email_из_вашего_списка_отправителей'
$mailTo = ['test@test.com' => 'Имя получателя'];
$subject = 'Заголовок письма';
$body = 'Html код содержимого письма';
$result = $client->sendEmailBody($senderId, $mailTo, $subject, $body);
```
Ответ идентичен методу `Отправка письма на основе шаблона`

### Получение информации об отправленном сообщении
После отправки сообщения клиенту вы можете обратиться к этому методу, чтобы получить расширенную информацию. Например, время фактической отправки/прочтения или перехода по ссылке из письма.
```php
<?php  
$result = $client->getEmail($emailId);
```
Пример ответа:
```php
print_r($result);

stdClass Object
(
    [id] => 8993cc6ffbfd04173d846c0113a97abe
    [time_add] => 1671777031
    [time_sended] => 1671777031
    [time_opened] => 
    [time_clicked] => 
    [time_opened_last] => 
    [time_clicked_last] => 
    [opened] => 
    [cliked] => 
    [is_test] => 
)
```

### Получение списка шаблонов
Вы можете запросить список шаблонов добавленных через личный кабинет, например для отображения в вашей crm системе.
```php
<?php  
$page = 1;
$perPage = 50;
$result = $client->getTransactionsTemplates($page, $perPage);
```
Пример ответа:
```php
print_r($result);

Array
(
    [0] => stdClass Object
        (
            [id] => daf1670c9aa1b629a294b4556ffa600d
            [key] => user/registration
            [name] => user / Приветсвенное письмо
            [mail_subject] => Приветсвуем в сервисе транзакционных рассылок
            [mail_pre_text] => {{userName}} отправлять через нас просто 🤠
        )

)
```

### Получение списка отправителей
Вы можете запросить список отправителей с e-mail которых будут уходить отпарвляемые вами письма. Отправители добавляются/редактируются через личный кабинет.
```php
<?php  
$page = 1;
$perPage = 50;
$result = $client->getSenders($page, $perPage);
```
Пример ответа:
```php
print_r($result);

Array
(
    [0] => stdClass Object
        (
            [id] => 13d7c0f5a82f34ad9a76a163bc80a7f4
            [time_add] => 1669741763
            [email] => kennwort@devrus.space
            [name] => Сервис транзакционных рассылок
        )
)
```
