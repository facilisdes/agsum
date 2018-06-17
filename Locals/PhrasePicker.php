<?php
class PhrasePicker
{
    private static $codes = array(701 => array('ru' =>"Ошибка соединения с БД.", "en" => "DB connection error"),
        801 => array('ru' =>"Неправильная кодировка страницы.", "en" => "Incorrect page encoding"),
        802 => array('ru' =>"Ошибка Curl на ссылке. ", "en" => "Curl error on URL "),
        803 => array('ru' =>"Поиск по Википедии не дал результатов. ", "en" => "Wikipedia search has no result"));
    private $lang;
    public function __construct($language = "ru")
    {
        $this->lang = $language;
    }

    public function GetErrorText($errCode)
    {
        return self::$codes[$errCode][$this->lang];
    }

    public function GetLanguage()
    {
        return $this->lang;
    }
}