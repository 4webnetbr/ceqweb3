<?php

namespace App\Libraries;

use FPDF;

class MyPdf2025 extends FPDF
{
    protected $orientation;
    protected $size;
    protected $rotation;
    protected $units;
    protected $logo;
    protected $head_title;
    protected $head_subtitle;
    protected $footer_page_literal;
    protected $footer_center;

    //BARCODE
    protected $T128;                                         // Tableau des codes 128
    protected $ABCset = "";                                  // jeu des caractères éligibles au C128
    protected $Aset = "";                                    // Set A du jeu des caractères éligibles
    protected $Bset = "";                                    // Set B du jeu des caractères éligibles
    protected $Cset = "";                                    // Set C du jeu des caractères éligibles
    protected $SetFrom;                                      // Convertisseur source des jeux vers le tableau
    protected $SetTo;                                        // Convertisseur destination des jeux vers le tableau
    protected $JStart = array("A" => 103, "B" => 104, "C" => 105); // Caractères de sélection de jeu au début du C128
    protected $JSwap = array("A" => 101, "B" => 100, "C" => 99);   // Caractères de changement de jeu


    private $base_url;
    private $format;
    private $temheader;
    private $temfooter;
    private $angle;
    private $angulo;
    var $B; // Negrito
    var $I; // Itálico
    var $U; // Sublinhado
    var $HREF; // Hyperlink
    var $ALIGN; // Alinhamento
    var $FONTFAMILY; // Família de fontes
    var $FONTSIZE; // Tamanho da fonte
    var $COLOR; // Cor
    var $TEXTCOLOR; // Cor do texto
    var $BGCOLOR; // Cor de fundo


