<?php
  header("Set-Cookie: cross-site-cookie=whatever; SameSite=Strict;");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Identificação - <?=NAME_APP?></title>
    <link rel="shortcut icon" href="/assets/images/favicon.ico?noc=<?=time();?>" type="image/x-icon">

  <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <!-- Bootstrap icones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

  <!-- Estilo principal -->
    <link rel="stylesheet" href="<?=base_url('assets/css/default.css?nocache='.time());?>">
  <!-- Estilos personalizados -->
  <?php
    if(isset($styles) && strlen($styles)>0){
      $styles = explode(',',$styles);
      for ($s=0; $s < sizeof($styles); $s++) {
        $sty    = base_url('assets/css/'.$styles[$s].'.css');
        $style = "<link rel='stylesheet' href='".$sty."'>";
        echo $style;
      }
    }
  ?>
  
  <!-- Links JAVASCRIPTS -->
  <!-- JQUERY -->
    <script src="<?=base_url('assets/jscript/jquery-3.6.3.js');?>"></script>
  <!-- Scripts personalizados -->
    <script src="<?=base_url('assets/jscript/my_default.js');?>"></script>
  <?php
    if(isset($scripts) && strlen($scripts)>0){
      $scripts = explode(',',$scripts);
      for ($s=0; $s < sizeof($scripts); $s++) {
        $scr    = base_url('assets/jscript/'.$scripts[$s].'.js');
        $script = "<script src=".$scr."></script>";
        echo $script;
      }
    }
  ?>
    
  <!-- Bootstrap JS -->
  
</head>
<body>
    <section>
        <?=$this->renderSection('content');?>
    </section>
</body>
</html>
