<?php

namespace App\Libraries;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Wrapper padrão para geração de PDF com mPDF no CodeIgniter 4.
 *
 * Substitui o antigo MyPdf2025 baseado em FPDF.
 * A ideia agora é gerar PDF por HTML/CSS, deixando o código mais limpo
 * e menos dependente de coordenadas manuais.
 */
class MymPdf2026
{
    protected Mpdf $mpdf;

    protected string $orientation = 'P';
    protected string $size = 'A4';
    protected string $logo = '';
    protected string $headTitle = '';
    protected string $headSubtitle = '';
    protected string $footerPageLiteral = 'Página';
    protected string $footerCenter = '';
    protected bool $temHeader = true;
    protected bool $temFooter = true;
    protected array $config = [];

    public function __construct(bool $temHeader = true, bool $temFooter = true, string|array|false $size = false, string $orientation = '')
    {
        $pdfConfig = config('Pdf');

        $this->temHeader = $temHeader;
        $this->temFooter = $temFooter;

        $this->orientation       = $orientation !== '' ? strtoupper($orientation) : strtoupper($pdfConfig->orientation ?? 'P');
        $this->size              = $size ?: ($pdfConfig->size ?? 'A4');
        $this->headTitle         = $this->toUtf8($pdfConfig->head_title ?? '');
        $this->headSubtitle      = $this->toUtf8($pdfConfig->head_subtitle ?? '');
        $this->footerPageLiteral = $this->toUtf8($pdfConfig->footer_page_literal ?? 'Página');

        $logo = $pdfConfig->logo ?? '';
        if ($logo !== '') {
            $this->logo = $this->resolvePath($logo);
        }

        $format = $this->size;
        if (is_string($this->size) && in_array($this->orientation, ['L', 'LANDSCAPE'], true)) {
            $format = $this->size . '-L';
        }

        $this->config = [
            'mode'              => 'utf-8',
            'format'            => $format,
            'orientation'       => $this->orientation,
            'margin_left'       => 10,
            'margin_right'      => 10,
            'margin_top'        => $this->temHeader ? 18 : 10,
            'margin_bottom'     => $this->temFooter ? 14 : 10,
            'margin_header'     => 5,
            'margin_footer'     => 5,
            'default_font'      => 'arial',
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
            'tempDir'           => WRITEPATH . 'mpdf',
        ];

        if (! is_dir($this->config['tempDir'])) {
            @mkdir($this->config['tempDir'], 0775, true);
        }

        $this->mpdf = new Mpdf($this->config);
        $this->mpdf->SetDisplayMode('fullwidth');

        if ($this->temHeader) {
            $this->setDefaultHeader();
        }

        if ($this->temFooter) {
            $this->setDefaultFooter();
        }
    }

    public function getMpdf(): Mpdf
    {
        return $this->mpdf;
    }

    public function titulo(string $title): self
    {
        $this->mpdf->SetTitle($this->toUtf8($title));
        return $this;
    }

    /** Compatibilidade com chamadas antigas em FPDF. */
    public function SetTitle($title, $isUTF8 = false): void
    {
        $this->setTitle((string) $title);
    }

    public function setAuthor(string $author): self
    {
        $this->mpdf->SetAuthor($this->toUtf8($author));
        return $this;
    }

    public function footerCenter(string $footerCenter): self
    {
        $this->footerCenter = $this->toUtf8($footerCenter);
        $this->setDefaultFooter();
        return $this;
    }

    /** Compatibilidade com o método antigo. */
    public function SetFooterCenter($footcenter): void
    {
        $this->setFooterCenter((string) $footcenter);
    }

    public function setHeaderHtml(string $html): self
    {
        $this->mpdf->SetHTMLHeader($html);
        return $this;
    }

    public function setFooterHtml(string $html): self
    {
        $this->mpdf->SetHTMLFooter($html);
        return $this;
    }

    public function setDefaultHeader(): self
    {
        $logo = $this->logo !== '' ? '<img src="' . $this->e($this->logo) . '" style="height:18mm;">' : '';

        $html = '
        <table width="100%" style="border-bottom:1px solid #ddd; font-size:9pt; font-family:Arial, sans-serif;">
            <tr>
                <td width="20%">' . $logo . '</td>
                <td width="80%" style="text-align:right;">
                    <strong>' . $this->e($this->headTitle) . '</strong><br>
                    <span>' . $this->e($this->headSubtitle) . '</span>
                </td>
            </tr>
        </table>';

        return $this->setHeaderHtml($html);
    }

    public function setDefaultFooter(): self
    {
        $center = $this->footerCenter !== '' ? $this->footerCenter : '+55 41 3657-7755';

        $html = '
        <table width="100%" style="border-top:1px solid #ddd; font-size:7.5pt; font-family:Arial, sans-serif;">
            <tr>
                <td width="55%">R. Professor Alfredo Valente, 1158 - Jd Gramados - Alm. Tamandaré - CEP 83.504-000</td>
                <td width="20%" style="text-align:center;">' . $this->e($center) . '</td>
                <td width="25%" style="text-align:right;">' . $this->e($this->footerPageLiteral) . ' {PAGENO}/{nbpg}</td>
            </tr>
        </table>';

        return $this->setFooterHtml($html);
    }

