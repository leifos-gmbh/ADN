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

import com.lowagie.text.BadElementException;
import com.lowagie.text.Chunk;
import com.lowagie.text.DocumentException;
import com.lowagie.text.Font;
import com.lowagie.text.FontFactory;
import com.lowagie.text.Image;
import com.lowagie.text.List;
import com.lowagie.text.ListItem;
import com.lowagie.text.Paragraph;
import com.lowagie.text.Phrase;
import com.lowagie.text.pdf.BaseFont;
import com.lowagie.text.pdf.MultiColumnText;
import com.lowagie.text.pdf.PdfContentByte;
import com.lowagie.text.pdf.PdfPCell;
import com.lowagie.text.pdf.PdfPTable;
import com.lowagie.text.pdf.PdfWriter;
import java.awt.Color;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.StringWriter;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.Iterator;
import java.util.Vector;
import java.util.logging.Level;
import javax.print.attribute.HashAttributeSet;
import javax.swing.GroupLayout;
import org.apache.batik.css.engine.value.StringValue;
import org.apache.log4j.Logger;
import org.jdom.Attribute;
import org.jdom.Document;
import org.jdom.Element;
import org.jdom.JDOMException;
import org.jdom.input.SAXBuilder;
import org.jdom.xpath.XPath;

/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class QuestionSheetWriter {

	protected static Logger logger = Logger.getLogger(QuestionSheetWriter.class);

	private File inFile = null;
	private File outFile = null;

	private Document xmlSheet = null;

	private Font fontTable = null;
	private Font fontSmallBold = null;
	private Font fontHeaderA = null;
	private Font fontHeaderB = null;
	private Font fontNormal = null;
	private Font fontTableBold = null;

	/**
	 *
	 * @param inFile
	 * @param outFile
	 * @throws TransformationException
	 */
	public QuestionSheetWriter(String inFile, String outFile) throws TransformationException {
		
		//logger.setLevel(org.apache.log4j.Level.INFO);
		initFiles(inFile,outFile);
		initXmlBuilder();
		initFonts();

	}

	/**
	 * Write
	 */
	public void write() throws TransformationException {

		try {
			Element answerSheet = xmlSheet.getRootElement();
			Element qsts = (Element) XPath.selectSingleNode(answerSheet, "questions");

			String type = qsts.getAttributeValue("type");
			if(type.equalsIgnoreCase("1")) {
				writeMC();
			}
			else {
				writeCASE();
			}

		}
		catch (JDOMException ex) {
			logger.error("Parsing input file failed: " + ex.getMessage());
			throw new TransformationException(ex);
		}

	}
	/**
	 * Write solution sheet
	 */
	public void writeSolution() throws TransformationException {

		try {
			Element answerSheet = xmlSheet.getRootElement();
			Element qsts = (Element) XPath.selectSingleNode(answerSheet, "questions");

			String type = qsts.getAttributeValue("type");
			if(type.equalsIgnoreCase("1")) {
				writeSolutionMC();
			}
			else {
				writeSolutionCASE();
			}
		}
		catch (JDOMException e) {
			logger.error("writeSolution failed with message " + e.getMessage());
			throw new TransformationException(e);
		}
		catch(TransformationException e) {
			logger.error("writeSolution failed with message " + e.getMessage());
			throw e;
		}
	}

	/**
	 * Write CASE answer sheet
	 */
	private void writeCASE() throws TransformationException {

		logger.debug("Writing CASE sheet");

		com.lowagie.text.Document doc = new com.lowagie.text.Document();
		PdfWriter writer;

		try {
			writer = PdfWriter.getInstance(doc, new FileOutputStream(this.outFile));
			doc.open();

			Element answerSheet = xmlSheet.getRootElement();
			Element qsts = (Element) XPath.selectSingleNode(answerSheet, "questions");

			String wsd = answerSheet.getChildTextTrim("wsd");

			doc.add(new Paragraph(wsd, fontSmallBold));
			doc.add(new Paragraph(" "));


			int numQuestions = 0;
			int totalQuestions = 0;

			for(Object qst : qsts.getChildren("question")) {

				numQuestions++;
				totalQuestions++;

				if(numQuestions > 2) {
					doc.newPage();
					doc.add(new Paragraph(wsd, fontSmallBold));
					doc.add(new Paragraph(" "));
					numQuestions = 1;
				}

				Paragraph para = new Paragraph();
				para.add(new Chunk(String.valueOf(totalQuestions) + ". ",fontNormal));

				// Parse chunks
				Vector<Chunk> chunks = null;
				chunks = parseChunks(((Element) qst).getChild("title"));
				for(int i = 0; i < chunks.size(); i++) {
					para.add(chunks.elementAt(i));
				}
				// Add paragraph and newlines
				doc.add(para);
				for(int i = 0 ; i < 15 ; i++) {
					doc.add(new Paragraph(" "));
				}

			}
			doc.newPage();
			doc.close();
		}
		catch(Exception e) {
			logger.error(e.getMessage());
			throw new TransformationException(e);
		}



	}


	/**
	 * Write solution sheet for case questions
	 * @throws TransformationException
	 */
	public void writeSolutionCASE() throws TransformationException {

		logger.debug("Writing CASE solution sheet");

		com.lowagie.text.Document doc = new com.lowagie.text.Document();
		PdfWriter writer;

		try {

			writer = PdfWriter.getInstance(doc, new FileOutputStream(this.outFile));
			doc.open();

			Element answerSheet = xmlSheet.getRootElement();
			Element qsts = (Element) XPath.selectSingleNode(answerSheet, "questions");

			String wsd = answerSheet.getChildTextTrim("wsd");

			// Add wmo header
			doc.add(new Paragraph(wsd, fontSmallBold));
			doc.add(new Paragraph(" "));
			doc.add(new Paragraph(" "));

			String headerA = answerSheet.getChildTextTrim("solutionHeaderA");
			String headerB = answerSheet.getChildTextTrim("solutionHeaderB");

			Paragraph p;

			p = new Paragraph(headerA,fontHeaderA);
			p.setAlignment(Paragraph.ALIGN_CENTER);
			doc.add(p);
			doc.add(new Paragraph(" "));

			p = new Paragraph(headerB,fontHeaderB);
			p.setAlignment(Paragraph.ALIGN_CENTER);
			doc.add(p);
			doc.add(new Paragraph(" "));

			//
			List list = new List(true,false);
			
			int questionNumber = 1;
			Iterator qstIterator = qsts.getChildren("question").iterator();
			while(qstIterator.hasNext()) {

				ListItem item = new ListItem("",fontTable);
				Vector<Chunk> chunks = null;

				Element cur = (Element) qstIterator.next();
				chunks = parseChunks(cur.getChild("solution"));
				for(int i = 0; i < chunks.size(); i++) {
					if(i == 0)
					{
						Chunk chunk = null;
						chunk = new Chunk(cur.getChildTextTrim("fullNumber") + " ",fontTable);
						chunk.setTextRenderMode(PdfContentByte.TEXT_RENDER_MODE_FILL_STROKE, 0.25f, null);
						item.add(chunk);
					}

					item.add(chunks.elementAt(i));
				}
				list.add(item);
			}

			doc.add(list);
			doc.close();
		}
		catch(Exception e) {
			logger.error("Write solution sheet failed with message: " + e.getMessage());
			throw new TransformationException(e);
		}
	}

	/**
	 * Write solution sheet
	 */
	public void writeSolutionMC() throws TransformationException {

		logger.debug("Writing MC solution sheet");

		com.lowagie.text.Document doc = new com.lowagie.text.Document();
		PdfWriter writer;

		try {

			writer = PdfWriter.getInstance(doc, new FileOutputStream(this.outFile));
			doc.open();

			Element answerSheet = xmlSheet.getRootElement();
			Element qsts = (Element) XPath.selectSingleNode(answerSheet, "questions");

			String wsd = answerSheet.getChildTextTrim("wsd");

			// Add wmo header
			doc.add(new Paragraph(wsd, fontSmallBold));
			doc.add(new Paragraph(" "));
			doc.add(new Paragraph(" "));

			String headerA = answerSheet.getChildTextTrim("solutionHeaderA");
			String headerB = answerSheet.getChildTextTrim("solutionHeaderB");

			logger.debug(headerA);
			logger.debug(headerB);

			Paragraph p;

			p = new Paragraph(headerA,fontHeaderA);
			p.setAlignment(Paragraph.ALIGN_CENTER);
			doc.add(p);
			doc.add(new Paragraph(" "));

			p = new Paragraph(headerB,fontHeaderB);
			p.setAlignment(Paragraph.ALIGN_CENTER);
			doc.add(p);
			doc.add(new Paragraph(" "));

			// Add two columns for each table
			MultiColumnText mct = new MultiColumnText();
			mct.addRegularColumns(doc.left(), doc.right(),0,1);

			PdfPTable table = null;
			PdfPCell cell = null;
			Iterator qstIterator = qsts.getChildren("question").iterator();
			int questionNumber = 0;
			while(qstIterator.hasNext()) {

				Element question = (Element) qstIterator.next();
				String solution = question.getChildTextTrim("solution");
				String fullNumber = question.getChildTextTrim("fullNumber");

				logger.debug("New solution " + solution + " for question number " + String.valueOf(questionNumber));

				questionNumber++;

				//if(questionNumber == 16) {
				//	mct.addElement(table);
					//doc.add(mct);
					//mct.nextColumn();
				//}
				//if(questionNumber == 1 || questionNumber == 16) {
				if(questionNumber == 1) {
					table = new PdfPTable(6);
					table.setSpacingAfter(500);
					table.setWidthPercentage(100f);

					// Header
					cell = new PdfPCell(new Paragraph("Nr."));
					cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
					cell.setRowspan(2);
					table.addCell(cell);

					cell = new PdfPCell(new Paragraph("Fragennummer"));
					cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
					cell.setRowspan(2);
					table.addCell(cell);

					cell = new PdfPCell(new Paragraph("Antwort"));
					cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
					cell.setColspan(4);
					table.addCell(cell);

					cell = new PdfPCell(new Paragraph("A"));
					cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
					table.addCell(cell);

					cell = new PdfPCell(new Paragraph("B"));
					cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
					table.addCell(cell);

					cell = new PdfPCell(new Paragraph("C"));
					cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
					table.addCell(cell);

					cell = new PdfPCell(new Paragraph("D"));
					cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
					table.addCell(cell);
				}

				// Add solution
				cell = new PdfPCell(new Paragraph(String.valueOf(questionNumber) + "."));
				cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
				table.addCell(cell);

				// Add full number
				cell = new PdfPCell(new Paragraph(fullNumber));
				cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
				table.addCell(cell);
				
				if(solution.equalsIgnoreCase("A")) {
					cell = new PdfPCell(new Paragraph("X"));
				}
				else {
					cell = new PdfPCell();
				}
				cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
				table.addCell(cell);

				if(solution.equalsIgnoreCase("B")) {
					cell = new PdfPCell(new Paragraph("X"));
				}
				else {
					cell = new PdfPCell();
				}
				cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
				table.addCell(cell);

				if(solution.equalsIgnoreCase("C")) {
					cell = new PdfPCell(new Paragraph("X"));
				}
				else {
					cell = new PdfPCell();
				}
				cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
				table.addCell(cell);

				if(solution.equalsIgnoreCase("D")) {
					cell = new PdfPCell(new Paragraph("X"));
				}
				else {
					cell = new PdfPCell();
				}
				cell.setHorizontalAlignment(PdfPCell.ALIGN_CENTER);
				table.addCell(cell);
			}

			// Add second table
			mct.addElement(table);
			doc.add(mct);
			doc.close();
		}
		catch(Exception e) {
			logger.error("Write solution sheet failed with message: " + e.getMessage());
			throw new TransformationException(e);
		}
	}

	/**
	 * Write answer sheet
	 */
	public void writeMC() throws TransformationException {

		logger.debug("Writing MC sheet");

		com.lowagie.text.Document doc = new com.lowagie.text.Document();
		PdfWriter writer;

		try {
			writer = PdfWriter.getInstance(doc, new FileOutputStream(this.outFile));
			doc.open();

			Element answerSheet = xmlSheet.getRootElement();
			Element qsts = (Element) XPath.selectSingleNode(answerSheet, "questions");

			logger.debug("Element root: " + answerSheet.getTextTrim());
			logger.debug("Element questions: type " + qsts.getAttributeValue("type"));

			String wsd = answerSheet.getChildTextTrim("wsd");

			doc.add(new Paragraph(wsd, fontSmallBold));
			doc.add(new Paragraph(" "));

			MultiColumnText mct = new MultiColumnText();
			mct.addRegularColumns(doc.left(), doc.right(),0,2);

			PdfPTable table = new PdfPTable(1);
			table.setWidthPercentage(100);

			int questionNumber = 1;
			for(Object qst : qsts.getChildren("question")) {

				StringWriter title = new StringWriter();

				PdfPCell cell = new PdfPCell();
				cell.setPadding(5f);

				Paragraph para = new Paragraph();
				para.add(new Chunk(String.valueOf(questionNumber++) + ". ",fontTable));
				
				/*
				 * Add question image
				 */
				Chunk imageInChunk = addImage((Element) qst);
				if(imageInChunk != null) {
					para.add(new Chunk(" ", fontTable));
					para.add(imageInChunk);
				}
				
				Vector<Chunk> chunks = null;


				chunks = parseChunks(((Element) qst).getChild("title"));
				for(int i = 0; i < chunks.size(); i++) {
					para.add(chunks.elementAt(i));
				}
				cell.addElement(para);

				//cell.addElement(new Paragraph(title.toString(),fontTable));
				cell.addElement(new Paragraph(" "));


				// List for answers
				com.lowagie.text.List list = new List(true,true);
				list.setLowercase(false);

				for(Object answer : ((Element) qst).getChildren("answer")) {

					ListItem item = new ListItem("",fontTable);
					/**
					 * Add answer image
					 */
					Chunk answerImageInChunk = addImage((Element) answer);
					if(answerImageInChunk != null) {
						item.setSpacingBefore(15f);
						item.add(new Chunk(" "));
						item.add(answerImageInChunk);
					}

					chunks = parseChunks((Element) answer);
					for (int i = 0; i < chunks.size(); i++) {
						item.add(chunks.elementAt(i));
					}
					item.setIndentationLeft(20);
					list.add(item);
				}
				cell.addElement(list);
				cell.addElement(new Paragraph(" "));

				// add solution box
				cell.addElement(createSolutionBox());

				// Finally add cell
				table.addCell(cell);

			}

			// Create box for solution
			mct.addElement(table);

			doc.add(mct);
			doc.close();

		} 
		catch (FileNotFoundException ex) {
			throw new TransformationException(ex);
		}
		catch (com.lowagie.text.DocumentException ex) {
			throw new TransformationException(ex);
		}
		catch (JDOMException ex) {
			throw new TransformationException(ex);
		}

	}
	
	/**
	 * Get image
	 */
	protected Chunk addImage(org.jdom.Element xmlElement) {
		
		String path = ((Element) xmlElement).getAttribute("filePath").getValue();
					
		logger.info("Received image path " + path);
					
		if(path.length() > 0) {
						
			Image img;
			File imageFile;
			try {
				imageFile = new File(path);
				if(imageFile.canRead()) {
					
					img = Image.getInstance(path);
					img.scaleAbsolute(30f,30f);
					img.setAlignment(Image.MIDDLE);
								
					return new Chunk(img,0f,0f);
				}
			}
			catch(Exception e) {
				logger.error(e.getMessage());
			}
		}
		return null;
	}

	protected PdfPTable createSolutionBox()
	{
		PdfPTable box = null;
		PdfPCell cell = null;

		float[] width = { 90f, 10f};


		box = new PdfPTable(2);
		try
		{
			box.setWidths(width);
		}
		catch (DocumentException ex)
		{
			logger.error(ex.getMessage());
		}
		box.setWidthPercentage(100);
		box.setSpacingAfter(0f);
		box.setSpacingBefore(0f);

		cell = new PdfPCell(new Paragraph(" "));
		cell.setBorderWidth(0f);
		box.addCell(cell);

		cell = new PdfPCell(new Paragraph(" "));
		cell.setFixedHeight(10f);
		cell.setBorderWidth(1.3f);
		cell.setBorderColor(Color.BLACK);
		box.addCell(cell);

		return box;
	}

	/**
	 * Parse phrases
	 * @param el
	 * @return
	 */
	protected Vector<Chunk> parseChunks(Element parent) {

		Vector<Chunk> chunks = new Vector<Chunk>();

		logger.debug("Parsing phrases");

		Iterator chunksIte = parent.getChildren("phrase").iterator();
		while(chunksIte.hasNext()) {
			
			Element phrase = (Element) chunksIte.next();
			String txt = phrase.getText();
			Chunk chunk = null;

			float underlineOffset = 0f;

			logger.debug("Question title is " + txt);

			chunk = new Chunk(txt, fontTable);
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

	/**
	 * init files
	 * @param inFile
	 * @param outFile
	 */
	private void initFiles(String inf, String outf) throws TransformationException {

		this.inFile = new File(inf);
		if(!this.inFile.canRead()) {
			logger.error("Cannot read from file " + inf);
			throw new TransformationException("Cannot read from file: " + inf);
		}
		this.outFile = new File(outf);
		/*
		if(!this.outFile.can()) {
			logger.error("Cannot write to file " + outf);
			throw new TransformationException("Cannot write to file: " + outf);
		}
		*/
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

		/*
		this.fontHeaderA = new Font(Font.HELVETICA,12);
		this.fontHeaderB = new Font(Font.HELVETICA,10);
		this.fontTable = new Font(Font.HELVETICA,9);
		this.fontTableBold = new Font(Font.BOLD,9);
		this.fontNormal = new Font(Font.HELVETICA,9);
		this.fontSmallBold = new Font(Font.BOLD,8);
		*/
		
		this.fontHeaderA = FontFactory.getFont("/de/ilias/services/transformation/fonts/freesans.ttf", BaseFont.IDENTITY_H, BaseFont.EMBEDDED, 12);
		this.fontHeaderB = FontFactory.getFont("/de/ilias/services/transformation/fonts/freesans.ttf", BaseFont.IDENTITY_H, BaseFont.EMBEDDED, 10);
		this.fontTable = FontFactory.getFont("/de/ilias/services/transformation/fonts/freesans.ttf", BaseFont.IDENTITY_H, BaseFont.EMBEDDED, 9);
		this.fontTableBold = FontFactory.getFont("/de/ilias/services/transformation/fonts/freesansbold.ttf", BaseFont.IDENTITY_H, BaseFont.EMBEDDED, 9);
		this.fontNormal = FontFactory.getFont("/de/ilias/services/transformation/fonts/freesans.ttf", BaseFont.IDENTITY_H, BaseFont.EMBEDDED, 9);
		this.fontSmallBold = FontFactory.getFont("/de/ilias/services/transformation/fonts/freesansbold.ttf", BaseFont.IDENTITY_H, BaseFont.EMBEDDED, 9);
		
	}



}
