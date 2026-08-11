<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleDriveGallery
{
    private const API = 'https://www.googleapis.com/drive/v3';

    public function configured(): bool
    {
        return filled(config('google-drive.folder_id'))
            && (filled(config('google-drive.api_key')) || is_file($this->credentialsPath()));
    }

    public function photos(): array
    {
        if (! $this->configured()) return [];

        return Cache::remember(
            'google-drive-gallery.'.sha1((string) config('google-drive.folder_id')),
            max(30, (int) config('google-drive.cache_seconds')),
            fn () => $this->fetchPhotos(),
        );
    }

    public function download(string $fileId): array
    {
        $photo = collect($this->photos())->firstWhere('id', $fileId);
        abort_unless($photo, 404);

        $response = $this->request()->get(self::API."/files/{$fileId}", $this->parameters(['alt' => 'media']));
        $response->throw();

        return ['body' => $response->body(), 'type' => $response->header('Content-Type') ?: $photo['mimeType']];
    }

    private function fetchPhotos(): array
    {
        $files = []; $pageToken = null;
        do {
            $params = [
                'q' => sprintf("'%s' in parents and mimeType contains 'image/' and trashed = false", config('google-drive.folder_id')),
                'fields' => 'nextPageToken,files(id,name,mimeType,modifiedTime,size)',
                'orderBy' => 'modifiedTime desc',
                'pageSize' => 1000,
            ];
            if ($pageToken) $params['pageToken'] = $pageToken;
            $response = $this->request()->get(self::API.'/files', $this->parameters($params));
            $response->throw();
            $json = $response->json();
            $files = array_merge($files, $json['files'] ?? []);
            $pageToken = $json['nextPageToken'] ?? null;
        } while ($pageToken);

        return $files;
    }

    private function request(): PendingRequest
    {
        $request = Http::acceptJson()->timeout(30)->retry(2, 500);
        if (filled(config('google-drive.ca_bundle'))) {
            $request = $request->withOptions(['verify' => config('google-drive.ca_bundle')]);
        }

        return filled(config('google-drive.api_key'))
            ? $request->withHeaders(['X-Goog-Api-Key' => config('google-drive.api_key')])
            : $request->withToken($this->accessToken());
    }

    private function parameters(array $parameters): array
    {
        return $parameters;
    }

    private function accessToken(): string
    {
        return Cache::remember('google-drive-service-token', 3300, function () {
            $credentials = json_decode((string) file_get_contents($this->credentialsPath()), true);
            if (! isset($credentials['client_email'], $credentials['private_key'])) throw new RuntimeException('Kredensial Google Drive tidak valid.');
            $now = time();
            $header = $this->base64Url(json_encode(['alg'=>'RS256','typ'=>'JWT']));
            $payload = $this->base64Url(json_encode([
                'iss'=>$credentials['client_email'], 'scope'=>'https://www.googleapis.com/auth/drive.readonly',
                'aud'=>$credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', 'iat'=>$now, 'exp'=>$now + 3600,
            ]));
            $unsigned = $header.'.'.$payload; $signature = '';
            if (! openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) throw new RuntimeException('Gagal menandatangani token Google Drive.');
            $jwt = $unsigned.'.'.$this->base64Url($signature);
            $response = Http::asForm()->timeout(30)->post($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
                'grant_type'=>'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion'=>$jwt,
            ]);
            $response->throw();
            return (string) $response->json('access_token');
        });
    }

    private function credentialsPath(): string
    {
        $path = (string) config('google-drive.credentials_path');
        return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) ? $path : base_path($path);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
