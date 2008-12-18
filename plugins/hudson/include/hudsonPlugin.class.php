<?php
/**
 * @copyright Copyright (c) Xerox Corporation, CodeX, Codendi 2007-2008.
 *
 * This file is licensed under the GNU General Public License version 2. See the file COPYING.
 * 
 * @author Marc Nazarian <marc.nazarian@xrce.xerox.com>
 *
 * HudsonPlugin
 */

require_once('common/plugin/Plugin.class.php');

class hudsonPlugin extends Plugin {
	
	function hudsonPlugin($id) {
		$this->Plugin($id);
        $this->_addHook('site_admin_option_hook', 'siteAdminHooks', false);
        $this->_addHook('javascript_file', 'jsFile', false);
        $this->_addHook('cssfile', 'cssFile', false);
        
        $this->_addHook('widget_instance', 'myPageBox', false);
        $this->_addHook('widgets', 'widgets', false);
        
	}
	
    function &getPluginInfo() {
        if (!is_a($this->pluginInfo, 'hudsonPluginInfo')) {
            require_once('hudsonPluginInfo.class.php');
            $this->pluginInfo =& new hudsonPluginInfo($this);
        }
        return $this->pluginInfo;
    }
    
    function siteAdminHooks($params) {
        echo '<li><a href="'.$this->getPluginPath().'/">hudson</a></li>';
    }
    
    function cssFile($params) {
        // Only show the stylesheet if we're actually in the hudson pages.
        // This stops styles inadvertently clashing with the main site.
        if (strpos($_SERVER['REQUEST_URI'], $this->getPluginPath()) === 0) {
            echo '<link rel="stylesheet" type="text/css" href="'.$this->getThemePath().'/css/style.css" />';
        }
    }
    
    function jsFile($params) {
        // Only include the js files if we're actually in the IM pages.
        // This stops styles inadvertently clashing with the main site.
        if (strpos($_SERVER['REQUEST_URI'], $this->getPluginPath()) === 0) {
            echo '<script type="text/javascript" src="/scripts/scriptaculous/scriptaculous.js"></script>'."\n";
            echo '<script type="text/javascript" src="hudson_tab.js"></script>'."\n";
        }
    }
    
    function myPageBox($params) {
        if ($params['widget'] == 'myhudsonjobs') {
            require_once('hudson_Widget_MyMonitoredJobs.class.php');
            $params['instance'] = new hudson_Widget_MyMonitoredJobs($this);
        }
    }
    function widgets($params) {
        require_once('common/widget/WidgetLayoutManager.class.php');
        $lm = new WidgetLayoutManager();
        if ($params['owner_type'] == $lm->OWNER_TYPE_USER) {
            $params['codex_widgets'][] = 'myhudsonjobs';
        }
    }
        
    function process() {
        require_once('hudson.class.php');
        $controler =& new hudson();
        $controler->process();
    }
}

?>