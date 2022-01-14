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

import com.lowagie.text.Document;
import com.lowagie.text.DocumentException;
import com.lowagie.text.Element;
import com.lowagie.text.pdf.AcroFields;
import com.lowagie.text.pdf.BadPdfFormatException;
import com.lowagie.text.pdf.BaseFont;
import com.lowagie.text.pdf.PdfContentByte;
import com.lowagie.text.pdf.PdfCopy;
import com.lowagie.text.pdf.PdfDictionary;
import com.lowagie.text.pdf.PdfDocument;
import com.lowagie.text.pdf.PdfPTable;
import com.lowagie.text.pdf.PdfReader;
import com.lowagie.text.pdf.PdfStamper;
import com.lowagie.text.pdf.PdfTable;
import com.lowagie.text.pdf.PdfWriter;
import com.lowagie.text.pdf.TextField;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.FileReader;
import java.io.FileWriter;
import java.io.IOException;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Vector;
import org.apache.logging.log4j.Level;
import org.jdom.JDOMException;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;

/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
public class RPCTransformationHandler {

	private FO2PDF fo2pdf;
    protected static Logger logger = LogManager.getLogger(RPCTransformationHandler.class);

	/**
	 *
	 */
	public RPCTransformationHandler() {

		fo2pdf = new FO2PDF();

	}

	public boolean ping() {

		return true;
	}

	/**
	 * Fo string to pdf
	 * @param foString
	 * @return
	 */
	public byte[] ilFO2PDF(String foString) {

		try {
			fo2pdf.setFoString(foString);
			fo2pdf.transform();
			return fo2pdf.getPdf();
		} catch (TransformationException e) {

			logger.warn("Transformation failed:" + e);
		}
		return null;
	}

	/**
	 * Merge one or more PDFs into a new pdf.
	 * outFile is the path to the new PDF file.
	 * @param inFiles
	 * @param outFile
	 * @return
	 * @throws FileNotFoundException, TransformationException
	 */
	public boolean mergePdf(Vector<String> inFiles,String outFile) throws FileNotFoundException, TransformationException {

		logger.info("Start merging PDF documents...");

		Document document = new Document();
		PdfCopy copy = null;
		try {
			logger.debug("PDF copy: " + outFile);
			copy = new PdfCopy(document, new FileOutputStream(outFile));
		}
		catch (DocumentException exc) {
			logger.error(exc.getMessage());
			throw new TransformationException(exc);
		}
		catch (FileNotFoundException exc) {
			logger.error(exc.getMessage());
			throw exc;
		}
		document.open();

		PdfReader reader;

		logger.debug("more messages");
		logger.debug(inFiles.size());
		int n;
		for(int i = 0; i < inFiles.size(); i++) {
			try {
				logger.debug("Input file is: " + inFiles.elementAt(i));
				reader = new PdfReader(inFiles.elementAt(i));
				n = reader.getNumberOfPages();
				for (int page = 0; page < n;) {
					copy.addPage(copy.getImportedPage(reader, ++page));
				}
			}
			catch (IOException exc) {
				logger.error(exc.getMessage());
				throw new TransformationException(exc);
			}
			catch (BadPdfFormatException exc) {
				logger.error(exc.getMessage());
				throw new TransformationException(exc);
			}
		}
		try {
			document.close();
		}
		catch(Exception e) {
			logger.error(e.getMessage());
		}
		logger.info("Merged new PDF to " + outFile);
		return true;
 
	}

	/**
	 * Describe multiple tasks in one xml definition and execute them in one step to avoid
	 * multiple rpc calls.
	 * Should start threads for each task execution until numThreads is reached.
	 *
	 * @param taskDef
	 * @return
	 * @throws JDOMException
	 * @throws IOException
	 * @throws DocumentException 
	 */
	public boolean transformationTaskScheduler(String taskDef)
			throws JDOMException, IOException, DocumentException {

		TransformationTaskScheduler scheduler = new TransformationTaskScheduler(taskDef);

		try {
			scheduler.parse();
			scheduler.execute();
		}
		catch(TransformationException e) {

		}
		return true;
	}



