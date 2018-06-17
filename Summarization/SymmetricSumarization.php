<?php
class SymmetricSumarization
{
    public static function Summarize($content, $keywords)
    {
//        $data = self::BuildRefMatrixes($content, $keywords);
//        $sentencesData = self::CountSentenceReferences($data["references"], $data["keywordInclusions"],
//            $data["sentencesPositions"], $data["sentencesDocuments"]);
//        $summary = self::BuildSummary($data["sentences"], $sentencesData["ratings"], $sentencesData["sentencesPositions"], $data["sentencesDocuments"], $data["stems"]);

        $sData = self::BuildRefLists($content, $keywords);
        $refMatrix = self::BuildRefMatrix($sData["sentenceKeywords"], $sData["keywordSentences"]);
        $ratings = self::CountRatings($refMatrix, $sData['sentenceDocs']);
        self::ApplyRules($ratings, $sData['sentencePositions'], $sData['sentenceParagraph']);
        $summary = self::BuildSummary($sData['sentences'], $ratings, $sData['sentencePositions'], $sData['sentenceDocs'], $sData['stems']);
        return $summary;
    }

    private static function BuildRefLists($documents, $keywords)
    {
        $sentences = array();
        $stems = array();
        $sKeywords = array();
        $kSentences = array();
        $sPositions = array();
        $sDocs = array();
        $sPars = array();

        $documentsCount = count($documents);
        for($i=0;$i<$documentsCount; $i++)
        {
            $sentenceCount = 0;
            $document = $documents[$i]['stems'];
            $paragraphsCount = count($document);
            for($j=0;$j<$paragraphsCount;$j++)
            {
                $paragraph = $document[$j];
                $sentencesCount = count($paragraph);
                for($k=0;$k<$sentencesCount;$k++)
                {
                    $sentence = $paragraph[$k];
                    $sentences[] = $documents[$i]['sentences'][$j][$k];
                    $stems[] = $documents[$i]['stems'][$j][$k];
                    $sentenceIndex = key( array_slice( $sentences, -1, 1, TRUE ) );
                    foreach($keywords as $keyword => $tfidf)
                    {
                        $isSubstr = in_array($keyword, $sentence); //есть ли в этом предложении кейворд
                        if($isSubstr) //если есть, то
                        {
                            $sKeywords[$sentenceIndex][] = $keyword; //записываем, какие в предложении есть кейворды
                            $kSentences[$keyword][] = $sentenceIndex; //записываем, в каких предложениях есть кейворд

                            $sDocs[$sentenceIndex] = $i;  //записываем, в каком документе
                            $sPositions[$sentenceIndex] = $sentenceCount; //и какое положение в документе оно занимает
                            $sPars[$sentenceIndex] = $j;
                        }
                    }
                    $sentenceCount++;
                }
            }
        }
        return array("sentences" => $sentences, "stems" => $stems, "sentenceKeywords" => $sKeywords,
            "keywordSentences"  => $kSentences, "sentencePositions" => $sPositions, "sentenceDocs" => $sDocs, "sentenceParagraph" => $sPars);
    }

    private static function BuildRefMatrix($sKeywords, $kSentences)
    {
        $result = array();
        foreach($sKeywords as $sentence => $keywords)
        {
            $encountersSum = 0;
            $result[$sentence] = array();
            foreach($keywords as $keyword)
            {
                foreach($kSentences[$keyword] as $refSentence)
                {
                    if($refSentence==$sentence) continue;
                    if(isset($result[$sentence][$refSentence]))
                    {
                        $result[$sentence][$refSentence]++;
                    }
                    else
                    {
                        $result[$sentence][$refSentence] = 1;
                    }
                    $encountersSum++;
                }
            }
            $result[$sentence]['summary'] = $encountersSum;
        }
        return $result;
    }

    private static function CountRatings($refMatrix, $sDocs)
    {
        $ratings = array();
        foreach($refMatrix as $sentence => $hits)
        {
            $sum = $hits['summary'];
            foreach($hits as $referral => $hitCount)
            {
                $newScore = 0;
                if($referral=='summary') continue;
                $addScore = $refMatrix[$referral]['summary'];
                for($i = $hitCount; $i>0;$i--)
                {
                    $addScore*=SYMSUM_SHARED_RATING_PERCENTAGE;
                    $newScore+=$addScore;
                }

                if($sDocs[$sentence]!=$sDocs[$referral]) $newScore*=SYMSUM_REFERENCE_TO_OTHER_DOC_STRENGTH;
                $sum+=$newScore;
            }
            $ratings[$sentence] = sqrt($sum);
        }
        return $ratings;
    }

