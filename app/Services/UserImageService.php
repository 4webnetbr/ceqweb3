<?php

namespace App\Services;


class UserImageService
{
    private \CodeIgniter\Cache\CacheInterface $cache;

    public function __construct()
    {
        $this->cache = service('cache');
    }

    public function getUserImage(int|string|null $usuId): ?string
    {
        if($usuId === null || !filter_var($usuId, FILTER_VALIDATE_INT)){
            return null;
        }
        // inválido → retorna null
        $cacheKey = "user_img_exists_{$usuId}";

        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            return $cached ? $this->buildUrl($usuId) : null;
        }

        $url = $this->buildUrl($usuId);

        // salva por 1 hora
        $this->cache->save($cacheKey, $url, 3600);

        return $url;
    }

    private function buildUrl(int $usuId): string
    {
        return "https://ceqweb3.ceqnep.com.br/assets/uploads/usuario/usu_{$usuId}.jpg";
    }
        
    private function urlExists(string $url): bool
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_NOBODY         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false, // evita erro SSL (opcional)
        ]);

        curl_exec($ch);

        // $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error  = curl_error($ch);

curl_close($ch);

var_dump($status, $error);
        // curl_close($ch);

        return $status === 200;
    }

}