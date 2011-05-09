<?php
/**
 * Copyright (c) STMicroelectronics, 2011. All Rights Reserved.
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('common/plugin/Plugin.class.php');
require_once('common/system_event/SystemEvent.class.php');
require_once('CodexToRemedy.class.php');

class codextoremedyPlugin extends Plugin {

    /**
     * Constructor
     *
     * @param Integer $id id of the plugin
     *
     * @return void
     */
    function codextoremedyPlugin($id) {
        $this->Plugin($id);
        if (extension_loaded('oci8')) {
            $this->_addHook('cssfile', 'cssFile', false);
            $this->_addHook('site_help', 'redirectToPlugin', false);
        }
    }

    /**
     * Retrieve plugin info
     *
     * @return CodexToRemedyPluginInfo
     */
    function getPluginInfo() {
        if (!$this->pluginInfo instanceof CodexToRemedyPluginInfo) {
            include_once('CodexToRemedyPluginInfo.class.php');
            $this->pluginInfo = new CodexToRemedyPluginInfo($this);
        }
        return $this->pluginInfo;
    }

    /**
     * Launch the controller
     *
     * @return void
     */
    function process() {
        $controler = new CodexToRemedy();
        $controler->process();
    }

     /**
     * Set the right style sheet for CodexToRemedy form
     *
     * @param Array $params params of the hook
     *
     * @return void
     */
    function cssFile($params) {
        echo '<link rel="stylesheet" type="text/css" href="'.$this->getThemePath().'/css/style.css" />'."\n";
    }

    /**
     * Redirects the hook call to plugin path
     *
     * @return void
     */
    function redirectToPlugin() {
        $c = new CodexToRemedy();
        $c->redirect($this->getPluginPath().'/');
    }

    /**
     * Retreive a param config giving its name
     *
     * @param String $name Property name
     *
     * @return String
     */
    public function getProperty($name) {
        $info =$this->getPluginInfo();
        return $info->getPropertyValueForName($name);
    }
}
?>