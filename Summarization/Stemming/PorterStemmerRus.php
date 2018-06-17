<?php
    class PorterStemmerRus
    {
        private static $vowels = "аеиоуыэюя";
        private static $PG1 = "(в|вши|вшись)";
        private static $PG2 = "(ив|ивши|ившись|ыв|ывши|ывшись)";
        private static $adjective = "(ее|ие|ые|ое|ими|ыми|ей|ий|ый|ой|ем|им|ым|ом|его|ого|ему|ому|их|ых|ую|юю|ая|яя|ою|ею)";
        private static $participle1 = "(ем|нн|вш|ющ|щ)";
        private static $participle2 = "(ивш|ывш|ующ)";
        private static $reflexives = "(ся|сь)";
        private static $verb1 = "(ла|на|ете|йте|ли|й|л|ем|н|ло|но|ет|ют|ны|ть|ешь|нно)";
        private static $verb2 = "(ила|ыла|ена|ейте|уйте|ите|или|ыли|ей|уй|ил|ыл|им|ым|ен|ило|ыло|ено|ят|ует|уют|ит|ыт|ены|ить|ыть|ишь|ую|ю)";
        private static $noun = "(а|ев|ов|ие|ье|е|иями|ями|ами|еи|ии|и|ией|ей|ой|ий|й|иям|ям|ием|ем|ам|ом|о|у|ах|иях|ях|ы|ь|ию|ью|ю|ия|ья|я)";
        private static $superlative = "(ейш|ейше)";
        private static $derivational = "(ост|ость)";

        public static function StemWord($word)
        {
            $stemmedWord = str_replace('ё', 'е', $word);
            $parts = self::FindRegions($stemmedWord);
            $RV = $parts["RV"];
            $RVPrefix = $parts["RVP"];
            $R2 = $parts["R2"];
            self::DoStep1($RV);
            self::DoStep2($RV);
            self::DoStep3($RV, $R2);
            self::DoStep4($RV);
            $stemmedWord = $RVPrefix . $RV;
            return $stemmedWord;
        }

        private static function FindRegions($word)
        {
            $matches = array();
            $result = array();
            $vowels = "/[" . self::$vowels . "](.+)/u";
            preg_match_all($vowels, $word, $matches);
            if(count($matches[1])<1)
            {
                $result["RV"] = "";
                $result["RVP"] = $word;
                $result["R2"] = "";
                return $result;
            }       
            $RV = $matches[1][0];
            $RVPrefix = preg_replace("/$RV$/u", "", $word);
            $R1 = self::FindRegion($RV);
            $R2 = self::FindRegion($R1);

            $result["RV"] = $RV;            
            $result["RVP"] = $RVPrefix;
            $result["R2"] = $R2;
            return $result;
        }

        private static function FindRegion($base)
        {
            $isPrevVowel = false;
            for($i=0; $i<strlen($base) - 2;$i++)
            {
                $letter = $base[$i];
                if(mb_strpos(self::$vowels, $letter)===false)
                {
                    $reg = mb_substr($base, $i+1);
                    if($isPrevVowel) return $reg;
                    $isPrevVowel = false;
                }
                else $isPrevVowel = true;
            }
            return "";
        }

        private static function DoStep1(&$RV)
        {

            if(preg_match("/[а|я]" . self::$PG1 . "$/u", $RV))
            {
                $RV = preg_replace("/" . self::$PG1 . "$/u", "", $RV, -1, $count);
                return;
            }
            $RV = preg_replace("/" . self::$PG2 . "$/u", "", $RV, -1, $count);
            if($count>0) return;

            $RV = preg_replace("/" . self::$reflexives . "$/u", "", $RV);

            if(preg_match("/[а|я]" . self::$participle1 . self::$adjective . "$/u", $RV))
            {
                $RV = preg_replace("/" . self::$participle1 . self::$adjective . "$/u", "", $RV, -1, $count);
                if($count>0) return;
            }
            $RV = preg_replace("/" . self::$participle2 . self::$adjective . "$/u", "", $RV, -1, $count);
            if($count>0) return;
            $RV = preg_replace("/" . self::$adjective . "$/u", "", $RV, -1, $count);
            if($count>0) return;

            if(preg_match("/[а|я]" . self::$verb1 . "$/u", $RV))
            {
                $RV = preg_replace("/" . self::$verb1 . "$/u", "", $RV, -1, $count);
                if($count>0) return;
            }

            $RV = preg_replace("/" . self::$verb2 . "$/u", "", $RV, -1, $count);
            if($count>0) return;

            $RV = preg_replace("/" . self::$noun . "$/u", "", $RV, -1, $count);
        }

        private static function DoStep2(&$RV)
        {
            self::CutWord($RV, "и");
        }

        private static function DoStep3(&$RV, $R2)
        {
            if(preg_match("/" . self::$derivational . "/u", $R2))
            {
                $RV = preg_replace("/" . self::$derivational . "$/u", "", $RV, -1, $count);
            }
        }

        private static function DoStep4(&$word)
        {
            if(self::UndoubleN($word)) return;
            if(self::CutWord($word, self::$superlative))
            {
                self::UndoubleN($word);
                return;
            }
            self::CutWord($word, "ь");
        }

        private static function UndoubleN(&$word)
        {
            if(self::CutWord($word, "нн"))
            {
                $word.="н";
                return true;
            }
            return false;
        }

        private static function CutWord(&$word, $ending)
        {
            $word = preg_replace("/$ending$/u", "", $word, -1, $count);
            $result = $count > 0;
            return $result;
        }
    }

//    $words = array("а", "зде", "котор", "с", "эт", "год", "это", "году", "года", "был", "была", "было", "можно", "который", "которая", "котором", "которых", "которое", "как", "это",
//"так", "что", "еще", "уже", "очень", "бы", "в", "во", "вот", "для", "до", "если", "же", "за", "и", "из", "или", "к", "ко", "на", "но", "о", "об", "от", "по", "при", "с", "то", "у", "чтобы",
//    "да", "нет", "не");
//    $stems = array();
//    //$stems = array("бездарн", "бездыхан", "беспомощн", "будущн");
//    for($i=0;$i<count($words); $i++)
//    {
//        $word = $words[$i];
////        $stem_etalon = $stems[$i];
//        $stem = PorterStemmerRus::StemWord($word);
//
////        if($stem_etalon!=$stem & $stem_etalon . 'ост'!=$stem)
////        {
//        if(array_search($stem, $stems)===false)
//        {
//            echo "\"$stem\", ";
//        }
//        $stems[] = $stem;
////        }
//    }
////    echo $result;