    //строим матрицы ссылок
    private static function BuildRefMatrixes($documents, $keywords)
    {
        $sentences = array(); //матрица предложений - нужна для хранения предложений в плоском массиве
        $stems = array(); //аналогично, для стеммированных предложений

        $references = array(); //матрица ссылок - если в предложении есть кейворд, то в этом массиве на месте
        // предложения есть элемент с ключом в виде кейворда
        $keywordInclusions = array(); //матрица ссылок для слов - хранит количество вхождений слова в предложения
        $sentencesPositions = array();//положения предложений в своих текстах
        $sentencesDocuments = array();//какому документу принадлежат предложения

        $documentsCount = count($documents);
        for($i=0;$i<$documentsCount; $i++)
        {
            $sentenceCount = 0;
            $document = $documents[$i]['stems'];
            $paragraphsCount = count($document);
            for($j=0;$j<$paragraphsCount;$j++)
            {
                $paragraph = $document[$j];
                $sentencesCount = count($paragraph);
                for($k=0;$k<$sentencesCount;$k++)
                {
                    $sentence = $paragraph[$k];
                    $sentences[] = $documents[$i]['sentences'][$j][$k];
                    $stems[] = $documents[$i]['stems'][$j][$k];
                    $sentenceIndex = count($sentences) - 1;
                    foreach($keywords as $keyword => $tfidf)
                    {
                        $isSubstr = in_array($keyword, $sentence); //есть ли в этом предложении кейворд
                        if($isSubstr) //если есть, то
                        {
                            $references[$sentenceIndex][$keyword] = 1; //создаем элемент в матрице ссылок
                            $sentencesDocuments[$sentenceIndex] = $i;  //записываем, в каком документе
                            $sentencesPositions[$sentenceIndex] = $sentenceCount; //и какое положение в абзаце оно занимает
                            if(isset($keywordInclusions[$keyword][$i])) //и, если в матрице ссылок для слов есть текущий кейворд,
                            {
                                $keywordInclusions[$keyword][$i]++; //увеличиваем хранящаеся там число на единицу
                            }
                            else
                            {
                                $keywordInclusions[$keyword][$i] = 1; //а иначе создаем элемент под этот кейворд
                            }
                        }
                    }
                    $sentenceCount++;
                }
            }
        }
        $result = array("sentences" => $sentences, "stems" => $stems, "references" => $references,
            "keywordInclusions" => $keywordInclusions, "sentencesDocuments" => $sentencesDocuments,
            "sentencesPositions" => $sentencesPositions);
        return $result;
    }

    //считаем количество ссылок для каждого предложения
    private static function CountSentenceReferences($ref_matrix, $words_ref_matrix, $sentences_positions, $sentences_documents)
    {
        $ratings = array();  //массив вида элемент = номер предложения => рейтинг
        foreach($ref_matrix as $id => $sentence) //перебираем все предложения
        {
            $currentSentenceDocument = $sentences_documents[$id];
            $referencesCount = 0;
            foreach($sentence as $keyword => $inclusionFlag) //и все кейворды, которые есть в нем
            {
                foreach($words_ref_matrix[$keyword] as $docIndex => $entries)
                {
                    if($docIndex==$currentSentenceDocument)
                    {
                        $referencesCount += $entries - 1; // берем количество вхождений кейворда во все предложения
                        //того текста, в котором находится текущее предложение,
                        //и вычитаем единицу потому что одно из вхождений это текущее предложение
                    }
                    else
                    {
                        $referencesCount += $entries * SYMSUM_REFERENCE_TO_OTHER_DOC_STRENGTH; //иначе плюсуем к
                        //рейтингу количество связей
                    }
                    //тут можно домножать на вес кейворда, но это может повлечь за собой увеличение рейтинга предложений
                    //с мусорными словами если такие слова попадут в кейворды
                }

            }
            $ratings[$id] = $referencesCount;
            self::ApplyRules($ratings, $id, $sentences_positions[$id]);
        }
        return array("ratings"=>$ratings, "sentencesPositions"=>$sentences_positions);
    }

