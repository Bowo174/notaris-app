<?php

namespace App\Services;

use App\Models\Order;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;
use ZipArchive;

class PpatReceiptDocument
{
    private const WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public function generate(Order $order): string
    {
        $template = resource_path('templates/TEMPLATE_TANDA_TERIMA_BERKAS_PPAT.docx');
        if (! is_file($template)) {
            throw new RuntimeException('Template tanda terima tidak ditemukan.');
        }

        File::ensureDirectoryExists(storage_path('app/tmp'));
        $sourcePath = tempnam(storage_path('app/tmp'), 'receipt-template-');
        $outputPath = tempnam(storage_path('app/tmp'), 'receipt-output-');
        if ($sourcePath === false || $outputPath === false) {
            throw new RuntimeException('File sementara tanda terima gagal disiapkan.');
        }

        $sourcePath .= '.docx';
        $outputPath .= '.docx';
        try {
            if (! copy($template, $sourcePath)) {
                throw new RuntimeException('Template tanda terima gagal disalin.');
            }

            $this->prepareTemplate($sourcePath, $order);
            $processor = new TemplateProcessor($sourcePath);
            $processor->setValues($this->values($order));
            $processor->setComplexBlock('FILE_LIST', $this->linesBlock($this->fileLines($order)));
            $processor->saveAs($outputPath);
        } catch (\Throwable $exception) {
            @unlink($sourcePath);
            @unlink($outputPath);
            throw $exception;
        }

        @unlink($sourcePath);

        return $outputPath;
    }

