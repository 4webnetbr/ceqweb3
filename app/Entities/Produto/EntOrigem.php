<?php

namespace App\Entities\Produto;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
Use App\Models\Produt\ProdutOrigemModel;

class EntOrigem extends Entity
{
    /**
     * Atributos permitidos
     */
    protected $attributes = [
        'ori_codOri'        => null,
        'ori_desOri'        => null,
        'ori_codDescricao'  => null,
    ];

    /**
     * Tipagem (se quiser evoluir depois)
     */
    protected $casts = [
        'ori_codOri'       => 'string',
        'ori_desOri'       => 'string',
        'ori_codDescricao' => 'string',
    ];

    /**
     * Construtor
     */
    public function __construct(array $data = null, bool $cast = false)
    {
        parent::__construct($data, $cast);
    }

    /**
     * Define os campos da tela (padrão MyCampo)
     *
     * @param array|object $dados
     * @param bool $show
     * @return array
     */
    public function defCampos($dados = false, $show = false)
    {
        $dados = (array) $dados;

        // CÓDIGO DA ORIGEM
        $cori = new MyCampo('pro_sap_origem', 'ori_codOri', true);
        $cori->valor        = $dados['ori_codOri'] ?? '';
        $cori->obrigatorio = true;
        $cori->leitura     = $show;
        $ret['ori_codOri'] = $cori->crInput();

        // DESCRIÇÃO DA ORIGEM
        $dori = new MyCampo('pro_sap_origem', 'ori_desOri');
        $dori->valor        = $dados['ori_desOri'] ?? '';
        $dori->obrigatorio = true;
        $dori->leitura     = $show;
        $ret['ori_desOri'] = $dori->crInput();

        // CÓDIGO + DESCRIÇÃO
        $cdes = new MyCampo('pro_sap_origem', 'ori_codDescricao');
        $cdes->valor        = $dados['ori_codDescricao'] ?? '';
        $cdes->obrigatorio = true;
        $cdes->leitura     = $show;
        $ret['ori_codDescricao'] = $cdes->crInput();

        return $ret;
    }
}