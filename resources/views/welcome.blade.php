<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vogelvereniging 't Fratertje</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased h-screen flex flex-col justify-between">

<header class="bg-[#7d848c] shadow-md w-full">
    @if (Route::has('login'))
        @auth
            @include('layouts.navigation')
        @else
            <div class="flex items-center justify-between px-6 py-4 max-w-7xl mx-auto w-full">
                <div class="flex flex-grow justify-start items-center">
                    <img src="{{ asset('Images/frontpage.jpg') }}" alt="Frater Logo" class="h-16 w-auto object-contain">
                </div>
                <div class="flex flex-grow justify-end space-x-4">
                    <a href="{{ route('login') }}" class="bg-[#a3b8cc] text-gray-900 px-6 py-2 font-semibold hover:bg-gray-500 transition">
                        Inloggen
                    </a>
                    <a href="{{ route('register') }}" class="bg-[#a3b8cc] text-gray-900 px-6 py-2 font-semibold hover:bg-gray-500 transition">
                        aanmelden
                    </a>
                </div>
            </div>
        @endauth
    @endif
</header>

    <main class="flex-grow bg-[#b0c4de] p-8 md:p-12 text-gray-900">
        <div class="max-w-6xl mx-auto">
            
            <div class="text-center mb-8">
                <h1 class="text-xl font-bold mb-4">Welkom bij de website van VogelVereniging 't Fratertje'!</h1>
                <div class="space-y-3 text-left max-w-5xl mx-auto leading-relaxed">
                    <p>Hier vindt u informatie over de vereniging, zoals de geschiedenis, de lidmaatschappen die we aanbieden en contactgegevens.</p>
                    <p>Er zijn verschillende lidmaatschappen beschikbaar, en er is in overleg met de gemeente Gouda een natuurgebied ingericht voor vogelspotters, met verschillende spottersplekken en zeer veel verschillende vogelsoorten.</p>
                    <p>Natuur- en dierenbescherming Midden Nederland heeft het gebied in juni 2006 verkozen tot beste gebied van de maand met de navolgende motivatie:</p>
                    <blockquote class="italic border-l-4 border-gray-600 pl-4 my-2">
                        “Het gebied ademt rust uit en het heeft een soort alzijdigheid. Het totaal is zeer hoogwaardig vormgegeven”,<br>
                        Bovendien is het gebied een stiltegebied waardoor je alleen de vogeltjes hoort fluiten en de wind door de bomen. "Het is een prachtig gebied om te wandelen en te genieten van de natuur. Het is een aanrader voor iedereen die van vogels en natuur houdt."
                    </blockquote>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start max-w-5xl mx-auto mt-8">
                <div class="flex justify-center">
                    <img src="{{ asset('Images/gebied.jpg') }}" alt="omgeving" class="w-full max-w-md h-auto shadow-lg object-cover">
                </div>

                <div class="space-y-6 md:pl-8">
                    <div>
                        <h2 class="font-semibold mb-2">We bieden verschillende lidmaatschappen aan, waaronder:</h2>
                            <ul class="md:pl-14 list-disc space-y-1 pl-4">
                                <li>Senior-lid</li>
                                <li>Junior-lid</li>
                                <li>Gast-lid</li>
                            </ul>
                    </div>

                    <div>
                        <h2 class="font-semibold mb-2">U kunt ons op de volgende manier vinden en contacteren:</h2>
                        <address class="not-italic space-y-1 pl-4">
                            <p><strong class="font-medium">Vereniging:</strong> Vogelvereniging 't Fratertje</p>
                            <p><strong class="font-medium">Clubgebouw:</strong> Biologisch centrum Jong Dordrecht:</p>
                            <p><strong class="font-medium">Adres:</strong> Noorderelsweg 4A,<br><span class="pl-12">3329 KH Dordrecht</span></p>
                            <p><strong class="font-medium">Telefoon:</strong> 078-6213921</p>
                            <p><strong class="font-medium">E-mail:</strong><a href="mailto:contact@vogelvereniging.nl" class="text-blue-600 hover:underline"> contact@vogelvereniging.nl</a></p>
                        </address>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <footer class="bg-[#f3b05a] px-6 py-2 text-sm text-gray-800">
        <p>&copy; {{ date('d-m-Y') }} - Made by Aart Verschuure</p>
    </footer>
</body>
</html>