    function __construct($temheader = true, $temfooter = true, $size = false)
    {
        $config = config('Pdf');
        $this->orientation          =   $config->orientation;
        if (!$size) {
            $this->size                 =   $config->size;
        } else {
            $this->size                 =   $size;
        }
        $this->rotation             =   $config->rotation;
        $this->units                =   $config->units;
        $this->format               =   $config->format;
        $this->head_title           =   $this->format($config->head_title);
        $this->head_subtitle        =   $this->format($config->head_subtitle);
        $this->footer_page_literal  =   $this->format($config->footer_page_literal);

        $this->base_url         =   $config->url_wrapper;
        if ($this->base_url === TRUE)
            $this->logo = base_url($config->logo);
        else
            $this->logo = $config->logo;

        $this->temheader = $temheader;
        $this->temfooter = $temfooter;
        // lets construct the fpdf objet!
        parent::__construct($this->orientation, $this->units, $this->size);

        $this->B = 0;
        $this->I = 0;
        $this->U = 0;
        $this->HREF = '';
        $this->ALIGN = 'left';
        $this->FONTFAMILY = 'Arial';
        $this->FONTSIZE = 10;
        $this->COLOR = array(0, 0, 0);
        $this->TEXTCOLOR = array(0, 0, 0);
        $this->BGCOLOR = array(255, 255, 255);

        $this->T128[] = array(2, 1, 2, 2, 2, 2);           //0 : [ ]               // composition des caractères
        $this->T128[] = array(2, 2, 2, 1, 2, 2);           //1 : [!]
        $this->T128[] = array(2, 2, 2, 2, 2, 1);           //2 : ["]
        $this->T128[] = array(1, 2, 1, 2, 2, 3);           //3 : [#]
        $this->T128[] = array(1, 2, 1, 3, 2, 2);           //4 : [$]
        $this->T128[] = array(1, 3, 1, 2, 2, 2);           //5 : [%]
        $this->T128[] = array(1, 2, 2, 2, 1, 3);           //6 : [&]
        $this->T128[] = array(1, 2, 2, 3, 1, 2);           //7 : [']
        $this->T128[] = array(1, 3, 2, 2, 1, 2);           //8 : [(]
        $this->T128[] = array(2, 2, 1, 2, 1, 3);           //9 : [)]
        $this->T128[] = array(2, 2, 1, 3, 1, 2);           //10 : [*]
        $this->T128[] = array(2, 3, 1, 2, 1, 2);           //11 : [+]
        $this->T128[] = array(1, 1, 2, 2, 3, 2);           //12 : [,]
        $this->T128[] = array(1, 2, 2, 1, 3, 2);           //13 : [-]
        $this->T128[] = array(1, 2, 2, 2, 3, 1);           //14 : [.]
        $this->T128[] = array(1, 1, 3, 2, 2, 2);           //15 : [/]
        $this->T128[] = array(1, 2, 3, 1, 2, 2);           //16 : [0]
        $this->T128[] = array(1, 2, 3, 2, 2, 1);           //17 : [1]
        $this->T128[] = array(2, 2, 3, 2, 1, 1);           //18 : [2]
        $this->T128[] = array(2, 2, 1, 1, 3, 2);           //19 : [3]
        $this->T128[] = array(2, 2, 1, 2, 3, 1);           //20 : [4]
        $this->T128[] = array(2, 1, 3, 2, 1, 2);           //21 : [5]
        $this->T128[] = array(2, 2, 3, 1, 1, 2);           //22 : [6]
        $this->T128[] = array(3, 1, 2, 1, 3, 1);           //23 : [7]
        $this->T128[] = array(3, 1, 1, 2, 2, 2);           //24 : [8]
        $this->T128[] = array(3, 2, 1, 1, 2, 2);           //25 : [9]
        $this->T128[] = array(3, 2, 1, 2, 2, 1);           //26 : [:]
        $this->T128[] = array(3, 1, 2, 2, 1, 2);           //27 : [;]
        $this->T128[] = array(3, 2, 2, 1, 1, 2);           //28 : [<]
        $this->T128[] = array(3, 2, 2, 2, 1, 1);           //29 : [=]
        $this->T128[] = array(2, 1, 2, 1, 2, 3);           //30 : [>]
        $this->T128[] = array(2, 1, 2, 3, 2, 1);           //31 : [?]
        $this->T128[] = array(2, 3, 2, 1, 2, 1);           //32 : [@]
        $this->T128[] = array(1, 1, 1, 3, 2, 3);           //33 : [A]
        $this->T128[] = array(1, 3, 1, 1, 2, 3);           //34 : [B]
        $this->T128[] = array(1, 3, 1, 3, 2, 1);           //35 : [C]
        $this->T128[] = array(1, 1, 2, 3, 1, 3);           //36 : [D]
        $this->T128[] = array(1, 3, 2, 1, 1, 3);           //37 : [E]
        $this->T128[] = array(1, 3, 2, 3, 1, 1);           //38 : [F]
        $this->T128[] = array(2, 1, 1, 3, 1, 3);           //39 : [G]
        $this->T128[] = array(2, 3, 1, 1, 1, 3);           //40 : [H]
        $this->T128[] = array(2, 3, 1, 3, 1, 1);           //41 : [I]
        $this->T128[] = array(1, 1, 2, 1, 3, 3);           //42 : [J]
        $this->T128[] = array(1, 1, 2, 3, 3, 1);           //43 : [K]
        $this->T128[] = array(1, 3, 2, 1, 3, 1);           //44 : [L]
        $this->T128[] = array(1, 1, 3, 1, 2, 3);           //45 : [M]
        $this->T128[] = array(1, 1, 3, 3, 2, 1);           //46 : [N]
        $this->T128[] = array(1, 3, 3, 1, 2, 1);           //47 : [O]
        $this->T128[] = array(3, 1, 3, 1, 2, 1);           //48 : [P]
        $this->T128[] = array(2, 1, 1, 3, 3, 1);           //49 : [Q]
        $this->T128[] = array(2, 3, 1, 1, 3, 1);           //50 : [R]
        $this->T128[] = array(2, 1, 3, 1, 1, 3);           //51 : [S]
        $this->T128[] = array(2, 1, 3, 3, 1, 1);           //52 : [T]
        $this->T128[] = array(2, 1, 3, 1, 3, 1);           //53 : [U]
        $this->T128[] = array(3, 1, 1, 1, 2, 3);           //54 : [V]
        $this->T128[] = array(3, 1, 1, 3, 2, 1);           //55 : [W]
        $this->T128[] = array(3, 3, 1, 1, 2, 1);           //56 : [X]
        $this->T128[] = array(3, 1, 2, 1, 1, 3);           //57 : [Y]
        $this->T128[] = array(3, 1, 2, 3, 1, 1);           //58 : [Z]
        $this->T128[] = array(3, 3, 2, 1, 1, 1);           //59 : [[]
        $this->T128[] = array(3, 1, 4, 1, 1, 1);           //60 : [\]
        $this->T128[] = array(2, 2, 1, 4, 1, 1);           //61 : []]
        $this->T128[] = array(4, 3, 1, 1, 1, 1);           //62 : [^]
        $this->T128[] = array(1, 1, 1, 2, 2, 4);           //63 : [_]
        $this->T128[] = array(1, 1, 1, 4, 2, 2);           //64 : [`]
        $this->T128[] = array(1, 2, 1, 1, 2, 4);           //65 : [a]
        $this->T128[] = array(1, 2, 1, 4, 2, 1);           //66 : [b]
        $this->T128[] = array(1, 4, 1, 1, 2, 2);           //67 : [c]
        $this->T128[] = array(1, 4, 1, 2, 2, 1);           //68 : [d]
        $this->T128[] = array(1, 1, 2, 2, 1, 4);           //69 : [e]
        $this->T128[] = array(1, 1, 2, 4, 1, 2);           //70 : [f]
        $this->T128[] = array(1, 2, 2, 1, 1, 4);           //71 : [g]
        $this->T128[] = array(1, 2, 2, 4, 1, 1);           //72 : [h]
        $this->T128[] = array(1, 4, 2, 1, 1, 2);           //73 : [i]
        $this->T128[] = array(1, 4, 2, 2, 1, 1);           //74 : [j]
        $this->T128[] = array(2, 4, 1, 2, 1, 1);           //75 : [k]
        $this->T128[] = array(2, 2, 1, 1, 1, 4);           //76 : [l]
        $this->T128[] = array(4, 1, 3, 1, 1, 1);           //77 : [m]
        $this->T128[] = array(2, 4, 1, 1, 1, 2);           //78 : [n]
        $this->T128[] = array(1, 3, 4, 1, 1, 1);           //79 : [o]
        $this->T128[] = array(1, 1, 1, 2, 4, 2);           //80 : [p]
        $this->T128[] = array(1, 2, 1, 1, 4, 2);           //81 : [q]
        $this->T128[] = array(1, 2, 1, 2, 4, 1);           //82 : [r]
        $this->T128[] = array(1, 1, 4, 2, 1, 2);           //83 : [s]
        $this->T128[] = array(1, 2, 4, 1, 1, 2);           //84 : [t]
        $this->T128[] = array(1, 2, 4, 2, 1, 1);           //85 : [u]
        $this->T128[] = array(4, 1, 1, 2, 1, 2);           //86 : [v]
        $this->T128[] = array(4, 2, 1, 1, 1, 2);           //87 : [w]
        $this->T128[] = array(4, 2, 1, 2, 1, 1);           //88 : [x]
        $this->T128[] = array(2, 1, 2, 1, 4, 1);           //89 : [y]
        $this->T128[] = array(2, 1, 4, 1, 2, 1);           //90 : [z]
        $this->T128[] = array(4, 1, 2, 1, 2, 1);           //91 : [{]
        $this->T128[] = array(1, 1, 1, 1, 4, 3);           //92 : [|]
        $this->T128[] = array(1, 1, 1, 3, 4, 1);           //93 : [}]
        $this->T128[] = array(1, 3, 1, 1, 4, 1);           //94 : [~]
        $this->T128[] = array(1, 1, 4, 1, 1, 3);           //95 : [DEL]
        $this->T128[] = array(1, 1, 4, 3, 1, 1);           //96 : [FNC3]
        $this->T128[] = array(4, 1, 1, 1, 1, 3);           //97 : [FNC2]
        $this->T128[] = array(4, 1, 1, 3, 1, 1);           //98 : [SHIFT]
        $this->T128[] = array(1, 1, 3, 1, 4, 1);           //99 : [Cswap]
        $this->T128[] = array(1, 1, 4, 1, 3, 1);           //100 : [Bswap]                
        $this->T128[] = array(3, 1, 1, 1, 4, 1);           //101 : [Aswap]
        $this->T128[] = array(4, 1, 1, 1, 3, 1);           //102 : [FNC1]
        $this->T128[] = array(2, 1, 1, 4, 1, 2);           //103 : [Astart]
        $this->T128[] = array(2, 1, 1, 2, 1, 4);           //104 : [Bstart]
        $this->T128[] = array(2, 1, 1, 2, 3, 2);           //105 : [Cstart]
        $this->T128[] = array(2, 3, 3, 1, 1, 1);           //106 : [STOP]
        $this->T128[] = array(2, 1);                       //107 : [END BAR]

        for ($i = 32; $i <= 95; $i++) {                                            // jeux de caractères
            $this->ABCset .= chr($i);
        }
        $this->Aset = $this->ABCset;
        $this->Bset = $this->ABCset;

        for ($i = 0; $i <= 31; $i++) {
            $this->ABCset .= chr($i);
            $this->Aset .= chr($i);
        }
        for ($i = 96; $i <= 127; $i++) {
            $this->ABCset .= chr($i);
            $this->Bset .= chr($i);
        }
        for ($i = 200; $i <= 210; $i++) {                                           // controle 128
            $this->ABCset .= chr($i);
            $this->Aset .= chr($i);
            $this->Bset .= chr($i);
        }
        $this->Cset = "0123456789" . chr(206);

        for ($i = 0; $i < 96; $i++) {                                                   // convertisseurs des jeux A & B
            @$this->SetFrom["A"] .= chr($i);
            @$this->SetFrom["B"] .= chr($i + 32);
            @$this->SetTo["A"] .= chr(($i < 32) ? $i + 64 : $i - 32);
            @$this->SetTo["B"] .= chr($i);
        }
        for ($i = 96; $i < 107; $i++) {                                                 // contrôle des jeux A & B
            @$this->SetFrom["A"] .= chr($i + 104);
            @$this->SetFrom["B"] .= chr($i + 104);
            @$this->SetTo["A"] .= chr($i);
            @$this->SetTo["B"] .= chr($i);
        }
    }

