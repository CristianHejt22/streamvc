@extends('layouts.public')

@section('title', 'Bóveda Privada de Credenciales de Streaming')

@section('content')
@php
    $effectiveBrand = $reseller ? $reseller->getEffectiveBrandName() : config('app.name', 'CStreame Master');
    $brandLogo = $reseller ? $reseller->brand_logo_url : asset('images/spro-play-logo.svg');
    // Soporte exclusivo: Revendedor para Cliente. Si el vendedor es Admin, usa el del panel principal.
    $whatsapp = $supportWhatsAppPhone ?: ($reseller ? $reseller->resolved_vault_whatsapp : null);
    $referralCode = ($reseller && $reseller->referral_code) ? $reseller->referral_code : '';
    $antiPhishingCode = $customer->anti_phishing_phrase ?: 'CST-SAFE-' . ($customer->customer_code ?: $customer->id);
@endphp

<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 space-y-8" x-data="{
    changePinModal: false,
    reportModal: false,
    unlockModal: false,
    renewModal: false,
    guideModal: false,
    renewSale: null,
    renewMonths: 1,
    renewPricePerMonth: 0,
    vaultUnlocked: false,
    unlockTab: 'biometric',
    unlockPassword: '',
    unlockError: '',
    isVerifying: false,
    selectedSale: null,
    showPasswords: {},
    openRenewModal(sale) {
        this.renewSale = sale;
        this.renewMonths = 1;
        this.renewPricePerMonth = (sale.plan && sale.plan.price) ? parseFloat(sale.plan.price) : parseFloat(sale.sale_price);
        this.renewModal = true;
    },
    togglePassword(id) {
        if (!this.vaultUnlocked) {
            this.unlockModal = true;
            return;
        }
        this.showPasswords[id] = !this.showPasswords[id];
    },
    copyToClipboard(text, label) {
        if (!this.vaultUnlocked && (label === 'Contraseña' || label === 'PIN')) {
            this.unlockModal = true;
            return;
        }
        navigator.clipboard.writeText(text);
        alert(label + ' copiado al portapapeles: ' + text);
    },
    openReportModal(sale) {
        this.selectedSale = sale;
        this.reportModal = true;
    },
    lockVault() {
        this.vaultUnlocked = false;
        this.showPasswords = {};
    },
    async triggerBiometric() {
        this.isVerifying = true;
        this.unlockError = '';
        try {
            // Intentar autenticación biométrica en el dispositivo (WebAuthn / Sensor de huella / FaceID)
            if (window.PublicKeyCredential && PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable) {
                await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
            }

            const response = await fetch('{{ route('boveda.verify_unlock') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ type: 'biometric' })
            });

            const data = await response.json();
            if (data.success) {
                this.vaultUnlocked = true;
                this.unlockModal = false;
            } else {
                this.unlockError = data.message || 'Fallo en la verificación biométrica.';
            }
        } catch (err) {
            this.unlockError = 'No se pudo comunicar con el sensor biométrico del dispositivo.';
        } finally {
            this.isVerifying = false;
        }
    },
    async verifyPassword() {
        if (!this.unlockPassword) {
            this.unlockError = 'Ingresa tu contraseña o PIN de Bóveda.';
            return;
        }
        this.isVerifying = true;
        this.unlockError = '';
        try {
            const response = await fetch('{{ route('boveda.verify_unlock') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    type: 'password',
                    password: this.unlockPassword
                })
            });

            const data = await response.json();
            if (data.success) {
                this.vaultUnlocked = true;
                this.unlockModal = false;
                this.unlockPassword = '';
            } else {
                this.unlockError = data.message || 'Contraseña incorrecta.';
            }
        } catch (err) {
            this.unlockError = 'Error de conexión al validar contraseña.';
        } finally {
            this.isVerifying = false;
        }
    }
}">

    <!-- Top Client & Brand Banner con Sello Anti-Phishing y Desbloqueo Biométrico -->
    <div class="bg-gradient-to-r from-[#0f172a] via-[#1e293b] to-[#0f172a] p-6 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl relative overflow-hidden space-y-4">
        <!-- Glow effects -->
        <div class="absolute -top-12 -left-12 w-48 h-48 bg-red-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-red-600 to-amber-500 flex items-center justify-center text-white shadow-xl shadow-red-600/20 text-2xl font-black">
                    <i data-lucide="shield-check" class="w-8 h-8"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-red-600/20 text-red-400 border border-red-500/30">
                            {{ $customer->customer_code ?: 'CLI-' . $customer->id }}
                        </span>
                        <!-- Sello Anti-Phishing Oficial -->
                        <span class="text-[11px] font-mono font-bold px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                            <i data-lucide="shield-alert" class="w-3 h-3"></i>
                            <span>Anti-Phishing: <strong>{{ $antiPhishingCode }}</strong></span>
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white mt-1">¡Hola, {{ $customer->name }}!</h1>
                    <p class="text-xs text-slate-400 flex items-center gap-1.5 mt-0.5">
                        <span>Distribuidor autorizado:</span>
                        <strong class="text-amber-400 font-bold">{{ $effectiveBrand }}</strong>
                        @if($reseller && $reseller->isPanelBasic())
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-gradient-to-r from-amber-500 to-red-500 text-white font-mono tracking-wider">SPRO PLAY</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Botones de Acción y Desbloqueo Biométrico -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Botón de Estado de Bloqueo / Desbloqueo Biométrico -->
                <template x-if="!vaultUnlocked">
                    <button @click="unlockModal = true" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-red-600 hover:opacity-90 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-red-600/20 transition-all cursor-pointer">
                        <i data-lucide="fingerprint" class="w-4 h-4"></i>
                        <span>Desbloquear Credenciales</span>
                    </button>
                </template>

                <template x-if="vaultUnlocked">
                    <button @click="lockVault()" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2 shadow-lg transition-all cursor-pointer">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        <span>Bóveda Desbloqueada (Bloquear)</span>
                    </button>
                </template>

                <button type="button" onclick="triggerPwaInstallDirectly(event)" class="pwa-install-btn px-3.5 py-2.5 rounded-xl bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 border border-purple-500/30 font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer active:scale-95" title="Instalar App de Streaming en tu dispositivo">
                    <i data-lucide="smartphone" class="w-4 h-4 text-purple-400"></i>
                    <span class="pwa-btn-label">Instalar App</span>
                </button>

                <button @click="changePinModal = true" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 font-bold text-xs flex items-center gap-1.5 border border-amber-500/30 transition-all cursor-pointer" title="Ajustar tu PIN personal de Bóveda">
                    <i data-lucide="key" class="w-4 h-4"></i>
                    <span>{{ $customer->security_pin ? 'Mi PIN (4 Dígitos)' : 'Ajustar PIN de Bóveda' }}</span>
                </button>

                @if($whatsappSupportEnabled && !empty($supportWhatsAppPhone))
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsAppPhone) }}?text=Hola,%20soy%20{{ urlencode($customer->name) }}%20(N°%20Cliente:%20{{ $customer->customer_code }})%20y%20tengo%20una%20consulta%20sobre%20mi%20boveda%20de%20streaming." target="_blank" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2 shadow-lg transition-all">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Soporte WhatsApp</span>
                </a>
                @endif

                <form method="POST" action="{{ route('boveda.logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs flex items-center gap-1.5 border border-slate-700 transition-all cursor-pointer">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Banner de Estado de Seguridad Anti-Phishing -->
        <div class="pt-3 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400">
            <span class="flex items-center gap-1.5 text-emerald-400">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                <span>Servidor Cifrado AES-256 en Base de Datos & Conexión Certificada</span>
            </span>
            <span class="text-slate-500 font-mono">
                Autenticación biométrica WebAuthn habilitada
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2.5">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
        <span class="font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    @if(empty($customer->security_pin))
    <!-- Alerta: Ajustar PIN de Bóveda en Primera Compra -->
    <div class="p-4 rounded-2xl bg-gradient-to-r from-amber-950/40 via-slate-900 to-amber-950/30 border border-amber-500/40 shadow-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold shrink-0">
                <i data-lucide="key" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h4 class="text-sm font-black text-white">Ajusta tu PIN de Bóveda (4 Dígitos)</h4>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-amber-500 text-black font-mono">SEGURIDAD</span>
                </div>
                <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">
                    Define tu PIN personal de 4 dígitos. Sirve para consultar y revelar tus contraseñas en cualquier celular o PC cuando no uses biometría. ¡Debes recordarlo siempre!
                </p>
            </div>
        </div>
        <button @click="changePinModal = true" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20 shrink-0 cursor-pointer">
            Establecer Mi PIN
        </button>
    </div>
    @endif

    <!-- SECCIÓN: CUENTAS ACTIVAS CON CREDENCIALES OFICIALES -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-3 h-3 rounded-full bg-emerald-400 animate-ping"></div>
                <h2 class="text-xl font-black text-white">Tus Servicios & Credenciales Activas</h2>
            </div>
            <span class="text-xs font-mono font-bold text-slate-400 bg-slate-900 px-3 py-1 rounded-xl border border-slate-800">
                {{ $activeSales->count() }} Suscripción(es) Activa(s)
            </span>
        </div>

        @if($activeSales->isEmpty())
        <div class="text-center py-12 px-6 rounded-3xl bg-[#0f172a]/60 border border-slate-800 border-dashed space-y-3">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-800/80 flex items-center justify-center text-slate-400 text-2xl">
                🍿
            </div>
            <h3 class="text-lg font-bold text-white">Aún no tienes suscripciones activas en tu Bóveda</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">
                Elige cualquiera de las plataformas líderes a continuación (Netflix, HBO Max, Disney Estándar, Disney Premium) para solicitar tu cuenta oficial de inmediato.
            </p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($activeSales as $sale)
            @php
                $srv = $sale->service;
                $srvName = $srv ? $srv->name : 'Streaming Oficial';
                $srvColor = $srv ? ($srv->color_hex ?? '#E50914') : '#E50914';
                $planName = $sale->plan ? $sale->plan->name : 'Plan Estándar';
                $days = $sale->days_remaining;
                $badge = $sale->status_badge ?? [
                    'label' => 'Activa (Garantía OK)',
                    'class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                    'icon' => 'check-circle'
                ];
                $endDate = $sale->end_date ? \Carbon\Carbon::parse($sale->end_date)->format('d/m/Y') : '30 días';

                // REGLA: No se entregará sin monto en billetera o si no se recibe el pago del proveedor
                $blockReason = null;
                $canDeliver = $sale->canDeliverCredentials($blockReason);
            @endphp
            <div class="bg-[#0f172a] rounded-3xl border border-slate-800 shadow-xl overflow-hidden hover:border-slate-700 transition-all flex flex-col justify-between">
                <!-- Card Header -->
                <div class="p-5 pb-4 border-b border-slate-800/80 flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full shrink-0 shadow" style="background-color: {{ $srvColor }}"></span>
                        <div>
                            <h3 class="font-black text-white text-base">{{ $srvName }}</h3>
                            <span class="text-xs text-sky-400 font-mono">{{ $planName }} ({{ $sale->months_duration ?: 1 }} mes/es)</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full border {{ $badge['class'] }}">
                        {{ $badge['label'] }}
                    </span>
                </div>

                <!-- ALERTA DE CONSIGNACIÓN / ENTREGA RETENIDA -->
                @if(!$canDeliver)
                <div class="p-4 mx-5 mt-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs space-y-2">
                    <div class="flex items-center gap-2 font-black text-amber-400">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-amber-400 animate-pulse"></i>
                        <span>ACCESO RETENIDO - PAGO PENDIENTE DE ACREDITACIÓN</span>
                    </div>
                    <p class="text-slate-300 leading-relaxed">{{ $blockReason }}</p>
                    <div class="pt-2 border-t border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-amber-400 font-sans font-semibold">
                        <span>🔒 Las credenciales se liberarán automáticamente al confirmarse la acreditación.</span>
                        @if($whatsappSupportEnabled && !empty($supportWhatsAppPhone))
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsAppPhone) }}?text=Hola,%20tengo%20una%20consulta%20sobre%20mi%20cuenta%20{{ urlencode($srvName) }}%20(Venta:%20{{ $sale->sale_code }}).%20Figura%20retenida%20por%20pago." target="_blank" class="underline hover:text-white flex items-center gap-1 w-max">
                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                            <span>Consultar a Soporte</span>
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                <!-- ALERTA DE SOLICITUD DE RENOVACIÓN PENDIENTE -->
                @if($sale->latestRenewalRequest && $sale->latestRenewalRequest->isPending())
                <div class="p-3 mx-5 mt-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-300 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="refresh-cw" class="w-4 h-4 text-cyan-400 animate-spin"></i>
                        <span><strong>Solicitud de Renovación en Proceso:</strong> {{ $sale->latestRenewalRequest->months_duration }} mes(es) por ${{ number_format($sale->latestRenewalRequest->amount, 2) }}. Tu revendedor la confirmará en breve.</span>
                    </div>
                </div>
                @endif

                <!-- Credenciales Oficiales -->
                <div class="p-5 space-y-4 text-xs font-mono">
                    @if(!$canDeliver)
                    <!-- Estado de Retención Estricto -->
                    <div class="bg-slate-900/90 p-4 rounded-2xl border border-dashed border-amber-500/40 text-center space-y-2">
                        <i data-lucide="lock" class="w-8 h-8 text-amber-400 mx-auto opacity-80"></i>
                        <h4 class="text-white font-bold text-sm">Credenciales No Entregadas</h4>
                        <p class="text-[11px] text-slate-400 font-sans">
                            Por políticas comerciales y de seguridad, las claves de acceso se mantienen retenidas hasta que se liquide el saldo en la billetera o se reciba el pago del proveedor.
                        </p>
                        <span class="inline-block px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-bold font-mono">
                            ESTADO: RETENIDO HASTA PAGO
                        </span>
                    </div>
                    @else
                    <!-- Correo Oficial -->
                    <div class="bg-slate-900/90 p-3 rounded-2xl border border-slate-800">
                        <span class="text-[10px] uppercase font-bold text-slate-400 font-sans block mb-1">Correo Electrónico Oficial</span>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-white text-sm font-bold truncate">{{ $sale->account_email_snapshot }}</span>
                            <button @click="copyToClipboard('{{ $sale->account_email_snapshot }}', 'Correo')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-sky-400 hover:text-white transition-colors text-xs font-sans font-bold flex items-center gap-1 cursor-pointer">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span>Copiar</span>
                            </button>
                        </div>
                    </div>

                    <!-- Contraseña Oficial (Protegida por Biometría) -->
                    <div class="bg-slate-900/90 p-3 rounded-2xl border border-slate-800">
                        <span class="text-[10px] uppercase font-bold text-slate-400 font-sans block mb-1">Contraseña de Acceso</span>
                        <div class="flex items-center justify-between gap-2">
                            <!-- Si la Bóveda está Desbloqueada -->
                            <template x-if="vaultUnlocked">
                                <div class="flex items-center justify-between w-full">
                                    <div>
                                        <span x-show="!showPasswords[{{ $sale->id }}]" class="text-amber-400 text-sm font-bold tracking-widest">••••••••</span>
                                        <span x-show="showPasswords[{{ $sale->id }}]" class="text-amber-400 text-sm font-bold">{{ $sale->account_password_snapshot }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 font-sans">
                                        <button type="button" @click="togglePassword({{ $sale->id }})" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors text-xs font-bold cursor-pointer">
                                            <span x-text="showPasswords[{{ $sale->id }}] ? 'Ocultar' : 'Ver'"></span>
                                        </button>
                                        <button @click="copyToClipboard('{{ $sale->account_password_snapshot }}', 'Contraseña')" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-400 hover:text-white transition-colors text-xs font-bold flex items-center gap-1 cursor-pointer">
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                            <span>Copiar</span>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- Si la Bóveda está Bloqueada -->
                            <template x-if="!vaultUnlocked">
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-slate-500 font-bold tracking-wider">🔒 Clave Bloqueada</span>
                                    <button @click="unlockModal = true" class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 text-xs font-sans font-bold flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="fingerprint" class="w-3.5 h-3.5"></i>
                                        <span>Desbloquear</span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Perfil y PIN Asignado (Protegido por Biometría) -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-900/90 p-3 rounded-2xl border border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 font-sans block mb-1">Perfil Asignado</span>
                            <span class="text-white font-bold text-xs block truncate">{{ $sale->profile_name_snapshot ?: 'Perfil 1' }}</span>
                        </div>

                        <div class="bg-slate-900/90 p-3 rounded-2xl border border-slate-800">
                            <span class="text-[10px] uppercase font-bold text-slate-400 font-sans block mb-1">PIN de Seguridad</span>
                            <template x-if="vaultUnlocked">
                                <div class="flex items-center justify-between">
                                    <span class="text-emerald-400 font-black text-sm">{{ $sale->profile_pin_snapshot ?: 'Sin PIN' }}</span>
                                    @if($sale->profile_pin_snapshot)
                                    <button @click="copyToClipboard('{{ $sale->profile_pin_snapshot }}', 'PIN')" class="text-slate-400 hover:text-emerald-400 cursor-pointer">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    </button>
                                    @endif
                                </div>
                            </template>
                            <template x-if="!vaultUnlocked">
                                <span class="text-slate-500 font-bold">🔒 PIN Oculto</span>
                            </template>
                        </div>
                    </div>
                    @endif

                    <!-- Vencimiento SLA & Barra de Progreso Dinámica -->
                    @php
                        $totalDays = max(1, ($sale->months_duration ?: 1) * 30);
                        $remainingDays = max(0, $days);
                        $pctRemaining = min(100, max(0, round(($remainingDays / $totalDays) * 100)));
                        $barColor = $days > 7 ? 'bg-emerald-500' : ($days > 3 ? 'bg-amber-500' : 'bg-rose-500 animate-pulse');
                    @endphp
                    <div class="bg-slate-950 p-3 rounded-2xl border border-slate-900 space-y-2 text-xs font-sans">
                        <div class="flex items-center justify-between text-xs">
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase block">Vencimiento Oficial</span>
                                <span class="font-bold text-slate-200">{{ $endDate }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] font-bold {{ $days <= 3 ? 'text-rose-400 animate-pulse' : ($days <= 7 ? 'text-amber-400' : 'text-emerald-400') }}">
                                    {{ $days < 0 ? 'Expiró hace ' . abs($days) . ' días' : ($days === 0 ? '¡Vence hoy!' : 'Quedan ' . $days . ' días (' . $pctRemaining . '%)') }}
                                </span>
                            </div>
                        </div>
                        <div class="w-full bg-slate-900 h-2 rounded-full overflow-hidden border border-slate-800">
                            <div class="h-full {{ $barColor }} rounded-full transition-all duration-500" style="width: {{ $pctRemaining }}%"></div>
                        </div>
                        @if($sale->renewal_count > 0)
                        <div class="text-[10px] text-sky-400 font-mono text-right flex items-center justify-end gap-1">
                            <i data-lucide="check" class="w-3 h-3 text-sky-400"></i>
                            <span>Renovada {{ $sale->renewal_count }} vez/veces con éxito</span>
                        </div>
                        @endif
                    </div>

                    <!-- Botón de Guía Anti-Bloqueo de Hogar -->
                    <button type="button" @click="guideModal = true" class="w-full p-2.5 rounded-xl bg-slate-900/90 hover:bg-slate-800 text-sky-300 hover:text-white border border-slate-800 text-[11px] font-sans font-semibold flex items-center justify-between transition-colors cursor-pointer">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="tv" class="w-3.5 h-3.5 text-sky-400"></i>
                            <span>¿Cómo conectar en Smart TV sin bloqueo de Hogar?</span>
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-500"></i>
                    </button>

                    <!-- Reglas de Uso -->
                    <div class="p-2.5 rounded-xl bg-amber-500/5 border border-amber-500/20 text-[11px] text-amber-300 font-sans leading-relaxed">
                        ⚠️ <strong>Reglas de Uso:</strong> Prohibido modificar correo o contraseña. Ingresar ÚNICAMENTE al perfil asignado con tu PIN. Válido para 1 dispositivo a la vez.
                    </div>
                </div>

                <!-- Footer Card Actions -->
                <div class="p-4 bg-slate-950/60 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-2">
                    <button @click="openReportModal({{ json_encode($sale) }})" class="px-3 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                        <span>Reportar Falla</span>
                    </button>

                    <div class="flex items-center gap-2">
                        @if($whatsappSupportEnabled && !empty($supportWhatsAppPhone))
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsAppPhone) }}?text=Hola,%20necesito%20soporte%20con%20mi%20cuenta%20de%20*{{ urlencode($srvName) }}*%20(Código:%20{{ $sale->sale_code }},%20Perfil:%20{{ urlencode($sale->profile_name_snapshot) }}).%20Cliente:%20{{ urlencode($customer->name) }}." target="_blank" class="px-3 py-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold transition-all flex items-center gap-1.5" title="Soporte oficial por WhatsApp">
                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                            <span>Soporte</span>
                        </a>
                        @endif

                        <button type="button" @click="openRenewModal({{ json_encode($sale) }})" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:opacity-90 text-white text-xs font-black transition-all flex items-center gap-1.5 shadow-lg shadow-emerald-600/20 cursor-pointer">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span>Renovar Cuenta</span>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    @if(isset($historySales) && $historySales->isNotEmpty())
    <!-- SECCIÓN: HISTORIAL DE SERVICIOS ANTERIORES -->
    <div class="space-y-4 pt-4 border-t border-slate-800/80" x-data="{ showHistory: false }">
        <div class="flex items-center justify-between cursor-pointer" @click="showHistory = !showHistory">
            <div class="flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-slate-400"></i>
                <h3 class="text-sm font-bold text-slate-300">Historial de Cuentas Anteriores / Vencidas</h3>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-slate-800 text-slate-400">
                    {{ $historySales->count() }}
                </span>
            </div>
            <button type="button" class="text-xs text-sky-400 hover:text-sky-300 font-semibold flex items-center gap-1">
                <span x-text="showHistory ? 'Ocultar' : 'Ver Historial'"></span>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': showHistory }"></i>
            </button>
        </div>

        <div x-show="showHistory" x-cloak class="space-y-2">
            @foreach($historySales as $hSale)
            @php
                $hSrv = $hSale->service;
                $hSrvName = $hSrv ? $hSrv->name : 'Streaming';
                $hEndDate = $hSale->end_date ? \Carbon\Carbon::parse($hSale->end_date)->format('d/m/Y') : '-';
            @endphp
            <div class="p-3.5 rounded-2xl bg-[#0f172a]/50 border border-slate-800/80 flex items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-600"></span>
                    <div>
                        <span class="font-bold text-white">{{ $hSrvName }}</span>
                        <span class="text-slate-500 font-mono text-[11px] block">{{ $hSale->plan ? $hSale->plan->name : 'Individual' }} • Venció el {{ $hEndDate }}</span>
                    </div>
                </div>
                <button type="button" @click="openRenewModal({{ json_encode($hSale) }})" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] transition-all flex items-center gap-1 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                    <span>Renovar / Reactivar</span>
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- SECCIÓN: ACTIVAR OTRAS PLATAFORMAS (CATÁLOGO RÁPIDO) -->
    <div class="space-y-4 pt-6 border-t border-slate-800">
        <div>
            <h2 class="text-xl font-black text-white">Activar Nuevas Plataformas de Streaming</h2>
            <p class="text-xs text-slate-400">Agrega más pantallas a tu Bóveda con activación garantizada</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($catalogServices as $csrv)
            <div class="bg-[#0f172a] p-5 rounded-2xl border border-slate-800 space-y-3 flex flex-col justify-between hover:border-slate-700 transition-all">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full shrink-0" style="background-color: {{ $csrv->color_hex ?? '#E50914' }}"></span>
                        <h4 class="font-bold text-white text-sm">{{ $csrv->name }}</h4>
                    </div>
                    <p class="text-[11px] text-slate-400 line-clamp-2">{{ $csrv->description }}</p>
                </div>

                <div class="pt-2 border-t border-slate-800/80">
                    <a href="{{ route('catalog.index', array_filter(['ref' => $referralCode])) }}" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-red-600 hover:text-white text-slate-200 text-xs font-bold transition-all flex items-center justify-center gap-1.5">
                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                        <span>Ver Planes & Activar</span>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL DE DESBLOQUEO BIOMÉTRICO O CONTRASEÑA -->
    <!-- ========================================== -->
    <div x-show="unlockModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md" @click.self="unlockModal = false">
        <div class="bg-[#0f172a] p-6 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl max-w-md w-full space-y-6 relative overflow-hidden">
            <!-- Glow background -->
            <div class="absolute -top-16 -right-16 w-36 h-36 bg-amber-500/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5 text-amber-400">
                    <i data-lucide="shield-lock" class="w-6 h-6"></i>
                    <h3 class="font-black text-white text-base">Desbloquear Credenciales</h3>
                </div>
                <button @click="unlockModal = false" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <p class="text-xs text-slate-400 leading-relaxed">
                Por seguridad anti-phishing, las contraseñas maestras y PINs de streaming requieren validación biométrica en tu dispositivo o confirmación de tu contraseña de Bóveda.
            </p>

            <!-- Pestañas de Selección: Biometría vs Contraseña -->
            <div class="grid grid-cols-2 gap-2 bg-slate-900/80 p-1 rounded-2xl border border-slate-800">
                <button type="button" @click="unlockTab = 'biometric'" :class="unlockTab === 'biometric' ? 'bg-red-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="fingerprint" class="w-4 h-4"></i>
                    <span>Biometría</span>
                </button>
                <button type="button" @click="unlockTab = 'password'" :class="unlockTab === 'password' ? 'bg-red-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="key" class="w-4 h-4"></i>
                    <span>Contraseña / PIN</span>
                </button>
            </div>

            <!-- Error message if any -->
            <div x-show="unlockError" x-cloak class="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs">
                <span x-text="unlockError"></span>
            </div>

            <!-- OPCIÓN 1: BIOMETRÍA -->
            <div x-show="unlockTab === 'biometric'" class="space-y-4 text-center py-2">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-3xl shadow-lg">
                    <i data-lucide="fingerprint" class="w-10 h-10 animate-pulse"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-sm">Autenticación Biométrica</h4>
                    <p class="text-[11px] text-slate-400 mt-1">Usa tu Huella Dactilar, Face ID o Windows Hello registrado en tu dispositivo.</p>
                </div>
                <button type="button" @click="triggerBiometric()" :disabled="isVerifying" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-600 to-red-600 hover:opacity-90 text-white font-black text-xs tracking-wide shadow-lg shadow-red-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="scan" class="w-4 h-4"></i>
                    <span x-text="isVerifying ? 'Verificando huella...' : 'Escanear Huella / Face ID'"></span>
                </button>
            </div>

            <!-- OPCIÓN 2: CONTRASEÑA O PIN -->
            <div x-show="unlockTab === 'password'" class="space-y-4 py-2">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Contraseña o PIN de Bóveda *</label>
                    <input type="password" x-model="unlockPassword" @keydown.enter.prevent="verifyPassword()" placeholder="••••••••" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-sm text-white placeholder-slate-500 outline-none focus:border-red-500">
                </div>
                <button type="button" @click="verifyPassword()" :disabled="isVerifying" class="w-full py-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-black text-xs tracking-wide shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="unlock" class="w-4 h-4"></i>
                    <span x-text="isVerifying ? 'Verificando...' : 'Desbloquear Credenciales'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL DE REPORTE DE INCIDENCIA / CAÍDA -->
    <div x-show="reportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click.self="reportModal = false">
        <div class="bg-[#0f172a] p-6 rounded-3xl border border-slate-800 shadow-2xl max-w-md w-full space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2 text-rose-400">
                    <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                    <h3 class="font-black text-white text-base">Reportar Problema en la Cuenta</h3>
                </div>
                <button @click="reportModal = false" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <p class="text-xs text-slate-400">
                Si la contraseña fue cambiada por el proveedor o la pantalla no te permite ingresar, escribe los detalles para que tu distribuidor verifique el stock y te asigne reposición de garantía.
            </p>

            <form x-bind:action="'/boveda/report-issue/' + (selectedSale ? selectedSale.id : '')" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Motivo del Reporte *</label>
                    <select name="reason_type" class="w-full p-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white outline-none mb-2" onchange="document.getElementById('reason_text').value = this.value">
                        <option value="Contraseña incorrecta al iniciar sesión">Contraseña incorrecta al iniciar sesión</option>
                        <option value="Perfil ocupado por otra persona">Perfil ocupado por otra persona</option>
                        <option value="Cuenta con suscripción pausada o caída">Cuenta con suscripción pausada o caída</option>
                        <option value="PIN no coincide con el perfil">PIN no coincide con el perfil</option>
                        <option value="Otro inconveniente">Otro inconveniente</option>
                    </select>
                    <textarea id="reason_text" name="reason" rows="3" required placeholder="Describe brevemente lo que sucede..." class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white placeholder-slate-500 outline-none focus:border-red-500">Contraseña incorrecta al iniciar sesión</textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="reportModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-bold hover:bg-slate-700">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-black shadow-lg">
                        Enviar Reporte de Garantía
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL DE SOLICITUD DE RENOVACIÓN DE CUENTA -->
    <!-- ========================================== -->
    <div x-show="renewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md" @click.self="renewModal = false">
        <div class="bg-[#0f172a] p-6 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl max-w-lg w-full space-y-5 relative overflow-hidden">
            <!-- Glow background -->
            <div class="absolute -top-16 -right-16 w-36 h-36 bg-emerald-500/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5 text-emerald-400">
                    <i data-lucide="refresh-cw" class="w-6 h-6"></i>
                    <div>
                        <h3 class="font-black text-white text-base">Solicitar Renovación de Cuenta</h3>
                        <span class="text-xs text-slate-400 font-mono" x-text="renewSale?.service?.name + ' (' + (renewSale?.sale_code || '') + ')'"></span>
                    </div>
                </div>
                <button @click="renewModal = false" class="text-slate-400 hover:text-white cursor-pointer text-lg font-bold">✕</button>
            </div>

            <form x-bind:action="'/boveda/renovar/' + (renewSale ? renewSale.id : '')" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Selector de Meses de Duración -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-2">Selecciona la Duración a Renovar *</label>
                    <div class="grid grid-cols-5 gap-2">
                        @foreach([1 => '1 Mes', 2 => '2 Meses', 3 => '3 Meses', 6 => '6 Meses', 12 => '12 Meses'] as $m => $lbl)
                        <button type="button" @click="renewMonths = {{ $m }}" :class="renewMonths === {{ $m }} ? 'bg-emerald-600 text-white border-emerald-500 ring-2 ring-emerald-500/40 shadow-lg shadow-emerald-600/30 font-black' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700 font-semibold'" class="p-2.5 rounded-xl border text-xs text-center transition-all cursor-pointer">
                            <span class="block text-sm">{{ $m }}M</span>
                            <span class="text-[10px] block opacity-80">{{ $m == 1 ? '30d' : ($m*30).'d' }}</span>
                        </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="months_duration" :value="renewMonths">
                </div>

                <!-- Monto Total Calculado -->
                <div class="p-3.5 rounded-2xl bg-gradient-to-r from-emerald-950/60 to-slate-900 border border-emerald-500/30 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-slate-400 uppercase font-bold block">Total a Abonar (<span x-text="renewMonths"></span> mes/es)</span>
                        <span class="text-xs text-emerald-400 font-mono" x-text="'$' + renewPricePerMonth.toFixed(2) + ' / mes'"></span>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black text-emerald-300 font-mono" x-text="'$' + (renewMonths * renewPricePerMonth).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                    </div>
                </div>

                <!-- Datos de Pago del Revendedor / Proveedor -->
                @if($reseller && ($reseller->bank_alias || $reseller->bank_cvu || $reseller->bank_cbu || $reseller->binance_id))
                <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-1.5 text-xs font-mono">
                    <span class="text-[10px] font-bold text-amber-400 uppercase font-sans flex items-center gap-1">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i>
                        <span>Datos de Pago de {{ $reseller->name }}</span>
                    </span>
                    @if($reseller->bank_alias)
                    <div class="flex justify-between text-slate-300">
                        <span class="text-slate-500 font-sans">Alias:</span>
                        <strong class="text-white">{{ $reseller->bank_alias }}</strong>
                    </div>
                    @endif
                    @if($reseller->bank_cvu || $reseller->bank_cbu)
                    <div class="flex justify-between text-slate-300">
                        <span class="text-slate-500 font-sans">CVU/CBU:</span>
                        <span class="text-slate-200">{{ $reseller->bank_cvu ?: $reseller->bank_cbu }}</span>
                    </div>
                    @endif
                    @if($reseller->bank_holder_name)
                    <div class="flex justify-between text-slate-300">
                        <span class="text-slate-500 font-sans">Titular:</span>
                        <span class="text-slate-200">{{ $reseller->bank_holder_name }}</span>
                    </div>
                    @endif
                    @if($reseller->binance_id)
                    <div class="flex justify-between text-slate-300">
                        <span class="text-slate-500 font-sans">Binance Pay ID:</span>
                        <span class="text-amber-400">{{ $reseller->binance_id }}</span>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Método de Pago -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Método de Pago Utilizado *</label>
                    <select name="payment_method" required class="w-full p-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white outline-none focus:border-emerald-500">
                        <option value="bank_transfer">Transferencia Bancaria / CVU Mercado Pago</option>
                        <option value="mercadopago">Mercado Pago (Dinero en Cuenta / Tarjeta)</option>
                        <option value="binance">Binance Pay (USDT Crypto)</option>
                        <option value="cash">Efectivo / Acordar con Vendedor</option>
                    </select>
                </div>

                <!-- Adjuntar Comprobante (Mini Nube) -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Adjuntar Comprobante de Pago (Opcional)</label>
                    <input type="file" name="receipt" accept="image/*,application/pdf" class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-emerald-400 hover:file:bg-slate-700 cursor-pointer">
                </div>

                <!-- Notas del Cliente -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Notas o Referencia de Pago</label>
                    <textarea name="customer_notes" rows="2" placeholder="Ej: Ya transferí desde Mercado Pago titular Juan Pérez..." class="w-full p-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white placeholder-slate-500 outline-none focus:border-emerald-500"></textarea>
                </div>

                @if($whatsappSupportEnabled && $supportWhatsAppPhone)
                <!-- Botón de Renovación Directa por WhatsApp -->
                <div class="p-3.5 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-xs text-emerald-300">
                        <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>¿Prefieres consultar o enviar tu comprobante directamente por WhatsApp?</span>
                    </div>
                    <a :href="'https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsAppPhone) }}?text=Hola,%20quiero%20renovar%20mi%20servicio%20de%20*' + encodeURIComponent(renewSale?.service?.name || '') + '*%20(Código:%20' + (renewSale?.sale_code || '') + ')%20por%20' + renewMonths + '%20mes(es).%20Cliente:%20{{ urlencode($customer->name) }}%20(Tel:%20{{ $customer->phone }}).'" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 shrink-0 transition-all shadow-md shadow-emerald-600/30 cursor-pointer">
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        <span>Renovar por WhatsApp</span>
                    </a>
                </div>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="renewModal = false" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-bold hover:bg-slate-700 cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:opacity-90 text-white text-xs font-black shadow-lg shadow-emerald-600/30 flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Enviar Solicitud al Panel</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 8: GUÍA OFICIAL ANTI-BLOQUEO & SMART TV -->
    <!-- ========================================== -->
    <div x-show="guideModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md" @click.self="guideModal = false">
        <div class="bg-[#0f172a] p-6 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl max-w-lg w-full space-y-5 relative overflow-hidden">
            <!-- Glow background -->
            <div class="absolute -top-16 -right-16 w-36 h-36 bg-sky-500/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5 text-sky-400">
                    <i data-lucide="tv" class="w-6 h-6"></i>
                    <div>
                        <h3 class="font-black text-white text-base">Guía de Conexión & Tips Anti-Bloqueo</h3>
                        <span class="text-xs text-slate-400">Instrucciones para Smart TV, TV Box y Dispositivos Móviles</span>
                    </div>
                </div>
                <button @click="guideModal = false" class="text-slate-400 hover:text-white text-lg font-bold cursor-pointer">✕</button>
            </div>

            <div class="space-y-3.5 max-h-80 overflow-y-auto pr-1 text-xs">
                <!-- Netflix -->
                <div class="p-3.5 rounded-2xl bg-[#0b0f19] border border-slate-800 space-y-2">
                    <div class="flex items-center gap-2 text-red-500 font-bold">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                        <h4 class="text-white font-bold">Netflix (Smart TV & Evitar Bloqueo de Hogar)</h4>
                    </div>
                    <ul class="space-y-1.5 text-slate-300 list-disc list-inside leading-relaxed text-[11px]">
                        <li>Inicia sesión con el correo y contraseña oficial provisto en tu Bóveda.</li>
                        <li>Si en el televisor aparece el aviso <em>"Este televisor no forma parte de tu Hogar Netflix"</em>, elige la opción <strong>"Estoy de viaje"</strong> o <strong>"Ver código temporal"</strong> y solicítaselo a tu distribuidor por el botón de WhatsApp.</li>
                        <li>Ingresa <strong>ÚNICAMENTE</strong> a tu perfil asignado con tu PIN numérico.</li>
                        <li><strong>PROHIBIDO</strong> cambiar el correo, contraseña o asociar números de teléfono.</li>
                    </ul>
                </div>

                <!-- Disney+ & Star -->
                <div class="p-3.5 rounded-2xl bg-[#0b0f19] border border-slate-800 space-y-2">
                    <div class="flex items-center gap-2 text-sky-400 font-bold">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        <h4 class="text-white font-bold">Disney+ Estándar y Premium (Star & ESPN)</h4>
                    </div>
                    <ul class="space-y-1.5 text-slate-300 list-disc list-inside leading-relaxed text-[11px]">
                        <li>Ingresa directamente con las credenciales de acceso de la Bóveda.</li>
                        <li>Selecciona tu perfil designado con tu PIN. No modifiques nombres ni avatares de otros usuarios.</li>
                        <li>Válido para reproducción en simultáneo en 1 pantalla activa a la vez.</li>
                    </ul>
                </div>

                <!-- HBO Max -->
                <div class="p-3.5 rounded-2xl bg-[#0b0f19] border border-slate-800 space-y-2">
                    <div class="flex items-center gap-2 text-purple-400 font-bold">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                        <h4 class="text-white font-bold">HBO (MAX Platino 4K UHD)</h4>
                    </div>
                    <ul class="space-y-1.5 text-slate-300 list-disc list-inside leading-relaxed text-[11px]">
                        <li>Disfruta películas y series en ultra alta definición 4K sin interrupciones.</li>
                        <li>Accede a tu perfil privado protegido por PIN para mantener tus listas y reproducciones personalizadas.</li>
                    </ul>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-slate-800">
                <button type="button" @click="guideModal = false" class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                    Entendido, Cerrar Guía
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL: AJUSTAR PIN PERSONAL DE BÓVEDA (4 DÍGITOS) -->
    <!-- ========================================== -->
    <div x-show="changePinModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md" @click.self="changePinModal = false">
        <div class="bg-[#0f172a] p-6 sm:p-8 rounded-3xl border border-amber-500/40 shadow-2xl max-w-md w-full space-y-5 relative overflow-hidden">
            <div class="absolute -top-16 -right-16 w-36 h-36 bg-amber-500/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5 text-amber-400">
                    <i data-lucide="key" class="w-6 h-6"></i>
                    <h3 class="font-black text-white text-base">Ajustar PIN de Bóveda (4 Dígitos)</h3>
                </div>
                <button @click="changePinModal = false" class="text-slate-400 hover:text-white">✕</button>
            </div>

            <form method="POST" action="{{ route('boveda.update_pin') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">PIN Personal de 4 Dígitos *</label>
                    <input type="password" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" name="security_pin" value="{{ $customer->security_pin }}" required placeholder="••••" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-lg font-mono text-center tracking-[0.5em] text-white placeholder-slate-500 outline-none focus:border-amber-400 font-black">
                </div>

                <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs leading-relaxed space-y-1.5">
                    <strong class="block text-white">🔒 ¿Para qué sirve este PIN que debes recordar?</strong>
                    <p>
                        Este PIN de 4 dígitos es tu <strong>clave privada de desbloqueo para la Bóveda Cifrada</strong>. Te servirá para consultar y revelar tus contraseñas, usuario y perfiles asignados en cualquier celular o PC, o cuando tu dispositivo no use biometría. <strong>Guárdalo y recuérdalo siempre</strong> para tus futuras consultas y renovaciones.
                    </p>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="changePinModal = false" class="px-4 py-2 text-xs text-slate-400 hover:text-white">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 hover:opacity-90 text-black font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/20">
                        Guardar Mi PIN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
