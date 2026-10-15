<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Customers/Index', ['customers' => Customer::query()->orderBy('name')->get()->map(fn (Customer $customer): array => $this->transform($customer))]);
    }

    public function create(): Response
    {
        return Inertia::render('Customers/Create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::query()->create($request->validated());

        return to_route('customers.index');
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Edit', ['customer' => $this->transform($customer)]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return to_route('customers.index');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return to_route('customers.index');
    }

    private function transform(Customer $customer): array
    {
        return ['id' => $customer->id, 'name' => $customer->name, 'description' => $customer->description];
    }
}
