<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: AdminComments.php 1411129 2016-05-05 16:15:58Z worschtebrot $
 * @package   
 */ 
class IfwPsn_Util_Parser_AdminComments 
{
    /**
     * @var array
     */
    protected static $_allowedTags = array(
        '<a>',
        '<b>',
        '<br>',
        '<div>',
        '<em>',
        '<p>',
        '<span>',
        '<ul>',
        '<li>',
    );

    /**
     * @return array
     */
    public static function getAllowedTags()
    {
        return self::$_allowedTags;
    }

    public static function addAllowedTag($tag)
    {
        //array_push(self::$_allowedTags, $tag);
    }

    public static function sanitize($text)
    {
        return strip_tags(html_entity_decode($text), implode('', self::getAllowedTags()));
    }

    /**
     * @param $text
     * @return mixed|string
     */
    public static function parse($text)
    {
        $result =  nl2br(strip_tags(html_entity_decode($text), implode('', self::getAllowedTags())));
        $result = IfwPsn_Util_Parser_Html::sanitize($result);
        return $result;
    }
}
