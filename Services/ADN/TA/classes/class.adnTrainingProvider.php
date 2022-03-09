<?php
/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN training provider application class
 *
 * @author Alex Killing <killing@leifos.de>
 * @version $Id: class.adnTrainingProvider.php 32529 2012-01-05 10:31:40Z jluetzen $
 *
 * @ingroup ServicesADN
 */
class adnTrainingProvider extends adnDBBase
{
    protected $id; // [int]
    protected $name; // [string]
    protected $contact; // [string]
    protected $street; // [string]
    protected $street_no; // [string]
    protected $po_box; // [string]
    protected $zip; // [string]
    protected $city; // [string]
    protected $alt_street; // [string]
    protected $alt_street_no; // [string]
    protected $alt_po_box; // [string]
    protected $alt_zip; // [string]
    protected $alt_city; // [string]
    protected $phone; // [string]
    protected $fax; // [string]
    protected $email; // [string]
    protected $training_types; // [array]

    /**
     * Constructor
     *
     * @param int $a_id instance id
     */
    public function __construct($a_id = 0)
    {
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }

    /**
     * Set id
     *
     * @param	integer	$a_val	id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }

    /**
     * Get id
     *
     * @return	integer	id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param	string	$a_val name
     */
    public function setName($a_val)
    {
        $this->name = $a_val;
    }

    /**
     * Get name
     *
     * @return	string	name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set contact
     *
     * @param	string	$a_val name
     */
    public function setContact($a_val)
    {
        $this->contact = (string) $a_val;
    }

    /**
     * Get contact
     *
     * @return	string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set street
     *
     * @param	string	$a_val street
     */
    public function setStreet($a_val)
    {
        $this->street = (string) $a_val;
    }

    /**
     * Get street
     *
     * @return	string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set street number
     *
     * @param	string	$a_val street number
     */
    public function setStreetNumber($a_val)
    {
        $this->street_number = (string) $a_val;
    }

    /**
     * Get street number
     *
     * @return	string
     */
    public function getStreetNumber()
    {
        return $this->street_number;
    }

    /**
     * Set po box
     *
     * @param	string	$a_val po box
     */
    public function setPoBox($a_val)
    {
        $this->po_box = (string) $a_val;
    }

    /**
     * Get po box
     *
     * @return	string
     */
    public function getPoBox()
    {
        return $this->po_box;
    }

    /**
     * Set postal code
     *
     * @param	string	$a_val  postal code
     */
    public function setZip($a_val)
    {
        $this->zip = (string) $a_val;
    }

    /**
     * Get postal code
     *
     * @return	string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set city
     *
     * @param	string	$a_val city
     */
    public function setCity($a_val)
    {
        $this->city = (string) $a_val;
    }

    /**
     * Get city
     *
     * @return	string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set phone
     *
     * @param	string	$a_val phone
     */
    public function setPhone($a_val)
    {
        $this->phone = (string) $a_val;
    }

    /**
     * Get phone
     *
     * @return	string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set fax
     *
     * @param	string	$a_val fax
     */
    public function setFax($a_val)
    {
        $this->fax = (string) $a_val;
    }

    /**
     * Get fax
     *
     * @return	string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set email
     *
     * @param	string	$a_val email
     */
    public function setEmail($a_val)
    {
        $this->email = (string) $a_val;
    }

    /**
     * Get email
     *
     * @return	string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set alternative street
     *
     * @param	string	$a_val street
     */
    public function setAlternativeStreet($a_val)
    {
        $this->alt_street = (string) $a_val;
    }

    /**
     * Get alternative street
     *
     * @return	string
     */
    public function getAlternativeStreet()
    {
        return $this->alt_street;
    }

    /**
     * Set alternative street number
     *
     * @param	string	$a_val street number
     */
    public function setAlternativeStreetNumber($a_val)
    {
        $this->alt_street_number = (string) $a_val;
    }

    /**
     * Get alternative street number
     *
     * @return	string
     */
    public function getAlternativeStreetNumber()
    {
        return $this->alt_street_number;
    }

    /**
     * Set alternative po box
     *
     * @param	string	$a_val po box
     */
    public function setAlternativePoBox($a_val)
    {
        $this->alt_po_box = (string) $a_val;
    }

    /**
     * Get alternative po box
     *
     * @return	string
     */
    public function getAlternativePoBox()
    {
        return $this->alt_po_box;
    }

    /**
     * Set alternative postal code
     *
     * @param	string	$a_val  postal code
     */
    public function setAlternativeZip($a_val)
    {
        $this->alt_zip = (string) $a_val;
    }

    /**
     * Get alternative postal code
     *
     * @return	string
     */
    public function getAlternativeZip()
    {
        return $this->alt_zip;
    }

    /**
     * Set alternative city
     *
     * @param	string	$a_val city
     */
    public function setAlternativeCity($a_val)
    {
        $this->alt_city = (string) $a_val;
    }

