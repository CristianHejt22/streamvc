@extends('layouts.public')

@section('title', 'Finalizar Compra - ' . $plan->name)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10" x-data="checkoutApp()">

    <div class="mb-6">
        <a href="{{ route('catalog.index', ['ref' => $seller->referral_code]) }}" class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Volver al Catálogo</span>
        </a>
    </div>

    <div class="bg-[#111827] border border-gray-800 rounded-3xl shadow-2xl overflow-hidden">
        <!-- Order Header Summary -->
        <div class="p-6 border-b border-gray-800 bg-[#0d1322] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-white text-lg shadow-lg" style="background-color: {{ $plan->service->color_hex }}">
                    {{ substr($plan->service->name, 0, 2) }}
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">{{ $plan->service->name }}</h2>
                    <p class="text-xs text-gray-400">{{ $plan->name }} · {{ $plan->duration_days }} días</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs text-gray-400 block">Total a Pagar</span>
                <span class="text-2xl font-black text-emerald-400">${{ number_format($plan->price, 2) }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('catalog.checkout.process', $plan) }}" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
            @csrf
            <input type="hidden" name="seller_id" value="{{ $seller->id }}">

            <!-- Datos del Cliente -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-red-500"></i>
                    <span>Tus Datos de Contacto (Para Envío de Credenciales)</span>
                </h3>

                <!-- SI EL CLIENTE YA INICIÓ SESIÓN -->
                <div x-show="isCustomerLoggedIn" class="p-4 rounded-2xl bg-gradient-to-br from-emerald-950/40 to-slate-900 border border-emerald-500/40 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[11px] font-bold text-emerald-300 uppercase tracking-wider font-mono">Sesión Activa</span>
                        </div>
                        <button type="button" @click="logoutCustomerAjax()" class="text-[11px] text-slate-400 hover:text-rose-400 font-mono transition-colors cursor-pointer">
                            Cambiar cuenta
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold">
                            <i data-lucide="user-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white" x-text="customer.name"></h4>
                            <p class="text-[11px] text-slate-400 font-mono" x-text="customer.phone"></p>
                        </div>
                    </div>
                    <div class="pt-1 border-t border-slate-800 flex items-center justify-between text-[10px]">
                        <span class="text-emerald-400 flex items-center gap-1">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                            <span>Bóveda vinculada con PIN activo</span>
                        </span>
                        <button type="button" @click="openRecoverModal()" class="text-cyan-400 hover:underline cursor-pointer">
                            Ver / Recuperar PIN
                        </button>
                    </div>

                    <!-- Inputs ocultos para enviar la orden con sus datos ya registrados -->
                    <input type="hidden" name="customer_name" :value="customer.name">
                    <input type="hidden" name="customer_phone" :value="customer.phone">
                    <input type="hidden" name="customer_email" :value="customer.email">
                    <input type="hidden" name="boveda_pin" :value="customer.security_pin || '0000'">
                </div>

                <!-- SI EL CLIENTE NO HA INICIADO SESIÓN (TABS: INICIAR SESIÓN vs REGISTRARME) -->
                <div x-show="!isCustomerLoggedIn" class="space-y-3">
                    <!-- Selector de Tabs -->
                    <div class="grid grid-cols-2 p-1 rounded-2xl bg-slate-950 border border-slate-800 text-xs font-bold">
                        <button type="button" @click="customerTab = 'login'"
                                class="py-2.5 rounded-xl transition-all cursor-pointer"
                                :class="customerTab === 'login' ? 'bg-amber-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white'">
                            Ya tengo cuenta
                        </button>
                        <button type="button" @click="customerTab = 'register'"
                                class="py-2.5 rounded-xl transition-all cursor-pointer"
                                :class="customerTab === 'register' ? 'bg-amber-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white'">
                            Soy nuevo (Registrarme)
                        </button>
                    </div>

                    <!-- TAB 1: INICIAR SESIÓN RÁPIDA -->
                    <div x-show="customerTab === 'login'" class="p-4 rounded-2xl bg-[#0b0f19] border border-slate-800 space-y-3">
                        <div class="space-y-0.5">
                            <span class="text-xs font-bold text-white block">Inicia Sesión en 1 Clic</span>
                            <p class="text-[10px] text-slate-400">Si ya compraste antes, no necesitas volver a ingresar tus datos.</p>
                        </div>

                        <div x-show="loginError" x-cloak class="p-2.5 rounded-xl bg-red-500/10 border border-red-500/30 text-[11px] text-red-300 flex items-center gap-1.5">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0 text-red-400"></i>
                            <span x-text="loginError"></span>
                        </div>

                        <div class="space-y-3">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-300 uppercase block">WhatsApp o Correo</label>
                                <input type="text" x-model="loginInput" placeholder="Ej: +54 9 11 1234-5678 o tu@correo.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none transition-colors">
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <label class="text-[10px] font-bold text-slate-300 uppercase block">Contraseña o PIN (4 dígitos)</label>
                                    <button type="button" @click="openRecoverModal()" class="text-[10px] text-cyan-400 hover:underline cursor-pointer">
                                        ¿Olvidaste tu PIN?
                                    </button>
                                </div>
                                <input type="password" x-model="loginPassword" @keydown.enter.prevent="loginCustomerAjax()" placeholder="Tu contraseña o PIN de 4 números" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none transition-colors">
                            </div>

                            <button type="button" @click="loginCustomerAjax()" :disabled="loginLoading"
                                    class="w-full py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20 transition-all cursor-pointer disabled:opacity-50">
                                <span x-show="!loginLoading">Iniciar Sesión y Autocompletar</span>
                                <span x-show="loginLoading" class="flex items-center gap-1.5">
                                    <span class="w-3.5 h-3.5 border-2 border-slate-950/30 border-t-slate-950 rounded-full animate-spin"></span>
                                    <span>Verificando...</span>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- TAB 2: REGISTRO POR PRIMERA VEZ (CON CONTRASEÑA Y PIN) -->
                    <div x-show="customerTab === 'register'" class="space-y-4">
                        <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-200 space-y-1">
                            <span class="font-bold flex items-center gap-1.5 text-amber-300">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                                <span>Te registras una sola vez</span>
                            </span>
                            <p class="text-[11px] text-slate-300 leading-relaxed">
                                Define tu <strong>contraseña</strong> y tu <strong>PIN de 4 dígitos</strong>. Con tu contraseña podrás iniciar sesión en cualquier momento y recuperar tu PIN si lo olvidas.
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Nombre y Apellido *</label>
                            <input type="text" name="customer_name" :required="!isCustomerLoggedIn && customerTab === 'register'" placeholder="Ej: Juan Manuel Pérez" class="w-full px-4 py-2.5 rounded-xl bg-[#0b0f19] border border-gray-800 text-xs text-white focus:outline-none focus:border-red-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Número de WhatsApp * (Con código de país)</label>
                                <input type="text" name="customer_phone" :required="!isCustomerLoggedIn && customerTab === 'register'" placeholder="Ej: +5491144556677" class="w-full px-4 py-2.5 rounded-xl bg-[#0b0f19] border border-gray-800 text-xs text-white focus:outline-none focus:border-red-500 font-mono">
                                <span class="text-[10px] text-gray-400 mt-1 block">Identificador principal para el ingreso a tu Bóveda.</span>
                            </div>

                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Correo Electrónico (Opcional)</label>
                                <input type="email" name="customer_email" placeholder="tu.correo@gmail.com" class="w-full px-4 py-2.5 rounded-xl bg-[#0b0f19] border border-gray-800 text-xs text-white focus:outline-none focus:border-red-500">
                            </div>
                        </div>

                        <!-- Contraseña Maestra de Cuenta -->
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Contraseña para tu Cuenta *</label>
                            <input type="password" name="password" :required="!isCustomerLoggedIn && customerTab === 'register'" minlength="6" placeholder="Mínimo 6 caracteres" class="w-full px-4 py-2.5 rounded-xl bg-[#0b0f19] border border-gray-800 text-xs text-white focus:outline-none focus:border-red-500">
                            <span class="text-[10px] text-gray-400 mt-1 block">Con esta contraseña podrás iniciar sesión y recuperar tu PIN de Bóveda si lo olvidas.</span>
                        </div>

                        <!-- PIN de Seguridad de la Bóveda -->
                        <div class="p-4 rounded-2xl bg-[#0b0f19] border border-cyan-500/30 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-cyan-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <i data-lucide="key" class="w-4 h-4"></i>
                                    <span>PIN Personal de Bóveda (4 Dígitos) *</span>
                                </label>
                                <span class="text-[10px] font-mono text-cyan-300 font-bold px-2 py-0.5 rounded-full bg-cyan-500/10 border border-cyan-500/20">
                                    Llave Privada
                                </span>
                            </div>

                            <input type="password" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" name="boveda_pin" :required="!isCustomerLoggedIn && customerTab === 'register'" placeholder="Ej: 1234" class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-cyan-500/40 text-sm font-mono text-center tracking-[0.5em] text-cyan-300 focus:outline-none focus:border-cyan-400 font-black">

                            <p class="text-[11px] text-slate-300 leading-relaxed">
                                🔒 <strong>¿Para qué sirve este PIN de 4 números?</strong>
                                Es tu <strong>clave personal para desbloquear tus credenciales de streaming en la Bóveda</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selección de Método de Pago -->
            <div class="space-y-4 pt-4 border-t border-gray-800">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-4 h-4 text-red-500"></i>
                    <span>Selecciona tu Método de Pago</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="p-4 rounded-2xl border cursor-pointer transition-all flex items-center gap-3" :class="paymentMethod === 'bank_transfer' ? 'bg-red-950/20 border-red-500 text-white' : 'bg-[#0b0f19] border-gray-800 text-gray-400 hover:border-gray-700'">
                        <input type="radio" name="payment_method" value="bank_transfer" x-model="paymentMethod" class="text-red-600 focus:ring-red-500">
                        <div>
                            <span class="text-xs font-bold block text-white">Transferencia / Billetera</span>
                            <span class="text-[11px] text-gray-400">Alias, CBU, CVU o Binance</span>
                        </div>
                    </label>

                    <label class="p-4 rounded-2xl border cursor-pointer transition-all flex items-center gap-3" :class="paymentMethod === 'mercadopago' ? 'bg-cyan-950/20 border-cyan-500 text-white' : 'bg-[#0b0f19] border-gray-800 text-gray-400 hover:border-gray-700'">
                        <input type="radio" name="payment_method" value="mercadopago" x-model="paymentMethod" class="text-cyan-600 focus:ring-cyan-500">
                        <div>
                            <span class="text-xs font-bold block text-white">Mercado Pago</span>
                            <span class="text-[11px] text-gray-400">Tarjetas y acreditación inmediata</span>
                        </div>
                    </label>
                </div>

                <!-- Datos Bancarios & Subida de Comprobante -->
                <div x-show="paymentMethod === 'bank_transfer'" class="p-5 rounded-2xl bg-[#0b0f19] border border-gray-800 space-y-4">
                    <div class="text-xs font-bold text-white mb-2 flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-amber-400"></i>
                        <span>Datos para realizar tu transferencia:</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs bg-black/40 p-4 rounded-xl border border-gray-800">
                        @if($seller->bank_alias)
                        <div>
                            <span class="text-[10px] text-gray-400 block uppercase">Alias CBU / CVU:</span>
                            <span class="font-mono font-bold text-emerald-400 text-sm select-all">{{ $seller->bank_alias }}</span>
                        </div>
                        @endif

                        @if($seller->bank_holder_name)
                        <div>
                            <span class="text-[10px] text-gray-400 block uppercase">Titular:</span>
                            <span class="font-bold text-white">{{ $seller->bank_holder_name }}</span>
                        </div>
                        @endif

                        @if($seller->bank_cvu)
                        <div>
                            <span class="text-[10px] text-gray-400 block uppercase">CVU:</span>
                            <span class="font-mono text-gray-300 text-xs select-all">{{ $seller->bank_cvu }}</span>
                        </div>
                        @endif

                        @if($seller->binance_id)
                        <div>
                            <span class="text-[10px] text-gray-400 block uppercase">Binance Pay ID:</span>
                            <span class="font-mono text-amber-400 font-bold select-all">{{ $seller->binance_id }}</span>
                        </div>
                        @endif
                    </div>

                    @if($seller->bank_instructions)
                    <p class="text-[11px] text-gray-400 italic">{{ $seller->bank_instructions }}</p>
                    @endif

                    <!-- Input Subir Comprobante (Mini Nube) -->
                    <div>
                        <label class="block text-xs font-bold text-white mb-1">Adjuntar Comprobante de Transferencia (Opcional)</label>
                        <div class="relative border-2 border-dashed border-gray-800 hover:border-red-500/50 rounded-2xl p-4 text-center cursor-pointer transition-all bg-black/20">
                            <input type="file" name="receipt" accept="image/*,.pdf" @change="receiptFileName = $event.target.files[0]?.name" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <i data-lucide="upload-cloud" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                            <span class="text-xs text-gray-300 block font-medium" x-text="receiptFileName || 'Toca aquí para seleccionar captura o PDF'"></span>
                            <span class="text-[10px] text-gray-400">JPG, PNG o PDF (Máximo 10MB)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-gray-800">
                <button type="submit" class="w-full py-3.5 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-black text-sm shadow-xl shadow-red-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <span x-text="paymentMethod === 'bank_transfer' ? 'Enviar Comprobante y Solicitar Activación' : 'Continuar a Mercado Pago'"></span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL PARA RECUPERAR PIN DE BÓVEDA CON CONTRASEÑA -->
    <!-- ======================================================== -->
    <div x-cloak x-show="recoverModalOpen" class="relative z-50" aria-labelledby="modal-recover-pin" role="dialog" aria-modal="true">
        <div x-show="recoverModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="recoverModalOpen = false"
             class="fixed inset-0 bg-slate-950/85 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 flex items-center justify-center">
            <div x-show="recoverModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-[#0f172a] border border-cyan-500/40 text-left shadow-2xl transition-all w-full max-w-md p-6 sm:p-7 space-y-5">

                <!-- Header Modal -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 border border-cyan-500/40 flex items-center justify-center text-cyan-400">
                            <i data-lucide="key" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold font-mono text-cyan-400 uppercase tracking-wider block">Seguridad de Bóveda</span>
                            <h3 class="text-base sm:text-lg font-black text-white">Recuperar PIN con Contraseña</h3>
                        </div>
                    </div>
                    <button type="button" @click="recoverModalOpen = false" class="p-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed">
                    Si olvidaste tu PIN de 4 dígitos para desbloquear tus credenciales de streaming, ingresa tu WhatsApp o correo y tu contraseña para recuperarlo.
                </p>

                <!-- Feedback Message -->
                <div x-show="recoverError" x-cloak class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-xs text-red-300 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-red-400"></i>
                    <span x-text="recoverError"></span>
                </div>

                <div x-show="recoveredPin" x-cloak class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-center space-y-2">
                    <div class="flex items-center justify-center gap-2 text-emerald-400 text-xs font-bold font-mono">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>¡Identidad Verificada con Éxito!</span>
                    </div>
                    <p class="text-[11px] text-slate-300">Tu PIN de 4 dígitos de la Bóveda es:</p>
                    <div class="inline-block px-5 py-2 rounded-xl bg-slate-950 border border-emerald-500/50 text-2xl font-black font-mono tracking-widest text-emerald-300 shadow-inner select-all" x-text="recoveredPin"></div>
                    <p class="text-[10px] text-slate-400">Tu sesión ha sido iniciada automáticamente. Ya puedes continuar con tu compra.</p>
                </div>

                <form x-show="!recoveredPin" @submit.prevent="submitRecoverPin()" class="space-y-4">
                    <!-- WhatsApp o Correo -->
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-300 uppercase block">WhatsApp o Correo *</label>
                        <input type="text" x-model="recoverInput" required placeholder="Ej: +54 9 11 1234-5678 o tu@correo.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-cyan-400 focus:outline-none transition-colors">
                    </div>

                    <!-- Contraseña Maestra -->
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-300 uppercase block">Contraseña de tu Cuenta *</label>
                        <input type="password" x-model="recoverPassword" required placeholder="Tu contraseña registrada" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:border-cyan-400 focus:outline-none transition-colors">
                    </div>

                    <!-- Opcional: Nuevo PIN -->
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-400 uppercase block">Nuevo PIN de 4 dígitos (Opcional)</label>
                        <input type="password" x-model="recoverNewPin" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" placeholder="Dejar vacío para ver el actual" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-center text-sm font-mono tracking-widest text-cyan-300 placeholder-slate-600 focus:border-cyan-400 focus:outline-none transition-colors">
                        <span class="text-[10px] text-slate-500 block">Si deseas cambiar tu PIN por uno nuevo, escribe los 4 números aquí.</span>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2.5">
                        <button type="button" @click="recoverModalOpen = false" class="px-4 py-2.5 rounded-xl text-slate-400 hover:text-white text-xs font-bold transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="recoverLoading" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:opacity-90 disabled:opacity-50 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-600/30 transition-all cursor-pointer">
                            <span x-show="!recoverLoading">Recuperar PIN</span>
                            <span x-show="recoverLoading" class="flex items-center gap-1.5">
                                <span class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <span>Verificando...</span>
                            </span>
                        </button>
                    </div>
                </form>

                <div x-show="recoveredPin" class="pt-2">
                    <button type="button" @click="recoverModalOpen = false" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider transition-all cursor-pointer">
                        Listo, Continuar con mi Compra
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function checkoutApp() {
    return {
        paymentMethod: 'bank_transfer',
        receiptFileName: '',

        // Estado de Sesión del Cliente
        isCustomerLoggedIn: {{ Auth::guard('customer')->check() ? 'true' : 'false' }},
        customer: {
            name: '{{ Auth::guard('customer')->check() ? addslashes(Auth::guard('customer')->user()->name) : '' }}',
            phone: '{{ Auth::guard('customer')->check() ? addslashes(Auth::guard('customer')->user()->phone) : '' }}',
            email: '{{ Auth::guard('customer')->check() ? addslashes(Auth::guard('customer')->user()->email ?? '') : '' }}',
            security_pin: '{{ Auth::guard('customer')->check() ? addslashes(Auth::guard('customer')->user()->security_pin ?? '') : '' }}'
        },
        customerTab: '{{ Auth::guard('customer')->check() ? 'active' : 'login' }}',
        loginInput: '',
        loginPassword: '',
        loginLoading: false,
        loginError: '',

        // Estado del Modal de Recuperación de PIN
        recoverModalOpen: false,
        recoverInput: '',
        recoverPassword: '',
        recoverNewPin: '',
        recoverLoading: false,
        recoverError: '',
        recoverSuccess: '',
        recoveredPin: '',

        init() {
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },

        async loginCustomerAjax() {
            if (!this.loginInput || !this.loginPassword) {
                this.loginError = 'Ingresa tu WhatsApp/correo y tu contraseña o PIN.';
                return;
            }

            this.loginLoading = true;
            this.loginError = '';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('{{ route('boveda.login.ajax') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        login: this.loginInput,
                        password: this.loginPassword
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.customer = data.customer;
                    this.isCustomerLoggedIn = true;
                    this.loginPassword = '';
                    this.loginError = '';
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                } else {
                    this.loginError = data.message || 'Credenciales incorrectas.';
                }
            } catch (err) {
                this.loginError = 'Error de conexión al verificar tus datos.';
            } finally {
                this.loginLoading = false;
            }
        },

        async logoutCustomerAjax() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                await fetch('{{ route('boveda.logout.ajax') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
            } catch (e) {}

            this.isCustomerLoggedIn = false;
            this.customer = { name: '', phone: '', email: '', security_pin: '' };
            this.customerTab = 'login';
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openRecoverModal() {
            this.recoverInput = this.loginInput || this.customer.phone || this.customer.email || '';
            this.recoverPassword = '';
            this.recoverNewPin = '';
            this.recoverError = '';
            this.recoverSuccess = '';
            this.recoveredPin = '';
            this.recoverModalOpen = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
            });
        },

        async submitRecoverPin() {
            if (!this.recoverPassword) {
                this.recoverError = 'Ingresa tu contraseña para verificar tu identidad.';
                return;
            }

            this.recoverLoading = true;
            this.recoverError = '';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('{{ route('boveda.recover_pin') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        login: this.recoverInput,
                        password: this.recoverPassword,
                        new_pin: this.recoverNewPin
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.recoveredPin = data.security_pin;
                    this.recoverSuccess = data.message;
                    if (data.customer) {
                        this.customer = data.customer;
                        this.isCustomerLoggedIn = true;
                    }
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                } else {
                    this.recoverError = data.message || 'Contraseña o identificador incorrecto.';
                }
            } catch (e) {
                this.recoverError = 'Error al comunicarse con el servidor.';
            } finally {
                this.recoverLoading = false;
            }
        }
    };
}
</script>
@endsection
