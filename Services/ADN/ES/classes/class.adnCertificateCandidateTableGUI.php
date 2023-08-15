<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * ADN certificate table GUI class (scoring context). This table lists all certificates
 * that are assigned to candidates of an exam. For each certificate an "edit" action is offered.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCertificateCandidateTableGUI.php 27884 2011-02-27 21:01:07Z akill $
 *
 * @ingroup ServicesADN
 */
class adnCertificateCandidateTableGUI extends ilTable2GUI
{
    protected $event_id; // [int] id of examination event
    protected $map; // [array] captions for foreign keys

    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent default command
     * @param int $a_event_id id of examination event
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_event_id)
    {
        global $ilCtrl, $lng;

        $this->event_id = (int) $a_event_id;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->id = "adn_tbl_ccd";

        include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
        $this->setTitle($lng->txt("adn_candidates") . ": " .
            adnExaminationEvent::lookupName($this->event_id));

        // column headers
        $this->addColumn("", "", true);
        $this->addColumn($this->lng->txt("adn_last_name"), "last_name");
        $this->addColumn($this->lng->txt("adn_first_name"), "first_name");
        $this->addColumn($this->lng->txt("adn_birthdate"), "birthdate");
        $this->addColumn($this->lng->txt("adn_certificate issued_on"), "issued_on");
        $this->addColumn($this->lng->txt('adn_certificate_type'), 'certificate_type');
        $this->addColumn($this->lng->txt('adn_certificate_status'), 'card_status');
        $this->addColumn($this->lng->txt("actions"));
    
        $this->setDefaultOrderField("last_name");
        $this->setDefaultOrderDirection("asc");

        // get assignments of event
        include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
        $assignments = adnAssignment::getAllAssignments(
            array("event_id" => $this->event_id,
                "result_total" => adnAssignment::SCORE_PASSED),
            array("first_name", "last_name", "birthdate")
        );
        $this->setData($assignments);

        $this->addMultiCommand("downloadCertificates", $lng->txt("adn_download_certificates"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.candidates_certificate_row.html", "Services/ADN/ES");
    }
    
    /**
     * Fill table row
     *
     * @param array $a_set data array
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        // actions...
        $ilCtrl->setParameter($this->parent_obj, "ass_id", $a_set["id"]);

        include_once("./Services/ADN/ES/classes/class.adnCertificate.php");
        if ($ct_id = adnCertificate::getCertificateIdOfProfForEvent(
            $a_set["cp_professional_id"],
            $a_set["ep_exam_event_id"]
        )) {

            // issued on date
            $certificate = new adnCertificate($ct_id);
            $this->tpl->setVariable(
                "VAL_ISSUED_ON",
                ilDatePresentation::formatDate(
                    $certificate->getIssuedOn()
                )
            );
            if ($certificate->getUuid() === '') {
                // checkbox only for certificate type "paper"
                $this->tpl->setCurrentBlock("cb");
                $this->tpl->setVariable("CID", $ct_id);
                $this->tpl->parseCurrentBlock();

                // edit link
                if (adnPerm::check(adnPerm::ES, adnPerm::WRITE)) {
                    $ilCtrl->setParameter($this->parent_obj, "ct_id", $ct_id);
                    $this->tpl->setCurrentBlock("action");
                    $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_edit_certificate"));
                    $this->tpl->setVariable(
                        "HREF_CMD",
                        $ilCtrl->getLinkTarget($this->parent_obj, "editCertificate")
                    );
                    $this->tpl->parseCurrentBlock();
                    $ilCtrl->setParameter($this->parent_obj, "ct_id", "");
                }

                $this->tpl->setVariable(
                    'VAL_CERTIFICATE_TYPE',
                    $this->lng->txt('adn_certificate_type_paper')
                );
                $this->tpl->setVariable('VAL_CERTIFICATE_STATUS');
            } else {

                $this->tpl->setVariable(
                    'VAL_CERTIFICATE_TYPE',
                    $this->lng->txt('adn_certificate_type_card')
                );
                $this->tpl->setVariable(
                    'VAL_CERTIFICATE_STATUS',
                    $this->lng->txt('adn_certificate_status_' . (string) $certificate->getCardStatus())
                );
            }
        } else {
            // create link
            if (adnPerm::check(adnPerm::ES, adnPerm::WRITE)) {
                $ilCtrl->setParameter($this->parent_obj, "ct_id", "");
                $this->tpl->setCurrentBlock("action");
                $this->tpl->setVariable("TXT_CMD", $lng->txt("adn_create_certificate"));
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "createCertificate")
                );
                $this->tpl->parseCurrentBlock();
            }
        }
        $ilCtrl->setParameter($this->parent_obj, "ass_id", "");
                
        // properties
        $this->tpl->setVariable("VAL_LAST_NAME", $a_set["last_name"]);
        $this->tpl->setVariable("VAL_FIRST_NAME", $a_set["first_name"]);

        $this->tpl->setVariable(
            "VAL_BIRTHDATE",
            ilDatePresentation::formatDate(
                new ilDate($a_set["birthdate"], IL_CAL_DATE)
            )
        );
    }
}
