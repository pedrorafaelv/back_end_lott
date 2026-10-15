<?php

namespace App\Console\Commands;

use App\Models\LegalDocumentVersion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class PublishTermsVersion extends Command
{
    protected $signature = 'terms:publish
                            {docType : Tipo de documento (terms, privacy, etc.)}
                            {version : Versión (ej. 2.0)}
                            {file : Ruta al archivo con el texto legal}
                            {--effective-date= : Fecha de entrada en vigor (ISO 8601)}';

    protected $description = 'Publica una nueva versión de un documento legal';

    public function handle(): int
    {
        $docType = $this->argument('docType');
        $version = $this->argument('version');
        $filePath = $this->argument('file');

        if (! File::exists($filePath)) {
            $this->error("El archivo '{$filePath}' no existe.");
            return self::FAILURE;
        }

        $text = File::get($filePath);
        $hash = hash('sha256', $text);

        $effectiveDate = $this->option('effective-date')
            ? now()->parse($this->option('effective-date'))
            : now();

        $created = LegalDocumentVersion::firstOrCreate(
            [
                'doc_type' => $docType,
                'version' => $version,
            ],
            [
                'effective_date' => $effectiveDate,
                'content_hash' => $hash,
                'content_text' => $text,
            ]
        );

        if ($created->wasRecentlyCreated) {
            $this->info("✓ Publicada {$docType} v{$version} (hash {$hash}).");
            $this->info("  Entra en vigor: {$effectiveDate->toIso8601String()}");
        } else {
            $this->warn("La versión {$docType} v{$version} ya existía.");
        }

        return self::SUCCESS;
    }
}