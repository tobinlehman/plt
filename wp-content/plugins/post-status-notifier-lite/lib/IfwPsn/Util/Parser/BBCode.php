<?php
/**
 * AmazonSimpleAffiliate (ASA2)
 * For more information see http://www.wp-amazon-plugin.com/
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: BBCode.php 1411129 2016-05-05 16:15:58Z worschtebrot $
 */ 
class IfwPsn_Util_Parser_BBCode extends IfwPsn_Util_Parser_Abstract
{
    /**
     * @param $text
     * @return mixed
     */
    public static function parse($text)
    {
        $find = array(
            '~\[br\]~s',
            '~\[b\](.*?)\[/b\]~s',
            '~\[i\](.*?)\[/i\]~s',
            '~\[u\](.*?)\[/u\]~s',
            '~\[quote\](.*?)\[/quote\]~s',
            '~\[size=(.*?)\](.*?)\[/size\]~s',
            '~\[color=(.*?)\](.*?)\[/color\]~s',
            '~\[url=((?:ftp|https?)://.*?)\](.*?)\[/url\]~s',
            '~\[img\](https?://.*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s'
        );

        $replace = array(
            '<br>',
            '<b>$1</b>',
            '<i>$1</i>',
            '<span style="text-decoration:underline;">$1</span>',
            '<pre>$1</'.'pre>',
            '<span style="font-size:$1px;">$2</span>',
            '<span style="color:$1;">$2</span>',
            '<a href="$1" target="_blank">$2</a>',
            '<img src="$1" alt="" />'
        );

        $text = preg_replace($find, $replace, $text);
        return self::stripNullByte($text);
    }
}
