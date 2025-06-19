<?php

namespace App\Controllers\Ws;

use App\Controllers\BuscasSapiens;
use App\Models\CommonModel;
use App\Models\Config\ConfigPerfilItemModel;
use App\Models\Estoqu\EstoquDepositoModel;
use App\Models\NotificaMonModel;
use App\Models\Produt\ProdutFabricanteModel;
use App\Models\Produt\ProdutFamiliaModel;
use App\Models\Produt\ProdutLoteModel;
use App\Models\Produt\ProdutOrigemModel;
use App\Models\Produt\ProdutProdutoModel;
use CodeIgniter\RESTful\ResourceController;
use DateTime;

class WsCeqweb extends ResourceController
{
    public $data = [];
    public $mode_deposito;
    public $mode_notifica;
    public $busca_sap;
    public $mode_perfil;
    public $mode_origem;
    public $mode_familia;
    public $mode_produto;
    public $mode_fabricante;
    public $mode_lote;
    public $common;

    public function __construct()
    {
        $this->mode_deposito    = new EstoquDepositoModel();
        $this->mode_notifica    = new NotificaMonModel();
        $this->busca_sap        = new BuscasSapiens();
        $this->mode_perfil      = new ConfigPerfilItemModel();
        $this->mode_origem      = new ProdutOrigemModel();
        $this->mode_familia     = new ProdutFamiliaModel();
        $this->mode_produto     = new ProdutProdutoModel();
        $this->mode_fabricante  = new ProdutFabricanteModel();
        $this->mode_lote        = new ProdutLoteModel();
        $this->common           = new CommonModel();
        helper('funcoes');
    }

    public function SapDeposito($tipo, $idDeposito)
    {
        // se o tipo for 'I' // Inclusão
        if ($tipo == 'I' || $tipo == 'A') {
            // Cria uma notificação avisando que foi incluído um novo depósito
            //Ao clicar na notificação, o usuário será redirecionado para a tela de Consulta do Depósito 
            //com o ID do Depósito incluído
            $msgsocket = '';
            if ($tipo == 'I') {
                $msgsocket  = "O Depósito " . $idDeposito . " foi incluído!";
            } else {
                $depos = $this->mode_deposito->getDeposito($idDeposito);
                if ($depos) {
                    $deposito = $depos[0]['dep_desDep'];
                    $msgsocket  = "O Depósito " . $deposito . " foi alterado!";
                }
            }
            if ($msgsocket != '') {
                $this->gravaNotifica('Estoque\Deposito', $idDeposito, $msgsocket, $tipo);
            }
            // Chama o método Integra da Classe Depósito para atualizar a tabela de depósitos local
            $this->integraDeposito();
        } else if ($tipo == 'E') {
            $depos = $this->mode_deposito->getDeposito($idDeposito);
            if ($depos) {
                $deposito = $depos[0]['dep_desDep'];
                $msgsocket  = "O Depósito " . $deposito . " foi excluído no Sapiens!";
                // Cria uma notificação avisando que foi incluído um novo depósito
                //Ao clicar na notificação, o usuário será redirecionado para a tela de Consulta do Depósito 
                //com o ID do Depósito incluído
                $this->gravaNotifica('Estoque\Deposito', $idDeposito, $msgsocket, $tipo);
            }
        }
        cache()->clean();
        return $this->respond(200);
    }

