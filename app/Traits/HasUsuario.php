<?php

namespace App\Traits;

use App\Entities\Config\EntCfgUsuario;
use App\Models\Config\ConfigUsuarioModel;

trait HasUsuario
{
    protected ?EntCfgUsuario $usuario = null;

    public function getUsuario(): ?EntCfgUsuario
    {
        if ($this->usuario instanceof EntCfgUsuario) {
            return $this->usuario;
        }

        if (empty($this->attributes['usu_id'])) {
            return null;
        }

        $model = new ConfigUsuarioModel();
        $usuario = $model->find($this->attributes['usu_id']);

        if ($usuario instanceof EntCfgUsuario) {
            $this->usuario = $usuario;
        }

        return $this->usuario;
    }

    public function setUsuario(EntCfgUsuario $usuario): self
    {
        $this->usuario = $usuario;
        $this->attributes['usu_id'] = $usuario->usu_id;

        return $this;
    }
}
