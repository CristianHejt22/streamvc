<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'seller_id',
        'service_plan_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'boveda_pin',
        'amount',
        'subtotal',
        'discount_amount',
        'order_type',
        'items_payload',
        'payment_method',
        'payment_status',
        'receipt_path',
        'sale_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'items_payload' => 'array',
    ];

    public function isCartOrder(): bool
    {
        return !empty($this->items_payload) || $this->order_type === 'cart_combo';
    }

    public function isComboOrder(): bool
    {
        return (float) $this->discount_amount > 0;
    }

    public function isPanelOrder(): bool
    {
        return in_array($this->order_type, ['panel_basic', 'panel_pro']);
    }

    public function getItemsListAttribute(): array
    {
        if (is_array($this->items_payload)) {
            return $this->items_payload;
        }

        if ($this->servicePlan) {
            return [[
                'planId' => $this->servicePlan->id,
                'serviceName' => $this->servicePlan->service->name ?? 'Servicio',
                'planName' => $this->servicePlan->name,
                'price' => (float) $this->amount,
                'qty' => 1,
            ]];
        }

        return [];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_code)) {
                $order->order_code = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            }
        });
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function servicePlan()
    {
        return $this->belongsTo(ServicePlan::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Eliminación permanente e inmediata del comprobante físico para no saturar hosting compartido
     */
    public function deleteReceiptFile(): void
    {
        if ($this->receipt_path) {
            if (Storage::disk('local')->exists($this->receipt_path)) {
                Storage::disk('local')->delete($this->receipt_path);
            }
            if (Storage::disk('public')->exists($this->receipt_path)) {
                Storage::disk('public')->delete($this->receipt_path);
            }

            $this->update([
                'receipt_path' => null,
            ]);
        }
    }

    /**
     * Aprobar y Cerrar: Asigna cuenta/pantalla del stock, crea venta, genera credenciales y elimina comprobante
     */
    public function approveAndClose(?string $notes = null): ?Sale
    {
        // CASO 1: ORDEN DE PANEL FRANQUICIA (BÁSICO O PRO)
        if ($this->isPanelOrder()) {
            $tier = ($this->order_type === 'panel_pro') ? 'pro' : 'basic';

            // Buscar si ya existe el usuario revendedor o crearlo
            $reseller = null;
            if ($this->customer_email) {
                $reseller = User::where('email', $this->customer_email)->first();
            }
            if (!$reseller && $this->customer_phone) {
                $reseller = User::where('phone', $this->customer_phone)->first();
            }

            if (!$reseller) {
                $uniqueEmail = $this->customer_email ?: ('revendedor_' . time() . '_' . Str::random(4) . '@streamvc.app');
                $reseller = User::create([
                    'name' => $this->customer_name,
                    'email' => $uniqueEmail,
                    'phone' => $this->customer_phone,
                    'password' => bcrypt($this->boveda_pin ?: '123456'),
                    'role' => 'reseller',
                    'parent_id' => $this->seller_id,
                    'panel_type' => 'cloned',
                    'panel_tier' => $tier,
                    'status' => 'active',
                    'panel_expires_at' => now()->addDays(30),
                    'cloned_store_paid_to_admin' => true,
                    'cloned_store_paid_by_reseller' => true,
                    'cloned_store_last_payment_date' => now(),
                    'referral_code' => strtoupper(Str::random(6)),
                ]);
            } else {
                $reseller->update([
                    'role' => 'reseller',
                    'panel_type' => 'cloned',
                    'panel_tier' => $tier,
                    'status' => 'active',
                    'panel_expires_at' => now()->addDays(30),
                    'cloned_store_paid_to_admin' => true,
                    'cloned_store_paid_by_reseller' => true,
                    'cloned_store_last_payment_date' => now(),
                ]);
            }

            $this->update([
                'payment_status' => 'approved',
                'notes' => ($this->notes ? $this->notes . "\n" : '') . ($notes ?: "Panel Franquicia " . strtoupper($tier) . " activado exitosamente para {$this->customer_name}."),
            ]);

            $this->deleteReceiptFile();

            AuditLog::create([
                'user_id' => auth()->id() ?? $this->seller_id,
                'action' => 'approve_panel_order',
                'description' => "Orden de panel {$this->order_code} aprobada. Panel " . strtoupper($tier) . " activado para {$reseller->name} ({$reseller->email}). Comprobante eliminado.",
                'ip_address' => request()->ip(),
            ]);

            return null;
        }

        // CASO 2: ORDEN DE CARRITO MULTICUENTAS (COMBO)
        if ($this->isCartOrder() && is_array($this->items_payload) && count($this->items_payload) > 0) {
            $seller = $this->seller;

            // Buscar o crear cliente
            $customer = Customer::firstOrCreate(
                [
                    'user_id' => $this->seller_id,
                    'phone' => $this->customer_phone,
                ],
                [
                    'name' => $this->customer_name,
                    'email' => $this->customer_email,
                    'password' => bcrypt($this->boveda_pin ?: '123456'),
                    'security_pin' => $this->boveda_pin,
                ]
            );

            if ($this->boveda_pin && (empty($customer->security_pin) || $customer->wasRecentlyCreated)) {
                $customer->update(['security_pin' => $this->boveda_pin]);
            }

            $createdSales = [];
            foreach ($this->items_payload as $item) {
                $planId = $item['planId'] ?? null;
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $plan = ServicePlan::with('service')->find($planId);
                if (!$plan) continue;

                $service = $plan->service;

                for ($i = 0; $i < $qty; $i++) {
                    $streamingAccount = null;
                    $profile = null;

                    if ($plan->modality === 'profile') {
                        $profileQuery = AccountProfile::whereHas('streamingAccount', function ($q) use ($service, $seller) {
                            $q->where('service_id', $service->id)->where('status', 'active');
                            if ($seller) {
                                $seller->applyInventoryScope($q);
                            }
                        })->where('status', 'available');

                        $profile = $profileQuery->first();
                        if (!$profile) {
                            throw new \Exception("No hay pantallas disponibles en stock para {$service->name}. Carga stock primero antes de aprobar la orden.");
                        }
                        $streamingAccount = $profile->streamingAccount;
                    } else {
                        $accountQuery = StreamingAccount::where('service_id', $service->id)
                            ->where('modality', 'full_account')
                            ->where('status', 'active')
                            ->whereDoesntHave('sales', function ($q) {
                                $q->whereIn('status', ['active', 'expiring_soon']);
                            });

                        if ($seller) {
                            $seller->applyInventoryScope($accountQuery);
                        }

                        $streamingAccount = $accountQuery->first();
                        if (!$streamingAccount) {
                            throw new \Exception("No hay cuentas completas en stock para {$service->name}. Carga stock primero antes de aprobar la orden.");
                        }
                    }

                    // Validación de revendedor si aplica
                    $parentCommission = 0.00;
                    if ($seller && $seller->isReseller()) {
                        $providerBlockReason = null;
                        if (!$seller->canDeliverAccounts($providerBlockReason)) {
                            throw new \Exception("⛔ Entrega Bloqueada: {$providerBlockReason}");
                        }
                        $cost = $plan->reseller_price > 0 ? (float)$plan->reseller_price : (float)$plan->price;
                        if (!$seller->hasBalance($cost)) {
                            throw new \Exception("⛔ Saldo insuficiente (\${$cost}) para {$plan->name}.");
                        }
                        $seller->deductBalance($cost);

                        if ($seller->parent && $seller->parent->isSuperReseller()) {
                            $pct = (float) ($seller->parent_commission_pct ?: 25.00);
                            $parentCommission = round(((float)$item['price']) * ($pct / 100), 2);
                            $seller->parent->addBalance($parentCommission);
                        }
                    }

                    $startDate = now();
                    $endDate = now()->addDays($plan->duration_days ?: 30);
                    $saleCode = 'CST-' . date('Ymd') . '-' . strtoupper(Str::random(4));

                    $sale = Sale::create([
                        'sale_code' => $saleCode,
                        'user_id' => $this->seller_id,
                        'customer_id' => $customer->id,
                        'service_id' => $service->id,
                        'service_plan_id' => $plan->id,
                        'streaming_account_id' => $streamingAccount->id,
                        'account_profile_id' => $profile ? $profile->id : null,
                        'modality' => $plan->modality,
                        'account_email_snapshot' => $streamingAccount->account_email,
                        'account_password_snapshot' => $streamingAccount->account_password,
                        'profile_name_snapshot' => $profile ? $profile->profile_name : 'Cuenta Completa',
                        'profile_pin_snapshot' => $profile ? $profile->pin : null,
                        'usage_rules_snapshot' => $service->default_rules,
                        'sale_price' => (float)$item['price'],
                        'cost_price' => $plan->cost_estimate ?: 0,
                        'profit' => (float)$item['price'] - ($plan->cost_estimate ?: 0),
                        'parent_commission_amount' => $parentCommission,
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString(),
                        'status' => 'active',
                        'notes' => 'Generada automáticamente desde orden de carrito ' . $this->order_code,
                    ]);

                    if ($profile) {
                        $profile->update(['status' => 'assigned']);
                    }

                    $createdSales[] = $sale;
                }
            }

            $primarySale = $createdSales[0] ?? null;

            $this->update([
                'payment_status' => 'approved',
                'sale_id' => $primarySale ? $primarySale->id : null,
                'notes' => ($this->notes ? $this->notes . "\n" : '') . ($notes ?: 'Aprobado y ' . count($createdSales) . ' cuentas asignadas exitosamente a la Bóveda.'),
            ]);

            $this->deleteReceiptFile();

            AuditLog::create([
                'user_id' => auth()->id() ?? $this->seller_id,
                'action' => 'approve_order',
                'description' => "Orden de carrito {$this->order_code} aprobada. " . count($createdSales) . " ventas generadas y cargadas en Bóveda.",
                'ip_address' => request()->ip(),
            ]);

            return $primarySale;
        }

        // CASO 3: ORDEN INDIVIDUAL DE UN PLAN
        $plan = $this->servicePlan;
        if (!$plan) {
            throw new \Exception("El plan seleccionado ya no existe en el catálogo.");
        }

        $service = $plan->service;

        $seller = $this->seller;

        // Buscar stock disponible según modalidad y alcance de inventario del vendedor
        $streamingAccount = null;
        $profile = null;

        if ($plan->modality === 'profile') {
            // Buscar perfil libre dentro del inventario habilitado para este vendedor
            $profileQuery = AccountProfile::whereHas('streamingAccount', function ($q) use ($service, $seller) {
                $q->where('service_id', $service->id)->where('status', 'active');
                if ($seller) {
                    $seller->applyInventoryScope($q);
                }
            })->where('status', 'available');

            $profile = $profileQuery->first();

            if (!$profile) {
                throw new \Exception("No hay pantallas disponibles en stock para {$service->name}. Carga stock primero antes de aprobar la orden.");
            }

            $streamingAccount = $profile->streamingAccount;
        } else {
            // Cuenta completa dentro del alcance de inventario
            $accountQuery = StreamingAccount::where('service_id', $service->id)
                ->where('modality', 'full_account')
                ->where('status', 'active')
                ->whereDoesntHave('sales', function ($q) {
                    $q->whereIn('status', ['active', 'expiring_soon']);
                });

            if ($seller) {
                $seller->applyInventoryScope($accountQuery);
            }

            $streamingAccount = $accountQuery->first();

            if (!$streamingAccount) {
                throw new \Exception("No hay cuentas completas en stock para {$service->name}. Carga stock primero antes de aprobar la orden.");
            }
        }

        // Buscar o crear cliente
        $customer = Customer::firstOrCreate(
            [
                'user_id' => $this->seller_id,
                'phone' => $this->customer_phone,
            ],
            [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'password' => bcrypt($this->boveda_pin ?: '123456'),
                'security_pin' => $this->boveda_pin,
            ]
        );

        if ($this->boveda_pin && (empty($customer->security_pin) || $customer->wasRecentlyCreated)) {
            $customer->update(['security_pin' => $this->boveda_pin]);
        }

        // Validación estricta de entrega y descuento de billetera si el vendedor es revendedor
        $parentCommission = 0.00;
        if ($seller && $seller->isReseller()) {
            $providerBlockReason = null;
            if (!$seller->canDeliverAccounts($providerBlockReason)) {
                throw new \Exception("⛔ Entrega Bloqueada: {$providerBlockReason}");
            }

            $cost = $plan->reseller_price > 0 ? (float)$plan->reseller_price : (float)$plan->price;
            if (!$seller->hasBalance($cost)) {
                throw new \Exception("⛔ El revendedor no dispone de saldo suficiente (\${$cost}) en su billetera para autorizar esta orden. Saldo actual: \${$seller->balance}.");
            }
            $seller->deductBalance($cost);

            // Acreditar comisión automática del 25% al Proveedor Mayorista patrocinador (Super Reseller)
            // REGLA: El Super Admin Central NO cobra ninguna comisión por servicios ofrecidos por proveedores o revendedores.
            if ($seller->parent && $seller->parent->isSuperReseller()) {
                $pct = (float) ($seller->parent_commission_pct ?: 25.00);
                $parentCommission = round($this->amount * ($pct / 100), 2);
                $seller->parent->addBalance($parentCommission);
            }
        }

        // Asignar y crear venta
        $startDate = now();
        $endDate = now()->addDays($plan->duration_days ?: 30);
        $saleCode = 'CST-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $sale = Sale::create([
            'sale_code' => $saleCode,
            'user_id' => $this->seller_id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'service_plan_id' => $plan->id,
            'streaming_account_id' => $streamingAccount->id,
            'account_profile_id' => $profile ? $profile->id : null,
            'modality' => $plan->modality,
            'account_email_snapshot' => $streamingAccount->account_email,
            'account_password_snapshot' => $streamingAccount->account_password,
            'profile_name_snapshot' => $profile ? $profile->profile_name : 'Cuenta Completa',
            'profile_pin_snapshot' => $profile ? $profile->pin : null,
            'usage_rules_snapshot' => $service->default_rules,
            'sale_price' => $this->amount,
            'cost_price' => $plan->cost_estimate ?: 0,
            'profit' => $this->amount - ($plan->cost_estimate ?: 0),
            'parent_commission_amount' => $parentCommission,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'status' => 'active',
            'notes' => 'Generada automáticamente desde orden ' . $this->order_code,
        ]);

        // Marcar perfil como asignado
        if ($profile) {
            $profile->update(['status' => 'assigned']);
        }

        // Actualizar orden
        $this->update([
            'payment_status' => 'approved',
            'sale_id' => $sale->id,
            'notes' => ($this->notes ? $this->notes . "\n" : '') . ($notes ?: 'Aprobado y credenciales asignadas exitosamente: ' . $saleCode),
        ]);

        // Cierre de seguridad: ELIMINAR PERMANENTEMENTE EL COMPROBANTE FÍSICO
        $this->deleteReceiptFile();

        // Auditoría
        AuditLog::create([
            'user_id' => auth()->id() ?? $this->seller_id,
            'action' => 'approve_order',
            'description' => "Orden {$this->order_code} aprobada. Venta {$saleCode} generada. Comprobante eliminado del servidor.",
            'ip_address' => request()->ip(),
        ]);

        return $sale;
    }

    /**
     * Rechazar orden y eliminar comprobante
     */
    public function rejectAndClose(?string $reason = null): void
    {
        $this->update([
            'payment_status' => 'rejected',
            'notes' => ($this->notes ? $this->notes . "\n" : '') . ($reason ?: 'Pago rechazado por el operador.'),
        ]);

        $this->deleteReceiptFile();

        AuditLog::create([
            'user_id' => auth()->id() ?? $this->seller_id,
            'action' => 'reject_order',
            'description' => "Orden {$this->order_code} rechazada. Motivo: {$reason}. Archivo de comprobante eliminado.",
            'ip_address' => request()->ip(),
        ]);
    }
}
