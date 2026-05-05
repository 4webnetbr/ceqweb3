<?php

namespace App\Services;

class RedisSessionService
{
    private $redis;
    private $ttl = 600; // 30 minutos

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    private function tabKey($tabId)
    {
        return "tab:$tabId";
    }

    private function userTabsKey($userId)
    {
        return "user:$userId:tabs";
    }

    // 🔥 Atualiza ou cria atividade da aba
    public function updateTab($userId, $tabId)
    {
        $this->redis->hMSet($this->tabKey($tabId), [
            'user_id'       => $userId,
            'last_activity' => time(),
        ]);

        $this->redis->expire($this->tabKey($tabId), $this->ttl);

        $this->redis->sAdd($this->userTabsKey($userId), $tabId);
    }

    // 🔍 Verifica se a aba ainda está ativa
    public function isTabActive($tabId)
    {
        return $this->redis->exists($this->tabKey($tabId));
    }

    // ❌ Remove aba (logout específico)
    public function removeTab($userId, $tabId)
    {
        debug('remover ' . $userId . ' - ' . $tabId);
        $this->redis->del($this->tabKey($tabId));
        $this->redis->sRem($this->userTabsKey($userId), $tabId);
    }

    // 📋 Lista abas do usuário
    public function getUserTabs($userId)
    {
        return $this->redis->sMembers($this->userTabsKey($userId));
    }
}
