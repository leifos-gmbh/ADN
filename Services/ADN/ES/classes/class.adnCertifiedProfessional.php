<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * Certified professional application class. This class manages candidates/certified professionals
 * which are both handled and stored in the same database table. Basic properties inlcude name,
 * addresses, information on latest registered exam and blocking status.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.de>
 * @version $Id: class.adnCertifiedProfessional.php 28361 2011-04-05 08:12:06Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnCertifiedProfessional extends adnDBBase
{
    private const ONLINE_CANDIDATE_LOGIN_PREFIX = 'Kandidat';

    protected $id; // [int]
    protected $salutation; // [string]
    protected $last_name; // [string]
    protected $first_name; // [string]
    protected $birthdate; // [ilDate]
    protected $citizenship; // [int]
    protected $postal_country; // [int]
    protected $postal_code; // [string]
    protected $postal_city; // [string]
    protected $postal_street; // [string]
    protected $postal_street_no; // [string]
    protected $shipping_salutation; // [string]
    protected $shipping_last_name; // [string]
    protected $shipping_first_name; // [string]
    protected $shipping_country; // [int]
    protected $shipping_code; // [string]
    protected $shipping_city; // [string]
    protected $shipping_street; // [string]
    protected $shipping_street_no; // [string]
    protected $shipping_active; // [bool]
    protected $phone; // [string]
    protected $email; // [string]
    protected $comment; // [string]
    protected $subject_area; // [string]
    protected $registered_exam; // [bool]
    protected $foreign_certificate; // [bool]	// Basisbescheinigung aus dem Auslang
    protected $registered_by; // [int]
    protected $blocked_until; // [ilDate]
    protected $ilias_user_id; // [int]
    protected $foreign_cert_handed_in; // [bool]	// Bescheinigung aus dem Ausland vorgelegt #13
    protected ?adnCertifiedProfessionalImageHandler $image_handler = null;

    /**
     * Constructor
     */
    public function __construct($a_id = null)
    {
        global $ilCtrl;

        if ($a_id) {
            $this->setId($a_id);
            $this->read();
        }
    }

    public function getImageHandler() : ?adnCertifiedProfessionalImageHandler
    {
        if (!$this->image_handler instanceof adnCertifiedProfessionalImageHandler && $this->getId()) {
            $this->image_handler = new adnCertifiedProfessionalImageHandler($this->getId());
        }
        return $this->image_handler;
    }

    /**
     * Set id
     *
     * @param int $a_id
     */
    public function setId($a_id)
    {
        $this->id = (int) $a_id;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set saluation
     *
     * @param string $a_value
     */
    public function setSalutation($a_value)
    {
        if (in_array($a_value, array("m", "f"))) {
            $this->salutation = (string) $a_value;
        }
    }

    /**
     * Get salutation
     *
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * Set last name
     *
     * @param string $a_name
     */
    public function setLastName($a_name)
    {
        $this->last_name = (string) $a_name;
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set first name
     *
     * @param string $a_name
     */
    public function setFirstName($a_name)
    {
        $this->first_name = (string) $a_name;
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set birthdate
     *
     * @param ilDate $a_value
     */
    public function setBirthdate(ilDate $a_value)
    {
        $this->birthdate = $a_value;
    }

    /**
     * Get birthdate
     *
     * @return ilDate
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Get age
     *
     * @return int
     */
    public function getAge()
    {
        return adnDateUtil::getAge($this->getBirthdate());
    }

    /**
     * Set citizenship
     *
     * @param int $a_value
     */
    public function setCitizenship($a_value)
    {
        if (!(int) $a_value) {
            $this->citizenship = null;
        } else {
            $this->citizenship = (int) $a_value;
        }
    }

    /**
     * Get citizenship
     *
     * @return int
     */
    public function getCitizenship()
    {
        return $this->citizenship;
    }

    /**
     * Set postal country
     *
     * @param int $a_value
     */
    public function setPostalCountry($a_value)
    {
        if (!(int) $a_value) {
            $this->postal_country = null;
        } else {
            $this->postal_country = (int) $a_value;
        }
    }

    /**
     * Get postal country
     *
     * @return int
     */
    public function getPostalCountry()
    {
        return $this->postal_country;
    }

    /**
     * Set postal code
     *
     * @param string $a_value
     */
    public function setPostalCode($a_value)
    {
        $this->postal_code = (string) $a_value;
    }

    /**
     * Get postal code
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * Set postal city
     *
     * @param string $a_value
     */
    public function setPostalCity($a_value)
    {
        $this->postal_city = (string) $a_value;
    }

    /**
     * Get postal city
     *
     * @return string
     */
    public function getPostalCity()
    {
        return $this->postal_city;
    }

    /**
     * Set postal street
     *
     * @param string $a_value
     */
    public function setPostalStreet($a_value)
    {
        $this->postal_street = (string) $a_value;
    }

    /**
     * Get postal street
     *
     * @return string
     */
    public function getPostalStreet()
    {
        return $this->postal_street;
    }

    /**
     * Set postal street number
     *
     * @param string $a_value
     */
    public function setPostalStreetNumber($a_value)
    {
        $this->postal_street_number = (string) $a_value;
    }

    /**
     * Get postal street number
     *
     * @return string
     */
    public function getPostalStreetNumber()
    {
        return $this->postal_street_number;
    }

    /**
     * Set shipping saluation
     *
     * @param string $a_value
     */
    public function setShippingSalutation($a_value)
    {
        if (in_array($a_value, array("m", "f"))) {
            $this->shipping_salutation = (string) $a_value;
        }
    }

    /**
     * Get shipping salutation
     *
     * @return string
     */
    public function getShippingSalutation()
    {
        return $this->shipping_salutation;
    }

    /**
     * Set shipping last name
     *
     * @param string $a_name
     */
    public function setShippingLastName($a_name)
    {
        $this->shipping_last_name = (string) $a_name;
    }

    /**
     * Get shipping last name
     *
     * @return string
     */
    public function getShippingLastName()
    {
        return $this->shipping_last_name;
    }

    /**
     * Set shipping first name
     *
     * @param string $a_name
     */
    public function setShippingFirstName($a_name)
    {
        $this->shipping_first_name = (string) $a_name;
    }

    /**
     * Get shipping first name
     *
     * @return string
     */
    public function getShippingFirstName()
    {
        return $this->shipping_first_name;
    }

    /**
     * Set shipping country
     *
     * @param int $a_value
     */
    public function setShippingCountry($a_value)
    {
        if (!(int) $a_value) {
            $this->shipping_country = null;
        } else {
            $this->shipping_country = (int) $a_value;
        }
    }

    /**
     * Get shipping country
     *
     * @return int
     */
    public function getShippingCountry()
    {
        return $this->shipping_country;
    }

    /**
     * Set shipping code
     *
     * @param string $a_value
     */
    public function setShippingCode($a_value)
    {
        $this->shipping_code = (string) $a_value;
    }

    /**
     * Get shipping code
     *
     * @return string
     */
    public function getShippingCode()
    {
        return $this->shipping_code;
    }

    /**
     * Set shipping city
     *
     * @param string $a_value
     */
    public function setShippingCity($a_value)
    {
        $this->shipping_city = (string) $a_value;
    }

    /**
     * Get shipping city
     *
     * @return string
     */
    public function getShippingCity()
    {
        return $this->shipping_city;
    }

    /**
     * Set shipping street
     *
     * @param string $a_value
     */
    public function setShippingStreet($a_value)
    {
        $this->shipping_street = (string) $a_value;
    }

    /**
     * Get shipping street
     *
     * @return string
     */
    public function getShippingStreet()
    {
        return $this->shipping_street;
    }

    /**
     * Set shipping street number
     *
     * @param string $a_value
     */
    public function setShippingStreetNumber($a_value)
    {
        $this->shipping_street_number = (string) $a_value;
    }

    /**
     * Get shipping street number
     *
     * @return string
     */
    public function getShippingStreetNumber()
    {
        return $this->shipping_street_number;
    }

    /**
     * Set shipping active
     *
     * @param bool $a_value
     */
    public function setShippingActive($a_value)
    {
        $this->shipping_active = (bool) $a_value;
    }

    /**
     * Is shipping address active?
     *
     * @return bool
     */
    public function isShippingActive()
    {
        return $this->shipping_active;
    }

    /**
     * Set phone
     *
     * @param string $a_value
     */
    public function setPhone($a_value)
    {
        $this->phone = (string) $a_value;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $a_value
     */
    public function setEmail($a_value)
    {
        $this->email = (string) $a_value;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set comment
     *
     * @param string $a_value
     */
    public function setComment($a_value)
    {
        $this->comment = (string) $a_value;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set registered for exam
     *
     * @param bool $a_value
     */
    public function setRegisteredForExam($a_value)
    {
        $this->registered_exam = (bool) $a_value;
    }

    /**
     * Get registered for exam status
     *
     * @return bool
     */
    public function isRegisteredForExam()
    {
        return $this->registered_exam;
    }

    /**
     * Set foreign certificate (Basisbescheinigung aus Ausland vorhanden)
     *
     * @param bool $a_value
     */
    public function setForeignCertificate($a_value)
    {
        $this->foreign_certificate = (bool) $a_value;
    }

    /**
     * Get foreign certificate status
     *
     * @return bool
     */
    public function hasForeignCertificate()
    {
        return $this->foreign_certificate;
    }

    /**
     * Set foreign certificate handed in (Bescheinigung aus dem Ausland vorgelegt)
     *
     * @param bool $a_value
     */
    public function setForeignCertificateHandedIn($a_value)
    {
        $this->foreign_cert_handed_in = (bool) $a_value;
    }

    /**
     * Get foreign certificate handed in status
     *
     * @return bool
     */
    public function hasForeignCertificateHandedIn()
    {
        return $this->foreign_cert_handed_in;
    }

    /**
     * Set registered by
     *
     * @param int $a_value
     */
    public function setRegisteredBy($a_value)
    {
        $this->registered_by = (int) $a_value;
    }

    /**
     * Get registered by
     *
     * @return int
     */
    public function getRegisteredBy()
    {
        return $this->registered_by;
    }

    /**
     * Set blocked by
     *
     * @param int $a_value
     */
    public function setBlockedBy($a_value)
    {
        if ($a_value !== null) {
            $this->blocked_by = (int) $a_value;
        } else {
            $this->blocked_by = null;
        }
    }

    /**
     * Get blocked by
     *
     * @return int
     */
    public function getBlockedBy()
    {
        return $this->blocked_by;
    }

    /**
     * Set blocked until
     *
     * @param ilDate $a_value
     */
    public function setBlockedUntil(ilDate $a_value = null)
    {
        $this->blocked_until = $a_value;
    }

    /**
     * Get blocked until
     *
     * @return ilDate
     */
    public function getBlockedUntil()
    {
        return $this->blocked_until;
    }

    /**
     * Set subject area
     *
     * @param string $a_value
     */
    public function setSubjectArea($a_value)
    {
        $this->subject_area = (string) $a_value;
    }

    /**
     * Get subject area
     *
     * @return string
     */
    public function getSubjectArea()
    {
        return $this->subject_area;
    }

    /**
     * Set last event
     *
     * @param int $a_value
     */
    public function setLastEvent($a_value)
    {
        if ($a_value !== null) {
            $this->last_event = (int) $a_value;
        } else {
            $this->last_event = null;
        }
    }

    /**
     * Get last event
     *
     * @return int
     */
    public function getLastEvent()
    {
        return $this->last_event;
    }

    /**
     * Set ILIAS user id
     *
     * @param	int	$a_val	ILIAS user id
     */
    public function setIliasUserId($a_val)
    {
        $this->ilias_user_id = $a_val;
    }

    /**
     * Get ILIAS user id
     *
     * @return	int	ILIAS user id
     */
    public function getIliasUserId()
    {
        return $this->ilias_user_id;
    }

    /**
     * Read db entry
     */
    public function read()
    {
        global $ilDB;

        $id = $this->getId();
        if (!$id) {
            return;
        }

        $res = $ilDB->query("SELECT salutation,last_name,first_name," .
            "birthdate,citizenship,subject_area," .
            "registered_for_exam,foreign_certificate,foreign_cert_handed_in,pa_country,pa_postal_code," .
            "pa_city,pa_street," .
            "pa_street_no,sa_salutation,sa_last_name,sa_first_name,sa_country," .
            "sa_postal_code,sa_city," .
            "sa_street,sa_street_no,sa_active,phone,email,ucomment," .
            "registered_by_wmo_id,blocked_until," .
            "blocked_by_wmo_id,last_ta_event_id,ilias_user_id" .
            " FROM adn_cp_professional" .
            " WHERE id = " . $ilDB->quote($id, "integer"));
        $set = $ilDB->fetchAssoc($res);
        $this->setSalutation($set["salutation"]);
        $this->setLastName($set["last_name"]);
        $this->setFirstName($set["first_name"]);
        $this->setBirthdate(new ilDate($set["birthdate"], IL_CAL_DATE, ilTimeZone::UTC));
        $this->setCitizenship($set["citizenship"]);
        $this->setSubjectArea($set["subject_area"]);
        $this->setRegisteredForExam($set["registered_for_exam"]);
        $this->setForeignCertificate($set["foreign_certificate"]);
        $this->setForeignCertificateHandedIn($set["foreign_cert_handed_in"]);
        $this->setPostalCountry($set["pa_country"]);
        $this->setPostalCode($set["pa_postal_code"]);
        $this->setPostalCity($set["pa_city"]);
        $this->setPostalStreet($set["pa_street"]);
        $this->setPostalStreetNumber($set["pa_street_no"]);
        $this->setShippingSalutation($set["sa_salutation"]);
        $this->setShippingLastName($set["sa_last_name"]);
        $this->setShippingFirstName($set["sa_first_name"]);
        $this->setShippingCountry($set["sa_country"]);
        $this->setShippingCode($set["sa_postal_code"]);
        $this->setShippingCity($set["sa_city"]);
        $this->setShippingStreet($set["sa_street"]);
        $this->setShippingStreetNumber($set["sa_street_no"]);
        $this->setShippingActive($set["sa_active"]);
        $this->setPhone($set["phone"]);
        $this->setEmail($set["email"]);
        $this->setComment($set["ucomment"]);
        $this->setRegisteredBy($set["registered_by_wmo_id"]);
        $this->setBlockedBy($set["blocked_by_wmo_id"]);
        if ($set["blocked_until"]) {
            $this->setBlockedUntil(new ilDate($set["blocked_until"], IL_CAL_DATE, ilTimeZone::UTC));
        }
        $this->setLastEvent($set["last_ta_event_id"]);
        $this->setIliasUserId($set["ilias_user_id"]);

        parent::_read($id, "adn_cp_professional");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array
     */
    protected function propertiesToFields()
    {
        $fields = array("salutation" => array("text", $this->getSalutation()),
            "last_name" => array("text", $this->getLastName()),
            "first_name" => array("text", $this->getFirstName()),
            "birthdate" => array("timestamp", $this->getBirthdate()->get(
                IL_CAL_DATE,
                "",
                ilTimeZone::UTC
            )),
            "citizenship" => array("integer", $this->getCitizenship()),
            "subject_area" => array("text", $this->getSubjectArea()),
            "registered_for_exam" => array("integer", $this->isRegisteredForExam()),
            "foreign_certificate" => array("integer", $this->hasForeignCertificate()),
            "foreign_cert_handed_in" => array("integer", $this->hasForeignCertificateHandedIn()),
            "pa_country" => array("integer", $this->getPostalCountry()),
            "pa_postal_code" => array("text", $this->getPostalCode()),
            "pa_city" => array("text", $this->getPostalCity()),
            "pa_street" => array("text", $this->getPostalStreet()),
            "pa_street_no" => array("text", $this->getPostalStreetNumber()),
            "sa_salutation" => array("text", $this->getShippingSalutation()),
            "sa_last_name" => array("text", $this->getShippingLastName()),
            "sa_first_name" => array("text", $this->getShippingFirstName()),
            "sa_country" => array("integer", $this->getShippingCountry()),
            "sa_postal_code" => array("text", $this->getShippingCode()),
            "sa_city" => array("text", $this->getShippingCity()),
            "sa_street" => array("text", $this->getShippingStreet()),
            "sa_street_no" => array("text", $this->getShippingStreetNumber()),
            "sa_active" => array("integer", $this->isShippingActive()),
            "phone" => array("text", $this->getPhone()),
            "email" => array("text", $this->getEmail()),
            "ucomment" => array("text", $this->getComment()),
            "registered_by_wmo_id" => array("integer", $this->getRegisteredBy()),
            "blocked_by_wmo_id" => array("integer", $this->getBlockedBy()),
            "last_ta_event_id" => array("integer", $this->getLastEvent()),
            "ilias_user_id" => array("integer", $this->getIliasUserId())
            );

        $date = $this->getBlockedUntil();
        if ($date && !$date->isNull()) {
            $fields["blocked_until"] = array("timestamp", $date->get(
                IL_CAL_DATE,
                "",
                ilTimeZone::UTC
            ));
        } else {
            $fields["blocked_until"] = array("timestamp", "");
        }
            
        return $fields;
    }

    /**
     * Create new db entry
     *
     * @return int new id
     */
    public function save()
    {
        global $ilDB;

        // sequence
        $this->setId($ilDB->nextId("adn_cp_professional"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);
            
        $ilDB->insert("adn_cp_professional", $fields);

        parent::_save($id, "adn_cp_professional");

        $this->image_handler = new adnCertifiedProfessionalImageHandler($id);
        
        return $id;
    }

    /**
     * Update db entry
     *
     * @return bool
     */
    public function update()
    {
        global $ilDB;

        $id = $this->getId();
        if (!$id) {
            return;
        }
        
        $fields = $this->propertiesToFields();

        $ilDB->update("adn_cp_professional", $fields, array("id" => array("integer", $id)));

        parent::_update($id, "adn_cp_professional");

        return true;
    }

    /**
     * Delete from DB
     *
     * @return bool
     */
    public function delete()
    {
        global $ilDB;

        $id = $this->getId();
        if ($id) {
            // Use Case U.PVB.3.4: check if candidate has training events, assignments or
            // certifcates
            $in_use = false;
            if ($this->getLastEvent()) {
                include_once "Services/ADN/TA/classes/class.adnTrainingEvent.php";
                $event = new adnTrainingEvent($this->getLastEvent());
                $now = new ilDateTime(time(), IL_CAL_UNIX);
                $now = $now->get(IL_CAL_UNIX, "", ilTimeZone::UTC);
                if ($event->getDateTo()->get(IL_CAL_UNIX, "", ilTimeZone::UTC) < $now) {
                    $in_use = true;
                }
            }
            if (!$in_use) {
                include_once "Services/ADN/EP/classes/class.adnAssignment.php";
                $all = adnAssignment::getAllCurrentUserAssignments($id, true);
                if (sizeof($all)) {
                    $in_use = true;
                }
            }
            if (!$in_use) {
                // @todo: include archived and invalid certificates?
                include_once "Services/ADN/ES/classes/class.adnCertificate.php";
                $all = adnCertificate::getAllCertificates(
                    array("cp_professional_id" => $id),
                    true,
                    true
                );
                if (sizeof($all)) {
                    $in_use = true;
                }
            }
            
            if ($in_use) {
                // cr-008 start
                $this->setFirstName(null);
                $this->setLastName(null);
                $this->setBirthdate(new ilDate());
                //$this->setCitizenship(null);
                $this->setPostalCountry(null);
                $this->setPostalCode(null);
                $this->setPostalCity(null);
                $this->setPostalStreet(null);
                $this->setPostalStreetNumber(null);
                $this->setShippingLastName(null);
                $this->setShippingFirstName(null);
                $this->setShippingCountry(null);
                $this->setShippingCode(null);
                $this->setShippingCity(null);
                $this->setShippingStreet(null);
                $this->setShippingStreetNumber(null);
                $this->setPhone(null);
                $this->setEmail(null);
                $this->setComment(null);
                $this->setLastEvent(null);

                // delete all invitations
                include_once("./Services/ADN/Report/classes/class.adnReportInvitation.php");
                include_once "Services/ADN/EP/classes/class.adnAssignment.php";
                foreach (adnAssignment::getAllAssignments(array("user_id" => $this->getId())) as $ass) {
                    if ($ass["invited_on"] != "") {
                        include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
                        $inv = new adnReportInvitation(
                            new adnExaminationEvent($ass['ep_exam_event_id'])
                        );
                        $inv->delete($this->getId());
                    }
                }

                // delete all answer sheets
                include_once("./Services/ADN/Report/classes/class.adnReportAnswerSheet.php");
                include_once("./Services/ADN/EP/classes/class.adnAnswerSheet.php");
                include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
                include_once "Services/ADN/EP/classes/class.adnAssignment.php";
                include_once "Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php";
                foreach (adnAssignment::getAllAssignments(array("user_id" => $this->getId())) as $ass) {
                    $exam_event = new adnExaminationEvent($ass["ep_exam_event_id"]);
                    foreach (adnAnswerSheetAssignment::getAllSheets($this->getId(), $ass["ep_exam_event_id"]) as $s) {
                        $as_rep = new adnReportAnswerSheet($exam_event);
                        $as_rep->deleteSheet($this->getId(), $s["ep_answer_sheet_id"]);
                    }
                }

                // archive/delete all certificates
                include_once("./Services/ADN/Report/classes/class.adnReportCertificate.php");
                include_once './Services/ADN/ES/classes/class.adnCertificate.php';
                foreach (adnCertificate::getAllCertificates(array("cp_professional_id" => $this->getId()), true, true) as $cert) {
                    $c = new adnCertificate($cert["id"]);

                    // delete certificate file
                    adnReportCertificate::deleteCertificate($cert["id"]);

                    if (!$c->isArchived()) {
                        // archivate certificate
                        $c->delete();
                    }
                }

                // delete all score notifications
                include_once("./Services/ADN/EP/classes/class.adnAssignment.php");
                include_once("./Services/ADN/EP/classes/class.adnExaminationEvent.php");
                foreach (adnAssignment::getAllAssignments(array("user_id" => $this->getId())) as $ass) {
                    include_once './Services/ADN/Report/classes/class.adnReportScoreNotificationLetter.php';
                    if (adnReportScoreNotificationLetter::hasFile($ass["ep_exam_event_id"], $ass["id"])) {
                        adnReportScoreNotificationLetter::deleteFile($ass["ep_exam_event_id"], $ass["id"]);
                    }
                }

                // delete all invoices
                include_once("./Services/ADN/ES/classes/class.adnCertificate.php");
                include_once './Services/ADN/Report/classes/class.adnReportInvoice.php';
                foreach (adnCertificate::getAllCertificates(array("cp_professional_id" => $this->getId()), true, true) as $cert) {
                    if (adnReportInvoice::hasInvoice($cert["id"])) {
                        adnReportInvoice::deleteInvoice($cert["id"]);
                    }
                }
                // cr-008 end


                // remove assignments / invitations / sheet assignments from FUTURE exam events
                include_once "Services/ADN/EP/classes/class.adnAssignment.php";
                include_once "Services/ADN/EP/classes/class.adnAnswerSheetAssignment.php";
                $all = adnAssignment::getAllCurrentUserAssignments($id);
                if (sizeof($all)) {
                    foreach ($all as $event_id) {
                        $sheets = adnAnswerSheetAssignment::getSheetsSelect($id, $event_id);
                        if ($sheets) {
                            $ilDB->manipulate("DELETE FROM adn_ep_cand_sheet" .
                                " WHERE " . $ilDB->in("id", array_keys($sheets), false, "integer"));
                        }

                        $ilDB->manipulate("DELETE FROM adn_ep_exam_invitation" .
                            " WHERE ep_exam_event_id = " . $ilDB->quote($event_id, "integer") .
                            " AND cp_professional_id = " . $ilDB->quote($id, "integer"));

                        $ilDB->manipulate("DELETE FROM adn_ep_assignment" .
                            " WHERE ep_exam_event_id = " . $ilDB->quote($event_id, "integer") .
                            " AND cp_professional_id = " . $ilDB->quote($id, "integer"));
                    }
                }

                $this->setArchived(true);
                return $this->update();
            } else {
                // $ilDB->manipulate("DELETE FROM adn_es_certificate".
                //		"WHERE cp_professional_id = ".$ilDB->quote($id, "integer"));
                $ilDB->manipulate("DELETE FROM adn_ep_cand_sheet" .
                    " WHERE cp_professional_id = " . $ilDB->quote($id, "integer"));
                $ilDB->manipulate("DELETE FROM adn_ep_exam_invitation" .
                    " WHERE cp_professional_id = " . $ilDB->quote($id, "integer"));
                $ilDB->manipulate("DELETE FROM adn_ep_assignment" .
                    " WHERE cp_professional_id = " . $ilDB->quote($id, "integer"));
                $ilDB->manipulate("DELETE FROM adn_cp_professional" .
                    " WHERE id = " . $ilDB->quote($id, "integer"));
                $this->setId(null);
                return true;
            }
        }
    }

    /**
     * Get all candidates
     *
     * @param array $a_filter
     * @param bool $a_with_archived
     * @return array
     */
    public static function getAllCandidates(array $a_filter = null, $a_with_archived = false)
    {
        global $ilDB;

        $sql = "SELECT a.id,a.last_name,a.first_name,a.birthdate,a.citizenship,a.subject_area," .
            "a.registered_by_wmo_id, a.create_date, " .
            "a.blocked_until,a.pa_country,a.pa_street,a.pa_street_no,a.pa_postal_code,a.pa_city," .
            "a.foreign_certificate,a.foreign_cert_handed_in," .
            "a.last_ta_event_id,a.ilias_user_id" .
            " FROM adn_cp_professional a ";

        // cr-008 start
        if (is_array($a_filter) && is_array($a_filter["equal"]) && count($a_filter["equal"]) > 0) {
            $on = "";
            if (isset($a_filter["equal"]["last_name"])) {
                $on .= " AND a.last_name = b.last_name";
            }
            if (isset($a_filter["equal"]["pa_city"])) {
                $on .= " AND a.pa_city = b.pa_city";
            }
            if (isset($a_filter["equal"]["pa_street"])) {
                $on .= " AND a.pa_street = b.pa_street";
            }
            if (isset($a_filter["equal"]["birthdate"])) {
                $on .= " AND a.birthdate = b.birthdate";
            }
            if ($on != "") {
                $sql .= " JOIN adn_cp_professional b ON (a.id <> b.id " . $on . ") ";
            }
        }
        // cr-008 end
        $where = array();

        // archived?
        if (!$a_with_archived) {
            $where[] = "a.archived < " . $ilDB->quote(1, "integer");
        }

        // name filter
        if (isset($a_filter["last_name"]) && $a_filter["last_name"]) {
            $where[] = $ilDB->like("a.last_name", "text", "%" . $a_filter["last_name"] . "%");
        }
        if (isset($a_filter["first_name"]) && $a_filter["first_name"]) {
            $where[] = $ilDB->like("a.first_name", "text", "%" . $a_filter["first_name"] . "%");
        }

        // birthdate
        if (isset($a_filter["birthdate"]) && $a_filter["birthdate"]) {
            if ($a_filter["birthdate"]["from"]) {
                $where[] = "a.birthdate >= " .
                    $ilDB->quote($a_filter["birthdate"]["from"]->get(IL_CAL_DATE));
            }
            if ($a_filter["birthdate"]["to"]) {
                $where[] = "a.birthdate <= " .
                    $ilDB->quote($a_filter["birthdate"]["to"]->get(IL_CAL_DATE));
            }
        }

        // subject area
        if (isset($a_filter["subject_area"]) && $a_filter["subject_area"]) {
            $where[] = "a.subject_area = " . $ilDB->quote($a_filter["subject_area"], "text");
        }

        // registered by
        if (isset($a_filter["registered_by"]) && $a_filter["registered_by"]) {
            $where[] = "a.registered_by_wmo_id = " .
                $ilDB->quote($a_filter["registered_by"], "integer");
        }

        // registered for exam
        if (isset($a_filter["registered_for_exam"]) && $a_filter["registered_for_exam"]) {
            $where[] = "a.registered_for_exam = " .
                $ilDB->quote($a_filter["registered_for_exam"], "integer");
        }
        if (isset($a_filter["id"]) && $a_filter["id"]) {
            $ids = (is_array($a_filter["id"]))
                ? $a_filter["id"]
                : array($a_filter["id"]);
            $where[] = $ilDB->in("a.id", $ids, false, "integer");
        }
        if (sizeof($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[$row["id"]] = $row;
        }
        return $all;
    }

    /**
     * Get candidate ids and names
     *
     * @return array array of candiates (names)
     */
    public static function getCandidatesSelect()
    {
        global $ilDB;

        $sql = "SELECT id,last_name,first_name" .
            " FROM adn_cp_professional" .
            " WHERE archived < " . $ilDB->quote(1, "integer") .
            " ORDER BY last_name,first_name";
    
        $res = $ilDB->query($sql);
        $all = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $all[$row["id"]] = $row["last_name"] . ", " . $row["first_name"];
        }
        return $all;
    }

    /**
     * Lookup property
     *
     * @param integer $a_id candidate id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT " . $a_prop .
            " FROM adn_cp_professional" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Lookup name
     *
     * @param int $a_id
     * @return string name
     */
    public static function lookupName($a_id)
    {
        return self::lookupProperty($a_id, "last_name") . ", " .
            self::lookupProperty($a_id, "first_name");
    }

    /**
     * Check if given combination of user data already exists
     *
     * @param string $a_last_name
     * @param string $a_first_name
     * @param ilDate $a_birthdate
     * @param int $a_id
     * @param bool $a_return_id
     * @return bool is user unique true/false
     */
    public static function isUserUnique(
        $a_last_name,
        $a_first_name,
        ilDate $a_birthdate,
        $a_id = null,
        $a_return_id = false
    )
    {
        global $ilDB;

        $sql = "SELECT id" .
            " FROM adn_cp_professional" .
            " WHERE last_name = " . $ilDB->quote(trim($a_last_name), "text") .
            " AND first_name = " . $ilDB->quote(trim($a_first_name), "text") .
            " AND birthdate = " . $ilDB->quote(
                $a_birthdate->get(IL_CAL_DATE, "", ilTimeZone::UTC),
                "timestamp"
            ) .
            " AND archived < " . $ilDB->quote(1, "integer");
        if ($a_id) {
            $sql .= " AND id <> " . $ilDB->quote($a_id, "integer");
        }

        $set = $ilDB->query($sql);
        if (!$a_return_id) {
            if ($ilDB->numRows($set)) {
                return false;
            }
            return true;
        } else {
            if ($ilDB->numRows($set)) {
                $row = $ilDB->fetchAssoc($set);
                return $row["id"];
            }
            return null;
        }
    }

    /**
     * Check if any professional has assigned a given country
     *
     * We are using a separate method because country deletion depends on it
     *
     * @param int $a_id
     * @return bool is country assigned true/false
     */
    public static function hasCountry($a_id)
    {
        global $ilDB;

        $id = $ilDB->quote($a_id, "integer");

        $set = $ilDB->query("SELECT id" .
            " FROM adn_cp_professional" .
            " WHERE citizenship = " . $id . " OR pa_country = " . $id . " OR sa_country = " . $id);
        if ($ilDB->numRows($set)) {
            return true;
        }
        return false;
    }

    /**
     * Has professional a valid domestic or foreign base certificate?
     *
     * @param bool $a_current_foreign
     * @return bool has certificate true/false
     */
    public function hasValidBaseCertificate($a_current_foreign = null)
    {
        if ($a_current_foreign === null) {
            $a_current_foreign = $this->hasForeignCertificate();
        }
        if ($a_current_foreign) {
            return true;
        } else {
            include_once "Services/ADN/ES/classes/class.adnCertificate.php";
            $all = adnCertificate::getAllProfessionalsWithValidCertificates();
            if ($all) {
                return in_array($this->getId(), $all);
            }
        }
        return false;
    }

    /**
     * Prepare user for online test
     *
     * @param int $a_cp_id certified professional id
     */
    public static function prepareUser($a_cp_id)
    {
        global $ilClientIniFile, $rbacadmin;

        $cp_prof = new adnCertifiedProfessional($a_cp_id);
        if ($cp_prof->getIliasUserId() <= 0) {
            // create ilias user for professional candidate
            $user = new ilObjUser();
            $user->setGender("m");
            $user->setLogin(self::ONLINE_CANDIDATE_LOGIN_PREFIX . $a_cp_id);
            $user->setFirstname("Prüfungskandidat");
            $user->setLastname($a_cp_id);
            $user->setActive(true);
            $user->setAgreeDate(ilUtil::now());
            $user->setTimeLimitUnlimited(true);
            $user->setProfileIncomplete(false);
            $user->create();
            $user->saveAsNew();
            $user->setLanguage($_POST["language"]);
            $user->setPref("hits_per_page", 20);
            $user->writePrefs();

            $role_id = $ilClientIniFile->readVariable("system", "ONLINE_TEST_ROLE");

            if ($role_id > 0) {
                $rbacadmin->assignUser($role_id, $user->getId(), true);
            }

            $cp_prof->setIliasUserId($user->getId());
            $cp_prof->update();
        }
    }

    /**
     * Get certified professional id for user login
     *
     * @param string $a_login user login
     * @return int certified professional id
     */
    public static function getCPIdForUserLogin($a_login)
    {
        global $ilDB;

        $user_id = ilObjUser::_lookupId($a_login);
        $set = $ilDB->query("SELECT id" .
            " FROM adn_cp_professional" .
            " WHERE ilias_user_id = " . $ilDB->quote($user_id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["id"];
        }
        return 0;
    }
}
