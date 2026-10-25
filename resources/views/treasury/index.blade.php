@extends('layouts.app')

@section('title', 'Tesorería & Mini Nube')
@section('page_title', 'Módulo de Tesorería & Mini Nube de Comprobantes')
@section('page_subtitle', 'Auditoría de transferencias, aprobación inmediata con entrega de credenciales y purga de comprobantes')

@section('content')
<div class="space-y-6" x-data="{
    auditModal: false,
    activeOrder: null,
    receiptUrl: '',
    orderCode: '',
    customerName: '',
    amount: '',
    notes: ''
}">

    <!-- Notice Banner about Shared Hosting Mini Nube Optimization -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-red-950/40 via-purple-950/20 to-gray-900 border border-red-500/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-red-500/20 text-red-400 flex items-center justify-center shrink-0 border border-red-500/30 text-xl font-bold">
                ☁️
            </div>
            <div>
                <h4 class="font-bold text-sm text-white">Mini Nube & Cierre de Seguridad para Hosting Compartido (cPanel)</h4>
                <p class="text-xs text-gray-400 mt-1 max-w-2xl leading-relaxed">
                    Los comprobantes de transferencia se guardan temporalmente para tu inspección visual. Al presionar <strong class="text-emerald-400">"Aprobar y Entregar Credenciales"</strong> (o <strong class="text-rose-400">"Rechazar"</strong>), el sistema crea la venta, asigna la pantalla disponible y <strong>ELIMINA DE INMEDIATO Y DE FORMA PERMANENTE</strong> el comprobante del servidor para que nunca se llene el disco de tu hosting.
                </p>
            </div>
        </div>
        <a href="{{ route('treasury.settings') }}" class="px-4 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-200 border border-gray-700 text-xs font-bold shrink-0 transition-all flex items-center gap-2">
            <i data-lucide="settings" class="w-4 h-4"></i>
            <span>Mis Métodos de Cobro</span>
        </a>
    </div>

    <!-- Stats & Filters -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="px-4 py-2.5 rounded-xl bg-[#111827] border border-gray-800 flex items-center gap-3">
                <span class="text-xs text-gray-400">Total Aprobado:</span>
                <span class="text-sm font-black text-emerald-400">${{ number_format($approvedTotal, 2) }}</span>
            </div>
            <div class="px-4 py-2.5 rounded-xl bg-[#111827] border border-gray-800 flex items-center gap-3">
                <span class="text-xs text-gray-400">Por Auditar:</span>
                <span class="text-sm font-black text-amber-400">{{ $pendingCount }}</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="{{ route('treasury.index') }}" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-[#111827] border border-gray-800 rounded-xl text-xs text-gray-300">
                    <option value="">Todos los Estados</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendientes de Auditoría</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Aprobadas</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rechazadas</option>
                </select>

                <select name="method" onchange="this.form.submit()" class="px-3 py-2 bg-[#111827] border border-gray-800 rounded-xl text-xs text-gray-300">
                    <option value="">Todos los Métodos</option>
                    <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Transferencia</option>
                    <option value="mercadopago" {{ request('method') === 'mercadopago' ? 'selected' : '' }}>Mercado Pago</option>
                    <option value="binance" {{ request('method') === 'binance' ? 'selected' : '' }}>Binance USDT</option>
                </select>

                @if(request()->hasAny(['status', 'method']))
                    <a href="{{ route('treasury.index') }}" class="p-2 text-gray-400 hover:text-white" title="Limpiar">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                @endif
            </form>

            <a href="{{ route('treasury.export', request()->all()) }}" class="px-3.5 py-2 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-200 border border-gray-700 text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm">
                <i data-lucide="download" class="w-4 h-4 text-emerald-400"></i>
                <span class="hidden sm:inline">Exportar CSV</span>
            </a>
        </div>
    </div>

    <!-- Orders & Receipts Table -->
    <div class="bg-[#111827] border border-gray-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#0d1322] text-gray-400 uppercase tracking-wider font-semibold border-b border-gray-800">
                    <tr>
                        <th class="py-3.5 px-4">Orden</th>
                        <th class="py-3.5 px-4">Cliente</th>
                        <th class="py-3.5 px-4">Plan / Cuenta</th>
                        <th class="py-3.5 px-4">Monto</th>
                        <th class="py-3.5 px-4">Método</th>
                        <th class="py-3.5 px-4">Mini Nube (Comprobante)</th>
                        <th class="py-3.5 px-4">Estado</th>
                        <th class="py-3.5 px-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-800/30 transition-colors">
                        <td class="py-3.5 px-4">
                            <span class="font-mono font-bold text-gray-200">{{ $order->order_code }}</span>
                            <div class="text-[10px] text-gray-400 mt-0.5">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                        </td>

                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white">{{ $order->customer_name }}</div>
                            <div class="text-[11px] text-emerald-400">{{ $order->customer_phone }}</div>
                        </td>

                        <td class="py-3.5 px-4">
                            @if($order->isPanelOrder())
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                    💎 Panel {{ $order->order_type === 'panel_pro' ? 'PRO ILIMITADO' : 'BÁSICO (10 Servicios)' }}
                                </span>
                                <span class="text-[11px] block text-white font-bold mt-0.5">Solicitud de Activación de Panel</span>
                            @elseif($order->isCartOrder() && is_array($order->items_payload) && count($order->items_payload) > 1)
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        🛒 Combo ({{ count($order->items_payload) }} cuentas)
                                    </span>
                                </div>
                                <div class="text-[10px] text-gray-300 mt-1 space-y-0.5">
                                    @foreach($order->items_payload as $it)
                                    <div>• {{ $it['qty'] }}x {{ $it['serviceName'] ?? 'Streaming' }} ({{ $it['planName'] ?? '' }})</div>
                                    @endforeach
                                </div>
                            @else
                                <span class="font-bold text-white">{{ $order->servicePlan ? $order->servicePlan->name : 'Streaming' }}</span>
                                <span class="text-[10px] block text-cyan-400">{{ $order->servicePlan && $order->servicePlan->service ? $order->servicePlan->service->name : '' }}</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="font-black text-sm text-emerald-400 font-mono">${{ number_format($order->amount, 0, ',', '.') }}</span>
                            @if((float)$order->discount_amount > 0)
                                <div class="text-[10px] text-amber-400 font-mono">
                                    🔥 -${{ number_format($order->discount_amount, 0, ',', '.') }} Combo
                                </div>
                            @endif
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-800 text-gray-300">
                                {{ str_replace('_', ' ', $order->payment_method) }}
                            </span>
                        </td>

                        <!-- Mini Nube Comprobante Status -->
                        <td class="py-3.5 px-4">
                            @if($order->receipt_path)
                                <button @click="activeOrder = {{ $order->id }}; orderCode = '{{ $order->order_code }}'; customerName = '{{ $order->customer_name }}'; amount = '{{ number_format($order->amount, 2) }}'; receiptUrl = '{{ route('treasury.receipt', $order) }}'; auditModal = true;" class="px-3 py-1.5 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 text-cyan-400 text-xs font-bold flex items-center gap-1.5 transition-all">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                    <span>Ver Comprobante</span>
                                </button>
                            @else
                                <span class="text-[11px] text-gray-500 italic flex items-center gap-1">
                                    <i data-lucide="check" class="w-3 h-3 text-emerald-500"></i>
                                    Archivo purgado
                                </span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4">
                            @if($order->payment_status === 'pending')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30 animate-pulse">
                                    POR AUDITAR
                                </span>
                            @elseif($order->payment_status === 'approved')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                    APROBADO
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                    RECHAZADO
                                </span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 text-right">
                            @if($order->payment_status === 'pending')
                                <button @click="activeOrder = {{ $order->id }}; orderCode = '{{ $order->order_code }}'; customerName = '{{ $order->customer_name }}'; amount = '{{ number_format($order->amount, 2) }}'; receiptUrl = '{{ $order->receipt_path ? route('treasury.receipt', $order) : '' }}'; auditModal = true;" class="px-3.5 py-1.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-md shadow-red-600/30">
                                    Auditar
                                </button>
                            @else
                                <span class="text-xs text-gray-500">Cerrado</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-gray-400">
                            No hay órdenes registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-gray-800 bg-[#0d1322]">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- MODAL: AUDITORÍA DE COMPROBANTE MINI NUBE -->
    <!-- ========================================== -->
    <div x-show="auditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md">
        <div @click.away="auditModal = false" class="w-full max-w-2xl bg-[#111827] border border-gray-800 rounded-3xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">
            <!-- Modal Header -->
            <div class="p-5 border-b border-gray-800 flex items-center justify-between bg-[#0e1424]">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>Auditoría de Comprobante</span>
                        <span class="font-mono text-cyan-400" x-text="orderCode"></span>
                    </h3>
                    <p class="text-[11px] text-gray-400">Cliente: <strong class="text-white" x-text="customerName"></strong> · Monto: <strong class="text-emerald-400" x-text="'$' + amount"></strong></p>
                </div>
                <button @click="auditModal = false" class="text-gray-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Content: Preview Comprobante -->
            <div class="p-5 overflow-y-auto flex-1 space-y-4">
                <div class="rounded-2xl border border-gray-800 bg-black/40 overflow-hidden flex items-center justify-center min-h-[300px] p-2">
                    <template x-if="receiptUrl">
                        <img :src="receiptUrl" alt="Comprobante en Mini Nube" class="max-h-[380px] w-auto object-contain rounded-xl shadow-lg">
                    </template>
                    <template x-if="!receiptUrl">
                        <div class="text-center text-gray-500 py-12 text-xs">
                            Sin archivo de comprobante adjunto o ya fue purgado.
                        </div>
                    </template>
                </div>

                <div class="p-3.5 rounded-xl bg-amber-950/20 border border-amber-500/30 text-amber-300 text-xs">
                    <strong>⚠️ Aviso de Cierre de Seguridad:</strong> Al hacer clic en "Aprobar y Entregar", se asignará automáticamente del stock disponible y el archivo del comprobante se <strong>eliminará de inmediato</strong> de tu hosting compartido.
                </div>
            </div>

            <!-- Modal Footer: Aprobar o Rechazar -->
            <div class="p-5 border-t border-gray-800 bg-[#0e1424] flex items-center justify-between gap-3">
                <!-- Rechazar -->
                <form :action="'/treasury/orders/' + activeOrder + '/reject'" method="POST" onsubmit="return confirm('¿Seguro que deseas rechazar este pago y eliminar el comprobante?');">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600/20 text-rose-400 hover:bg-rose-600 hover:text-white border border-rose-500/30 text-xs font-bold transition-all">
                        Rechazar y Cerrar
                    </button>
                </form>

                <!-- Aprobar -->
                <form :action="'/treasury/orders/' + activeOrder + '/approve'" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Aprobar y Entregar Credenciales</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
