<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Auth;

class FirebaseService
{
    protected Auth $auth;

    public function __construct()
    {
        // 👇 Ruta absoluta SIEMPRE
        $credentialsPath = storage_path('app/firebase/service-account.json');

        // 🔍 Validación 1: ¿Existe el archivo?
        if (!file_exists($credentialsPath)) {
            throw new \RuntimeException(
                "❌ Firebase credentials NOT FOUND at: {$credentialsPath}\n" .
                "Verifica que el archivo exista."
            );
        }

        // 🔍 Validación 2: ¿Es legible?
        if (!is_readable($credentialsPath)) {
            throw new \RuntimeException(
                "❌ Firebase credentials NOT READABLE at: {$credentialsPath}"
            );
        }

        // 🔍 Validación 3: ¿Es JSON válido?
        $content = file_get_contents($credentialsPath);
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                "❌ Firebase credentials is not valid JSON: " . json_last_error_msg()
            );
        }

        // 🔍 Validación 4: ¿Es un service account?
        if (!isset($json['type']) || $json['type'] !== 'service_account') {
            throw new \RuntimeException(
                "❌ Firebase credentials is not a service account. Type: " . ($json['type'] ?? 'unknown')
            );
        }

        // 🔍 Validación 5: ¿Tiene los campos requeridos?
        $required = ['project_id', 'private_key', 'client_email'];
        foreach ($required as $field) {
            if (empty($json[$field])) {
                throw new \RuntimeException(
                    "❌ Firebase credentials missing required field: {$field}"
                );
            }
        }

        // 👇 Crear el factory con el ARRAY (evita problemas de ruta/encoding)
        $factory = (new Factory)->withServiceAccount($json);

        // 👇 AQUÍ ES DONDE ANTES FALLABA (google/auth)
        $this->auth = $factory->createAuth();
    }

    public function verifyIdToken(string $idToken): array
    {
        $verified = $this->auth->verifyIdToken($idToken);

        return [
            'uid'   => $verified->claims()->get('sub'),
            'email' => $verified->claims()->get('email'),
            'name'  => $verified->claims()->get('name'),
        ];
    }
}