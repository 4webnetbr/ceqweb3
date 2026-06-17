<?php

namespace App\Services;

use App\Models\Ocorre\OcorreSubtOcorrenciaModel;
use App\Controllers\Ocorrencia\OcoTrataOcorrencia;

class OcorrenciaService
{
    public function processAfterSave(array $data): void
    {
        $model = new OcorreSubtOcorrenciaModel();

        $subtipo = $model->getSubtOcorrencia($data['sut_id']);

        if (!empty($subtipo) && $subtipo[0]->sut_fina === 'S') {
            $controller = new OcoTrataOcorrencia();
            $controller->store($data);
        }
    }
}
