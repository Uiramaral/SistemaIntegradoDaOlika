<?php

namespace App\Observers;

use App\Models\CustomerDebt;
use App\Models\Customer;

class CustomerDebtObserver
{
    /**
     * Atualizar saldo de débitos do cliente após criar um débito
     */
    public function created(CustomerDebt $debt)
    {
        $this->updateCustomerDebtsBalance($debt->customer_id);
    }

    /**
     * Atualizar saldo de débitos do cliente após atualizar um débito
     */
    public function updated(CustomerDebt $debt)
    {
        $this->updateCustomerDebtsBalance($debt->customer_id);
    }

    /**
     * Atualizar saldo de débitos do cliente após deletar um débito
     */
    public function deleted(CustomerDebt $debt)
    {
        $this->updateCustomerDebtsBalance($debt->customer_id);
    }

    /**
     * Atualizar o campo total_debts na tabela customers
     */
    private function updateCustomerDebtsBalance($customerId)
    {
        if (!$customerId) {
            return;
        }

        $balance = CustomerDebt::getBalance($customerId);
        
        Customer::where('id', $customerId)->update([
            'total_debts' => $balance
        ]);
    }
}

