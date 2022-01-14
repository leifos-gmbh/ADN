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

package de.ilias.services.lucene.search.highlight;

import java.util.HashMap;

import org.apache.log4j.Logger;
import org.jdom.Element;

import de.ilias.services.lucene.search.ResultExport;

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
public class HighlightObject implements ResultExport {

	protected static Logger logger = Logger.getLogger(HighlightObject.class);
	
	private HashMap<Integer, HighlightItem> items = new HashMap<Integer, HighlightItem>();
	private int objId;
	/**
	 * 
	 */
	public HighlightObject() {

	}

	/**
	 * @param objId
	 */
	public HighlightObject(int objId) {
		
		this.setObjId(objId);
	}

	public HighlightItem addItem(int subId) {
		
		if(items.containsKey(subId)) {
			return items.get(subId);
		}
		items.put(subId, new HighlightItem(subId));
		return items.get(subId);
	}
	/**
	 * @return the items
	 */
	public HashMap<Integer, HighlightItem> getItems() {
		return items;
	}

	/**
	 * @param objId the objId to set
	 */
	public void setObjId(int objId) {
		this.objId = objId;
	}

	/**
	 * @return the objId
	 */
	public int getObjId() {
		return objId;
	}

	/**
	 * Add xml
	 * @see de.ilias.services.lucene.search.highlight.HighlightResultExport#addXML(org.jdom.Element)
	 */
	public Element addXML() {

		Element obj = new Element("Object");
		obj.setAttribute("id",String.valueOf(getObjId()));
		
		for(Object item : items.values()) {
			
			obj.addContent(((ResultExport) item).addXML());
		}
		return obj;
	}

}
