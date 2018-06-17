<?php
require_once 'Stemming/dictionary.php';
class SentenceParser
{
    public static function ExplodeSentences($text)
    {
        $abbreviations = self::HideAbbreviations($text);
        $replacementsCount = self::GlueBrackets($text);
        self::HideUrls($text);
        $sentences = self::SplitText($text);
        $isPreviousIsPartOfName = false;
        $senCount = count($sentences);
        for($i=0;$i<$senCount;$i++)
        {
            $sentence = &$sentences[$i];
//            self::ShowAbbreviations($sentence, $abbreviations);
//            self::UnglueBrackets($sentence, $replacementsCount);
            self::ShowDelimeters($sentence);
            $sentence = trim($sentence);
            if(self::IsPartOfName($sentence))
            {
                $isPreviousIsPartOfName = true;
                self::SpliceSentences($sentences, $i);
                $senCount = count($sentences);
            }
            elseif($isPreviousIsPartOfName)
            {
                self::SpliceSentences($sentences, $i);
                $senCount = count($sentences);
                $isPreviousIsPartOfName = false;
            }
        }
        return $sentences;
    }

    private static function SpliceSentences(&$sentences, &$index)
    {
        if($index>1) {
            $sentences[$index - 1] .= $sentences[$index];
            unset($sentences[$index]);
            $sentences = array_values($sentences);
            $index--;
        }
    }

    private static function IsPartOfName($text)
    {
        $result = false;
        if(strlen($text)<4 & mb_strtoupper($text)==$text)
        {
            $result = preg_match("/[а-яА-ЯёЁa-zA-Z]/u", $text);
        }
        return $result;
    }

    private static function SplitText($text)
    {
        $pattern = "/(?<=[";
        foreach(SENTENCE_SPLITTERS as $splitter)
        {
            $pattern.= preg_quote($splitter);
        }
        $pattern.= "])/u";
        $sentences = preg_split($pattern, $text);
        unset($sentences[count($sentences)-1]);
        return $sentences;
    }

    private static function HideUrls(&$text)
    {
        $pattern = "/(?<=[a-zA-Zа-яА-Я0-9])\.(?=[a-zA-Zа-яА-Я0-9])/u";
        $replacement = "⡻";
        $text = preg_replace($pattern, $replacement, $text, -1, $count);
        return $count;
    }


    private static function ShowDelimeters(&$text)
    {
        foreach (SENTENCE_SPLITTERS as $replacement => $splitter) {
            $pattern = "/$replacement/u";
            $patterns[] = $pattern;
            $replacements[] = $splitter;
        }
        $text = preg_replace($patterns, $replacements, $text, -1, $count);
    }

    private static function HideAbbreviations(&$text)
    {
        $appliedAbbreviations = array();
        foreach(ABBREVIATIONS_RU as $abbreviation)
        {
            $splitter = '.';
            $replacement = '⡻';
            $old = " " . $abbreviation . preg_quote($splitter);
            $base = " " . $abbreviation . $splitter;
            $new = " $abbreviation$replacement";
            $pattern = "/($old)/u";
            $replace = "$new";
            $text = preg_replace($pattern, $replace, $text, -1, $count);
            if($count>0)
            {
                if(isset($appliedAbbreviations[$base]))
                {
                    $appliedAbbreviations[$base]["count"]+=$count;
                }
                else
                {
                    $appliedAbbreviations[$base] = array("replace" =>$new, "count" => $count);
                }

            }
        }
        return $appliedAbbreviations;
    }


    private static function GlueBrackets(&$text)
    {
        $replacementsCount = 0;
        foreach(BRACKETS as $bracketsType)
        {
            if(is_array($bracketsType))
            {
                $bOpen = preg_quote($bracketsType[0]);
                $bClose = preg_quote($bracketsType[1]);
            }
            else
            {
                $bOpen = preg_quote($bracketsType);
                $bClose = preg_quote($bracketsType);
            }
            foreach(SENTENCE_SPLITTERS as $replacement=>$splitter)
            {
                $pattern = '/('.$bOpen.'.[^'.$bClose.']*?)('.preg_quote($splitter).')(?=.*?'.$bClose.')/u';
                $text = preg_replace($pattern, "$1" . $replacement, $text, -1, $count);
                if($count>0)
                {
                    $replacementsCount+=$count;
                }
            }
        }
        return $replacementsCount;
    }

    public static function UnglueBrackets(&$text, &$replacementsCount)
    {
        if($replacementsCount>0)
        {
            $patterns = array();
            $replacements = array();
            foreach (SENTENCE_SPLITTERS as $replacement => $splitter) {
                $pattern = "/$replacement/u";
                $patterns[] = $pattern;
                $replacements[] = $splitter;
            }
            $text = preg_replace($patterns, $replacements, $text, -1, $count);
            $replacementsCount-=$count;
        }
    }

    public static function ExplodeSentences2($text)
    {
        //$paragraph = self::NormalizeParagraph($text);
        $splits_t1 = explode('. ', $text); //все разбиения по точке
        $splits_t2 = array();   //только те разбиения, в которых после точки в предыдущем элементе идет слово с большой буквы в следующем
        $sentence = $splits_t1[0];
        for($i = 1; $i<count($splits_t1); $i++)
        {
            $current_sentence = $splits_t1[$i];
            if(mb_strtolower($current_sentence) == $current_sentence)
            {
                $sentence.= '.  ' . $current_sentence;
            }
            else
            {
                //if($i==1)
                {
                    $words = explode(' ', $splits_t1[0]);
                    $last_word = $words[count($words) - 1];
                    $last_word = preg_replace("/[^а-яА-ЯёЁa-zA-Z]/u", "", $last_word);
                    $in_array = in_array($last_word, ABBREVIATIONS_RU);
                    if($in_array)
                    {
                        $sentence.= '.  ' . $current_sentence;
                        continue;
                    }
                }
                array_push($splits_t2, $sentence . '. ');
                $sentence = $current_sentence;
            }
        }
        array_push($splits_t2, $sentence);

        $splits = array();//разбиения не только по точке, но и по другим знакам 
        foreach($splits_t2 as $sentence)
        {
            $sentence_splitted = preg_split('/(?<=[\?!…])/u', $sentence, 0, PREG_SPLIT_NO_EMPTY);
            $sentences = array();
            foreach($sentence_splitted as $parsed_sentence)
            {
                $sentences[] = self::NormalizeSentence($parsed_sentence);
            }
            $splits = array_merge($splits, $sentences);
        }
        return $splits;
    }

    private static function NormalizeSentence($sentence)
    {
        $sentence = trim($sentence);
        return $sentence;
    }
}
//
//$qwe = "С XIV в. начинается постепенное возвышение Кракова. Владислав I Локоток делает этот город своей резиденцией (вместо Гнезно) и в 1319 г. коронуется здесь. Казимир Великий украшает город новыми сооружениями и покровительствует развитию промыслов и торговли. 14 февраля 1386 г. в Кракове состоялось крещение Ягайло и бракосочетание его с Ядвигой. В эпоху Ягеллонов первенствующее значение Кракова окончательно упрочивается; город богатеет, число жителей его возрастает до 100 тыс. С 1610 г. резиденция королей переносится в Варшаву, но польские короли продолжают короноваться в Кракове. Частые нападения неприятелей постепенно подтачивали благосостояние города; в 1787 г. Краков насчитывал 9,5 тысяч жителей.";
//
//$text = SentenceParser::ExplodeSentences($qwe);
//echo $qwe . "<br>";
//foreach ($text as $str)
//{
//    echo $str . "|";
//}
////
