<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @version   $Id: Interface.php 1248505 2015-09-18 13:49:54Z worschtebrot $
 * @package   
 */
interface IfwPsn_Wp_Model_Mapper_Interface 
{
    public static function getInstance();
    public function getSingular();
    public function getPlural();
    public function getPerPageId($prefix = '');
}
