<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Catálogo de Cuentas') | {{ $brandName ?? 'CStreame Streaming' }}</title>

    <!-- PWA Primary Meta Tags -->
    <meta name="theme-color" content="{{ $primaryColor ?? '#E50914' }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $brandName ?? 'CStreame Streaming' }}">
    <link rel="manifest" href="{{ route('pwa.manifest', ['ref' => request('ref') ?? (isset($seller) ? $seller->referral_code : '')]) }}">
    <link rel="apple-touch-icon" href="{{ isset($brandLogo) && $brandLogo ? asset($brandLogo) : asset('images/cstreame-icon.svg') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <!-- PWA Install Prompt Capture Engine -->
    <script>
        window.deferredPwaPrompt = null;
        window.isPwaInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredPwaPrompt = e;
            const btn = document.getElementById('pwa-header-btn');
            if (btn) {
                btn.classList.add('ring-2', 'ring-amber-400/50', 'animate-pulse');
            }
        });

        window.addEventListener('appinstalled', () => {
            window.isPwaInstalled = true;
            window.deferredPwaPrompt = null;
            const btnText = document.getElementById('pwa-header-btn-text');
            if (btnText) btnText.innerText = 'App Instalada ✓';
        });
    </script>
</head>
<body class="bg-[#0b0f19] text-gray-100 min-h-screen flex flex-col font-sans antialiased selection:bg-red-600 selection:text-white">

    <!-- Top Glow Effect -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-gradient-to-b from-red-600/15 via-rose-500/10 to-transparent blur-3xl pointer-events-none -z-10"></div>

    <!-- Navigation Header -->
    <header class="border-b border-gray-800/80 bg-[#0e1424]/80 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand / Logo -->
            <a href="{{ route('catalog.index', array_filter(['ref' => request('ref') ?? (isset($seller) && $seller && $seller->isReseller() ? $seller->referral_code : null)])) }}" class="flex items-center gap-3">
                @if(isset($brandLogo) && $brandLogo)
                    <img src="{{ $brandLogo }}" alt="{{ $brandName ?? 'CStreame Streaming' }}" class="h-10 w-auto object-contain">
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-red-600 via-rose-500 to-amber-500 flex items-center justify-center text-white text-xl font-black shadow-lg shadow-red-600/20">
                        🍿
                    </div>
                @endif
                <div>
                    <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-white via-gray-100 to-red-300 bg-clip-text text-transparent">
                        {{ $brandName ?? 'CStreame Streaming' }}
                    </span>
                    <span class="text-[10px] text-red-400 block font-semibold uppercase tracking-wider">Tienda Oficial de Cuentas Streaming</span>
                </div>
            </a>

            @php
                $isCatalog = request()->routeIs('catalog.index');
                $refParam = request('ref') ?? (isset($seller) && $seller && $seller->isReseller() ? $seller->referral_code : (isset($provider) ? $provider->referral_code : null));
                $baseCatalog = route('catalog.index', array_filter(['ref' => $refParam]));
                $urlPlanes = $isCatalog ? '#planes-paneles' : $baseCatalog . '#planes-paneles';
                $urlCatalogo = $isCatalog ? '#catalogo' : $baseCatalog . '#catalogo';
                $urlComoFunciona = $isCatalog ? '#como-funciona' : $baseCatalog . '#como-funciona';
                $urlFaq = $isCatalog ? '#faq' : $baseCatalog . '#faq';
                $urlCotizador = $isCatalog ? '#cotizador-creditos' : $baseCatalog . '#cotizador-creditos';
            @endphp

            <!-- Center Nav Links -->
            <nav class="hidden lg:flex items-center gap-6 text-xs font-bold text-slate-400">
                @if(request()->routeIs('affiliate.register') || request()->routeIs('resellers.affiliate_register'))
                    {{-- PÁGINA DE REGISTRO / AFILIACIÓN: NAVEGACIÓN LIMPIA --}}
                    <a href="{{ $urlCatalogo }}" class="hover:text-white transition-colors flex items-center gap-1.5">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Ver Catálogo de {{ $brandName ?? 'Streaming' }}</span>
                    </a>
                    <a href="{{ $urlComoFunciona }}" class="hover:text-red-400 transition-colors">¿Cómo Funciona?</a>
                    <a href="{{ $urlFaq }}" class="hover:text-amber-400 transition-colors">FAQ</a>
                @elseif(isset($seller) && $seller->isReseller())
                    {{-- LANDING DE PROVEEDOR / REVENDEDOR ASOCIADO --}}
                    @if($seller->canResellPanels() && $seller->isPanelSalesOffered())
                    <a href="{{ $urlPlanes }}" class="text-white hover:text-amber-400 transition-colors flex items-center gap-1.5 font-bold">
                        <span class="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></span>
                        <span>Planes de Panel</span>
                    </a>
                    @endif
                    <a href="{{ $urlCatalogo }}" class="hover:text-white transition-colors">Catálogo</a>
                    <a href="{{ $urlComoFunciona }}" class="hover:text-red-400 transition-colors">¿Cómo Funciona?</a>
                    <a href="{{ $urlFaq }}" class="hover:text-amber-400 transition-colors">FAQ</a>
                @else
                    {{-- LANDING CENTRAL SUPER ADMIN --}}
                    @if(!isset($seller) || $seller->isPanelSalesOffered())
                    <a href="{{ $urlPlanes }}" class="hover:text-white transition-colors">Planes</a>
                    @endif
                    <a href="{{ $urlCotizador }}" class="hover:text-white transition-colors">Cotizador</a>
                    <a href="{{ $urlCatalogo }}" class="hover:text-white transition-colors">Catálogo</a>
                    <a href="{{ $urlComoFunciona }}" class="hover:text-red-400 transition-colors">¿Cómo Funciona?</a>
                    <a href="{{ $urlFaq }}" class="hover:text-amber-400 transition-colors">FAQ</a>
                @endif
            </nav>

            <!-- Right Actions -->
            <div class="flex items-center gap-3">
                <!-- Botón de Instalación PWA Directa (1-Clic sin menús) -->
                <button type="button" id="pwa-header-btn" onclick="triggerPwaInstallDirectly(event)" class="pwa-install-btn flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gradient-to-r from-red-600/20 to-purple-600/20 hover:from-red-600/30 hover:to-purple-600/30 text-amber-300 border border-amber-500/30 text-xs font-bold transition-all shadow-sm cursor-pointer active:scale-95" title="Instalar Aplicación en tu pantalla de inicio">
                    <i data-lucide="smartphone" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span id="pwa-header-btn-text">Instalar App</span>
                </button>

                @if(isset($supportWhatsapp) && $supportWhatsapp)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsapp) }}?text=Hola,%20quisiera%20consultar%20por%20cuentas%20streaming" target="_blank" class="hidden sm:flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold transition-all">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Atención WhatsApp</span>
                </a>
                @endif

                @if(\Illuminate\Support\Facades\Auth::guard('customer')->check())
                <a href="{{ route('boveda.index') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-red-600 to-amber-500 hover:opacity-90 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-md">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    <span>Mi Bóveda</span>
                </a>
                @else
                <a href="{{ route('boveda.login') }}" class="px-3.5 py-2 rounded-xl bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-500/30 text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="key" class="w-3.5 h-3.5"></i>
                    <span>Bóveda Clientes</span>
                </a>
                @endif

                {{-- Solo visible si el usuario ya inició sesión previamente como Administrador o Revendedor --}}
                @if(\Illuminate\Support\Facades\Auth::guard('web')->check())
                <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 border border-purple-500/30 text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm" title="Ir a mi panel de gestión">
                    <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                    <span>Mi Panel</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Mobile Quick Subnav Bar -->
        <div class="lg:hidden flex items-center justify-center gap-4 px-4 py-2.5 bg-[#0b1020]/95 border-t border-gray-800/60 overflow-x-auto scrollbar-none text-xs font-bold text-slate-400">
            @if(request()->routeIs('affiliate.register') || request()->routeIs('resellers.affiliate_register'))
                <a href="{{ $urlCatalogo }}" class="hover:text-white shrink-0 flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3 h-3"></i>
                    <span>Catálogo</span>
                </a>
                <a href="{{ $urlComoFunciona }}" class="hover:text-red-400 shrink-0">¿Cómo Funciona?</a>
                <a href="{{ $urlFaq }}" class="hover:text-amber-400 shrink-0">FAQ</a>
            @elseif(isset($seller) && $seller->isReseller())
                @if($seller->canResellPanels() && $seller->isPanelSalesOffered())
                <a href="{{ $urlPlanes }}" class="text-white hover:text-amber-400 shrink-0 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                    <span>Planes</span>
                </a>
                @endif
                <a href="{{ $urlCatalogo }}" class="hover:text-white shrink-0">Catálogo</a>
                <a href="{{ $urlComoFunciona }}" class="hover:text-red-400 shrink-0">¿Cómo Funciona?</a>
                <a href="{{ $urlFaq }}" class="hover:text-amber-400 shrink-0">FAQ</a>
            @else
                @if(!isset($seller) || $seller->isPanelSalesOffered())
                <a href="{{ $urlPlanes }}" class="hover:text-white shrink-0">Planes</a>
                @endif
                <a href="{{ $urlCotizador }}" class="hover:text-white shrink-0">Cotizador</a>
                <a href="{{ $urlCatalogo }}" class="hover:text-white shrink-0">Catálogo</a>
                <a href="{{ $urlComoFunciona }}" class="hover:text-red-400 shrink-0">¿Cómo Funciona?</a>
                <a href="{{ $urlFaq }}" class="hover:text-amber-400 shrink-0">FAQ</a>
            @endif
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Floating WhatsApp Pill Button -->
    @if(isset($supportWhatsapp) && $supportWhatsapp)
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsapp) }}?text=Hola,%20necesito%20asistencia%20en%20mi%20compra%20de%20cuenta" target="_blank" class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs shadow-2xl shadow-emerald-500/40 flex items-center gap-2.5 transition-all hover:scale-105 active:scale-95 group">
        <i data-lucide="message-circle" class="w-5 h-5"></i>
        <span>¿Dudas? Hablá con nosotros</span>
    </a>
    @endif

    <!-- Footer -->
    <footer class="border-t border-gray-800/80 bg-[#0a0f1c] text-gray-400 text-xs py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                © {{ date('Y') }} <strong>{{ $brandName ?? 'CStreame Streaming' }}</strong>. Plataforma integral de gestión y venta de cuentas streaming.
            </div>
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5 text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Servidores en línea
                </span>
                <span class="text-gray-600">|</span>
                <span>Optimizado para cPanel</span>
            </div>
        </div>
    </footer>

    <!-- Modal Guía para iPhone / iPad (Safari) -->
    <div id="pwa-ios-modal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/85 backdrop-blur-md">
        <div class="w-full max-w-sm bg-[#111827] border border-gray-800 rounded-3xl p-6 shadow-2xl space-y-4 text-center">
            <div class="w-12 h-12 rounded-2xl bg-sky-500/20 text-sky-400 border border-sky-500/30 flex items-center justify-center mx-auto">
                <i data-lucide="smartphone" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Instalar en iPhone / iPad (Safari)</h3>
                <p class="text-xs text-slate-400 mt-1">Apple requiere 2 toques rápidos para agregar la App a tu pantalla:</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-left text-xs text-slate-300 space-y-2.5">
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-sky-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">1</span>
                    <span>Toca el botón <strong>Compartir</strong> <span class="text-sky-400 font-mono text-base">⎋</span> en la barra inferior de Safari.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-sky-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">2</span>
                    <span>Desliza y selecciona <strong>"Agregar a pantalla de inicio" ➕</strong>.</span>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('pwa-ios-modal').classList.add('hidden')" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                ¡Entendido!
            </button>
        </div>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

        async function triggerPwaInstallDirectly(e) {
            if (e) e.preventDefault();
            
            if (window.isPwaInstalled) {
                alert('¡La App ya se encuentra instalada en tu dispositivo!');
                return;
            }

            const isIos = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
            if (isIos) {
                const modal = document.getElementById('pwa-ios-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    lucide.createIcons();
                }
                return;
            }

            // Disparar de inmediato si ya fue capturado (1-Clic instantáneo)
            if (window.deferredPwaPrompt) {
                try {
                    window.deferredPwaPrompt.prompt();
                    const { outcome } = await window.deferredPwaPrompt.userChoice;
                    if (outcome === 'accepted') {
                        window.isPwaInstalled = true;
                        window.deferredPwaPrompt = null;
                        const btnText = document.getElementById('pwa-header-btn-text');
                        if (btnText) btnText.innerText = 'App Instalada ✓';
                    }
                    return;
                } catch (err) {
                    console.warn('Install prompt error:', err);
                }
            }

            // Esperar brevemente a que el navegador emita el prompt
            const btnText = document.getElementById('pwa-header-btn-text');
            const originalText = btnText ? btnText.innerText : 'Instalar App';
            if (btnText) btnText.innerText = 'Instalando...';

            const promptEvent = await new Promise((resolve) => {
                if (window.deferredPwaPrompt) return resolve(window.deferredPwaPrompt);
                const handler = (evt) => {
                    evt.preventDefault();
                    window.deferredPwaPrompt = evt;
                    window.removeEventListener('beforeinstallprompt', handler);
                    resolve(evt);
                };
                window.addEventListener('beforeinstallprompt', handler);
                setTimeout(() => {
                    window.removeEventListener('beforeinstallprompt', handler);
                    resolve(null);
                }, 1200);
            });

            if (btnText) btnText.innerText = originalText;

            if (promptEvent) {
                promptEvent.prompt();
                const { outcome } = await promptEvent.userChoice;
                if (outcome === 'accepted') {
                    window.isPwaInstalled = true;
                    window.deferredPwaPrompt = null;
                    if (btnText) btnText.innerText = 'App Instalada ✓';
                }
                return;
            }

            // Fallback a pantalla dedicada PWA
            const pwaUrl = "{{ route('pwa.install', ['code' => request('ref') ?? (isset($seller) ? $seller->referral_code : '')]) }}";
            window.location.href = pwaUrl;
        }

        lucide.createIcons();
    </script>
</body>
</html>
