<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Netsuite Printing Server Sys</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif
</head>

<body
    class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">

    <div
        class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
        <main class="flex justify-center items-center">
            <div class="border border-gray-200 p-5 rounded-sm shadow shadow-gray-300">
                <p class="text-4xl font-bold text-center text-white animate-typing">
                    Welcome to the Oracle NetSuite Printing System. <br />
                    To proceed with receipt printing, please visit the link below. <br /><br />
                    <a href="https://netsuite-print.smctgroup.ph"
                        class="text-white p-2 bg-blue-500 hover:bg-blue-400 text-sm rounded-md">Click Here</a>
                </p>
            </div>
        </main>
    </div>
</body>

</html>
