@extends('layouts.public')

@section('title', 'Orden ' . $order->order_code)

@section('content')
<div class="max-w-xl mx-auto px-4 sm:px-6 py-12" x-data="{
    copied: false,
    copy(text) {
        if (!text) return;
        navigator.clipboard.writeText(text);
        this.copied = true;
        setTimeout(() => this.copied = false, 2000);
    }
}">

    <div class="bg-[#111827] border border-gray-800 rounded-3xl p-6 sm:p-8 shadow-2xl text-center space-y-6">

        @if($order->payment_status === 'approved')
        <!-- ========================================== -->
        <!-- PAGO APROBADO: ENTREGA / ACCESO -->
        <!-- ========================================== -->
        <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center mx-auto text-2xl font-black">
            <i data-lucide="check" class="w-8 h-8"></i>
        </div>

        <div>
            <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest block mb-1">¡Pedido Aprobado con Éxito!</span>
            <h2 class="text-2xl font-black text-white">
                @if($order->isPanelOrder())
                    Tu Franquicia de Panel Está Activada
                @else
                    Tus Cuentas Están Listas en la Bóveda
                @endif
            </h2>
            <p class="text-xs text-gray-400 mt-1">Orden: <strong class="font-mono text-cyan-400">{{ $order->order_code }}</strong></p>
        </div>

        @if($order->isPanelOrder())
            <!-- Ficha Panel Activado -->
            <div class="p-5 rounded-2xl bg-[#0b0f19] border border-purple-500/30 text-left space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-gray-800">
                    <span class="text-xs font-bold text-white uppercase font-mono">
                        {{ $order->order_type === 'panel_pro' ? '💎 Panel PRO Ilimitado' : '📦 Panel Básico' }}
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">Activo por 30 Días</span>
                </div>
                <p class="text-xs text-slate-300">
                    Tu acceso como revendedor independiente ha sido habilitado con éxito. Ya puedes iniciar sesión con tu correo/teléfono y tu PIN/contraseña.
                </p>
                <a href="{{ route('login') }}" class="w-full py-3 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Ingresar a Mi Panel de Control</span>
                </a>
            </div>
        @elseif($order->sale)
            <!-- Ficha de Credenciales para venta individual -->
            <div class="p-5 rounded-2xl bg-[#0b0f19] border border-gray-800 text-left space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-gray-800">
                    <span class="text-xs font-bold text-white">{{ $order->sale->service ? $order->sale->service->name : 'Streaming' }}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-red-600/20 text-red-400 font-bold">
                        {{ $order->sale->profile_name_snapshot ?: 'Cuenta Completa' }}
                    </span>
                </div>

                <!-- Usuario -->
                <div>
                    <span class="text-[10px] text-gray-400 uppercase font-semibold block">Usuario / Correo:</span>
                    <div class="flex items-center justify-between gap-2 mt-0.5">
                        <code class="font-mono text-xs text-white font-bold select-all">{{ $order->sale->account_email_snapshot }}</code>
                        <button type="button" @click="copy('{{ $order->sale->account_email_snapshot }}')" class="p-1 text-gray-400 hover:text-white cursor-pointer">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Contraseña -->
                <div>
                    <span class="text-[10px] text-gray-400 uppercase font-semibold block">Contraseña:</span>
                    <div class="flex items-center justify-between gap-2 mt-0.5">
                        <code class="font-mono text-xs text-amber-400 font-bold select-all">{{ $order->sale->account_password_snapshot }}</code>
                        <button type="button" @click="copy('{{ $order->sale->account_password_snapshot }}')" class="p-1 text-gray-400 hover:text-white cursor-pointer">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <!-- Perfil & PIN -->
                @if($order->sale->profile_pin_snapshot)
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-800/80">
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-semibold block">Perfil:</span>
                        <span class="text-xs font-bold text-white">{{ $order->sale->profile_name_snapshot }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-gray-400 uppercase font-semibold block">PIN del Perfil:</span>
                        <code class="text-xs font-mono font-bold text-emerald-400">{{ $order->sale->profile_pin_snapshot }}</code>
                    </div>
                </div>
                @endif

                <!-- Vencimiento -->
                <div class="pt-2 border-t border-gray-800/80 text-[11px] text-gray-400 flex items-center justify-between">
                    <span>Vence el:</span>
                    <strong class="text-white">{{ $order->sale->end_date ? $order->sale->end_date->format('d/m/Y') : '30 días' }}</strong>
                </div>

                <!-- Reglas -->
                @if($order->sale->usage_rules_snapshot)
                <div class="pt-2 border-t border-gray-800/80 text-[10px] text-amber-200/90">
                    <span class="font-bold block mb-0.5">⚠️ Reglas de uso:</span>
                    <pre class="font-sans whitespace-pre-line text-[10px] text-gray-300">{{ $order->sale->usage_rules_snapshot }}</pre>
                </div>
                @endif
            </div>

            <a href="{{ route('boveda.login') }}" class="w-full py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:opacity-95 text-white font-bold text-xs flex items-center justify-center gap-2 transition-all">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>Abrir Mi Bóveda de Clientes</span>
            </a>
        @else
            <!-- Pedido de Combo aprobado -->
            <div class="p-5 rounded-2xl bg-[#0b0f19] border border-gray-800 text-left space-y-3">
                <p class="text-xs text-slate-300">
                    Todas las cuentas de tu pedido se encuentran disponibles en tu <strong>Bóveda Privada</strong>.
                </p>
                <a href="{{ route('boveda.login') }}" class="w-full py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:opacity-95 text-white font-bold text-xs flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>Ingresar a Mi Bóveda para Ver Mis Cuentas</span>
                </a>
            </div>
        @endif

        @else
        <!-- ========================================== -->
        <!-- PAGO PENDIENTE: AUDITORÍA EN EL PANEL -->
        <!-- ========================================== -->
        <div class="w-16 h-16 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center mx-auto text-2xl font-black animate-pulse">
            <i data-lucide="clock" class="w-8 h-8"></i>
        </div>

        <div>
            <span class="text-xs font-bold text-amber-400 uppercase tracking-widest block mb-1">¡Pedido Registrado con Éxito!</span>
            <h2 class="text-2xl font-black text-white">Comprobante en Auditoría</h2>
            <p class="text-xs text-gray-400 mt-1">
                Orden: <strong class="font-mono text-cyan-400">{{ $order->order_code }}</strong>
            </p>
        </div>

        <!-- Detalle del Pedido -->
        <div class="p-5 rounded-2xl bg-[#0b0f19] border border-gray-800 text-left space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-gray-800">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Detalle del Pedido</span>
                <span class="text-xs font-black text-white font-mono">${{ number_format($order->amount, 0, ',', '.') }}</span>
            </div>

            @if($order->isPanelOrder())
                <div class="flex items-center justify-between text-xs text-white">
                    <span class="font-bold">{{ $order->order_type === 'panel_pro' ? '💎 Panel Franquicia PRO Ilimitado' : '📦 Panel Franquicia Básico (10 Servicios)' }}</span>
                    <span class="font-mono text-purple-400 font-bold">${{ number_format($order->amount, 0, ',', '.') }}</span>
                </div>
            @elseif($order->isCartOrder() && is_array($order->items_payload) && count($order->items_payload) > 0)
                <div class="space-y-2 text-xs">
                    @foreach($order->items_payload as $item)
                        <div class="flex items-center justify-between text-slate-300">
                            <span>• {{ $item['qty'] }}x <strong>{{ $item['serviceName'] ?? 'Streaming' }}</strong> ({{ $item['planName'] ?? '' }})</span>
                            <span class="font-mono text-white">${{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1), 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                    @if((float)$order->discount_amount > 0)
                        <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-emerald-400 font-bold">
                            <span>🔥 Descuento Combo:</span>
                            <span class="font-mono">-${{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            @else
                <div class="flex items-center justify-between text-xs text-white">
                    <span>{{ $order->servicePlan ? $order->servicePlan->name : 'Cuenta Streaming' }}</span>
                    <span class="font-mono text-emerald-400 font-bold">${{ number_format($order->amount, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- PIN de Bóveda del Cliente -->
        @if($order->boveda_pin)
        <div class="p-4 rounded-2xl bg-cyan-950/30 border border-cyan-500/30 text-left space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="key" class="w-4 h-4 text-cyan-400"></i>
                    <span class="text-xs font-bold text-cyan-300 uppercase">Tu PIN de Bóveda Streaming</span>
                </div>
                <code class="px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-300 font-mono font-black text-sm">{{ $order->boveda_pin }}</code>
            </div>
            <p class="text-[11px] text-slate-400">
                Guarda este PIN de 4 dígitos. Con él y tu teléfono ({{ $order->customer_phone }}) podrás entrar a tu <a href="{{ route('boveda.login') }}" class="text-cyan-400 underline font-bold">Bóveda Privada</a> una vez aprobado tu pago.
            </p>
        </div>
        @endif

        <!-- Datos para Transferencia Bancaria (si aplica) -->
        @if($order->payment_method === 'bank_transfer' && $order->seller)
        <div class="p-5 rounded-2xl bg-[#0b0f19] border border-amber-500/30 text-left space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-gray-800">
                <span class="text-xs font-bold text-amber-300 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                    <span>Datos de Transferencia Bancaria</span>
                </span>
                <span class="text-[10px] font-mono text-slate-400">Total: ${{ number_format($order->amount, 0, ',', '.') }}</span>
            </div>

            @if($order->seller->bank_name)
            <div class="text-xs text-slate-300">
                <span class="text-[10px] text-gray-400 block uppercase font-semibold">Banco / Billetera:</span>
                <span class="font-bold text-white">{{ $order->seller->bank_name }}</span>
            </div>
            @endif

            @if($order->seller->bank_account_holder)
            <div class="text-xs text-slate-300">
                <span class="text-[10px] text-gray-400 block uppercase font-semibold">Titular:</span>
                <span class="font-bold text-white">{{ $order->seller->bank_account_holder }}</span>
            </div>
            @endif

            @if($order->seller->bank_alias)
            <div>
                <span class="text-[10px] text-gray-400 block uppercase font-semibold">Alias:</span>
                <div class="flex items-center justify-between gap-2 mt-0.5">
                    <code class="font-mono text-xs text-amber-300 font-bold select-all">{{ $order->seller->bank_alias }}</code>
                    <button type="button" @click="copy('{{ $order->seller->bank_alias }}')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-xs text-slate-300 font-bold cursor-pointer">
                        Copiar Alias
                    </button>
                </div>
            </div>
            @endif

            @if($order->seller->bank_cbu)
            <div>
                <span class="text-[10px] text-gray-400 block uppercase font-semibold">CBU / CVU:</span>
                <div class="flex items-center justify-between gap-2 mt-0.5">
                    <code class="font-mono text-xs text-slate-300 font-bold select-all">{{ $order->seller->bank_cbu }}</code>
                    <button type="button" @click="copy('{{ $order->seller->bank_cbu }}')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-xs text-slate-300 font-bold cursor-pointer">
                        Copiar CBU
                    </button>
                </div>
            </div>
            @endif
        </div>
        @endif

        <div x-show="copied" x-cloak class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400 text-xs font-bold text-center">
            ¡Copiado al portapapeles!
        </div>

        <!-- Botón Contactar a Soporte por WhatsApp (Registro ya completado en el panel) -->
        @php
            $waNumber = $order->seller ? preg_replace('/[^0-9]/', '', $order->seller->support_whatsapp ?: $order->seller->phone) : null;
            $orderDesc = $order->isPanelOrder() 
                ? ($order->order_type === 'panel_pro' ? 'Panel PRO Ilimitado' : 'Panel Básico')
                : ($order->isCartOrder() ? 'Combo Cuentas Carrito' : ($order->servicePlan ? $order->servicePlan->name : 'Streaming'));
            $waMsg = "¡Hola Soporte! Acabo de registrar el pedido #" . $order->order_code . " por $" . number_format($order->amount, 0, ',', '.') . " (" . $orderDesc . "). Adjunto mi comprobante de pago para su validación en el panel.";
        @endphp

        @if($waNumber)
        <div class="space-y-2">
            <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode($waMsg) }}" target="_blank"
               class="w-full py-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:opacity-95 text-white font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-xl shadow-emerald-600/30 transition-all cursor-pointer">
                <i data-lucide="message-circle" class="w-5 h-5"></i>
                <span>Enviar Comprobante a Soporte por WhatsApp</span>
            </a>
            <p class="text-[11px] text-slate-400">
                Tu pedido ya quedó registrado automáticamente en el sistema. Toca el botón para enviar tu comprobante a soporte.
            </p>
        </div>
        @endif

        @endif

        <div class="pt-4 border-t border-gray-800 flex items-center justify-between text-xs text-gray-400">
            <a href="{{ route('catalog.index', ['ref' => $order->seller ? $order->seller->referral_code : '']) }}" class="hover:text-white transition-colors">
                ← Volver al Catálogo
            </a>
            <a href="{{ route('boveda.login') }}" class="text-cyan-400 hover:underline">
                Portal de Bóveda
            </a>
        </div>
    </div>

</div>
@endsection
