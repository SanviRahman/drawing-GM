<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class EncryptedJson implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($decoded)) {
            throw new RuntimeException("The {$key} value is not valid JSON.");
        }

        // Backward-compatible read of an already-plain JSON object.
        if (! isset($decoded['ciphertext'])) {
            return $decoded;
        }

        $plain = Crypt::decryptString((string) $decoded['ciphertext']);
        $payload = json_decode($plain, true);

        if (! is_array($payload)) {
            throw new RuntimeException("The decrypted {$key} value is not a JSON object.");
        }

        return $payload;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (! is_array($value)) {
            throw new RuntimeException("The {$key} value must be an array.");
        }

        $json = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return json_encode([
            'v' => 1,
            'ciphertext' => Crypt::encryptString($json),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}