    public function SapProduto($tipo, $codEmp, $codPro, $codOri)
    {
        // VERIFICA SE A ORIGEM INTERESSA PARA O CEQWEB
        log_message('info', 'Parametros: ' . $tipo . ' - ' . $codEmp . ' - ' . $codPro . ' - ' . $codOri);
        $origem = new ProdutOrigemModel();
        $temori = $origem->getOrigem($codOri);
        log_message('info', 'tem Origem: ' . json_encode($temori));
        if ($temori) {
            // se o tipo for 'I' // Inclusão
            if ($tipo == 'I' || $tipo == 'A') {
                // Cria uma notificação avisando que foi incluído um novo Produto
                //Ao clicar na notificação, o usuário será redirecionado para a tela de Consulta do Produto 
                //com o ID do Produto incluído
                // Chama o método Integra para atualizar a tabela local
                $this->integraProduto($codEmp, $codPro);
                $msgsocket = '';
                if ($tipo == 'I') {
                    $msgsocket  = "O Produto " . $codPro . " foi incluído!";
                } else {
                    $prods = $this->mode_produto->getProdutoCod($codPro);
                    // debug($prods, true);
                    if ($prods) {
                        $produto = $prods[0]['pro_despro'];
                        $msgsocket  = "O Produto " . $produto . " foi alterado!";
                    }
                }
                // debug('MsgSocket '.$msgsocket);
                if ($msgsocket != '') {
                    $gravaNotifica = $this->gravaNotifica('Produto\Produto', $codPro, $msgsocket, $tipo);
                    var_dump($gravaNotifica);
                }
            } else if ($tipo == 'E') {
                $prods = $this->mode_produto->getProduto($codPro);
                if ($prods) {
                    $produto = $prods[0]['pro_desDep'];
                    $msgsocket  = "O Produto " . $produto . " foi excluído no Sapiens!";
                    // Cria uma notificação avisando que foi incluído um novo Produto
                    //Ao clicar na notificação, o usuário será redirecionado para a tela de Consulta do Produto 
                    //com o ID do Produto incluído
                    $this->gravaNotifica('Produto\Produto', $codPro, $msgsocket, $tipo);
                }
            }
            cache()->clean();
        }
        return $this->respond(200);
    }

    public function SapLote($tipo, $codOri, $codBar, $codErp, $codLot, $datVal)
    {
        log_message('info', 'Parametros: ' . $tipo . ' - ' . $codOri . ' - ' . $codBar . ' - ' . $codErp . ' - ' . $codLot . ' - ' . $datVal);
        // VERIFICA SE A ORIGEM INTERESSA PARA O CEQWEB
        $origem = new ProdutOrigemModel();
        $temori = $origem->getOrigem($codOri);
        log_message('info', 'tem Origem: ' . json_encode($temori));
        if ($temori) {
            // se o tipo for 'I' // Inclusão
            if ($tipo == 'I' || $tipo == 'A') {
                $micro = $this->mode_produto->getProdutoCod($codErp);
                $status = 9;
                if (isset($micro[0]['cla_micro']) && $micro[0]['cla_micro'] == 'S') {
                    $status = 8;
                }
                // Chama o método Integra para atualizar a tabela local
                // $integra = $this->integraLote($codBar, $codErp, $codLot, $datVal, $status);
                $hoje = new DateTime();
                $sql_lote = [
                    'lot_codbar'    => $codBar,
                    'lot_codpro'    => $codErp,
                    'lot_lote'      => $codLot,
                    'lot_entrada'   => $hoje->format('Y-m-d H:i'),
                    'lot_validade'  => $datVal,
                    'stt_id'        => $status
                ];
                log_message('info', 'SQL Lote: ' . json_encode($sql_lote));
                if ($this->mode_lote->insert($sql_lote)) {
                    // Cria uma notificação avisando que foi incluído um novo Lote
                    //Ao clicar na notificação, o usuário será redirecionado para a tela de Consulta de Lotes
                    //com o ID do Lote incluído
                    $msgsocket = '';
                    if ($tipo == 'I') {
                        $msgsocket  = "O Lote $codLot do Produto $codErp foi incluído!";
                    }
                    log_message('info', 'Msg Socket: ' . $msgsocket);
                    // debug('MsgSocket '.$msgsocket);
                    if ($msgsocket != '') {
                        $gravaNotifica = $this->gravaNotifica('Produto\Lote', $codLot, $msgsocket, $tipo);
                        // var_dump($gravaNotifica);
                    }
                }
            }
            // cache()->clean();
        }
        return $this->respond(200);
    }


    /**
     * integraDeposito
     */
    public function integraDeposito()
    {
        $r_deps = $this->busca_sap->buscaDepositos();
        // debug($r_deps, true);
        log_message('info', 'Depósitos Retornados ' . json_encode($r_deps));
        $deposs = [];
        for ($d = 0; $d < count($r_deps); $d++) {
            $dep = $r_deps[$d];
            $deps['dep_desDep'] = $dep->desDep;
            $deps['dep_codDep'] = $dep->codDep;
            $deps['dep_aceNeg'] = $dep->aceNeg;
            $deps['dep_codDescricao'] = $dep->codDescricao;
            $tem = $this->mode_deposito->getDeposito($dep->codDep);
            if ($tem) {
                $this->mode_deposito->save($deps);
            } else {
                $this->mode_deposito->insert($deps);
            }
        }
    }