    private static function ApplyRules(&$ratings, $positionsInText, $paragraph)
    {
        foreach($ratings as $id => $rating) {
            $positionInText = $positionsInText[$id];
            if (isset($ratings[$id - 1])) //если есть сосед слева, то
            {
                if($paragraph[$id]==$paragraph[$id - 1])
                {
                    $leftNeighbourScore = $ratings[$id - 1]; //сохраняем его результат в памяти
                    $ratings[$id - 1] += $ratings[$id] * SYMSUM_NEIGHBOURS_BONUS_MULTIPLIER; //прибавляем к его результату
                    //часть от своего
                    $ratings[$id] += $leftNeighbourScore * SYMSUM_NEIGHBOURS_BONUS_MULTIPLIER; //и прибавляем к своему
                    //часть от его
                    if($paragraph[$id]!=$paragraph[$id + 1]) //текущее - последнее в своем абзаце
                    {
                        $ratings[$id] *= SYMREF_LAST_IN_PARAGRAPH_MULTIPLIER; //умножаем его
                    }
                }
                elseif($paragraph[$id]==$paragraph[$id + 1])//если текущее предложение - первое в своем параграфе
                {
                    $ratings[$id] *= SYMREF_FIRST_IN_PARAGRAPH_MULTIPLIER; //умножаем его
                }
            }
            if ($positionInText == 0) //если текущее предложение - первое в своем тексте
            {
                $ratings[$id] *= SYMREF_FIRST_SENTENCE_IN_TEXT_MULTIPLIER; //умножаем его
            }
        }
    }
    
    private static function GetTopSentences($sentencesRatings, $stemmedSentences, $sentencesPositions)
    {
        arsort($sentencesRatings);
        $count = 0;
        $summarySentencesStemmed = array();
        $summarySentencesPositions = array();
        foreach($sentencesRatings as $id => $rating)
        {
            if(!self::isTooClose($stemmedSentences[$id], $summarySentencesStemmed))
            {
                $summarySentencesStemmed[] = $stemmedSentences[$id];
                $summarySentencesPositions[$id] = $sentencesPositions[$id];
                $count++;
                if($count == SYMSUM_SUMMARY_LENGTH) break;
            }
        }
        return $summarySentencesPositions;
    }

    private static function BuildSummary($sentences, $sentencesRatings, $sentencesPositions, $sentencesDocuments, $stemmedSentences)
    {
        $summarySentencesPositions = self::GetTopSentences($sentencesRatings, $stemmedSentences, $sentencesPositions);
        $sumGroupsByDoc = array();

        foreach($summarySentencesPositions as $id=>$position)
        {
            $doc = $sentencesDocuments[$id];
            if(isset($sumGroupsByDoc[$doc]))
            {
                $sumGroupsByDoc[$doc][$position] = $id;
            }
            else
            {
                $sumGroupsByDoc[$doc] = array($position => $id);
            }
        }

        ksort($sumGroupsByDoc);

        $summary = array();
        $preSummary=array();
        $sumPositions = array();
        $documentsImpact = array();
        foreach($sumGroupsByDoc as $documentIndex => $sentencesArray)
        {
            if(isset($documentsImpact[$documentIndex])) $documentsImpact[$documentIndex]+=count($sentencesArray);
            else $documentsImpact[$documentIndex] = count($sentencesArray);

            $paragraph = array();
            $firstSentencePosition = -99;
            $prevSentencePosition = -2;
            asort($sentencesArray);
            foreach($sentencesArray as $position => $index)
            {
                if($prevSentencePosition+1==$position)
                {
                    $paragraph[] = $sentences[$index];
                }
                else
                {
                    if(count($paragraph)>0)
                    {
                        $preSummary[] = $paragraph;
                        $sumPositions[$firstSentencePosition][] = key( array_slice( $preSummary, -1, 1, TRUE ) );
                    }
                    $firstSentencePosition = $position;
                    $paragraph = array($sentences[$index]);
                }
                $prevSentencePosition = $position;

            }
            $preSummary[] = $paragraph;
            $sumPositions[$firstSentencePosition][] = key( array_slice( $preSummary, -1, 1, TRUE ) );
        }

        ksort($sumPositions);

        foreach($sumPositions as $position => $links)
        {
            foreach($links as $link)
            {
                $summary[] = $preSummary[$link];
            }
        }

        arsort($documentsImpact);

        return array("summary" => $summary, "documentsImpact"=> $documentsImpact);
    }

    private static function isTooClose($sentence, $sentences)
    {
        if(count($sentences)==0) return false;
        $result = false;
        foreach($sentences as $sentenceToCompare)
        {
            $simularWordsCount = 0;
            $overallWordCount = min(count($sentenceToCompare), count($sentence));
            foreach($sentence as $word)
            {
                if(in_array($word, $sentenceToCompare)) $simularWordsCount++;
            }
            if($simularWordsCount > $overallWordCount * SYMSUM_SIMULAR_WORDS_PERCENTAGE_TO_DENY_SENTENCE)
                return true;
        }
        return $result;
    }

}