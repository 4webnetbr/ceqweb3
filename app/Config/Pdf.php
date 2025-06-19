<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Pdf extends BaseConfig
{
    /*
    | -------------------------------------------------------------------
    |  Orientation
    | -------------------------------------------------------------------
    | Prototype:
    |
    |  'P' as "portrait", 'L' as "landscape"
    |
    */
    public $orientation = 'P';

    /*
    | -------------------------------------------------------------------
    |  Size
    | -------------------------------------------------------------------
    | Prototype:
    |    A3
    |    A4
    |    A5
    |    Letter
    |    Legal
    |
    |   Or array, in units enabled by user, with no standarised size: array(with,hight)
    */
    public $size = 'A4';

    /*
    | -------------------------------------------------------------------
    |  Rotation
    | -------------------------------------------------------------------
    | Prototype:
    |
    |  Integer multiple of 90 degrees: 0,90,180,270
    |
    */
    public $rotation = '0';

    /*
    | -------------------------------------------------------------------
    |  Units
    | -------------------------------------------------------------------
    | Prototype:
    |
    |   'mm' means milimetres
    |   'pt' means points
    |   'cm' means centimetre
    |   'in' means inches
    |
    */
    public $units = 'mm';

    /*
    | -------------------------------------------------------------------
    |  convert logo as base_url() address
    | -------------------------------------------------------------------
    | Prototype:
    |  TRUE , FALSE
    |
    | Behavior:
    |   If false, logo addres will be passed as you declare
    |   else, will be wrapped in base_url() function.
    */
    public $url_wrapper = TRUE;

    /*
    | -------------------------------------------------------------------
    |  Logo
    | -------------------------------------------------------------------
    | Logo url
    | the address of the logo will be subsequently converted to an absolute address
    */
    public $logo = 'assets/images/logo-back.png';

    /*
    | -------------------------------------------------------------------
    |  Head Title
    | -------------------------------------------------------------------
    |
    | Main page's Title
    */
    public $head_title = '';

    /*
    | -------------------------------------------------------------------
    |  Head Subitle
    | -------------------------------------------------------------------
    |
    | Main page's Subitle
    */
    public $head_subtitle = '';

    /*
    | -------------------------------------------------------------------
    |  Footer 'page' literal
    | -------------------------------------------------------------------
    |
    | Set 'page' in your language
    */
    public $footer_page_literal = 'Página';

    /*
    | -------------------------------------------------------------------
    |  Format
    | -------------------------------------------------------------------
    |
    | Prototype boolean
    |  TRUE means UTF8.false means ISO-8959-1
    */
    public $format = TRUE;
}
