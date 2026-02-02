<?php

namespace App\Traits;

use App\Entities\Config\EntCfgTela;
use App\Models\Config\ConfigTelaModel;

trait HasTela
{
    protected ?EntCfgTela $tela = null;

    public function getTela(): ?EntCfgTela
    {
        if ($this->tela instanceof EntCfgTela) {
            return $this->tela;
        }

        if (empty($this->attributes['tel_id'])) {
            return null;
        }

        $model = new ConfigTelaModel();
        $tela = $model->find($this->attributes['tel_id']);

        if ($tela instanceof EntCfgTela) {
            $this->tela = $tela;
        }

        return $this->tela;
    }

    public function setTela(EntCfgTela $tela): self
    {
        $this->tela = $tela;
        $this->attributes['tel_id'] = $tela->tel_id;

        return $this;
    }
}
