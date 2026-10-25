<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServicePlan;
use App\Models\User;
use App\Models\Order;
use App\Models\Customer;
use App\Models\ResellerPlanPrice;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $refCode = $request->query('ref');
        $seller = null;

        if ($refCode) {
            $seller = User::where('referral_code', $refCode)->where('status', 'active')->first();
        }

        if (!$seller) {
            $seller = User::where('role', 'admin')->first();
        }

        $query = Service::where('status', 'active')->with(['plans' => function ($q) {
            $q->where('is_active', true);
        }]);

        // Si es revendedor, filtrar catálogo: servicios propios + servicios de su proveedor + del sistema
        if ($seller && $seller->isReseller()) {
            $query->where(function ($q) use ($seller) {
                $q->where('user_id', $seller->id)
                  ->orWhereNull('user_id');
                if ($seller->parent_id) {
                    $q->orWhere('user_id', $seller->parent_id);
                }
            });

            // Si su panel es Básico, límite estricto de 10 servicios
            if ($seller->isPanelBasic()) {
                $query->take(10);
            }
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $services = $query->latest()->get();

        // Aplicar precios de venta al público (PVP) personalizados del revendedor
        if ($seller && $seller->isReseller()) {
            foreach ($services as $service) {
                foreach ($service->plans as $plan) {
                    $customPrice = ResellerPlanPrice::where('reseller_id', $seller->id)
                        ->where('service_plan_id', $plan->id)
                        ->value('reseller_pvp');
                    if ($customPrice !== null && (float)$customPrice > 0) {
                        $plan->price = (float)$customPrice;
                    }
                }
            }
        }

        $categories = Service::where('status', 'active')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        // Marca Oficial SPRO PLAY para Paneles Básicos
        if ($seller && $seller->isReseller() && $seller->isPanelBasic()) {
            $brandName = 'SPRO PLAY';
            $brandLogo = 'images/spro-play-logo.svg';
        } else {
            $brandName = $seller->brand_name ?: ($seller->isAdmin() ? 'CStreame Master Platform' : $seller->name);
            $brandLogo = $seller->brand_logo;
        }

        $primaryColor = $seller->brand_primary_color ?: '#E50914';

        // Resolución de WhatsApp de Soporte para Clientes:
        // El cliente de un revendedor se comunica exclusivamente con el revendedor.
        // El WhatsApp del Admin es solo para el panel principal propio.
        $supportWhatsapp = $seller->resolved_vault_whatsapp;
        $supportTelegram = $seller->support_telegram;

        return view('catalog.index', compact(
            'services',
            'categories',
            'seller',
            'brandName',
            'brandLogo',
            'primaryColor',
            'supportWhatsapp',
            'supportTelegram'
        ));
    }

    public function checkout(Request $request, ServicePlan $plan)
    {
        $sellerId = $request->input('seller_id');
        $seller = User::find($sellerId) ?? User::where('role', 'admin')->first();

        $plan->load('service');

        return view('catalog.checkout', compact('plan', 'seller'));
    }

    public function processCheckout(Request $request, ServicePlan $plan)
    {
        $isLoggedIn = Auth::guard('customer')->check();
        $customer = $isLoggedIn ? Auth::guard('customer')->user() : null;

        $request->validate([
            'customer_name' => $isLoggedIn ? 'nullable|string|max:255' : 'required|string|max:255',
            'customer_phone' => $isLoggedIn ? 'nullable|string|max:50' : 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6',
            'boveda_pin' => ($isLoggedIn && !empty($customer->security_pin)) ? 'nullable|string|size:4|regex:/^[0-9]{4}$/' : 'nullable|string|size:4|regex:/^[0-9]{4}$/',
            'payment_method' => 'required|in:bank_transfer,mercadopago,binance',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp,svg|max:10240',
            'seller_id' => 'required|exists:users,id',
        ], [
            'customer_name.required' => 'Ingresa tu nombre para identificarte.',
            'customer_phone.required' => 'Ingresa tu WhatsApp para entregarte los accesos.',
            'boveda_pin.size' => 'El PIN de la Bóveda debe tener exactamente 4 dígitos numéricos.',
            'boveda_pin.regex' => 'El PIN de la Bóveda solo debe contener números del 0 al 9.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $seller = User::findOrFail($request->seller_id);

        $customerName = $customer ? $customer->name : $request->customer_name;
        $customerPhone = $customer ? $customer->phone : $request->customer_phone;
        $customerEmail = $customer ? $customer->email : $request->customer_email;
        $bovedaPin = $customer ? ($customer->security_pin ?: ($request->boveda_pin ?: '1234')) : ($request->boveda_pin ?: '1234');

        $order = new Order();
        $order->seller_id = $seller->id;
        $order->service_plan_id = $plan->id;
        $order->customer_name = $customerName;
        $order->customer_phone = $customerPhone;
        $order->customer_email = $customerEmail;
        $order->boveda_pin = $bovedaPin;
        $order->amount = $plan->price;
        $order->payment_method = $request->payment_method;
        $order->payment_status = 'pending';

        if ($request->payment_method === 'bank_transfer' || $request->payment_method === 'binance') {
            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $filename = 'recibo_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('receipts_temp', $filename, 'local');
                $order->receipt_path = $path;
            }

            $order->notes = 'Comprobante cargado por el cliente desde el catálogo. Listo para auditoría.';
            $order->save();
        } else {
            // Mercado Pago
            $order->save();
        }

        // Crear / Vincular cliente en la base de datos de inmediato con su PIN de Bóveda y Password
        if (!$customer) {
            $customer = Customer::where('phone', $customerPhone)
                ->orWhere(function ($q) use ($customerEmail) {
                    if ($customerEmail) $q->where('email', $customerEmail);
                })
                ->first();

            if (!$customer) {
                $customer = new Customer();
                $customer->user_id = $seller->id;
                $customer->name = $customerName;
                $customer->phone = $customerPhone;
                $customer->email = $customerEmail;
                $customer->security_pin = $bovedaPin;
                $customer->password = Hash::make($request->password ?: $bovedaPin);
                $customer->status = 'active';
                $customer->save();
            } else {
                if ($bovedaPin && empty($customer->security_pin)) {
                    $customer->security_pin = $bovedaPin;
                }
                if ($request->filled('password')) {
                    $customer->password = Hash::make($request->password);
                }
                $customer->save();
            }

            Auth::guard('customer')->login($customer, true);
        }

        if ($request->payment_method === 'mercadopago') {
            return redirect()->route('catalog.mp_redirect', ['order' => $order->order_code]);
        }

        return redirect()->route('catalog.success', ['order' => $order->order_code])
            ->with('success', '¡Comprobante recibido con éxito! En breve auditaremos el pago y recibirás tus credenciales.');
    }

    public function processCartCheckout(Request $request)
    {
        $isLoggedIn = Auth::guard('customer')->check();
        $customer = $isLoggedIn ? Auth::guard('customer')->user() : null;

        $request->validate([
            'seller_id' => 'required|exists:users,id',
            'customer_name' => $isLoggedIn ? 'nullable|string|max:255' : 'required|string|max:255',
            'customer_phone' => $isLoggedIn ? 'nullable|string|max:50' : 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6',
            'boveda_pin' => ($isLoggedIn && !empty($customer->security_pin)) ? 'nullable|string|size:4|regex:/^[0-9]{4}$/' : 'required|string|size:4|regex:/^[0-9]{4}$/',
            'payment_method' => 'required|in:bank_transfer,mercadopago,binance',
            'cart_items' => 'required',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp,svg|max:10240',
        ], [
            'customer_name.required' => 'Ingresa tu nombre para identificarte.',
            'customer_phone.required' => 'Ingresa tu WhatsApp para enviarte las alertas.',
            'boveda_pin.required' => 'El PIN de seguridad de tu Bóveda (4 dígitos) es obligatorio para proteger tus cuentas.',
            'boveda_pin.size' => 'El PIN de la Bóveda debe tener exactamente 4 dígitos numéricos.',
            'boveda_pin.regex' => 'El PIN de la Bóveda solo debe contener números del 0 al 9.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'cart_items.required' => 'El carrito está vacío. Agrega al menos una cuenta para continuar.',
        ]);

        $seller = User::findOrFail($request->seller_id);

        $items = is_array($request->cart_items) ? $request->cart_items : json_decode($request->cart_items, true);
        if (empty($items) || !is_array($items)) {
            return back()->with('error', 'El carrito está vacío o contiene datos inválidos.');
        }

        // Calcular subtotal y verificar items
        $subtotal = 0.00;
        $totalCount = 0;
        $sanitizedItems = [];
        $firstPlanId = null;

        foreach ($items as $item) {
            $planId = $item['planId'] ?? null;
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $plan = ServicePlan::with('service')->find($planId);
            if ($plan) {
                if (!$firstPlanId) {
                    $firstPlanId = $plan->id;
                }
                $price = (float) $plan->price;

                // Revisar si el revendedor tiene PVP personalizado
                if ($seller->isReseller()) {
                    $customPvp = \App\Models\ResellerPlanPrice::where('reseller_id', $seller->id)
                        ->where('service_plan_id', $plan->id)
                        ->value('reseller_pvp');
                    if ($customPvp !== null && (float) $customPvp > 0) {
                        $price = (float) $customPvp;
                    }
                }

                $itemSubtotal = $price * $qty;
                $subtotal += $itemSubtotal;
                $totalCount += $qty;

                $sanitizedItems[] = [
                    'planId' => $plan->id,
                    'serviceId' => $plan->service_id,
                    'serviceName' => $plan->service->name ?? 'Streaming',
                    'planName' => $plan->name,
                    'duration_days' => $plan->duration_days,
                    'modality' => $plan->modality,
                    'color' => $plan->service->color_hex ?? '#E50914',
                    'price' => $price,
                    'qty' => $qty,
                    'total' => $itemSubtotal,
                ];
            }
        }

        if (empty($sanitizedItems)) {
            return back()->with('error', 'Ningún plan seleccionado se encuentra activo.');
        }

        // Calcular descuento de combo
        $comboDiscount = $seller->calculateComboDiscount($totalCount, $subtotal);
        $finalTotal = max(0, $subtotal - $comboDiscount);

        $customerName = $customer ? $customer->name : $request->customer_name;
        $customerPhone = $customer ? $customer->phone : $request->customer_phone;
        $customerEmail = $customer ? $customer->email : $request->customer_email;
        $bovedaPin = $customer ? ($customer->security_pin ?: $request->boveda_pin) : $request->boveda_pin;

        // Crear Orden en base de datos
        $order = new Order();
        $order->seller_id = $seller->id;
        $order->service_plan_id = $firstPlanId;
        $order->customer_name = $customerName;
        $order->customer_phone = $customerPhone;
        $order->customer_email = $customerEmail;
        $order->boveda_pin = $bovedaPin;
        $order->subtotal = $subtotal;
        $order->discount_amount = $comboDiscount;
        $order->amount = $finalTotal;
        $order->order_type = ($totalCount >= 2 && $comboDiscount > 0) ? 'cart_combo' : 'accounts';
        $order->items_payload = $sanitizedItems;
        $order->payment_method = $request->payment_method;
        $order->payment_status = 'pending';

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = 'recibo_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('receipts_temp', $filename, 'local');
            $order->receipt_path = $path;
        }

        $order->notes = "Pedido de carrito con {$totalCount} cuentas. Subtotal: \${$subtotal}. Descuento Combo: \${$comboDiscount}.";
        $order->save();

        // Crear / Vincular cliente en la base de datos de inmediato con su PIN de Bóveda
        if (!$customer) {
            $customer = Customer::where('phone', $customerPhone)
                ->orWhere(function ($q) use ($customerEmail) {
                    if ($customerEmail) $q->where('email', $customerEmail);
                })
                ->first();

            if (!$customer) {
                $customer = new Customer();
                $customer->user_id = $seller->id;
                $customer->name = $customerName;
                $customer->phone = $customerPhone;
                $customer->email = $customerEmail;
                $customer->security_pin = $bovedaPin;
                $customer->password = Hash::make($request->password ?: $bovedaPin);
                $customer->status = 'active';
                $customer->save();
            } else {
                if ($bovedaPin && empty($customer->security_pin)) {
                    $customer->security_pin = $bovedaPin;
                }
                if ($request->filled('password')) {
                    $customer->password = Hash::make($request->password);
                }
                $customer->save();
            }

            Auth::guard('customer')->login($customer, true);
        }

        if ($request->payment_method === 'mercadopago') {
            return redirect()->route('catalog.mp_redirect', ['order' => $order->order_code]);
        }

        return redirect()->route('catalog.success', ['order' => $order->order_code])
            ->with('success', '¡Tu pedido fue registrado en el sistema exitosamente! En breve el vendedor auditará tu pago.');
    }

    public function processPanelCheckout(Request $request)
    {
        $isLoggedIn = Auth::guard('customer')->check();
        $customer = $isLoggedIn ? Auth::guard('customer')->user() : null;

        $request->validate([
            'seller_id' => 'required|exists:users,id',
            'panel_type' => 'required|in:basic,pro',
            'customer_name' => $isLoggedIn ? 'nullable|string|max:255' : 'required|string|max:255',
            'customer_phone' => $isLoggedIn ? 'nullable|string|max:50' : 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6',
            'boveda_pin' => ($isLoggedIn && !empty($customer->security_pin)) ? 'nullable|string|size:4|regex:/^[0-9]{4}$/' : 'nullable|string|size:4|regex:/^[0-9]{4}$/',
            'payment_method' => 'required|in:bank_transfer,mercadopago,binance',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp,svg|max:10240',
        ]);

        $seller = User::findOrFail($request->seller_id);
        $panelType = $request->panel_type;
        $price = $panelType === 'pro' ? $seller->pro_panel_price : $seller->basic_panel_price;
        $panelTitle = $panelType === 'pro' ? 'Panel Franquicia PRO Ilimitado' : 'Panel Básico (10 Servicios)';

        $customerName = $customer ? $customer->name : $request->customer_name;
        $customerPhone = $customer ? $customer->phone : $request->customer_phone;
        $customerEmail = $customer ? $customer->email : $request->customer_email;
        $bovedaPin = $customer ? ($customer->security_pin ?: ($request->boveda_pin ?: '0000')) : ($request->boveda_pin ?: '0000');

        $order = new Order();
        $order->seller_id = $seller->id;
        $order->service_plan_id = null;
        $order->customer_name = $customerName;
        $order->customer_phone = $customerPhone;
        $order->customer_email = $customerEmail;
        $order->boveda_pin = $bovedaPin;
        $order->subtotal = $price;
        $order->discount_amount = 0.00;
        $order->amount = $price;
        $order->order_type = 'panel_' . $panelType;
        $order->items_payload = [[
            'type' => 'panel',
            'panel_type' => $panelType,
            'title' => $panelTitle,
            'price' => $price,
            'qty' => 1,
        ]];
        $order->payment_method = $request->payment_method;
        $order->payment_status = 'pending';

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = 'recibo_panel_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('receipts_temp', $filename, 'local');
            $order->receipt_path = $path;
        }

        $order->notes = "Solicitud de activación de {$panelTitle} registrada desde la web.";
        $order->save();

        if (!$customer) {
            $customer = Customer::where('phone', $customerPhone)
                ->orWhere(function ($q) use ($customerEmail) {
                    if ($customerEmail) $q->where('email', $customerEmail);
                })
                ->first();

            if (!$customer) {
                $customer = new Customer();
                $customer->user_id = $seller->id;
                $customer->name = $customerName;
                $customer->phone = $customerPhone;
                $customer->email = $customerEmail;
                $customer->security_pin = $bovedaPin;
                $customer->password = Hash::make($request->password ?: $bovedaPin);
                $customer->status = 'active';
                $customer->save();
            } else {
                if ($bovedaPin && empty($customer->security_pin)) {
                    $customer->security_pin = $bovedaPin;
                }
                if ($request->filled('password')) {
                    $customer->password = Hash::make($request->password);
                }
                $customer->save();
            }

            Auth::guard('customer')->login($customer, true);
        }

        if ($request->payment_method === 'mercadopago') {
            return redirect()->route('catalog.mp_redirect', ['order' => $order->order_code]);
        }

        return redirect()->route('catalog.success', ['order' => $order->order_code])
            ->with('success', "¡Solicitud de {$panelTitle} registrada en el panel! Comunícate a soporte para la activación inmediata.");
    }

    public function mpRedirect($orderCode)
    {
        $order = Order::with(['seller', 'servicePlan.service'])->where('order_code', $orderCode)->firstOrFail();
        return view('catalog.mercadopago', compact('order'));
    }

    public function mpSimulatePayment(Request $request, $orderCode)
    {
        $order = Order::with(['seller', 'servicePlan.service'])->where('order_code', $orderCode)->firstOrFail();

        try {
            $sale = $order->approveAndClose('Aprobado automáticamente mediante pasarela Mercado Pago.');
            return redirect()->route('catalog.success', ['order' => $order->order_code])
                ->with('success', '¡Pago acreditado por Mercado Pago! Aquí tienes tus credenciales de streaming.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function success($orderCode)
    {
        $order = Order::with(['seller', 'servicePlan.service', 'sale'])->where('order_code', $orderCode)->firstOrFail();
        return view('catalog.success', compact('order'));
    }
}
