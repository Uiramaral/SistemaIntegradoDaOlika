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
        $order->load(['customer', 'address', 'items.product', 'items.variant', 'payment']);
        
        $commands = [];
        
        // Inicializar impressora e configurar para impressão mais escura (50% mais escuro)
        $commands[] = "\x1B\x40"; // ESC @ - Reset
        $commands[] = "\x1B\x45\x01"; // ESC E 1 - Bold ON (escurece)
        $commands[] = "\x1B\x47\x01"; // ESC G 1 - Double Strike ON (escurece mais - sobrepõe linhas)
        $commands[] = "\x1B\x4D\x00"; // ESC M 0 - Font A (mais densa que Font B)
        // Comando adicional para aumentar densidade (EPSON TM-20X) - Aumentado para máximo (3 de 0-3)
        // Densidade: 0=normal, 1=leve, 2=média, 3=máxima (50% mais escuro)
        $commands[] = "\x1D\x28\x4B\x02\x00\x31\x03"; // GS ( K 02 00 31 03 - Densidade máxima (3 de 0-3)
        $commands[] = "\x1B\x61\x01"; // ESC a 1 - Centralizar
        
        // Cabeçalho - usando largura total de 80mm (48 caracteres)
        $commands[] = str_repeat("=", 48) . "\n";
        $commands[] = "\x1B\x61\x01"; // Centralizar
        $commands[] = "\x1D\x21\x11"; // GS ! 17 - Double width and height
        $commands[] = $this->removeAccents("OLIKA") . "\n";
        $commands[] = "\x1D\x21\x00"; // GS ! 0 - Normal size
        $commands[] = $this->removeAccents("PAES ARTESANAIS") . "\n";
        $commands[] = "\x1B\x61\x00"; // Alinhar à esquerda
        $commands[] = str_repeat("=", 48) . "\n";
        $commands[] = "\n";
        
        // Data e hora - usando largura total
        $commands[] = "DATA: " . $order->created_at->format('d/m/Y H:i:s') . "\n";
        $commands[] = "PEDIDO: #" . $order->order_number . "\n";
        $commands[] = str_repeat("-", 48) . "\n";
        $commands[] = "\n";
        
        // Dados do cliente - usando largura total (48 caracteres)
        if ($order->customer) {
            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $commands[] = $this->removeAccents("CLIENTE") . "\n";
            $commands[] = "\x1B\x45\x00"; // BOLD OFF
            $commands[] = $this->wrapText($this->removeAccents($order->customer->name), 46) . "\n";
            if ($order->customer->phone) {
                $commands[] = "TEL: " . $order->customer->phone . "\n";
            }
            $commands[] = "\n";
        }
        
        // Endereço de entrega - usando largura total (48 caracteres)
        if ($order->address) {
            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $commands[] = $this->removeAccents("ENTREGA") . "\n";
            $commands[] = "\x1B\x45\x00"; // BOLD OFF
            $street = $order->address->street ?? $order->address->address ?? '';
            $commands[] = $this->wrapText($this->removeAccents($street . ", " . $order->address->number), 46) . "\n";
            if ($order->address->complement) {
                $commands[] = $this->wrapText($this->removeAccents($order->address->complement), 46) . "\n";
            }
            $commands[] = $this->wrapText($this->removeAccents($order->address->neighborhood), 46) . "\n";
            $commands[] = $this->removeAccents($order->address->city) . " - " . $order->address->state . "\n";
            // Tentar CEP do address primeiro, depois do customer como fallback
            $cep = $order->address->cep ?? $order->address->zip_code ?? $order->customer->zip_code ?? '';
            if ($cep) {
                $commands[] = "CEP: " . $cep . "\n";
            }
            // Entrega agendada logo abaixo do endereço
            if ($order->scheduled_delivery_at) {
                $scheduledDate = \Carbon\Carbon::parse($order->scheduled_delivery_at);
                $commands[] = "\n";
                $commands[] = "\x1B\x45\x01"; // BOLD ON
                $commands[] = $this->removeAccents("ENTREGA AGENDADA: ") . $scheduledDate->format('d/m/Y') . " as " . $scheduledDate->format('H:i') . "\n";
                $commands[] = "\x1B\x45\x00"; // BOLD OFF
            }
            $commands[] = "\n";
        } elseif ($order->customer) {
            // Se não houver address, usar dados do customer
            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $commands[] = $this->removeAccents("ENTREGA") . "\n";
            $commands[] = "\x1B\x45\x00"; // BOLD OFF
            if ($order->customer->address) {
                $commands[] = $this->wrapText($this->removeAccents($order->customer->address), 46) . "\n";
            }
            if ($order->customer->neighborhood) {
                $commands[] = $this->wrapText($this->removeAccents($order->customer->neighborhood), 46) . "\n";
            }
            if ($order->customer->city && $order->customer->state) {
                $commands[] = $this->removeAccents($order->customer->city) . " - " . $order->customer->state . "\n";
            }
            if ($order->customer->zip_code) {
                $commands[] = "CEP: " . $order->customer->zip_code . "\n";
            }
            // Entrega agendada logo abaixo do endereço
            if ($order->scheduled_delivery_at) {
                $scheduledDate = \Carbon\Carbon::parse($order->scheduled_delivery_at);
                $commands[] = "\n";
                $commands[] = "\x1B\x45\x01"; // BOLD ON
                $commands[] = $this->removeAccents("ENTREGA AGENDADA: ") . $scheduledDate->format('d/m/Y') . " as " . $scheduledDate->format('H:i') . "\n";
                $commands[] = "\x1B\x45\x00"; // BOLD OFF
            }
            $commands[] = "\n";
        }
        
        // Itens do pedido - usando largura total (48 caracteres)
        // Formato: ITEM (até 28 chars) | QTD (4 chars) | VALOR (12 chars) = 48
        $commands[] = str_repeat("-", 48) . "\n";
        $commands[] = "\x1B\x45\x01"; // BOLD ON
        $commands[] = str_pad($this->removeAccents("ITEM"), 30) . str_pad("QTD", 6, ' ', STR_PAD_LEFT) . str_pad($this->removeAccents("VALOR"), 12, ' ', STR_PAD_LEFT) . "\n";
        $commands[] = "\x1B\x45\x00"; // BOLD OFF
        $commands[] = str_repeat("-", 48) . "\n";
        
        foreach ($order->items as $item) {
            $itemName = $item->custom_name ?? ($item->product ? $item->product->name : 'Produto');
            $variantName = null;
            $weight = null;
            
            // Carregar variante se existir
            if ($item->variant_id && $item->variant) {
                $variantName = $item->variant->name;
                $weight = $item->variant->weight_grams;
            }
            
            // Se não tem variante, usar peso do produto
            if (!$weight && $item->product) {
                $weight = $item->product->weight_grams;
            }
            
            // Montar nome completo: Nome + Variante (se houver) + Peso (se houver)
            $displayName = $itemName;
            if ($variantName) {
                $displayName .= ' (' . $variantName . ')';
            }
            if ($weight) {
                $displayName .= ' - ' . number_format($weight / 1000, 1, ',', '.') . 'kg';
            }
            
            $itemName = $this->truncateText($this->removeAccents($displayName), 28);
            $qty = str_pad((string)$item->quantity, 3, ' ', STR_PAD_LEFT);
            $price = str_pad("R$ " . number_format($item->unit_price, 2, ',', '.'), 12, ' ', STR_PAD_LEFT);
            
            $commands[] = str_pad($itemName, 30) . str_pad($qty, 6, ' ', STR_PAD_LEFT) . $price . "\n";
            
            // Se tiver observação, mostrar
            if ($item->special_instructions) {
                $commands[] = "  " . $this->removeAccents("Obs: ") . $this->wrapText($this->removeAccents($item->special_instructions), 42) . "\n";
            }
        }
        
        $commands[] = str_repeat("-", 48) . "\n";
        $commands[] = "\n";
        
        // Totais - formato igual à tela (justify-between)
        $subtotal = number_format($order->total_amount ?? 0, 2, ',', '.');
        $labelSubtotal = $this->removeAccents("SUBTOTAL") . ":";
        $valueSubtotal = "R$ " . $subtotal;
        $commands[] = str_pad($labelSubtotal, 32) . str_pad($valueSubtotal, 16, ' ', STR_PAD_LEFT) . "\n";
        
        if ($order->delivery_fee > 0) {
            $deliveryFee = number_format($order->delivery_fee, 2, ',', '.');
            $labelEntrega = $this->removeAccents("ENTREGA") . ":";
            $valueEntrega = "R$ " . $deliveryFee;
            $commands[] = str_pad($labelEntrega, 32) . str_pad($valueEntrega, 16, ' ', STR_PAD_LEFT) . "\n";
        }
        
        if ($order->discount_amount > 0) {
            $discount = number_format($order->discount_amount, 2, ',', '.');
            
            // Formato igual à tela: apenas "CUPOM CODIGO:" sem percentual
            $discountLabel = $this->removeAccents("DESCONTO");
            if ($order->coupon_code) {
                $couponCodeClean = $this->removeAccents(strtoupper($order->coupon_code));
                $discountLabel = $this->removeAccents("CUPOM") . " " . $couponCodeClean;
            } elseif ($order->manual_discount_type) {
                $discountType = strtoupper($order->manual_discount_type === 'percentage' ? 'PERCENTUAL' : 'FIXO');
                $discountLabel = $this->removeAccents("DESCONTO") . " " . $this->removeAccents($discountType);
            }
            
            $valueDiscount = "-R$ " . $discount;
            $commands[] = str_pad($discountLabel . ":", 32) . str_pad($valueDiscount, 16, ' ', STR_PAD_LEFT) . "\n";
        }
        
        if ($order->cashback_used > 0) {
            $cashback = number_format($order->cashback_used, 2, ',', '.');
            $labelCashback = $this->removeAccents("CASHBACK UTILIZADO") . ":";
            $valueCashback = "-R$ " . $cashback;
            $commands[] = str_pad($labelCashback, 32) . str_pad($valueCashback, 16, ' ', STR_PAD_LEFT) . "\n";
        }
        
        // Total em destaque (igual à tela)
        $commands[] = str_repeat("-", 48) . "\n";
        $finalAmount = number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.');
        $commands[] = "\x1B\x45\x01"; // BOLD ON
        $labelTotal = $this->removeAccents("TOTAL") . ":";
        $valueTotal = "R$ " . $finalAmount;
        $commands[] = str_pad($labelTotal, 32) . str_pad($valueTotal, 16, ' ', STR_PAD_LEFT) . "\n";
        $commands[] = "\x1B\x45\x00"; // BOLD OFF
        $commands[] = str_repeat("-", 48) . "\n";
        $commands[] = "\n";
        
        // Forma de pagamento - usando largura total
        $commands[] = str_repeat("-", 48) . "\n";
        $commands[] = "\x1B\x45\x01"; // BOLD ON
        $commands[] = $this->removeAccents("PAGAMENTO") . "\n";
        $commands[] = "\x1B\x45\x00"; // BOLD OFF
        $paymentMethod = strtoupper(str_replace('_', ' ', $order->payment_method ?? 'PIX'));
        $commands[] = $this->removeAccents($paymentMethod) . "\n";
        
        $paymentStatus = $order->payment_status ?? 'pending';
        $orderStatus = $order->status ?? 'pending';
        
        // Se o status do pedido for "confirmed" e payment_status ainda não estiver pago, considerar como pago
        if ($orderStatus === 'confirmed' && ($paymentStatus === 'pending' || $paymentStatus === null)) {
            $paymentStatus = 'paid';
        }
        
        if ($paymentStatus === 'paid' || $paymentStatus === 'approved' || $orderStatus === 'confirmed') {
            $commands[] = $this->removeAccents("STATUS: PAGO") . "\n";
        } else {
            $commands[] = $this->removeAccents("STATUS: PENDENTE") . "\n";
        }
        $commands[] = str_repeat("-", 48) . "\n";
        $commands[] = "\n";
        
        // Observações do pedido - usando largura total
        if ($order->notes) {
            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $commands[] = $this->removeAccents("OBSERVACOES") . "\n";
            $commands[] = "\x1B\x45\x00"; // BOLD OFF
            $commands[] = $this->wrapText($this->removeAccents($order->notes), 46) . "\n";
            $commands[] = "\n";
        }
        
        // Rodapé
        $commands[] = "\n";
        $commands[] = str_repeat("=", 48) . "\n";
        $commands[] = "\x1B\x61\x01"; // Centralizar
        $commands[] = "\x1B\x45\x01"; // BOLD ON
        $commands[] = $this->removeAccents("OBRIGADO PELA PREFERENCIA!") . "\n";
        $commands[] = "\x1B\x45\x00"; // BOLD OFF
        $commands[] = "\n";
        $commands[] = "pedido.menuolika.com.br\n";
        $commands[] = "\n";
        $commands[] = $this->removeAccents("WhatsApp") . ":\n";
        
        // Buscar número do WhatsApp das configurações
        $settings = \App\Models\Setting::getSettings();
        $phone = $settings->business_phone ?? config('olika.business.phone', '(71) 98701-9420');
        $commands[] = $phone . "\n";
        
        $commands[] = "\n";
        $commands[] = str_repeat("=", 48) . "\n";
        $commands[] = "\n";
        
        // Avançar papel antes de cortar
        $commands[] = "\n\n"; // Avançar algumas linhas
        
        // Cortar papel
        $commands[] = "\x1D\x56\x41\x03"; // GS V A 3 - Cortar parcialmente (3mm)
        
        // Alternativa: Se a impressora não suportar corte parcial, usar avanço de papel
        // $commands[] = "\x1D\x56\x00"; // GS V 0 - Cortar totalmente
        // $commands[] = "\n\n\n"; // Avançar papel
        
        // Implodir comandos mantendo a integridade dos bytes binários
        // IMPORTANTE: Não fazer conversão de encoding - PHP trata strings como binárias
        $output = '';
        foreach ($commands as $cmd) {
            $output .= $cmd;
        }
        
        // DEBUG: Verificar se os primeiros bytes são ESC @ (0x1B 0x40)
        if (strlen($output) >= 2) {
            $firstByte = ord($output[0]);
            $secondByte = ord($output[1]);
            if ($firstByte !== 0x1B || $secondByte !== 0x40) {
                Log::warning('FiscalPrinterService: Comandos ESC/POS não começam com ESC @', [
                    'first_byte' => '0x' . dechex($firstByte),
                    'second_byte' => '0x' . dechex($secondByte),
                    'expected' => '0x1B 0x40',
                    'output_length' => strlen($output),
                    'first_4_bytes_hex' => bin2hex(substr($output, 0, 4))
                ]);
            } else {
                // Log removido - apenas manter em caso de erro
            }
        }
        
        return $output;
    }
    
    /**
     * Gera HTML otimizado para impressão em impressora térmica (80mm)
     */
    public function generateHtmlReceipt(Order $order): string
    {
        $order->load(['customer', 'address', 'items.product', 'items.variant', 'payment']);
        
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
                // Usar base64_encode que preserva os bytes binários corretamente
                // IMPORTANTE: Não fazer nenhuma conversão de encoding antes do base64
                $base64Data = base64_encode($commands);
                
                // Log removido - apenas manter em caso de erro
                
                return [
                    'success' => true,
                    'type' => 'escpos',
                    'data' => $base64Data,
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
        $text = $this->removeAccents($text);
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
     * Remove acentos e caracteres especiais
     */
    private function removeAccents(string $text): string
    {
        // Mapeamento de acentos e caracteres especiais
        $replacements = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'Ç' => 'C',
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ñ' => 'n', 'Ñ' => 'N',
        ];
        
        $text = strtr($text, $replacements);
        
        // Remover outros caracteres especiais não-ASCII, mantendo ASCII básico (32-126)
        $text = preg_replace('/[^\x20-\x7E]/', '', $text);
        
        return $text;
    }
    
    /**
     * Trunca texto para tamanho máximo
     */
    private function truncateText(string $text, int $maxLength): string
    {
        $text = $this->removeAccents($text);
        
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }
        
        return mb_substr($text, 0, $maxLength - 3) . '...';
    }
    
    /**
     * Gera QR Code em ESC/POS para EPSON TM-20X
     * Formato: GS ( k [pL] [pH] [cn] [fn] [n] [c1] [c2] [d1...dk]
     * Para EPSON TM-20X usa função 49 (QR Code)
     */
    private function generateQRCode(string $data): string
    {
        $dataLength = strlen($data);
        
        // Log para debug
        Log::info('FiscalPrinterService: generateQRCode chamado', [
            'data' => $data,
            'data_length' => $dataLength
        ]);
        
        // Calcular tamanho do pacote: 5 bytes de parâmetros (cn, fn, n, c1, c2) + dados
        $totalSize = $dataLength + 5;
        $pL = $totalSize & 0xFF; // Byte baixo do tamanho total
        $pH = ($totalSize >> 8) & 0xFF; // Byte alto do tamanho total
        
        // Log dos parâmetros
        Log::info('FiscalPrinterService: Parâmetros do QR code', [
            'total_size' => $totalSize,
            'pL' => $pL,
            'pH' => $pH
        ]);
        
        // Comando ESC/POS para QR Code (EPSON TM-20X)
        // Formato: GS ( k [pL] [pH] [cn] [fn] [n] [c1] [c2] [d1...dk]
        // Para EPSON TM-20X, vamos usar valores mais compatíveis
        
        // ABORDAGEM: Sintaxe padrão EPSON com valores mais compatíveis
        // cn = 49 (0x31) - função QR Code
        // fn = 65 (0x41) - Função A - armazenar na memória
        // n = 4 (0x04) - tamanho do módulo: 0-8 (4 = médio, mais compatível - REDUZIDO de 6 para 4)
        // c1 = 48 (0x30) - nível de correção: 48=L (Low, mais compatível), 49=M, 50=Q, 51=H
        // c2 = 0 (0x00) - parâmetro adicional
        
        // NOTA: Reduzido tamanho do módulo de 6 para 4 para evitar problemas de largura
        // Algumas impressoras podem recusar QR codes muito grandes
        
        // Passo 1: Armazenar QR code na memória
        // Usando tamanho 4 e correção L para máxima compatibilidade
        $storeCommand = "\x1D\x28\x6B" . chr($pL) . chr($pH) . "\x31\x41\x04\x30\x00" . $data;
        
        // Passo 2: Imprimir QR code da memória
        // GS ( k [03] [00] [49] [67] [m]
        // cn = 49 (0x31), fn = 67 (0x43 - Função C - imprimir), m = 2 (imprimir)
        $printCommand = "\x1D\x28\x6B\x03\x00\x31\x43\x02";
        
        // Montar saída com espaçamento adequado
        // IMPORTANTE: Sequência correta para EPSON TM-20X
        $output = "\n"; // Linha em branco antes do QR code
        
        // Centralizar ANTES de armazenar (não depois)
        $output .= "\x1B\x61\x01"; // ESC a 1 - Centralizar
        
        // CRÍTICO: Armazenar QR code primeiro
        $output .= $storeCommand; // Armazenar QR code na memória da impressora
        
        // CRÍTICO: Imprimir QR code imediatamente após armazenar (sem delay ou outros comandos)
        $output .= $printCommand; // Imprimir QR code da memória
        
        // Aguardar processamento (algumas impressoras precisam)
        $output .= "\n"; // Avançar uma linha após imprimir
        
        // Voltar alinhamento à esquerda APÓS imprimir
        $output .= "\x1B\x61\x00"; // ESC a 0 - Alinhar à esquerda
        
        $output .= "\n"; // Espaçamento final
        
        // Log detalhado para debug - COMPLETO
        Log::info('FiscalPrinterService: QR code gerado - DIAGNÓSTICO COMPLETO', [
            'data' => $data,
            'data_length' => $dataLength,
            'total_size' => $dataLength + 5,
            'pL' => $pL,
            'pH' => $pH,
            'store_command_length' => strlen($storeCommand),
            'print_command_length' => strlen($printCommand),
            'output_length' => strlen($output),
            // Hex completo de cada comando
            'store_command_hex_full' => bin2hex($storeCommand),
            'print_command_hex_full' => bin2hex($printCommand),
            'output_hex_full' => bin2hex($output),
            // Verificação de estrutura
            'store_starts_with' => bin2hex(substr($storeCommand, 0, 3)), // Deve ser 1d286b (GS ( k)
            'store_has_data' => strlen($storeCommand) > 8,
            'print_starts_with' => bin2hex(substr($printCommand, 0, 3)), // Deve ser 1d286b (GS ( k)
            // Verificação de parâmetros
            'module_size' => '4 (0x04) - REDUZIDO para compatibilidade',
            'error_correction' => 'L (0x30)',
            'function_store' => 'A (0x41)',
            'function_print' => 'C (0x43)',
            // Verificação de integridade dos dados
            'data_in_store_command' => substr($storeCommand, -$dataLength) === $data,
            'store_command_ends_with_data' => true,
        ]);
        
        return $output;
    }
}
