<?php

require_once 'SentenceParser.php';
require_once 'StemHandler.php';
require_once 'SymmetricSumarization.php';
include_once 'WordCounter.php';
class SummarizationHandler
{
    public static function Summarize($query, $search_data, &$times = array())
    {
        $content = array();
        $urls = array();
        $time_stem = 0;
        $time_count = 0;
        $wordCounter = new WordCounter($query);
        foreach($search_data as $search_result)
        {
            $urls[] = $search_result['url'];
            $a = microtime(true);/*timer*/
            $site_content = self::HandleSearchResult($search_result);
            $b = microtime(true);/*timer*/
            $time = $b-$a;
            $time_stem+=$time;
            $c = microtime(true);/*timer*/
            $wordCounter->IndexWords($site_content['stems']);
            $d = microtime(true);/*timer*/
            $time_count+=$d-$c;
            $content[] = $site_content;
        }
        $e = microtime(true);/*timer*/
        $keywords = $wordCounter->GetKeywords();
        unset($wordCounter);
        $f = microtime(true);/*timer*/
        $summaryData = SymmetricSumarization::Summarize($content, $keywords);
        $j = microtime(true);/*timer*/

        $times["stemming_and_sentences_parsing"] = $time_stem;
        $times["word_count"] = $time_count;
        $times["keywords_extraction"] = $f - $e;
        $times["summary_building"] = $j - $f;
        $urlsByRelevance = self::BuildUrlsArray($summaryData['documentsImpact'], $urls);
        //self::SumPrint($content, $urls, $keywords, $summary);
        return array("summary" => $summaryData['summary'], "keywords" => $keywords, "urls" => $urlsByRelevance);
    }

    private static function BuildUrlsArray($documentsImpacts, $urls)
    {
        $result = array();
        foreach($documentsImpacts as $documentIndex => $hits)
        {
            $result[] = $urls[$documentIndex];
        }
        return $result;
    }

    private static function HandleSearchResult($search_result)
    {
        $old_content = $search_result['content'];
        $new_content = array();
        $stemmed_content = array();
        foreach($old_content as $old_paragraph)
        {
            $new_paragraph = SentenceParser::ExplodeSentences($old_paragraph);
            $stemmed_paragraph = StemHandler::StemText($new_paragraph);
            array_push($new_content, $new_paragraph);
            array_push($stemmed_content, $stemmed_paragraph);
        }
        return array('paragraph' => $old_content, "sentences" => $new_content, "stems" => $stemmed_content);
        
    }

    private static function SumPrint($content, $urls, $keywords, $summary)
    {
        echo "<html>";
        echo ' <style type="text/css">
   TABLE {
    border-collapse: collapse; /* Убираем двойные линии между ячейками */
   }
   TD, TH {
    padding: 3px; /* Поля вокруг содержимого таблицы */
    border: 1px solid black; /* Параметры рамки */
   }
   TH {
    background: #b0e0e6; /* Цвет фона */
   }
  </style>';
        echo "<body>";

        echo "Ключевые слова:";
        foreach($keywords as $keyword => $rate)
        {
            echo "$keyword, ";
        }
        echo "<br>";

        echo "<h2>$summary</h2><br><br><br>";

        echo "<table>";
        for($kk=0; $kk<count($content); $kk++)
        {
            $page_content = $content[$kk];
            $url = $urls[$kk];
            $old_content = $page_content['paragraph'];
            $new_content = $page_content["sentences"];
            $stemmed_content = $page_content["stems"];
            echo "<h1>$url</h1><br>";
            echo "<tr><td>old</td><td>new</td><td>stemmed</td></tr>";
            for($i=0;$i<count($old_content); $i++)
            {
                $old = $old_content[$i];
                $new = "";
                $stemmed = "";

                for($j=0;$j<count($new_content[$i]); $j++)
                {
                    $new_paragraphes = $new_content[$i][$j];
                    $stemmed_paragraphes = $stemmed_content[$i][$j];
                    $new.=$new_paragraphes . "¶";
                    foreach($stemmed_paragraphes as $stemmed_words)
                    {
                        $stemmed.=$stemmed_words . ' ';
                    }
                    $stemmed.="¶";
                }
                echo "<tr><td>$old</td><td>$new</td><td>$stemmed</td></tr>";
            }
            echo "</table><hr><table>";
            }
        echo "</table></body></html>";
    }
}
