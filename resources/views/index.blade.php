<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Components</title>
    <link rel="stylesheet" href="http://cdn.syncfusion.com/15.1.0.41/js/web/flat-azure/ej.web.all.min.css">
    <link rel="stylesheet" href="http://cdn.syncfusion.com/15.1.0.41/js/web/responsive-css/ej.responsive.css">
</head>
<body>
<div id="fileExplorer"></div>
<script src="{{ mix('js/app.js') }}"></script>
<script src="http://cdn.syncfusion.com/js/assets/external/jsrender.min.js"></script>
<script src="http://cdn.syncfusion.com/js/assets/external/jquery.easing.1.3.min.js"></script>
<script src="http://cdn.syncfusion.com/js/assets/external/excanvas.min.js"></script>
<script src="http://cdn.syncfusion.com/js/assets/external/jquery.validate.min.js"></script>
<script src="http://cdn.syncfusion.com/15.1.0.41/js/web/ej.web.all.min.js"></script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        global: true,
        type: "GET"
    });
    let fileSystemPath = "root";
    let ajaxActionHandler = "{{ url('fileExplorer') }}";
    $("#fileExplorer").ejFileExplorer({
        path: fileSystemPath,
        ajaxAction: ajaxActionHandler,
        enableThumbnailCompress: true,
        isResponsive: true,
        toolsList: [
            "layout",
            "creation",
            "navigation",
            "addressBar",
            "editing",
            "getProperties"
        ]
    });
</script>
</body>
</html>