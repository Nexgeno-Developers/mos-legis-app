import JSZip from 'jszip';

/**
 * Count words in a .docx the same way App\Services\Manuscripts\DocxWordCounter does:
 * read word/document.xml (and footnotes), strip tags, split on whitespace.
 */
export async function countDocxWords(file) {
    const zip = await JSZip.loadAsync(file);
    let text = '';

    for (const part of ['word/document.xml', 'word/footnotes.xml', 'word/endnotes.xml']) {
        const entry = zip.file(part);
        if (!entry) continue;
        const xml = await entry.async('string');
        text += ' ' + xml
            .replace(/<w:tab\/>|<w:br\/>|<\/w:p>/g, ' ')
            .replace(/<[^>]+>/g, '')
            .replace(/&[a-z#0-9]+;/gi, ' ');
    }

    return text.split(/\s+/).filter((w) => /[\p{L}\p{N}]/u.test(w)).length;
}
