<?php
/**
 * ifeelweb.de WordPress Plugin Framework
 * For more information see http://www.ifeelweb.de/wp-plugin-framework
 * 
 * 
 *
 * @author    Timo Reith <timo@ifeelweb.de>
 * @copyright Copyright (c) ifeelweb.de
 * @version   $Id: UpdateManager.php 1411129 2016-05-05 16:15:58Z worschtebrot $
 * @package   
 */
require_once dirname(__FILE__) . '/Abstract.php';

class IfwPsn_Wp_Plugin_Bootstrap_Observer_UpdateManager extends IfwPsn_Wp_Plugin_Bootstrap_Observer_Abstract
{
    /**
     * @return string
     */
    public function getId()
    {
        return 'update_manager';
    }

    protected function _postModules()
    {
        if (!$this->_pm->getAccess()->isHeartbeat() && $this->_pm->getAccess()->isAdmin()) {

            require_once $this->_pm->getPathinfo()->getRootLib() . '/IfwPsn/Wp/Plugin/Update/Manager.php';
            $this->_resource = new IfwPsn_Wp_Plugin_Update_Manager($this->_pm);
            $this->_resource->init();
        }
    }

    protected function _postBootstrap()
    {
        if (!$this->_pm->getAccess()->isHeartbeat() && !$this->_pm->getAccess()->isActivation() && $this->_pm->getAccess()->isAdmin()) {

            $this->_resource->getPatcher()->autoUpdate();
        }
    }
}
