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

import com.lowagie.text.Chunk;
import com.lowagie.text.DocumentException;
import com.lowagie.text.Font;
import com.lowagie.text.Paragraph;
import com.lowagie.text.Phrase;
import com.lowagie.text.pdf.PdfContentByte;
import com.lowagie.text.pdf.PdfWriter;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.Iterator;
import java.util.Vector;
import org.apache.log4j.Logger;
import org.jdom.Document;
import org.jdom.JDOMException;
import org.jdom.input.SAXBuilder;
import org.jdom.xpath.XPath;

/**
 * Creates pdfs from xml
 * @author Stefan Meyer <meyer@leifos.com>
 */
class AdnPdfFromXml {

	protected static Logger logger = Logger.getLogger(AdnPdfFromXml.class);

	private File inFile = null;
	private File outFile = null;

	private Document xmlSheet;
	private com.lowagie.text.Document pdf = null;

	private Font fontHeaderA;
	private Font fontHeaderB;
	private Font fontTable;
	private Font fontTableBold;
	private Font fontNormal;
	private Font fontSmallBold;

	private SimplePageHeader header = new SimplePageHeader();

	/**
	 * Constructor
	 * @param inFile
	 * @param outFile
	 * @throws TransformationException
	 */
	AdnPdfFromXml(String inFile, String outFile) throws TransformationException {

		initFiles(inFile,outFile);
		initXmlBuilder();
		initFonts();



	}

	/**
	 * create pdf
	 * @throws TransformationException
	 */
	void create()  throws TransformationException {

		pdf = new com.lowagie.text.Document();
		PdfWriter writer;

		try {
			writer = PdfWriter.getInstance(pdf, new FileOutputStream(this.outFile));
			writer.setPageEvent(header);
			pdf.open();

			org.jdom.Element page = (org.jdom.Element) xmlSheet.getRootElement();

			parsePageHeader((org.jdom.Element) XPath.selectSingleNode(page, "pageHeader"));
			parseParagraphs(page);


			pdf.close();
		}
		catch(FileNotFoundException e) {
			throw new TransformationException(e);
		}
		catch(com.lowagie.text.DocumentException e) {
			throw new TransformationException(e);
		}
		catch(JDOMException e) {
			throw new TransformationException(e);
		}
	}

	/**
	 * Init input/output files
	 * @param String inFile
	 * @param String outFile
	 * @throws TransformationException
	 */
	private void initFiles(String inFile, String outFile) throws TransformationException {

		this.inFile = new File(inFile);
		if(!this.inFile.canRead()) {
			logger.error("Cannot read from file " + inFile);
			throw new TransformationException("Cannot read from file: " + inFile);
		}
		this.outFile = new File(outFile);
	}

	/**
	 * Init dom4j builder
	 * @throws TransformationException
	 */
	private void initXmlBuilder() throws TransformationException {

		SAXBuilder builder = new SAXBuilder();
		try {
			xmlSheet = builder.build(this.inFile);
		}
		catch (JDOMException ex) {
			logger.error(ex.getMessage());
			throw new TransformationException(ex);
		}
		catch (IOException ex) {
			logger.error(ex.getMessage());
			throw new TransformationException(ex);
		}
	}

	private void initFonts() {

		this.fontHeaderA = new Font(Font.HELVETICA,12);
		this.fontHeaderB = new Font(Font.HELVETICA,10);
		this.fontTable = new Font(Font.HELVETICA,9);
		this.fontTableBold = new Font(Font.BOLD,9);
		this.fontNormal = new Font(Font.HELVETICA,9);
		this.fontSmallBold = new Font(Font.BOLD,8);
	}

	/**
	 * Parse page header
	 * @param element
	 */
	private void parsePageHeader(org.jdom.Element header) {

		if(header == null) {
			return;
		}
		this.header.setPhrase(
				new Phrase(header.getTextTrim(),fontSmallBold));
	}


	/**
	 *
	 * @param root
	 * @throws TransformationException
	 */
	private void parseParagraphs(org.jdom.Element root) throws TransformationException {


		try {

			Iterator paraIte = root.getChildren("paragraph").iterator();
			while (paraIte.hasNext()) {

				org.jdom.Element para = (org.jdom.Element) paraIte.next();

				if (para.getAttributeValue("type") == null) {
					continue;
				}
				// Parse paragraph of type header
				if (para.getAttributeValue("type").equals("header")) {

					pdf.add(new Paragraph(para.getTextTrim(), fontHeaderA));
					pdf.add(new Paragraph(" "));
				}
				if (para.getAttributeValue("type").equals("bold")) {

					pdf.add(new Paragraph(para.getTextTrim(), fontTableBold));
				}

				// Parse paragraph containing chunks
				if(para.getAttributeValue("type").equals("phrase")) {

					Paragraph p = new Paragraph();

					// Parse chunks
					Vector<Chunk> chunks = null;
					chunks = parseChunks(para);
					for (int i = 0; i < chunks.size(); i++) {
						p.add(chunks.elementAt(i));
					}

					pdf.add(p);
				}
			}
		}
		catch(DocumentException e) {
			throw new TransformationException(e);
		}
	}

	/**
	 * Parse phrases
	 * @param el
	 * @return
	 */
	protected Vector<Chunk> parseChunks(org.jdom.Element parent) {

		Vector<Chunk> chunks = new Vector<Chunk>();

		logger.debug("Parsing phrases");

		Iterator chunksIte = parent.getChildren("phrase").iterator();
		while(chunksIte.hasNext()) {

			org.jdom.Element phrase = (org.jdom.Element) chunksIte.next();
			String txt = phrase.getText();
			Chunk chunk = null;

			float underlineOffset = 0f;

			logger.debug("Question title is " + txt);

			chunk = new Chunk(txt, fontNormal);
			// Bold or not
			if(phrase.getAttribute("bold") != null) {
				chunk.setTextRenderMode(PdfContentByte.TEXT_RENDER_MODE_FILL_STROKE, 0.25f, null);
			}

			if(phrase.getAttribute("higher") != null) {
				underlineOffset = 3.0f;
				chunk.setTextRise(3.0f);
			}
			if(phrase.getAttribute("deeper") != null) {
				underlineOffset = -3.0f;
				chunk.setTextRise(-3.0f);
			}
			// Underline
			if(phrase.getAttribute("underline") != null) {
				chunk.setUnderline(0.2f, -2f + underlineOffset);
			}

			// Add to map
			chunks.add(chunk);

		}

		return chunks;
	}
}
