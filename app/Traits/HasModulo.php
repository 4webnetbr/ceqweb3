<?php

namespace App\Traits;

use App\Entities\Config\EntCfgModulo;
use App\Models\Config\ConfigModuloModel;

trait HasModulo
{
    /**
     * Módulo relacionado (cacheado)
     */
    protected ?EntCfgModulo $modulo = null;

    /**
     * Retorna a entidade do módulo relacionada (lazy loaded)
     */
    public function getModulo(): ?EntCfgModulo
    {
        if ($this->modulo instanceof EntCfgModulo) {
            return $this->modulo;
        }

        if (empty($this->attributes['mod_id'])) {
            return null;
        }

        $model = new ConfigModuloModel();
        $modulo = $model->find($this->attributes['mod_id']);

        if ($modulo instanceof EntCfgModulo) {
            $this->modulo = $modulo;
        }

        return $this->modulo;
    }

    /**
     * Define manualmente o módulo (injeção)
     */
    public function setModulo(EntCfgModulo $modulo): self
    {
        $this->modulo = $modulo;
        $this->attributes['mod_id'] = $modulo->mod_id;

        return $this;
    }
}
