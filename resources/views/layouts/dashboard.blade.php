<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Page Title'}}</title>
    <script>
    (function () {

        const theme = localStorage.getItem('theme');

        if (theme === 'dark') {

            document.documentElement.classList.add('dark');

        } else if (theme === 'light') {

            document.documentElement.classList.remove('dark');

        } else {

            // default ikut system
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            }

        }

    })();
    </script>
    @vite(['resources/js/app.js', 'resources/css/app.css'])

    @livewireStyles
    @livewireScripts
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{ asset('tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="dot-pattern dark:selection-bg selection-bg font-sans dark:bg-slate-900 bg-stone-50">
    @yield('content')

    <x-toaster-hub />

    <img src="https://controlsheet.mapbiomas.id/assets/logo.png" alt="" class="fixed sm:block hidden sm:-bottom-1/6 sm:-left-1/6 rotate-90 z-0 pointer-events-none">
    @stack('scripts')

</body>
</html>
