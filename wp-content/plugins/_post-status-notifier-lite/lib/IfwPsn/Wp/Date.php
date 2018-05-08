<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * Date format helper class
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: Date.php 1411129 2016-05-05 16:15:58Z worschtebrot $
 * @package   
 */ 
class IfwPsn_Wp_Date
{
    /**
     * Formats a date
     *
     * @param $time expects date format YYYY-MM-DD HH:MM:SS
     * @param $format the output format, blog default will be used if empty
     * @param $offset int
     * @param null $timezone
     * @return string the formatted date
     */
    public static function format($time, $format = null, $offset = null, $timezone = null)
    {
        if (empty($timezone)) {
            $timezone = 'UTC';
        }
        $dt = new DateTime($time, new DateTimeZone($timezone));

        if (empty($format)) {
            $format = IfwPsn_Wp_Proxy_Blog::getDateFormat() .' '. IfwPsn_Wp_Proxy_Blog::getTimeFormat();
        }

        if ($offset === null) {
            $offset = IfwPsn_Wp_Proxy_Blog::getGmtOffset();
        }
        if (empty($offset)) {
            $offset = 0;
        }

        return date($format, $dt->format('U') + ($offset * 3600));
    }

    /**
     * Formats a timestamp
     *
     * @param $ts
     * @param $format the output format, blog default will be used if empty
     * @internal param timestamp $time
     * @return string the formatted date
     */
    public static function formatTs($ts, $format = null)
    {
        $dt = new DateTime();
        $dt->setTimestamp($ts);

        if (empty($format)) {
            $format = IfwPsn_Wp_Proxy_Blog::getDateFormat() .' '. IfwPsn_Wp_Proxy_Blog::getTimeFormat();
        }

        $offset = IfwPsn_Wp_Proxy_Blog::getGmtOffset();
        if (empty($offset)) {
            $offset = 0;
        }

        return date($format, $dt->format('U') + ($offset * 3600));
    }

    /**
     * Checks whether a given date string is older than the given seconds
     *
     * @param $time expects date format YYYY-MM-DD HH:MM:SS
     * @param $seconds
     * @return bool
     */
    public static function isOlderThanSeconds($time, $seconds)
    {
        $dt = new DateTime($time, new DateTimeZone('UTC'));

        $offset = IfwPsn_Wp_Proxy_Blog::getGmtOffset();
        if (empty($offset)) {
            $offset = 0;
        }

        $timeTs = (int)$dt->format('U');

        return $timeTs + $seconds < time();
    }

    /**
     * @return string
     */
    public static function getMysqlDateTime($timestamp = null)
    {
        if ($timestamp != null) {
            return date('Y-m-d H:i:s', $timestamp);
        }
        return date('Y-m-d H:i:s');
    }
}
