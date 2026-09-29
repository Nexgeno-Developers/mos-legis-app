<?php

namespace App\Services\Manuscripts;

use RuntimeException;
use ZipArchive;

/**
 * Reads the text of a .docx (body, footnotes, endnotes) for the automatic
 * word count (SOW B.04) and for the plagiarism checker. Mirrors resources/js/word-count.js.
 */
class DocxWordCounter
{
    private const PARTS = ['word/document.xml', 'word/footnotes.xml', 'word/endnotes.xml'];

    public function text(string $absolutePath): string
    {
        $zip = new ZipArchive;

        if ($zip->open($absolutePath) !== true || $zip->locateName('word/document.xml') === false) {
            throw new RuntimeException('The file is not a valid .docx document.');
        }

        $text = '';

        foreach (self::PARTS as $part) {
            $xml = $zip->getFromName($part);
            if ($xml === false) {
                continue;
            }

            $xml = preg_replace('#<w:tab/>|<w:br/>|</w:p>#', ' ', $xml);
            $text .= ' '.html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
        }

        $zip->close();

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    public function count(string $absolutePath): int
    {
        return $this->countText($this->text($absolutePath));
    }

    public function countText(string $text): int
    {
        return count(array_filter(
            preg_split('/\s+/u', $text) ?: [],
            fn (string $word) => preg_match('/[\p{L}\p{N}]/u', $word) === 1,
        ));
    }
}
