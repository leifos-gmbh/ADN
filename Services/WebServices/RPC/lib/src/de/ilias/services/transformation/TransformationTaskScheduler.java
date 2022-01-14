/*
+-----------------------------------------------------------------------------------------+
| ILIAS open source                                                                       |
+-----------------------------------------------------------------------------------------+
| Copyright (c) 1998-2001 ILIAS open source, University of Cologne                        |
|                                                                                         |
| This program is free software; you can redistribute it and/or                           |
| modify it under the terms of the GNU General Public License                             |
| as published by the Free Software Foundation; either version 2                          |
| of the License, or (at your option) any later version.                                  |
|                                                                                         |
| This program is distributed in the hope that it will be useful,                         |
| but WITHOUT ANY WARRANTY; without even the implied warranty of                          |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                           |
| GNU General Public License for more details.                                            |
|                                                                                         |
| You should have received a copy of the GNU General Public License                       |
| along with this program; if not, write to the Free Software                             |
| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             |
+-----------------------------------------------------------------------------------------+
*/
package de.ilias.services.transformation;

import com.lowagie.text.DocumentException;
import java.io.IOException;
import java.io.StringReader;
import java.util.HashMap;
import java.util.Vector;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.jdom.Document;
import org.jdom.Element;
import org.jdom.JDOMException;
import org.jdom.input.SAXBuilder;
import org.jdom.xpath.XPath;

/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
public class TransformationTaskScheduler {

	protected static Logger logger = LogManager.getLogger(TransformationTaskScheduler.class);


	String xml = null;
	private Document taskDoc;

	/**
	 *
	 * @param xml
	 */
	public TransformationTaskScheduler(String xml) {

		this.xml = xml;
	}

	/**
	 * Parse xml
	 */
	public void parse() throws JDOMException, IOException {
		
		SAXBuilder builder = new SAXBuilder();
		taskDoc = builder.build(new StringReader(xml));

	}

	/**
	 * Execute tasks
	 *
	 *
	 * @throws TransformationException
	 */
	public void execute()
		throws TransformationException, JDOMException, IOException, DocumentException {

		Element tasks = taskDoc.getRootElement();

		for(Object action : tasks.getChildren("action")) {

			// Call method
			String method = ((Element) action).getAttributeValue("method");
			if(method.equals("fillPdfTemplate")) {

				Element sth;
				sth =  (Element) XPath.selectSingleNode((Element) action, "param[1]/string");
				String inFile = sth.getText();
				sth =  (Element) XPath.selectSingleNode((Element) action, "param[2]/string");
				String outFile = sth.getText();

				HashMap<String, String> hash = new HashMap<String, String>();

				sth = (Element) XPath.selectSingleNode((Element) action, "param[3]");
				for(Object map : sth.getChildren()) {

					hash.put(
						((Element) map).getAttributeValue("name").trim(),
						((Element) map).getAttributeValue("value").trim()
					);
				}

				logger.debug("New param " + inFile);
				logger.debug("New param " + outFile);
				logger.debug("New param " + hash.toString());


				RPCTransformationHandler handler = new RPCTransformationHandler();
				handler.fillPdfTemplate(inFile.trim(), outFile.trim(), hash);
			}
			if(method.equalsIgnoreCase("mergePdf")) {

				Element sth;

				// Vector of infiles
				sth =  (Element) XPath.selectSingleNode((Element) action, "param[1]");
				Vector<String> vector = new Vector<String>();
				for(Object vec : sth.getChildren("vector")) {
					logger.debug("Adding " + ((Element) vec).getTextTrim());
					vector.add(((Element) vec).getTextTrim());
				}
				
				// outfile
				sth = (Element) XPath.selectSingleNode((Element) action, "param[2]/string");
				String outFile = sth.getTextTrim();

				logger.debug("PDF merge new param " + outFile);
				logger.debug("PDF merge new param " + vector.toString());

				RPCTransformationHandler handler = new RPCTransformationHandler();
				handler.mergePdf(vector, outFile.trim());
			}
			if(method.equalsIgnoreCase("writeQuestionSheet")) {

				logger.debug("Calling writeQuestionSheet");

				Element sth;
				sth =  (Element) XPath.selectSingleNode((Element) action, "param[1]/string");
				String inFile = sth.getText();
				sth =  (Element) XPath.selectSingleNode((Element) action, "param[2]/string");
				String outFile = sth.getText();

				RPCTransformationHandler handler = new RPCTransformationHandler();
				handler.writeQuestionSheet(inFile.trim(), outFile.trim());
			}
			if(method.equalsIgnoreCase("writeSolutionSheet")) {

				logger.debug("Calling writeSolutionSheet");

				Element sth;
				sth =  (Element) XPath.selectSingleNode((Element) action, "param[1]/string");
				String inFile = sth.getText();
				sth =  (Element) XPath.selectSingleNode((Element) action, "param[2]/string");
				String outFile = sth.getText();

				RPCTransformationHandler handler = new RPCTransformationHandler();
				handler.writeSolutionSheet(inFile.trim(), outFile.trim());
			}
			if(method.equalsIgnoreCase("adnPdfFromXml")) {

				logger.debug("Calling adnPdfFromXml");

				Element sth;
				sth = (Element) XPath.selectSingleNode((Element) action, "param[1]/string");
				String inFile = sth.getText();
				sth =  (Element) XPath.selectSingleNode((Element) action, "param[2]/string");
				String outFile = sth.getText();

				RPCTransformationHandler handler = new RPCTransformationHandler();
				handler.adnPdfFromXml(inFile.trim(),outFile.trim());
			}
			if(method.equalsIgnoreCase("adnNumberPdf")) {
				
				logger.debug("Calling numbering pdf");
				
				Element sth;
				
				sth = (Element) XPath.selectSingleNode((Element) action, "param[1]/string");
				String inFile = sth.getText().trim();
				sth = (Element) XPath.selectSingleNode((Element) action, "param[2]/string");
				String outFile = sth.getText().trim();
				
				RPCTransformationHandler handler = new RPCTransformationHandler();
				handler.adnNumberPdf(inFile, outFile);
			}
		}
	}
}
