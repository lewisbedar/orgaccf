<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>OrgaCCF - {{ $title ?? 'Document' }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="print-page">@yield('content')</body>
</html>
