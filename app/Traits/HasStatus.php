<?php

namespace App\Traits;

use App\Entities\Config\EntCfgStatus;
use App\Models\Config\ConfigStatusModel;

trait HasStatus
{
    protected ?EntCfgStatus $status = null;

    public function getStatus(): ?EntCfgStatus
    {
        if ($this->status instanceof EntCfgStatus) {
            return $this->status;
        }

        if (empty($this->attributes['stt_id'])) {
            return null;
        }

        $model = new ConfigStatusModel();
        $status = $model->find($this->attributes['stt_id']);

        if ($status instanceof EntCfgStatus) {
            $this->status = $status;
        }

        return $this->status;
    }

    public function setStatus(EntCfgStatus $status): self
    {
        $this->status = $status;
        $this->attributes['stt_id'] = $status->stt_id;

        return $this;
    }
}
