@php($school = \App\Models\SchoolSetting::current())
@php($year = \App\Models\SchoolYear::active())
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OrgaCCF - {{ $title ?? 'Application' }}</title>
    <link rel="icon" href="/images/orgaccf-square.png" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ route('dashboard') }}">
        <img src="/images/orgaccf-square.png" alt="OrgaCCF">
    </a>
    <nav>
        <a href="{{ route('dashboard') }}">Tableau de bord</a>
        <span class="dropdown">
            <button type="button" data-menu-button>Structure</button>
            <span>
                <a href="{{ route('classes.index') }}">Classes</a>
                <a href="{{ route('students.index') }}">Élèves</a>
            </span>
        </span>
        <span class="dropdown">
            <button type="button" data-menu-button>Organisation</button>
            <span>
                <a href="{{ route('exams.index') }}">Épreuves</a>
            </span>
        </span>
        <span class="dropdown">
            <button type="button" data-menu-button>Résultats</button>
            <span>
                <a href="{{ route('grades.summary') }}">Notes</a>
                <a href="{{ route('grades.final') }}">Bilan</a>
            </span>
        </span>
        @if(auth()->user()->isCoordinator())
            <span class="dropdown">
                <button type="button" data-menu-button>Paramétrages</button>
                <span>
                    <a href="{{ route('settings.school') }}">Établissement</a>
                    <a href="{{ route('users.index') }}">Utilisateurs</a>
                </span>
            </span>
        @endif
    </nav>
    <form method="post" action="{{ route('logout') }}">@csrf<button class="ghost">Déconnexion</button></form>
</header>
<div class="context-line"><strong>{{ $school->school_name }}</strong><span>Année scolaire : {{ $year?->label ?? 'à configurer' }}</span></div>
<main class="page">
    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
    @yield('content')
</main>
</body>
</html>
