<?php
    $max_documents_count = 20;
    $sites_to_ignore = array('wikipedia', 'ushmm.org', 'liveinternet.ru', 'nesiditsa.ru', 'avtovokzalov.info', 'youtube', 'habrahabr', 'comss', 'ria.ru', 'vk.com');

    define("LANGUAGES", array('ru'=>'ru', 'en'=>'en'));
    define("MAX_DOCUMENTS_COUNT", $max_documents_count);
    define("SEARCH_URL", "https://yandex.com/search/xml");
    define("SEARCH_USER", "facilisdes");
    define("SEARCH_USER_PARAM_NAME", "user");
    define("SEARCH_QUERY_PARAM_NAME", "query");
    define("SEARCH_KEY", "03.312919160:77843c8ff8ad57e23b4fe9a74a82a586");
    define("SEARCH_KEY_PARAM_NAME", "key");
    define("SEARCH_LANGUAGE", "ru");
    define("SEARCH_LANGUAGE_PARAM_NAME", "l10n");
    define("SEARCH_SORTBY", "rlv");
    define("SEARCH_SORTBY_PARAM_NAME", "sortby");
    define("SEARCH_FILTER", "moderate");
    define("SEARCH_FILTER_PARAM_NAME", "filter");
    define("SEARCH_GROUPBY", "attr%3D%22%22.mode%3Dflat.groups-on-page%3D$max_documents_count.docs-in-group%3D1");
    define("SEARCH_GROUPBY_PARAM_NAME", "groupby");
    define("SEARCH_GROUPING", '<groupby attr="d" mode="deep" groups-on-page="10" docs-in-group="1" />');
    define("SEARCH_GROUPING_PARAM_NAME", 'groupings');
    define("SEARCH_SITES_TO_IGNORE", $sites_to_ignore);
    define('LOG_ERROR_LOGFILE_PATH', 'errors.log');
    define('LOG_REGULAR_LOGFILE_PATH', 'journal.log');
    define('YANDEX_CAPTCHA_URL', 'https://yandex.ru/xcheckcaptcha');
    define('YANDEX_CAPTCHA_KEY_PARAM_NAME', 'key');
    define('YANDEX_CAPTCHA_REP_PARAM_NAME', 'rep');

    define('LINK_PARSER_TIMEOUT', 5);
    define("AGGREGATION_MAX_SITES_COUNT", 8);

    define("TFIDF_SITE_RELEVANCE_STEP", 0.05); //A
    define("TFIDF_SITE_RELEVANCE_BASE", 1);   //A
    define("TFIDF_QUERY_WORD_MULTIPLIER", 0.4); //B
    define("TFIDF_KEYWORDS_COUNT", 8);
    define("TFIDF_KEYWORD_IN_SINGLE_DOCUMENT_MULTIPLIER", 0.01); //B

    define("SYMREF_FIRST_IN_PARAGRAPH_MULTIPLIER", 1.4); //F
    define("SYMREF_LAST_IN_PARAGRAPH_MULTIPLIER", 1.1); //F
    define("SYMREF_FIRST_SENTENCE_IN_TEXT_MULTIPLIER", 1.1); //F
    define("SYMSUM_NEIGHBOURS_BONUS_MULTIPLIER", 0.5); //E
    define("SYMSUM_SUMMARY_LENGTH", 16);
    define("SYMSUM_REFERENCE_TO_OTHER_DOC_STRENGTH", 0.5); //D
    define("SYMSUM_SIMULAR_WORDS_PERCENTAGE_TO_DENY_SENTENCE", 0.6);
    define("SYMSUM_SHARED_RATING_PERCENTAGE", 0.3); //C

    define("DB_LOGIN", 'agsum');
    define("DB_PASSWORD", '1@34qwEr');
    define("DB_NAME", 'agsum');
    define("DB_HOST", 'localhost');

    define("CACHER_LIFESPAN_DAYS", 7);

