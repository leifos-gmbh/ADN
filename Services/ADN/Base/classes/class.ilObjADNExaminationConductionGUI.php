<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
 * Settings/Permission object GUI class for ADN examination conduction
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.ilObjADNExaminationConductionGUI.php 27867 2011-02-25 09:35:47Z akill $
 *
 * @ilCtrl_Calls ilObjADNExaminationConductionGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjADNExaminationConductionGUI: ilAdministrationGUI
 *
 * @ingroup ServicesADN
 */
class ilObjADNExaminationConductionGUI extends ilObjectGUI
{
    protected string $type;
    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        $this->type = 'xaec';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('no_permission'), $this->ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
                    $this->tabs_gui->setTabActive('perm_settings');
                    include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                    $perm_gui = new ilPermissionGUI($this);
                    $ret = $this->ctrl->forwardCommand($perm_gui);
                }
                break;

            default:
                $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs()
    {

        if ($this->rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    /**
     * View
     *
     * @param
     * @return
     */
    public function view()
    {
        $this->ctrl->redirectByClass("ilpermissiongui", "perm");
    }
}