    /**
     * integraProduto
     */
    public function integraProduto($codEmp = '1', $codPro = '')
    {
        // $r_pros = $this->busca_sap->buscaProduto($codEmp, $codPro);
        // if ($r_pros) {
        //     log_message('info', 'Produtos Retornados ' . json_encode($r_pros));
        //     if ($codPro != '') {
        //         $pro = $r_pros;
        //         $pros['pro_codemp'] = $pro->codemp;
        //         $pros['pro_codpro'] = $pro->codpro;
        //         $pros['pro_despro'] = $pro->despro;
        //         $pros['ori_codOri'] = $pro->codori;
        //         $pros['fam_codFam'] = $pro->codfam;
        //         $pros['pro_cplpro'] = $pro->cplpro;
        //         $pros['pro_ctrlot'] = $pro->ctrlot;
        //         $pros['pro_qtdemb'] = $pro->qtdemb;
        //         $tem = $this->mode_produto->getProdutoCod($pro->codpro);
        //         if ($tem) {
        //             log_message('info', 'ja tem ' . json_encode($tem));
        //             $id = $tem[0]['pro_id'];
        //             $this->mode_produto->update($id, $pros);
        //         } else {
        //             log_message('info', 'não tem ' . json_encode($pros));
        //             $this->mode_produto->insert($pros);
        //         }
        //         $this->integraProdutoFabricante($pro->codemp, $pro->codpro);
        //     } else {
        //         for ($p = 0; $p < count($r_pros); $p++) {
        //             // $pro = $r_pros[$p];
        //             $pro = $r_pros[$p];
        //             // debug($pro, TRUE);
        //             $pros['pro_codemp'] = $pro->codemp;
        //             $pros['pro_codpro'] = $pro->codpro;
        //             $pros['pro_despro'] = $pro->despro;
        //             $pros['ori_codOri'] = $pro->codori;
        //             $pros['fam_codFam'] = $pro->codfam;
        //             $pros['pro_cplpro'] = $pro->cplpro;
        //             $pros['pro_ctrlot'] = $pro->ctrlot;
        //             $pros['pro_qtdemb'] = $pro->qtdemb;
        //             $tem = $this->mode_produto->getProdutoCod($pro->codpro);
        //             if ($tem) {
        //                 log_message('info', 'ja tem ' . json_encode($tem));
        //                 $id = $tem[0]['pro_id'];
        //                 $this->mode_produto->update($id, $pros);
        //             } else {
        //                 log_message('info', 'não tem ' . json_encode($pros));
        //                 $this->mode_produto->insert($pros);
        //             }
        //             $this->integraProdutoFabricante($pro->codemp, $pro->codpro);
        //         }
        //     }
        // }

        $r_pros = $this->busca_sap->buscaProduto($codEmp, $codPro);

        if ($r_pros) {
            log_message('info', 'Produtos Retornados: ' . json_encode($r_pros));

            // Transforma resultado em array se for objeto único
            if (!is_array($r_pros)) {
                $r_pros = [$r_pros];
            }

            $codigos = array_map(fn($p) => $p->codpro, $r_pros);
            $existentes = $this->mode_produto->getProdutoCodLista($codigos);

            // Indexa produtos existentes por código para facilitar comparação
            $existentesIndexados = [];
            foreach ($existentes as $e) {
                $existentesIndexados[$e['pro_codpro']] = $e;
            }

            $dadosInsert = [];
            $dadosUpdate = [];
            $produtosParaIntegrar = [];

            foreach ($r_pros as $pro) {
                $dados = [
                    'pro_codemp'  => $pro->codemp,
                    'pro_codpro'  => $pro->codpro,
                    'pro_despro'  => $pro->despro,
                    'ori_codOri'  => $pro->codori,
                    'fam_codFam'  => $pro->codfam,
                    'pro_cplpro'  => $pro->cplpro,
                    'pro_ctrlot'  => $pro->ctrlot,
                    'pro_qtdemb'  => $pro->qtdemb
                ];

                if (isset($existentesIndexados[$pro->codpro])) {
                    $dados['pro_id'] = $existentesIndexados[$pro->codpro]['pro_id'];
                    $dadosUpdate[] = $dados;

                    log_message('info', 'Atualizando Produto: ' . json_encode($dados));
                    envia_msg_ws('WsCeqweb', 'Atualizando Produto: ' . $pro->codpro, 'MsgServer', session()->get('usu_id'), 1);
                } else {
                    $dadosInsert[] = $dados;

                    log_message('info', 'Produto novo: ' . json_encode($dados));
                    envia_msg_ws('WsCeqweb', 'Inserindo Produto: ' . $pro->codpro, 'MsgServer', session()->get('usu_id'), 1);
                }

                $produtosParaIntegrar[] = [
                    'codemp' => $pro->codemp,
                    'codpro' => $pro->codpro
                ];
            }

            if (!empty($dadosInsert)) {
                $this->mode_produto->insertBatch($dadosInsert);
            }

            if (!empty($dadosUpdate)) {
                $this->mode_produto->updateBatch($dadosUpdate, 'pro_id');
            }

            // Chamada única para integrar todos os produtos
            $this->integraProdutoFabricanteLote($produtosParaIntegrar); // nova versão que aceita array
        }
    }

