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
     * Regex signature to validate SMPL-standard Datetime strings: YYYYMMDDHHmmSS
     * @var string
     */
    const SIGNATURE_DATETIME = '/((?!0{4})\d{4})(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])([0-1][0-9]|2[0-3])([0-5][0-9])([0-5][0-9])/';

    /**
     * Regex signature to catch URIs related to the Tag Search feature:
     * /tags/<search-phrase>/
     * /tags/<search-phrase>/<index-number>/ (Seeking through results)
     * /tags/<search-phrase>/date/ (sort results by date, most recent first)
     * /tags/<search-phrase>/date/<index-number>/ (Seeking through results)
     * Issue: This also validates /tags/<search-phrase>///           
     * @var string
     */
    const SIGNATURE_URI_TAG_SEARCH = '/(tags)\/([A-Za-z0-9\-]+)\/{0,1}(date)*\/{0,1}(\d+)*/';

    /**
     * Regex signature to catch URIs related to the Category Indexing:
     * /categories/<category-title>/
     * /categories/<category-title>/<index-number>/     
     *
     * @var string
     */
    const SIGNATURE_URI_CATEGORIES = '/\/(categories)\/([A-Za-z0-9\-]+)\/([0-9]*)\//';
    
    /**
     * Regex signature to catch URIs related to Category Indexing:
     * /<category-title>/articles/ (redirect to /categories/<category-title>/)     
     *
     * @var string
     */
    const SIGNATURE_URI_CATEGORIES_2PARAM = '/([A-Za-z0-9\-]+)\/(articles)\//';
        
    /**
     * Regex signature to catch URIs related to articles:
     * /articles/ (all active articles)
     * /articles/<index-number>/     
     *
     * @var string
     */
    const SIGNATURE_URI_ARTICLES = '/\/(articles)\/([0-9]*)\//';
    
    /**
     * Regex signature to catch URIs related to articles:
     *
     * @var string
     */
    const SIGNATURE_URI_ARTICLES_3PARAM = '/\/([A-Za-z0-9\-]+)\/(articles)\/([A-Za-z0-9\-]+)\//';
    
    /**
     * Regex signature to catch URIs related to pages:
     * /<category-title>/<page-title>/ (Long-form URL)     
     *
     * @var string
     */
    const SIGNATURE_URI_PAGE = '/\/([A-Za-z0-9\-]+)\/([A-Za-z0-9\-]+)\//';
    
    /**
     * Regex signature to catch URIs related to pages:
     * /<page-title>/ (Articles cannot be accessed this way, they must have the "Static Content" flag to be treated like a page)     
     *
     * @var string
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
     * @return mixed
     */ 
    public static function Validate($pattern, $subject)
    {
        $matches = array();
        $valid = preg_match($pattern, $subject, $matches);
        
        if ($valid === 1) {
            return $matches;
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