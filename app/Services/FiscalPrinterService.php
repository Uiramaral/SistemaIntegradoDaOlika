<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class FiscalPrinterService
{
    /**
     * Gera comandos ESC/POS para impressão fiscal do pedido
     */
    public function generateEscPosReceipt(Order $order): string
    {
        $order->load(['customer', 'address', 'items.product', 'payment']);
        
        $commands = [];
        
        // Inicializar impressora
        $commands[] = "\x1B\x40"; // ESC @ - Reset
        $commands[] = "\x1B\x61\x01"; // ESC a 1 - Centralizar
        
        // Cabeçalho
        $commands[] = "--------------------------------\n";
        $commands[] = "\x1B\x61\x01"; // Centralizar
        $commands[] = "OLIKA\n";
        $commands[] = "PAES ARTESANAIS\n";
        $commands[] = "\x1B\x61\x00"; // Alinhar à esquerda
        $commands[] = "--------------------------------\n";
        $commands[] = "\n";
        
        // Data e hora
        $commands[] = "DATA: " . $order->created_at->format('d/m/Y H:i:s') . "\n";
        $commands[] = "PEDIDO: #" . $order->order_number . "\n";
        $commands[] = "--------------------------------\n";
        $commands[] = "\n";
        
        // Dados do cliente
        if ($order->customer) {
            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $commands[] = "CLIENTE:\n";
            $commands[] = "\x1B\x45\x00"; // BOLD OFF
            $commands[] = $this->wrapText($order->customer->name, 32) . "\n";
            if ($order->customer->phone) {
                $commands[] = "TEL: " . $order->customer->phone . "\n";
            }
            $commands[] = "\n";
        }
        
        // Endereço de entrega
        if ($order->address) {
            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $commands[] = "ENTREGA:\n";
            $commands[] = "\x1B\x45\x00"; // BOLD OFF
            $street = $order->address->street ?? $order->address->address ?? '';
            $commands[] = $this->wrapText($street . ", " . $order->address->number, 32) . "\n";
            if ($order->address->complement) {
                $commands[] = $this->wrapText($order->address->complement, 32) . "\n";
            }
            $commands[] = $this->wrapText($order->address->neighborhood, 32) . "\n";
            $commands[] = $order->address->city . " - " . $order->address->state . "\n";
            // Tentar CEP do address primeiro, depois do customer como fallback
            $cep = $order->address->cep ?? $order->address->zip_code ?? $order->customer->zip_code ?? '';
            if ($cep) {
                $commands[] = "CEP: " . $cep . "\n";
            }
            $commands[] = "\n";
        } elseif ($order->customer) {
            // Se não houver address, usar dados do customer
            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $commands[] = "ENTREGA:\n";
            $commands[] = "\x1B\x45\x00"; // BOLD OFF
            if ($order->customer->address) {
                $commands[] = $this->wrapText($order->customer->address, 32) . "\n";
            }
            if ($order->customer->neighborhood) {
                $commands[] = $this->wrapText($order->customer->neighborhood, 32) . "\n";
            }
            if ($order->customer->city && $order->customer->state) {
                $commands[] = $order->customer->city . " - " . $order->customer->state . "\n";
            }
            if ($order->customer->zip_code) {
                $commands[] = "CEP: " . $order->customer->zip_code . "\n";
            }
            $commands[] = "\n";
        }
        
        // Itens do pedido
        $commands[] = "--------------------------------\n";
        $commands[] = "\x1B\x45\x01"; // BOLD ON
        $commands[] = "ITEM                QTD   VALOR\n";
        $commands[] = "\x1B\x45\x00"; // BOLD OFF
        $commands[] = "--------------------------------\n";
        
        foreach ($order->items as $item) {
            $itemName = $item->custom_name ?? ($item->product ? $item->product->name : 'Produto');
            $itemName = $this->truncateText($itemName, 18);
            $qty = str_pad((string)$item->quantity, 3, ' ', STR_PAD_LEFT);
            $price = str_pad("R$ " . number_format($item->unit_price, 2, ',', '.'), 9, ' ', STR_PAD_LEFT);
            
            $commands[] = sprintf("%-18s %s %s\n", $itemName, $qty, $price);
            
            // Se tiver observação, mostrar
            if ($item->special_instructions) {
                $commands[] = "  Obs: " . $this->wrapText($item->special_instructions, 26) . "\n";
            }
        }
        
        $commands[] = "--------------------------------\n";
        $commands[] = "\n";
        
        // Totais
        $commands[] = "\x1B\x61\x02"; // Alinhar à direita
        $subtotal = number_format($order->total_amount ?? 0, 2, ',', '.');
        $commands[] = "SUBTOTAL:    R$ " . str_pad($subtotal, 10, ' ', STR_PAD_LEFT) . "\n";
        
        if ($order->delivery_fee > 0) {
            $deliveryFee = number_format($order->delivery_fee, 2, ',', '.');
            $commands[] = "ENTREGA:     R$ " . str_pad($deliveryFee, 10, ' ', STR_PAD_LEFT) . "\n";
        }
        
        if ($order->discount_amount > 0) {
            $discount = number_format($order->discount_amount, 2, ',', '.');
            if ($order->coupon_code) {
                $commands[] = "CUPOM " . $order->coupon_code . ":\n";
            } elseif ($order->manual_discount_type) {
                $discountType = strtoupper($order->manual_discount_type === 'percentage' ? 'DESC. %' : 'DESC. FIXO');
                $commands[] = $discountType . ":\n";
            } else {
                $commands[] = "DESCONTO:\n";
            }
            $commands[] = "DESCONTO:   -R$ " . str_pad($discount, 10, ' ', STR_PAD_LEFT) . "\n";
        }
        
        if ($order->cashback_used > 0) {
            $cashback = number_format($order->cashback_used, 2, ',', '.');
            $commands[] = "CASHBACK:   -R$ " . str_pad($cashback, 10, ' ', STR_PAD_LEFT) . "\n";
        }
        
        $commands[] = "\n";
        $commands[] = "\x1B\x45\x01"; // BOLD ON
        $commands[] = "\x1B\x64\x01"; // DOUBLE WIDTH
        $finalAmount = number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.');
        $commands[] = "TOTAL: R$ " . $finalAmount . "\n";
        $commands[] = "\x1B\x64\x00"; // DOUBLE WIDTH OFF
        $commands[] = "\x1B\x45\x00"; // BOLD OFF
        $commands[] = "\x1B\x61\x00"; // Alinhar à esquerda
        $commands[] = "\n";
        
        // Entrega agendada
        if ($order->scheduled_delivery_at) {
            $scheduledDate = \Carbon\Carbon::parse($order->scheduled_delivery_at);
            $commands[] = "--------------------------------\n";
            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $commands[] = "ENTREGA AGENDADA:\n";
            $commands[] = "\x1B\x45\x00"; // BOLD OFF
            $commands[] = $scheduledDate->format('d/m/Y') . " as " . $scheduledDate->format('H:i') . "\n";
            $commands[] = "--------------------------------\n";
            $commands[] = "\n";
        }
        
        // Forma de pagamento
        $commands[] = "--------------------------------\n";
        $commands[] = "\x1B\x45\x01"; // BOLD ON
        $commands[] = "PAGAMENTO:\n";
        $commands[] = "\x1B\x45\x00"; // BOLD OFF
        $paymentMethod = strtoupper(str_replace('_', ' ', $order->payment_method ?? 'PIX'));
        $commands[] = $paymentMethod . "\n";
        
        $paymentStatus = $order->payment_status ?? 'pending';
        $orderStatus = $order->status ?? 'pending';
        
        // Se o status do pedido for "confirmed" e payment_status ainda não estiver pago, considerar como pago
        if ($orderStatus === 'confirmed' && ($paymentStatus === 'pending' || $paymentStatus === null)) {
            $paymentStatus = 'paid';
        }
        
        if ($paymentStatus === 'paid' || $paymentStatus === 'approved' || $orderStatus === 'confirmed') {
            $commands[] = "STATUS: PAGO\n";
        } else {
            $commands[] = "STATUS: PENDENTE\n";
        }
        $commands[] = "--------------------------------\n";
        $commands[] = "\n";
        
        // Observações do pedido
        if ($order->notes) {
            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $commands[] = "OBSERVACOES:\n";
            $commands[] = "\x1B\x45\x00"; // BOLD OFF
            $commands[] = $this->wrapText($order->notes, 32) . "\n";
            $commands[] = "\n";
        }
        
        // Rodapé
        $commands[] = "\n";
        $commands[] = "\x1B\x61\x01"; // Centralizar
        $commands[] = "OBRIGADO PELA PREFERENCIA!\n";
        $commands[] = "\n";
        $commands[] = "www.menuolika.com.br\n";
        $commands[] = "\n";
        $commands[] = "WhatsApp:\n";
        $commands[] = "(71) 98701-9420\n";
        $commands[] = "\n";
        $commands[] = "\n";
        $commands[] = "\n";
        
        // Cortar papel
        $commands[] = "\x1D\x56\x41\x03"; // GS V A - Cortar parcialmente (3mm)
        
        // Implodir comandos mantendo a integridade dos bytes binários
        // NÃO converter encoding pois isso pode corromper comandos ESC/POS
        return implode('', $commands);
    }
    
    /**
     * Gera HTML otimizado para impressão em impressora térmica (80mm)
     */
    public function generateHtmlReceipt(Order $order): string
    {
        $order->load(['customer', 'address', 'items.product', 'payment']);
        
        $html = view('dashboard.orders.fiscal-receipt', compact('order'))->render();
        
        return $html;
    }
    
    /**
     * Envia recibo para impressora via JavaScript (navegador)
     */
    public function sendToPrinter(Order $order, $printerType = 'thermal'): array
    {
        try {
            if ($printerType === 'thermal') {
                $commands = $this->generateEscPosReceipt($order);
                
                // Garantir que os comandos sejam uma string binária limpa
                // Não incluir 'raw' na resposta JSON pois pode causar problemas de serialização
                return [
                    'success' => true,
                    'type' => 'escpos',
                    'data' => base64_encode($commands),
                ];
            }
            
            return [
                'success' => true,
                'type' => 'html',
                'data' => $this->generateHtmlReceipt($order),
            ];
            
        } catch (\Exception $e) {
            Log::error('FiscalPrinterService: Erro ao gerar recibo', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Quebra texto em múltiplas linhas
     */
    private function wrapText(string $text, int $maxLength): string
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';
        
        foreach ($words as $word) {
            if (mb_strlen($currentLine . ' ' . $word) <= $maxLength) {
                $currentLine .= ($currentLine ? ' ' : '') . $word;
            } else {
                if ($currentLine) {
                    $lines[] = $currentLine;
                }
                $currentLine = $word;
            }
        }
        
        if ($currentLine) {
            $lines[] = $currentLine;
        }
        
        return implode("\n", $lines);
    }
    
    /**
     * Trunca texto para tamanho máximo
     */
    private function truncateText(string $text, int $maxLength): string
    {
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }
        
        return mb_substr($text, 0, $maxLength - 3) . '...';
    }
}
