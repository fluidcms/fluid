<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fluid CMS</title>
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:300,400,500,700,900">
    <link rel="stylesheet" href="stylesheets/style-0.1.0.css">
    <link rel="icon" href="images/favicon.png">
    <script>
        var initParams = {
            branch: "<?= $GLOBALS['branch'] ?>",
            path: "<?= $GLOBALS['path'] ?>",
            websocket: "<?= $GLOBALS['websocket'] ?>",
            user: {
                "id": "<?= $GLOBALS['user']->getId() ?>",
                "name": "<?= $GLOBALS['user']->getName() ?>",
                "email": "<?= $GLOBALS['user']->getEmail() ?>"
            },
            language: "<?= $GLOBALS['language'] ?>",
            languages: [<?= json_encode($GLOBALS['language']) ?>]

        };
    </script>
    <script src="javascripts/vendor/autobahnjs-0.8.0.min.js"></script>
    <script data-main="javascripts/fluid-0.1.0.min.js" src="javascripts/vendor/requirejs-2.1.11.min.js"></script>
</head>
<body>
<div id="pannel">
    <div id="nav"></div>
    <div id="content"></div>
</div>

<div id="toolbar">
</div>

<div id="website">
    <iframe id="website-iframe"></iframe>
</div>

<div id="main"></div>

<div id="editor"></div>

<div class="loader">
    <img src="images/preloader.gif" width="32" height="32" alt="">
</div>
</body>
</html>