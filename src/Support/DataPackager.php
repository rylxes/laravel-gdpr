<?php

namespace Rylxes\Gdpr\Support;

use Illuminate\Support\Facades\Storage;
use Rylxes\Gdpr\Exceptions\ExportException;
use Rylxes\Gdpr\Models\DataExport;

class DataPackager
{
    /**
     * Package data sections into the requested format.
     *
     * @param array<string, array> $sections Keyed by export label
     * @param string $format json, csv, or xml
     * @return array{content: string, size_bytes: int, mime_type: string, extension: string}
     */
    public function package(array $sections, string $format): array
    {
        $content = match ($format) {
            'json' => $this->toJson($sections),
            'csv' => $this->toCsv($sections),
            'xml' => $this->toXml($sections),
            default => throw new ExportException("Unsupported export format: {$format}"),
        };

        $mimeType = match ($format) {
            'json' => 'application/json',
            'csv' => 'text/csv',
            'xml' => 'application/xml',
            default => 'application/octet-stream',
        };

        return [
            'content' => $content,
            'size_bytes' => strlen($content),
            'mime_type' => $mimeType,
            'extension' => $format,
        ];
    }

    /**
     * Store the packaged content to disk.
     *
     * @return string The relative file path
     */
    public function store(array $packageResult, DataExport $export): string
    {
        $disk = config('gdpr.export.storage_disk', 'local');
        $basePath = config('gdpr.export.storage_path', 'gdpr-exports');
        $fileName = sprintf(
            'data-export-%d-%s.%s',
            $export->id,
            now()->format('Y-m-d-His'),
            $packageResult['extension'],
        );
        $filePath = $basePath . '/' . $fileName;

        $maxSize = config('gdpr.export.max_export_size_mb', 100) * 1024 * 1024;
        if ($packageResult['size_bytes'] > $maxSize) {
            throw new ExportException(
                "Export size ({$packageResult['size_bytes']} bytes) exceeds maximum ({$maxSize} bytes)"
            );
        }

        Storage::disk($disk)->put($filePath, $packageResult['content']);

        return $filePath;
    }

    /**
     * Convert sections to JSON.
     */
    protected function toJson(array $sections): string
    {
        $data = [
            'exported_at' => now()->toIso8601String(),
            'sections' => $sections,
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * Convert sections to CSV.
     *
     * Each section becomes a header row followed by its data rows.
     */
    protected function toCsv(array $sections): string
    {
        $output = fopen('php://temp', 'r+');

        foreach ($sections as $label => $rows) {
            // Section header
            fputcsv($output, ["=== {$label} ==="], ',', '"', '\\');

            if (empty($rows)) {
                fputcsv($output, ['No data'], ',', '"', '\\');
                fputcsv($output, [], ',', '"', '\\'); // blank line
                continue;
            }

            // Normalize: if single record (assoc array), wrap in array
            if (! isset($rows[0])) {
                $rows = [$rows];
            }

            // Column headers from first row
            $headers = array_keys($this->flattenRow($rows[0]));
            fputcsv($output, $headers, ',', '"', '\\');

            // Data rows
            foreach ($rows as $row) {
                $flat = $this->flattenRow($row);
                fputcsv($output, array_values($flat), ',', '"', '\\');
            }

            fputcsv($output, [], ',', '"', '\\'); // blank line between sections
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    /**
     * Convert sections to XML.
     */
    protected function toXml(array $sections): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><data_export/>');
        $xml->addAttribute('exported_at', now()->toIso8601String());

        foreach ($sections as $label => $rows) {
            $sectionNode = $xml->addChild('section');
            $sectionNode->addAttribute('label', $label);

            // Normalize
            if (! isset($rows[0])) {
                $rows = [$rows];
            }

            foreach ($rows as $row) {
                $recordNode = $sectionNode->addChild('record');
                $flat = $this->flattenRow($row);
                foreach ($flat as $key => $value) {
                    $safeKey = preg_replace('/[^a-zA-Z0-9_]/', '_', $key);
                    $recordNode->addChild($safeKey, htmlspecialchars((string) ($value ?? ''), ENT_XML1, 'UTF-8'));
                }
            }
        }

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    /**
     * Flatten nested arrays/objects in a row for CSV output.
     */
    protected function flattenRow(array $row, string $prefix = ''): array
    {
        $flat = [];

        foreach ($row as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $flat = array_merge($flat, $this->flattenRow($value, $fullKey));
            } elseif (is_object($value)) {
                $flat[$fullKey] = json_encode($value);
            } else {
                $flat[$fullKey] = $value;
            }
        }

        return $flat;
    }
}
