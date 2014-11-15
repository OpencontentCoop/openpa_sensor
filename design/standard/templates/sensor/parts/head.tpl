<head>

    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,400italic,600,600italic,700,700italic,300italic" rel="stylesheet" type="text/css">

    <meta charset="utf-8">

    <title>{$sensor.site_title}</title>

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {ezcss_load( array(
        'animate.css',
        'style.css',
        'fonts/font-awesome/css/font-awesome.min.css',
        'debug.css',
        'websitetoolbar.css'
    ) )}

    {ezscript_load(array(
        'modernizr.custom.48287.js'
    ))}

    <link rel="apple-touch-icon-precomposed" sizes="114x114" href={$sensor.site_images["apple-touch-icon-114x114-precomposed"]}>
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href={$sensor.site_images["apple-touch-icon-72x72-precomposed"]}>
    <link rel="apple-touch-icon-precomposed" href={$sensor.site_images["apple-touch-icon-57x57-precomposed"]}>
    <link rel="shortcut icon" href={$sensor.site_images["favicon"]}>
</head>