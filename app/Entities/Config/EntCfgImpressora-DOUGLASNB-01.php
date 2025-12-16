<?php

namespace App\Entities\Config;

use CodeIgniter\Entity\Entity;
use App\Libraries\MyCampo;

class EntCfgImpressora extends Entity
{
    protected $attributes = [
        'imp_id'       => null,
        'imp_nome'     => null,
        'imp_ip'       => null,
        'imp_porta'    => null,
        'imp_ativo'    => 'A',
        'imp_excluido' => null,
    ];

    protected $dates     = ['imp_excluido'];
    protected $casts     = [];
    public array $campos = [];

    public function __construct(?array $data = null, bool $show = false)
    {
        parent::__construct($data);
        $this->campos = $this->defCampos($show);
    }


    public function defCampos(bool $show = false): array
    {
        $dados = $this->toArray();
        $ret   = [];

        // ID
        $mid = new MyCampo('cfg_impressora', 'imp_id');
        $mid->valor    = $dados['imp_id'] ?? '';
        $ret['imp_id'] = $mid->crOculto();

        // Nome
        $nome = new MyCampo('cfg_impressora', 'imp_nome');
        $nome->valor       = $dados['imp_nome'] ?? '';
        $nome->obrigatorio = true;
        $nome->leitura     = $show;
        $ret['imp_nome']   = $nome->crInput();

        // IP
        $ip = new MyCampo('cfg_impressora', 'imp_ip');
        $ip->tipo        = 'ip';
        $ip->valor       = $dados['imp_ip'] ?? '';
        $ip->largura     = 25;
        $ip->maxLength   = 15;
        $ip->obrigatorio = true;
        $ip->leitura     = $show;
        $ret['imp_ip']   = $ip->crInput();

        // Porta
        $porta = new MyCampo('cfg_impressora', 'imp_porta');
        $porta->valor       = $dados['imp_porta'] ?? '';
        $porta->largura     = 20;
        $porta->maximo      = 10000;
        $porta->obrigatorio = true;
        $porta->leitura     = $show;
        $ret['imp_porta']   = $porta->crInput();

        return $ret;
    }
}
