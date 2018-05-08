<?php
/**
 * AmazonSimpleAffiliate (ASA2)
 * For more information see http://www.wp-amazon-plugin.com/
 * 
 * 
 *
 * @author   Timo Reith <timo@ifeelweb.de>
 * @version  $Id: Abstract.php 1248505 2015-09-18 13:49:54Z worschtebrot $
 */ 
abstract class IfwPsn_Util_Parser_Abstract
{
    /**
     * @param $string
     * @return mixed
     */
    public static function stripNullByte($string)
    {
        return str_replace(chr(0), '', $string);
    }
}
