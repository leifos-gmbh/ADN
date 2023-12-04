<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN certificate application class. This class manages ADN certificates
 * and their properties.
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnCertificate.php 58885 2015-04-16 14:38:44Z fwolf $
 *
 * @ingroup ServicesADN
 */
class adnCertificate extends adnDBBase
{
    public const CARD_STATUS_UNDEFINED = 0;
    public const CARD_STATUS_RECEIVED = 1;

    public const CARD_STATUS_PRODUCTION = 2;

    public const CARD_STATUS_SHIPPED = 3;


    // certificate types
    const DRY_MATERIAL = "dm";
    const TANK = "tank";
    const GAS = "gas";
    const CHEMICALS = "chem";

    // proof types (training and experience)
    const PROOF_TRAIN_DRY = "proof_train_dm";
    const PROOF_TRAIN_TANK = "proof_train_tank";
    const PROOF_TRAIN_COMBINED = "proof_train_combined";
    const PROOF_TRAIN_GAS = "proof_train_gas";
    const PROOF_TRAIN_CHEMICALS = "proof_train_chem";
    const PROOF_EXP_GAS = "proof_exp_gas";
    const PROOF_EXP_CHEMICALS = "proof_exp_chem";

    // status
    const STATUS_VALID = 0;
    const STATUS_INVALID = 1;

    protected $id; // [int]
    protected string $uuid = '';
    protected int $card_status = self::CARD_STATUS_UNDEFINED;
    protected $number = 0; // [int]
    protected $cert_prof_id = 0; // [int]
    protected $exam_id = null; // [int]
    protected $signed_by = ""; // [string]
    protected $issued_by_wmo = 0; // [int]
    protected $is_extension = false; // [bool]
    protected $file = ""; // [string]
    protected $status = self::STATUS_VALID; // [int]

    // certificate types array
    protected $type = array(
        self::DRY_MATERIAL => false,
        self::TANK => false,
        self::GAS => false,
        self::CHEMICALS => false
    );

    // proof types (training and experience) array
    protected $proof = array(
        self::PROOF_TRAIN_DRY => false,
        self::PROOF_TRAIN_TANK => false,
        self::PROOF_TRAIN_COMBINED => false,
        self::PROOF_TRAIN_GAS => false,
        self::PROOF_TRAIN_CHEMICALS => false,
        self::PROOF_EXP_GAS => false,
        self::PROOF_EXP_CHEMICALS => false
    );

    /**
     * Constructor
     *
     * @param integer $a_id certificate id
     */
    public function __construct($a_id = 0)
    {
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }

