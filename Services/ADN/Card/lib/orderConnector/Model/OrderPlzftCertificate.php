<?php
/**
 * OrderPlzftCertificate
 *
 * PHP version 7.4
 *
 * @category Class
 * @package  Plasticard\PLZFT
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * ADN/leifos - OpenAPI 3.0
 *
 * Definition of the REST API for ADN/leifos
 *
 * The version of the OpenAPI document: 0.1.0
 * Contact: developer@plasticard.de
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 6.3.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Plasticard\PLZFT\Model;

use \ArrayAccess;
use \Plasticard\PLZFT\ObjectSerializer;

/**
 * OrderPlzftCertificate Class Doc Comment
 *
 * @category Class
 * @package  Plasticard\PLZFT
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class OrderPlzftCertificate implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'Order_plzft_Certificate';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'plzft_certificate_id' => 'string',
        'plzft_certificate_number' => 'string',
        'plzft_lastname' => 'string',
        'plzft_firstname' => 'string',
        'plzft_nationality' => 'string',
        'plzft_birthday' => '\DateTime',
        'plzft_issued_by' => 'string',
        'plzft_valid_until' => '\DateTime',
        'plzft_certificate_types' => '\Plasticard\PLZFT\Model\OrderPlzftCertificatePlzftCertificateTypes',
        'plzft_photo' => 'string',
        'plzft_postal_address' => '\Plasticard\PLZFT\Model\OrderPlzftCertificatePlzftPostalAddress',
        'plzft_return_address' => '\Plasticard\PLZFT\Model\OrderPlzftCertificatePlzftReturnAddress'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'plzft_certificate_id' => null,
        'plzft_certificate_number' => null,
        'plzft_lastname' => null,
        'plzft_firstname' => 'string',
        'plzft_nationality' => null,
        'plzft_birthday' => 'date',
        'plzft_issued_by' => null,
        'plzft_valid_until' => 'date',
        'plzft_certificate_types' => null,
        'plzft_photo' => null,
        'plzft_postal_address' => null,
        'plzft_return_address' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'plzft_certificate_id' => false,
		'plzft_certificate_number' => false,
		'plzft_lastname' => false,
		'plzft_firstname' => false,
		'plzft_nationality' => false,
		'plzft_birthday' => false,
		'plzft_issued_by' => false,
		'plzft_valid_until' => false,
		'plzft_certificate_types' => false,
		'plzft_photo' => false,
		'plzft_postal_address' => false,
		'plzft_return_address' => false
    ];

    /**
      * If a nullable field gets set to null, insert it here
      *
      * @var boolean[]
      */
    protected array $openAPINullablesSetToNull = [];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of nullable properties
     *
     * @return array
     */
    protected static function openAPINullables(): array
    {
        return self::$openAPINullables;
    }

    /**
     * Array of nullable field names deliberately set to null
     *
     * @return boolean[]
     */
    private function getOpenAPINullablesSetToNull(): array
    {
        return $this->openAPINullablesSetToNull;
    }

    /**
     * Setter - Array of nullable field names deliberately set to null
     *
     * @param boolean[] $openAPINullablesSetToNull
     */
    private function setOpenAPINullablesSetToNull(array $openAPINullablesSetToNull): void
    {
        $this->openAPINullablesSetToNull = $openAPINullablesSetToNull;
    }

    /**
     * Checks if a property is nullable
     *
     * @param string $property
     * @return bool
     */
    public static function isNullable(string $property): bool
    {
        return self::openAPINullables()[$property] ?? false;
    }

    /**
     * Checks if a nullable property is set to null.
     *
     * @param string $property
     * @return bool
     */
    public function isNullableSetToNull(string $property): bool
    {
        return in_array($property, $this->getOpenAPINullablesSetToNull(), true);
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'plzft_certificate_id' => 'plzft:CertificateId',
        'plzft_certificate_number' => 'plzft:CertificateNumber',
        'plzft_lastname' => 'plzft:Lastname',
        'plzft_firstname' => 'plzft:Firstname',
        'plzft_nationality' => 'plzft:Nationality',
        'plzft_birthday' => 'plzft:Birthday',
        'plzft_issued_by' => 'plzft:IssuedBy',
        'plzft_valid_until' => 'plzft:ValidUntil',
        'plzft_certificate_types' => 'plzft:CertificateTypes',
        'plzft_photo' => 'plzft:Photo',
        'plzft_postal_address' => 'plzft:PostalAddress',
        'plzft_return_address' => 'plzft:ReturnAddress'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'plzft_certificate_id' => 'setPlzftCertificateId',
        'plzft_certificate_number' => 'setPlzftCertificateNumber',
        'plzft_lastname' => 'setPlzftLastname',
        'plzft_firstname' => 'setPlzftFirstname',
        'plzft_nationality' => 'setPlzftNationality',
        'plzft_birthday' => 'setPlzftBirthday',
        'plzft_issued_by' => 'setPlzftIssuedBy',
        'plzft_valid_until' => 'setPlzftValidUntil',
        'plzft_certificate_types' => 'setPlzftCertificateTypes',
        'plzft_photo' => 'setPlzftPhoto',
        'plzft_postal_address' => 'setPlzftPostalAddress',
        'plzft_return_address' => 'setPlzftReturnAddress'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'plzft_certificate_id' => 'getPlzftCertificateId',
        'plzft_certificate_number' => 'getPlzftCertificateNumber',
        'plzft_lastname' => 'getPlzftLastname',
        'plzft_firstname' => 'getPlzftFirstname',
        'plzft_nationality' => 'getPlzftNationality',
        'plzft_birthday' => 'getPlzftBirthday',
        'plzft_issued_by' => 'getPlzftIssuedBy',
        'plzft_valid_until' => 'getPlzftValidUntil',
        'plzft_certificate_types' => 'getPlzftCertificateTypes',
        'plzft_photo' => 'getPlzftPhoto',
        'plzft_postal_address' => 'getPlzftPostalAddress',
        'plzft_return_address' => 'getPlzftReturnAddress'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }


    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->setIfExists('plzft_certificate_id', $data ?? [], null);
        $this->setIfExists('plzft_certificate_number', $data ?? [], null);
        $this->setIfExists('plzft_lastname', $data ?? [], null);
        $this->setIfExists('plzft_firstname', $data ?? [], null);
        $this->setIfExists('plzft_nationality', $data ?? [], null);
        $this->setIfExists('plzft_birthday', $data ?? [], null);
        $this->setIfExists('plzft_issued_by', $data ?? [], null);
        $this->setIfExists('plzft_valid_until', $data ?? [], null);
        $this->setIfExists('plzft_certificate_types', $data ?? [], null);
        $this->setIfExists('plzft_photo', $data ?? [], null);
        $this->setIfExists('plzft_postal_address', $data ?? [], null);
        $this->setIfExists('plzft_return_address', $data ?? [], null);
    }

    /**
    * Sets $this->container[$variableName] to the given data or to the given default Value; if $variableName
    * is nullable and its value is set to null in the $fields array, then mark it as "set to null" in the
    * $this->openAPINullablesSetToNull array
    *
    * @param string $variableName
    * @param array  $fields
    * @param mixed  $defaultValue
    */
    private function setIfExists(string $variableName, array $fields, $defaultValue): void
    {
        if (self::isNullable($variableName) && array_key_exists($variableName, $fields) && is_null($fields[$variableName])) {
            $this->openAPINullablesSetToNull[] = $variableName;
        }

        $this->container[$variableName] = $fields[$variableName] ?? $defaultValue;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets plzft_certificate_id
     *
     * @return string|null
     */
    public function getPlzftCertificateId()
    {
        return $this->container['plzft_certificate_id'];
    }

    /**
     * Sets plzft_certificate_id
     *
     * @param string|null $plzft_certificate_id plzft_certificate_id
     *
     * @return self
     */
    public function setPlzftCertificateId($plzft_certificate_id)
    {
        if (is_null($plzft_certificate_id)) {
            throw new \InvalidArgumentException('non-nullable plzft_certificate_id cannot be null');
        }
        $this->container['plzft_certificate_id'] = $plzft_certificate_id;

        return $this;
    }

    /**
     * Gets plzft_certificate_number
     *
     * @return string|null
     */
    public function getPlzftCertificateNumber()
    {
        return $this->container['plzft_certificate_number'];
    }

    /**
     * Sets plzft_certificate_number
     *
     * @param string|null $plzft_certificate_number plzft_certificate_number
     *
     * @return self
     */
    public function setPlzftCertificateNumber($plzft_certificate_number)
    {
        if (is_null($plzft_certificate_number)) {
            throw new \InvalidArgumentException('non-nullable plzft_certificate_number cannot be null');
        }
        $this->container['plzft_certificate_number'] = $plzft_certificate_number;

        return $this;
    }

    /**
     * Gets plzft_lastname
     *
     * @return string|null
     */
    public function getPlzftLastname()
    {
        return $this->container['plzft_lastname'];
    }

    /**
     * Sets plzft_lastname
     *
     * @param string|null $plzft_lastname plzft_lastname
     *
     * @return self
     */
    public function setPlzftLastname($plzft_lastname)
    {
        if (is_null($plzft_lastname)) {
            throw new \InvalidArgumentException('non-nullable plzft_lastname cannot be null');
        }
        $this->container['plzft_lastname'] = $plzft_lastname;

        return $this;
    }

    /**
     * Gets plzft_firstname
     *
     * @return string|null
     */
    public function getPlzftFirstname()
    {
        return $this->container['plzft_firstname'];
    }

    /**
     * Sets plzft_firstname
     *
     * @param string|null $plzft_firstname plzft_firstname
     *
     * @return self
     */
    public function setPlzftFirstname($plzft_firstname)
    {
        if (is_null($plzft_firstname)) {
            throw new \InvalidArgumentException('non-nullable plzft_firstname cannot be null');
        }
        $this->container['plzft_firstname'] = $plzft_firstname;

        return $this;
    }

    /**
     * Gets plzft_nationality
     *
     * @return string|null
     */
    public function getPlzftNationality()
    {
        return $this->container['plzft_nationality'];
    }

    /**
     * Sets plzft_nationality
     *
     * @param string|null $plzft_nationality plzft_nationality
     *
     * @return self
     */
    public function setPlzftNationality($plzft_nationality)
    {
        if (is_null($plzft_nationality)) {
            throw new \InvalidArgumentException('non-nullable plzft_nationality cannot be null');
        }
        $this->container['plzft_nationality'] = $plzft_nationality;

        return $this;
    }

    /**
     * Gets plzft_birthday
     *
     * @return \DateTime|null
     */
    public function getPlzftBirthday()
    {
        return $this->container['plzft_birthday'];
    }

    /**
     * Sets plzft_birthday
     *
     * @param \DateTime|null $plzft_birthday plzft_birthday
     *
     * @return self
     */
    public function setPlzftBirthday($plzft_birthday)
    {
        if (is_null($plzft_birthday)) {
            throw new \InvalidArgumentException('non-nullable plzft_birthday cannot be null');
        }
        $this->container['plzft_birthday'] = $plzft_birthday;

        return $this;
    }

    /**
     * Gets plzft_issued_by
     *
     * @return string|null
     */
    public function getPlzftIssuedBy()
    {
        return $this->container['plzft_issued_by'];
    }

    /**
     * Sets plzft_issued_by
     *
     * @param string|null $plzft_issued_by plzft_issued_by
     *
     * @return self
     */
    public function setPlzftIssuedBy($plzft_issued_by)
    {
        if (is_null($plzft_issued_by)) {
            throw new \InvalidArgumentException('non-nullable plzft_issued_by cannot be null');
        }
        $this->container['plzft_issued_by'] = $plzft_issued_by;

        return $this;
    }

    /**
     * Gets plzft_valid_until
     *
     * @return \DateTime|null
     */
    public function getPlzftValidUntil()
    {
        return $this->container['plzft_valid_until'];
    }

    /**
     * Sets plzft_valid_until
     *
     * @param \DateTime|null $plzft_valid_until plzft_valid_until
     *
     * @return self
     */
    public function setPlzftValidUntil($plzft_valid_until)
    {
        if (is_null($plzft_valid_until)) {
            throw new \InvalidArgumentException('non-nullable plzft_valid_until cannot be null');
        }
        $this->container['plzft_valid_until'] = $plzft_valid_until;

        return $this;
    }

    /**
     * Gets plzft_certificate_types
     *
     * @return \Plasticard\PLZFT\Model\OrderPlzftCertificatePlzftCertificateTypes|null
     */
    public function getPlzftCertificateTypes()
    {
        return $this->container['plzft_certificate_types'];
    }

    /**
     * Sets plzft_certificate_types
     *
     * @param \Plasticard\PLZFT\Model\OrderPlzftCertificatePlzftCertificateTypes|null $plzft_certificate_types plzft_certificate_types
     *
     * @return self
     */
    public function setPlzftCertificateTypes($plzft_certificate_types)
    {
        if (is_null($plzft_certificate_types)) {
            throw new \InvalidArgumentException('non-nullable plzft_certificate_types cannot be null');
        }
        $this->container['plzft_certificate_types'] = $plzft_certificate_types;

        return $this;
    }

    /**
     * Gets plzft_photo
     *
     * @return string|null
     */
    public function getPlzftPhoto()
    {
        return $this->container['plzft_photo'];
    }

    /**
     * Sets plzft_photo
     *
     * @param string|null $plzft_photo plzft_photo
     *
     * @return self
     */
    public function setPlzftPhoto($plzft_photo)
    {
        if (is_null($plzft_photo)) {
            throw new \InvalidArgumentException('non-nullable plzft_photo cannot be null');
        }
        $this->container['plzft_photo'] = $plzft_photo;

        return $this;
    }

    /**
     * Gets plzft_postal_address
     *
     * @return \Plasticard\PLZFT\Model\OrderPlzftCertificatePlzftPostalAddress|null
     */
    public function getPlzftPostalAddress()
    {
        return $this->container['plzft_postal_address'];
    }

    /**
     * Sets plzft_postal_address
     *
     * @param \Plasticard\PLZFT\Model\OrderPlzftCertificatePlzftPostalAddress|null $plzft_postal_address plzft_postal_address
     *
     * @return self
     */
    public function setPlzftPostalAddress($plzft_postal_address)
    {
        if (is_null($plzft_postal_address)) {
            throw new \InvalidArgumentException('non-nullable plzft_postal_address cannot be null');
        }
        $this->container['plzft_postal_address'] = $plzft_postal_address;

        return $this;
    }

    /**
     * Gets plzft_return_address
     *
     * @return \Plasticard\PLZFT\Model\OrderPlzftCertificatePlzftReturnAddress|null
     */
    public function getPlzftReturnAddress()
    {
        return $this->container['plzft_return_address'];
    }

    /**
     * Sets plzft_return_address
     *
     * @param \Plasticard\PLZFT\Model\OrderPlzftCertificatePlzftReturnAddress|null $plzft_return_address plzft_return_address
     *
     * @return self
     */
    public function setPlzftReturnAddress($plzft_return_address)
    {
        if (is_null($plzft_return_address)) {
            throw new \InvalidArgumentException('non-nullable plzft_return_address cannot be null');
        }
        $this->container['plzft_return_address'] = $plzft_return_address;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
       return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


