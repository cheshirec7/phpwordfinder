<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Word Updater</title>
    <meta name="description" content="Word Updater">
    <meta name="author" content="Eric Totten">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    @stack('before-styles')
    <style>
        .loader {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            z-index: 9998;
        }

        .ajax-spinner {
            width: 48px;
            height: 48px;
            display: inline-block;
            box-sizing: border-box;
            position: absolute;
            top: 120px;
            left: calc(50% - 24px);
        }

        .ajax-skeleton {
            border-radius: 50%;
            border-top: solid 6px darkorange;
            border-right: solid 6px transparent;
            border-bottom: solid 6px transparent;
            border-left: solid 6px transparent;
            animation: ajax-skeleton-animate 1s linear infinite;
        }

        .ajax-skeleton:before {
            border-radius: 50%;
            content: " ";
            width: 48px;
            height: 48px;
            display: inline-block;
            box-sizing: border-box;
            border-top: solid 6px transparent;
            border-right: solid 6px transparent;
            border-bottom: solid 6px transparent;
            border-left: solid 6px darkorange;
            position: absolute;
            top: -6px;
            left: -6px;
            transform: rotateZ(-30deg);
        }

        .ajax-skeleton:after {
            border-radius: 50%;
            content: " ";
            width: 48px;
            height: 48px;
            display: inline-block;
            box-sizing: border-box;
            border-top: solid 6px transparent;
            border-right: solid 6px darkorange;
            border-bottom: solid 6px transparent;
            border-left: solid 6px transparent;
            position: absolute;
            top: -6px;
            right: -6px;
            transform: rotateZ(30deg);
        }

        @keyframes ajax-skeleton-animate {
            0% {
                transform: rotate(0);
                opacity: 1
            }
            50% {
                opacity: .7
            }
            100% {
                transform: rotate(360deg);
                opacity: 1;
            }
        }
    </style>
    @stack('after-styles')
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Word Updater</a>
    <div class="col-auto">
        <form action="startswith" method="POST">
            <select class="custom-select" id="startsWith" name="startsWith" onchange="this.form.submit()">
                <option>- Select -</option>
                <option value="A">Starts with A</option>
                <option value="B">Starts with B</option>
                <option value="C">Starts with C</option>
                <option value="D">Starts with D</option>
                <option value="E">Starts with E</option>
                <option value="F">Starts with F</option>
                <option value="G">Starts with G</option>
                <option value="H">Starts with H</option>
                <option value="I">Starts with I</option>
                <option value="J">Starts with J</option>
                <option value="K">Starts with K</option>
                <option value="L">Starts with L</option>
                <option value="M">Starts with M</option>
                <option value="N">Starts with N</option>
                <option value="O">Starts with O</option>
                <option value="P">Starts with P</option>
                <option value="Q">Starts with Q</option>
                <option value="R">Starts with R</option>
                <option value="S">Starts with S</option>
                <option value="T">Starts with T</option>
                <option value="U">Starts with U</option>
                <option value="V">Starts with V</option>
                <option value="W">Starts with W</option>
                <option value="X">Starts with X</option>
                <option value="Y">Starts with Y</option>
                <option value="Z">Starts with Z</option>
            </select>
        </form>
    </div>
</nav>
<main role="main">
    <div class="container-fluid">
        <div class="loader" style="display: none;">
            <div class="ajax-spinner ajax-skeleton"></div>
        </div>
        <div id="resultsArea">
            @yield('content')
        </div>
    </div>
</main>

@stack('before-scripts')
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function () {

        let $loading = $('.loader'),
            $spinner = $('.ajax-spinner');

        $(document).ajaxStart(function () {
            $loading.show();
        }).ajaxError(function (event, jqxhr, settings, thrownError) {
            $loading.hide();
            // alert(thrownError);
            location.reload();
        }).ajaxStop(function () {
            $loading.hide();
        });
    });

</script>
@stack('after-scripts')
</body>
</html>
