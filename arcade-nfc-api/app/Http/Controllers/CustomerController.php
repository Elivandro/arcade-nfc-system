<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class CustomerController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document' => ['required', 'string', 'max:255', 'unique:customers,document'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
        ]);

        $customer = Customer::create($validated);

        return redirect()->back()->with('status_cadastro', [
            'type' => 'success',
            'message' => 'Cliente ' . $customer->name . ' cadastrado com sucesso.',
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document' => ['required', 'string', 'max:255', Rule::unique('customers', 'document')->ignore($customer->id)],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer->id)],
        ]);

        $customer->update($validated);

        return redirect()->back()->with('status_cadastro', [
            'type' => 'success',
            'message' => 'Cadastro de ' . $customer->name . ' atualizado com sucesso.',
        ]);
    }

    public function nfcRegister(Customer $customer): RedirectResponse
    {
        Cache::put('cadastro:espera:cliente', $customer->id, 120);

        return redirect()->back()->with('status_cadastro', [
            'type' => 'success',
            'message' => 'Modo captura ativado para ' . $customer->name . '! Aproxime o novo cartão em qualquer fliperama nos próximos 2 minutos.'
        ]);
    }
}
