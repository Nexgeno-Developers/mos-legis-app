<?php

namespace Tests\Support;

use Illuminate\Http\UploadedFile;
use ZipArchive;

/**
 * Builds minimal real .docx files for word-count and upload tests.
 */
final class Docx
{
    public static function withWords(int $words, string $name = 'manuscript.docx'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'docx');
        $body = implode(' ', array_fill(0, $words, 'law'));

        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>'.$body.'</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();

        return new UploadedFile($path, $name, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
    }
}