    /**
     * Get alternative city
     *
     * @return	string
     */
    public function getAlternativeCity()
    {
        return $this->alt_city;
    }

    /**
     * Set approved training types
     *
     * array(TYPE => DATE OF APPROVAL)
     *
     * @param	array	$a_val training types
     */
    public function setTypesOfTraining(array $a_val)
    {
        $this->training_types = $a_val;
    }

    /**
     * Get approved training types
     *
     * @return	array
     */
    public function getTypesOfTraining()
    {
        return array_keys($this->training_types);
    }

    /**
     * Is given training type approved
     *
     * @return	ilDatetime
     */
    public function IsTrainingTypeApproved($type)
    {
        if (in_array($type, array_keys($this->training_types))) {
            return $this->training_types[$type];
        }
    }

    /**
     * Read instance data from database
     */
    public function read()
    {
        global $ilDB;

        $id = $this->getId();
        if (!$id) {
            return;
        }

        $set = $ilDB->query("SELECT * FROM adn_ta_provider" .
            " WHERE id = " . $ilDB->quote($id, "integer"));
        if ($rec = $ilDB->fetchAssoc($set)) {
            $this->setName($rec["name"]);
            $this->setContact($rec["contact"]);
            $this->setStreet($rec["street"]);
            $this->setStreetNumber($rec["street_no"]);
            $this->setPoBox($rec["po_box"]);
            $this->setZip($rec["postal_code"]);
            $this->setCity($rec["city"]);
            $this->setPhone($rec["phone"]);
            $this->setFax($rec["fax"]);
            $this->setEmail($rec["email"]);
            $this->setAlternativeStreet($rec["pa_street"]);
            $this->setAlternativeStreetNumber($rec["pa_street_no"]);
            $this->setAlternativePoBox($rec["pa_po_box"]);
            $this->setAlternativeZip($rec["pa_postal_code"]);
            $this->setAlternativeCity($rec["pa_city"]);
        }

        // get training types
        $set = $ilDB->query("SELECT *" .
            " FROM adn_ta_provider_ttype" .
            " WHERE ta_provider_id = " . $id);
        $types = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $date = new ilDate($rec["approval_date"], IL_CAL_DATE, ilTimeZone::UTC);
            $types[$rec["training_type"]] = $date;
        }
        $this->setTypesOfTraining($types);

