<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Storage;

class WhiteLabelController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isWhiteLabelAllowed = $user->isWhiteLabelAllowed();

        return view('whitelabel.index', compact('user', 'isWhiteLabelAllowed'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $isBasic = $user->isReseller() && $user->isPanelBasic();

        $rules = [
            'support_whatsapp' => 'nullable|string|max:50',
            'support_telegram' => 'nullable|string|max:50',
            'whatsapp_support_preference' => 'nullable|in:auto,reseller,provider,admin',
        ];

        // Marca blanca completa sólo permitida si no es panel básico
        if (!$isBasic) {
            $rules['brand_name'] = 'required|string|max:100';
            $rules['brand_logo'] = 'nullable|image|max:2048';
            $rules['brand_favicon'] = 'nullable|image|max:1024';
            $rules['brand_primary_color'] = 'required|string|max:20';
            $rules['brand_accent_color'] = 'required|string|max:20';
            $rules['custom_login_bg'] = 'nullable|image|max:4096';
        }

        $validated = $request->validate($rules);

        // Guardar datos de contacto de soporte
        $user->support_whatsapp = $validated['support_whatsapp'] ?? null;
        $user->support_telegram = $validated['support_telegram'] ?? null;

        // Gestión de preferencia de WhatsApp para Bóveda
        $oldPref = $user->whatsapp_support_preference ?? 'auto';
        $newPref = $request->input('whatsapp_support_preference', $oldPref);
        $prefChanged = ($oldPref !== $newPref);
        $user->whatsapp_support_preference = $newPref;

        // Si es panel PRO o Admin, actualizar datos de marca
        if (!$isBasic) {
            $user->brand_name = $validated['brand_name'];
            $user->brand_primary_color = $validated['brand_primary_color'];
            $user->brand_accent_color = $validated['brand_accent_color'];

            if ($request->hasFile('brand_logo')) {
                $user->brand_logo = $request->file('brand_logo')->store('branding', 'public');
            }

            if ($request->hasFile('brand_favicon')) {
                $user->brand_favicon = $request->file('brand_favicon')->store('branding', 'public');
            }

            if ($request->hasFile('custom_login_bg')) {
                $user->custom_login_bg = $request->file('custom_login_bg')->store('branding', 'public');
            }

            if ($user->isAdmin()) {
                SystemSetting::set('site_name', $user->brand_name);
            }
        } else {
            // En panel básico, fijar SPRO PLAY
            $user->brand_name = 'SPRO PLAY';
        }

        // Configuración de reventa de paneles para Proveedores / PRO
        if ($user->canResellPanels()) {
            $user->offer_panels_on_catalog = $request->boolean('offer_panels_on_catalog');
            if ($request->filled('panel_basic_sale_price')) {
                $user->panel_basic_sale_price = max(0, (float) $request->input('panel_basic_sale_price'));
            }
            if ($request->filled('panel_pro_sale_price')) {
                $user->panel_pro_sale_price = max(0, (float) $request->input('panel_pro_sale_price'));
            }

            // Si es el Super Admin, actualizar también el Precio Sugerido Oficial global de la plataforma
            if ($user->isAdmin()) {
                SystemSetting::set('suggested_basic_panel_price', $user->panel_basic_sale_price);
                SystemSetting::set('suggested_pro_panel_price', $user->panel_pro_sale_price);
            }
        }

        // Configuración de Precios de Combos (2 o más cuentas)
        if ($request->has('combo_discount_settings_submitted') || $user->isWhiteLabelAllowed() || $user->isAdmin()) {
            $user->combo_discount_enabled = $request->boolean('combo_discount_enabled');
            if ($request->filled('combo_min_items')) {
                $user->combo_min_items = max(2, (int) $request->input('combo_min_items'));
            }
            if ($request->filled('combo_discount_type')) {
                $user->combo_discount_type = in_array($request->input('combo_discount_type'), ['fixed', 'percent']) ? $request->input('combo_discount_type') : 'fixed';
            }
            if ($request->filled('combo_discount_amount')) {
                $user->combo_discount_amount = max(0, (float) $request->input('combo_discount_amount'));
            }
            $user->combo_banner_text = $request->input('combo_banner_text');
        }

        $user->save();

        // Registro de Auditoría y Notificaciones si cambió la preferencia de WhatsApp
        if ($prefChanged) {
            $destLabel = match($newPref) {
                'provider' => 'WhatsApp de su Proveedor Patrocinador (' . ($user->parent ? $user->parent->name . ' - ' . $user->parent->support_whatsapp : 'Admin') . ')',
                'reseller' => 'WhatsApp Propio del Revendedor (' . ($user->support_whatsapp ?: 'Sin número') . ')',
                'admin' => 'WhatsApp Central del Super Admin',
                default => 'Modo Automático'
            };

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'update_whatsapp_support_preference',
                'description' => "El usuario {$user->name} actualizó su preferencia de atención por WhatsApp para Bóveda Clientes a: {$destLabel}.",
                'ip_address' => $request->ip(),
            ]);

            // Si el revendedor tiene proveedor patrocinador, registrar mensaje de aviso en la auditoría del proveedor
            if ($user->parent_id) {
                AuditLog::create([
                    'user_id' => $user->parent_id,
                    'action' => 'reseller_whatsapp_preference_change',
                    'description' => "Tu revendedor {$user->name} ha configurado la atención de consultas y renovaciones de Bóveda: {$destLabel}.",
                    'ip_address' => $request->ip(),
                ]);
            }
        }

        $msg = '¡Preferencias de panel actualizadas con éxito!';
        if ($prefChanged && $newPref === 'provider') {
            $msg .= ' Se ha solicitado y registrado que las consultas y renovaciones de Bóveda lleguen al WhatsApp de tu Proveedor Patrocinador.';
        } elseif ($prefChanged && $newPref === 'reseller') {
            $msg .= ' Las consultas y renovaciones de Bóveda llegarán directamente a tu número de WhatsApp.';
        }

        return back()->with('success', $msg);
    }
}
