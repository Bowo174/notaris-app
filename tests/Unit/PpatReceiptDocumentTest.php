<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderFile;
use App\Services\PpatReceiptDocument;
use Tests\TestCase;
use ZipArchive;

class PpatReceiptDocumentTest extends TestCase
{
    public function test_it_generates_a_word_receipt_from_the_template(): void
    {
        $order = new Order([
            'client_name_snapshot' => 'Siti Aminah',
            'client_type_snapshot' => 'perorangan',
            'client_nik_snapshot' => '3201234567890001',
            'client_phone_snapshot' => '081234567890',
            'client_email_snapshot' => 'siti@example.test',
            'client_address_snapshot' => 'Jl. Merdeka No. 10',
            'service_name_snapshot' => 'Akta Jual Beli, Balik Nama',
            'object_location' => 'SHM No. 919, Desa Jatibarang Baru',
        ]);
        $order->setRelation('services', collect());
        $order->setRelation('files', collect([
            new OrderFile(['label' => 'KTP pemohon']),
            new OrderFile(['label' => 'Sertifikat tanah']),
        ]));

        $path = app(PpatReceiptDocument::class)->generate($order);

        try {
            $archive = new ZipArchive;
            $this->assertSame(true, $archive->open($path));
            $document = $archive->getFromName('word/document.xml');
            $archive->close();

            $this->assertNotFalse($document);
            $text = html_entity_decode(strip_tags($document), ENT_QUOTES | ENT_XML1);
            foreach (['Siti Aminah', '3201234567890001', '081234567890', 'Akta Jual Beli, Balik Nama', 'dari Notaris Putri Hilaliatul Badria Hakim, S.H., M.Kn.,', '1. KTP pemohon', '2. Sertifikat tanah'] as $expected) {
                $this->assertStringContainsString($expected, $text);
            }
            foreach (['(dari inputan', '${FILE_LIST}', '${OBJECT_LIST}', 'Fc. Keterangan Persetujuan IMB', 'Dan sebagainya', 'SHM No.'] as $templateExample) {
                $this->assertStringNotContainsString($templateExample, $text);
            }

            $this->assertStringContainsString('w:ascii="Times New Roman"', $document);
            $this->assertStringContainsString('w:sz w:val="24"', $document);

            $xml = new \DOMDocument;
            $xml->loadXML($document);
            $xpath = new \DOMXPath($xml);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $boldPhrase = $xpath->query('//w:r[w:rPr/w:b and contains(string(.), "dari Notaris Putri Hilaliatul Badria Hakim, S.H., M.Kn.,")]');
            $this->assertCount(1, $boldPhrase);
        } finally {
            @unlink($path);
        }
    }
}