    public static function lookupIdByUuid(string $uuid) : int
    {
        global $DIC;

        $db = $DIC->database();
        $query = 'select id from adn_es_certificate ' .
            'where uuid = ' . $db->quote($uuid, ilDBConstants::T_TEXT);
        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->id;
        }
        return 0;
    }

    public function initUuid() : void
    {
        $uuid_factory = new adnCardCertificateIdentification();
        $this->uuid = $uuid_factory->identificator();
    }

    public function getUuid() : string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid) : void
    {
        $this->uuid = $uuid;
    }

    /**
     * Get all proof types
     *
     * @return array proof types
     */
    public static function getProofTypes()
    {
        global $lng;
        
        return array(
            self::PROOF_TRAIN_DRY => $lng->txt("adn_proof_train_dm"),
            self::PROOF_TRAIN_TANK => $lng->txt("adn_proof_train_tank"),
            self::PROOF_TRAIN_COMBINED => $lng->txt("adn_proof_train_combined"),
            self::PROOF_TRAIN_GAS => $lng->txt("adn_proof_train_gas"),
            self::PROOF_TRAIN_CHEMICALS => $lng->txt("adn_proof_train_chemicals"),
            self::PROOF_EXP_GAS => $lng->txt("adn_proof_exp_gas"),
            self::PROOF_EXP_CHEMICALS => $lng->txt("adn_proof_exp_chemicals")
        );
    }

    /**
     * Get all certificate types
     *
     * @return array array of certificate types. key is id, value is text representation
     */
    public static function getCertificateTypes()
    {
        global $lng;

        return array(
            self::DRY_MATERIAL =>
                $lng->txt("adn_subject_area_cert_" . self::DRY_MATERIAL),
            self::TANK =>
                $lng->txt("adn_subject_area_cert_" . self::TANK),
            self::GAS =>
                $lng->txt("adn_subject_area_cert_" . self::GAS),
            self::CHEMICALS =>
                $lng->txt("adn_subject_area_cert_" . self::CHEMICALS),
        );
    }

    /**
     * Set id
     *
     * @param integer $a_val id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }

    /**
     * Get id
     *
     * @return integer id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set proof
     *
     * @param string $a_type proof type
     * @param boolean $a_val proof given true/false
     */
    public function setProof($a_type, $a_val)
    {
        $this->proof[$a_type] = $a_val;
    }

    /**
     * Get proof
     *
     * @param string $a_type proof type
     * @return boolean proof given true/false
     */
    public function getProof($a_type)
    {
        return $this->proof[$a_type];
    }

    /**
     * Set number
     *
     * @param number $a_val number
     */
    public function setNumber($a_val)
    {
        $this->number = $a_val;
    }

    /**
     * Get number
     *
     * @return number number
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set certified professional id
     *
     * @param integer $a_val certified professional id
     */
    public function setCertifiedProfessionalId($a_val)
    {
        $this->cert_prof_id = $a_val;
    }

    /**
     * Get certified professional id
     *
     * @return integer certified professional id
     */
    public function getCertifiedProfessionalId()
    {
        return $this->cert_prof_id;
    }

    /**
     * Set examination id
     *
     * @param integer $a_val examination id
     */
    public function setExaminationId($a_val)
    {
        $this->exam_id = $a_val;
    }

    /**
     * Get examination id
     *
     * @return integer examination id
     */
    public function getExaminationId()
    {
        return $this->exam_id;
    }

    /**
     * Set issued on
     *
     * @param ilDateTime $a_val issued on
     */
    public function setIssuedOn($a_val)
    {
        $this->issued_on = $a_val;
    }

    /**
     * Get issued on
     *
     * @return ilDateTime issued on
     */
    public function getIssuedOn()
    {
        return $this->issued_on;
    }

    /**
     * Set valid until
     *
     * @param ilDateTime $a_val valid until
     */
    public function setValidUntil($a_val)
    {
        $this->valid_until = $a_val;
    }

    /**
     * Get valid until
     *
     * @return ilDateTime valid until
     */
    public function getValidUntil()
    {
        return $this->valid_until;
    }

    /**
     * Set type
     *
     * @param string $a_type type
     * @param boolean $a_val type given true/false
     */
    public function setType($a_type, $a_val)
    {
        $this->type[$a_type] = $a_val;
    }

    /**
     * Get type
     *
     * @param string $a_type type
     * @return boolean type given true/false
     */
    public function getType($a_type)
    {
        return $this->type[$a_type];
    }

    /**
     * Set signed by
     *
     * @param string $a_val signed by
     */
    public function setSignedBy($a_val)
    {
        $this->signed_by = $a_val;
    }

    /**
     * Get signed by
     *
     * @return string signed by
     */
    public function getSignedBy()
    {
        return $this->signed_by;
    }

    /**
     * Set issued by wmo
     *
     * @param integer $a_val issued by wmo
     */
    public function setIssuedByWmo($a_val)
    {
        $this->issued_by_wmo = $a_val;
    }

    /**
     * Get issued by wmo
     *
     * @return integer issued by wmo
     */
    public function getIssuedByWmo()
    {
        return $this->issued_by_wmo;
    }

    /**
     * Set is extension
     *
     * @param bool $a_val is extension
     */
    public function setIsExtension($a_val)
    {
        $this->is_extension = $a_val;
    }

    /**
     * Get is extension
     *
     * @return bool is extension
     */
    public function getIsExtension()
    {
        return $this->is_extension;
    }

    /**
     * Set file
     *
     * @param string $a_val file
     */
    public function setFile($a_val)
    {
        $this->file = $a_val;
    }

    /**
     * Get file
     *
     * @return string file
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set status
     *
     * @param bool $a_val status
     */
    public function setStatus($a_val)
    {
        $this->status = $a_val;
    }

    /**
     * Get status
     *
     * @return bool status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get type chem
     *
     * @return bool type chem
     */
    public function getTypeChem()
    {
        return $this->type_chem;
    }

    /**
     * Get full certificate number
     *
     * @return string full certificate number
     */
    public function getFullCertificateNumber()
    {
        return self::_getFullCertificateNumber(
            $this->getIssuedByWmo(),
            $this->getNumber(),
            $this->getIssuedOn()
        );
    }

    /**
     * Get full certificate number (static version)
     *
     * @return string full certificate number
     */
    public static function _getFullCertificateNumber($a_wmo_id, $a_number, $a_issued_on)
    {
        if (!$a_issued_on instanceof ilDate) {
            return '';
        }

        $year = substr($a_issued_on->get(IL_CAL_DATE), 0, 4);
        include_once("./Services/ADN/MD/classes/class.adnWMO.php");
        $number_str = adnWMO::lookupCode($a_wmo_id) . "-" .
            str_pad($a_number, 4, "0", STR_PAD_LEFT) . "-" .
            $year;

        return $number_str;
    }

    /**
     * Read instance data from database
     */
    public function read()
    {
        global $ilDB;

        $set = $ilDB->query("SELECT *" .
            " FROM adn_es_certificate" .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            $this->setNumber($rec["nr"]);
            $this->setUuid((string) $rec['uuid']);
            $this->setCertifiedProfessionalId($rec["cp_professional_id"]);
            $this->setExaminationId($rec["ep_exam_id"]);
            $this->setValidUntil(new ilDate($rec["valid_until"], IL_CAL_DATE));
            $this->setIssuedOn(new ilDate($rec["issued_on"], IL_CAL_DATE));
            $this->setSignedBy($rec["signed_by"]);
            $this->setIssuedByWmo($rec["issued_by_wmo"]);
            $this->setStatus($rec["status"]);
            $this->setIsExtension($rec["is_extension"]);
            $this->setFile($rec["cfile"]);
            $this->setCardStatus((int) $rec['card_status']);
            foreach (self::getProofTypes() as $k => $v) {
                $this->setProof(
                    $k,
                    $rec[$k]
                );
            }
            foreach (self::getCertificateTypes() as $k => $v) {
                $this->setType(
                    $k,
                    $rec["type_" . $k]
                );
            }

            parent::_read($this->getId(), "adn_es_certificate");
        }
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (cp_professional_id, ep_exam_id, nr, signed_by, issued_by_wmo, is_extension,
     * status, cfile, valid_until, issued_on, proof_*, type_*)
     */
    protected function propertiesToFields()
    {
        $fields = array(
            "cp_professional_id" => array("integer", $this->getCertifiedProfessionalId()),
            'uuid' => ['text', $this->getUuid()],
            "ep_exam_id" => array("integer", $this->getExaminationId()),
            "nr" => array("integer", $this->getNumber()),
            "signed_by" => array("text", $this->getSignedBy()),
            "issued_by_wmo" => array("text", $this->getIssuedByWmo()),
            "is_extension" => array("integer", $this->getIsExtension()),
            "status" => array("integer", $this->getStatus()),
            "cfile" => array("integer", $this->getFile()),
            'card_status' => ['integer', $this->getCardStatus()]
            );

        // proofs
        foreach (self::getProofTypes() as $k => $v) {
            $fields[$k] = array("integer", $this->getProof($k));
        }

        // types
        foreach (self::getCertificateTypes() as $k => $v) {
            $fields["type_" . $k] = array("integer", $this->getType($k));
        }

        // valid until
        $date = $this->getValidUntil();
        if ($date && !$date->isNull()) {
            $fields["valid_until"] = array("timestamp", $date->get(IL_CAL_DATE));
        } else {
            $fields["valid_until"] = array("timestamp", "");
        }

        // issued on
        $date = $this->getIssuedOn();
        if ($date && !$date->isNull()) {
            $fields["issued_on"] = array("timestamp", $date->get(IL_CAL_DATE));
        } else {
            $fields["issued_on"] = array("timestamp", "");
        }

        return $fields;
    }

    /**
     * Create certificate
     *
     * @return int new id
     */
    public function save($a_generate_number = true)
    {
        global $ilDB, $ilUser;

        if ($a_generate_number) {
            // set the status of all other certificates of the user to invalid
            $ilDB->manipulate("UPDATE adn_es_certificate" .
                " SET status = " . $ilDB->quote(self::STATUS_INVALID, "integer") .
                " WHERE cp_professional_id = " .
                    $ilDB->quote($this->getCertifiedProfessionalId(), "integer"));

            // lock table to keep table numbering consistent
            $ilDB->lockTables(array(
                0 => array('name' => 'adn_es_certificate','type' => ilDBConstants::LOCK_WRITE),
                1 => array('name' => 'adn_es_certificate_seq', 'type' => ilDBConstants::LOCK_WRITE)
            ));

            $this->setNumber($this->determineNextNumber());
        }

        // save new certificate
        $this->setId($ilDB->nextId("adn_es_certificate"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);

        $ilDB->insert("adn_es_certificate", $fields);

        if ($a_generate_number) {
            // unlock table
            $ilDB->unlockTables();
        }

        parent::_save($id, "adn_es_certificate");

        return $id;
    }

    /**
     * Update certificate
     *
     * @return bool
     */
    public function update()
    {
        global $ilDB;

        $id = $this->getId();
        if (!$id) {
            return false;
        }

        $fields = $this->propertiesToFields();

        // make sure the "issued on" and "issued by" fields are never changed
        // if this may be necessary in the future, we recommend to
        // add an option to the update() function but keep this default behaviour
        unset($fields["issued_by_wmo"]);
        unset($fields["issued_on"]);
        
        $ilDB->update("adn_es_certificate", $fields, array("id" => array("integer", $id)));

        parent::_update($id, "adn_es_certificate");

        return true;
    }

    /**
     * Delete certificate
     */
    public function delete()
    {
        global $ilDB;

        $id = $this->getId();
        if ($id) {
            // certificates are only set to archived
            $this->setArchived(true);
            return $this->update();
        }
    }

    /**
     * Get all certificates
     *
     * @param array $a_filter
     * @param bool $a_with_archived
     * @return array
     */
    public static function getAllCertificates(
        array $a_filter = null,
        $a_with_invalid = false,
        $a_with_archived = false,
        $a_exclude_valids = false
    )
    {
        global $ilDB;

        $sql = "SELECT ct.*, cp.first_name, cp.last_name, cp.birthdate, wmo.code_nr wmo_code_nr " .
            " FROM adn_es_certificate ct " .
            " JOIN adn_cp_professional cp ON (ct.cp_professional_id = cp.id)" .
            " JOIN adn_md_wmo wmo ON (ct.issued_by_wmo = wmo.id)";

        $where = array();

        // include invalids
        if (!$a_with_invalid) {
            $where[] = "ct.status = " . $ilDB->quote(self::STATUS_VALID, "integer");
            $date = new ilDate(time(), IL_CAL_UNIX);
            $where[] = "ct.valid_until >= " . $ilDB->quote($date->get(IL_CAL_DATE), "timestamp");
        }

        // exclude valids
        if ($a_exclude_valids) {
            $date = new ilDate(time(), IL_CAL_UNIX);
            $where[] = "(ct.valid_until < " .
                $ilDB->quote($date->get(IL_CAL_DATE), "timestamp") .
                " or status = " . $ilDB->quote(self::STATUS_INVALID, "integer") .
                ")";
        }

        // filter only one professional
        if (isset($a_filter["cp_professional_id"]) && $a_filter["cp_professional_id"] > 0) {
            $where[] = "ct.cp_professional_id = " .
                $ilDB->quote($a_filter["cp_professional_id"], "integer");
        }

        // name filter
        if (isset($a_filter["last_name"]) && $a_filter["last_name"]) {
            $where[] = $ilDB->like("cp.last_name", "text", "%" . $a_filter["last_name"] . "%");
        }
        if (isset($a_filter["first_name"]) && $a_filter["first_name"]) {
            $where[] = $ilDB->like("cp.first_name", "text", "%" . $a_filter["first_name"] . "%");
        }

        // include archived?
        if (!$a_with_archived) {
            $where[] = "ct.archived < " . $ilDB->quote(1, "integer");
        }

        if (sizeof($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $set = $ilDB->query($sql);
        $certificate = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $full_nr = $rec["wmo_code_nr"] . "-" .
                str_pad($rec["nr"], 4, "0", STR_PAD_LEFT) . "-" .
                substr($rec["issued_on"], 0, 4);

            if (isset($a_filter["number"]) && $a_filter["number"] &&
                !stristr($full_nr, trim($a_filter["number"]))) {
                continue;
            }

            $certificate[$rec["id"]] = $rec;
            $certificate[$rec["id"]]["full_nr"] = $full_nr;
        }

        return $certificate;
    }

    /**
     * Lookup property
     *
     * @param integer $a_id certificate id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT " . $a_prop .
            " FROM adn_es_certificate" .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Get certificate of professional for an event
     *
     * @param integer $a_cp_id certificate id
     * @param integer $a_event_id event id
     * @return int certificate id
     */
    public static function getCertificateIdOfProfForEvent($a_cp_id, $a_event_id)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT id" .
            " FROM adn_es_certificate" .
            " WHERE cp_professional_id = " . $ilDB->quote($a_cp_id, "integer") .
            " AND ep_exam_id = " . $ilDB->quote($a_event_id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            return $rec["id"];
        }
        return 0;
    }

    /**
     * Determine next certificate number (middle part)
     *
     * @return int next number
     */
    public function determineNextNumber()
    {
        return self::_determineNextNumber(
            $this->getIssuedByWmo(),
            $this->getIssuedOn()
        );
    }

    /**
     * Determine next certificate number (middle part), static version
     *
     * @param int $a_issued_by_wmo wmo id
     *
     * @return int next number
     */
    public static function _determineNextNumber($a_issued_by_wmo, $a_issued_on)
    {
        global $ilDB;

        if (!$a_issued_on instanceof ilDate) {
            return 0;
        }

        $year = substr($a_issued_on->get(IL_CAL_DATE), 0, 4);
        $from = $year . "-01-01 00:00:00";
        $to = $year . "-12-31 23:59:59";

        // initial numbers
        if ($a_issued_by_wmo == 4 && $year == 2013) {
            return 306;
        }

        $set = $ilDB->query("SELECT nr n" .
            " FROM adn_es_certificate" .
            " WHERE issued_on >= " . $ilDB->quote($from, "timestamp") .
            " AND issued_on <= " . $ilDB->quote($to, "timestamp") .
            " AND issued_by_wmo = " . $ilDB->quote($a_issued_by_wmo, "integer") .
            " ORDER BY n ASC");
        $prev_nr = 0;

        while ($rec = $ilDB->fetchAssoc($set)) {
            if (($prev_nr + 1) < $rec["n"]) {
                return $prev_nr + 1;
            }

            $prev_nr = $rec["n"];
        }

        return $prev_nr + 1;
    }


    /**
     * Get ids of professionals with currently valid certificates
     *
     * @return array array of professional ids
     */
    public static function getAllProfessionalsWithValidCertificates()
    {
        global $ilDB;

        $date = new ilDate(time(), IL_CAL_UNIX);
        $date = $date->get(IL_CAL_DATE);
        
        $set = $ilDB->query("SELECT cp_professional_id" .
            " FROM adn_es_certificate" .
            " WHERE archived < " . $ilDB->quote(1, "integer") .
            " AND status < " . $ilDB->quote(1, "integer") .
            " AND valid_until >= " . $ilDB->quote($date, "timestamp"));
        $all = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $all[] = $row["cp_professional_id"];
        }
        return $all;
    }

    /**
     * Count certificates for professional
     *
     * @param int professional id
     * @return int number of certificates
     */
    public static function countCertificatesForProfessional($a_cp_id)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT count(id) as cnt" .
            " FROM adn_es_certificate" .
            " WHERE cp_professional_id = " . $ilDB->quote($a_cp_id, "integer"));
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["cnt"];
    }

    /**
     * Create extension
     */
    public function createExtension()
    {
        global $ilDB, $ilUser;
        
        // set the status of all other certificates of the user to invalid

        // #2606 - if certificate is archived because of extension the last
        // update has to reflect that
        $last_update = new ilDateTime(time(), IL_CAL_UNIX);
        $last_update = $last_update->get(IL_CAL_DATETIME, "", ilTimeZone::UTC);
            
        $ilDB->manipulate("UPDATE adn_es_certificate" .
            " SET status = " . $ilDB->quote(self::STATUS_INVALID, "integer") .
            ", last_update = " . $ilDB->quote($last_update, "timestamp") .
            ", last_update_user = " . $ilDB->quote($ilUser->getId(), "integer") .
            " WHERE cp_professional_id = " .
                $ilDB->quote($this->getCertifiedProfessionalId(), "integer") .
            " AND status = " . $ilDB->quote(self::STATUS_VALID, "integer"));
        

        // save current certificate as extension
        $this->setCardStatus(self::CARD_STATUS_UNDEFINED);
        $this->setId($ilDB->nextId("adn_es_certificate"));
        $id = $this->getId();
        $this->setExaminationId(null);
        $this->setIsExtension(true);

        // lock table to keep table numbering consistent
        $ilDB->lockTables(array(0 => array('name' => 'adn_es_certificate',
            'type' => ilDBConstants::LOCK_WRITE)));

        $this->setNumber($this->determineNextNumber());
        $this->setStatus(self::STATUS_VALID);

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);

        $ilDB->insert("adn_es_certificate", $fields);

        // unlock table
        $ilDB->unlockTables();

        parent::_save($id, "adn_es_certificate");
    }

    /**
     * Get extended certificates
     *
     * @param ilDateTime $a_from
     * @param ilDateTime $a_to
     * @param int $a_wmo
     * @param array $a_proofs
     * @return array array of numbers per type
     */
    public static function getExtensionStatistics(
        ilDateTime $a_from,
        ilDateTime $a_to,
        $a_wmo = null,
        array $a_proofs = null
    )
    {
        global $ilDB;

        $types = array(self::PROOF_TRAIN_DRY, self::PROOF_TRAIN_TANK , self::PROOF_TRAIN_COMBINED,
            self::PROOF_TRAIN_GAS, self::PROOF_TRAIN_CHEMICALS, self::PROOF_EXP_GAS,
            self::PROOF_EXP_CHEMICALS);

        $valid = $res = array();
        if ($a_proofs) {
            foreach ($a_proofs as $proof) {
                if (in_array($proof, $types)) {
                    $res[$proof] = 0;
                    $valid[] = $proof;
                }
            }
        }

        $sql = "SELECT id," . implode(",", $valid) .
            " FROM adn_es_certificate";

        $where = self::getStatisticsConditions($a_from, $a_to);
        
        // extensions only
        $where[] = "is_extension = " . $ilDB->quote(1, "integer");

        $tmp = array();
        foreach ($valid as $type) {
            $tmp[] = $type . " = " . $ilDB->quote(1, "integer");
        }
        $where[] = "(" . implode(" OR ", $tmp) . ")";

        if ((int) $a_wmo) {
            $where[] = "issued_by_wmo = " . $ilDB->quote((int) $a_wmo, "integer");
        }

        $sql .= " WHERE " . implode(" AND ", $where);
        
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            foreach ($valid as $type) {
                if ($row[$type]) {
                    $res[$type]++;
                }
            }
        }
        return $res;
    }

    /**
     * Get certificates statistics by type
     *
     * @param ilDateTime $a_from
     * @param ilDateTime $a_to
     * @param int $a_wmo
     * @param array $a_types
     * @return array array of number per type
     */
    public static function getTypeStatistics(
        ilDateTime $a_from,
        ilDateTime $a_to,
        $a_wmo = null,
        array $a_types = null
    )
    {
        global $ilDB;

        $types = array(self::GAS, self::CHEMICALS, self::DRY_MATERIAL, self::TANK);

        $valid = $res = array();
        if ($a_types) {
            foreach ($a_types as $type) {
                if (in_array($type, $types)) {
                    $res["type_" . $type] = 0;
                    $valid[] = "type_" . $type;
                }
            }
        }
        
        
        // gather duplicates
        $set = self::getDuplicateStatistics($a_from, $a_to, $a_wmo, true);
        $duplicate_ids = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            if (!in_array($row["id"], $duplicate_ids)) {
                $ids[] = $row["id"];
            }
        }
        unset($set);

        
        $sql = "SELECT id," . implode(",", $valid) .
            " FROM adn_es_certificate";

        $where = self::getStatisticsConditions($a_from, $a_to);

        $tmp = array();
        foreach ($valid as $type) {
            $tmp[] = $type . " = " . $ilDB->quote(1, "integer");
        }
        $where[] = "(" . implode(" OR ", $tmp) . ")";
        
        // no extensions
        $where[] = "is_extension = " . $ilDB->quote(0, "integer");

        if ((int) $a_wmo) {
            $where[] = "issued_by_wmo = " . $ilDB->quote((int) $a_wmo, "integer");
        }

        $sql .= " WHERE " . implode(" AND ", $where);

        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            // no duplicates
            if (in_array($row["id"], $duplicate_ids)) {
                continue;
            }
            
            foreach ($valid as $type) {
                if ($row[$type]) {
                    $res[$type]++;
                }
            }
        }
        return $res;
    }

    /**
     * Get certificates summary
     *
     * @param ilDateTime $a_from
     * @param ilDateTime $a_to
     * @param int $a_wmo
     * @return array array of numbers per year
     */
    public static function getTotalStatistics(ilDateTime $a_from, ilDateTime $a_to, $a_wmo = null)
    {
        global $ilDB;
        
        $sql = "SELECT id,issued_on" .
            " FROM adn_es_certificate";

        $where = self::getStatisticsConditions($a_from, $a_to);

        if ((int) $a_wmo) {
            $where[] = "issued_by_wmo = " . $ilDB->quote((int) $a_wmo, "integer");
        }

        $sql .= " WHERE " . implode(" AND ", $where);

        $set = $ilDB->query($sql);
        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[substr($row["issued_on"], 0, 4)]++;
        }
        
        
        // add duplicates
        $set = self::getDuplicateStatistics($a_from, $a_to, $a_wmo, true);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[substr($row["duplicate_issued_on"], 0, 4)]++;
        }
        unset($set);
        

        return $res;
    }

    /**
     * Get sql conditions for statistics
     *
     * @param ilDateTime $a_from
     * @param ilDateTime $a_to
     * @param bool $a_is_duplicate
     * @param bool $a_restrict_to_issued
     * @return array array of where condition strings
     */
    protected static function getStatisticsConditions(
        ilDateTime $a_from,
        ilDateTime $a_to,
        $a_is_duplicate = false,
        $a_restrict_to_issued = true,
        $a_tbl_prefix = ''
    )
    {
        global $ilDB;

        $a_tbl_prefix = ($a_tbl_prefix ? $a_tbl_prefix . '.' : '');
        
        // ilTimeZone::UTC does mess things up somehow
        $from = $a_from->get(IL_CAL_DATE);
        $to = $a_to->get(IL_CAL_DATE);

        if (!$a_is_duplicate) {
            $issued_field = $a_tbl_prefix . "issued_on";
        } else {
            $issued_field = $a_tbl_prefix . "duplicate_issued_on";
        }

        $where = array();
        $where[] = $ilDB->quote($to . " 23:59:59", "timestamp") . " >= " . $issued_field;
        $where[] = "((" . $ilDB->quote($from . " 00:00:00", "timestamp") . " <= " . $a_tbl_prefix . "last_update" .
            " AND " . $a_tbl_prefix . "status = " . $ilDB->quote(1, "integer") . ") OR (" .
            $ilDB->quote($from . " 00:00:00", "timestamp") . " <= " . $a_tbl_prefix . "valid_until AND " .
            $a_tbl_prefix . "status < " . $ilDB->quote(1, "integer") . "))";

        if ($a_restrict_to_issued) {
            $where[] = $issued_field . " >= " . $ilDB->quote($from . " 00:00:00", "timestamp");
            $where[] = $issued_field . " <= " . $ilDB->quote($to . " 23:59:59", "timestamp");
        }

        return $where;
    }
    
    /**
     * Create duplicate
     *
     * @param ilDateTime $a_date issued on
     */
    public function createDuplicate($a_date)
    {
        global $ilDB, $ilUser;

        $ilDB->manipulate("INSERT INTO adn_es_duplicate " .
            "(es_certificate_id, duplicate_issued_on) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($a_date->get(IL_CAL_DATE), "timestamp") . ")");

        $this->update();
    }

    /**
     * Get duplicate statistics
     *
     * @param ilDateTime $a_from
     * @param ilDateTime $a_to
     * @param int $a_wmo
     * @param bool $a_return_resultset
     * @return int number of duplicates
     */
    public static function getDuplicateStatistics(
        ilDateTime $a_from,
        ilDateTime $a_to,
        $a_wmo = null,
        $a_return_resultset = false
    )
    {
        global $ilDB;

        $sql = "SELECT id,duplicate_issued_on" .
            " FROM adn_es_certificate" .
            " JOIN adn_es_duplicate ON (adn_es_certificate.id = adn_es_duplicate.es_certificate_id)";

        $where = self::getStatisticsConditions($a_from, $a_to, true);

        if ((int) $a_wmo) {
            $where[] = "issued_by_wmo = " . $ilDB->quote((int) $a_wmo, "integer");
        }

        $sql .= " WHERE " . implode(" AND ", $where);

        $set = $ilDB->query($sql);
        
        if (!$a_return_resultset) {
            return $ilDB->numRows($set);
        } else {
            return $set;
        }
    }


    /**
     * Get data for professional directory
     *
     * @param ilDateTime $a_from
     * @param ilDateTime $a_to
     * @param int $a_wmo
     * @return array array of professionals data records
     */
    public static function getProfessionalDirectory(ilDateTime $a_from, ilDateTime $a_to, $a_wmo)
    {
        global $ilDB;
        
        $sql = "SELECT esc.id,esc.nr,cp_professional_id,signed_by,type_tank,type_gas,type_chem,type_dm," .
            "esc.issued_on,esc.valid_until,code_nr,wmo.id wmo_id " .
            "FROM adn_es_certificate esc " .
            'JOIN adn_md_wmo wmo ON issued_by_wmo = wmo.id ';

        $where = self::getStatisticsConditions($a_from, $a_to, false, false, 'esc');
        if ((int) $a_wmo) {
            $where[] = "issued_by_wmo = " . $ilDB->quote((int) $a_wmo, "integer");
        }
        $sql .= " WHERE " . implode(" AND ", $where);

        // this way only the "last" certificate will show up
        $sql .= " ORDER BY esc.valid_until";

        $set = $ilDB->query($sql);
        $res = $ids = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["cp_professional_id"]] = $row;
            $ids[] = $row["cp_professional_id"];
        }

        if ($ids) {
            $set = $ilDB->query("SELECT id,last_name,first_name,birthdate,citizenship" .
                " FROM adn_cp_professional WHERE " . $ilDB->in("id", $ids, false, "integer"));
            while ($row = $ilDB->fetchAssoc($set)) {
                $res[$row["id"]] = array_merge($res[$row["id"]], $row);
            }
        }

        $result = array();
        foreach ($res as $id => $row) {
            $result[$id] = $row;
            $result[$id]['full_nr'] =
                $row['code_nr'] . '-' .
                str_pad($row['nr'], 4, "0", STR_PAD_LEFT) . '-' .
                substr($row['issued_on'], 0, 4);
        }
        return $result;
    }
    
    /**
     * Check if cert is duplicate
     *
     * @param int $a_cert_id
     * @return boolean has duplicates true/false
     */
    public static function isDuplicate($a_cert_id)
    {
        global $ilDB;
        
        $query = "SELECT COUNT(*) num" .
            " FROM adn_es_duplicate" .
            " WHERE es_certificate_id = " . $ilDB->quote($a_cert_id, "integer");
        $res = $ilDB->query($query);
        $row = $res->fetchRow(\ilDBConstants::FETCHMODE_ASSOC);
        return $row["num"] > 0 ? true : false;
    }

    /**
     * Check whether certificate is valid (status and valid until date)
     *
     * @return boolean valid true/false
     */
    public function isValid()
    {
        if ($this->getValidUntil()->get(IL_CAL_DATE) < substr(ilUtil::now(), 0, 10)) {
            return false;
        }
        if ($this->getStatus() == self::STATUS_INVALID) {
            return false;
        }
        return true;
    }

    /**
     * Get all duplicate dates (if any)
     *
     * @return array
     */
    public function getDuplicateDates()
    {
        global $ilDB;

        $set = $ilDB->query("SELECT duplicate_issued_on" .
            " FROM  adn_es_duplicate" .
            " WHERE es_certificate_id = " . $ilDB->quote($this->getId(), "integer") .
            " ORDER BY duplicate_issued_on");
        $all = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $all[] = new ilDate($row["duplicate_issued_on"], IL_CAL_DATE);
        }
        return $all;
    }

    /**
     * Get original issued on or latest duplicate
     *
     * @return ilDate
     */
    public function getLatestIssuedOn()
    {
        $duplicates = $this->getDuplicateDates();
        if ($duplicates) {
            return array_pop($duplicates);
        } else {
            return $this->getIssuedOn();
        }
    }

    public function getCardStatus() : int
    {
        return $this->card_status;
    }

    public function setCardStatus(int $card_status) : void
    {
        $this->card_status = $card_status;
    }
}
