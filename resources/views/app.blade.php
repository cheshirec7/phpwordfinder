<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Dictionary Updater</title>
    <meta name="description" content="Word Updater">
    <meta name="author" content="Eric Totten">
    @stack('before-styles')
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/styles.css">
    @stack('after-styles')
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Dictionary Updater</a>
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
<script src="/js/jquery-3.3.1.min.js"></script>
<script src="/js/popper.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/scripts.js"></script>
@stack('after-scripts')
</body>
</html>
