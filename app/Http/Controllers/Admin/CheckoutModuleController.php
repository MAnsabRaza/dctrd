<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckoutModule;
use App\Models\CheckoutModuleTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutModuleController extends Controller
{
    /**
     * METHOD 1: index()
     *
     * Saare checkout modules list mein dikhata hai.
     * URL: GET /admin/checkout-modules
     */
    public function index()
    {
        $modules = CheckoutModule::orderBy('order_index', 'asc')->paginate(20);

        return view('admin.checkout_modules.index', [
            'pageTitle'            => trans('admin/checkout_modules.list_title'),
            'modules'              => $modules,
            'editModule'           => null,
            'inputTypes'           => $this->getInputTypes(),
            'locales'              => $this->getSupportedLocales(),
            'translationsByLocale' => collect(),
        ]);
    }

    // =========================================================

    /**
     * METHOD 2: create()
     *
     * Naya module banane ka form dikhata hai.
     * URL: GET /admin/checkout-modules/create
     */
    public function create()
    {
        $modules = CheckoutModule::orderBy('order_index', 'asc')->paginate(20);

        return view('admin.checkout_modules.index', [
            'pageTitle'            => trans('admin/checkout_modules.create_title'),
            'modules'              => $modules,
            'editModule'           => null,
            'inputTypes'           => $this->getInputTypes(),
            'locales'              => $this->getSupportedLocales(),
            'translationsByLocale' => collect(),
        ]);
    }

    // =========================================================

    /**
     * METHOD 3: store()
     *
     * Naya module database mein save karta hai.
     * URL: POST /admin/checkout-modules
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:checkout_modules,name',
            'input_type'  => 'required|string|in:' . implode(',', array_keys($this->getInputTypes())),
            'config'      => 'nullable|json',
            'price_rule'  => 'nullable|json',
            'order_index' => 'required|integer|min:0',
            'is_active'   => 'boolean',
            'is_required' => 'boolean',
            // Translations validation
            'translations'          => 'nullable|array',
            'translations.*.locale' => 'required|string|max:10',
            'translations.*.label'  => 'required|string|max:255',
            'translations.*.help_text' => 'nullable|string',
        ], [
            'name.unique'      => trans('admin/checkout_modules.name_already_exists'),
            'input_type.in'    => trans('admin/checkout_modules.invalid_input_type'),
            'config.json'      => trans('admin/checkout_modules.invalid_json'),
            'price_rule.json'  => trans('admin/checkout_modules.invalid_json'),
        ]);

        DB::beginTransaction();

        try {
            // Module save karo
            $module = CheckoutModule::create([
                'name'        => $request->name,
                'input_type'  => $request->input_type,
                'config'      => $request->config ? json_decode($request->config, true) : null,
                'price_rule'  => $request->price_rule ? json_decode($request->price_rule, true) : null,
                'order_index' => $request->order_index,
                'is_active'   => $request->boolean('is_active', true),
                'is_required' => $request->boolean('is_required', false),
            ]);

            // Translations save karo
            if ($request->filled('translations')) {
                foreach ($request->translations as $translation) {
                    if (!empty($translation['label'])) {
                        CheckoutModuleTranslation::create([
                            'module_id' => $module->id,
                            'locale'    => $translation['locale'],
                            'label'     => $translation['label'],
                            'help_text' => $translation['help_text'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.checkout-modules.index')
                ->with('success', trans('admin/checkout_modules.created_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', trans('admin/checkout_modules.create_failed') . ': ' . $e->getMessage());
        }
    }

    // =========================================================

    /**
     * METHOD 4: edit()
     *
     * Existing module edit karne ka form dikhata hai.
     * URL: GET /admin/checkout-modules/{id}/edit
     */
    public function edit(int $id)
    {
        $editModule = CheckoutModule::with('translations')->findOrFail($id);
        $modules    = CheckoutModule::orderBy('order_index', 'asc')->paginate(20);

        // Translations ko locale => data format mein convert karo
        $translationsByLocale = $editModule->translations->keyBy('locale');

        // Category pattern — same index view pe wapas jao, editModule pass karo
        return view('admin.checkout_modules.index', [
            'pageTitle'            => trans('admin/checkout_modules.edit_title'),
            'modules'              => $modules,
            'editModule'           => $editModule,
            'inputTypes'           => $this->getInputTypes(),
            'locales'              => $this->getSupportedLocales(),
            'translationsByLocale' => $translationsByLocale,
        ]);
    }

    // =========================================================

    /**
     * METHOD 4.5: show()
     *
     * Redirect pure show URL to edit so old links or manual hits do not break.
     */
    public function show(int $id)
    {
        return redirect()->route('admin.checkout-modules.edit', ['checkout_module' => $id]);
    }

    // =========================================================

    /**
     * METHOD 5: update()
     *
     * Existing module ka data update karta hai.
     * URL: PUT /admin/checkout-modules/{id}
     */
    public function update(Request $request, int $id)
    {
        $module = CheckoutModule::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:100|unique:checkout_modules,name,' . $id,
            'input_type'  => 'required|string|in:' . implode(',', array_keys($this->getInputTypes())),
            'config'      => 'nullable|json',
            'price_rule'  => 'nullable|json',
            'order_index' => 'required|integer|min:0',
            'is_active'   => 'boolean',
            'is_required' => 'boolean',
            'translations'             => 'nullable|array',
            'translations.*.locale'    => 'required|string|max:10',
            'translations.*.label'     => 'required|string|max:255',
            'translations.*.help_text' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Module update karo
            $module->update([
                'name'        => $request->name,
                'input_type'  => $request->input_type,
                'config'      => $request->config ? json_decode($request->config, true) : null,
                'price_rule'  => $request->price_rule ? json_decode($request->price_rule, true) : null,
                'order_index' => $request->order_index,
                'is_active'   => $request->boolean('is_active', true),
                'is_required' => $request->boolean('is_required', false),
            ]);

            // Translations update karo — pehle delete phir re-insert
            if ($request->filled('translations')) {
                CheckoutModuleTranslation::where('module_id', $module->id)->delete();

                foreach ($request->translations as $translation) {
                    if (!empty($translation['label'])) {
                        CheckoutModuleTranslation::create([
                            'module_id' => $module->id,
                            'locale'    => $translation['locale'],
                            'label'     => $translation['label'],
                            'help_text' => $translation['help_text'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.checkout-modules.index')
                ->with('success', trans('admin/checkout_modules.updated_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', trans('admin/checkout_modules.update_failed') . ': ' . $e->getMessage());
        }
    }

    // =========================================================

    /**
     * METHOD 6: destroy()
     *
     * Module delete karta hai.
     * Agar module kisi order_meta mein use ho raha hai toh delete nahi hoga.
     * URL: DELETE /admin/checkout-modules/{id}
     */
    public function destroy(int $id)
    {
        $module = CheckoutModule::findOrFail($id);

        // Check karo — koi order is module ka data use kar raha hai?
        $inUse = DB::table('order_meta')
            ->where('key', $module->name)
            ->exists();

        if ($inUse) {
            return back()->with(
                'error',
                trans('admin/checkout_modules.cannot_delete_in_use', [
                    'name' => $module->name
                ])
            );
        }

        try {
            // Translations bhi automatically delete hongi (cascade)
            $module->delete();

            return redirect()
                ->route('admin.checkout-modules.index')
                ->with('success', trans('admin/checkout_modules.deleted_successfully'));

        } catch (\Exception $e) {
            return back()->with(
                'error',
                trans('admin/checkout_modules.delete_failed') . ': ' . $e->getMessage()
            );
        }
    }

    // =========================================================

    /**
     * METHOD 7: toggle()
     *
     * Module ko active/inactive karta hai — AJAX se call hota hai.
     * URL: POST /admin/checkout-modules/{id}/toggle
     * Returns: JSON
     */
    public function toggle(int $id)
    {
        $module = CheckoutModule::findOrFail($id);

        // is_active flip karo
        $module->update([
            'is_active' => !$module->is_active,
        ]);

        return response()->json([
            'success'   => true,
            'is_active' => $module->is_active,
            'message'   => $module->is_active
                ? trans('admin/checkout_modules.module_activated')
                : trans('admin/checkout_modules.module_deactivated'),
        ]);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Supported input types ki list — dropdown ke liye
     */
    private function getInputTypes(): array
    {
        return [
            'date_range'    => trans('admin/checkout_modules.input_type_date_range'),
            'time_slot'     => trans('admin/checkout_modules.input_type_time_slot'),
            'select'        => trans('admin/checkout_modules.input_type_select'),
            'stepper'       => trans('admin/checkout_modules.input_type_stepper'),
            'checkbox_list' => trans('admin/checkout_modules.input_type_checkbox_list'),
            'info_checkbox' => trans('admin/checkout_modules.input_type_info_checkbox'),
            'textarea'      => trans('admin/checkout_modules.input_type_textarea'),
        ];
    }

    /**
     * Supported locales — translations form ke liye
     */
    private function getSupportedLocales(): array
    {
        return [
            'en' => 'English',
            'ar' => 'Arabic',
            'fr' => 'French',
            'es' => 'Spanish',
        ];
    }
}