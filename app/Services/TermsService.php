<?php

namespace App\Services;

use App\Exceptions\VersionNotActiveException;
use App\Models\LegalDocumentVersion;
use App\Models\TermsAcceptanceLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TermsService
{
    /**
     * Tipos de documento que se verifican por defecto en el guard.
     */
    public const MANDATORY_DOC_TYPES = ['terms', 'privacy'];

    /**
     * Verifica el estado de aceptación de TODOS los documentos obligatorios.
     */
    public function getStatus(User $user): array
    {
        $pending = [];

        foreach (self::MANDATORY_DOC_TYPES as $docType) {
            $currentVersion = LegalDocumentVersion::active($docType)
                ->latest('effective_date')
                ->first();

            if (! $currentVersion) {
                continue;
            }

            $acceptance = $this->getCurrentAcceptance($user, $docType);

            $upToDate = $acceptance !== null
                && $acceptance->version->version === $currentVersion->version;

            if (! $upToDate) {
                $pending[] = [
                    'docType' => $docType,
                    'currentVersion' => $currentVersion->version,
                    'acceptedVersion' => $acceptance?->version?->version,
                ];
            }
        }

        return [
            'requiresAcceptance' => count($pending) > 0,
            'pending' => $pending,
        ];
    }

    /**
     * Obtiene la aceptación vigente del usuario para un tipo de documento.
     */
    public function getCurrentAcceptance(User $user, string $docType): ?TermsAcceptanceLog
    {
        return TermsAcceptanceLog::with('version')
            ->where('user_id', $user->id)
            ->current()
            ->whereHas('version', function ($query) use ($docType) {
                $query->where('doc_type', $docType);
            })
            ->latest('accepted_at')
            ->first();
    }

    /**
     * Obtiene el estado de aceptación para un docType concreto.
     */
    public function getCurrent(User $user, string $docType): array
    {
        $currentVersion = LegalDocumentVersion::active($docType)
            ->latest('effective_date')
            ->first();

        if (! $currentVersion) {
            throw new \RuntimeException("No hay versión vigente para {$docType}");
        }

        $acceptance = $this->getCurrentAcceptance($user, $docType);

        $requiresAcceptance = $acceptance === null
            || $acceptance->version->version !== $currentVersion->version;

        return [
            'docType' => $docType,
            'currentVersion' => $currentVersion->version,
            'acceptance' => $acceptance ? [
                'id' => $acceptance->id,
                'userId' => $acceptance->user_id,
                'docType' => $docType,
                'version' => $acceptance->version->version,
                'contentHash' => $acceptance->version->content_hash,
                'acceptedAt' => $acceptance->accepted_at->toIso8601String(),
                'ipAddress' => $acceptance->ip_address,
                'userAgent' => $acceptance->user_agent,
                'sourceUrl' => $acceptance->source_url,
            ] : null,
            'requiresAcceptance' => $requiresAcceptance,
        ];
    }

    /**
     * Registra la aceptación de forma transaccional.
     *
     * @throws VersionNotActiveException
     */
    public function accept(
        User $user,
        string $docType,
        string $version,
        ?string $sourceUrl,
        ?string $ipAddress,
        ?string $userAgent
    ): TermsAcceptanceLog {
        return DB::transaction(function () use (
            $user, $docType, $version, $sourceUrl, $ipAddress, $userAgent
        ) {
            // 1. Verifica que la versión existe y está en vigor
            $docVersion = LegalDocumentVersion::where('doc_type', $docType)
                ->where('version', $version)
                ->where('effective_date', '<=', now())
                ->first();

            if (! $docVersion) {
                throw new VersionNotActiveException(
                    "La versión '{$version}' de '{$docType}' no existe o no está vigente."
                );
            }

            // 2. Marca como superseded la aceptación anterior del mismo docType
            $previous = TermsAcceptanceLog::where('user_id', $user->id)
                ->current()
                ->whereHas('version', function ($query) use ($docType) {
                    $query->where('doc_type', $docType);
                })
                ->get();

            // 3. Inserta la nueva aceptación
            $acceptance = TermsAcceptanceLog::create([
                'user_id' => $user->id,
                'doc_version_id' => $docVersion->id,
                'accepted_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'source_url' => $sourceUrl,
            ]);

            // 4. Marca las anteriores como superseded
            foreach ($previous as $prev) {
                $prev->update(['superseded_by' => $acceptance->id]);
            }

            return $acceptance->load('version');
        });
    }
}