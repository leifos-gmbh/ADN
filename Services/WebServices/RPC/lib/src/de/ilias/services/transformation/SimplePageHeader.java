/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

package de.ilias.services.transformation;

import com.lowagie.text.Document;
import com.lowagie.text.Element;
import com.lowagie.text.Paragraph;
import com.lowagie.text.Phrase;
import com.lowagie.text.Rectangle;
import com.lowagie.text.pdf.ColumnText;
import com.lowagie.text.pdf.PdfContentByte;
import com.lowagie.text.pdf.PdfPageEvent;
import com.lowagie.text.pdf.PdfWriter;

/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class SimplePageHeader implements PdfPageEvent {

	private Phrase phrase;


	public void setPhrase(Phrase p)
	{
		this.phrase = p;
	}

	public Phrase getPhrase()
	{
		return this.phrase;
	}

	public void onEndPage(PdfWriter writer, Document document)
	{
		PdfContentByte cb = writer.getDirectContent();
		ColumnText.showTextAligned(
				cb,
				Element.ALIGN_LEFT,
				getPhrase(),
				document.left(),
				document.top() + 25,
				0);

	}

	public void onOpenDocument(PdfWriter writer, Document dcmnt) {
	}

	public void onStartPage(PdfWriter writer, Document dcmnt) {
	}

	public void onCloseDocument(PdfWriter writer, Document dcmnt) {
	}

	public void onParagraph(PdfWriter writer, Document dcmnt, float f) {
	}

	public void onParagraphEnd(PdfWriter writer, Document dcmnt, float f) {
	}

	public void onChapter(PdfWriter writer, Document dcmnt, float f, Paragraph prgrph) {
	}

	public void onChapterEnd(PdfWriter writer, Document dcmnt, float f) {
	}

	public void onSection(PdfWriter writer, Document dcmnt, float f, int i, Paragraph prgrph) {
	}

	public void onSectionEnd(PdfWriter writer, Document dcmnt, float f) {
	}

	public void onGenericTag(PdfWriter writer, Document dcmnt, Rectangle rctngl, String string) {
	}
}
