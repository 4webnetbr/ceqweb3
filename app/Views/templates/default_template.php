<?php
header("Set-Cookie: cross-site-cookie=whatever; SameSite=Strict;");

// Garante que $title sempre exista
$title ??= $controler;
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title); ?> - <?= esc(NAME_APP); ?></title>
  <link rel="shortcut icon" href="<?= base_url('assets/images/favicon.ico'); ?>" type="image/x-icon">

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

  <!-- Frameworks e plugins -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

  <!-- Estilos padrão do sistema -->
  <link rel="stylesheet" href="<?= base_url('assets/css/default.css'); ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/menu.css'); ?>">

  <!-- Estilos adicionais (dinâmicos) -->
  <?php if (!empty($styles)) : ?>
    <?php foreach (explode(',', $styles) as $style) : ?>
      <link rel="stylesheet" href="<?= base_url("assets/css/{$style}.css"); ?>">
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- Scripts principais -->
  <script src="<?= base_url('assets/jscript/jquery-3.6.3.js'); ?>"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" crossorigin="anonymous"></script>
  <script src="https://kit.fontawesome.com/da32fd0a5b.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

  <!-- Scripts customizados -->
  <script src="<?= base_url('assets/jscript/my_default.js'); ?>"></script>
  <script src="<?= base_url('assets/jscript/my_menu.js'); ?>"></script>
  <script src="<?= base_url('assets/jscript/my_wsconn.js'); ?>"></script>

  <!-- Scripts adicionais (dinâmicos) -->
  <?php if (!empty($scripts)) : ?>
    <?php foreach (explode(',', $scripts) as $script) : ?>
      <script src="<?= base_url("assets/jscript/{$script}.js"); ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>
</head>

<body>
  <!-- Preloader -->
  <div id="bloqueiaTela" style="display:block">
    <div id="preloader" class="preloader bloqTela d-flex align-items-center justify-content-center"></div>
    <div class="bg-info px-5 py-3 border border-1 border-dark rounded-pill msgprocessando text-center">
      <div class="spinner-border spinner-border-sm mx-2"></div>
      <span id="msgprocessando"> Processando...</span>
    </div>
  </div>

  <!-- Seções do layout -->
  <section class="header"><?= $this->renderSection('titulo'); ?></section>
  <section class="menu"><?= $this->renderSection('menu'); ?></section>
  <section class="content"><?= $this->renderSection('content'); ?></section>
  <section class="manutencao"><?= $this->renderSection('manutencao'); ?></section>
  <section class="footer"><?= $this->renderSection('footer'); ?></section>

  <?= view('strut/vw_modal'); ?>

  <!-- Toast de notificação -->
  <!--
  <div id="myToast" class="toast bg-success text-white d-none">
    <div class="toast-header">
      <strong class="me-auto">
        <span class="toast-title"><i class="bi bi-info-circle"></i> Informação</span>
      </strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body">
    </div>
  </div>
    -->
  <div id="toast-container"></div>


  <!-- Scripts inline finais -->
  <script>
    jQuery(window).on("load", function() {

      desBloqueiaTela();
      msgtoast = '<?= esc(session('msg')); ?>';
      if (msgtoast != '') {
        setTimeout(mostranoToast(msgtoast), 750);
      }
    });
    // Exporta mensagens da sessão para JS
    const msg_cfg = <?= json_encode(session('msg_cfg') ?? [], JSON_UNESCAPED_UNICODE); ?>;
  </script>
</body>

</html>