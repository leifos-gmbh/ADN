<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Directory of certified professionals GUI class
 *
 * Generate overview of certified professionals (in a certain timeframe) [display only]
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCertifiedProfessionalDirectoryGUI.php 31050 2011-10-09 10:38:06Z smeyer $
 *
 * @ilCtrl_Calls adnCertifiedProfessionalDirectoryGUI:
 *
 * @ingroup ServicesADN
 */
class adnCertifiedProfessionalDirectoryGUI
{

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilToolbarGUI $toolbar;
    protected ilTabsGUI $tabs;
    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {

        $this->tpl->setTitle($this->lng->txt("adn_cp") . " - " . $this->lng->txt("adn_cp_dir"));
        
        $next_class = $this->ctrl->getNextClass();
        
        // forward command to next gui class in control flow
        switch ($next_class) {
            // no next class:
            // this class is responsible to process the command
            default:
                $cmd = $this->ctrl->getCmd("createDirectoryForm");
                
                switch ($cmd) {
                    // commands that need read permission
                    case "createDirectoryForm":
                    case "displayDirectoryList":
                    case "downloadDirectory":
                        if (adnPerm::check(adnPerm::CP, adnPerm::READ)) {
                            $this->$cmd();
                        }
                        break;
                }
                break;
        }
    }
    
    /**
     * Display form to create directory
     */
    protected function createDirectoryForm()
    {

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "displayDirectoryList"));
        $form->setTitle($this->lng->txt("adn_create_certificate_professional_directory"));
        $form->setDescription($this->lng->txt("adn_professional_directory_help"));

        // default: first day of current year
        $from = new ilDateTimeInputGUI($this->lng->txt("adn_date_from"), "date_from");
        $date = new ilDateTime(date("Y") . "-01-01 00:00:00", IL_CAL_DATETIME);
        $from->setDate($date);
        $from->setRequired(true);
        $form->addItem($from);

        // default: today
        $to = new ilDateTimeInputGUI($this->lng->txt("adn_date_to"), "date_to");
        $date = new ilDateTime(time(), IL_CAL_UNIX);
        $to->setDate($date);
        $to->setRequired(true);
        $form->addItem($to);

        include_once "Services/ADN/MD/classes/class.adnWMO.php";
        $wsd = new ilSelectInputGUI($this->lng->txt("adn_issued_by"), "issued_by");
        $options = array(0 => $this->lng->txt("adn_filter_all")) + adnWMO::getWMOsSelect();
        $wsd->setOptions($options);
        $wsd->setRequired(true);
        $form->addItem($wsd);

        // reload data from last request
        if (isset($_REQUEST["date_from"])) {
            $from->setDate(new ilDate((string) $_REQUEST["date_from"], IL_CAL_DATE));
            $to->setDate(new ilDate((string) $_REQUEST["date_to"], IL_CAL_DATE));
            $wsd->setValue((int) $_REQUEST["issued_by"]);
        }

        $form->addCommandButton("displayDirectoryList", $this->lng->txt("adn_display_directory"));

        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Display list of professionals
     */
    protected function displayDirectoryList()
    {

        // 1st call
        if (isset($_POST["issued_by"])) {
            $wmo = (int) $_POST["issued_by"];
            $date_from = $_POST["date_from"];
            $date_from = new ilDate($date_from, IL_CAL_DATE);
            $date_to = $_POST["date_to"];
            $date_to = new ilDate($date_to, IL_CAL_DATE);
        }
        // reload, e.g. table sorting
        else {
            $wmo = (int) $_REQUEST["issued_by"];
            $date_from = new ilDate($_REQUEST["date_from"], IL_CAL_DATE);
            $date_to = new ilDate($_REQUEST["date_to"], IL_CAL_DATE);
        }

        $this->ctrl->setParameter($this, "issued_by", $wmo);
        $this->ctrl->setParameter($this, "date_from", $date_from->get(IL_CAL_DATE));
        $this->ctrl->setParameter($this, "date_to", $date_to->get(IL_CAL_DATE));

        $this->tabs->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget(
            $this,
            "createDirectoryForm"
        ));

        if (adnPerm::check(adnPerm::ST, adnPerm::READ)) {
            $this->toolbar->addButton(
                $this->lng->txt("adn_download_directory"),
                $this->ctrl->getLinkTarget($this, "downloadDirectory")
            );
        }
        
        include_once("Services/ADN/CP/classes/class.adnCertifiedProfessionalDirectoryTableGUI.php");
        $table = new adnCertifiedProfessionalDirectoryTableGUI(
            $this,
            "displayDirectoryList",
            $date_from,
            $date_to,
            $wmo
        );

        $this->tpl->setContent($table->getHTML());
    }
    
    /**
     * Generate download directory (pdf)
     */
    protected function downloadDirectory()
    {
        
        $wmo = (int) $_REQUEST["issued_by"];
        $date_from = new ilDate($_REQUEST["date_from"], IL_CAL_DATE);
        $date_to = new ilDate($_REQUEST["date_to"], IL_CAL_DATE);

        $this->ctrl->setParameter($this, "issued_by", $wmo);
        $this->ctrl->setParameter($this, "date_from", $date_from->get(IL_CAL_DATE));
        $this->ctrl->setParameter($this, "date_to", $date_to->get(IL_CAL_DATE));

        include_once("Services/ADN/CP/classes/class.adnCertifiedProfessionalDirectoryTableGUI.php");
        $table = new adnCertifiedProfessionalDirectoryTableGUI(
            $this,
            "displayDirectoryList",
            $date_from,
            $date_to,
            $wmo
        );
        $table->getHTML();

        // check if there is any data to export
        $data = $table->getData();

        if (!$data) {
            $this->ctrl->redirect($this, "displayDirectoryList");
        }

        $data = ilUtil::sortArray($data, $table->getOrderField(), $table->getOrderDirection());

        include_once './Services/ADN/Report/exceptions/class.adnReportException.php';
        try {
            include_once("./Services/ADN/Report/classes/class.adnReportDirectory.php");
            $report = new adnReportDirectory();
            $report->setWmoIds($table->getWmoIds());
            $report->setDuration($date_from, $date_to);
            $report->setData($data);
            $report->create();
                
            ilUtil::deliverFile(
                $report->getOutfile(),
                'Verzeichnis.pdf',
                'application.pdf'
            );
        } catch (adnReportException $e) {
            ilUtil::sendFailure($e->getMessage(), true);
            $this->ctrl->redirect($this, 'displayDirectoryList');
        }
    }
}
