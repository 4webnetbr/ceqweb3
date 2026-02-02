<?php

namespace App\Entities\Ocorrencia;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Ocorre\OcorreTipoAcaoModel;
use App\Models\Ocorre\OcorreTipoOcorrenciaModel;

class EntOcoTipoAcao extends Entity
{
    protected $attributes = [
        'tpa_id'       => null,
        'tpa_nome'     => null,
        'tpa_ativo'    => null,
        'tpa_tipo'     => null,
        'tpa_excluido' => null,
    ];

    protected $casts = [
        'tpa_id'    => 'integer',
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }

    
     public static function campoSelectTipoAcao(mixed $valor = null, bool $leitura = false, string $entidade = ''): string
    {
        $tpoModel = new OcorreTipoAcaoModel();
        $tpos = array_column($tpoModel->getTipoAcao(), 'tpa_nome', 'tpa_id');

        $mod = (new MyCampo($entidade, 'tpa_id'))
            ->setLabel('Tipo de Ação') 
            ->setValor($valor ?? '')
            ->setSelecionado($valor ?? '')
            ->setOpcoes($tpos)
            ->setLargura(50)
            ->setObrigatorio()
            ->setDispForm('col-6')
            ->setLeitura($leitura);

        return $mod->crSelect();
    }

    public function defCampos($dados = false, $show = false)
    {
        $ret = [];

        // ID do Tipo de Ação
        $mid            = new MyCampo('oco_tipo_acao', 'tpa_id');
        $mid->valor     = (isset($dados['tpa_id'])) ? $dados['tpa_id'] : '';
        $ret['tpa_id']   = $mid->crOculto();

        // Tipo de Ação
        $nome           =  new MyCampo('oco_tipo_acao', 'tpa_nome');
        $nome->valor    = (isset($dados['tpa_nome'])) ? $dados['tpa_nome'] : '';
        $nome->obrigatorio = true;
        $nome->leitura  = $show;
        $ret['tpa_nome'] = $nome->crInput();

        // Opções de Tipo da Ação
        $opcex['1'] = 'Justificar';
        $opcex['2'] = 'Listar Telas';
        $opcex['3'] = 'Listar Movimentações';
        $opcex['4'] = 'Listar Status';

        // Tipo da Ação
        $tipo           =  new MyCampo('oco_tipo_acao', 'tpa_tipo');
        $tipo->valor    = (isset($dados['tpa_tipo'])) ? $dados['tpa_tipo'] : '';
        $tipo->selecionado    = $tipo->valor;
        $tipo->opcoes   = $opcex;
        $tipo->dispForm     = 'col-2';
        $tipo->classep     = 'mb-2';
        $ret['tpa_tipo'] = $tipo->crRadio();

        // Retorna os campos do Tipo de Ação
        return $ret;
    }
}    