    /**
     * integraLotesBusca
     */
    public function integraLotesBusca()
    {
        $ultimo = $this->mode_lote->getUltimoLote();
        // debug($ultimo, true);
        if (!isset($ultimo[0]['lot_codbar'])) {
            $ultimo[0]['lot_codbar'] = '';
        }
        // $ultimo[0]['lot_codbar'] = '';
        // debug($ultimo[0]['lot_codbar'], true);
        log_message('info', 'Ultimo Lote ' . $ultimo[0]['lot_codbar']);
        log_message('info', 'Início do BuscaLotes ' . date('d/m/Y H:i:s'));
        $r_lots = $this->busca_sap->buscaLotes($ultimo[0]['lot_codbar']);
        log_message('info', 'Final do BuscaLotes ' . date('d/m/Y H:i:s'));
        if ($r_lots) {
            // debug(gettype($r_lots));
            if (!is_array($r_lots)) {
                $a_lots[0] = (array)$r_lots;
                $r_lots = $a_lots;
                // debug($r_lots);
            }
            log_message('info', 'Lotes Retornados ' . json_encode($r_lots));
            log_message('info', 'Total de Lotes  ' . count($r_lots));
            $origem = new ProdutOrigemModel();
            for ($d = 0; $d < count($r_lots); $d++) {
                $lot = $r_lots[$d];
                // debug($lot);
                log_message('info', 'Contador de Lote  ' . $d);
                log_message('info', 'Lote  ' . json_encode($lot));
                $lcodori = (isset($lot->codori) ? $lot->codori : $lot['codori']);
                // debug($lcodori);
                $temori = $origem->getOrigem($lcodori);
                if ($temori) {
                    $lcodpro = (isset($lot->codpro) ? $lot->codpro : $lot['codpro']);
                    // debug($lcodpro);
                    $micro = $this->mode_produto->getProdutoCod($lcodpro);
                    // debug($micro);
                    $status = 9;
                    if (isset($micro[0]['stt_disponivel']) && $micro[0]['stt_disponivel'] == 'S') {
                        if (isset($micro[0]['cla_micro']) && $micro[0]['cla_micro'] == 'S') {
                            $status = 8;
                            if (isset($micro[0]['stt_id']) && $micro[0]['stt_id'] == 1) {
                                $status = 8;
                            } else {
                                $status = 9;
                            }
                        } else {
                            $status = 9;
                        }
                    } else {
                        $status = 8;
                    }
                    $lcodbar = (isset($lot->codbar) ? $lot->codbar : $lot['codbar']);
                    $lcodlot = (isset($lot->codlot) ? $lot->codlot : $lot['codlot']);
                    $ldatenv = (isset($lot->datenv) ? $lot->datenv : $lot['datenv']);
                    $ldatval = (isset($lot->datval) ? $lot->datval : $lot['datval']);

                    $lots['lot_codbar']     = $lcodbar;
                    $lots['lot_codpro']     = $lcodpro;
                    $lots['lot_lote']       = $lcodlot;
                    $lots['lot_entrada']    = data_db($ldatenv);
                    $lots['lot_validade']   = data_db($ldatval);
                    $lots['stt_id']         = $status;
                    $tem = $this->mode_lote->getLoteCodbar($lcodbar);
                    if ($tem) {
                        $this->mode_lote->update($tem[0]['lot_id'], $lots);
                    } else {
                        $this->mode_lote->insert($lots);
                    }
                }
            }
        }
    }