    /**
     * header function
     *
     * @param none
     * @return none
     **/
    function header()
    {
        if ($this->temheader) {

            $this->Image($this->logo, 11, 10, 20);
            $this->SetFont('Arial', '', 9);
            $this->Ln(10);
        }
    }

    /**
     * footer function
     *
     * @param none
     * @return none
     **/
    function footer()
    {
        if ($this->temfooter) {
            $this->SetY(-10);
            $this->SetFont('Arial', '', 8);
            $this->Cell(80, 3, utf8_decode('R. Professor Alfredo Valente, 1158 - Jd Gramados - Alm. Tamandaré - CEP 83.504-000'), 0, 0, 'L');
            $this->Cell(0, 3, utf8_decode('+55 41 3657-7755'), 0, 0, 'C');
            // $this->Cell(0,3,utf8_decode('E-mail: pelegrini@pelegrini.ind.br'),0,1,'R');
            // $this->Cell(0,0,utf8_decode('Artefatos de Metais Pelegrini Ltda'),0,1,'L');
            // $this->Cell(0,0,$this->footer_center,0,0,'C');
            $this->Cell(0, 3, "{$this->footer_page_literal} " . $this->PageNo() . '/{nb}', 0, 0, 'R');
        }
    }

    /**
     * logo getter
     *
     * @param none
     * @return string
     **/
    function get_logo()
    {
        return $this->logo;
    }