    private function prepareTemplate(string $path, Order $order): void
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Template tanda terima tidak dapat dibuka.');
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            throw new RuntimeException('Isi template tanda terima tidak valid.');
        }

        $document = new DOMDocument;
        $document->preserveWhiteSpace = true;
        if (! $document->loadXML($xml, LIBXML_NONET)) {
            $zip->close();
            throw new RuntimeException('XML template tanda terima tidak dapat dibaca.');
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', self::WORD_NS);
        $paragraphs = $xpath->query('//w:body//w:p');
        $insideSupportingFiles = false;
        $fileListInserted = false;

        foreach ($paragraphs as $paragraph) {
            $text = '';
            foreach ($xpath->query('.//w:t', $paragraph) as $textNode) {
                $text .= $textNode->textContent;
            }

            if (str_contains($text, 'Yang Menerima')) {
                $insideSupportingFiles = false;
            }

            if (str_contains($text, 'Dan berkas pendukung yang diserahkan')) {
                $insideSupportingFiles = true;
                $this->replaceParagraphText($document, $xpath, $paragraph, 'Dan berkas pendukung yang diserahkan berupa :');

                continue;
            }

            if (str_contains($text, 'dari inputan objek lokasi') || str_contains($text, 'Asli SHM No. 919/ Ds. Jatibarang Baru')) {
                $paragraph->parentNode?->removeChild($paragraph);

                continue;
            }

            if ($insideSupportingFiles && (str_contains($text, '(berkas pendukung)') || str_contains($text, 'Dan sebagainya'))) {
                if (! $fileListInserted && str_contains($text, '(berkas pendukung)')) {
                    $this->replaceParagraphText($document, $xpath, $paragraph, '${FILE_LIST}');
                    $fileListInserted = true;
                } else {
                    $paragraph->parentNode?->removeChild($paragraph);
                }

                continue;
            }

            $replacement = $this->templateParagraph($text, $order);
            if ($replacement !== null) {
                $this->replaceParagraphText($document, $xpath, $paragraph, $replacement);
            }
        }

        $this->applyTypography($document, $xpath);

        if (! $zip->addFromString('word/document.xml', $document->saveXML())) {
            $zip->close();
            throw new RuntimeException('Template tanda terima gagal diproses.');
        }
        $zip->close();
    }

    private function templateParagraph(string $text, Order $order): ?string
    {
        if (str_contains($text, 'berkas pendukung')) {
            return '${FILE_LIST}';
        }
        if (str_contains($text, 'Pada hari ini')) {
            return str_replace(
                ['(hari ini)', '(tanggal, nama bulan, tahun berapa)'],
                ['${WEEKDAY}', '${FULL_DATE}'],
                $text,
            );
        }

        $replacements = [
            '(dari inputan Nama)' => '${CLIENT_NAME}',
            '(dari inputan Tipe)' => '${CLIENT_TYPE}',
            '(dari inputan nomor identitas/NIK)' => '${CLIENT_NIK}',
            '(dari inputan No. WA)' => '${CLIENT_PHONE}',
            '(dari inputan Email)' => '${CLIENT_EMAIL}',
            '(dari inputan Alamat)' => '${CLIENT_ADDRESS}',
        ];
        $updated = str_replace(array_keys($replacements), array_values($replacements), $text);

        if (str_contains($updated, 'multi select') || str_contains($updated, 'Jenis Akta')) {
            $updated = preg_replace_callback(
                '/\(dari inputan yang multi select[^)]*\)?/u',
                fn () => '${SERVICE_NAMES}',
                $updated,
            ) ?? $updated;
        }
        if ($updated !== $text) {
            return $updated;
        }

        if (str_contains($text, 'Andre Wibowo')) {
            return str_replace('Andre Wibowo', '${CLIENT_NAME}', $text);
        }

        return null;
    }

    private function replaceParagraphText(DOMDocument $document, DOMXPath $xpath, DOMElement $paragraph, string $text): void
    {
        $runProperties = $xpath->query('./w:r/w:rPr', $paragraph)->item(0);
        $runProperties = $runProperties?->cloneNode(true);

        $boldPhrase = 'dari Notaris Putri Hilaliatul Badria Hakim, S.H., M.Kn.,';
        if (str_contains($text, $boldPhrase)) {
            foreach (iterator_to_array($paragraph->childNodes) as $child) {
                if (! ($child instanceof DOMElement && $child->namespaceURI === self::WORD_NS && $child->localName === 'pPr')) {
                    $paragraph->removeChild($child);
                }
            }

            $start = strpos($text, $boldPhrase);
            $segments = [
                [substr($text, 0, $start), false],
                [$boldPhrase, true],
                [substr($text, $start + strlen($boldPhrase)), false],
            ];
            foreach ($segments as [$segment, $bold]) {
                if ($segment !== '') {
                    $this->appendTextRun($document, $paragraph, $segment, $runProperties, $bold);
                }
            }

            return;
        }

        foreach (iterator_to_array($paragraph->childNodes) as $child) {
            if ($child instanceof DOMElement && $child->namespaceURI === self::WORD_NS && $child->localName === 'pPr') {
                continue;
            }
            $paragraph->removeChild($child);
        }

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [''];
        foreach ($lines as $index => $line) {
            $run = $document->createElementNS(self::WORD_NS, 'w:r');
            if ($index > 0) {
                $run->appendChild($document->createElementNS(self::WORD_NS, 'w:br'));
            }
            if ($index === 0 && $runProperties) {
                $run->appendChild($runProperties);
            }
            $textNode = $document->createElementNS(self::WORD_NS, 'w:t');
            $textNode->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
            $textNode->appendChild($document->createTextNode($line));
            $run->appendChild($textNode);
            $paragraph->appendChild($run);
        }
    }

    private function appendTextRun(DOMDocument $document, DOMElement $paragraph, string $text, ?\DOMNode $runProperties, bool $bold): void
    {
        $run = $document->createElementNS(self::WORD_NS, 'w:r');
        if ($runProperties) {
            $properties = $runProperties->cloneNode(true);
            $propertiesXPath = new DOMXPath($document);
            $propertiesXPath->registerNamespace('w', self::WORD_NS);
            if (! $bold) {
                foreach ($propertiesXPath->query('./w:b | ./w:bCs', $properties) as $boldNode) {
                    $boldNode->parentNode?->removeChild($boldNode);
                }
            }
            $run->appendChild($properties);
        }
        if ($bold) {
            $properties = $run->getElementsByTagNameNS(self::WORD_NS, 'rPr')->item(0);
            if (! $properties) {
                $properties = $document->createElementNS(self::WORD_NS, 'w:rPr');
                $run->insertBefore($properties, $run->firstChild);
            }
            $properties->appendChild($document->createElementNS(self::WORD_NS, 'w:b'));
            $properties->appendChild($document->createElementNS(self::WORD_NS, 'w:bCs'));
        }

        $textNode = $document->createElementNS(self::WORD_NS, 'w:t');
        $textNode->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        $textNode->appendChild($document->createTextNode($text));
        $run->appendChild($textNode);
        $paragraph->appendChild($run);
    }

    private function applyTypography(DOMDocument $document, DOMXPath $xpath): void
    {
        $wordNamespace = self::WORD_NS;
        foreach ($xpath->query('//w:r') as $run) {
            $properties = $xpath->query('./w:rPr', $run)->item(0);
            if (! $properties) {
                $properties = $document->createElementNS($wordNamespace, 'w:rPr');
                $run->insertBefore($properties, $run->firstChild);
            }

            $fonts = $xpath->query('./w:rFonts', $properties)->item(0);
            if (! $fonts) {
                $fonts = $document->createElementNS($wordNamespace, 'w:rFonts');
                $properties->insertBefore($fonts, $properties->firstChild);
            }
            foreach (['ascii', 'hAnsi', 'eastAsia', 'cs'] as $fontType) {
                $fonts->setAttributeNS($wordNamespace, 'w:'.$fontType, 'Times New Roman');
            }

            foreach (['sz', 'szCs'] as $sizeType) {
                $size = $xpath->query('./w:'.$sizeType, $properties)->item(0);
                if (! $size) {
                    $size = $document->createElementNS($wordNamespace, 'w:'.$sizeType);
                    $properties->appendChild($size);
                }
                $size->setAttributeNS($wordNamespace, 'w:val', '24');
            }
        }
    }

    private function values(Order $order): array
    {
        $now = now();
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $services = $this->serviceNames($order);
        $clientType = [
            'perorangan' => 'Perorangan',
            'individu' => 'Perorangan',
            'badan_usaha' => 'Badan usaha / badan hukum',
            'lembaga_non_ahu' => 'Lembaga / badan / perkumpulan non AHU',
            'kuasa' => 'Kuasa',
        ][$order->client_type_snapshot] ?? $order->client_type_snapshot;

        return [
            'WEEKDAY' => $days[$now->dayOfWeek],
            'FULL_DATE' => $now->day.' '.$months[$now->month].' '.$now->year,
            'CLIENT_NAME' => $order->client_name_snapshot,
            'CLIENT_TYPE' => $clientType,
            'CLIENT_NIK' => $order->client_nik_snapshot ?: '-',
            'CLIENT_PHONE' => $order->client_phone_snapshot,
            'CLIENT_EMAIL' => $order->client_email_snapshot ?: '-',
            'CLIENT_ADDRESS' => $order->client_address_snapshot,
            'SERVICE_NAMES' => implode(', ', $services) ?: '-',
        ];
    }

    private function serviceNames(Order $order): array
    {
        $order->loadMissing('services');
        $names = $order->services->map(fn ($service) => $service->pivot->service_name_snapshot ?: $service->name)->all();
        if ($order->services->isEmpty() && filled($order->service_name_snapshot)) {
            $names[] = $order->service_name_snapshot;
        }
        if (filled($order->other_service) && ! in_array($order->other_service, $names, true)) {
            $names[] = $order->other_service;
        }

        return $names;
    }

    private function fileLines(Order $order): array
    {
        $order->loadMissing('files');
        $labels = $order->files->pluck('label')->filter()->values()->all();

        return $labels
            ? array_map(fn (int $index, string $label) => ($index + 1).'. '.$label, array_keys($labels), $labels)
            : ['Tidak ada berkas pendukung'];
    }

    private function linesBlock(array $lines): TextRun
    {
        $run = new TextRun;
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $run->addTextBreak();
            }
            $run->addText((string) $line, ['name' => 'Times New Roman', 'size' => 12]);
        }

        return $run;
    }
}
