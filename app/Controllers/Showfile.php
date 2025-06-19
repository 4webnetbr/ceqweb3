<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ArquivoMonModel;

class Showfile extends BaseController {	
    public $arqdb;
    /**
     * Construtor da Classe
     * construct
     */
    public function __construct()
    {
        $this->arqdb       = new ArquivoMonModel();
    }

    public function show($id){
        $dados_files = $this->arqdb->getArquivoId($id);
        // debug($dados_files);
        if(str_contains($dados_files->arq_tipo, 'image')){
            echo "<html><head><title>".$dados_files->arq_nome."</title>";
            echo "<link rel='shortcut icon' href='/assets/images/favicon.png' type='image/x-icon'>";            
            echo "<script>document.addEventListener('contextmenu', event => event.preventDefault())</script>";
            echo "</head>";
            echo "<body>";
            echo "<img src='".$dados_files->arq_conteudo."' style='max-height:100%; max-width:100%;' ></img>";
            echo "</body></html>";
            exit;
            //imagem
        } else if(str_contains($dados_files->arq_tipo, 'pdf')){
        //pdf
            if(isMobile()){
                $base64 = $dados_files->arq_conteudo;
                $decoded = base64_decode($base64);
                $file = 'uploads/files/'.$dados_files->arq_nome;
            
                if (file_exists($file)) {
                    file_put_contents($file, $decoded);
                    header('Content-Description: File Transfer');
                    header("Content-Type: $dados_files->arq_tipo");
                    header('Content-Disposition: attachment; filename="'.basename($file).'"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file));
                    readfile($file);
                    exit;
                }        
            }
            echo "<html><head>";
            echo "<link rel='shortcut icon' href='/assets/images/favicon.ico' type='image/x-icon'>";
            echo "<title>Visualizar Arquivo - ".NAME_APP."</title>";
            echo "</head>";
            echo "<body>";
            echo "Arquivo ".$dados_files->arq_nome;
            echo "<iframe src='".$dados_files->arq_conteudo."#toolbar=0' type='application/pdf' width='100%' height='100%'></iframe>";
            echo "</body>";
            echo "</html>";
        } else {
            $base64 = $dados_files->arq_conteudo;
            $decoded = base64_decode($base64);
            $file = $dados_files->arq_nome;
            file_put_contents($file, $decoded);
            
            if (file_exists($file)) {
                header('Content-Description: File Transfer');
                header("Content-Type: $dados_files->arq_tipo");
                header('Content-Disposition: attachment; filename="'.basename($file).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                readfile($file);
                exit;
            }        
        }
    }
}