    /**
     * orientation getter
     *
     * @param none
     * @return string
     **/
    function get_orientation()
    {
        return $this->orientation;
    }

    /**
     * size getter
     *
     * @param none
     * @return string
     **/
    function get_size()
    {
        return $this->size;
    }

    /**
     * rotation getter
     *
     * @param none
     * @return int
     **/
    function get_rotation()
    {
        return $this->rotation;
    }

    /**
     * units getter
     *
     * @param none
     * @return string
     **/
    function get_units()
    {
        return $this->units;
    }

    /**
     * Head title getter
     *
     * @param none
     * @return string
     **/
    function get_head_title()
    {
        return $this->head_title;
    }

    /**
     * Head subtitle getter
     *
     * @param none
     * @return string
     **/
    function get_head_subtitle()
    {
        return $this->head_subtitle;
    }

    /**
     * Footer center set
     *
     * @param none
     * @return string
     **/
    function SetFooterCenter($footcenter)
    {
        $this->footer_center = $footcenter;
    }

    /**
     * addpage function
     *
     * @param string
     * @param mixed
     * @param int
     * @return void
     **/

    function Add_Page($orientation = NULL, $size = NULL, $rotation = NULL)
    {
        if (is_null($orientation))
            $orientation = $this->orientation;
        else
            $this->orientation = $orientation;

        if (is_null($size))
            $size = $this->size;
        else
            $this->size = $size;

        if (is_null($rotation))
            $rotation = $this->rotation;
        else
            $this->rotation = $rotation;

        $this->AddPage($this->orientation, $this->size, $this->rotation);
    }

    /**
     * render function
     *
     * @param string
     * @param string
     * @param bool
     * @return void
     *
     * Behaviour:
     * dest,             indicates where send the documment. It can bo one of following
     *                   'I': send the file inline to the browser. The PDF viewer is used if available.
     *                   'D': send to the browser and force a file download with the name given by name.
     *                   'F': save to a local file with the name given by name (may include a path).
     *                   'S': return the document as a string.
     *
     * name,             The name of the file. It is ignored in case of destination S.
     *                   The default value is doc.pdf.
     *
     * $this->format,    Indicates if name is encoded in ISO-8859-1 (false) or UTF-8 (true).
     *                   Only used for destinations I and D.
     *                   The default value is false.
     **/
    function render($dest = 'I', $name = 'document.pdf')
    {
        $this->Output($dest, $name, $this->format);
    }


