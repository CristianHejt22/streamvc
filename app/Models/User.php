<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'balance',
        'reseller_plan',
        'referral_code',
        'brand_name',
        'brand_logo',
        'brand_favicon',
        'brand_primary_color',
        'brand_accent_color',
        'custom_login_bg',
        'support_whatsapp',
        'support_telegram',
        'mp_public_key',
        'mp_access_token',
        'parent_id',
        'anti_phishing_code',
        'plain_panel_token',
        'panel_type',
        'panel_tier',
        'panel_monthly_fee',
        'panel_expires_at',
        'cloned_store_paid_to_admin',
        'cloned_store_paid_by_reseller',
        'cloned_store_last_payment_date',
        'reseller_commission_pct',
        'parent_commission_pct',
        'credits_basic',
        'credits_pro',
        'provider_override_support_whatsapp',
        'whatsapp_support_preference',
        'bank_cbu',
        'bank_cvu',
        'bank_alias',
        'bank_holder_name',
        'bank_dni',
        'binance_id',
        'bank_instructions',
        'panel_basic_sale_price',
        'panel_pro_sale_price',
        'offer_panels_on_catalog',
        'combo_discount_enabled',
        'combo_min_items',
        'combo_discount_type',
        'combo_discount_amount',
        'combo_banner_text',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mp_access_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'balance' => 'decimal:2',
        'panel_expires_at' => 'datetime',
        'cloned_store_paid_to_admin' => 'boolean',
        'cloned_store_paid_by_reseller' => 'boolean',
        'cloned_store_last_payment_date' => 'datetime',
        'panel_monthly_fee' => 'decimal:2',
        'reseller_commission_pct' => 'decimal:2',
        'parent_commission_pct' => 'decimal:2',
        'credits_basic' => 'integer',
        'credits_pro' => 'integer',
        'provider_override_support_whatsapp' => 'boolean',
        'panel_basic_sale_price' => 'decimal:2',
        'panel_pro_sale_price' => 'decimal:2',
        'offer_panels_on_catalog' => 'boolean',
        'combo_discount_enabled' => 'boolean',
        'combo_min_items' => 'integer',
        'combo_discount_amount' => 'decimal:2',
    ];

    public function setPlainPasswordAttribute(?string $val): void
    {
        $this->attributes['plain_panel_token'] = !empty($val) ? \Illuminate\Support\Facades\Crypt::encryptString($val) : null;
    }

    public function getPlainPasswordAttribute(): ?string
    {
        if (empty($this->plain_panel_token)) {
            return null;
        }
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($this->plain_panel_token);
        } catch (\Throwable $e) {
            return '******';
        }
    }

    public function isPanelCloned(): bool
    {
        return $this->panel_type === 'cloned';
    }

    public function isPanelExpired(): bool
    {
        if (!$this->isPanelCloned()) {
            return false;
        }
        return $this->panel_expires_at && \Carbon\Carbon::parse($this->panel_expires_at)->isPast();
    }

    /**
     * Valida si el usuario o revendedor tiene sus pagos al día para emitir y entregar cuentas.
     * "No se entregará sin monto en billetera o si no se recibe el pago del proveedor."
     */
    public function canDeliverAccounts(&$reason = null): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->status !== 'active') {
            $reason = 'El panel del revendedor se encuentra inactivo o suspendido.';
            return false;
        }

        // Si es un panel clonado o depende de un proveedor:
        if ($this->isPanelCloned() || $this->parent_id) {
            // 1. Debe haberse recibido el pago de la tienda clonada ($15.000 / mes)
            if (!$this->cloned_store_paid_by_reseller) {
                $reason = 'No se ha recibido el pago del proveedor. El proveedor tiene pendiente de cobro la suscripción de esta tienda clonada ($15.000 / mes).';
                return false;
            }

            // 2. El proveedor debe haber abonado la cuota al Administrador ($8.000 / 30d) y no estar vencido
            if (!$this->cloned_store_paid_to_admin || $this->isPanelExpired()) {
                $reason = 'La suscripción de la tienda ante el Administrador General no está abonada o se encuentra vencida ($8.000 / 30 días al Admin).';
                return false;
            }
        }

        return true;
    }

    public function getDaysRemainingPanelAttribute(): ?int
    {
        if (!$this->panel_expires_at) {
            return null;
        }
        return (int) \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($this->panel_expires_at)->startOfDay(), false);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperReseller(): bool
    {
        return $this->role === 'super_reseller';
    }

    public function isReseller(): bool
    {
        return in_array($this->role, ['reseller', 'super_reseller']);
    }

    public function subResellers()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function hasBalance(float $amount): bool
    {
        return (float)$this->balance >= $amount;
    }

    public function deductBalance(float $amount): bool
    {
        if ($this->hasBalance($amount)) {
            $this->decrement('balance', $amount);
            return true;
        }
        return false;
    }

    public function addBalance(float $amount): void
    {
        $this->increment('balance', $amount);
    }

    public function isPanelBasic(): bool
    {
        return ($this->panel_tier ?? 'pro') === 'basic';
    }

    public function isPanelPro(): bool
    {
        return ($this->panel_tier ?? 'pro') === 'pro';
    }

    /**
     * Determina si el usuario puede revender acceso a las funciones de panel a sub-revendedores.
     * "El basico no revende ningun plan, el pro tiene acceso a la posibilidad de revender."
     */
    public function canResellPanels(): bool
    {
        if ($this->isAdmin() || $this->isSuperReseller()) {
            return true;
        }

        return $this->isPanelPro();
    }

    /**
     * Aplica el scope de inventario de cuentas de streaming según el tipo de panel:
     * - Super Admin: sin restricciones (ve todas las cuentas).
     * - Proveedor / Super Reseller: ve su inventario propio (created_by = $this->id) y cuentas del sistema (created_by IS NULL).
     * - Revendedor con Tienda Clonada (panel_type === 'cloned' y parent_id):
     *     ve las cuentas de su proveedor (created_by = $this->parent_id) y las suyas propias (created_by = $this->id).
     * - Panel Individual (panel_type !== 'cloned' o sin clonación):
     *     "No a los que soliciten el panel individual" -> ÚNICAMENTE ve su propio inventario (created_by = $this->id).
     */
    public function applyInventoryScope($query)
    {
        if ($this->isAdmin()) {
            return $query;
        }

        if ($this->isSuperReseller()) {
            return $query->where(function ($q) {
                $q->where('created_by', $this->id)
                  ->orWhereNull('created_by');
                if ($this->isPanelCloned() && $this->parent_id) {
                    $q->orWhere('created_by', $this->parent_id);
                }
            });
        }

        if ($this->isPanelCloned() && $this->parent_id) {
            return $query->where(function ($q) {
                $q->where('created_by', $this->id)
                  ->orWhere('created_by', $this->parent_id);
                if ($this->parent && ($this->parent->isAdmin() || $this->parent->isSuperReseller())) {
                    $q->orWhereNull('created_by');
                }
            });
        }

        // Panel Individual: Únicamente su inventario propio
        return $query->where('created_by', $this->id);
    }

    public function maxServicesAllowed(): int
    {
        return $this->isPanelBasic() ? 10 : 999999;
    }

    public function hasCredits(string $tier, int $qty = 1): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        if ($tier === 'basic') {
            return (int)$this->credits_basic >= $qty;
        }
        return (int)$this->credits_pro >= $qty;
    }

    public function addCredits(string $tier, int $qty, ?float $unitPrice = null, ?int $adminId = null, ?string $notes = null): CreditTransaction
    {
        $field = $tier === 'basic' ? 'credits_basic' : 'credits_pro';
        $this->increment($field, $qty);

        return CreditTransaction::create([
            'user_id' => $this->id,
            'admin_id' => $adminId,
            'type' => 'purchase',
            'panel_tier' => $tier,
            'credits_amount' => $qty,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice ? ($unitPrice * $qty) : null,
            'notes' => $notes,
        ]);
    }

    public function deductCredits(string $tier, int $qty = 1, ?string $targetPanel = null, ?string $notes = null): bool
    {
        // El Administrador Principal cuenta con créditos ilimitados (∞)
        if ($this->isAdmin()) {
            CreditTransaction::create([
                'user_id' => $this->id,
                'admin_id' => null,
                'type' => 'activation_use',
                'panel_tier' => $tier,
                'credits_amount' => 0,
                'target_panel_name' => $targetPanel,
                'notes' => $notes ?: "Activación de panel {$tier} por Administrador (Crédito Ilimitado) para {$targetPanel}",
            ]);
            return true;
        }

        if (!$this->hasCredits($tier, $qty)) {
            return false;
        }

        $field = $tier === 'basic' ? 'credits_basic' : 'credits_pro';
        $this->decrement($field, $qty);

        CreditTransaction::create([
            'user_id' => $this->id,
            'admin_id' => null,
            'type' => 'activation_use',
            'panel_tier' => $tier,
            'credits_amount' => -$qty,
            'target_panel_name' => $targetPanel,
            'notes' => $notes ?: "Activación de panel {$tier} para {$targetPanel}",
        ]);

        return true;
    }

    public function getBasicPanelPriceAttribute(): float
    {
        return (float) ($this->panel_basic_sale_price ?? SystemSetting::get('suggested_basic_panel_price', 4000.00));
    }

    public function getProPanelPriceAttribute(): float
    {
        return (float) ($this->panel_pro_sale_price ?? SystemSetting::get('suggested_pro_panel_price', 6000.00));
    }

    public function isPanelSalesOffered(): bool
    {
        return $this->offer_panels_on_catalog !== false;
    }

    public function isComboDiscountActive(): bool
    {
        return (bool) ($this->combo_discount_enabled ?? true);
    }

    public function getComboMinItems(): int
    {
        return max(2, (int) ($this->combo_min_items ?? 2));
    }

    public function getComboDiscountType(): string
    {
        return $this->combo_discount_type === 'percent' ? 'percent' : 'fixed';
    }

    public function getComboDiscountAmount(): float
    {
        return (float) ($this->combo_discount_amount ?? 1000.00);
    }

    public function getComboBannerText(): string
    {
        if (!empty($this->combo_banner_text)) {
            return $this->combo_banner_text;
        }

        $min = $this->getComboMinItems();
        if ($this->getComboDiscountType() === 'percent') {
            $pct = number_format($this->getComboDiscountAmount(), 0);
            return "🔥 ¡PROMO COMBOS! Elige {$min} o más cuentas y obtén un {$pct}% de descuento automático en tu carrito.";
        }

        $amt = number_format($this->getComboDiscountAmount(), 0, ',', '.');
        return "🔥 ¡PROMO COMBOS! Elige {$min} o más cuentas y ahorra \${$amt} de descuento inmediato en tu pedido.";
    }

    public function calculateComboDiscount(int $totalItems, float $subtotal): float
    {
        if (!$this->isComboDiscountActive() || $totalItems < $this->getComboMinItems()) {
            return 0.00;
        }

        if ($this->getComboDiscountType() === 'percent') {
            $pct = $this->getComboDiscountAmount();
            return round(($subtotal * ($pct / 100)), 2);
        }

        return min($subtotal, $this->getComboDiscountAmount());
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function streamingAccounts()
    {
        return $this->hasMany(StreamingAccount::class, 'created_by');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function shortcuts()
    {
        return $this->hasMany(Shortcut::class);
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class);
    }

    public function renewalRequests()
    {
        return $this->hasMany(RenewalRequest::class, 'user_id');
    }

    public function customServiceCommissions()
    {
        return $this->hasMany(ResellerServiceCommission::class, 'reseller_id');
    }

    public function configuredChildCommissions()
    {
        return $this->hasMany(ResellerServiceCommission::class, 'parent_id');
    }

    public function customPlanPrices()
    {
        return $this->hasMany(ResellerPlanPrice::class, 'reseller_id');
    }

    public function subResellerPlanPrices()
    {
        return $this->hasMany(ResellerPlanPrice::class, 'parent_id');
    }

    public function balanceTransfersSent()
    {
        return $this->hasMany(BalanceTransfer::class, 'reseller_id');
    }

    public function balanceTransfersReceived()
    {
        return $this->hasMany(BalanceTransfer::class, 'provider_id');
    }

    public function isWhiteLabelAllowed(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return $this->isPanelPro();
    }

    public function getEffectiveBrandName(): string
    {
        if ($this->isReseller() && $this->isPanelBasic()) {
            return 'SPRO PLAY';
        }
        return $this->attributes['brand_name'] ?? 'CStreame Master';
    }

    public function getBrandLogoUrlAttribute(): string
    {
        if ($this->isReseller() && $this->isPanelBasic()) {
            return asset('images/spro-play-logo.svg');
        }
        if (!empty($this->brand_logo)) {
            return asset('storage/' . $this->brand_logo);
        }
        return asset('images/spro-play-logo.svg');
    }

    public function getResolvedVaultWhatsAppAttribute(): string
    {
        // REGLA DEL SISTEMA:
        // 1. El WhatsApp de Administrador es SOLO para soporte del panel principal propio.
        // 2. El WhatsApp de soporte de Proveedor es SOLO para el revendedor.
        // 3. El WhatsApp de Revendedor es para el cliente final (Bóveda y Catálogo).

        if ($this->isAdmin()) {
            return $this->support_whatsapp ?: SystemSetting::get('whatsapp_support_admin_phone', '5491199887766');
        }

        // Para revendedores y proveedores vendiendo a sus clientes:
        // La atención al cliente final corresponde estrictamente al WhatsApp propio del revendedor.
        // Nunca se filtra el WhatsApp del Administrador ni del Proveedor al cliente final.
        return $this->support_whatsapp ?: ($this->phone ?: '');
    }

    public function getEffectiveSupportWhatsappAttribute(): string
    {
        if ($this->isAdmin()) {
            return $this->support_whatsapp ?: SystemSetting::get('whatsapp_support_admin_phone', '5491199887766');
        }
        return $this->support_whatsapp ?: ($this->phone ?: '');
    }

    /**
     * WhatsApp del Proveedor Mayorista para soporte exclusivo del Revendedor
     */
    public function getProviderResellerSupportWhatsAppAttribute(): ?string
    {
        if ($this->parent) {
            return $this->parent->support_whatsapp ?: ($this->parent->phone ?: null);
        }
        // Si no tiene proveedor asignado (revendedor directo del panel central), su soporte de panel es el Administrador
        return self::getAdminPlatformSupportWhatsApp();
    }

    /**
     * WhatsApp Oficial del Administrador de la Plataforma (Soporte Técnico de Panel Propio y Convenios)
     */
    public static function getAdminPlatformSupportWhatsApp(): string
    {
        $admin = self::where('role', 'admin')->first();
        return $admin?->support_whatsapp ?: SystemSetting::get('whatsapp_support_admin_phone', '5491199887766');
    }

    public function getCommissionForService($serviceId): float
    {
        $custom = ResellerServiceCommission::where('reseller_id', $this->id)
            ->where('service_id', $serviceId)
            ->value('commission_pct');

        return $custom !== null ? (float)$custom : (float)($this->reseller_commission_pct ?: 40.00);
    }

    public function getEffectivePlanPricing(ServicePlan $plan): array
    {
        $custom = ResellerPlanPrice::where('reseller_id', $this->id)
            ->where('service_plan_id', $plan->id)
            ->first();

        $providerCost = $custom && $custom->provider_cost !== null 
            ? (float)$custom->provider_cost 
            : (float)($plan->reseller_price ?: ($plan->price * 0.60));

        $resellerPvp = $custom && $custom->reseller_pvp !== null 
            ? (float)$custom->reseller_pvp 
            : (float)$plan->price;

        $margin = max(0, $resellerPvp - $providerCost);

        return [
            'provider_cost' => $providerCost,
            'reseller_pvp' => $resellerPvp,
            'margin' => $margin,
            'margin_pct' => $resellerPvp > 0 ? round(($margin / $resellerPvp) * 100, 1) : 0,
        ];
    }
}
