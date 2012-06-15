<?php
/**
 * Copyright (c) Enalean, 2012. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */
require_once AGILEDASHBOARD_BASE_DIR .'/AgileDashboard/Pane.class.php';
require_once 'common/mustache/MustacheRenderer.class.php';
require_once 'PaneContentPresenter.class.php';
require_once 'Column.class.php';
require_once 'Swimline.class.php';
require_once 'Mapping.class.php';
require_once 'MappingCollection.class.php';
require_once 'InjectColumnIdVisitor.class.php';
require_once 'InjectDropIntoClassnamesVisitor.class.php';

/**
 * A pane to be displayed in AgileDashboard
 */
class Cardwall_Pane extends AgileDashboard_Pane {

    /**
     * @var array Accumulated array of Tracker_FormElement_Field_Selectbox
     */
    private $accumulated_status_fields = array();

    /**
     * @var Planning_Milestone
     */
    private $milestone;

    public function __construct(Planning_Milestone $milestone) {
        $this->milestone = $milestone;

        $column_id_visitor = new Cardwall_InjectColumnIdVisitor();
        $this->milestone->getPlannedArtifacts()->accept($column_id_visitor);
        $this->accumulated_status_fields = $column_id_visitor->getAccumulatedStatusFields();

        $drop_into_visitor = new Cardwall_InjectDropIntoClassnamesVisitor($this->getMapping());
        $this->milestone->getPlannedArtifacts()->accept($drop_into_visitor);
    }

    /**
     * @see AgileDashboard_Pane::getIdentifier()
     */
    public function getIdentifier() {
        return 'cardwall';
    }

    /**
     * @see AgileDashboard_Pane::getTitle()
     */
    public function getTitle() {
        return 'Card Wall';
    }

    /**
     * @see AgileDashboard_Pane::getContent()
     */
    public function getContent() {
        if (! $this->getField()) {
            return $GLOBALS['Language']->getText('plugin_cardwall', 'on_top_miss_status');
        }

        $columns       = $this->getColumns();
        $mappings      = $this->getMapping();
        $swimlines     = $this->getSwimlines($columns, $this->milestone->getPlannedArtifacts()->getChildren());
        $backlog_title = $this->milestone->getPlanning()->getBacklogTracker()->getName();

        $renderer  = new MustacheRenderer(dirname(__FILE__).'/../templates');
        $presenter = new Cardwall_PaneContentPresenter($backlog_title, $swimlines, $columns, $mappings);
        ob_start();
        $renderer->render('pane-content', $presenter);
        return ob_get_clean();
    }

    /**
     * @var Tracker_FormElement_Field_Selectbox
     */
    private $field;

    /**
     * @return Tracker_FormElement_Field_Selectbox
     */
    private function getField() {
        if (! $this->field) {
            $tracker     = $this->milestone->getPlanning()->getBacklogTracker();
            $this->field = Tracker_Semantic_StatusFactory::instance()->getByTracker($tracker)->getField();
        }
        return $this->field;
    }

    /**
     * @return Cardwall_MappingCollection
     */
    private function getMapping() {
        $columns  = $this->getColumns();
        $mappings = new Cardwall_MappingCollection();
        foreach ($this->accumulated_status_fields as $status_field) {
            foreach ($this->getFieldValues($status_field) as $value) {
                foreach ($columns as $column) {
                    if ($column->label == $value->getLabel()) {
                        $mappings->add(new Cardwall_Mapping($column->id, $status_field->getId(), $value->getId()));
                    }
                }
            }
        }
        return $mappings;
    }

    /**
     *  @var array of Cardwall_Column
     */
    private $columns;

    private function getColumns() {
        if ($this->columns) return $this->columns;

        $field = $this->getField();
        if (! $field) return array();

        $values        = $this->getFieldValues($field);
        $decorators    = $field->getBind()->getDecorators();
        $this->columns = array();
        foreach ($values as $value) {
            list($bgcolor, $fgcolor) = $this->getColumnColors($value, $decorators);

            $this->columns[] = new Cardwall_Column((int)$value->getId(), $value->getLabel(), $bgcolor, $fgcolor);
        }
        return $this->columns;
    }

    private function getColumnColors($value, $decorators) {
        $id      = (int)$value->getId();
        $bgcolor = 'white';
        $fgcolor = 'black';
        if (isset($decorators[$id])) {
            $bgcolor = $decorators[$id]->css($bgcolor);
            //choose a text color to have right contrast (black on dark colors is quite useless)
            $fgcolor = $decorators[$id]->isDark($fgcolor) ? 'white' : 'black';
        }
        return array($bgcolor, $fgcolor);
    }

    private function getFieldValues(Tracker_FormElement_Field_Selectbox $field) {
        $values = $field->getAllValues();
        foreach ($values as $key => $value) {
            if ($value->isHidden()) {
                unset($values[$key]);
            }
        }
        if ($values) {
            if (! $field->isRequired()) {
                $none = new Tracker_FormElement_Field_List_Bind_StaticValue(100, $GLOBALS['Language']->getText('global','none'), '', 0, false);
                $values = array_merge(array($none), $values);
            }
        }
        return $values;
    }

    private function getSwimlines(array $columns, array $nodes) {
        $swimlines = array();
        foreach ($nodes as $child) {
            $cells = $this->getCells($columns, $child->getChildren());
            $swimlines[] = new Cardwall_Swimline($child, $cells);
        }
        return $swimlines;
    }

    private function getCells(array $columns, array $nodes) {
        $cells = array();
        foreach ($columns as $column) {
            $cells[] = $this->getCell($column, $nodes);
        }
        return $cells;
    }

    private function getCell(Cardwall_Column $column, array $nodes) {
        $artifacts = array();
        foreach ($nodes as $node) {
            $this->addNodeToCell($node, $column, $artifacts);
        }
        return array('artifacts' => $artifacts);;
    }

    private function addNodeToCell(TreeNode $node, Cardwall_Column $column, array &$artifacts) {
        $data            = $node->getData();
        $artifact        = $data['artifact'];
        $artifact_status = $artifact->getStatus();
        if ($this->isArtifactInCell($artifact, $column)) {
            $artifacts[] = $node;
        }
    }

    private function isArtifactInCell(Tracker_Artifact $artifact, Cardwall_Column $column) {
        $artifact_status = $artifact->getStatus();
        return $artifact_status === $column->label || $artifact_status === null && $column->id == 100;
    }
}
?>