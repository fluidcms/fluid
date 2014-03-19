<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fluid CMS</title>
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:300,400,500,700,900">
    <link rel="stylesheet" href="../../../../public/stylesheets/fluid-0.0.1.css">
    <link rel="icon" href="../../../../public/images/favicon.png">
    <script>
        var fluidSession = "<?= $GLOBALS['session'] ?>";
        var fluidBranch = "<?= $GLOBALS['branch'] ?>";
        var fluidUrl = "<?= $GLOBALS['site_url'] ?>";
        var fluidWebSocketUrl = "<?= $GLOBALS['websocket_url'] ?>fluidcms/websocket";
        var fluidUserId = "<?= $GLOBALS['user_id'] ?>";
        var fluidUserName = "Gabriel Bull";
        var fluidUserEmail = "gavroche.bull@gmail.com";
        var fluidLanguage = <?= json_encode($GLOBALS['language']) ?>;
    </script>
    <script src="../../../../public/javascripts/vendor/autobahnjs-0.9.2.min.js"></script>
    <script data-main="javascripts/fluid-0.1.0.min.js" src="../../../../public/javascripts/vendor/requirejs-2.1.11.min.js"></script>
</head>
<body>
<div id="main">
    <div id="nav"></div>
    <div id="content"></div>
</div>

<div id="toolbar">
</div>

<div id="target">
    <iframe id="website"></iframe>
</div>

<div class="loader">
    <h1>Loading Fluid</h1>
    <img src="/fluidcms/images/preloader.gif" width="32" height="32" alt="">
</div>
</body>
</html>