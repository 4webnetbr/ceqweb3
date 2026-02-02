<?php

namespace App\Entities\Config;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

class EntCfgEtiquetaCampo extends Entity
{
    protected $attributes = [
        'etc_id'         => null,
        'etq_id'         => null,
        'etc_campo'      => null,
        'etc_rotulo'     => null,
        'etc_codbar'     => 'N',
        'etc_fonte'      => 'Arial',
        'etc_tamanho'    => 12,
        'etc_negrito'    => 'N',
        'etc_italico'    => 'N',
        'etc_sublinhado' => 'N',
        'etc_alinhamento'=> 'L',
        'etc_caracteres' => null,
        'etc_linhas'     => 1,
        'etc_colunas'    => 50,
        'etc_atualizado' => null,
    ];

    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false, int $pos = 0)
    {
        parent::__construct($data);
        $this->campos = $this->defCamposCfg($show, $pos);
    }

    public function defCamposCfg(bool $show = false, int $pos = 0): array
    {
        $dados = $this->toArray();
        $ret   = [];
        $simnao = ['S' => 'Sim', 'N' => 'Não'];

        // $base_url = base_url('/CriaEtiquetaZPL/previewEtiquetaViaAjax');

        // // Campo
        // $campo = new MyCampo('cfg_etiqueta_campo', 'etc_campo');
        // $campo->valor       = $dados['etc_campo'] ?? '';
        // $campo->selecionado = $campo->valor;
        // $campo->obrigatorio = true;
        // $campo->leitura     = $show;
        // $campo->ordem       = $pos;
        // $campo->dispForm    = 'col-5';
        // $campo->funcChan    = "prevEtiqueta('{$base_url}')";
        // $ret['etc_campo']   = $campo->crDepende();

        $base_url = base_url('/CriaEtiquetaZPL/previewEtiquetaViaAjax');

        // Campo
        $config = [];
        $config['DispForm']    = 'col-5';
        $config['Obrigatorio'] = true;
        $config['Leitura']     = $show;
        $config['Ordem']       = $pos;
        $config['FuncChan']    = "prevEtiqueta('{$base_url}')";
        
        $ret['etc_campo'] = criaSelectRelativo(
            'cfg_etiqueta_campo',    
            'etc_campo',               
            'etc_campo',              
            $dados['etc_campo'] ?? '',
            2,                       
            'cfg_etiqueta_campo',      
            [],                        
            $config,
            'etc_campo'
        );

        // Código de barras
        $cod = new MyCampo('cfg_etiqueta_campo', 'etc_codbar');
        $cod->valor       = $dados['etc_codbar'] ?? 'N';
        $cod->selecionado = $cod->valor;
        $cod->opcoes      = $simnao;
        $cod->leitura     = $show;
        $cod->ordem       = $pos;
        $cod->dispForm    = 'col-5';
        $ret['etc_codbar'] = $cod->cr2opcoes();

        // Rótulo
        $rot = new MyCampo('cfg_etiqueta_campo', 'etc_rotulo');
        $rot->valor       = $dados['etc_rotulo'] ?? 'Sem Rótulo';
        $rot->label       = 'Rótulo';
        $rot->obrigatorio = true;
        $rot->leitura     = $show;
        $rot->ordem       = $pos;
        $rot->dispForm    = 'col-4';
        $ret['etc_rotulo'] = $rot->crInput();

        // Caracteres
        $car = new MyCampo('cfg_etiqueta_campo', 'etc_caracteres');
        $car->valor       = $dados['etc_caracteres'] ?? '';
        $car->obrigatorio = true;
        $car->leitura     = $show;
        $car->ordem       = $pos;
        $car->dispForm    = 'col-3';
        $ret['etc_caracteres'] = $car->crInput();

        // Botões
        $add = new MyCampo();
        $add->tipo     = 'button';
        $add->classep  = 'btn-outline-success btn-sm bt-repete';
        $add->i_cone   = "<i class='fas fa-plus'></i>";
        $add->funcChan = "addCampo('".base_url("CfgEtiqueta/addCampo/")."','campos_para_etiqueta',this)";
        $ret['bt_add'] = $add->crBotao();

        // deletar
        $del = new MyCampo();
        $del->tipo     = 'button';
        $del->classep  = 'btn-outline-danger btn-sm bt-exclui';
        $del->i_cone   = "<i class='fas fa-trash'></i>";
        $del->funcChan = "exclui_campo('campos_para_etiqueta',this)";
        $ret['bt_del'] = $del->crBotao();

        return $ret;
    }
}
