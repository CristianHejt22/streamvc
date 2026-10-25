@extends('layouts.public')

@section('title', ($seller && $seller->isReseller()) ? ($seller->brand_name ?: $seller->name) . ' | Cuentas Streaming' : 'CStreame Master Platform | Paneles & Cuentas Streaming')

@section('content')
<div class="space-y-16 pb-16" x-data="catalogApp()">

    <!-- ======================================================== -->
    <!-- HERO SECTION DINÁMICO (SUPER ADMIN vs REVENDEDOR) -->
    <!-- ======================================================== -->
    @if($seller && $seller->isReseller())
    <!-- ========================================== -->
    <!-- LANDING PERSONALIZADA PARA REVENDEDOR CON EL NOMBRE DE SU PANEL -->
    <!-- ========================================== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12">
        <div class="relative rounded-3xl bg-gradient-to-br from-slate-900 via-[#0d1424] to-slate-950 border border-slate-800 p-8 sm:p-12 overflow-hidden shadow-2xl">
            <!-- Glow background -->
            <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full blur-3xl pointer-events-none opacity-20" style="background-color: {{ $primaryColor }}"></div>

            <div class="relative z-10 max-w-3xl space-y-5">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700 text-xs font-bold font-mono">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-white">TIENDA OFICIAL ASOCIADA</span>
                    <span class="text-sky-400">• Ref: {{ $seller->referral_code }}</span>
                    @if($seller->isPanelBasic())
                    <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px]">Panel BÁSICO</span>
                    @else
                    <span class="px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30 text-[10px]">Panel PRO ILIMITADO</span>
                    @endif
                </div>

                <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                    Bienvenido a <span class="bg-gradient-to-r from-red-500 via-rose-400 to-amber-400 bg-clip-text text-transparent">{{ $brandName ?? ($seller->brand_name ?? 'CStreame Streaming') }}</span>
                </h1>

                @if($seller->canResellPanels() && $seller->isPanelSalesOffered())
                <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                    Distribuidor oficial de streaming y franquicias de paneles independientes. Adquiere tus cuentas garantizadas o <strong>inicia tu propio negocio de reventa</strong> con Panel Básico o PRO, catálogo sincronizado y Bóveda privada para tus clientes.
                </p>
                @else
                <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                    Tu distribuidor de confianza para cuentas y perfiles de Netflix 4K, Disney+, Max, Prime Video y MagisTV. Entrega segura y protegida en tu <strong>Bóveda Privada de Cliente</strong> con biometría y PIN personal.
                </p>
                @endif

                <!-- Botones de Acción -->
                <div class="flex flex-wrap items-center gap-3 pt-2">
                    @if($seller->canResellPanels() && $seller->isPanelSalesOffered())
                    <a href="#planes-paneles" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:opacity-90 text-white font-bold text-xs flex items-center gap-2 shadow-xl shadow-purple-600/30 transition-all cursor-pointer">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                        <span>Franquicia de Paneles</span>
                    </a>
                    @endif

                    <a href="#catalogo" class="px-5 py-3 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center gap-2 shadow-xl shadow-red-600/30 transition-all cursor-pointer">
                        <i data-lucide="tv" class="w-4 h-4"></i>
                        <span>Ver Cuentas Disponibles</span>
                    </a>

                    <a href="{{ route('boveda.login') }}" class="px-5 py-3 rounded-2xl bg-slate-800/90 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs flex items-center gap-2 transition-all cursor-pointer">
                        <i data-lucide="key" class="w-4 h-4 text-amber-400"></i>
                        <span>Acceder a Mi Bóveda</span>
                    </a>

                    @if($supportWhatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsapp) }}?text=Hola%20{{ urlencode($brandName ?? 'CStreame') }},%20tengo%20una%20consulta%20sobre%20tus%20servicios%20de%20streaming." target="_blank" class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2 shadow-xl shadow-emerald-600/20 transition-all">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Contactar por WhatsApp</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if($seller && $seller->isReseller() && $seller->canResellPanels() && $seller->isPanelSalesOffered())
    <!-- OFERTA DE PANELES CON PRECIOS PERSONALIZADOS DEL PROVEEDOR/REVENDEDOR -->
    <section id="planes-paneles" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 scroll-mt-24">
        <div class="text-center max-w-2xl mx-auto space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-purple-400 font-mono">Emprende con Tu Propio Panel</span>
            <h2 class="text-2xl sm:text-3xl font-black text-white">Lanza Tu Propia Franquicia Streaming con {{ $brandName }}</h2>
            <p class="text-xs text-slate-400">
                Obtén tu panel independiente con Bóveda de Clientes, entrega automática y soporte directo.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <!-- PANEL BÁSICO -->
            <div class="rounded-3xl bg-[#111827] border border-amber-500/30 p-6 flex flex-col justify-between space-y-6 shadow-xl hover:border-amber-500/60 transition-all">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold font-mono bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            📦 PANEL BÁSICO
                        </span>
                        <span class="text-xs text-slate-400 font-bold">10 Servicios</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white">Panel Franquicia Básico</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Hasta 10 servicios de streaming activos simultáneos.</p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800">
                        <span class="text-[10px] text-gray-400 block uppercase font-bold">Suscripción Mensual:</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-3xl font-black text-white font-mono">${{ number_format($seller->basic_panel_price, 0, ',', '.') }}</span>
                            <span class="text-xs text-slate-400 font-mono">/ mes</span>
                        </div>
                    </div>
                    <ul class="space-y-2 text-xs text-slate-300">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span>Catálogo con 10 servicios</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span>Bóveda Privada para tus clientes</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <span>Soporte por WhatsApp de {{ $brandName }}</span>
                        </li>
                    </ul>
                </div>
                <div>
                    @if($supportWhatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsapp) }}?text=Hola%20{{ urlencode($brandName) }},%20quisiera%20solicitar%20la%20activaci%C3%B3n%20de%20un%20PANEL%20B%C3%81SICO%20por%20${{ number_format($seller->basic_panel_price, 0) }}." target="_blank" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-md">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Solicitar Panel Básico (${{ number_format($seller->basic_panel_price, 0, ',', '.') }})</span>
                    </a>
                    @endif
                    @if($seller->referral_code)
                    <div class="mt-2 text-center">
                        <a href="{{ route('affiliate.register', $seller->referral_code) }}" class="text-[11px] text-amber-300/80 hover:text-amber-200 hover:underline font-mono inline-flex items-center gap-1">
                            <span>O registrarme en línea directamente</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- PANEL PRO -->
            <div class="rounded-3xl bg-gradient-to-b from-[#141b2d] to-[#0f172a] border-2 border-purple-500/50 p-6 flex flex-col justify-between space-y-6 shadow-xl hover:border-purple-400 transition-all">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold font-mono bg-purple-500/20 text-purple-300 border border-purple-500/30">
                            💎 PANEL PRO (ILIMITADO)
                        </span>
                        <span class="text-xs text-purple-300 font-bold">Sin Límites</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white">Panel Franquicia PRO Ilimitado</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Servicios ilimitados, marca personalizada y reventa.</p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-900 border border-purple-500/30">
                        <span class="text-[10px] text-purple-400 block uppercase font-bold">Suscripción Mensual:</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-3xl font-black text-purple-400 font-mono">${{ number_format($seller->pro_panel_price, 0, ',', '.') }}</span>
                            <span class="text-xs text-slate-400 font-mono">/ mes</span>
                        </div>
                    </div>
                    <ul class="space-y-2 text-xs text-slate-300">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-purple-400"></i>
                            <span>Servicios y plataformas ILIMITADAS</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-purple-400"></i>
                            <span>Marca Blanca Propia (Logo, nombre y colores)</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-purple-400"></i>
                            <span>Capacidad de crear y revender sub-paneles</span>
                        </li>
                    </ul>
                </div>
                <div>
                    @if($supportWhatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsapp) }}?text=Hola%20{{ urlencode($brandName) }},%20quisiera%20solicitar%20la%20activaci%C3%B3n%20de%20un%20PANEL%20PRO%20por%20${{ number_format($seller->pro_panel_price, 0) }}." target="_blank" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:opacity-90 text-white font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-md shadow-purple-600/30">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Solicitar Panel PRO (${{ number_format($seller->pro_panel_price, 0, ',', '.') }})</span>
                    </a>
                    @endif
                    @if($seller->referral_code)
                    <div class="mt-2 text-center">
                        <a href="{{ route('affiliate.register', $seller->referral_code) }}" class="text-[11px] text-purple-300/80 hover:text-purple-200 hover:underline font-mono inline-flex items-center gap-1">
                            <span>O registrarme en línea directamente</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

    @else
    <!-- ========================================== -->
    <!-- LANDING PAGE SUPER ADMIN (VENTA DE PANELES & CRÉDITOS) -->
    <!-- ========================================== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-14 space-y-12">
        <div class="text-center max-w-4xl mx-auto space-y-5">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-600/10 border border-red-500/20 text-red-400 text-xs font-bold tracking-wide">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                <span>PLATAFORMA OFICIAL MAYORISTA & SISTEMA DE PANELES STREAMING</span>
            </div>

            <h1 class="text-4xl sm:text-6xl font-black text-white tracking-tight leading-tight">
                Lanza Tu Propia Franquicia de <br class="hidden sm:inline">
                <span class="bg-gradient-to-r from-red-500 via-rose-400 to-amber-400 bg-clip-text text-transparent">Cuentas Streaming</span> con Marca Blanca
            </h1>

            <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl mx-auto">
                Gestiona y vende Netflix 4K, Disney+, Max y más. Panel propio, activación con créditos, <strong>Bóveda con Biometría</strong> para clientes, entrega automática con saldo en billetera o transferencia bancaria y comisiones en tiempo real.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-3 pt-3">
                <a href="#planes-paneles" class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-red-600 to-rose-600 hover:opacity-90 text-white font-black text-xs uppercase tracking-wider shadow-2xl shadow-red-600/30 flex items-center gap-2 transition-all cursor-pointer">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    <span>Ver Planes Básico & PRO</span>
                </a>

                <a href="#cotizador-creditos" class="px-6 py-3.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-sky-400 font-bold text-xs uppercase tracking-wider border border-slate-700 flex items-center gap-2 transition-all cursor-pointer">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                    <span>Cotizar Créditos</span>
                </a>

                <a href="#como-funciona" class="px-6 py-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-slate-300 font-bold text-xs uppercase tracking-wider border border-slate-800 flex items-center gap-2 transition-all cursor-pointer">
                    <i data-lucide="help-circle" class="w-4 h-4 text-red-400"></i>
                    <span>¿Cómo Funciona?</span>
                </a>

                <a href="#faq" class="px-6 py-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-slate-300 font-bold text-xs uppercase tracking-wider border border-slate-800 flex items-center gap-2 transition-all cursor-pointer">
                    <i data-lucide="message-square" class="w-4 h-4 text-amber-400"></i>
                    <span>FAQ</span>
                </a>

                <a href="{{ route('boveda.login') }}" class="px-6 py-3.5 rounded-2xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 font-bold text-xs uppercase tracking-wider border border-amber-500/30 flex items-center gap-2 transition-all cursor-pointer">
                    <i data-lucide="shield-check" class="w-4 h-4 text-amber-400"></i>
                    <span>Bóveda Clientes</span>
                </a>
            </div>
        </div>

        <!-- Métricas de Confianza -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 text-center">
                <span class="text-2xl font-black text-white block">+15.000</span>
                <span class="text-xs text-slate-400">Cuentas Entregadas</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 text-center">
                <span class="text-2xl font-black text-emerald-400 block">99.9%</span>
                <span class="text-xs text-slate-400">Uptime & Garantía</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 text-center">
                <span class="text-2xl font-black text-sky-400 block">Inmediata</span>
                <span class="text-xs text-slate-400">Entrega Automática</span>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 text-center">
                <span class="text-2xl font-black text-amber-400 block">WebAuthn</span>
                <span class="text-xs text-slate-400">Biometría Bóveda</span>
            </div>
        </div>
    </section>

    @if($seller && $seller->isPanelSalesOffered())
    <!-- ======================================================== -->
    <!-- COMPARATIVA DE PANELES: BÁSICO (10 SERVICIOS) vs PRO ILIMITADO -->
    <!-- ======================================================== -->
    <section id="planes-paneles" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 scroll-mt-24">
        <div class="text-center max-w-2xl mx-auto space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-purple-400 font-mono">Emprende con Tu Propio Panel</span>
            <h2 class="text-3xl font-black text-white tracking-tight">Elige Tu Tipo de Panel de Streaming</h2>
            <p class="text-xs sm:text-sm text-slate-400">
                Inicia tu propio negocio de reventa de streaming con entrega automática, Bóveda privada de clientes y soporte garantizado.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
            <!-- PANEL BÁSICO (10 SERVICIOS) -->
            <div class="rounded-3xl bg-[#111827] border border-amber-500/30 p-7 sm:p-9 flex flex-col justify-between relative shadow-2xl hover:border-amber-500/60 transition-all">
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full text-xs font-bold font-mono bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            📦 PANEL BÁSICO
                        </span>
                        <span class="text-xs text-slate-400 font-bold">10 Servicios Max</span>
                    </div>

                    <div>
                        <h3 class="text-2xl font-black text-white">Panel Distribuidor Básico</h3>
                        <p class="text-xs text-slate-400 mt-1">Ideal para revendedores que comercializan hasta 10 plataformas principales.</p>
                    </div>

                    <!-- Precio Sugerido Usuario Final -->
                    <div class="p-4 rounded-2xl bg-slate-900/90 border border-amber-500/30 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400 block">Suscripción Mensual / Precio Sugerido:</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-3xl font-black text-white font-mono">${{ number_format($seller->basic_panel_price, 0, ',', '.') }}</span>
                            <span class="text-xs text-slate-400 font-mono">/ mes</span>
                        </div>
                        <span class="text-[10px] text-slate-400 block pt-1 border-t border-slate-800">
                            💡 PVP Sugerido Oficial al Público para emprender con tu propio panel de 10 servicios.
                        </span>
                    </div>

                    <!-- Características -->
                    <ul class="space-y-2.5 text-xs text-slate-300">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>Catálogo limitado a <strong>10 servicios activos</strong></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>Bóveda privada de clientes con <strong>PIN y Biometría</strong></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>Entrega protegida: sin saldo no se revelan credenciales</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>Entrega automática con saldo o transferencia bancaria aceptada</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>Soporte por WhatsApp de {{ $brandName }}</span>
                        </li>
                    </ul>
                </div>

                <div class="pt-6 mt-6 border-t border-slate-800 space-y-2">
                    @if($supportWhatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsapp) }}?text=Hola%20{{ urlencode($brandName) }},%20quisiera%20solicitar%20la%20activaci%C3%B3n%20de%20un%20PANEL%20B%C3%81SICO%20por%20${{ number_format($seller->basic_panel_price, 0) }}." target="_blank" class="w-full py-3 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all shadow-lg shadow-amber-500/20 cursor-pointer">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Adquirir Panel Básico (${{ number_format($seller->basic_panel_price, 0, ',', '.') }}/mes)</span>
                    </a>
                    @endif
                    @if($seller->referral_code)
                    <div class="text-center">
                        <a href="{{ route('affiliate.register', $seller->referral_code) }}" class="text-[11px] text-amber-300/80 hover:text-amber-200 hover:underline font-mono inline-flex items-center gap-1">
                            <span>O registrarme en línea directamente</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- PANEL PRO (SERVICIOS ILIMITADOS) -->
            <div class="rounded-3xl bg-gradient-to-b from-[#141b2d] to-[#0f172a] border-2 border-purple-500/50 p-7 sm:p-9 flex flex-col justify-between relative shadow-2xl shadow-purple-500/10 hover:border-purple-400 transition-all">
                <!-- Badge Destacado -->
                <div class="absolute -top-3.5 right-6 px-3 py-1 rounded-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-black text-[10px] uppercase tracking-wider shadow-lg">
                    🔥 RECOMENDADO - MÁXIMA GANANCIA
                </div>

                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full text-xs font-bold font-mono bg-purple-500/20 text-purple-300 border border-purple-500/30">
                            💎 PANEL PRO (ILIMITADO)
                        </span>
                        <span class="text-xs text-purple-300 font-bold">Servicios ILIMITADOS</span>
                    </div>

                    <div>
                        <h3 class="text-2xl font-black text-white">Panel Mayorista PRO Ilimitado</h3>
                        <p class="text-xs text-slate-400 mt-1">Para proveedor o revendedor que quiere libertad total de servicios y stock.</p>
                    </div>

                    <!-- Precio Sugerido Usuario Final -->
                    <div class="p-4 rounded-2xl bg-slate-900/90 border border-purple-500/30 space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-purple-400 block">Suscripción Mensual / Precio Sugerido:</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-3xl font-black text-purple-400 font-mono">${{ number_format($seller->pro_panel_price, 0, ',', '.') }}</span>
                            <span class="text-xs text-slate-400 font-mono">/ mes</span>
                        </div>
                        <span class="text-[10px] text-purple-300/80 block pt-1 border-t border-slate-800">
                            💡 PVP Sugerido Oficial al Público con servicios ilimitados, marca propia y reventa de paneles.
                        </span>
                    </div>

                    <!-- Características -->
                    <ul class="space-y-2.5 text-xs text-slate-300">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-purple-400 shrink-0"></i>
                            <span><strong>Servicios y plataformas ILIMITADAS</strong></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-purple-400 shrink-0"></i>
                            <span><strong>Marca Blanca Propia</strong>: tu propio nombre, logo y colores</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-purple-400 shrink-0"></i>
                            <span><strong>Reventa de paneles</strong> a tu propia red de revendedores</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-purple-400 shrink-0"></i>
                            <span>Bóveda privada de clientes con <strong>PIN y Biometría</strong></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-purple-400 shrink-0"></i>
                            <span>Entrega automática con saldo o transferencia bancaria aceptada</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-purple-400 shrink-0"></i>
                            <span>Soporte por WhatsApp de {{ $brandName }}</span>
                        </li>
                    </ul>
                </div>

                <div class="pt-6 mt-6 border-t border-slate-800 space-y-2">
                    @if($supportWhatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsapp) }}?text=Hola%20{{ urlencode($brandName) }},%20quisiera%20solicitar%20la%20activaci%C3%B3n%20de%20un%20PANEL%20PRO%20por%20${{ number_format($seller->pro_panel_price, 0) }}." target="_blank" class="w-full py-3 rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:opacity-90 text-white font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all shadow-lg shadow-purple-600/30 cursor-pointer">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Adquirir Panel PRO (${{ number_format($seller->pro_panel_price, 0, ',', '.') }}/mes)</span>
                    </a>
                    @endif
                    @if($seller->referral_code)
                    <div class="text-center">
                        <a href="{{ route('affiliate.register', $seller->referral_code) }}" class="text-[11px] text-purple-300/80 hover:text-purple-200 hover:underline font-mono inline-flex items-center gap-1">
                            <span>O registrarme en línea directamente</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

        <!-- ======================================================== -->
        <!-- PACK COMBO DÚO: 5 DE CADA UNO JUNTOS -->
        <!-- ======================================================== -->
        <div class="max-w-5xl mx-auto rounded-3xl bg-gradient-to-r from-amber-950/40 via-[#131b2e] to-purple-950/40 border-2 border-emerald-500/50 p-6 sm:p-8 shadow-2xl relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-500 text-slate-950 uppercase tracking-wider">
                        🌟 PACK COMBO DÚO (5 DE CADA UNO)
                    </span>
                    <span class="text-xs font-bold text-emerald-400 font-mono">10 Créditos Mixtos</span>
                </div>
                <h3 class="text-xl sm:text-2xl font-black text-white">5 Créditos Básico + 5 Créditos PRO Juntos</h3>
                <p class="text-xs text-slate-300 max-w-xl leading-relaxed">
                    Para proveedor o revendedor que desea activar y gestionar ambos tipos de paneles con flexibilidad total: 
                    5 créditos Básico a $3.000 c/u ($15.000) y 5 créditos PRO a $5.000 c/u ($25.000).
                </p>
                <div class="flex flex-wrap items-center gap-2 pt-1 text-xs font-mono">
                    <span class="px-2.5 py-1 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-300">📦 5 Básicos: $15.000 ($3.000 c/u)</span>
                    <span class="px-2.5 py-1 rounded-xl bg-purple-500/15 border border-purple-500/30 text-purple-300">💎 5 PRO: $25.000 ($5.000 c/u)</span>
                </div>
            </div>

            <div class="flex flex-col sm:items-end justify-center shrink-0 space-y-3">
                <div class="sm:text-right">
                    <span class="text-xs text-slate-400 uppercase font-bold block">Inversión Total Combo:</span>
                    <span class="text-3xl font-black text-emerald-400 font-mono">$40.000</span>
                    <span class="text-[11px] text-slate-400 block font-mono">10 créditos listos en tu billetera</span>
                </div>
                @if($supportWhatsapp)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsapp) }}?text=Hola,%20quisiera%20solicitar%20el%20PACK%20COMBO%20D%C3%9AO%20(5%20B%C3%A1sicos%20+%205%20PRO)%20por%20$40.000." target="_blank" class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2 shadow-xl shadow-emerald-600/30 transition-all cursor-pointer">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Solicitar Combo Dúo ($40k)</span>
                </a>
                @endif
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- COTIZADOR & CALCULADORA EN VIVO DE CRÉDITOS -->
    <!-- ======================================================== -->
    <section id="cotizador-creditos" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-24" x-data="{
        calcPack: 'combo_5_5',
        get packDetails() {
            if (this.calcPack === 'basic_10') {
                return {
                    title: 'Pack 10 Créditos Básicos',
                    tierBadge: '📦 BÁSICO (10 SERVICIOS)',
                    desc: '10 créditos para activar paneles básicos (hasta 10 servicios)',
                    unitPriceText: '$2.500 c/u (mínimo 10 créditos)',
                    totalPrice: 25000,
                    qtyText: '10 Créditos Básicos'
                };
            } else if (this.calcPack === 'pro_10') {
                return {
                    title: 'Pack 10 Créditos PRO',
                    tierBadge: '💎 PRO ILIMITADO',
                    desc: '10 créditos para activar paneles PRO (servicios ilimitados)',
                    unitPriceText: '$4.000 c/u (mínimo 10 créditos)',
                    totalPrice: 40000,
                    qtyText: '10 Créditos PRO'
                };
            } else {
                return {
                    title: 'Pack Combo Dúo (5 de Cada Uno)',
                    tierBadge: '🌟 5 BÁSICOS + 5 PRO JUNTOS',
                    desc: '5 créditos Básico a $3.000 ($15.000) + 5 créditos PRO a $5.000 ($25.000)',
                    unitPriceText: '5 Básicos ($3.000 c/u) + 5 PRO ($5.000 c/u)',
                    totalPrice: 40000,
                    qtyText: '10 Créditos Mixtos (5 Básicos + 5 PRO)'
                };
            }
        }
    }">
        <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-[#10182b] to-slate-900 border border-slate-800 p-6 sm:p-9 space-y-6 shadow-2xl relative overflow-hidden">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
                <div class="w-12 h-12 rounded-2xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold">
                    <i data-lucide="calculator" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-white">Cotizador de Créditos de Activación</h3>
                    <p class="text-xs text-slate-400">Selecciona el paquete para proveedor o revendedor y cotiza al instante</p>
                </div>
            </div>

            <!-- Selector de Paquete Oficial -->
            <div class="space-y-3">
                <label class="block text-xs font-bold uppercase text-slate-400">Selecciona el Paquete de Créditos</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <!-- 10 Básicos -->
                    <button type="button" @click="calcPack = 'basic_10'" 
                            :class="calcPack === 'basic_10' ? 'bg-amber-500/20 border-amber-500 ring-2 ring-amber-500/30 text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'"
                            class="p-4 rounded-2xl border text-left flex flex-col justify-between transition-all cursor-pointer">
                        <div>
                            <span class="text-[10px] font-mono font-bold text-amber-400 block uppercase">10 Básicos</span>
                            <strong class="text-white text-sm block mt-1">Pack 10 Básicos</strong>
                            <span class="text-[11px] text-slate-400 block mt-0.5">$2.500 c/u</span>
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-800/80 font-black text-white font-mono text-sm">
                            $25.000
                        </div>
                    </button>

                    <!-- 10 PRO -->
                    <button type="button" @click="calcPack = 'pro_10'" 
                            :class="calcPack === 'pro_10' ? 'bg-purple-500/20 border-purple-500 ring-2 ring-purple-500/30 text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'"
                            class="p-4 rounded-2xl border text-left flex flex-col justify-between transition-all cursor-pointer">
                        <div>
                            <span class="text-[10px] font-mono font-bold text-purple-400 block uppercase">10 PRO</span>
                            <strong class="text-white text-sm block mt-1">Pack 10 PRO</strong>
                            <span class="text-[11px] text-slate-400 block mt-0.5">$4.000 c/u · Ilimitado</span>
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-800/80 font-black text-white font-mono text-sm">
                            $40.000
                        </div>
                    </button>

                    <!-- Combo 5 de Cada Uno -->
                    <button type="button" @click="calcPack = 'combo_5_5'" 
                            :class="calcPack === 'combo_5_5' ? 'bg-emerald-500/20 border-emerald-500 ring-2 ring-emerald-500/30 text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'"
                            class="p-4 rounded-2xl border text-left flex flex-col justify-between transition-all cursor-pointer">
                        <div>
                            <span class="text-[10px] font-mono font-bold text-emerald-400 block uppercase">Combo Dúo</span>
                            <strong class="text-white text-sm block mt-1">5 de Cada Uno</strong>
                            <span class="text-[11px] text-slate-400 block mt-0.5">5 Básicos + 5 PRO</span>
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-800/80 font-black text-emerald-400 font-mono text-sm">
                            $40.000
                        </div>
                    </button>
                </div>
            </div>

            <!-- Resumen en Vivo -->
            <div class="p-5 rounded-2xl bg-slate-950/90 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-slate-800 text-slate-300 inline-block" x-text="packDetails.tierBadge"></span>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <span class="text-3xl font-black text-emerald-400 font-mono" x-text="'$' + packDetails.totalPrice.toLocaleString('es-AR', {minimumFractionDigits: 2})"></span>
                        <span class="text-xs text-slate-400 font-mono" x-text="'(' + packDetails.unitPriceText + ')'"></span>
                    </div>
                    <p class="text-xs text-slate-400" x-text="packDetails.desc"></p>
                </div>

                @if($supportWhatsapp)
                <a :href="'https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsapp) }}?text=Hola,%20quiero%20comprar%20el%20' + encodeURIComponent(packDetails.title) + '%20por%20$' + packDetails.totalPrice.toLocaleString('es-AR') + '.'" target="_blank" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/30 transition-all shrink-0 cursor-pointer">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Ordenar Pack por WhatsApp</span>
                </a>
                @endif
            </div>
        </div>
    </section>
    @endif

    <!-- ======================================================== -->
    <!-- CATÁLOGO DE PLATAFORMAS STREAMING (PVP AL PÚBLICO) -->
    <!-- ======================================================== -->
    <section id="catalogo" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 scroll-mt-24">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-2 border-b border-slate-800">
            <div>
                @if($seller && $seller->isReseller() && $seller->canResellPanels() && $seller->isPanelSalesOffered())
                <span class="text-xs font-bold uppercase tracking-wider text-purple-400 block font-mono">Servicios Disponibles para Clientes y Revendedores</span>
                <h2 class="text-2xl sm:text-3xl font-black text-white">Catálogo de Cuentas & Pantallas Oficiales</h2>
                @else
                <span class="text-xs font-bold uppercase tracking-wider text-red-400 block font-mono">Disfruta Tu Contenido Favorito</span>
                <h2 class="text-2xl sm:text-3xl font-black text-white">Catálogo de Cuentas & Pantallas</h2>
                @endif
            </div>

            <!-- Categories Pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                <a href="{{ route('catalog.index', array_merge(request()->except('category'), ['ref' => request('ref')])) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ !request('category') ? 'bg-red-600 text-white shadow-md' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                    Todos
                </a>
                @foreach($categories as $cat)
                <a href="{{ route('catalog.index', array_merge(request()->except('category'), ['category' => $cat, 'ref' => request('ref')])) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('category') === $cat ? 'bg-red-600 text-white shadow-md' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                    {{ ucfirst($cat) }}
                </a>
                @endforeach
            </div>
        </div>

        <!-- Banner Interactivo de Promoción de Combos Multicuentas -->
        @if($seller && $seller->isComboDiscountActive())
        <div class="rounded-3xl p-5 sm:p-6 border transition-all duration-300 relative overflow-hidden shadow-2xl"
             :class="isComboApplied ? 'bg-gradient-to-r from-emerald-950/80 via-slate-900 to-teal-950/80 border-emerald-500/60 shadow-emerald-900/30' : 'bg-gradient-to-r from-amber-950/60 via-slate-900 to-slate-900 border-amber-500/40 shadow-amber-950/20'">
            <!-- Glow background -->
            <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full blur-3xl pointer-events-none opacity-20"
                 :class="isComboApplied ? 'bg-emerald-400' : 'bg-amber-400'"></div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider flex items-center gap-1.5"
                              :class="isComboApplied ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-amber-500/20 text-amber-300 border border-amber-500/40'">
                            <span class="w-2 h-2 rounded-full animate-ping" :class="isComboApplied ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                            <span x-text="isComboApplied ? '🎉 ¡PROMO COMBO APLICADA!' : '🔥 PROMO COMBO MULTICUENTAS'"></span>
                        </span>
                        <span class="text-xs text-slate-400 hidden sm:inline">• Elige y combina las plataformas que quieras</span>
                    </div>
                    
                    <h3 class="text-lg sm:text-xl font-black text-white">
                        <span x-show="!isComboApplied">{{ $seller->getComboBannerText() }}</span>
                        <span x-show="isComboApplied" x-cloak class="text-emerald-400">
                            ¡Felicidades! Tienes <span x-text="totalItems"></span> cuentas en tu carrito y ahorras <span x-text="formatMoney(comboDiscount)"></span> de descuento inmediato.
                        </span>
                    </h3>

                    <p class="text-xs text-slate-400">
                        <span x-show="totalItems === 0">Agrega tus cuentas favoritas al carrito con el botón <strong>"+ Carrito"</strong> para armar tu combo.</span>
                        <span x-show="totalItems === 1" x-cloak class="text-amber-300 font-semibold">✨ ¡Te falta solo 1 cuenta más para desbloquear el descuento de combo!</span>
                        <span x-show="isComboApplied" x-cloak class="text-emerald-300">Descuento aplicado en tiempo real sobre tu total. Puedes sumar más cuentas para maximizar tu ahorro.</span>
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <button type="button" @click="openCartDrawer = true" 
                            class="px-5 py-3 rounded-2xl font-bold text-xs flex items-center gap-2.5 transition-all shadow-xl cursor-pointer"
                            :class="isComboApplied ? 'bg-emerald-500 hover:bg-emerald-400 text-slate-950 shadow-emerald-500/30' : 'bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-amber-500/30'">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        <span>Ver Carrito (<span x-text="totalItems"></span>)</span>
                        <span class="px-2 py-0.5 rounded-lg bg-black/20 font-mono text-[11px]" x-text="formatMoney(finalTotal)"></span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Grid de Servicios -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($services as $serv)
            <div class="bg-[#111827] border border-slate-800 rounded-3xl p-6 flex flex-col justify-between hover:border-slate-700 transition-all hover:scale-[1.01] shadow-xl group relative overflow-hidden">
                <!-- Glow Accent -->
                <div class="absolute -top-12 -right-12 w-28 h-28 rounded-full blur-2xl pointer-events-none opacity-20" style="background-color: {{ $serv->color_hex }}"></div>

                <div>
                    <!-- Header Tarjeta -->
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-white shadow-lg text-lg shrink-0" style="background-color: {{ $serv->color_hex }}">
                                {{ substr($serv->name, 0, 2) }}
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-white group-hover:text-red-400 transition-colors">
                                    {{ $serv->name }}
                                </h3>
                                <span class="text-[11px] text-slate-400 capitalize">{{ $serv->category }} · Ultra HD 4K</span>
                            </div>
                        </div>

                        @if($serv->isIndividual())
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                            Exclusivo
                        </span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            ONLINE
                        </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-400 mt-2 line-clamp-2">
                        {{ $serv->description ?: 'Disfruta de tus películas y series favoritas con perfiles privados o cuenta completa.' }}
                    </p>

                    <!-- Planes de la Plataforma con Soporte de Carrito y Combos -->
                    <div class="mt-4 pt-3 border-t border-slate-800/80 space-y-2.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Planes Disponibles:</span>
                        @foreach($serv->plans as $plan)
                        <div class="p-3.5 rounded-2xl bg-[#0b0f19] border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-slate-700 transition-all">
                            <div>
                                <span class="text-xs font-bold text-white block">{{ $plan->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $plan->duration_days }} días · {{ $plan->modality === 'profile' ? 'Pantalla con PIN' : 'Cuenta Completa' }}</span>
                            </div>
                            <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                                <span class="text-base font-black text-emerald-400 font-mono">${{ number_format($plan->price, 0, ',', '.') }}</span>
                                
                                <div class="flex items-center gap-1.5">
                                    <!-- Botón Agregar al Carrito si no está en carrito -->
                                    <template x-if="getItemQty({{ $plan->id }}) === 0">
                                        <button type="button" 
                                            @click="addToCart({ id: {{ $plan->id }}, name: '{{ addslashes($plan->name) }}', price: {{ (float)$plan->price }}, duration_days: {{ $plan->duration_days }}, modality: '{{ $plan->modality }}' }, { id: {{ $serv->id }}, name: '{{ addslashes($serv->name) }}', color_hex: '{{ $serv->color_hex }}' })" 
                                            class="px-2.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-[11px] flex items-center gap-1 shadow-md shadow-amber-500/20 transition-all cursor-pointer">
                                            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                                            <span>+ Carrito</span>
                                        </button>
                                    </template>

                                    <!-- Selector de Cantidad si ya está en carrito -->
                                    <template x-if="getItemQty({{ $plan->id }}) > 0">
                                        <div class="flex items-center gap-1 bg-slate-900 border border-amber-500/50 rounded-xl p-0.5 shadow-sm">
                                            <button type="button" @click="updateQty({{ $plan->id }}, -1)" class="w-6 h-6 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold flex items-center justify-center text-xs transition-colors cursor-pointer">-</button>
                                            <span class="px-2 text-xs font-mono font-bold text-amber-400" x-text="getItemQty({{ $plan->id }})"></span>
                                            <button type="button" @click="updateQty({{ $plan->id }}, 1)" class="w-6 h-6 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold flex items-center justify-center text-xs transition-colors cursor-pointer">+</button>
                                        </div>
                                    </template>

                                    <!-- Botón Comprar Ya Directo -->
                                    <a href="{{ route('catalog.checkout', ['plan' => $plan->id, 'seller_id' => $seller->id]) }}" class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-semibold text-[11px] border border-slate-700 transition-all text-center">
                                        Comprar
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Footer Tarjeta -->
                <div class="mt-4 pt-3 border-t border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                    <span class="flex items-center gap-1 text-emerald-400 font-semibold">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        Garantía Oficial
                    </span>
                    <span class="flex items-center gap-1 text-sky-400 font-semibold">
                        <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                        Entrega en Bóveda
                    </span>
                </div>
            </div>
            @empty
            <div class="col-span-3 py-16 text-center text-slate-500 text-sm">
                No hay plataformas disponibles en esta categoría en este momento.
            </div>
            @endforelse
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- SECCIÓN DE SEGURIDAD: BÓVEDA CON BIOMETRÍA & ANTI-PHISHING -->
    <!-- ======================================================== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-[#0e1628] to-slate-900 border border-slate-800 p-8 sm:p-12">
            <div class="max-w-3xl mx-auto text-center space-y-4">
                <span class="text-xs font-mono font-bold text-amber-400 uppercase tracking-wider">Tecnología de Protección Avanzada</span>
                <h2 class="text-3xl sm:text-4xl font-black text-white">¿Por Qué Usar la Bóveda Privada de Cliente?</h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    Tus credenciales de streaming nunca se pierden en un chat de WhatsApp. Todos tus correos, claves, perfiles y PINs quedan centralizados y protegidos en una plataforma cifrada grado militar.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
                <div class="p-6 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-3 text-center">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto">
                        <i data-lucide="fingerprint" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Desbloqueo Biométrico & PIN</h3>
                    <p class="text-xs text-slate-400">Protege la visualización de tus contraseñas con tu huella dactilar, Face ID o PIN personal de 4 dígitos.</p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-3 text-center">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto">
                        <i data-lucide="refresh-cw" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Renovación en 1 Clic</h3>
                    <p class="text-xs text-slate-400">Solicita extender tu cuenta por 1, 2, 3 o 6 meses sin perder tu perfil, tus series ni tus preferencias.</p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-3 text-center">
                    <div class="w-12 h-12 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center mx-auto">
                        <i data-lucide="tv" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Guía Smart TV & Anti-Hogar</h3>
                    <p class="text-xs text-slate-400">Tutorial interactivo dentro de tu Bóveda para vincular tu televisor sin bloqueos ni caídas por ubicación.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- SECCIÓN: CÓMO FUNCIONA EL ECOSISTEMA CSTREAME -->
    <!-- ======================================================== -->
    <section id="como-funciona" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 scroll-mt-24">
        @if(isset($seller) && $seller->isReseller())
            @if($seller->canResellPanels() && $seller->isPanelSalesOffered())
                {{-- CÓMO FUNCIONA PARA TIENDA DE PROVEEDOR CON FRANQUICIA DE PANELES --}}
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="text-xs font-mono font-bold text-purple-400 uppercase tracking-wider">Tu Negocio Streaming con {{ $brandName }}</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white">¿Cómo Funciona Tu Franquicia y Compras?</h2>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        Plataforma integral y segura para adquirir cuentas de streaming o lanzar tu propio panel de ventas con catálogo sincronizado y entrega inmediata.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Paso 1 -->
                    <div class="bg-gradient-to-b from-[#111827] to-[#0d1424] border border-slate-800 hover:border-purple-500/50 rounded-3xl p-6 flex flex-col justify-between transition-all group relative overflow-hidden shadow-xl">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-2xl bg-purple-600/20 text-purple-400 font-mono font-black text-sm flex items-center justify-center border border-purple-500/30">
                                    01
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                    Panel o Servicio
                                </span>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                                <i data-lucide="layers" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-black text-white group-hover:text-purple-400 transition-colors">
                                1. Elige Servicio o Franquicia
                            </h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Adquiere tu suscripción de streaming o inicia tu propio negocio con tu <strong>Panel Básico (${{ number_format($seller->basic_panel_price, 0, ',', '.') }})</strong> o <strong>Panel PRO (${{ number_format($seller->pro_panel_price, 0, ',', '.') }})</strong>. Activación y entrega directa con tu proveedor <strong>{{ $brandName }}</strong>.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-800/80">
                            <span class="text-[11px] font-mono text-purple-400 flex items-center gap-1.5 font-bold">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                Activación Inmediata
                            </span>
                        </div>
                    </div>

                    <!-- Paso 2 -->
                    <div class="bg-gradient-to-b from-[#111827] to-[#0d1424] border border-slate-800 hover:border-amber-500/50 rounded-3xl p-6 flex flex-col justify-between transition-all group relative overflow-hidden shadow-xl">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 font-mono font-black text-sm flex items-center justify-center border border-amber-500/30">
                                    02
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                    Saldo & Abono
                                </span>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                                <i data-lucide="wallet" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-black text-white group-hover:text-amber-400 transition-colors">
                                2. Abona Seguro por Transferencia
                            </h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Para garantizar máxima seguridad y entrega al instante, abona mediante transferencia bancaria o saldo en billetera. Toda cuenta y panel se entrega respaldado al 100%, sin riesgos ni consignaciones de gracia.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-800/80">
                            <span class="text-[11px] font-mono text-amber-400 flex items-center gap-1.5 font-bold">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                Pago 100% Protegido
                            </span>
                        </div>
                    </div>

                    <!-- Paso 3 -->
                    <div class="bg-gradient-to-b from-[#111827] to-[#0d1424] border border-slate-800 hover:border-sky-500/50 rounded-3xl p-6 flex flex-col justify-between transition-all group relative overflow-hidden shadow-xl">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-2xl bg-sky-500/20 text-sky-400 font-mono font-black text-sm flex items-center justify-center border border-sky-500/30">
                                    03
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                    Bóveda
                                </span>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                                <i data-lucide="fingerprint" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-black text-white group-hover:text-sky-400 transition-colors">
                                3. Entrega Cifrada en tu Bóveda
                            </h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Tus clientes y tú reciben acceso inmediato a la <strong>Bóveda Cifrada</strong> con desbloqueo por PIN de 4 dígitos o biometría. Tus datos y perfiles quedan protegidos con tecnología anti-phishing.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-800/80">
                            <span class="text-[11px] font-mono text-sky-400 flex items-center gap-1.5 font-bold">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                Cero Fugas & Anti-Phishing
                            </span>
                        </div>
                    </div>

                    <!-- Paso 4 -->
                    <div class="bg-gradient-to-b from-[#111827] to-[#0d1424] border border-slate-800 hover:border-emerald-500/50 rounded-3xl p-6 flex flex-col justify-between transition-all group relative overflow-hidden shadow-xl">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 font-mono font-black text-sm flex items-center justify-center border border-emerald-500/30">
                                    04
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                    Soporte & PVP
                                </span>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                                <i data-lucide="sparkles" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-black text-white group-hover:text-emerald-400 transition-colors">
                                4. Vende o Disfruta con Soporte
                            </h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Si adquieres tu franquicia de panel, define tus propios precios de venta (PVP) y retén tus ganancias directas. Todas tus compras y renovaciones cuentan con soporte directo por WhatsApp con <strong>{{ $brandName }}</strong>.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-800/80">
                            <span class="text-[11px] font-mono text-emerald-400 flex items-center gap-1.5 font-bold">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                Soporte Directo WhatsApp
                            </span>
                        </div>
                    </div>
                </div>
            @else
                {{-- CÓMO FUNCIONA PARA TIENDA DE REVENDEDOR BÁSICO (SOLO CUENTAS RETAIL) --}}
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="text-xs font-mono font-bold text-red-400 uppercase tracking-wider">Proceso Simple y Seguro</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white">¿Cómo Funciona tu Compra?</h2>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        Sistema automatizado para que disfrutes de tus plataformas favoritas con entrega al instante y máxima seguridad.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Paso 1 -->
                    <div class="bg-gradient-to-b from-[#111827] to-[#0d1424] border border-slate-800 hover:border-red-500/50 rounded-3xl p-6 flex flex-col justify-between transition-all group relative overflow-hidden shadow-xl">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-2xl bg-red-600/20 text-red-400 font-mono font-black text-sm flex items-center justify-center border border-red-500/30">
                                    01
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                    Catálogo
                                </span>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-red-500/10 text-red-400 flex items-center justify-center">
                                <i data-lucide="tv" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-black text-white group-hover:text-red-400 transition-colors">
                                1. Elige tu Plataforma Streaming
                            </h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Selecciona entre las mejores plataformas: Netflix 4K, Disney+ Premium, Max, Prime Video y más perfiles oficiales con entrega rápida y disponibilidad asegurada.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-800/80">
                            <span class="text-[11px] font-mono text-emerald-400 flex items-center gap-1.5 font-bold">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                Disponibilidad Inmediata
                            </span>
                        </div>
                    </div>

                    <!-- Paso 2 -->
                    <div class="bg-gradient-to-b from-[#111827] to-[#0d1424] border border-slate-800 hover:border-amber-500/50 rounded-3xl p-6 flex flex-col justify-between transition-all group relative overflow-hidden shadow-xl">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 font-mono font-black text-sm flex items-center justify-center border border-amber-500/30">
                                    02
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                    Pago Seguro
                                </span>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                                <i data-lucide="wallet" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-black text-white group-hover:text-amber-400 transition-colors">
                                2. Abona con Transferencia o Saldo
                            </h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Realiza tu pago mediante transferencia bancaria o saldo disponible. Tu comprobante es verificado de inmediato para liberar tus accesos al segundo.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-800/80">
                            <span class="text-[11px] font-mono text-amber-400 flex items-center gap-1.5 font-bold">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                Pago 100% Verificado
                            </span>
                        </div>
                    </div>

                    <!-- Paso 3 -->
                    <div class="bg-gradient-to-b from-[#111827] to-[#0d1424] border border-slate-800 hover:border-sky-500/50 rounded-3xl p-6 flex flex-col justify-between transition-all group relative overflow-hidden shadow-xl">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-2xl bg-sky-500/20 text-sky-400 font-mono font-black text-sm flex items-center justify-center border border-sky-500/30">
                                    03
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                    Bóveda
                                </span>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                                <i data-lucide="fingerprint" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-black text-white group-hover:text-sky-400 transition-colors">
                                3. Accede a tu Bóveda Privada
                            </h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Ingresa a tu Bóveda personal con tu número de WhatsApp y PIN de 4 dígitos o biometría para consultar tus credenciales y perfiles en cualquier momento.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-800/80">
                            <span class="text-[11px] font-mono text-sky-400 flex items-center gap-1.5 font-bold">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                Cero Fugas & Anti-Phishing
                            </span>
                        </div>
                    </div>

                    <!-- Paso 4 -->
                    <div class="bg-gradient-to-b from-[#111827] to-[#0d1424] border border-slate-800 hover:border-emerald-500/50 rounded-3xl p-6 flex flex-col justify-between transition-all group relative overflow-hidden shadow-xl">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 font-mono font-black text-sm flex items-center justify-center border border-emerald-500/30">
                                    04
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                    Garantía
                                </span>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                                <i data-lucide="shield-check" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-lg font-black text-white group-hover:text-emerald-400 transition-colors">
                                4. Disfruta con Garantía Total
                            </h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Tu servicio queda cubierto durante todo el tiempo contratado con atención de soporte y renovaciones transparentes vía WhatsApp con <strong>{{ $brandName }}</strong>.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-800/80">
                            <span class="text-[11px] font-mono text-emerald-400 flex items-center gap-1.5 font-bold">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                Garantía de Servicio
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        @else
            {{-- CÓMO FUNCIONA PARA LANDING CENTRAL SUPER ADMIN --}}
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <span class="text-xs font-mono font-bold text-red-400 uppercase tracking-wider">Flujo de Negocio Paso a Paso</span>
                <h2 class="text-3xl sm:text-4xl font-black text-white">¿Cómo Funciona el Ecosistema?</h2>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                    Un sistema automatizado y seguro diseñado para que mayoristas, proveedores y revendedores comiencen a operar y generar ingresos hoy mismo sin fricciones.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Paso 1 -->
                <div class="bg-gradient-to-b from-[#111827] to-[#0d1424] border border-slate-800 hover:border-red-500/50 rounded-3xl p-6 flex flex-col justify-between transition-all group relative overflow-hidden shadow-xl">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-2xl bg-red-600/20 text-red-400 font-mono font-black text-sm flex items-center justify-center border border-red-500/30">
                                01
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                Activación
                            </span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-red-500/10 text-red-400 flex items-center justify-center">
                            <i data-lucide="layers" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-lg font-black text-white group-hover:text-red-400 transition-colors">
                            1. Adquiere Tu Panel
                        </h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Elige tu <strong>Panel Básico (SPRO PLAY)</strong> de 10 servicios o el <strong>Panel PRO Ilimitado</strong> con Marca Blanca. Si eres revendedor, puedes actualizar a <strong>Panel Administrador (${{ number_format($seller->panel_monthly_fee ?? 15000, 0, ',', '.') }} / 30 días)</strong> para crear tu propia red de sub-revendedores.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800/80">
                        <span class="text-[11px] font-mono text-emerald-400 flex items-center gap-1.5 font-bold">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            Entrega Inmediata
                        </span>
                    </div>
                </div>

                <!-- Paso 2 -->
                <div class="bg-gradient-to-b from-[#111827] to-[#0d1424] border border-slate-800 hover:border-amber-500/50 rounded-3xl p-6 flex flex-col justify-between transition-all group relative overflow-hidden shadow-xl">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 font-mono font-black text-sm flex items-center justify-center border border-amber-500/30">
                                02
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                Saldo & PVP
                            </span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                            <i data-lucide="wallet" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-lg font-black text-white group-hover:text-amber-400 transition-colors">
                            2. Carga Saldo y Fija Margen
                        </h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Carga saldo en tu billetera con tu proveedor o administrador patrocinador. Define tus propios <strong>Precios de Venta al Público (PVP)</strong>. Cada venta deduce el costo mayorista y tu ganancia neta queda en tu bolsillo.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800/80">
                        <span class="text-[11px] font-mono text-amber-400 flex items-center gap-1.5 font-bold">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            Margen 100% Libre
                        </span>
                    </div>
                </div>

                <!-- Paso 3 -->
                <div class="bg-gradient-to-b from-[#111827] to-[#0d1424] border border-slate-800 hover:border-sky-500/50 rounded-3xl p-6 flex flex-col justify-between transition-all group relative overflow-hidden shadow-xl">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-2xl bg-sky-500/20 text-sky-400 font-mono font-black text-sm flex items-center justify-center border border-sky-500/30">
                                03
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                Bóveda
                            </span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                            <i data-lucide="fingerprint" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-lg font-black text-white group-hover:text-sky-400 transition-colors">
                            3. Entrega en Bóveda Cliente
                        </h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Tus clientes reciben su acceso privado a la <strong>Bóveda Cifrada</strong> con desbloqueo por <strong>Biometría o PIN de 4 dígitos</strong> (Ajusta en la primera compra su pin de Bóveda: este PIN sirve para consultar y revelar sus contraseñas en cualquier dispositivo o cuando no use biometría, debiendo recordarlo siempre). Sin saldo en tu billetera, el sistema protege las cuentas y no revela credenciales.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800/80">
                        <span class="text-[11px] font-mono text-sky-400 flex items-center gap-1.5 font-bold">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            Cero Fugas & Anti-Phishing
                        </span>
                    </div>
                </div>

                <!-- Paso 4 -->
                <div class="bg-gradient-to-b from-[#111827] to-[#0d1424] border border-slate-800 hover:border-purple-500/50 rounded-3xl p-6 flex flex-col justify-between transition-all group relative overflow-hidden shadow-xl">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-2xl bg-purple-500/20 text-purple-400 font-mono font-black text-sm flex items-center justify-center border border-purple-500/30">
                                04
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                Red & Comisión
                            </span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                            <i data-lucide="copy" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-lg font-black text-white group-hover:text-purple-400 transition-colors">
                            4. Clona y Gana 25% Pasivo
                        </h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            El proveedor o panel administrador clona su catálogo a sus revendedores en segundos. Cada vez que ellos vendan de tus servicios, <strong>se te acredita automáticamente el 25% de comisión neta</strong> en tu saldo.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800/80">
                        <span class="text-[11px] font-mono text-purple-400 flex items-center gap-1.5 font-bold">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            Comisión Automática 25%
                        </span>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <!-- ======================================================== -->
    <!-- SECCIÓN: PREGUNTAS FRECUENTES (FAQ) -->
    <!-- ======================================================== -->
    <section id="faq" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 scroll-mt-24">
        <div class="text-center space-y-3">
            <span class="text-xs font-mono font-bold text-amber-400 uppercase tracking-wider">Centro de Respuestas</span>
            <h2 class="text-3xl sm:text-4xl font-black text-white">Preguntas Frecuentes (FAQ)</h2>
            <p class="text-xs sm:text-sm text-slate-400">
                Todo lo que necesitas saber sobre el funcionamiento de los paneles, billeteras, bóveda de clientes y comisiones.
            </p>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="rounded-2xl border transition-all overflow-hidden"
                 :class="openFaq === 1 ? 'border-red-500/50 bg-[#131b2e]' : 'border-slate-800 bg-[#0f172a] hover:border-slate-700'">
                <button type="button" @click="toggleFaq(1)" class="w-full p-5 text-left flex items-center justify-between gap-4 cursor-pointer">
                    <span class="font-bold text-sm sm:text-base text-white flex items-center gap-3">
                        <i data-lucide="help-circle" class="w-5 h-5 text-red-400 shrink-0"></i>
                        @if(isset($seller) && $seller->isReseller() && $seller->canResellPanels() && $seller->isPanelSalesOffered())
                            ¿Puedo empezar a comprar cuentas o activar mi franquicia hoy mismo?
                        @elseif(isset($seller) && $seller->isReseller())
                            ¿Puedo comprar y recibir mi cuenta de streaming hoy mismo?
                        @else
                            ¿Puedo empezar a vender y trabajar hoy mismo?
                        @endif
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200"
                       :class="openFaq === 1 ? 'rotate-180 text-red-400' : ''"></i>
                </button>
                <div x-show="openFaq === 1" x-collapse class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-slate-800/80 leading-relaxed space-y-2">
                    <p>
                        <strong>¡Sí, totalmente!</strong> El catálogo cuenta con disponibilidad inmediata y perfiles precargados con PINs individuales (Netflix 4K UHD, Disney+ Estándar, Disney+ Premium VIP y Max Platino).
                    </p>
                    <p>
                        Al realizar tu abono por transferencia bancaria o con saldo disponible en billetera, la entrega de tus cuentas o la activación de tu panel se realiza al instante mediante la Bóveda Privada de Clientes con soporte directo.
                    </p>
                </div>
            </div>

            @if(isset($seller) && $seller->isReseller())
                @if($seller->canResellPanels() && $seller->isPanelSalesOffered())
                <!-- FAQ 2 PARA PROVEEDORES CON VENTA DE PANELES -->
                <div class="rounded-2xl border transition-all overflow-hidden"
                     :class="openFaq === 2 ? 'border-amber-500/50 bg-[#131b2e]' : 'border-slate-800 bg-[#0f172a] hover:border-slate-700'">
                    <button type="button" @click="toggleFaq(2)" class="w-full p-5 text-left flex items-center justify-between gap-4 cursor-pointer">
                        <span class="font-bold text-sm sm:text-base text-white flex items-center gap-3">
                            <i data-lucide="crown" class="w-5 h-5 text-amber-400 shrink-0"></i>
                            ¿Cómo funciona la adquisición de un Panel de Ventas con {{ $brandName }}?
                        </span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200"
                           :class="openFaq === 2 ? 'rotate-180 text-amber-400' : ''"></i>
                    </button>
                    <div x-show="openFaq === 2" x-collapse class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-slate-800/80 leading-relaxed space-y-2">
                        <p>
                            Al adquirir tu franquicia de <strong>Panel Básico (${{ number_format($seller->basic_panel_price, 0, ',', '.') }}/mes)</strong> o <strong>Panel PRO (${{ number_format($seller->pro_panel_price, 0, ',', '.') }}/mes)</strong>, obtienes tu propia tienda y panel de control para comercializar cuentas de streaming con catálogo sincronizado y Bóveda Privada para tus clientes.
                        </p>
                        <p>
                            La activación es gestionada y habilitada directamente por <strong>{{ $brandName }}</strong> tras verificar tu suscripción. Cuentas con herramientas para fijar tus propios márgenes de ganancia y brindar soporte directo a tus clientes por WhatsApp.
                        </p>
                    </div>
                </div>

                <!-- FAQ 3 PARA PROVEEDORES CON VENTA DE PANELES -->
                <div class="rounded-2xl border transition-all overflow-hidden"
                     :class="openFaq === 3 ? 'border-purple-500/50 bg-[#131b2e]' : 'border-slate-800 bg-[#0f172a] hover:border-slate-700'">
                    <button type="button" @click="toggleFaq(3)" class="w-full p-5 text-left flex items-center justify-between gap-4 cursor-pointer">
                        <span class="font-bold text-sm sm:text-base text-white flex items-center gap-3">
                            <i data-lucide="package" class="w-5 h-5 text-purple-400 shrink-0"></i>
                            ¿Qué diferencia hay entre el Panel Básico y el Panel PRO?
                        </span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200"
                           :class="openFaq === 3 ? 'rotate-180 text-purple-400' : ''"></i>
                    </button>
                    <div x-show="openFaq === 3" x-collapse class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-slate-800/80 leading-relaxed space-y-2">
                        <p>
                            <strong>📦 Panel Básico (${{ number_format($seller->basic_panel_price, 0, ',', '.') }} / mes):</strong> Diseñado para iniciar en la reventa de streaming. Permite comercializar los 10 servicios oficiales más demandados con entrega automatizada en Bóveda para tus clientes y soporte directo de {{ $brandName }}.
                        </p>
                        <p>
                            <strong>💎 Panel PRO (${{ number_format($seller->pro_panel_price, 0, ',', '.') }} / mes):</strong> Para negocios que buscan máxima expansión. Incluye servicios ilimitados, personalización de Marca Blanca (tu propio logo, colores y nombre comercial) y la facultad de afiliar sub-revendedores con comisiones sugeridas.
                        </p>
                        <p class="text-sky-300 text-xs pt-1 border-t border-slate-800">
                            ⚡ La activación de tu panel es habilitada directamente por tu proveedor <strong>{{ $brandName }}</strong>.
                        </p>
                    </div>
                </div>
                @else
                <!-- FAQ 2 PARA REVENDEDOR BÁSICO (SOLO CUENTAS RETAIL) -->
                <div class="rounded-2xl border transition-all overflow-hidden"
                     :class="openFaq === 2 ? 'border-amber-500/50 bg-[#131b2e]' : 'border-slate-800 bg-[#0f172a] hover:border-slate-700'">
                    <button type="button" @click="toggleFaq(2)" class="w-full p-5 text-left flex items-center justify-between gap-4 cursor-pointer">
                        <span class="font-bold text-sm sm:text-base text-white flex items-center gap-3">
                            <i data-lucide="shield-check" class="w-5 h-5 text-amber-400 shrink-0"></i>
                            ¿Cómo recibo mi cuenta o pantalla luego de abonar?
                        </span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200"
                           :class="openFaq === 2 ? 'rotate-180 text-amber-400' : ''"></i>
                    </button>
                    <div x-show="openFaq === 2" x-collapse class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-slate-800/80 leading-relaxed space-y-2">
                        <p>
                            La entrega se realiza de forma inmediata y automática en tu <strong>Bóveda Privada de Cliente</strong>. Al registrarte o realizar tu compra, se generan tus credenciales y perfiles cifrados a los que solo tú puedes acceder mediante tu número de WhatsApp y PIN personal.
                        </p>
                    </div>
                </div>

                <!-- FAQ 3 PARA REVENDEDOR BÁSICO (SOLO CUENTAS RETAIL) -->
                <div class="rounded-2xl border transition-all overflow-hidden"
                     :class="openFaq === 3 ? 'border-purple-500/50 bg-[#131b2e]' : 'border-slate-800 bg-[#0f172a] hover:border-slate-700'">
                    <button type="button" @click="toggleFaq(3)" class="w-full p-5 text-left flex items-center justify-between gap-4 cursor-pointer">
                        <span class="font-bold text-sm sm:text-base text-white flex items-center gap-3">
                            <i data-lucide="shield" class="w-5 h-5 text-purple-400 shrink-0"></i>
                            ¿Qué garantía tienen las cuentas de streaming?
                        </span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200"
                           :class="openFaq === 3 ? 'rotate-180 text-purple-400' : ''"></i>
                    </button>
                    <div x-show="openFaq === 3" x-collapse class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-slate-800/80 leading-relaxed space-y-2">
                        <p>
                            Todas las suscripciones y pantallas cuentan con <strong>garantía total</strong> durante todo el tiempo contratado. En caso de requerir asistencia técnica o renovación de tu servicio, te comunicas directamente con <strong>{{ $brandName }}</strong> por WhatsApp.
                        </p>
                    </div>
                </div>
                @endif
            @else
                <!-- FAQ 2 PARA SUPER ADMIN (VENTA MAYORISTA DE CRÉDITOS & TIENDAS CLONADAS) -->
                <div class="rounded-2xl border transition-all overflow-hidden"
                     :class="openFaq === 2 ? 'border-amber-500/50 bg-[#131b2e]' : 'border-slate-800 bg-[#0f172a] hover:border-slate-700'">
                    <button type="button" @click="toggleFaq(2)" class="w-full p-5 text-left flex items-center justify-between gap-4 cursor-pointer">
                        <span class="font-bold text-sm sm:text-base text-white flex items-center gap-3">
                            <i data-lucide="crown" class="w-5 h-5 text-amber-400 shrink-0"></i>
                            ¿Cómo funciona la compra del Panel Administrador ($15.000 / 30 días)?
                        </span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200"
                           :class="openFaq === 2 ? 'rotate-180 text-amber-400' : ''"></i>
                    </button>
                    <div x-show="openFaq === 2" x-collapse class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-slate-800/80 leading-relaxed space-y-2.5">
                        <p>
                            Cualquier revendedor puede evolucionar a <strong>Panel Administrador / Super Revendedor</strong> pagando $15.000 por 30 días. La compra se realiza con 1 solo clic utilizando el saldo de su billetera o reportando el comprobante de pago por WhatsApp a su patrocinador.
                        </p>
                        <p>
                            <strong>Tiendas Clonadas ($15.000/mes):</strong> El abono mensual se distribuye en <strong>$8.000 para el administrador</strong> de la plataforma (licencia de software y soporte de servidor) y <strong>$7.000 para el proveedor</strong> patrocinador.
                        </p>
                        <div class="p-3 rounded-xl bg-blue-500/10 border border-blue-500/30 text-xs text-blue-200">
                            <strong>⚡ Rol del Super Admin Central:</strong> Opera exclusivamente para la provisión de paneles y cobro de abonos mensuales de clonación. <strong>El Super Admin NO cobra comisiones ni ganancias sobre las cuentas o servicios de streaming de ningún proveedor</strong>. Asimismo, el panel central es 100% independiente para elegir a qué proveedores comprar o vender en sus servicios directos.
                        </div>
                        <p>
                            Al ser Proveedor Mayorista obtienes la facultad de afiliar a tus propios revendedores, clonarles tu catálogo fijando tus propios costos mayoristas, y <strong>percibir automáticamente el 25% de comisión neta</strong> por cada venta que concrete tu red afiliada.
                        </p>
                    </div>
                </div>

                <!-- FAQ 3 PARA SUPER ADMIN (VENTA MAYORISTA DE CRÉDITOS) -->
                <div class="rounded-2xl border transition-all overflow-hidden"
                     :class="openFaq === 3 ? 'border-purple-500/50 bg-[#131b2e]' : 'border-slate-800 bg-[#0f172a] hover:border-slate-700'">
                    <button type="button" @click="toggleFaq(3)" class="w-full p-5 text-left flex items-center justify-between gap-4 cursor-pointer">
                        <span class="font-bold text-sm sm:text-base text-white flex items-center gap-3">
                            <i data-lucide="package" class="w-5 h-5 text-purple-400 shrink-0"></i>
                            ¿Qué diferencia hay entre el Panel Básico y el Panel PRO Ilimitado?
                        </span>
                        <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200"
                           :class="openFaq === 3 ? 'rotate-180 text-purple-400' : ''"></i>
                    </button>
                    <div x-show="openFaq === 3" x-collapse class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-slate-800/80 leading-relaxed space-y-2">
                        <p>
                            <strong>📦 Panel Básico (10 Servicios - SPRO PLAY):</strong> Diseñado para revendedores iniciales. Incluye hasta 10 servicios oficiales bajo la marca y logo <strong>SPRO PLAY</strong> para venta directa a clientes finales. 
                            <strong class="text-amber-300 block mt-1">⚠️ El básico no revende ningún plan de panel ni afilia revendedores.</strong>
                            Su costo de activación es de <strong>1 Crédito Básico</strong> ($2.500 c/u en pack mínimo de 10 = $25.000).
                        </p>
                        <p>
                            <strong>💎 Panel PRO (Ilimitado - Marca Blanca):</strong> Para proveedores o revendedores consolidados. Ofrece servicios ilimitados, posibilidad de agregar plataformas propias y personalización <strong>100% Marca Blanca</strong> (logo, nombre comercial y colores propios).
                            <strong class="text-purple-300 block mt-1">✓ El PRO tiene acceso a la posibilidad de revender acceso a funciones de panel a sus propios revendedores.</strong>
                            Su costo de activación es de <strong>1 Crédito PRO</strong> ($4.000 c/u en pack mínimo de 10 = $40.000).
                        </p>
                        <p class="text-emerald-400 font-semibold pt-1">
                            🌟 <strong>Pack Combo Dúo (5 de cada uno juntos):</strong> 5 Créditos Básicos ($3.000 c/u = $15.000) + 5 Créditos PRO ($5.000 c/u = $25.000) por un total de <strong>$40.000 juntos</strong> en tu billetera.
                        </p>
                        <p class="text-sky-300 text-xs pt-1 border-t border-slate-800">
                            ⚡ <strong>Regla de Activación:</strong> Las solicitudes de panel quedan registradas en estado <em>Pendiente</em> y <strong>son activadas desde el panel de administrador exclusivamente consumiendo 1 crédito según el plan adquirido</strong> (Básico o PRO).
                        </p>
                    </div>
                </div>
            @endif

            <!-- FAQ 4 -->
            <div class="rounded-2xl border transition-all overflow-hidden"
                 :class="openFaq === 4 ? 'border-sky-500/50 bg-[#131b2e]' : 'border-slate-800 bg-[#0f172a] hover:border-slate-700'">
                <button type="button" @click="toggleFaq(4)" class="w-full p-5 text-left flex items-center justify-between gap-4 cursor-pointer">
                    <span class="font-bold text-sm sm:text-base text-white flex items-center gap-3">
                        <i data-lucide="fingerprint" class="w-5 h-5 text-sky-400 shrink-0"></i>
                        ¿Cómo funciona la Bóveda de Cliente con PIN y Biometría?
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200"
                       :class="openFaq === 4 ? 'rotate-180 text-sky-400' : ''"></i>
                </button>
                <div x-show="openFaq === 4" x-collapse class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-slate-800/80 leading-relaxed space-y-2">
                    <p>
                        Cada cliente final accede a su portal personal en <code class="text-sky-300 font-mono text-xs">/boveda/login</code> con su número de WhatsApp y contraseña.
                    </p>
                    <p>
                        <strong>🔑 Ajuste en la primera compra:</strong> Al realizar su primera compra o registro, el cliente define su <strong>PIN de Bóveda de 4 dígitos</strong>. Este PIN es su llave personal de seguridad que le servirá para revelar y consultar sus contraseñas, perfiles y pines en cualquier celular o PC cuando no utilice biometría (Face ID o Huella dactilar). <strong>Debe recordarlo siempre</strong> para consultar sus credenciales en el futuro.
                    </p>
                    <p>
                        <strong>🛡️ Cero Fugas & Anti-Phishing:</strong> Si el revendedor no tiene saldo suficiente en su billetera o si no se ha recibido el pago del proveedor, el sistema retiene las credenciales protegiendo las cuentas maestras de fugas o exposiciones no autorizadas.
                    </p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="rounded-2xl border transition-all overflow-hidden"
                 :class="openFaq === 5 ? 'border-emerald-500/50 bg-[#131b2e]' : 'border-slate-800 bg-[#0f172a] hover:border-slate-700'">
                <button type="button" @click="toggleFaq(5)" class="w-full p-5 text-left flex items-center justify-between gap-4 cursor-pointer">
                    <span class="font-bold text-sm sm:text-base text-white flex items-center gap-3">
                        <i data-lucide="credit-card" class="w-5 h-5 text-emerald-400 shrink-0"></i>
                        ¿Cómo se abonan y entregan las cuentas de streaming?
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200"
                       :class="openFaq === 5 ? 'rotate-180 text-emerald-400' : ''"></i>
                </button>
                <div x-show="openFaq === 5" x-collapse class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-slate-800/80 leading-relaxed space-y-2">
                    <p>
                        Para las cuentas se deberá abonar por <strong>transferencia bancaria</strong> y/o con <strong>dinero disponible en la billetera</strong> (tanto para proveedor como revendedor).
                    </p>
                    <p>
                        ⚡ <strong>Revendedor con saldo en billetera:</strong> La entrega es <strong>100% automática e inmediata</strong>. Las credenciales y PINs se asignan al instante en la Bóveda Privada del cliente.
                    </p>
                    <p>
                        🏦 <strong>Abono por transferencia bancaria:</strong> La transferencia será aceptada y acreditada por el proveedor o administración para liberar y entregar la(s) cuenta(s).
                    </p>
                    <p class="text-rose-400 font-semibold pt-1 border-t border-slate-800/80">
                        🚫 <strong>Consignación de gracia (NO ES UNA OPCIÓN):</strong> No existen plazos de 48hs ni cuentas fiadas. Todo el stock se entrega protegido con saldo disponible o transferencia aceptada.
                    </p>
                </div>
            </div>

            <!-- FAQ 6 -->
            <div class="rounded-2xl border transition-all overflow-hidden"
                 :class="openFaq === 6 ? 'border-rose-500/50 bg-[#131b2e]' : 'border-slate-800 bg-[#0f172a] hover:border-slate-700'">
                <button type="button" @click="toggleFaq(6)" class="w-full p-5 text-left flex items-center justify-between gap-4 cursor-pointer">
                    <span class="font-bold text-sm sm:text-base text-white flex items-center gap-3">
                        <i data-lucide="shield-alert" class="w-5 h-5 text-rose-400 shrink-0"></i>
                        ¿Por qué no se entregan cuentas sin saldo en billetera o transferencia aceptada?
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200"
                       :class="openFaq === 6 ? 'rotate-180 text-rose-400' : ''"></i>
                </button>
                <div x-show="openFaq === 6" x-collapse class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-slate-800/80 leading-relaxed space-y-2">
                    <p>
                        Por seguridad financiera y protección del stock: <strong>ninguna cuenta se entrega o revela sin el costo mayorista cubierto</strong>.
                    </p>
                    <p>
                        El revendedor con saldo en billetera disfruta de entrega automática al segundo. Si opta por abonar por transferencia, la cuenta permanece retenida en la Bóveda hasta que el proveedor la acepte. Esto elimina deudas incobrables y garantiza un modelo 100% seguro y rentable.
                    </p>
                </div>
            </div>

            <!-- FAQ 7 -->
            <div class="rounded-2xl border transition-all overflow-hidden"
                 :class="openFaq === 7 ? 'border-teal-500/50 bg-[#131b2e]' : 'border-slate-800 bg-[#0f172a] hover:border-slate-700'">
                <button type="button" @click="toggleFaq(7)" class="w-full p-5 text-left flex items-center justify-between gap-4 cursor-pointer">
                    <span class="font-bold text-sm sm:text-base text-white flex items-center gap-3">
                        <i data-lucide="message-square" class="w-5 h-5 text-teal-400 shrink-0"></i>
                        ¿A qué número de WhatsApp van las consultas y renovaciones?
                    </span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 shrink-0 transition-transform duration-200"
                       :class="openFaq === 7 ? 'rotate-180 text-teal-400' : ''"></i>
                </button>
                <div x-show="openFaq === 7" x-collapse class="px-5 pb-5 pt-1 text-xs sm:text-sm text-slate-300 border-t border-slate-800/80 leading-relaxed space-y-2">
                    @if(isset($seller) && $seller->isReseller())
                    <p>
                        Las consultas, solicitudes de soporte y renovaciones van directamente al WhatsApp de tu vendedor <strong>{{ $brandName }}</strong> (@if($supportWhatsapp)<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsapp) }}" target="_blank" class="text-teal-400 underline font-mono">{{ $supportWhatsapp }}</a>@endif).
                    </p>
                    <p class="text-teal-300 font-semibold pt-1 border-t border-slate-800/80">
                        📱 Todo el proceso también se gestiona de forma interactiva desde la Bóveda del Cliente, recibiendo recordatorios antes de tu fecha de expiración para renovar en un solo clic.
                    </p>
                    @else
                    <p>
                        Cada panelista (proveedor o revendedor) configura su número de WhatsApp en la sección de Preferencias de su panel. En caso de ser una tienda clonada, las solicitudes de soporte y renovaciones de los clientes de la Bóveda llegan directamente al revendedor, o si éste lo prefiere, al WhatsApp de su proveedor patrocinador para que le brinde soporte directo.
                    </p>
                    <p class="text-teal-300 font-semibold pt-1 border-t border-slate-800/80">
                        📱 Todo el proceso también se gestiona por la Bóveda del Cliente y le llega directamente a su panel. El mismo revendedor deberá solicitar revisión al proveedor desde su panel si requiere soporte o autorización directa, quedando registros y mensajes de auditoría entre ambos.
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- BOTÓN FLOTANTE DEL CARRITO DE COMPRAS -->
    <!-- ======================================================== -->
    <div class="fixed bottom-6 right-6 z-40" x-cloak x-show="totalItems > 0"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-90"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-90">
        <button type="button" @click="openCartDrawer = true" 
                class="group relative flex items-center gap-3 px-5 py-3.5 rounded-full bg-slate-900 border-2 text-white shadow-2xl hover:scale-105 transition-all cursor-pointer backdrop-blur-md"
                :class="isComboApplied ? 'border-emerald-500 shadow-emerald-500/30' : 'border-amber-500 shadow-amber-500/30'">
            <!-- Badge contador -->
            <div class="relative">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-slate-950"
                     :class="isComboApplied ? 'bg-emerald-400' : 'bg-amber-400'">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                </div>
                <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-600 text-white font-mono font-black text-[10px] flex items-center justify-center border-2 border-slate-900"
                      x-text="totalItems"></span>
            </div>

            <!-- Texto de carrito y total -->
            <div class="text-left">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-white uppercase tracking-wider">Ver Carrito</span>
                    <span x-show="isComboApplied" class="px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-400 font-mono text-[10px] font-bold border border-emerald-500/30">
                        🔥 COMBO
                    </span>
                </div>
                <div class="flex items-baseline gap-1 font-mono">
                    <span class="text-sm font-black" :class="isComboApplied ? 'text-emerald-400' : 'text-amber-400'" x-text="formatMoney(finalTotal)"></span>
                    <span x-show="isComboApplied" class="text-[10px] text-slate-400 line-through" x-text="formatMoney(subtotal)"></span>
                </div>
            </div>

            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition-transform"></i>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL / DRAWER DESLIZABLE DEL CARRITO DE COMPRAS -->
    <!-- ======================================================== -->
    <div x-cloak x-show="openCartDrawer" class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <!-- Backdrop oscuro -->
        <div x-show="openCartDrawer"
             x-transition:enter="ease-in-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="openCartDrawer = false"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="openCartDrawer"
                         x-transition:enter="transform transition ease-in-out duration-300 sm:duration-400"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transform transition ease-in-out duration-300 sm:duration-400"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full"
                         class="pointer-events-auto w-screen max-w-md">
                        <div class="flex h-full flex-col bg-slate-900 border-l border-slate-800 shadow-2xl">
                            
                            <!-- Header Carrito -->
                            <div class="p-6 bg-slate-950 border-b border-slate-800 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold">
                                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-black text-white">Tu Carrito de Cuentas</h2>
                                        <span class="text-xs text-slate-400">
                                            <span x-text="totalItems"></span> <span x-text="totalItems === 1 ? 'cuenta seleccionada' : 'cuentas seleccionadas'"></span>
                                        </span>
                                    </div>
                                </div>
                                <button type="button" @click="openCartDrawer = false" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <!-- Banner Combo Dentro del Carrito -->
                            <div class="p-4 border-b border-slate-800"
                                 :class="isComboApplied ? 'bg-emerald-950/40 border-emerald-500/30' : 'bg-slate-950/60'">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-bold" :class="isComboApplied ? 'text-emerald-400' : 'text-amber-400'">
                                        <span x-show="!isComboApplied">💡 Agrega <span x-text="itemsNeededForCombo"></span> cuenta(s) más para activar Combo.</span>
                                        <span x-show="isComboApplied" x-cloak>🎉 ¡Descuento Combo Aplicado!</span>
                                    </span>
                                    <span x-show="isComboApplied" class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-mono text-[10px] font-bold border border-emerald-500/40">
                                        -<span x-text="formatMoney(comboDiscount)"></span> OFF
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Combina plataformas diferentes (Netflix, Max, Disney) y ahorras automáticamente en tu pedido.
                                </p>
                            </div>

                            <!-- Lista de Items en Carrito -->
                            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                                <template x-if="cart.length === 0">
                                    <div class="py-16 text-center space-y-3">
                                        <div class="w-14 h-14 rounded-full bg-slate-800/80 text-slate-500 flex items-center justify-center mx-auto">
                                            <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-400">Tu carrito está vacío</p>
                                        <p class="text-xs text-slate-500">Agrega servicios de streaming del catálogo para armar tu pedido o combo.</p>
                                    </div>
                                </template>

                                <template x-for="item in cart" :key="item.planId">
                                    <div class="p-4 rounded-2xl bg-[#0b0f19] border border-slate-800 flex items-center justify-between gap-3 shadow-md hover:border-slate-700 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white text-xs shrink-0"
                                                 :style="'background-color: ' + (item.color || '#E50914')">
                                                <span x-text="item.serviceName ? item.serviceName.substring(0, 2) : 'ST'"></span>
                                            </div>
                                            <div>
                                                <strong class="text-xs font-bold text-white block" x-text="item.serviceName"></strong>
                                                <span class="text-[11px] text-slate-400 block line-clamp-1" x-text="item.planName"></span>
                                                <span class="text-xs font-black text-emerald-400 font-mono mt-0.5 block" x-text="formatMoney(item.price)"></span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <!-- Selector de cantidad -->
                                            <div class="flex items-center gap-1 bg-slate-900 border border-slate-700 rounded-xl p-0.5">
                                                <button type="button" @click="updateQty(item.planId, -1)" class="w-6 h-6 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold flex items-center justify-center text-xs cursor-pointer">-</button>
                                                <span class="px-2 text-xs font-mono font-bold text-white" x-text="item.qty"></span>
                                                <button type="button" @click="updateQty(item.planId, 1)" class="w-6 h-6 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold flex items-center justify-center text-xs cursor-pointer">+</button>
                                            </div>

                                            <!-- Botón Eliminar -->
                                            <button type="button" @click="removeFromCart(item.planId)" class="p-1.5 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-colors cursor-pointer" title="Quitar">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Footer Resumen y Botón de Pago -->
                            <div class="p-6 bg-slate-950 border-t border-slate-800 space-y-4" x-show="cart.length > 0">
                                <!-- Desglose -->
                                <div class="space-y-2 text-xs font-mono">
                                    <div class="flex justify-between text-slate-400">
                                        <span>Subtotal (<span x-text="totalItems"></span> cuentas):</span>
                                        <span class="text-white" x-text="formatMoney(subtotal)"></span>
                                    </div>
                                    <div x-show="isComboApplied" class="flex justify-between text-emerald-400 font-bold">
                                        <span>🔥 Descuento Combo:</span>
                                        <span>-<span x-text="formatMoney(comboDiscount)"></span></span>
                                    </div>
                                    <div class="pt-2 border-t border-slate-800 flex justify-between items-baseline text-sm">
                                        <span class="font-bold text-white font-sans text-xs uppercase tracking-wider">Total a Pagar:</span>
                                        <span class="text-xl font-black text-emerald-400" x-text="formatMoney(finalTotal)"></span>
                                    </div>
                                </div>

                                <!-- Botón Comprar por WhatsApp -->
                                @if($supportWhatsapp)
                                <a :href="whatsappCheckoutUrl" target="_blank"
                                   class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:opacity-95 text-white font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-xl shadow-emerald-600/30 transition-all cursor-pointer">
                                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                                    <span>Pedir Combo por WhatsApp (<span x-text="formatMoney(finalTotal)"></span>)</span>
                                </a>
                                @endif

                                <div class="flex items-center justify-between pt-1">
                                    <button type="button" @click="clearCart()" class="text-[11px] text-slate-500 hover:text-red-400 font-mono transition-colors cursor-pointer">
                                        Vaciar carrito
                                    </button>
                                    <button type="button" @click="openCartDrawer = false" class="text-[11px] text-sky-400 hover:underline font-mono transition-colors cursor-pointer">
                                        + Agregar más cuentas
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function catalogApp() {
    return {
        openFaq: null,
        openCartDrawer: false,
        cart: [],
        comboConfig: {
            enabled: {{ $seller && $seller->isComboDiscountActive() ? 'true' : 'false' }},
            minItems: {{ $seller ? $seller->getComboMinItems() : 2 }},
            type: '{{ $seller ? $seller->getComboDiscountType() : 'fixed' }}',
            amount: {{ $seller ? (float)$seller->getComboDiscountAmount() : 1000.0 }},
            sellerWhatsapp: '{{ preg_replace('/[^0-9]/', '', $supportWhatsapp ?? '') }}',
            brandName: '{{ addslashes($brandName ?? 'CStreame') }}'
        },

        init() {
            try {
                const saved = localStorage.getItem('cstreame_cart');
                if (saved) {
                    this.cart = JSON.parse(saved);
                }
            } catch (e) {
                this.cart = [];
            }
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },

        toggleFaq(id) {
            this.openFaq = this.openFaq === id ? null : id;
        },

        saveCart() {
            try {
                localStorage.setItem('cstreame_cart', JSON.stringify(this.cart));
            } catch (e) {}
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },

        addToCart(plan, service) {
            const existing = this.cart.find(i => i.planId === plan.id);
            if (existing) {
                existing.qty++;
            } else {
                this.cart.push({
                    planId: plan.id,
                    serviceId: service.id,
                    serviceName: service.name,
                    planName: plan.name,
                    price: parseFloat(plan.price),
                    color: service.color_hex || '#E50914',
                    duration: plan.duration_days,
                    modality: plan.modality,
                    qty: 1
                });
            }
            this.saveCart();
            this.openCartDrawer = true;
        },

        getItemQty(planId) {
            const item = this.cart.find(i => i.planId === planId);
            return item ? item.qty : 0;
        },

        updateQty(planId, delta) {
            const index = this.cart.findIndex(i => i.planId === planId);
            if (index !== -1) {
                this.cart[index].qty += delta;
                if (this.cart[index].qty <= 0) {
                    this.cart.splice(index, 1);
                }
                this.saveCart();
            }
        },

        removeFromCart(planId) {
            this.cart = this.cart.filter(i => i.planId !== planId);
            this.saveCart();
        },

        clearCart() {
            this.cart = [];
            this.saveCart();
        },

        get totalItems() {
            return this.cart.reduce((sum, item) => sum + item.qty, 0);
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },

        get isComboApplied() {
            return this.comboConfig.enabled && this.totalItems >= this.comboConfig.minItems;
        },

        get comboDiscount() {
            if (!this.isComboApplied) return 0;
            if (this.comboConfig.type === 'percent') {
                return Math.round((this.subtotal * (this.comboConfig.amount / 100)) * 100) / 100;
            }
            return Math.min(this.subtotal, this.comboConfig.amount);
        },

        get finalTotal() {
            return Math.max(0, this.subtotal - this.comboDiscount);
        },

        get itemsNeededForCombo() {
            return Math.max(0, this.comboConfig.minItems - this.totalItems);
        },

        formatMoney(val) {
            return '$' + new Intl.NumberFormat('es-AR', { maximumFractionDigits: 0 }).format(val);
        },

        get whatsappCheckoutUrl() {
            if (!this.comboConfig.sellerWhatsapp) return '#';
            let lines = [];
            lines.push(`Hola *${this.comboConfig.brandName}*! Quiero comprar el siguiente pedido:`);
            lines.push('');
            lines.push('🛒 *DETALLE DE MI PEDIDO:*');
            this.cart.forEach(item => {
                lines.push(`• ${item.qty}x *${item.serviceName}* (${item.planName}) - ${this.formatMoney(item.price * item.qty)}`);
            });
            lines.push('');
            lines.push(`Subtotal: ${this.formatMoney(this.subtotal)}`);
            if (this.isComboApplied) {
                lines.push(`🔥 *Descuento Combo (${this.comboConfig.minItems}+ cuentas): -${this.formatMoney(this.comboDiscount)}*`);
            }
            lines.push(`💰 *TOTAL A PAGAR: ${this.formatMoney(this.finalTotal)}*`);
            lines.push('');
            lines.push('¿Me confirmas los datos de pago para la activación en mi Bóveda?');
            return 'https://wa.me/' + this.comboConfig.sellerWhatsapp + '?text=' + encodeURIComponent(lines.join('\n'));
        }
    };
}
</script>
@endsection
