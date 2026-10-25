@extends('layouts.app')

@section('title', 'Preferencias de Panel')
@section('page_title', 'Preferencias de Panel & Marca')
@section('page_subtitle', 'Configura la atención por WhatsApp en la Bóveda de Clientes, renovaciones e identidad de marca')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Formulario Principal de Preferencias -->
    <form method="POST" action="{{ route('whitelabel.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- ESTADO Y NIVEL DE PANEL (BÁSICO SPRO PLAY vs PRO MARCA BLANCA) -->
        @if(Auth::user()->isReseller())
            @if(Auth::user()->isPanelBasic())
            <div class="p-5 rounded-3xl bg-gradient-to-r from-amber-950/40 via-slate-900 to-slate-900 border border-amber-500/40 text-xs space-y-3 shadow-xl">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500 text-slate-950 uppercase tracking-wider">
                            📦 MARCA OFICIAL SPRO PLAY (PANEL BÁSICO)
                        </span>
                        <span class="text-amber-400 font-bold">10 Servicios Máximo</span>
                    </div>
                    <span class="text-[10px] font-mono text-gray-400">Marca Blanca Bloqueada</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl bg-[#0b0f19] border border-gray-800">
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('images/spro-play-logo.svg') }}" alt="SPRO PLAY" class="h-10 w-auto object-contain">
                        <div>
                            <h4 class="font-bold text-white text-sm">SPRO PLAY Oficial</h4>
                            <p class="text-[11px] text-gray-400">Tu tienda y catálogo operan bajo la marca oficial SPRO PLAY. Puedes configurar libremente tus números y preferencias de soporte por WhatsApp para la Bóveda de tus clientes.</p>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <span class="px-3 py-1.5 rounded-xl bg-purple-500/10 border border-purple-500/30 text-purple-300 font-bold text-[11px] inline-flex items-center gap-1">
                            <i data-lucide="gem" class="w-3.5 h-3.5 text-purple-400"></i>
                            <span>Marca Blanca en PRO</span>
                        </span>
                    </div>
                </div>

                <p class="text-[11px] text-slate-400 leading-relaxed">
                    ℹ️ <strong>Función de Marca Blanca:</strong> Para utilizar tu propio nombre de marca, subir tu logo personalizado y elegir tus colores corporativos, solicita la actualización a <strong>Panel PRO (Ilimitado)</strong>.
                </p>
            </div>
            @else
            <div class="p-5 rounded-3xl bg-gradient-to-r from-purple-950/30 via-slate-900 to-slate-900 border border-purple-500/30 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-purple-500/20 text-purple-400 border border-purple-500/30 flex items-center justify-center shrink-0">
                        <i data-lucide="gem" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-white text-sm">💎 PANEL PRO ILIMITADO (Marca Blanca Habilitada)</h4>
                        <p class="text-[11px] text-purple-300">Tienes acceso a personalización total (logo propio, colores y nombre) y la posibilidad de revender acceso a paneles a tus sub-revendedores.</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] font-black bg-purple-500 text-white uppercase tracking-wider shrink-0 w-max">
                    PRO TOTAL
                </span>
            </div>
            @endif
        @else
            <div class="p-4 rounded-3xl bg-red-950/30 border border-red-500/30 text-xs flex items-center justify-between gap-3 shadow-xl">
                <div class="flex items-center gap-3">
                    <i data-lucide="shield-check" class="w-5 h-5 text-red-400 shrink-0"></i>
                    <span class="text-white font-medium">Panel Administrador Master · Control Global de Marca y Preferencias</span>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-red-600 text-white uppercase tracking-wider">
                    MASTER
                </span>
            </div>
        @endif

        <!-- ======================================================== -->
        <!-- SECCIÓN 1: SOPORTE & RENOVACIONES POR WHATSAPP EN BÓVEDA -->
        <!-- ======================================================== -->
        <div class="bg-gray-900/80 border border-gray-800 rounded-3xl p-6 md:p-8 shadow-xl space-y-6">
            <div class="flex items-center gap-3 pb-4 border-b border-gray-800">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0">
                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-white">Soporte & Renovaciones por WhatsApp en Bóveda Clientes</h3>
                    <p class="text-xs text-gray-400">Configura tu número de contacto y el destino donde llegarán las consultas y renovaciones de tus clientes.</p>
                </div>
            </div>

            <!-- Canales de Contacto Directo -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- WhatsApp Support Input -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">
                        WhatsApp de Atención al Cliente (con código de país) *
                    </label>
                    <div class="relative">
                        <input type="text" name="support_whatsapp" value="{{ old('support_whatsapp', $user->support_whatsapp) }}" placeholder="Ej: 5491122334455"
                            class="w-full pl-10 pr-4 py-3 bg-gray-950 border border-gray-800 rounded-xl text-white text-sm focus:ring-2 focus:ring-emerald-500">
                        <i data-lucide="phone" class="w-4 h-4 text-emerald-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                    </div>
                    <span class="text-[11px] text-gray-500 mt-1 block">Ingresa tu número completo sin espacios ni signos +.</span>
                </div>

                <!-- Telegram Support Input -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">
                        Canal o Usuario de Telegram (Opcional)
                    </label>
                    <div class="relative">
                        <input type="text" name="support_telegram" value="{{ old('support_telegram', $user->support_telegram) }}" placeholder="Ej: mitienda_soporte"
                            class="w-full pl-10 pr-4 py-3 bg-gray-950 border border-gray-800 rounded-xl text-white text-sm focus:ring-2 focus:ring-emerald-500">
                        <i data-lucide="send" class="w-4 h-4 text-sky-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                    </div>
                    <span class="text-[11px] text-gray-500 mt-1 block">Opcional para canal o chat de soporte en Telegram.</span>
                </div>
            </div>

            <!-- EXPLICACIÓN Y DESTINO DIRECTO DE WHATSAPP: REVENDEDOR PARA CLIENTE -->
            <div class="pt-4 border-t border-gray-800 space-y-4">
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span>Regla de Canales de WhatsApp Oficiales</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-mono">Soporte Directo</span>
                    </label>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        En la plataforma, <strong class="text-white">el WhatsApp de cada revendedor es exclusivo para sus clientes finales</strong>. Tus clientes te escribirán a ti directamente en su Bóveda Privada y Catálogo.
                    </p>
                </div>

                <!-- Tarjetas Informativas de los 3 Niveles de Soporte -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <!-- Nivel 1: Revendedor para Cliente -->
                    <div class="p-3.5 rounded-2xl bg-emerald-950/20 border border-emerald-500/40 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-400 flex items-center gap-1.5">
                                <i data-lucide="users" class="w-4 h-4"></i>
                                <span>Tú ➔ Tus Clientes</span>
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono bg-emerald-500/30 text-emerald-300">ACTIVO</span>
                        </div>
                        <p class="text-[11px] text-gray-300">
                            Tus clientes ven tu WhatsApp configurado arriba en su Bóveda y Catálogo para atención y renovaciones.
                        </p>
                        <div class="text-[11px] font-mono font-bold text-white pt-1">
                            {{ $user->support_whatsapp ?: '⚠️ Falta configurar arriba' }}
                        </div>
                    </div>

                    <!-- Nivel 2: Proveedor para Revendedor -->
                    <div class="p-3.5 rounded-2xl bg-teal-950/20 border border-teal-500/30 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-teal-400 flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                                <span>Tu Proveedor ➔ Tú</span>
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono bg-teal-500/20 text-teal-300">MAYORISTA</span>
                        </div>
                        <p class="text-[11px] text-gray-400">
                            Canal exclusivo para que tú recargues saldo, compres paneles y pidas soporte a tu patrocinador.
                        </p>
                        <div class="text-[11px] font-mono font-bold text-teal-300 pt-1">
                            @if($user->parent)
                                {{ $user->parent->name }} · {{ $user->parent->support_whatsapp ?: 'Sin número' }}
                            @else
                                Panel Central (Directo)
                            @endif
                        </div>
                    </div>

                    <!-- Nivel 3: Administrador para Panel Propio -->
                    <div class="p-3.5 rounded-2xl bg-purple-950/20 border border-purple-500/30 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-purple-400 flex items-center gap-1.5">
                                <i data-lucide="cpu" class="w-4 h-4"></i>
                                <span>Admin ➔ Panel Propio</span>
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono bg-purple-500/20 text-purple-300">SISTEMA</span>
                        </div>
                        <p class="text-[11px] text-gray-400">
                            El WhatsApp del Administrador es solo para soporte técnico del sistema y convenios administrativos.
                        </p>
                        <div class="text-[11px] font-mono text-purple-300 pt-1">
                            Soporte Central de Software
                        </div>
                    </div>
                </div>

                <!-- Input oculto para compatibilidad con backend -->
                <input type="hidden" name="whatsapp_support_preference" value="reseller">
            </div>

            <!-- Indicador en Vivo del Destino Resuelto en Bóveda -->
            <div class="p-4 rounded-2xl bg-[#0b0f19] border border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <span class="inline-block w-2.5 h-2.5 rounded-full {{ $user->support_whatsapp ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400' }}"></span>
                    <div>
                        <span class="text-gray-400 block text-[11px]">Número Efectivo en la Bóveda y Catálogo de tus Clientes:</span>
                        <strong class="text-white font-mono text-sm">
                            {{ $user->support_whatsapp ?: '⚠️ Sin número (Ingresa tu WhatsApp arriba para activar la atención directa)' }}
                        </strong>
                    </div>
                </div>

                <div>
                    <span class="px-3 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-bold text-[11px] inline-flex items-center gap-1.5">
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        <span>Atención Directa: Revendedor ➔ Cliente</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- SECCIÓN 2: IDENTIDAD VISUAL DE MARCA BLANCA -->
        <!-- ======================================================== -->
        <div class="bg-gray-900/80 border border-gray-800 rounded-3xl p-6 md:p-8 shadow-xl space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center shrink-0">
                        <i data-lucide="palette" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white">Identidad de Marca & Personalización Visual</h3>
                        <p class="text-xs text-gray-400">Personaliza el nombre, logo, colores y fondo de login de tu tienda digital.</p>
                    </div>
                </div>

                @if(Auth::user()->isReseller() && Auth::user()->isPanelBasic())
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    Bloqueado en Básico
                </span>
                @else
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                    Marca Blanca Activa
                </span>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Brand Name -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">
                        Nombre de tu Marca / Empresa *
                    </label>
                    @if(Auth::user()->isReseller() && Auth::user()->isPanelBasic())
                    <input type="text" name="brand_name" value="SPRO PLAY" readonly
                        class="w-full px-4 py-3 bg-gray-950 border border-amber-500/40 rounded-xl text-amber-300 font-bold text-sm cursor-not-allowed">
                    <span class="text-[10px] text-amber-400 mt-1 block">Fijado como SPRO PLAY en Panel Básico. Habilitado en PRO.</span>
                    @else
                    <input type="text" name="brand_name" value="{{ old('brand_name', $user->attributes['brand_name'] ?? 'StreamPro Master') }}" required
                        class="w-full px-4 py-3 bg-gray-950 border border-gray-800 rounded-xl text-white text-sm focus:ring-2 focus:ring-indigo-500">
                    @endif
                </div>

                <!-- Theme Colors -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">
                            Color Primario
                        </label>
                        @if(Auth::user()->isReseller() && Auth::user()->isPanelBasic())
                        <div class="flex items-center gap-2 p-2 rounded-xl bg-gray-950 border border-gray-800">
                            <span class="w-6 h-6 rounded-lg bg-red-600 block shrink-0"></span>
                            <span class="text-xs font-mono text-gray-400">#E50914 (Oficial)</span>
                        </div>
                        @else
                        <div class="flex items-center gap-2">
                            <input type="color" name="brand_primary_color" value="{{ $user->brand_primary_color ?: '#6366f1' }}"
                                class="w-10 h-10 rounded-lg cursor-pointer bg-transparent border-0">
                            <span class="text-xs font-mono text-gray-400">{{ $user->brand_primary_color ?: '#6366f1' }}</span>
                        </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">
                            Color Acento
                        </label>
                        @if(Auth::user()->isReseller() && Auth::user()->isPanelBasic())
                        <div class="flex items-center gap-2 p-2 rounded-xl bg-gray-950 border border-gray-800">
                            <span class="w-6 h-6 rounded-lg bg-amber-500 block shrink-0"></span>
                            <span class="text-xs font-mono text-gray-400">#F59E0B (Oficial)</span>
                        </div>
                        @else
                        <div class="flex items-center gap-2">
                            <input type="color" name="brand_accent_color" value="{{ $user->brand_accent_color ?: '#06b6d4' }}"
                                class="w-10 h-10 rounded-lg cursor-pointer bg-transparent border-0">
                            <span class="text-xs font-mono text-gray-400">{{ $user->brand_accent_color ?: '#06b6d4' }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Uploads: Logo & Login Wallpaper -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-800">
                <!-- Logo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">
                        Logo Personalizado (PNG transparente)
                    </label>
                    @if(Auth::user()->isReseller() && Auth::user()->isPanelBasic())
                    <div class="p-3 rounded-xl bg-gray-950 border border-gray-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/spro-play-logo.svg') }}" class="h-8 max-w-[120px] object-contain">
                            <span class="text-xs text-amber-400 font-bold">Logo Oficial SPRO PLAY</span>
                        </div>
                        <span class="text-[10px] text-gray-500 font-mono">Predeterminado en Básico</span>
                    </div>
                    @else
                    <input type="file" name="brand_logo" accept="image/*"
                        class="w-full px-3 py-2 bg-gray-950 border border-gray-800 rounded-xl text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white cursor-pointer">
                    @if($user->brand_logo)
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-[11px] text-gray-400">Logo actual:</span>
                        <img src="{{ asset('storage/' . $user->brand_logo) }}" class="h-8 max-w-[120px] object-contain rounded bg-gray-950 p-1 border border-gray-800">
                    </div>
                    @endif
                    @endif
                </div>

                <!-- Wallpaper Login -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2">
                        Fondo de Pantalla de Login (Wallpaper)
                    </label>
                    @if(Auth::user()->isReseller() && Auth::user()->isPanelBasic())
                    <div class="p-3 rounded-xl bg-gray-950 border border-gray-800 text-xs text-gray-400">
                        <span>Fondo cinematográfico predeterminado oficial de SPRO PLAY.</span>
                    </div>
                    @else
                    <input type="file" name="custom_login_bg" accept="image/*"
                        class="w-full px-3 py-2 bg-gray-950 border border-gray-800 rounded-xl text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-600 file:text-white cursor-pointer">
                    <p class="text-[11px] text-gray-500 mt-1">Recomendado: 1920x1080 px en formato JPG o WebP.</p>
                    @endif
                </div>
            </div>

            @if($user->canResellPanels())
            <!-- SECCIÓN 3: PRECIOS DE REVENTA DE PANELES EN TU LANDING PAGE -->
            <div class="pt-6 border-t border-gray-800 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center shrink-0">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white">
                            {{ $user->isAdmin() ? 'Opciones de Panel & Precio Sugerido al Público (Usuarios Finales)' : 'Reventa de Paneles en Tu Landing Page / Catálogo' }}
                        </h3>
                        <p class="text-xs text-gray-400">
                            {{ $user->isAdmin() ? 'Configura el precio sugerido oficial para Panel Básico y PRO en el catálogo público y como base para la red de revendedores.' : 'Configura tus propios precios de venta para ofrecer franquicias de paneles streaming a tus clientes y revendedores.' }}
                        </p>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="offer_panels_on_catalog" value="1" {{ old('offer_panels_on_catalog', $user->offer_panels_on_catalog ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-red-600 focus:ring-0 bg-slate-900 border-gray-700">
                        <div>
                            <span class="text-xs font-bold text-white block">
                                {{ $user->isAdmin() ? 'Activar y mostrar opciones de Panel en el Catálogo Público / Landing' : 'Ofrecer venta de Paneles en mi Catálogo Público / Landing' }}
                            </span>
                            <span class="text-[11px] text-gray-400">
                                {{ $user->isAdmin() ? 'Permite que los usuarios finales y visitantes puedan adquirir Panel Básico o PRO al precio sugerido.' : 'Permite que quienes visiten tu link de referido puedan adquirir un Panel Básico o PRO a tu precio definido.' }}
                            </span>
                        </div>
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-slate-800/80">
                        <div>
                            <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">
                                Precio de Venta: Panel BÁSICO (10 Servicios) ($)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-sm">$</span>
                                <input type="number" step="0.01" min="0" name="panel_basic_sale_price" 
                                    value="{{ old('panel_basic_sale_price', $user->panel_basic_sale_price ?? 4000.00) }}"
                                    class="w-full pl-8 pr-4 py-2.5 bg-gray-900 border border-gray-700 rounded-xl text-white font-mono text-sm focus:ring-2 focus:ring-amber-500">
                            </div>
                            <span class="text-[10px] text-amber-400 block mt-1 font-mono">
                                💡 PVP Sugerido Oficial por Admin: $4.000 / mes (Tú decides tu ganancia).
                            </span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">
                                Precio de Venta: Panel PRO (Ilimitado) ($)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-sm">$</span>
                                <input type="number" step="0.01" min="0" name="panel_pro_sale_price" 
                                    value="{{ old('panel_pro_sale_price', $user->panel_pro_sale_price ?? 6000.00) }}"
                                    class="w-full pl-8 pr-4 py-2.5 bg-gray-900 border border-gray-700 rounded-xl text-white font-mono text-sm focus:ring-2 focus:ring-purple-500">
                            </div>
                            <span class="text-[10px] text-purple-400 block mt-1 font-mono">
                                💡 PVP Sugerido Oficial por Admin: $6.000 / mes (Tú decides tu ganancia).
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Configuración de Precios de Combos (2 o más cuentas) -->
            <div class="bg-gray-800/40 border border-amber-500/30 rounded-2xl p-6 relative overflow-hidden backdrop-blur-sm shadow-xl">
                <input type="hidden" name="combo_discount_settings_submitted" value="1">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-gray-700/60">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                <i data-lucide="sparkles" class="w-5 h-5"></i>
                            </span>
                            <h2 class="text-base font-bold text-white flex items-center gap-2">
                                Promoción de Combos Multicuentas (2 o más cuentas)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-amber-500/20 text-amber-300 border border-amber-500/30 font-bold">NUEVO</span>
                            </h2>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            Aplica descuentos automáticos cuando un mismo cliente agregue 2 o más cuentas a su carrito de compras. Aumenta tu volumen de ventas y ticket promedio.
                        </p>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="combo_discount_enabled" value="1" 
                            {{ old('combo_discount_enabled', $user->combo_discount_enabled ?? true) ? 'checked' : '' }} 
                            class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        <span class="ml-3 text-xs font-semibold text-gray-300">Activar Promo Combos</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">
                            Mínimo de Cuentas para Activar Combo
                        </label>
                        <input type="number" min="2" max="10" name="combo_min_items" 
                            value="{{ old('combo_min_items', $user->combo_min_items ?? 2) }}" 
                            class="w-full px-4 py-2.5 bg-gray-900 border border-gray-700 rounded-xl text-white font-mono text-sm focus:ring-2 focus:ring-amber-500">
                        <span class="text-[10px] text-gray-400 block mt-1">Recomendado: 2 cuentas.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">
                            Tipo de Descuento en Combo
                        </label>
                        <select name="combo_discount_type" class="w-full px-4 py-2.5 bg-gray-900 border border-gray-700 rounded-xl text-white text-sm focus:ring-2 focus:ring-amber-500">
                            <option value="fixed" {{ old('combo_discount_type', $user->combo_discount_type ?? 'fixed') === 'fixed' ? 'selected' : '' }}>Monto Fijo en Pesos ($ Descuento)</option>
                            <option value="percent" {{ old('combo_discount_type', $user->combo_discount_type ?? 'fixed') === 'percent' ? 'selected' : '' }}>Porcentaje de Descuento (%)</option>
                        </select>
                        <span class="text-[10px] text-gray-400 block mt-1">Descuento aplicado al total del carrito.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">
                            Valor del Descuento ($ o %)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" name="combo_discount_amount" 
                                value="{{ old('combo_discount_amount', $user->combo_discount_amount ?? 1000.00) }}" 
                                class="w-full px-4 py-2.5 bg-gray-900 border border-gray-700 rounded-xl text-white font-mono text-sm focus:ring-2 focus:ring-amber-500">
                        </div>
                        <span class="text-[10px] text-amber-400 block mt-1 font-mono">
                            💡 Ejemplo: $1.000 de descuento o 15% al llevar 2+ cuentas.
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">
                        Texto Promocional para el Banner del Catálogo (Opcional)
                    </label>
                    <input type="text" name="combo_banner_text" 
                        value="{{ old('combo_banner_text', $user->combo_banner_text ?? '') }}" 
                        placeholder="Ej: 🔥 ¡PROMO COMBOS! Elige 2 o más cuentas y obtén $1.000 de descuento inmediato en tu pedido."
                        class="w-full px-4 py-2.5 bg-gray-900 border border-gray-700 rounded-xl text-white text-sm focus:ring-2 focus:ring-amber-500">
                </div>
            </div>

            <!-- Botón de Guardar Todo -->
            <div class="pt-4 border-t border-gray-800 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 hover:opacity-95 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Guardar Preferencias de Panel</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
