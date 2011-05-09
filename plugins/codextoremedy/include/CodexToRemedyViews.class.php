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

require_once('mvc/PluginView.class.php');
require_once('common/include/HTTPRequest.class.php');

/**
 * CodexToRemedyViews
 */
class CodexToRemedyViews extends PluginView {

    /**
     * Display header
     *
     * @return void
     */
    function header() {
        $title = $GLOBALS['Language']->getText('plugin_codextoremedy', 'title');
        $GLOBALS['HTML']->header(array('title'=>$title));
        include($GLOBALS['Language']->getContent('help/site'));
    }

    /**
     * Display footer
     *
     * @return void
     */
    function footer() {
        $GLOBALS['HTML']->footer(array());
    }

    // {{{ Views
    /**
     * Redirect after ticket submission
     *
     * @return void
     */
    function remedyPostSubmission() {
        $c = $this->getController();
        $data = $c->getData();
        $requestStatus = $data['status'];
        if (!$requestStatus) {
            $params        = $data['params'];
            $this->displayForm($params);
        } else {
            $c->addInfo($GLOBALS['Language']->getText('plugin_codextoremedy', 'codextoremedy_ticket_submission_success'));
            $c->redirect('/my/');
        }
    }

    /**
     * Display form to fill a request
     *
     * @param Array $params params of the hook
     *
     * @return Void
     */
    function displayForm($params = null) {
        $um = UserManager::instance();
        $user = $um->getCurrentUser();
        if ($user->isLoggedIn()) {
            $type        = CodexToRemedy::TYPE_SUPPORT;
            $severity    = CodexToRemedy::SEVERITY_MINOR;
            $summary     = '';
            $description =  '';
            if (is_array($params)) {
                if (isset($params['type'])) {
                    $type = $params['type'];
                }
                if (isset($params['severity'])) {
                    $severity = $params['severity'];
                }
                if (isset($params['summary'])) {
                    $summary = $params['summary'];
                }
                if (isset($params['description'])) {
                    $description = $params['description'];
                }
            }
            $p = PluginManager::instance()->getPluginByName('codextoremedy');
            echo '<form name="request" class="cssform" action="'.$p->getPluginPath().'/" method="post" enctype="multipart/form-data">
             <fieldset >
                 <table>
                     <tr>';
            echo '<td><label>Type:</label>&nbsp;<span class="highlight"><big>*</big></b></span></td>
                     <td><select name="type">
                      <option value="'.CodexToRemedy::TYPE_SUPPORT.'" ';
            if ($type == CodexToRemedy::TYPE_SUPPORT) {
                echo 'selected';
            }
            echo '>'.$GLOBALS['Language']->getText('plugin_codextoremedy', 'Support_request').'</option>
                         <option value="'.CodexToRemedy::TYPE_ENHANCEMENT.'" ';
            if ($type == CodexToRemedy::TYPE_ENHANCEMENT) {
                echo 'selected';
            }
            echo '>'.$GLOBALS['Language']->getText('plugin_codextoremedy', 'Enhancement_request').'</option>
                     </select>';
            echo '</td><td><label>'.$GLOBALS['Language']->getText('plugin_codextoremedy', 'severity').':</label>&nbsp;<span class="highlight"><big>*</big></b></span></td>
                             <td><select name="severity">
                             <option value="'.CodexToRemedy::SEVERITY_MINOR.'" ';
            if ($severity == CodexToRemedy::SEVERITY_MINOR) {
                echo 'selected';
            }
            echo '>'.$GLOBALS['Language']->getText('plugin_codextoremedy', 'Minor').'</option>
                             <option value="'.CodexToRemedy::SEVERITY_SERIOUS.'" ';
            if ($severity == CodexToRemedy::SEVERITY_SERIOUS) {
                echo 'selected';
            }
            echo '>'.$GLOBALS['Language']->getText('plugin_codextoremedy', 'Serious').'</option>
                             <option value="'.CodexToRemedy::SEVERITY_CRITICAL.'" ';
            if ($severity == CodexToRemedy::SEVERITY_CRITICAL) {
                echo 'selected';
            }
            echo '>'.$GLOBALS['Language']->getText('plugin_codextoremedy', 'Critical').'</option>
                             </select>
                         </td>
                     </tr>';
            echo '<tr><td><label>'.$GLOBALS['Language']->getText('plugin_codextoremedy', 'summary').':</label>&nbsp;<span class="highlight"><big>*</big></b></span></td>
                     <td><input type="text" name="request_summary" value="'.$summary.'" /></td></tr>';
            echo '<tr><td><label><span class="totop">Description:</span></label>&nbsp;<span class="highlight"><span class="totop"><big>*</big></b></span></span></td><td><textarea name="request_description">'.$description.'</textarea></td></tr>
            <tr><td></td><td><i><b><u>Note</u>: </b>'.$GLOBALS['Language']->getText('plugin_codextoremedy', 'codextoremedy_cc_note').'</i></td></tr>
            <tr><td><label>CC :</label></td><td><input id="codextoremedy_cc" type="text" name="cc" /></td></tr>
            <tr><td></td><td><input name="action" type="hidden" value="submit_ticket" /></td></tr>
            <tr><td></td><td><input name="submit" type="submit" value="Submit" /></td></tr>
                </table>
            </fieldset>
        </form>';
            $js = "new UserAutoCompleter('codextoremedy_cc', '".util_get_dir_image_theme()."', true);";
            $GLOBALS['Response']->includeFooterJavascriptSnippet($js);
        }
    }
    // }}}
}

?>