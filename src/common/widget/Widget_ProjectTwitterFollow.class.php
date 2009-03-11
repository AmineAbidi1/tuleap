<?php
/*
 * Copyright (c) Xerox, 2009. All Rights Reserved.
 *
 * Originally written by Nicolas Terray, 2009. Xerox Codendi Team.
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

require_once('Widget_TwitterFollow.class.php');
require_once('WidgetLayoutManager.class.php');

/**
* Personal TwitterFollow
*/
class Widget_ProjectTwitterFollow extends Widget_TwitterFollow {
    function Widget_ProjectTwitterFollow() {
        $lm = new WidgetLayoutManager();
        $request = HTTPRequest::instance();
        $this->Widget_TwitterFollow('projecttwitterfollow', $request->get('group_id'), $lm->OWNER_TYPE_USER);
    }
}
?>