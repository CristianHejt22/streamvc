<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Instalar {{ $brandName }} | App Oficial</title>

    <!-- PWA Primary Meta Tags -->
    <meta name="theme-color" content="{{ $primaryColor }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $brandName }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="{{ $brandIcon }}">
    <link rel="icon" type="image/svg+xml" href="{{ $brandIcon }}">

    <!-- Tailwind CSS CDN & Lucide Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            background-color: #0b0f19;
            color: #f8fafc;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between selection:bg-red-500 selection:text-white" x-data="{
    deferredPrompt: window.deferredPwaPrompt || null,
    isInstalled: false,
    isIos: false,
    isAndroid: false,
    isInApp: false,
    showIosGuide: false,
    showAndroidGuide: false,
    showDesktopGuide: false,
    isPreparing: false,
    copied: false,
    init() {
        const userAgent = (window.navigator.userAgent || '').toLowerCase();
        this.isIos = /iphone|ipad|ipod/.test(userAgent);
        this.isAndroid = /android/.test(userAgent);
        this.isInApp = /fban|fbav|instagram|whatsapp|wv|micromessenger|line\//.test(userAgent);

        // Detectar si ya corre en modo instalado Standalone
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
            this.isInstalled = true;
        }

        // Capturar prompt de Chrome / Android
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            window.deferredPwaPrompt = e;
        });

        window.addEventListener('appinstalled', () => {
            this.isInstalled = true;
            this.deferredPrompt = null;
            window.deferredPwaPrompt = null;
        });
    },
    async triggerInstall() {
        if (this.isIos) {
            this.showIosGuide = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            return;
        }

        if (this.isInApp) {
            this.showAndroidGuide = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            return;
        }

        let prompt = this.deferredPrompt || window.deferredPwaPrompt;

        // Si todavía está registrando el service worker, esperar hasta 1.8s
        if (!prompt) {
            this.isPreparing = true;
            prompt = await new Promise((resolve) => {
                const handler = (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                    window.deferredPwaPrompt = e;
                    window.removeEventListener('beforeinstallprompt', handler);
                    resolve(e);
                };
                window.addEventListener('beforeinstallprompt', handler);
                setTimeout(() => {
                    window.removeEventListener('beforeinstallprompt', handler);
                    resolve(null);
                }, 1800);
            });
            this.isPreparing = false;
        }

        if (prompt) {
            try {
                prompt.prompt();
                const { outcome } = await prompt.userChoice;
                if (outcome === 'accepted') {
                    this.isInstalled = true;
                    this.deferredPrompt = null;
                    window.deferredPwaPrompt = null;
                }
            } catch (err) {
                console.warn('Install prompt error:', err);
                if (this.isAndroid) {
                    this.showAndroidGuide = true;
                } else {
                    this.showDesktopGuide = true;
                }
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        } else {
            // El navegador requiere instalación manual vía menú
            if (this.isAndroid) {
                this.showAndroidGuide = true;
            } else {
                this.showDesktopGuide = true;
            }
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        }
    },
    copyShareLink() {
        navigator.clipboard.writeText('{{ $shareUrl }}');
        this.copied = true;
        setTimeout(() => { this.copied = false; }, 2500);
    }
}">

    <!-- Background Ambient Glow -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full blur-3xl opacity-20" style="background-color: {{ $primaryColor }}"></div>
        <div class="absolute top-1/2 -right-40 w-80 h-80 rounded-full blur-3xl opacity-15" style="background-color: {{ $accentColor }}"></div>
    </div>

    <!-- Main Content Container -->
    <main class="relative z-10 max-w-lg mx-auto w-full px-5 py-8 flex-1 flex flex-col justify-center space-y-6">

        <!-- Banner si está dentro del visor de WhatsApp -->
        <template x-if="isInApp">
            <div class="p-4 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs space-y-2 text-left">
                <div class="flex items-center gap-2 font-bold text-amber-200">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-400"></i>
                    <span>Estás navegando dentro de WhatsApp</span>
                </div>
                <p class="text-[11px] text-slate-300 leading-relaxed">
                    Para instalar la App directamente en tu pantalla de inicio:
                </p>
                <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-[11px] font-mono text-slate-300 space-y-1">
                    <p>1. Toca los <strong>3 puntos (⋮)</strong> en la esquina superior derecha.</p>
                    <p>2. Elige <strong>"Abrir en Chrome"</strong> (o "Abrir en navegador").</p>
                </div>
            </div>
        </template>

        <!-- Header / Logo -->
        <div class="text-center space-y-4">
            <div class="inline-flex p-4 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-2xl backdrop-blur-md">
                <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="h-16 w-auto max-w-[200px] object-contain mx-auto drop-shadow-md">
            </div>

            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[11px] font-bold font-mono">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>APLICACIÓN WEB OFICIAL PWA</span>
                    @if($seller && $seller->referral_code)
                    <span>• {{ $seller->referral_code }}</span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight mt-2">
                    {{ $brandName }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-xs mx-auto">
                    Instala nuestra App en tu teléfono, tablet o computadora sin descargar de tiendas. Rápida, ligera y con acceso directo a tu Bóveda.
                </p>
            </div>
        </div>

        <!-- App Features Pills -->
        <div class="grid grid-cols-3 gap-2.5 text-center text-xs">
            <div class="p-3 rounded-2xl bg-slate-900/80 border border-slate-800">
                <i data-lucide="zap" class="w-5 h-5 text-amber-400 mx-auto mb-1"></i>
                <span class="text-[11px] font-bold text-white block">Súper Ligera</span>
                <span class="text-[9px] text-slate-400">Menos de 2 MB</span>
            </div>
            <div class="p-3 rounded-2xl bg-slate-900/80 border border-slate-800">
                <i data-lucide="shield-check" class="w-5 h-5 text-emerald-400 mx-auto mb-1"></i>
                <span class="text-[11px] font-bold text-white block">Bóveda PIN</span>
                <span class="text-[9px] text-slate-400">Con Biometría</span>
            </div>
            <div class="p-3 rounded-2xl bg-slate-900/80 border border-slate-800">
                <i data-lucide="bell" class="w-5 h-5 text-sky-400 mx-auto mb-1"></i>
                <span class="text-[11px] font-bold text-white block">Entrega 24/7</span>
                <span class="text-[9px] text-slate-400">Sin esperas</span>
            </div>
        </div>

        <!-- Primary Action: Install Button -->
        <div class="space-y-3">
            <template x-if="!isInstalled">
                <button type="button" @click="triggerInstall()" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-red-600 via-rose-600 to-amber-600 hover:opacity-95 text-white font-black text-sm uppercase tracking-wider flex items-center justify-center gap-3 shadow-2xl shadow-red-600/40 transition-all cursor-pointer transform active:scale-[0.98]">
                    <i data-lucide="download" class="w-5 h-5"></i>
                    <span x-text="isPreparing ? 'Preparando Instalación...' : '📲 Instalar Aplicación Ahora'"></span>
                </button>
            </template>

            <template x-if="isInstalled">
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold text-center flex items-center justify-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span>¡App instalada en este dispositivo! Ya puedes acceder desde tu pantalla principal.</span>
                </div>
            </template>

            <!-- WhatsApp Share Button -->
            <a href="{{ $whatsappShareUrl }}" target="_blank" class="w-full py-3.5 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xl shadow-emerald-600/30 transition-all cursor-pointer">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>💬 Compartir App por WhatsApp</span>
            </a>

            <!-- Copy Short URL Button -->
            <button type="button" @click="copyShareLink()" class="w-full py-3 px-4 rounded-xl bg-slate-900/90 hover:bg-slate-800 border border-slate-800 text-slate-300 font-mono text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                <i data-lucide="copy" class="w-3.5 h-3.5 text-sky-400"></i>
                <span x-text="copied ? '✓ ¡Link copiado al portapapeles!' : 'Copiar URL Corta: {{ $shareUrl }}'"></span>
            </button>
        </div>

        <!-- Direct Portal Navigation -->
        <div class="pt-4 border-t border-slate-800 grid grid-cols-2 gap-3 text-xs">
            <a href="{{ $catalogUrl }}" class="p-3 rounded-2xl bg-slate-950/90 border border-slate-800 hover:border-slate-700 text-center transition-all">
                <i data-lucide="tv" class="w-4 h-4 text-red-400 mx-auto mb-1"></i>
                <strong class="text-white block">Catálogo Web</strong>
                <span class="text-[10px] text-slate-400">Ver Cuentas</span>
            </a>

            <a href="{{ $vaultUrl }}" class="p-3 rounded-2xl bg-slate-950/90 border border-slate-800 hover:border-slate-700 text-center transition-all">
                <i data-lucide="key" class="w-4 h-4 text-amber-400 mx-auto mb-1"></i>
                <strong class="text-white block">Bóveda Clientes</strong>
                <span class="text-[10px] text-slate-400">Ver Credenciales</span>
            </a>
        </div>

        @if($supportWhatsapp)
        <!-- Contact Reseller / Support -->
        <div class="text-center pt-2">
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsapp) }}?text=Hola%20{{ urlencode($brandName) }},%20tengo%20una%20consulta%20sobre%20la%20App%20de%20streaming." target="_blank" class="text-xs text-slate-400 hover:text-emerald-400 transition-colors inline-flex items-center gap-1 font-mono">
                <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
                <span>¿Necesitas ayuda? Contactar Soporte</span>
            </a>
        </div>
        @endif
    </main>

    <!-- Android Guide Modal -->
    <div x-show="showAndroidGuide" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/85 backdrop-blur-md">
        <div @click.away="showAndroidGuide = false" class="w-full max-w-sm bg-[#111827] border border-gray-800 rounded-3xl p-6 shadow-2xl space-y-4 text-center">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center mx-auto">
                <i data-lucide="smartphone" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Instalar en Android (Chrome)</h3>
                <p class="text-xs text-slate-400 mt-1">Sigue estos 3 pasos rápidos en tu navegador:</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-left text-xs text-slate-300 space-y-2.5">
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">1</span>
                    <span>Toca los <strong>tres puntos (⋮)</strong> en la esquina superior derecha de Chrome.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">2</span>
                    <span>Selecciona <strong>"Instalar aplicación"</strong> o <strong>"Agregar a la pantalla principal"</strong> 📲.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">3</span>
                    <span>Confirma en <strong>"Instalar"</strong>. ¡Listo! La App aparecerá en tu menú.</span>
                </div>
            </div>
            <button type="button" @click="showAndroidGuide = false" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                Entendido
            </button>
        </div>
    </div>

    <!-- iOS Guide Modal -->
    <div x-show="showIosGuide" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/85 backdrop-blur-md">
        <div @click.away="showIosGuide = false" class="w-full max-w-sm bg-[#111827] border border-gray-800 rounded-3xl p-6 shadow-2xl space-y-4 text-center">
            <div class="w-12 h-12 rounded-2xl bg-sky-500/20 text-sky-400 border border-sky-500/30 flex items-center justify-center mx-auto">
                <i data-lucide="smartphone" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Instalar en iPhone / iPad (Safari)</h3>
                <p class="text-xs text-slate-400 mt-1">Apple requiere 2 simples toques para instalar la aplicación:</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-left text-xs text-slate-300 space-y-2.5">
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-sky-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">1</span>
                    <span>Toca el botón <strong>Compartir</strong> <span class="text-sky-400 font-mono text-base">⎋</span> en la barra inferior de Safari.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-sky-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">2</span>
                    <span>Desliza hacia abajo y selecciona <strong>"Agregar a pantalla de inicio" ➕</strong>.</span>
                </div>
            </div>
            <button type="button" @click="showIosGuide = false" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                Entendido
            </button>
        </div>
    </div>

    <!-- Desktop Guide Modal -->
    <div x-show="showDesktopGuide" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md">
        <div @click.away="showDesktopGuide = false" class="w-full max-w-sm bg-[#111827] border border-gray-800 rounded-3xl p-6 shadow-2xl space-y-4 text-center">
            <div class="w-12 h-12 rounded-2xl bg-purple-500/20 text-purple-400 border border-purple-500/30 flex items-center justify-center mx-auto">
                <i data-lucide="monitor" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Instalar en Computadora (PC / Mac)</h3>
                <p class="text-xs text-slate-400 mt-1">Instálala como aplicación de escritorio nativa:</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-left text-xs text-slate-300 space-y-2.5">
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-purple-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">1</span>
                    <span>Mira en la <strong>barra de direcciones (URL)</strong> de Chrome o Edge.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-purple-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">2</span>
                    <span>Haz clic en el icono <strong>Instalar ⊕ (o pantalla con flecha)</strong>.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-purple-500 text-slate-950 font-bold flex items-center justify-center text-[10px] shrink-0 mt-0.5">3</span>
                    <span>Confirma en <strong>"Instalar"</strong>. Se abrirá como una ventana independiente.</span>
                </div>
            </div>
            <button type="button" @click="showDesktopGuide = false" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                Entendido
            </button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="relative z-10 py-4 text-center text-[11px] text-slate-500">
        © {{ date('Y') }} {{ $brandName }}. Todos los derechos reservados.
    </footer>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((reg) => console.log('PWA ServiceWorker registrado:', reg.scope))
                    .catch((err) => console.warn('PWA ServiceWorker error:', err));
            });
        }
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>
