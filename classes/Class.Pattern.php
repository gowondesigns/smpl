<?php
/**
 * Class.Pattern
 * @package SMPL\Pattern
 */

/**
 * Pattern Class
 * Stores and validates REGEX patterns  
 * @package Pattern
 */
class Pattern
{
    /**
     * Return the regex matches on validation
     */
    const RETURN_MATCHES = true;

    /**
     * Regex XML tag names: Alphanumeric string, must begin with alpha
     */
    const XML_VALID_TAG_NAME = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
    
    /**
     * Regex Debug::Timer labels
     */
    const DEBUG_TIMER_LABEL_NAME = '/(^[A-Za-z][A-Za-z0-9\-_]{0,29}$)|(^0$)/';

    /**
     * Regex SQL column names: Alphanumeric string, must begin with alpha, up to 30 char length
     */
    const SQL_NAME = '/[A-Za-z][A-Za-z0-9\-_]{0,29}/';

    /**
     * Regex SQL column with possible table prepend
     */
    const SQL_NAME_WITH_PREPEND = '/[A-Za-z][A-Za-z0-9\-_]{0,29}(.[A-Za-z][A-Za-z0-9\-_]{0,29})?/';
    
    /**
     * Regex SMPL-format Datetime strings: YYYYMMDDHHmmSS all numeric
     */
    const SIGNATURE_DATETIME = '/((?!0{4})\d{4})(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])([0-1][0-9]|2[0-3])([0-5][0-9])([0-5][0-9])/';

    /**
     * Regex signature to catch URIs related to the Tag Search feature:
     * /tags/<search-phrase>/
     * /tags/<search-phrase>/<index-number>/ (Seeking through results)
     * /tags/<search-phrase>/date/ (sort results by date, most recent first)
     * /tags/<search-phrase>/date/<index-number>/ (Seeking through results)
     * Issue: This also validates /tags/<search-phrase>///           
     */
    const SIGNATURE_URI_TAG_SEARCH = '/(tags)\/([A-Za-z0-9\-]+)\/{0,1}(date)*\/{0,1}(\d+)*/';

    /**
     * Regex signature to catch URIs related to the Category Indexing:
     * /categories/<category-title>/
     * /categories/<category-title>/<index-number>/     
     */
    const SIGNATURE_URI_CATEGORIES = '/\/(categories)\/([A-Za-z0-9\-]+)\/([0-9]*)\//';
    
    /**
     * Regex signature to catch URIs related to Category Indexing:
     * /<category-title>/articles/ (redirect to /categories/<category-title>/)     
     */
    const SIGNATURE_URI_CATEGORIES_2PARAM = '/([A-Za-z0-9\-]+)\/(articles)\//';
        
    /**
     * Regex signature to catch URIs related to articles:
     * /articles/ (all active articles)
     * /articles/<index-number>/     
     */
    const SIGNATURE_URI_ARTICLES = '/\/(articles)\/([0-9]*)\//';
    
    /**
     * Regex signature to catch URIs related to articles:
     */
    const SIGNATURE_URI_ARTICLES_3PARAM = '/\/([A-Za-z0-9\-]+)\/(articles)\/([A-Za-z0-9\-]+)\//';
    
    /**
     * Regex signature to catch URIs related to pages:
     * /<category-title>/<page-title>/ (Long-form URL)     
     */
    const SIGNATURE_URI_PAGE = '/\/([A-Za-z0-9\-]+)\/([A-Za-z0-9\-]+)\//';
    
    /**
     * Regex signature to catch URIs related to pages:
     * /<page-title>/ (Articles cannot be accessed this way, they must have the "Static Content" flag to be treated like a page)     
     */
    const SIGNATURE_URI_PAGE_1PARAM = '/\/([A-Za-z0-9\-]+)\//';

    /**
     * Empty private constructor to enforce "static-ness"
     * @return \Pattern
     */
    private function __construct() {}

    /**
     * Validates REGEX patterns. Returns array of passing elements.
     * @param string $pattern
     * @param string $subject
     * @param bool $returnMatches
     * @return mixed
     */
    public static function Validate($pattern, $subject, $returnMatches = false)
    {
        $matches = array();
        $valid = preg_match($pattern, $subject, $matches);
        
        if ($valid === 1 && $returnMatches === Pattern::RETURN_MATCHES) {
            return $matches;
        }
        elseif ($valid === 1) {
            return true;
        }
        else {
            return false;
        }
        /*
        $results = preg_replace_callback(
            $pattern,
            function ($matches)
            {
                return strtolower($matches[0]);
            },
            $string);
        return $results;
        //*/        
    }
}
?>