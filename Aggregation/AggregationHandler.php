<?php
    require_once 'XmlParser.php';
    require_once 'YandexConnector.php';
    require_once 'LinkParser.php';
    require_once 'WikipediaSearcher.php';

    class AggregationHandler
    {
        public static function Aggregate($query, &$times = array())
        {
            $result = array();
            try
            {
                $wikiData = WikipediaSearcher::SearchForQuery($query);
                $result[] = array('url' => $wikiData['url'], 'content' => $wikiData['content']);
            }
            catch (Exception $e)
            {
                Logger::LogError($e->getMessage());
            }

            $a2 = microtime(true); /*timer*/

            $xml = YandexConnector::GetXml($query);

            $a3 = microtime(true); /*timer*/


            $xmlData = XmlParser::ParseXml($xml);
            $urls = $xmlData[0];

            $a4 = microtime(true); /*timer*/

            $right_urls = 0;
            foreach($urls as $url)
            {
                $status = false;
                $resultElement = array();
                try {

                    $text = LinkParser::ParseUrlContent($url, $status);
                }
                catch (Exception $e)
                {
                    Logger::LogError($e->getMessage());
                    continue;
                }
                if(!$status) continue;

                $resultElement['url'] = (string)$url;
                $resultElement['content'] = $text;
                if(count($resultElement)>1) {
                    array_push($result, $resultElement);
                }

                $right_urls++;
                if($right_urls==AGGREGATION_MAX_SITES_COUNT) break;
            }
            $a5 = microtime(true);/*timer*/


            $r2 = $a3-$a2;
            $times["yandex_connector"] = $r2;
            $r3 = $a4-$a3;
            $times["yandex_response_parsing"] = $r3;
            $r4 = $a5-$a4;  
            $times["links_ерparsing"] = $r4;

            /*
            echo "Работа со строкой запроса: $r1<br>";
            echo "Обращение к яндексу: $r2<br>";
            echo "Разбор ответа от яндекса: $r3<br>";
            echo "Разбор всех ссылок: $r4<br>";*/
            return array($result, $xmlData[1]);
                   
        }
    }