        parent::_read($id, "adn_ta_provider");
    }

    /**
     * Convert properties to DB fields
     *
     * @return array (name, contact, street, street_no, po_box, postal_code, city, pa_street,
     * pa_street_no, pa_po_box, pa_postal_code, pa_city, phone, fax, email)
     */
    protected function propertiesToFields()
    {
        $fields = array("name" => array("text", $this->getName()),
            "contact" => array("text", $this->getContact()),
            "street" => array("text", $this->getStreet()),
            "street_no" => array("text", $this->getStreetNumber()),
            "po_box" => array("text", $this->getPoBox()),
            "postal_code" => array("text", $this->getZip()),
            "city" => array("text", $this->getCity()),
            "pa_street" => array("text", $this->getAlternativeStreet()),
            "pa_street_no" => array("text", $this->getAlternativeStreetNumber()),
            "pa_po_box" => array("text", $this->getAlternativePoBox()),
            "pa_postal_code" => array("text", $this->getAlternativeZip()),
            "pa_city" => array("text", $this->getAlternativeCity()),
            "phone" => array("text", $this->getPhone()),
            "fax" => array("text", $this->getFax()),
            "email" => array("text", $this->getEmail())
            );

        return $fields;
    }

    /**
     * save approved training types (in sub-table)
     */
    public function saveTrainingTypes()
    {
        global $ilDB;

        $id = $this->getId();
        if ($id) {
            $ilDB->manipulate("DELETE FROM adn_ta_provider_ttype" .
                " WHERE ta_provider_id = " . $ilDB->quote($id, "integer"));

            foreach ($this->training_types as $type => $date) {
                $fields = array("ta_provider_id" => array("integer", $id),
                    "training_type" => array("text", $type),
                    "approval_date" => array("timestamp", $date->get(IL_CAL_DATETIME, "", ilTimeZone::UTC)));

                $ilDB->insert("adn_ta_provider_ttype", $fields);
            }
        }
    }

    /**
     * Create training provider
     *
     * @return int new id
     */
    public function save()
    {
        global $ilDB, $ilUser;

        // sequence
        $this->setId($ilDB->nextId("adn_ta_provider"));
        $id = $this->getId();
        if (!$id) {
            return;
        }

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);

        $ilDB->insert("adn_ta_provider", $fields);

        parent::_save($id, "adn_ta_provider");

        return $id;
    }

    /**
     * Update training provider
     *
     * @return bool
     */
    public function update()
    {
        global $ilDB, $ilUser;

        $id = $this->getId();
        if (!$id) {
            return;
        }

        $fields = $this->propertiesToFields();

        $ilDB->update("adn_ta_provider", $fields, array("id" => array("integer", $id)));

        parent::_update($id, "adn_ta_provider");

        return true;
    }

    /**
     * Delete training provider
     *
     * @return bool
     */
    public function delete()
    {
        global $ilDB;

        $id = $this->getId();
        if ($id) {
            // delete the events which have not taken place yet
            include_once "Services/ADN/TA/classes/class.adnTrainingEvent.php";
            $all = adnTrainingEvent::getAllTrainingEvents($id);
            if (is_array($all) && sizeof($all)) {
                foreach ($all as $event_set) {
                    $event = new adnTrainingEvent($event_set["id"]);
                    $event->delete();
                }
            }
                                        
            // delete/archive facilities (depends on events in the past)
            include_once "Services/ADN/TA/classes/class.adnTrainingFacility.php";
            $all = adnTrainingFacility::getTrainingFacilitiesSelect($id);
            if (is_array($all) && sizeof($all)) {
                foreach ($all as $facility_id => $name) {
                    $facility = new adnTrainingFacility($facility_id);
                    $facility->delete();
                }
            }
            
            // U.SV.1.4: check if there are training events which have already taken place
            $all = adnTrainingEvent::getAllTrainingEvents($id, false, null, true);
            if (is_array($all) && sizeof($all)) {
                $this->setArchived(true);
                // cr-008 start
                $this->setEmail("xxx");
                $this->setContact("xxx");
                // cr-008 end
                $this->update();
                
                // archive the events which have taken place
                foreach ($all as $event_set) {
                    $event = new adnTrainingEvent($event_set["id"]);
                    $event->delete();
                }
                
                // archive instructors
                include_once "Services/ADN/TA/classes/class.adnInstructor.php";
                $all = adnInstructor::getInstructorsSelect($id, true);
                if (is_array($all) && sizeof($all)) {
                    foreach ($all as $instructor_id => $name) {
                        $instructor = new adnInstructor($instructor_id);
                        // cr-0008 start
                        //$instructor->setArchived(true);
                        //$instructor->update();
                        $instructor->archive();
                        // cr-0008 end
                    }
                }
            } else {
                // delete instructors
                include_once "Services/ADN/TA/classes/class.adnInstructor.php";
                $all = adnInstructor::getInstructorsSelect($id, true);
                if (is_array($all) && sizeof($all)) {
                    foreach ($all as $instructor_id => $name) {
                        $instructor = new adnInstructor($instructor_id);
                        $instructor->delete();
                    }
                }
                
                $ilDB->manipulate("DELETE FROM adn_ta_provider_ttype" .
                    " WHERE ta_provider_id = " . $ilDB->quote($id, "integer"));
                $ilDB->manipulate("DELETE FROM adn_ta_provider" .
                    " WHERE id = " . $ilDB->quote($id, "integer"));
                $this->setId(null);
            }

            return true;
        }
        
        return false;
    }

    /**
     * Get all training providers
     *
     * @param bool $a_with_archived
     * @return array training provider data
     */
    public static function getAllTrainingProviders($a_with_archived = false)
    {
        global $ilDB;

        $sql = "SELECT id,name FROM adn_ta_provider";

        if (!$a_with_archived) {
            $sql .= " WHERE archived < " . $ilDB->quote(1, "integer");
        }

        $sql .= " ORDER BY name";

        $set = $ilDB->query($sql);
        $training_provider = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ids[] = $rec["id"];
            $training_provider[$rec["id"]] = $rec;
        }

        // add training types
        $set = $ilDB->query("SELECT ta_provider_id,training_type" .
            " FROM adn_ta_provider_ttype" .
            " WHERE " . $ilDB->in("ta_provider_id", $ids, false, "integer"));
        while ($rec = $ilDB->fetchAssoc($set)) {
            $training_provider[$rec["ta_provider_id"]]["app_types"][] = $rec["training_type"];
        }

        return $training_provider;
    }

    /**
     * Get training provider ids and names
     *
     * @return array (id => caption)
     */
    public static function getTrainingProvidersSelect()
    {
        global $ilDB;

        $set = $ilDB->query("SELECT id,name" .
            " FROM adn_ta_provider" .
            " WHERE archived < " . $ilDB->quote(1, "integer") .
            " ORDER BY name");
        $training_provider = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $training_provider[$rec["id"]] = $rec["name"];
        }

        return $training_provider;
    }

    /**
     * Lookup property
     *
     * @param integer $a_id training provider id
     * @param string $a_prop property
     * @return mixed property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT " . $a_prop .
            " FROM adn_ta_provider" .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Lookup name
     *
     * @param integer training provider id
     * @return string name
     */
    public static function lookupName($a_id)
    {
        return adnTrainingProvider::lookupProperty($a_id, "name");
    }
}
