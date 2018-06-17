<?php
require_once 'Stemming/PorterStemmerRus.php';
require_once 'Stemming/stop-words.php';
class StemHandler 
{
    public static function StemText($sentences)
    {
        $result = self::StemSentences($sentences);
        return $result;
    }

    private static function StemSentences($sentences)
    {
        $result = array();
        foreach($sentences as $sentence)
        {
            $result[] = self::StemSentence($sentence);
        }
        return $result;
    }

    public static function StemSentence($textToStem)
    {
        $words = self::StemmingPreparations($textToStem);
        $stemmed_sentence = array();
        foreach ($words as $word)
        {
            $word = trim($word);
            $lang = self::GetLangCode($word);
            $stemmed_word = PorterStemmerRus::StemWord($word);
            if($stemmed_word!="" & strlen($stemmed_word)>2 & !self::IsStopWord($stemmed_word))
            {
                $stemmed_sentence[] = $stemmed_word;
            }
        }
        return $stemmed_sentence;
    }

    private static function StemmingPreparations($sentence)
    {
        $result = mb_strtolower($sentence);
        $result = preg_replace("/[^а-яА-ЯёЁa-zA-Z0-9 ]/u", " ", $result);
        $result = preg_replace('/ {2,}/', ' ', $result);
        $result = trim($result);
        $result = explode(' ', $result);
        return $result;
    }

    private static function IsStopWord($word)
    {
        return !(array_search($word, STOP_WORDS_RU)===false);
    }

    private static function GetLangCode($word)
    {
        $result = null;
        if(preg_match('/[А-Яа-яЁё]/u', $word))
            $result = "ru";
        //someday i'll add en here
        return $result;
    }
}