/* Copyright (c) 2010 Leifos, GPL, see docs/LICENSE */

/**
 * ADN ${txt_class_name} application class
 *
 * @author ${author} <${email}>
 * @version $$Id$$
 *
 * @ingroup ServicesADN
 */
class adn${ClassName}
{
	// id
	protected $$id;

	// create date
	protected $$create_date;

	// create user
	protected $$create_user;

	// last update
	protected $$last_update;

	// last update user
	protected $$last_update_user;

	// ${txt_property_name}
	protected $$${property_var};

	/**
	 * Constructor
	 *
	 * @param	integer	$$a_id	${txt_class_name} id
	 */
	function __construct($$a_id = 0)
	{
		if ($$a_id > 0)
		{
			$$this->setId($$a_id);
			$$this->read();
		}
	}

	/**
	 * Set id
	 *
	 * @param	integer	$$a_val	id
	 */
	public function setId($$a_val)
	{
		$$this->id = $$a_val;
	}

	/**
	 * Get id
	 *
	 * @return	integer	id
	 */
	public function getId()
	{
		return $$this->id;
	}

	/**
	 * Set create date
	 *
	 * @param	timestamp	$$a_val	create date
	 */
	public function setCreateDate($$a_val)
	{
		$$this->create_date = $$a_val;
	}

	/**
	 * Get create date
	 *
	 * @return	timestamp	create date
	 */
	public function getCreateDate()
	{
		return $$this->create_date;
	}

	/**
	 * Set create user
	 *
	 * @param	integer	$$a_val	create user
	 */
	public function setCreateUser($$a_val)
	{
		$$this->create_user = $$a_val;
	}

	/**
	 * Get create user
	 *
	 * @return	integer	create user
	 */
	public function getCreateUser()
	{
		return $$this->create_user;
	}

	/**
	 * Set last update
	 *
	 * @param	timestamp	$$a_val	last update
	 */
	public function setLastUpdate($$a_val)
	{
		$$this->last_update = $$a_val;
	}

	/**
	 * Get last update
	 *
	 * @return	timestamp	last update
	 */
	public function getLastUpdate()
	{
		return $$this->last_update;
	}

	/**
	 * Set last update user
	 *
	 * @param	integer	$$a_val	last update user
	 */
	public function setLastUpdateUser($$a_val)
	{
		$$this->last_update_user = $$a_val;
	}

	/**
	 * Get last update user
	 *
	 * @return	integer	last update user
	 */
	public function getLastUpdateUser()
	{
		return $$this->last_update_user;
	}

	/**
	 * Set ${txt_property_name}
	 *
	 * @param	${property_type}	$$a_val ${txt_property_name}
	 */
	public function set${Property}($$a_val)
	{
		$$this->${property_var} = $$a_val;
	}

	/**
	 * Get ${txt_property_name}
	 *
	 * @return	${property_type}	${txt_property_name}
	 */
	public function get${Property}()
	{
		return $$this->${property_var};
	}

	/**
	 * Read instance data from database
	 */
	protected function read()
	{
		global $$ilDB;

		$$set = $$ilDB->query("SELECT * FROM ${table_name} WHERE ".
			" id = ".$$ilDB->quote($$this->getId(), "integer")
			);
		if ($$rec = $$ilDB->fetchAssoc($$set))
		{
			$$this->setCreateDate($$rec["create_date"]);
			$$this->setCreateUser($$rec["create_user"]);
			$$this->setLastUpdate($$rec["last_update"]);
			$$this->setLastUpdateUser($$rec["last_update_user"]);
			$$this->set${Property}($$rec["${property_var}"]);
		}
	}

	/**
	 * Create ${txt_class_name}
	 */
	public function create()
	{
		global $$ilDB, $$ilUser;

		$$this->setId($$ilDB->nextId("${table_name}"));

		$$now = ilUtil::now();
		$$this->setCreateDate($$now);
		$$this->setCreateUser($$ilUser->getId());
		$$this->setLastUpdate($$now);
		$$this->setLastUpdateUser($$ilUser->getId());

		$$ilDB->manipulate("INSERT INTO ${table_name} ".
			"(id, create_date, create_user, last_update, last_update_user, ${property_var})".
			" VALUES (".
			$$ilDB->quote($$this->getId(), "integer").",".
			$$ilDB->quote($$this->getCreateDate(), "timestamp").",".
			$$ilDB->quote($$this->getCreateUser(), "integer").",".
			$$ilDB->quote($$this->getLastUpdate(), "timestamp").",".
			$$ilDB->quote($$this->getLastUpdateUser(), "integer").",".
			$$ilDB->quote($$this->get${Property}(), "${property_db_type}").
			")");
	}

	/**
	 * Update ${txt_class_name}
	 */
	public function update()
	{
		global $$ilDB, $$ilUser;

		$$now = ilUtil::now();
		$$this->setLastUpdate($$now);
		$$this->setLastUpdateUser($$ilUser->getId());
		$$ilDB->manipulate("UPDATE ${table_name} SET ".
			" last_update = ".$$ilDB->quote($$this->getLastUpdate(), "timestamp").", ".
			" last_update_user = ".$$ilDB->quote($$this->getLastUpdateUser(), "integer").", ".
			" ${property_var} = ".$$ilDB->quote($$this->get${Property}(), "${property_db_type}").
			" WHERE id = ".$$ilDB->quote($$this->getId(), "integer")
			);
	}

	/**
	 * Delete ${txt_class_name}
	 */
	public function delete()
	{
		global $$ilDB;

		$$ilDB->manipulate("DELETE FROM ${table_name} WHERE "
			." id = ".$$ilDB->quote($$this->getId(), "integer")
			);

	}

	/**
	 * Get all ${txt_class_name}s
	 *
	 * @return	array	${txt_class_name} data
	 */
	public static function getAll${ClassName}s()
	{
		global $$ilDB;

		$$set = $$ilDB->query("SELECT * FROM ${table_name} ORDER BY ${property_var}");

		$$${class_instance_var} = array();
		while ($$rec = $$ilDB->fetchAssoc($$set))
		{
			$$${class_instance_var}[] = $$rec;
		}

		return $$${class_instance_var};
	}

	/**
	 * Lookup property
	 *
	 * @param	integer	$$a_id	${txt_class_name} id
	 * @param	string	$$a_prop	property
	 *
	 * @return	mixed	property value
	 */
	protected static function lookupProperty($$a_id, $$a_prop)
	{
		global $$ilDB;

		$$set = $$ilDB->query("SELECT $$a_prop FROM ${table_name} WHERE ".
			" id = ".$$ilDB->quote($$a_id, "integer")
			);
		$$rec = $$ilDB->fetchAssoc($$set);
		return $$rec[$$a_prop];
	}

	/**
	 * Lookup ${txt_property_name}
	 *
	 * @param	integer	${txt_class_name} id
	 *
	 * @return	string	${txt_property_name}
	 */
	public static function lookup${Property}($$a_id)
	{
		return adn${ClassName}::lookupProperty($$a_id, "${property_var}");
	}

}
