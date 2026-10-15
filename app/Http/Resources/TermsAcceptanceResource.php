<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TermsAcceptanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'docType' => $this->version->doc_type,
            'version' => $this->version->version,
            'contentHash' => $this->version->content_hash,
            'acceptedAt' => $this->accepted_at->toIso8601String(),
            'ipAddress' => $this->ip_address,
            'userAgent' => $this->user_agent,
            'sourceUrl' => $this->source_url,
        ];
    }
}