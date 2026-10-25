@extends('layouts.public')

@section('title', 'Ingreso a Bóveda Privada de Streaming')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" x-data="loginPortalApp()">
    <div class="max-w-md w-full space-y-8 bg-[#0f172a]/80 backdrop-blur-xl p-8 sm:p-10 rounded-3xl border border-slate-800 shadow-2xl relative overflow-hidden">
        <!-- Glow top -->
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-red-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="text-center relative z-10">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-red-600 to-amber-500 flex items-center justify-center text-white shadow-lg shadow-red-600/30 mb-4">
                <i data-lucide="shield-check" class="w-8 h-8"></i>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Bóveda de Credenciales</h2>
            <p class="text-xs text-slate-400 mt-2">Ingresa con tu correo/WhatsApp y contraseña o PIN de 4 dígitos para ver tus accesos y perfiles.</p>
        </div>

        @if(session('success'))
        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(isset($errors) && $errors->any())
        <div class="p-3.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('boveda.login.process') }}" class="space-y-4 relative z-10">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Correo Electrónico o WhatsApp de Cliente</label>
                <div class="relative">
                    <input type="text" name="login" x-model="loginInput" value="{{ old('login', old('email')) }}" required placeholder="tu-correo@cliente.com o +54 9 11..." class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-900/90 border border-slate-700/80 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 transition-colors">
                    <i data-lucide="user-check" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Contraseña o PIN (4 Dígitos)</label>
                    <button type="button" @click="openRecoverModal()" class="text-[11px] text-cyan-400 hover:text-cyan-300 hover:underline cursor-pointer">
                        ¿Olvidaste tu PIN?
                    </button>
                </div>
                <div class="relative" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" name="password" required placeholder="Tu contraseña o PIN de 4 dígitos" class="w-full pl-10 pr-10 py-3 rounded-xl bg-slate-900/90 border border-slate-700/80 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-red-500 transition-colors">
                    <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <button type="button" @click="show = !show" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white text-xs cursor-pointer">
                        <span x-text="show ? 'Ocultar' : 'Ver'"></span>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 text-slate-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-red-600 focus:ring-0">
                    <span>Recordar sesión</span>
                </label>
                <a href="{{ route('portal.register') }}" class="text-sky-400 hover:text-sky-300 font-semibold">¿No tienes cuenta? Regístrate</a>
            </div>

            <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-600 text-white font-black text-sm tracking-wide shadow-lg shadow-red-600/30 transition-all flex items-center justify-center gap-2 mt-4 cursor-pointer">
                <i data-lucide="key" class="w-4 h-4"></i>
                <span>Ingresar a mi Bóveda</span>
            </button>
        </form>

        <div class="pt-4 border-t border-slate-800 text-center relative z-10 space-y-2">
            <button type="button" @click="openRecoverModal()" class="text-xs text-amber-400 hover:text-amber-300 hover:underline flex items-center justify-center gap-1.5 mx-auto cursor-pointer">
                <i data-lucide="unlock" class="w-3.5 h-3.5"></i>
                <span>¿Olvidaste tu PIN? Recupéralo con tu contraseña</span>
            </button>

            <a href="{{ route('login') }}" class="text-xs text-slate-500 hover:text-slate-300 transition-colors flex items-center justify-center gap-1.5 pt-1">
                <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                <span>Acceso a Panel de Administración / Revendedores</span>
            </a>
        </div>
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
                    Ingresa tu correo o WhatsApp y tu contraseña de cliente. Te mostraremos tu PIN de 4 dígitos y podrás entrar directamente a tu Bóveda.
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
                    <p class="text-[10px] text-slate-400">Tu sesión ha sido iniciada. Ya puedes acceder a todas tus credenciales.</p>
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
                    <a href="{{ route('boveda.index') }}" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider transition-all cursor-pointer flex items-center justify-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>Ir a Mi Bóveda de Streaming</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loginPortalApp() {
    return {
        loginInput: '{{ old('login', old('email')) }}',

        // Recuperar PIN
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
                if (window.lucide) window.lucide.createIcons();
            });
        },

        openRecoverModal() {
            this.recoverInput = this.loginInput || '';
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
