<?php
class WordCounter
{
    private $TFIDFs = array();
    private $words = array();   //слова и их вхождения в весь корпус
    private $wordsByDocuments = array(); //слова и их вхождения в документы в виде слово => array(вх1, вх2, ...)
    private $documentsCount = 0; //количество обработанных документов
    private $wordsCountOverall = 0; //суммарное количество слов (не уникальных!)
    private $wordsCountByDocuments = array(); //суммарное количество слов по документам
    private $isRecountRequired = false;
    private $searchQuery = array();

    public function __construct($query)
    {
        $this->searchQuery = StemHandler::StemSentence($query);
    }

    public function IndexWords($stems) //индексировать слова, то есть внести в словарь все слова из текста
    {
        $this->CountWords($stems);
        $this->CountWordsOnDocument($stems);
        $this->documentsCount++;
        $this->isRecountRequired = true;
    }

    private function RecountTFIDF() //пересчет TFIDF
    {
        $this->TFIDFs = array();
        $wordsByDocuments = $this->wordsByDocuments;
        foreach($this->words as $word => $countInCorpus) //перебираем все слова в корпусе
        {
            if(preg_match('/[а-яА-ЯёЁa-zA-Z]/', $word)!=1)
            {
                $this->TFIDFs[$word] = 0;
                continue;
            }
            $wordTFIDF = 0;
            $inclusionDocumentsCount = 0;
            foreach($this->wordsCountByDocuments as $documentIndex => $documentWordsCount) //и перебираем все документы
            {
                $hitsCount = count($wordsByDocuments[$word]);

                $wordCountInDocument = isset($wordsByDocuments[$word][$documentIndex])? $wordsByDocuments[$word][$documentIndex] : 0; //берем вхождения слова в текущем документе
                if($wordCountInDocument!=0) //и если их больше нуля, то
                {
                    $inclusionDocumentsCount++;
                    $tf = $wordCountInDocument / $documentWordsCount; //считаем tf по формуле кол-во вхождений / кол-во слов в документе
                    $idf = (sqrt($this->wordsCountOverall / ($hitsCount * $documentWordsCount))); //считаем idf по формуле кореньиз( количество слов в корпусе / произведение вхождений слова в корпус и количества слов в документе )

                    $tfidf = $tf * $idf;
                    $this->ApplyDocumentRules($tfidf, $documentIndex);//применяем правила
                    $wordTFIDF+=$tfidf; //суммируем tfidf с данными по другим документам
                }
            }
            $this->ApplySuperRules($word, $wordTFIDF, $inclusionDocumentsCount); //применяем правила
            $this->TFIDFs[$word] = $wordTFIDF; //и фиксируем получившуюся величину
        }
        $this->isRecountRequired = false;
    }

    public function Reset($query = "")
    {
        $this->TFIDFs = array();
        $this->words = array();
        $this->wordsByDocuments = array();
        $this->documentsCount = 0;
        $this->wordsCountOverall = 0;
        $this->wordsCountByDocuments = array();
        $this->isRecountRequired = false;
        if($query!="")
            StemHandler::StemSentence($query);
        else
            $this->searchQuery = array();
    }

    public function GetKeywords()
    {
        if($this->isRecountRequired)
        {
            $this->RecountTFIDF();
            arsort($this->TFIDFs);
        }
        $result = array_slice($this->TFIDFs, 0, TFIDF_KEYWORDS_COUNT);
        return $result;
    }

    private function ApplySuperRules($word, &$TFIDF, $inclusionDocumentsCount)
    {
        //1. increase every query word's rate
        if(in_array($word, $this->searchQuery))
        {
            $multiplier = TFIDF_QUERY_WORD_MULTIPLIER;
            $TFIDF*=$multiplier;
        }
        if($inclusionDocumentsCount==1)//decrease every too unique keyword's rate
        {
            $TFIDF*=TFIDF_KEYWORD_IN_SINGLE_DOCUMENT_MULTIPLIER;
        }

    }

    private function ApplyDocumentRules(&$TF, $docIndex)
    {
        //2. apply TFIDF_SITE_RELEVANCE_STEP to every tf
        $stepMultiplier = $this->documentsCount - $docIndex;
        $TfMultiplier = TFIDF_SITE_RELEVANCE_BASE + $stepMultiplier * TFIDF_SITE_RELEVANCE_STEP;
        $result = $TF*$TfMultiplier;
        return $result;
    }

    private function CountWordsOnDocument($document) //подсчет вхождений слова в документ - всего и для каждого из корпуса
    {
        $docIndex = $this->documentsCount;
        $this->wordsCountByDocuments[$docIndex] = 0;
        foreach($document as $paragraph)
        {
            foreach($paragraph as $sentence)
            {
                $this->wordsCountByDocuments[$docIndex] += count($sentence);
                foreach($sentence as $word)
                {
                    if(isset($this->wordsByDocuments[$word][$docIndex]))
                    {
                        $this->wordsByDocuments[$word][$docIndex]++;
                    }
                    else
                    {
                        $this->wordsByDocuments[$word][$docIndex] = 1;
                    }
                }
            }
        }
        return false;
    }

    private function CountWords($document) //подсчет количества слов в корпусе - в сумме и для каждого слова
    {
        $words = &$this->words;
        $sumWordsCount = &$this->wordsCountOverall;
        $paragraphsCount = count($document);
        for($i=0;$i<$paragraphsCount;$i++)
        {
            $paragraph = $document[$i];
            $sentencesCount = count($paragraph);
            for($j=0;$j<$sentencesCount;$j++)
            {
                $sentence = $paragraph[$j];
                $wordsCount = count($sentence);
                $sumWordsCount+=$wordsCount;
                for($k=0;$k<$wordsCount;$k++)
                {
                    $word = $sentence[$k];
                    if(isset($words[$word]))
                    {
                        $words[$word]++;
                    }
                    else
                    {
                        $words[$word] = 1;
                    }
                }
            }
        }
    }
}