    /**
     * format function
     *
     * @param string
     * @return string
     **/
    function format($str)
    {
        return utf8_decode($str);
    }

    /**
     * imageprop function
     *
     * @param string
     * @param mixed
     * @param int
     * @return void
     **/

    function ImageProp($image, $x, $y, $w, $h)
    {
        list($width, $height) = getimagesize($image);

        // Calculando a proporção 
        $ratio_orig = $width / $height;

        $worig = $w;
        $horig = $h;

        if ($width >= $height) {
            $hn = $h + 10;
            while ($hn > $h) {
                $hn = $w / $ratio_orig;
                if ($hn > $h) {
                    $w = $w - 1;
                }
            }
            $h = $hn;
        } else {
            $wn = $w + 10;
            while ($wn > $w) {
                $wn = $h * $ratio_orig;
                if ($wn > $w) {
                    $h = $h - 1;
                }
            }
            $w = $wn;
        }
        // acha o centro
        $dif = $worig - $w;
        $x = $x + ($dif / 2);

        $dify = $horig - $h;
        $y = $y + ($dify / 2);

        $this->Image($image, $x, $y, $w, $h);

        $this->setY($y + $h + 1);
    }

    function EtiqTexto($etiq, $texto, $font, $tamfont, $h, $w, $border = 0, $ln = 0, $align = 'L', $preenche = 0, $negita = '')
    {
        $this->SetFont($font, 'B', $tamfont);
        $x = $this->GetX();
        if (strlen($etiq) > 0) {
            if (strlen($texto) > 0) {
                $alignetiq = 'L';
            } else {
                $alignetiq = $align;
            }
            $this->Cell($w, $h, formata_texto($etiq), $border, 0, $alignetiq, $preenche);
            if ($w == 0) {

                $x = $x + (strlen(formata_texto($etiq)) * 2) + 2;
                $this->SetX($x);
            }
        }
        // $x += $w;
        $this->SetFont($font, $negita, $tamfont);
        if (strlen(formata_texto($texto)) > 0) {
            // $wt = $w;
            // if($w == 0){
            //     $wt = (strlen(formata_texto($texto)) * 2) + 15;
            // }
            // $x += $wt;
            $this->Cell($w, $h, formata_texto($texto), $border, $ln, $align, $preenche);
            if ($w == 0  && $ln == 0) {
                $x = $x + (strlen(formata_texto($texto)) * 2) + 10;
                $this->SetX($x);
            }
        } else if ($ln > 0) {
            $this->Cell(0, $h, '', $border, $ln, $align, $preenche);
        }
        // debug($etiq.' '.$x);
        // if($ln > 0){
        //     $x = 10;
        // }
        // $this->SetX($x);
    }


