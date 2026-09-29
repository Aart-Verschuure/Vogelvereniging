@vite(['resources/css/app.css', 'resources/js/app.js'])
@include('layouts.navigation') <div class="relative min-h-[calc(100vh-4rem)] bg-cover bg-center flex flex-col justify-between" 
    style="background-image: url('images/dashboard_achtergrond.jpeg');">
    <div class="absolute inset-0 bg-black/20 pointer-events-none"></div>

    <div class="relative z-10 flex-grow flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto w-full space-y-6">
        
            <div class="w-full bg-[#968f79]/90 hover:bg-[#857e6a] transition text-white px-6 py-4 shadow-lg flex justify-between items-center backdrop-blur-sm">
                <span class="text-xl font-medium tracking-wide">Gebruikersbeheer</span>
                <a href="{{ route('members.index') }}" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 text-sm font-semibold transition border border-white/20">
                    Bekijken
                </a>
            </div>
            <div class="w-full bg-[#968f79]/90 hover:bg-[#857e6a] transition text-white px-6 py-4 shadow-lg flex justify-between items-center backdrop-blur-sm">
                <span class="text-xl font-medium tracking-wide">Nieuwe gebruiker</span>
                <a href="{{ route('members.create') }}" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 text-sm font-semibold transition border border-white/20">
                    Registreren
                </a>
            </div>
    </div>

    <div class="relative z-10 w-full bg-[#f3b05a] text-gray-800 py-2 text-center text-xs font-medium tracking-wide shadow_inner">
        &copy; - Made by Aart Verschuure
    </div>
</div>