    /**
     * integraProdutoFabricante
     */
    public function integraProdutoFabricante($codEmp, $produto)
    {
        $r_prof = $this->busca_sap->buscaProdutoFabricante($codEmp, $produto);
        if ($r_prof) {
            log_message('info', 'Produtos Retornados ' . json_encode($r_prof));
            $pro = $r_prof;
            $prof['pro_codpro'] = $pro->codPro;
            $prof['fab_codfab'] = $pro->codFab;
            $fabatual = $this->mode_produto->getFabricanteProduto($pro->codPro);
            // SÓ INCLUI O FABRICANTE SE NÃO TIVER, OU SE FOR DIFERENTE DO ATUAL
            if (!isset($fabatual[0]['fab_codFab']) || $fabatual[0]['fab_codFab'] != $pro->codFab) {
                // SE EXISTIR E FOR DIFERENTE, PRIMEIRO EXCLUI
                if (isset($fabatual[0]['fab_codFab']) && $fabatual[0]['fab_codFab'] != $pro->codFab) {
                    $this->mode_produto->excluiFabricante($pro->codPro);
                }
                envia_msg_ws('WsCeqweb', 'Atualizando Fabricante do Produto ' . $pro->codPro, 'MsgServer', session()->get('usu_id'), 1);
                $this->common->insertReg('dbProduto', 'pro_sap_prod_fabric', $prof);
            }
        }
    }

    /**
     * integraProdutoFabricanteLote
     */
    public function integraProdutoFabricanteLote(array $produtos)
    {
        if (empty($produtos)) {
            return;
        }

        $codEmp = $produtos[0]['codemp'] ?? null;
        if (!$codEmp) {
            log_message('error', 'Código da empresa não informado');
            envia_msg_ws('WsCeqweb', 'Código da empresa não informado', 'MsgServer', session()->get('usu_id'), 1);
            return;
        }

        // Busca todos os fabricantes dos produtos da empresa
        $r_prof = $this->busca_sap->buscaProdutoFabricante($codEmp);

        if (!$r_prof) {
            log_message('info', 'Nenhum fabricante retornado');
            envia_msg_ws('WsCeqweb', 'Nenhum fabricante retornado para codEmp ' . $codEmp, 'MsgServer', session()->get('usu_id'), 1);
            return;
        }

        log_message('info', 'Fabricantes retornados: ' . json_encode($r_prof));
        // envia_msg_ws('WsCeqweb', 'Fabricantes retornados: ' . json_encode($r_prof), 'MsgServer', session()->get('usu_id'), 1);

        // Indexa os dados retornados pelo código do produto
        $fabricantesNovos = [];
        foreach ($r_prof as $pro) {
            $fabricantesNovos[$pro->codPro] = $pro->codFab;
        }

        $codPros = array_column($produtos, 'codpro');

        // Busca os fabricantes atuais no banco
        $fabricantesAtuais = $this->mode_produto->getFabricanteProdutosArray($codPros);
        // Indexa também por código do produto
        $fabricantesAtuaisIndexados = [];
        foreach ($fabricantesAtuais as $item) {
            $fabricantesAtuaisIndexados[$item['pro_codPro']] = $item['fab_codFab'];
        }

        $dadosParaInserir = [];
        $codigosParaExcluir = [];

        foreach ($fabricantesNovos as $codPro => $codFabNovo) {
            $fabAtual = $fabricantesAtuaisIndexados[$codPro] ?? null;

            // Insere novo se não existir ou se for diferente
            if ($fabAtual !== $codFabNovo) {
                if ($fabAtual) {
                    $codigosParaExcluir[] = $codPro;
                }

                $dadosParaInserir[] = [
                    'pro_codpro' => $codPro,
                    'fab_codfab' => $codFabNovo
                ];

                envia_msg_ws('WsCeqweb', "Atualizando fabricante do produto $codPro", 'MsgServer', session()->get('usu_id'), 1);
            }
        }

        // Remove os que precisam ser substituídos
        if (!empty($codigosParaExcluir)) {
            $this->mode_produto->excluiFabricanteArray($codigosParaExcluir); // adaptar caso necessário
        }

        // Insere os novos ou atualizados em lote
        if (!empty($dadosParaInserir)) {
            $this->common->insertRegBatch('dbProduto', 'pro_sap_prod_fabric', $dadosParaInserir);
        }
    }

