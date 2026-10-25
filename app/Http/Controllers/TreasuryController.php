<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Storage;

class TreasuryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $baseQuery = Order::with(['seller', 'servicePlan.service', 'sale']);

        if ($user->isReseller()) {
            $baseQuery->where('seller_id', $user->id);
        }

        if ($request->filled('method')) {
            $baseQuery->where('payment_method', $request->method);
        }

        // Stats globales para el usuario
        $pendingCount = (clone $baseQuery)->where('payment_status', 'pending')->count();
        $approvedTotal = (clone $baseQuery)->where('payment_status', 'approved')->sum('amount');

        $query = clone $baseQuery;
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('treasury.index', compact('orders', 'user', 'pendingCount', 'approvedTotal'));
    }

    public function settings()
    {
        $user = Auth::user();
        return view('treasury.settings', compact('user'));
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'mp_public_key' => 'nullable|string|max:255',
            'mp_access_token' => 'nullable|string|max:255',
            'bank_cbu' => 'nullable|string|max:30',
            'bank_cvu' => 'nullable|string|max:30',
            'bank_alias' => 'nullable|string|max:100',
            'bank_holder_name' => 'nullable|string|max:255',
            'bank_dni' => 'nullable|string|max:50',
            'binance_id' => 'nullable|string|max:100',
            'bank_instructions' => 'nullable|string',
        ]);

        $user->update($validated);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'update_treasury_settings',
            'description' => "Datos de cobro y pasarelas actualizados.",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Configuración de métodos de cobro guardada correctamente.');
    }

    public function viewReceipt(Order $order)
    {
        $user = Auth::user();
        if ($user->isReseller() && $order->seller_id !== $user->id) {
            abort(403);
        }

        if (!$order->receipt_path || !Storage::disk('local')->exists($order->receipt_path)) {
            abort(404, 'El archivo del comprobante no existe o ya ha sido eliminado permanentemente del servidor.');
        }

        $fileContent = Storage::disk('local')->get($order->receipt_path);
        $mimeType = Storage::disk('local')->mimeType($order->receipt_path);

        return response($fileContent)->header('Content-Type', $mimeType);
    }

    public function approveAndClose(Request $request, Order $order)
    {
        $user = Auth::user();
        if ($user->isReseller() && $order->seller_id !== $user->id) {
            abort(403);
        }

        if ($order->payment_status === 'approved') {
            return back()->with('info', 'Esta orden ya se encuentra aprobada.');
        }

        try {
            $notes = $request->input('notes', 'Aprobado manualmente desde el panel de tesorería.');
            $sale = $order->approveAndClose($notes);

            $msg = $sale
                ? "¡Orden aprobada exitosamente! Venta {$sale->sale_code} generada, credenciales asignadas del stock y comprobante físico ELIMINADO permanentemente del hosting."
                : "¡Orden de panel aprobada exitosamente! La franquicia fue activada y el comprobante purgado de la Mini Nube.";

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al aprobar la orden: ' . $e->getMessage());
        }
    }

    public function rejectAndClose(Request $request, Order $order)
    {
        $user = Auth::user();
        if ($user->isReseller() && $order->seller_id !== $user->id) {
            abort(403);
        }

        $reason = $request->input('reason', 'Comprobante no válido o pago no acreditado.');
        $order->rejectAndClose($reason);

        return back()->with('success', 'Orden rechazada y archivo de comprobante eliminado del servidor.');
    }

    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $query = Order::with(['seller', 'servicePlan.service', 'sale']);

        if ($user->isReseller()) {
            $query->where('seller_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $orders = $query->latest()->get();
        $filename = 'ordenes_streaming_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID',
                'Código Orden',
                'Fecha',
                'Cliente',
                'Teléfono / WhatsApp',
                'Email',
                'Plan / Servicio',
                'Vendedor',
                'Monto',
                'Método de Pago',
                'Estado de Pago',
                'Venta Asignada',
                'Notas'
            ], ';');

            foreach ($orders as $ord) {
                fputcsv($file, [
                    $ord->id,
                    $ord->order_code,
                    $ord->created_at ? $ord->created_at->format('d/m/Y H:i') : '',
                    $ord->customer_name,
                    $ord->customer_phone,
                    $ord->customer_email ?? 'N/A',
                    $ord->servicePlan ? $ord->servicePlan->name : 'N/A',
                    $ord->seller ? $ord->seller->name : 'N/A',
                    number_format($ord->amount, 2, ',', '.'),
                    $ord->payment_method,
                    strtoupper($ord->payment_status),
                    $ord->sale ? $ord->sale->sale_code : 'Pendiente',
                    $ord->notes ?? ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
