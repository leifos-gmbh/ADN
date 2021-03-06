<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilFulltextMediaCastSearch
*
* class for searching media cast entries 
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* 
*
*/
include_once 'Services/Search/classes/class.ilMediaCastSearch.php';

class ilFulltextMediaCastSearch extends ilMediaCastSearch
{

	/**
	* Constructor
	* @access public
	*/
	function ilFulltextMediaCastSearch(&$qp_obj)
	{
		parent::ilMediaCastSearch($qp_obj);
	}

	function __createWhereCondition()
	{
		// IN BOOLEAN MODE
		if($this->db->isMysql4_0OrHigher())
		{
			$query .= " WHERE context_obj_type='mcst' AND MATCH(title,content) AGAINST('";
			foreach($this->query_parser->getQuotedWords(true) as $word)
			{
				$query .= $word;
				$query .= '* ';
			}
			$query .= "' IN BOOLEAN MODE) ";
		}
		else
		{
			// i do not see any reason, but MATCH AGAINST(...) OR MATCH AGAINST(...) does not use an index
			$query .= " WHERE  context_obj_type='mcst' AND MATCH (title,content) AGAINST(' ";
			foreach($this->query_parser->getQuotedWords(true) as $word)
			{
				$query .= $word;
				$query .= ' ';
			}
			$query .= "') ";
		}
		return $query;
	}
}
?>