    /**
     * integraOrigem
     */
    public function integraOrigem()
    {
        $r_orig = $this->busca_sap->buscaOrigem();
        $orioss = [];
        for ($d = 0; $d < count($r_orig); $d++) {
            $ori = $r_orig[$d];
            $orig['ori_desOri'] = $ori->desOri;
            $orig['ori_codOri'] = $ori->codOri;
            $orig['ori_codDescricao'] = $ori->codDescricao;
            $tem = $this->mode_origem->getOrigem($ori->codOri);
            if ($tem) {
                $this->mode_origem->save($orig);
            } else {
                $this->mode_origem->insert($orig);
            }
        }
    }

    /**
     * integraOrigem
     */
    public function integraFamilia()
    {
        $r_famg = $this->busca_sap->buscaFamilia();
        $famoss = [];
        for ($d = 0; $d < count($r_famg); $d++) {
            $fam = $r_famg[$d];
            $famg['fam_desFam'] = $fam->desFam;
            $famg['fam_codFam'] = $fam->codFam;
            $famg['ori_codOri'] = $fam->codOri;
            $famg['fam_codDescricao'] = $fam->codDescricao;
            $tem = $this->mode_familia->getFamilia($fam->codFam);
            if ($tem) {
                $this->mode_familia->save($famg);
            } else {
                $this->mode_familia->insert($famg);
            }
        }
    }

    /**
     * integraFabricante
     */
    public function integraFabricante()
    {
        $r_fabs = $this->busca_sap->buscaFabricante();
        log_message('info', 'Fabricantes Retornados ' . json_encode($r_fabs));
        for ($d = 0; $d < count($r_fabs); $d++) {
            $fab = $r_fabs[$d];
            $fabs['fab_codFab'] = $fab->codFab;
            $fabs['fab_nomFab'] = $fab->nomFab;
            $fabs['fab_apeFab'] = $fab->apeFab;
            $tem = $this->mode_fabricante->getFabricante($fab->codFab);
            if ($tem) {
                $this->mode_fabricante->save($fabs);
            } else {
                $this->mode_fabricante->insert($fabs);
            }
        }
    }

    function gravaNotifica($controler, $registro, $msg, $tipo)
    {
        $usuario  = 0;
        $userorig = 'Sapiens';
        $pos = strrpos($controler, "\\");
        $nomecontrol = substr($controler, $pos + 1);
        log_message('info', 'Controler ' . $controler);
        log_message('info', 'Posição ' . $pos);
        log_message('info', 'NomeControl ' . $nomecontrol);
        // debug($controler);

        $usuariospermissoes = $this->mode_perfil->getPermissaoTelaUsuario(false, false, false, $nomecontrol);
        log_message('info', 'Usuários ' . json_encode($usuariospermissoes));
        if (count($usuariospermissoes) > 0) {
            $texto = $msg . ' em ' . data_br(date('Y-m-d H:i:s')) . ' por: ' . $userorig;
            for ($up = 0; $up < count($usuariospermissoes); $up++) {
                $usu_dest = $usuariospermissoes[$up]['usu_id'];
                $permissoes = $usuariospermissoes[$up]['pit_permissao'];
                log_message('info', 'Usuario ' . $usuario);
                log_message('info', 'Permissoes ' . $permissoes);
                // debug($usuario);
                // debug($usu_dest);
                if ($usu_dest != $usuario  && str_contains($permissoes, 'N')) { // se não for o mesmo usuário que alterou e o usuário tem permissão de notificação
                    // insere a nova notificação
                    $insNot =  $this->mode_notifica->insertNotifica($controler, $texto, $registro, $usuario, $usu_dest, $tipo);
                    log_message('info', 'insNot ' . var_dump($insNot));
                    // var_dump($insNot);
                    envia_msg_ws($controler, $msg, 'Servidor', $usu_dest, $registro);
                }
            }
        }
        $usuariospermissoes = $this->mode_perfil->getPermissaoTelaUsuario(false, false, false, $nomecontrol);
        if (count($usuariospermissoes) > 0) {
            for ($up = 0; $up < count($usuariospermissoes); $up++) {
                $usuario = $usuariospermissoes[$up];
                log_message('info', 'Usuario ' . $usuario);
                envia_msg_ws($controler, $msg, 'Servidor', $usuario, $registro);
            }
        }

        return (json_encode([]));
    }


    /**
     * integraLote
     */
    public function integraLote($codBar, $codPro, $codLot, $datVal, $status) {}
}