    public function html(string $html): self
    {
        $this->mpdf->WriteHTML($html);
        return $this;
    }


    public function addPage(string $orientation = '', string|array $size = ''): self
    {
        $orientation = $orientation !== '' ? strtoupper($orientation) : $this->orientation;
        $size        = $size !== '' ? $size : $this->size;
        $format      = is_string($size) && in_array($orientation, ['L', 'LANDSCAPE'], true) ? $size . '-L' : $size;

        $this->mpdf->AddPageByArray([
            'orientation' => $orientation,
            'sheet-size'  => $format,
        ]);

        return $this;
    }

    /** Compatibilidade com o antigo Add_Page. */
    public function Add_Page($orientation = null, $size = null, $rotation = null): void
    {
        $this->addPage($orientation ?: $this->orientation, $size ?: $this->size);
    }

    public function inline(string $fileName = 'documento.pdf'): string
    {
        return $this->mpdf->Output($fileName, Destination::INLINE);
    }

    public function download(string $fileName = 'documento.pdf'): string
    {
        return $this->mpdf->Output($fileName, Destination::DOWNLOAD);
    }

    public function save(string $fullPath): string
    {
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return $this->mpdf->Output($fullPath, Destination::FILE);
    }

    public function string(): string
    {
        return $this->mpdf->Output('', Destination::STRING_RETURN);
    }

    /**
     * Compatibilidade com o Output antigo.
     * Destinos aceitos: I, D, F, S.
     */
    public function Output($name = '', $dest = ''): string
    {
        // No FPDF antigo o uso comum era Output('S').
        // No mPDF o primeiro parâmetro é nome e o segundo é destino.
        if (in_array($name, ['I', 'D', 'F', 'S'], true) && $dest === '') {
            $dest = $name;
            $name = '';
        }

        $map = [
            'I' => Destination::INLINE,
            'D' => Destination::DOWNLOAD,
            'F' => Destination::FILE,
            'S' => Destination::STRING_RETURN,
        ];

        $destination = $map[$dest ?: 'I'] ?? Destination::INLINE;
        return $this->mpdf->Output($name ?: 'documento.pdf', $destination);
    }

    public function render(string $dest = 'I', string $name = 'document.pdf'): string
    {
        return $this->Output($name, $dest);
    }

    public function base64(): string
    {
        return base64_encode($this->string());
    }

    public function outputBase64Json(): string
    {
        return json_encode(['pdf' => $this->base64()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function view(string $view, array $data = []): self
    {
        return $this->html(view($view, $data));
    }

    public function get_logo(): string
    {
        return $this->logo;
    }

    public function get_orientation(): string
    {
        return $this->orientation;
    }

    public function get_size(): string|array
    {
        return $this->size;
    }

    public function get_rotation(): int
    {
        return 0;
    }

    public function get_units(): string
    {
        return 'mm';
    }

    public function get_head_title(): string
    {
        return $this->headTitle;
    }

    public function get_head_subtitle(): string
    {
        return $this->headSubtitle;
    }

    public function cssBase(): string
    {
        return '
        <style>
            body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; }
            .pdf-page { width: 100%; }
            .box { border: 1px solid #000; padding: 2mm; margin-bottom: 0; }
            .header-box { border: 1px solid #000; height: 12mm; padding: 0; }
            .logo { width: 15mm; vertical-align: middle; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .text-left { text-align: left; }
            .bold { font-weight: bold; }
            .small { font-size: 8pt; }
            .xsmall { font-size: 7pt; }
            .mt-1 { margin-top: 1mm; }
            .mt-2 { margin-top: 2mm; }
            .mb-1 { margin-bottom: 1mm; }
            .mb-2 { margin-bottom: 2mm; }
            table { border-collapse: collapse; width: 100%; }
            th, td { vertical-align: middle; }
            .table-border th, .table-border td { border: 1px solid #000; padding: 1.2mm; }
            .table-border th { background: #dcdcdc; font-weight: bold; text-align: center; }
            .nowrap { white-space: nowrap; }
            .rotate {
                writing-mode: vertical-rl;
                transform: rotate(180deg);
                text-align: left;
                vertical-align: middle;
                white-space: nowrap;
            }
            .label { font-weight: bold; }
        </style>';
    }

    public function e(?string $text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function toUtf8(?string $text): string
    {
        $text = (string) $text;
        if ($text === '') {
            return '';
        }

        $enc = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($enc !== 'UTF-8') {
            return mb_convert_encoding($text, 'UTF-8', $enc ?: 'ISO-8859-1');
        }

        return $text;
    }

    public function clean(?string $text): string
    {
        return html_entity_decode($this->toUtf8((string) $text), ENT_QUOTES, 'UTF-8');
    }

    public function resolvePath(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (defined('FCPATH') && is_file(FCPATH . $path)) {
            return FCPATH . $path;
        }

        if (is_file($path)) {
            return realpath($path) ?: $path;
        }

        return $path;
    }
}
