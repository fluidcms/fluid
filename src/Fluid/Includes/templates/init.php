<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900">
    <link rel="stylesheet" href="/fluidcms/stylesheets/init-1.0.css">
    <script src="/fluidcms/javascripts/vendor/jquery.js"></script>
</head>
<body>
    <div class="box">
        <h1>Initializing Fluid</h1>
        <img src="/fluidcms/images/preloader.gif" width="32" height="32" alt="">
    </div>

    <script>
        $(document).ready(function() {
            $.ajax({url: "/fluidcms/init"}).done(function( msg ) {
                window.location.reload();
            });
        });
    </script>
</body>
</html>