<?php

namespace Devrusspace\Kennwort;

use Exception;

class ApiClient
{
    private $_apiUrl = 'https://api.kennwort.ru/v1/';
    private $_token;

    private $_client;

    public function __construct($token)
    {
        $this->_token = $token;

        if (empty($this->_token)) {
            throw new Exception('Could not connect to api, check your TOKEN');
        }
    }

    /**
     * Отправка письма на основе шаблона, созданного в личном кабинете
     *
     * @param string $template Ключ шаблона письма
     * @param array $user ["email" => "Имя получателя"]
     * @param array $params Переменные используемые в заголовке и контексте письма
     * @return object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendEmail($template, $user, $params = [])
    {
        $form_params = [];
        $form_params['template'] = (string)$template;
        $form_params = array_merge($form_params, $this->appendUserData($user));
        $form_params['params'] = json_encode($params);

        $response = $this->getClient()->post($this->getUrl('emails/send'), [
            'form_params' => $form_params
        ]);

        return $this->result($response);
    }

    /**
     * Отправка письма, сгенерированного на стороне вашего приложения
     *
     * @param string $senderId Идентификатор отправителя из личного кабинета пользовтаеля
     * @param array $user ["email" => "Имя получателя"]
     * @param string $subject Заголовок письма
     * @param string $body Контент письма, в формате html
     * @return object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendEmailBody($senderId, $user, $subject, $body)
    {
        $form_params = [];
        $form_params = array_merge($form_params, $this->appendUserData($user));
        $form_params['senderId'] = (string)$senderId;
        $form_params['subject'] = (string)$subject;
        $form_params['body'] = (string)$body;

        $response = $this->getClient()->post($this->getUrl('emails/send-body'), [
            'form_params' => $form_params
        ]);

        return $this->result($response);
    }

    /**
     * Получение информации об отправленном сообщении по его id
     *
     * @param $id
     * @return object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getEmail($id)
    {
        $response = $this->getClient()->get($this->getUrl('emails/{id}', ['{id}' => $id]));
        return $this->result($response);
    }

    /**
     * Получение списка отправителей из личного кабинета
     *
     * @param int $page Номер запрашиваемой страницы
     * @param int $perPage Количество элементов на странице
     * @return object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSenders($page = 1, $perPage = 50)
    {
        $response = $this->getClient()->get($this->getUrl('senders'), [
            'query' => ['page' => $page, 'per-page' => $perPage]
        ]);
        return $this->result($response);
    }

    /**
     * Получение списка шаблонов
     *
     * @param int $page Номер запрашиваемой страницы
     * @param int $perPage Количество элементов на странице
     * @return object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTransactionsTemplates($page = 1, $perPage = 50)
    {
        $response = $this->getClient()->get($this->getUrl('transaction-templates'), [
            'query' => ['page' => $page, 'per-page' => $perPage]
        ]);
        return $this->result($response);
    }

    private function getUrl($method, $params = [])
    {
        $url = $this->_apiUrl.$method;
        foreach ($params as $key=>$val)
        {
            $url = str_replace($key, $val, $url);
        }
        return $url;
    }

    private function appendUserData($user)
    {
        $form_params = [];
        if (is_string($user))
            $form_params['userEmail'] = $user;
        elseif(is_array($user)) {
            foreach($user as $email => $name) {
                $form_params['userEmail'] = $email;
                $form_params['userName'] = $name;
                break;
            }
        }
        return $form_params;
    }

    private function getClient()
    {
        if (!$this->_client) {
            $this->_client = new \GuzzleHttp\Client(['headers' => $this->getHeaders(), 'verify' => false]);
        }
        return $this->_client;
    }

    /**
     * Авторизация
     *
     * @return string[]
     */
    private function getHeaders()
    {
        return ['Authorization' => "Bearer {$this->_token}"];
    }

    /**
     * Подготовка ответа
     *
     * @param $response
     * @return object
     */
    private function result($response)
    {
        $content = $response->getBody()->getContents();
        return json_decode($content);
    }
}