    // Função que interpreta o HTML e escreve no PDF
    function WriteHTML($html)
    {
        // Remove quebras de linha
        $html = str_replace("\n", ' ', $html);
        // Separa o HTML em um array onde os elementos são textos ou tags
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                // Texto
                if ($this->HREF)
                    $this->PutLink($this->HREF, $e);
                else
                    $this->Write(5, $e);
            } else {
                // Tag
                // Se for tag de fechamento
                if ($e[0] == '/')
                    $this->CloseTag(strtoupper(substr($e, 1)));
                else {
                    // Separa a tag dos atributos
                    $a2 = explode(' ', $e);
                    $tag = strtoupper(array_shift($a2));
                    $attr = array();
                    // Lê os atributos, se existirem
                    foreach ($a2 as $v) {
                        if (preg_match('/([^=]*)=["\']?([^"\']*)["\']?/', $v, $a3))
                            $attr[strtoupper($a3[1])] = $a3[2];
                    }
                    $this->OpenTag($tag, $attr);
                }
            }
        }
    }

    // Função para abrir uma tag e aplicar seus atributos/estilos
    function OpenTag($tag, $attr)
    {
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, true);
        if ($tag == 'A')
            $this->HREF = isset($attr['HREF']) ? $attr['HREF'] : '';
        if ($tag == 'BR')
            $this->Ln(5);
    }

    // Função para fechar uma tag e retirar os estilos aplicados
    function CloseTag($tag)
    {
        if ($tag == 'B' || $tag == 'I' || $tag == 'U')
            $this->SetStyle($tag, false);
        if ($tag == 'A')
            $this->HREF = '';

        if ($tag == 'P') {
            $this->Ln(5);
        }
    }

    // Função para alterar o estilo de fonte (negrito, itálico, sublinhado)
    function SetStyle($tag, $enable)
    {
        // Atualiza o contador do estilo
        $this->$tag += ($enable ? 1 : -1);
        $style = '';
        if ($this->B > 0)
            $style .= 'B';
        if ($this->I > 0)
            $style .= 'I';
        if ($this->U > 0)
            $style .= 'U';
        $this->SetFont('', $style);
    }

    // Função para inserir um hyperlink
    function PutLink($URL, $txt)
    {
        // Cor azul para links
        $this->SetTextColor(0, 0, 255);
        // Sublinha o link
        $this->SetStyle('U', true);
        // Escreve o texto e cria o link
        $this->Write(5, $txt, $URL);
        // Restaura o estilo
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }


    //________________ Fonction encodage et dessin du code 128 _____________________
    function Code128($x, $y, $code, $w, $h)
    {
        $Aguid = "";                                                                      // Création des guides de choix ABC
        $Bguid = "";
        $Cguid = "";
        for ($i = 0; $i < strlen($code); $i++) {
            $needle = substr($code, $i, 1);
            $Aguid .= ((strpos($this->Aset, $needle) === false) ? "N" : "O");
            $Bguid .= ((strpos($this->Bset, $needle) === false) ? "N" : "O");
            $Cguid .= ((strpos($this->Cset, $needle) === false) ? "N" : "O");
        }

        $SminiC = "OOOO";
        $IminiC = 4;

        $crypt = "";
        while ($code > "") {
            // BOUCLE PRINCIPALE DE CODAGE
            $i = strpos($Cguid, $SminiC);                                                // forçage du jeu C, si possible
            if ($i !== false) {
                $Aguid[$i] = "N";
                $Bguid[$i] = "N";
            }

            if (substr($Cguid, 0, $IminiC) == $SminiC) {                                  // jeu C
                $crypt .= chr(($crypt > "") ? $this->JSwap["C"] : $this->JStart["C"]);  // début Cstart, sinon Cswap
                $made = strpos($Cguid, "N");                                             // étendu du set C
                if ($made === false) {
                    $made = strlen($Cguid);
                }
                if (fmod($made, 2) == 1) {
                    $made--;                                                            // seulement un nombre pair
                }
                for ($i = 0; $i < $made; $i += 2) {
                    $crypt .= chr(strval(substr($code, $i, 2)));                          // conversion 2 par 2
                }
                $jeu = "C";
            } else {
                $madeA = strpos($Aguid, "N");                                            // étendu du set A
                if ($madeA === false) {
                    $madeA = strlen($Aguid);
                }
                $madeB = strpos($Bguid, "N");                                            // étendu du set B
                if ($madeB === false) {
                    $madeB = strlen($Bguid);
                }
                $made = (($madeA < $madeB) ? $madeB : $madeA);                         // étendu traitée
                $jeu = (($madeA < $madeB) ? "B" : "A");                                // Jeu en cours

                $crypt .= chr(($crypt > "") ? $this->JSwap[$jeu] : $this->JStart[$jeu]); // début start, sinon swap

                $crypt .= strtr(substr($code, 0, $made), $this->SetFrom[$jeu], $this->SetTo[$jeu]); // conversion selon jeu

            }
            $code = substr($code, $made);                                           // raccourcir légende et guides de la zone traitée
            $Aguid = substr($Aguid, $made);
            $Bguid = substr($Bguid, $made);
            $Cguid = substr($Cguid, $made);
        }                                                                          // FIN BOUCLE PRINCIPALE

        $check = ord($crypt[0]);                                                   // calcul de la somme de contrôle
        for ($i = 0; $i < strlen($crypt); $i++) {
            $check += (ord($crypt[$i]) * $i);
        }
        $check %= 103;

        $crypt .= chr($check) . chr(106) . chr(107);                               // Chaine cryptée complète

        $i = (strlen($crypt) * 11) - 8;                                            // calcul de la largeur du module
        $modul = $w / $i;

        for ($i = 0; $i < strlen($crypt); $i++) {                                      // BOUCLE D'IMPRESSION
            $c = $this->T128[ord($crypt[$i])];
            for ($j = 0; $j < count($c); $j++) {
                $this->Rect($x, $y, $c[$j] * $modul, $h, "F");
                $x += ($c[$j++] + $c[$j]) * $modul;
            }
        }
    }

    function Code39($x, $y, $code, $ext = true, $cks = false, $w = 0.4, $h = 20, $wide = true)
    {

        //Imprime o codigo abaixo do codbar
        //Display code
        //$this->SetFont('Arial', '', 10);
        //$this->Text($x, $y+$h+4, $code);

        if ($ext) {
            //Extended encoding
            $code = $this->encode_code39_ext($code);
        } else {
            //Convert to upper case
            $code = strtoupper($code);
            //Check validity
            if (!preg_match('|^[0-9A-Z. $/+%-]*$|', $code))
                $this->Error('Invalid barcode value: ' . $code);
        }

        //Compute checksum
        if ($cks)
            $code .= $this->checksum_code39($code);

        //Add start and stop characters
        $code = '*' . $code . '*';

        //Conversion tables
        $narrow_encoding = array(
            '0' => '101001101101',
            '1' => '110100101011',
            '2' => '101100101011',
            '3' => '110110010101',
            '4' => '101001101011',
            '5' => '110100110101',
            '6' => '101100110101',
            '7' => '101001011011',
            '8' => '110100101101',
            '9' => '101100101101',
            'A' => '110101001011',
            'B' => '101101001011',
            'C' => '110110100101',
            'D' => '101011001011',
            'E' => '110101100101',
            'F' => '101101100101',
            'G' => '101010011011',
            'H' => '110101001101',
            'I' => '101101001101',
            'J' => '101011001101',
            'K' => '110101010011',
            'L' => '101101010011',
            'M' => '110110101001',
            'N' => '101011010011',
            'O' => '110101101001',
            'P' => '101101101001',
            'Q' => '101010110011',
            'R' => '110101011001',
            'S' => '101101011001',
            'T' => '101011011001',
            'U' => '110010101011',
            'V' => '100110101011',
            'W' => '110011010101',
            'X' => '100101101011',
            'Y' => '110010110101',
            'Z' => '100110110101',
            '-' => '100101011011',
            '.' => '110010101101',
            ' ' => '100110101101',
            '*' => '100101101101',
            '$' => '100100100101',
            '/' => '100100101001',
            '+' => '100101001001',
            '%' => '101001001001'
        );

        $wide_encoding = array(
            '0' => '101000111011101',
            '1' => '111010001010111',
            '2' => '101110001010111',
            '3' => '111011100010101',
            '4' => '101000111010111',
            '5' => '111010001110101',
            '6' => '101110001110101',
            '7' => '101000101110111',
            '8' => '111010001011101',
            '9' => '101110001011101',
            'A' => '111010100010111',
            'B' => '101110100010111',
            'C' => '111011101000101',
            'D' => '101011100010111',
            'E' => '111010111000101',
            'F' => '101110111000101',
            'G' => '101010001110111',
            'H' => '111010100011101',
            'I' => '101110100011101',
            'J' => '101011100011101',
            'K' => '111010101000111',
            'L' => '101110101000111',
            'M' => '111011101010001',
            'N' => '101011101000111',
            'O' => '111010111010001',
            'P' => '101110111010001',
            'Q' => '101010111000111',
            'R' => '111010101110001',
            'S' => '101110101110001',
            'T' => '101011101110001',
            'U' => '111000101010111',
            'V' => '100011101010111',
            'W' => '111000111010101',
            'X' => '100010111010111',
            'Y' => '111000101110101',
            'Z' => '100011101110101',
            '-' => '100010101110111',
            '.' => '111000101011101',
            ' ' => '100011101011101',
            '*' => '100010111011101',
            '$' => '100010001000101',
            '/' => '100010001010001',
            '+' => '100010100010001',
            '%' => '101000100010001'
        );

        $encoding = $wide ? $wide_encoding : $narrow_encoding;

        //Inter-character spacing
        $gap = ($w > 0.29) ? '00' : '0';

        //Convert to bars
        $encode = '';
        for ($i = 0; $i < strlen($code); $i++)
            $encode .= $encoding[$code[$i]] . $gap;

        //Draw bars
        $this->draw_code39($encode, $x, $y, $w, $h);
    }

    function checksum_code39($code)
    {

        //Compute the modulo 43 checksum
        // $chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        // 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 
        // 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 
        // 'U', 'V', 'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', 
        // '/', '+', '%');

        //Compute the modulo 36 checksum
        $chars = array(
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            '-',
            '.',
            ' ',
            '$',
            '/',
            '+',
            '%'
        );
        $sum = 0;
        for ($i = 0; $i < strlen($code); $i++) {
            $a = array_keys($chars, $code[$i]);
            $sum += $a[0];
        }
        $r = $sum % 43;
        return $chars[$r];
    }

    function encode_code39_ext($code)
    {

        //Encode characters in extended mode

        $encode = array(
            chr(0) => '%U',
            chr(1) => '$A',
            chr(2) => '$B',
            chr(3) => '$C',
            chr(4) => '$D',
            chr(5) => '$E',
            chr(6) => '$F',
            chr(7) => '$G',
            chr(8) => '$H',
            chr(9) => '$I',
            chr(10) => '$J',
            chr(11) => '£K',
            chr(12) => '$L',
            chr(13) => '$M',
            chr(14) => '$N',
            chr(15) => '$O',
            chr(16) => '$P',
            chr(17) => '$Q',
            chr(18) => '$R',
            chr(19) => '$S',
            chr(20) => '$T',
            chr(21) => '$U',
            chr(22) => '$V',
            chr(23) => '$W',
            chr(24) => '$X',
            chr(25) => '$Y',
            chr(26) => '$Z',
            chr(27) => '%A',
            chr(28) => '%B',
            chr(29) => '%C',
            chr(30) => '%D',
            chr(31) => '%E',
            chr(32) => ' ',
            chr(33) => '/A',
            chr(34) => '/B',
            chr(35) => '/C',
            chr(36) => '/D',
            chr(37) => '/E',
            chr(38) => '/F',
            chr(39) => '/G',
            chr(40) => '/H',
            chr(41) => '/I',
            chr(42) => '/J',
            chr(43) => '/K',
            chr(44) => '/L',
            chr(45) => '-',
            chr(46) => '.',
            chr(47) => '/O',
            chr(48) => '0',
            chr(49) => '1',
            chr(50) => '2',
            chr(51) => '3',
            chr(52) => '4',
            chr(53) => '5',
            chr(54) => '6',
            chr(55) => '7',
            chr(56) => '8',
            chr(57) => '9',
            chr(58) => '/Z',
            chr(59) => '%F',
            chr(60) => '%G',
            chr(61) => '%H',
            chr(62) => '%I',
            chr(63) => '%J',
            chr(64) => '%V',
            chr(65) => 'A',
            chr(66) => 'B',
            chr(67) => 'C',
            chr(68) => 'D',
            chr(69) => 'E',
            chr(70) => 'F',
            chr(71) => 'G',
            chr(72) => 'H',
            chr(73) => 'I',
            chr(74) => 'J',
            chr(75) => 'K',
            chr(76) => 'L',
            chr(77) => 'M',
            chr(78) => 'N',
            chr(79) => 'O',
            chr(80) => 'P',
            chr(81) => 'Q',
            chr(82) => 'R',
            chr(83) => 'S',
            chr(84) => 'T',
            chr(85) => 'U',
            chr(86) => 'V',
            chr(87) => 'W',
            chr(88) => 'X',
            chr(89) => 'Y',
            chr(90) => 'Z',
            chr(91) => '%K',
            chr(92) => '%L',
            chr(93) => '%M',
            chr(94) => '%N',
            chr(95) => '%O',
            chr(96) => '%W',
            chr(97) => '+A',
            chr(98) => '+B',
            chr(99) => '+C',
            chr(100) => '+D',
            chr(101) => '+E',
            chr(102) => '+F',
            chr(103) => '+G',
            chr(104) => '+H',
            chr(105) => '+I',
            chr(106) => '+J',
            chr(107) => '+K',
            chr(108) => '+L',
            chr(109) => '+M',
            chr(110) => '+N',
            chr(111) => '+O',
            chr(112) => '+P',
            chr(113) => '+Q',
            chr(114) => '+R',
            chr(115) => '+S',
            chr(116) => '+T',
            chr(117) => '+U',
            chr(118) => '+V',
            chr(119) => '+W',
            chr(120) => '+X',
            chr(121) => '+Y',
            chr(122) => '+Z',
            chr(123) => '%P',
            chr(124) => '%Q',
            chr(125) => '%R',
            chr(126) => '%S',
            chr(127) => '%T'
        );

        $code_ext = '';
        for ($i = 0; $i < strlen($code); $i++) {
            if (ord($code[$i]) > 127)
                $this->Error('Invalid character: ' . $code[$i]);
            $code_ext .= $encode[$code[$i]];
        }
        return $code_ext;
    }

    function draw_code39($code, $x, $y, $w, $h)
    {

        //Draw bars

        for ($i = 0; $i < strlen($code); $i++) {
            if ($code[$i] == '1')
                $this->Rect($x + $i * $w, $y, $w, $h, 'F');
        }
    }

    function Girar($angulo = 0, $x = -1, $y = -1)
    {
        if ($x == -1) $x = $this->x;

        if ($y == -1) $y = $this->y;

        if ($this->angulo != 0) $this->_out('Q');

        $this->angulo = $angulo;
        if ($angulo != 0) {
            $angulo *= M_PI / 180;
            $c = cos($angulo);
            $s = sin($angulo);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;

            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }
}                                                                             // FIN DE CLASSE
