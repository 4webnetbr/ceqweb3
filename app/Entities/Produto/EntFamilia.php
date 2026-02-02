<?php

namespace App\Entities\Produto;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;
use App\Models\Produt\ProdutFamiliaModel;

class EntFamilia extends Entity
{
    protected $attributes = [
        'fam_codFam'        => null,
        'fam_desFam'        => null,
        'ori_codOri'        => null,
        'ori_codDescricao'  => null,
        'fam_codDescricao'  => null,
    ];

    protected $casts = [
        'fam_codFam' => 'string',
    ];

    public array $campos = [];

    public function __construct(array|object|null $data = null, bool $show = false)
    {
        if ($data instanceof \stdClass) {
            $data = (array) $data;
        }

        parent::__construct($data ?? []);
        $this->campos = $this->defCampos($data ?? [], $show);
    }


     public static function campoSelectFamilia(mixed $valor = null, bool $leitura = false, string $entidade = ''): string
    {
        $famiModel = new ProdutFamiliaModel();
        $lst_fami  = $famiModel->getFamilia();
        $opc_fam   = array_column($lst_fami, 'fam_codDescricao', 'fam_codFam');
    
        $orig = (new MyCampo($entidade, 'fam_codFam', false))
            ->setLabel('Familia')
            ->setValor($valor ?? '')
            ->setSelecionado($valor ?? '')
            ->setOpcoes($opc_fam)
            ->setLeitura($leitura)
            ->setLargura(50)
            ->setObrigatorio()
            ->setDispForm('2col');
    
        return $orig->crSelect();
    }

    public function defCampos(object|array $dados = [], bool $show = false): array
    {
        $ret = [];
        if ($dados instanceof \stdClass) {
            $dados = (array) $dados;
        }

        // Código da Família
        $cfam              = new MyCampo('pro_sap_familia', 'fam_codFam', true);
        $cfam->valor       = $dados['fam_codFam'] ?? '';
        $cfam->obrigatorio = true;
        $cfam->leitura     = $show;
        $ret['fam_codFam'] = $cfam->crInput();

        // Descrição da Família
        $dfam              = new MyCampo('pro_sap_familia', 'fam_desFam');
        $dfam->valor       = $dados['fam_desFam'] ?? '';
        $dfam->obrigatorio = true;
        $dfam->leitura     = $show;
        $ret['fam_desFam'] = $dfam->crInput();

        // Origem
        $cori              = new MyCampo('pro_sap_origem', 'ori_codDescricao', true);
        $cori->valor       = $dados['ori_codDescricao'] ?? '';
        $cori->label       = 'Origem';
        $cori->obrigatorio = true;
        $cori->leitura     = $show;
        $ret['ori_codOri'] = $cori->crInput();

        // Código / Descrição auxiliar da Família
        $cdes                     = new MyCampo('pro_sap_familia', 'fam_codDescricao');
        $cdes->valor              = $dados['fam_codDescricao'] ?? '';
        $cdes->obrigatorio        = true;
        $cdes->leitura            = $show;
        $ret['fam_codDescricao']  = $cdes->crInput();

        return $ret;
    }
}
