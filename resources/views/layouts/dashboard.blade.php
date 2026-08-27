<!DOCTYPE html>
<html lang="en" class="bg-stone-50 dark:bg-slate-900">
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
    <script>
    // Ensures a vendor lib (tinymce/flatpickr) is present before running an init
    // callback; passes through instantly when the script is already loaded.
    window.whenLib = (function () {
        var pending = {};
        return function (name, url, cb) {
            if (window[name]) { return cb(); }
            (pending[url] = pending[url] || []).push(cb);
            if (pending[url].length === 1) {
                var s = document.createElement('script');
                s.src = url;
                s.onload = function () {
                    pending[url].forEach(function (fn) { fn(); });
                    pending[url] = null;
                };
                document.head.appendChild(s);
            }
        };
    })();
    </script>
    @vite(['resources/js/app.js', 'resources/css/app.css'])

    @livewireStyles
    @stack('vendor-styles')
    @stack('vendor-scripts')
</head>
<body class="dark:selection-bg selection-bg font-sans">
    @yield('content')

    <x-toaster-hub />

    <img src="{{ asset('assets/watermark.png') }}" alt="" class="fixed sm:block hidden sm:-bottom-1/6 sm:-left-1/6 rotate-90 -z-10 pointer-events-none">
    @livewireScripts
    @stack('scripts')

</body>
</html>