	public boolean fillPdfTemplate(String inFile, String outFile, HashMap<String, String> map) 
		throws IOException, DocumentException {

		logger.info("Filling PDF-Template...");
		logger.debug("Input file is:" + inFile);


		PdfReader reader = new PdfReader(inFile);
		
		FileOutputStream fos = new FileOutputStream(outFile);



		PdfStamper stamper = new PdfStamper(reader,fos);
		stamper.setFormFlattening(true);

		// Iterate all fields
		Iterator ite = map.keySet().iterator();
		while(ite.hasNext()) {
			
			String next = (String) ite.next();
			String value = null;

			value = map.get(next).replace("<br>", "\n");

			logger.debug("Value is: " + value);

			stamper.getAcroFields().setField(next, value);
		}

		reader.close();
		stamper.close();

		logger.info("Finished filling PDF-Template " + outFile);
		return true;
	}

	/**
	 * Write question sheet
	 * @param inFile
	 * @param outFile
	 * @return
	 */
	public boolean writeQuestionSheet(String inFile,String outFile) throws TransformationException {

		logger.info("Writing new table...");
		//logger.setLevel(Level.DEBUG);

		QuestionSheetWriter writer = new QuestionSheetWriter(inFile,outFile);

		try {
			writer.write();
			return true;
		}
		catch(TransformationException e) {

			e.printStackTrace();
			logger.error("Error is " + e.getMessage());
			logger.error("Writing question sheet failed for: " + inFile);
			throw e;
		}
	}
	/**
	 * Write solution pdf (MC or case)
	 * @param inFile Input xml
	 * @param outFile
	 * @return
	 * @throws TransformationException
	 */
	public boolean writeSolutionSheet(String inFile, String outFile) throws TransformationException {

		logger.info("Start writing new solution sheet");

		QuestionSheetWriter writer = new QuestionSheetWriter(inFile, outFile);

		try {
			writer.writeSolution();
			return true;
		}
		catch(TransformationException e) {

			logger.error("Wrting solution sheet failed with message: " + e.getMessage());
			throw e;
		}
	}

	/**
	 * Create pdf from xml
	 * @param String inFile
	 * @param String outFile
	 * @return boolean
	 * @throws TransformationException
	 */
	public boolean adnPdfFromXml(String inFile, String outFile) throws TransformationException {

		logger.info("Start creating pdf from xml");

		AdnPdfFromXml pdf = new AdnPdfFromXml(inFile,outFile);

		try {
			pdf.create();
			return true;
		}
		catch(TransformationException e) {

			logger.error("Creating pdf from xml failed with message: " + e.getMessage());
			throw e;
		}
	}
	
	
	/**
	 * Add page numbers
	 * @param outFile
	 * @return
	 * @throws TransformationException 
	 */
	public boolean adnNumberPdf(String inFile, String outFile) throws TransformationException {
		
		FileReader reader = null;
		FileWriter writer = null;
		File source = null;
		File target = null;
		
		try {
			logger.info("Start numbering pdf...");
			// copy to tmp file
			source = new File(inFile);
			target = new File(outFile);
			
			logger.info("in file is " + source.getAbsolutePath());
			logger.info("out file is " + target.getAbsolutePath());

			if(!source.canRead()) {
				throw new TransformationException("Cannot read from source file: " + source.getAbsolutePath());
			}
			if(!target.canWrite()) {
				throw new TransformationException("Cannot write to outfile: " + target.getAbsolutePath());
			}
		} 
		catch (Exception ex) {
			logger.error("Error creation temp file" + ex.getMessage());
			throw new TransformationException(ex);
		}
		
		PdfReader pReader;
		PdfStamper stamper;
		
		try {
			
			pReader = new PdfReader(source.getAbsolutePath());
			stamper = new PdfStamper(pReader, new FileOutputStream(target.getAbsolutePath()));

			BaseFont bf = BaseFont.createFont(BaseFont.HELVETICA, BaseFont.WINANSI, BaseFont.EMBEDDED);
			
			for(int i = 1; i <= pReader.getNumberOfPages(); i++) {
				PdfContentByte content = stamper.getUnderContent(i);
				content.beginText();
				content.setFontAndSize(bf, 10);
				content.setTextMatrix(30, 30);
				content.showText("Seite " + i + " von " + pReader.getNumberOfPages());
				content.endText();
			}
			
			stamper.close();
		}
		catch(Exception e) {
			logger.error(e.getMessage());
			throw new TransformationException(e);
		}
		return true;
	}
}
