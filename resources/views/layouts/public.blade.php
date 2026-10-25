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
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/cstreame-icon-192.png">
    <link rel="icon" type="image/svg+xml" href="/images/cstreame-icon.svg">

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
            document.querySelectorAll('.pwa-install-btn').forEach(btn => {
                btn.classList.add('ring-2', 'ring-amber-400/50', 'animate-pulse');
            });
        });

        window.addEventListener('appinstalled', () => {
            window.isPwaInstalled = true;
            window.deferredPwaPrompt = null;
            document.querySelectorAll('.pwa-btn-label, #pwa-header-btn-text').forEach(el => {
                el.innerText = 'App Instalada ✓';
            });
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

    <!-- Toast de Notificación PWA -->
    <div id="pwa-toast" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-5 py-3 rounded-2xl bg-emerald-600 text-white font-bold text-xs shadow-2xl flex items-center gap-2.5 transition-all">
        <i data-lucide="check-circle" class="w-4 h-4"></i>
        <span class="toast-msg">¡App instalada correctamente!</span>
    </div>

    <!-- Modal Guía para Android (Chrome / Samsung) -->
    <div id="pwa-android-modal" class="pwa-modal hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/85 backdrop-blur-md" onclick="if(event.target===this)closePwaModal('pwa-android-modal')">
        <div class="relative w-full max-w-sm bg-[#111827] border border-gray-800 rounded-3xl p-6 shadow-2xl space-y-4 text-center">
            <button type="button" onclick="closePwaModal('pwa-android-modal')" class="absolute top-4 right-4 text-slate-400 hover:text-white p-1 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center mx-auto">
                <i data-lucide="smartphone" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Instalar App en Android</h3>
                <p class="text-xs text-slate-400 mt-1">Sigue estos pasos rápidos en tu navegador:</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-left text-xs text-slate-300 space-y-2.5">
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">1</span>
                    <span>Toca los <strong>tres puntos (⋮)</strong> en la esquina superior derecha del navegador.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">2</span>
                    <span>Selecciona <strong>"Instalar aplicación"</strong> o <strong>"Agregar a la pantalla principal"</strong> 📲.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">3</span>
                    <span>Confirma en <strong>"Instalar"</strong>. ¡Listo! La App quedará en tu pantalla.</span>
                </div>
            </div>
            <button type="button" onclick="closePwaModal('pwa-android-modal')" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                ¡Entendido!
            </button>
        </div>
    </div>

    <!-- Modal Guía para iPhone / iPad (Safari) -->
    <div id="pwa-ios-modal" class="pwa-modal hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/85 backdrop-blur-md" onclick="if(event.target===this)closePwaModal('pwa-ios-modal')">
        <div class="relative w-full max-w-sm bg-[#111827] border border-gray-800 rounded-3xl p-6 shadow-2xl space-y-4 text-center">
            <button type="button" onclick="closePwaModal('pwa-ios-modal')" class="absolute top-4 right-4 text-slate-400 hover:text-white p-1 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
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
                    <span>Desliza hacia abajo y toca <strong>"Agregar a pantalla de inicio" ➕</strong>.</span>
                </div>
            </div>
            <button type="button" onclick="closePwaModal('pwa-ios-modal')" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                ¡Entendido!
            </button>
        </div>
    </div>

    <!-- Modal Guía para Computadora (PC / Mac) -->
    <div id="pwa-desktop-modal" class="pwa-modal hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/85 backdrop-blur-md" onclick="if(event.target===this)closePwaModal('pwa-desktop-modal')">
        <div class="relative w-full max-w-sm bg-[#111827] border border-gray-800 rounded-3xl p-6 shadow-2xl space-y-4 text-center">
            <button type="button" onclick="closePwaModal('pwa-desktop-modal')" class="absolute top-4 right-4 text-slate-400 hover:text-white p-1 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center mx-auto">
                <i data-lucide="monitor" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Instalar en Computadora (PC / Mac)</h3>
                <p class="text-xs text-slate-400 mt-1">Disfruta de la App en una ventana rápida e independiente:</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-left text-xs text-slate-300 space-y-2.5">
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">1</span>
                    <span>En Chrome o Edge, haz clic en el ícono <strong>⊕ o 💻 (Instalar)</strong> en la barra de direcciones (URL) a la derecha.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">2</span>
                    <span>O haz clic en el menú <strong>tres puntos (⋮)</strong> y selecciona <strong>"Instalar CStreame..."</strong></span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">3</span>
                    <span>Confirma en <strong>"Instalar"</strong>. ¡Listo! Quedará en tu escritorio y barra de tareas.</span>
                </div>
            </div>
            <button type="button" onclick="closePwaModal('pwa-desktop-modal')" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                ¡Entendido!
            </button>
        </div>
    </div>

    <!-- Modal Guía para WhatsApp / Redes Sociales (In-App WebView) -->
    <div id="pwa-inapp-modal" class="pwa-modal hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/85 backdrop-blur-md" onclick="if(event.target===this)closePwaModal('pwa-inapp-modal')">
        <div class="relative w-full max-w-sm bg-[#111827] border border-gray-800 rounded-3xl p-6 shadow-2xl space-y-4 text-center">
            <button type="button" onclick="closePwaModal('pwa-inapp-modal')" class="absolute top-4 right-4 text-slate-400 hover:text-white p-1 rounded-lg cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center mx-auto">
                <i data-lucide="external-link" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Abrir en el Navegador para Instalar</h3>
                <p class="text-xs text-slate-400 mt-1">Estás dentro del navegador integrado de WhatsApp o redes sociales. Para instalar en tu pantalla:</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-left text-xs text-slate-300 space-y-2.5">
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">1</span>
                    <span>Toca los <strong>tres puntos (⋮)</strong> o el botón <strong>Compartir</strong> arriba a la derecha.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">2</span>
                    <span>Selecciona <strong>"Abrir en Chrome"</strong> (o Safari en iPhone).</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">3</span>
                    <span>¡Desde Chrome o Safari podrás instalar la App en 1 clic!</span>
                </div>
            </div>
            <button type="button" onclick="copyCurrentUrlForBrowser()" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-emerald-600/20">
                <i data-lucide="copy" class="w-4 h-4"></i>
                <span id="copy-url-btn-text">Copiar Enlace para Abrir en Chrome</span>
            </button>
        </div>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

        function closePwaModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.add('hidden');
        }

        function copyCurrentUrlForBrowser() {
            navigator.clipboard.writeText(window.location.href);
            const btn = document.getElementById('copy-url-btn-text');
            if (btn) btn.innerText = '✓ ¡Enlace copiado! Ábrelo en Chrome';
            setTimeout(() => {
                if (btn) btn.innerText = 'Copiar Enlace para Abrir en Chrome';
            }, 3000);
        }

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.pwa-modal').forEach(m => m.classList.add('hidden'));
            }
        });

        async function triggerPwaInstallDirectly(e) {
            if (e && e.preventDefault) e.preventDefault();
            
            // 1. Si ya está instalada la aplicación (modo standalone o previamente instalada)
            if (window.isPwaInstalled || window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
                const toast = document.getElementById('pwa-toast');
                if (toast) {
                    toast.querySelector('.toast-msg').innerText = '¡La aplicación ya está instalada en tu dispositivo!';
                    toast.classList.remove('hidden');
                    setTimeout(() => toast.classList.add('hidden'), 3500);
                } else {
                    alert('¡La aplicación ya se encuentra instalada en este dispositivo!');
                }
                return;
            }

            // 2. Si todavía no se capturó beforeinstallprompt, esperar hasta 1.2 segundos
            if (!window.deferredPwaPrompt) {
                await new Promise((resolve) => {
                    const timer = setTimeout(() => {
                        window.removeEventListener('beforeinstallprompt', handler);
                        resolve(null);
                    }, 1200);

                    function handler(ev) {
                        ev.preventDefault();
                        window.deferredPwaPrompt = ev;
                        clearTimeout(timer);
                        window.removeEventListener('beforeinstallprompt', handler);
                        resolve(ev);
                    }

                    window.addEventListener('beforeinstallprompt', handler);
                });
            }

            // 3. Si se capturó el prompt nativo (Chrome / Edge / Android)
            if (window.deferredPwaPrompt) {
                try {
                    window.deferredPwaPrompt.prompt();
                    const { outcome } = await window.deferredPwaPrompt.userChoice;
                    if (outcome === 'accepted') {
                        window.isPwaInstalled = true;
                        window.deferredPwaPrompt = null;
                        document.querySelectorAll('.pwa-btn-label, #pwa-header-btn-text').forEach(el => {
                            el.innerText = 'App Instalada ✓';
                        });
                        const toast = document.getElementById('pwa-toast');
                        if (toast) {
                            toast.querySelector('.toast-msg').innerText = '¡App instalada con éxito!';
                            toast.classList.remove('hidden');
                            setTimeout(() => toast.classList.add('hidden'), 3500);
                        }
                    }
                    return;
                } catch (err) {
                    console.warn('Install prompt error:', err);
                }
            }

            const userAgent = (window.navigator.userAgent || '').toLowerCase();
            const isInApp = /fban|fbav|instagram|whatsapp|wv|micromessenger|line\//.test(userAgent);
            const isIos = /iphone|ipad|ipod/.test(userAgent);
            const isAndroid = /android/.test(userAgent);

            // 4. Si está dentro de WhatsApp / Redes Sociales
            if (isInApp) {
                const modal = document.getElementById('pwa-inapp-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    if (window.lucide) lucide.createIcons();
                }
                return;
            }

            // 5. Si es iOS Safari (iPhone / iPad)
            if (isIos) {
                const modal = document.getElementById('pwa-ios-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    if (window.lucide) lucide.createIcons();
                }
                return;
            }

            // 6. Si es Android
            if (isAndroid) {
                const modal = document.getElementById('pwa-android-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                    if (window.lucide) lucide.createIcons();
                }
                return;
            }

            // 7. Si es Computadora (PC / Mac)
            const desktopModal = document.getElementById('pwa-desktop-modal');
            if (desktopModal) {
                desktopModal.classList.remove('hidden');
                if (window.lucide) lucide.createIcons();
            } else {
                const androidModal = document.getElementById('pwa-android-modal');
                if (androidModal) {
                    androidModal.classList.remove('hidden');
                    if (window.lucide) lucide.createIcons();
                }
            }
        }

        // Interceptor global para cualquier botón de instalación en la página
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.pwa-install-btn, [data-pwa-install], a[href*="pwa/install"], a[href$="/app"], a[href*="/app?"]');
            if (btn && !btn.hasAttribute('data-no-pwa-intercept')) {
                e.preventDefault();
                triggerPwaInstallDirectly(e);
            }
        });

        lucide.createIcons();
    </script>
</body>
</html>
