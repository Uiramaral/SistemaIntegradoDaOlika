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

        // Inicializar impressora e configurar para máxima densidade
        $commands[] = "\x1B\x40"; // ESC @ - Reset
        $commands[] = "\x1B\x45\x01"; // ESC E 1 - Bold ON
        $commands[] = "\x1B\x47\x01"; // ESC G 1 - Double Strike ON
        $commands[] = "\x1D\x28\x4B\x02\x00\x31\x03"; // GS ( K - Densidade máxima

        $commands[] = "\x1B\x61\x01"; // Centralizar

        // Cabeçalho
        $commands[] = str_repeat("=", 48) . "\n";
        $commands[] = "\x1D\x21\x11"; // GS ! - Double size
        $commands[] = $this->removeAccents("OLIKA") . "\n";
        $commands[] = "\x1D\x21\x00"; // GS ! 0 - Normal size
        $commands[] = $this->removeAccents("PAES ARTESANAIS") . "\n";
        $commands[] = str_repeat("=", 48) . "\n\n";

        // Número do Pedido Grande
        $commands[] = "\x1D\x21\x11"; // GS ! - Double size
        $commands[] = "#" . $order->order_number . "\n";
        $commands[] = "\x1D\x21\x00"; // Normal size
        $commands[] = str_repeat("-", 48) . "\n";

        $commands[] = "\x1B\x61\x00"; // Alinhar à esquerda
        $commands[] = "DATA: " . $order->created_at->format('d/m/Y H:i:s') . "\n\n";

        // Dados do cliente
        if ($order->customer) {
            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $commands[] = "\x1D\x21\x01"; // Altura dupla
            $commands[] = $this->removeAccents($order->customer->name) . "\n";
            $commands[] = "\x1D\x21\x00"; // Normal size
            $commands[] = "\x1B\x45\x00"; // BOLD OFF
            if ($order->customer->phone) {
                $commands[] = "Tel: " . $order->customer->phone . "\n";
            }
            $commands[] = "\n";
        }

        // Endereço de entrega
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

            if ($order->scheduled_delivery_at) {
                $scheduledDate = \Carbon\Carbon::parse($order->scheduled_delivery_at);
                $commands[] = "\n";
                $commands[] = "\x1B\x45\x01"; // BOLD ON
                $commands[] = $this->removeAccents("AGENDADO: ") . $scheduledDate->format('d/m/Y') . " " . $scheduledDate->format('H:i') . "\n";
                $commands[] = "\x1B\x45\x00"; // BOLD OFF
            }
            $commands[] = "\n";
        }

        // Itens do pedido
        $commands[] = str_repeat("=", 48) . "\n";
        $commands[] = "\x1B\x45\x01"; // BOLD ON
        $commands[] = "  QTD  " . $this->removeAccents("DESCRIÇÃO") . str_pad("VALOR", 15, ' ', STR_PAD_LEFT) . "\n";
        $commands[] = "\x1B\x45\x00"; // BOLD OFF
        $commands[] = str_repeat("-", 48) . "\n";

        foreach ($order->items as $item) {
            $itemName = $item->custom_name ?? ($item->product ? $item->product->name : 'Produto');
            $variantName = ($item->variant_id && $item->variant) ? '(' . $item->variant->name . ')' : '';

            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $qty = str_pad((string) $item->quantity . "x ", 7, ' ', STR_PAD_RIGHT);
            $price = str_pad("R$ " . number_format($item->unit_price * $item->quantity, 2, ',', '.'), 12, ' ', STR_PAD_LEFT);

            // Primeira linha: Quantidade e Nome do produto (truncado)
            $nameLimit = 28;
            $commands[] = $qty . $this->truncateText($this->removeAccents($itemName), $nameLimit) . $price . "\n";
            $commands[] = "\x1B\x45\x00"; // BOLD OFF

            // Segunda linha: Variante e peso (se houver)
            if ($variantName) {
                $commands[] = "       " . $this->removeAccents($variantName) . "\n";
            }

            // Terceira linha: Instruções especiais
            if ($item->special_instructions) {
                $commands[] = "       " . "\x1B\x45\x01" . $this->removeAccents("Obs: ") . "\x1B\x45\x00";
                $commands[] = $this->wrapText($this->removeAccents($item->special_instructions), 40) . "\n";
            }
            $commands[] = "\n";
        }

        $commands[] = str_repeat("-", 48) . "\n";

        // Totais
        $subtotal = "R$ " . number_format($order->total_amount ?? 0, 2, ',', '.');
        $commands[] = str_pad($this->removeAccents("Subtotal:"), 32) . str_pad($subtotal, 16, ' ', STR_PAD_LEFT) . "\n";

        if ($order->delivery_fee > 0) {
            $deliveryFee = "R$ " . number_format($order->delivery_fee, 2, ',', '.');
            $commands[] = str_pad($this->removeAccents("Taxa de Entrega:"), 32) . str_pad($deliveryFee, 16, ' ', STR_PAD_LEFT) . "\n";
        }

        if ($order->discount_amount > 0) {
            $discount = "-R$ " . number_format($order->discount_amount, 2, ',', '.');
            $label = $order->coupon_code ? "Cupom (" . strtoupper($order->coupon_code) . "):" : "Desconto:";
            $commands[] = str_pad($this->removeAccents($label), 32) . str_pad($discount, 16, ' ', STR_PAD_LEFT) . "\n";
        }

        $commands[] = str_repeat("-", 48) . "\n";
        $finalAmount = "R$ " . number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.');
        $commands[] = "\x1B\x45\x01"; // BOLD ON
        $commands[] = "\x1D\x21\x01"; // Altura dupla
        $commands[] = str_pad($this->removeAccents("TOTAL DO PEDIDO:"), 30) . str_pad($finalAmount, 18, ' ', STR_PAD_LEFT) . "\n";
        $commands[] = "\x1D\x21\x00"; // Normal size
        $commands[] = "\x1B\x45\x00"; // BOLD OFF
        $commands[] = str_repeat("=", 48) . "\n\n";

        // Pagamento
        $commands[] = "\x1B\x45\x01"; // BOLD ON
        $paymentMethod = strtoupper(str_replace('_', ' ', $order->payment_method ?? 'PIX'));
        $commands[] = $this->removeAccents("FORMA DE PAGAMENTO: ") . $this->removeAccents($paymentMethod) . "\n";

        $paymentStatus = $order->payment_status ?? 'pending';
        $orderStatus = $order->status ?? 'pending';
        if ($orderStatus === 'confirmed' || in_array($paymentStatus, ['paid', 'approved'])) {
            $commands[] = "\x1D\x21\x01"; // Altura dupla
            $commands[] = "STATUS: PAGO\n";
        } else {
            $commands[] = "STATUS: PENDENTE / COBRAR\n";
        }
        $commands[] = "\x1D\x21\x00"; // Normal size
        $commands[] = "\x1B\x45\x00"; // BOLD OFF
        $commands[] = str_repeat("-", 48) . "\n\n";

        // Rodapé
        $commands[] = "\x1B\x61\x01"; // Centralizar
        $commands[] = $this->removeAccents("OBRIGADO PELA PREFERENCIA!") . "\n";
        $commands[] = "pedido.menuolika.com.br\n";
        $commands[] = "\n\n\n\n";
        $commands[] = "\x1D\x56\x41\x03"; // Cortar

        $output = '';
        foreach ($commands as $cmd) {
            $output .= $cmd;
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
     * Gera comandos ESC/POS para recibo de conferência (SEM PREÇOS)
     */
    public function generateCheckReceiptEscPos(Order $order): string
    {
        $order->load(['customer', 'items.product', 'items.variant']);

        $commands = [];

        // Inicializar impressora
        $commands[] = "\x1B\x40"; // ESC @ - Reset
        $commands[] = "\x1B\x45\x01"; // Bold ON
        $commands[] = "\x1B\x47\x01"; // Double Strike ON
        $commands[] = "\x1D\x28\x4B\x02\x00\x31\x03"; // Densidade máxima

        $commands[] = "\x1B\x61\x01"; // Centralizar

        // Cabeçalho
        $commands[] = str_repeat("=", 48) . "\n";
        $commands[] = "\x1D\x21\x11"; // Double size
        $commands[] = $this->removeAccents("RECIBO CONFERENCIA") . "\n";
        $commands[] = "\x1D\x21\x00"; // Normal size
        $commands[] = str_repeat("=", 48) . "\n\n";

        // Número do Pedido
        $commands[] = "\x1D\x21\x11"; // Double size
        $commands[] = "#" . $order->order_number . "\n";
        $commands[] = "\x1D\x21\x00"; // Normal size
        $commands[] = str_repeat("-", 48) . "\n";

        $commands[] = "\x1B\x61\x00"; // Alinhar à esquerda
        $commands[] = "DATA: " . $order->created_at->format('d/m/Y H:i:s') . "\n\n";

        // Cliente
        if ($order->customer) {
            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $commands[] = $this->removeAccents("CLIENTE:") . "\n";
            $commands[] = "\x1D\x21\x01"; // Altura dupla
            $commands[] = $this->removeAccents($order->customer->name) . "\n";
            $commands[] = "\x1D\x21\x00"; // Normal size
            $commands[] = "\x1B\x45\x00"; // BOLD OFF
            $commands[] = "\n";
        }

        // Itens
        $commands[] = str_repeat("=", 48) . "\n";
        $commands[] = "\x1B\x45\x01"; // BOLD ON
        $commands[] = "  QTD   " . $this->removeAccents("DESCRIÇÃO") . "\n";
        $commands[] = "\x1B\x45\x00"; // BOLD OFF
        $commands[] = str_repeat("-", 48) . "\n";

        foreach ($order->items as $item) {
            $itemName = $item->custom_name ?? ($item->product ? $item->product->name : 'Produto');
            $variantName = ($item->variant_id && $item->variant) ? '(' . $item->variant->name . ')' : '';

            $commands[] = "\x1B\x45\x01"; // BOLD ON
            $qty = str_pad((string) $item->quantity . "x ", 8, ' ', STR_PAD_RIGHT);

            $commands[] = $qty . $this->truncateText($this->removeAccents($itemName), 38) . "\n";
            $commands[] = "\x1B\x45\x00"; // BOLD OFF

            if ($variantName) {
                $commands[] = "        " . $this->removeAccents($variantName) . "\n";
            }

            if ($item->special_instructions) {
                $commands[] = "        " . "\x1B\x45\x01" . $this->removeAccents("Obs: ") . "\x1B\x45\x00";
                $commands[] = $this->wrapText($this->removeAccents($item->special_instructions), 40) . "\n";
            }
            $commands[] = "\n";
        }

        $commands[] = str_repeat("-", 48) . "\n";

        // Rodapé
        $commands[] = "\x1B\x61\x01"; // Centralizar
        $commands[] = "\x1B\x45\x01"; // BOLD ON
        $commands[] = $this->removeAccents("CONFERENCIA INTERNA") . "\n";
        $commands[] = "\x1B\x45\x00"; // BOLD OFF
        $commands[] = "\n\n\n\n";
        $commands[] = "\x1D\x56\x01"; // Cortar papel

        return implode('', $commands);
    }

    /**
     * Envia recibo para impressora via JavaScript (navegador)
     */
    public function sendToPrinter(Order $order, $printerType = 'thermal', $receiptType = 'normal'): array
    {
        try {
            if ($printerType === 'thermal') {
                // Gerar recibo baseado no tipo
                if ($receiptType === 'check') {
                    $commands = $this->generateCheckReceiptEscPos($order); // Recibo de conferência (sem preços)
                } else {
                    $commands = $this->generateEscPosReceipt($order); // Recibo fiscal (com preços)
                }

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
            'á' => 'a',
            'à' => 'a',
            'ã' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ó' => 'o',
            'ò' => 'o',
            'õ' => 'o',
            'ô' => 'o',
            'ö' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ç' => 'c',
            'Ç' => 'C',
            'Á' => 'A',
            'À' => 'A',
            'Ã' => 'A',
            'Â' => 'A',
            'Ä' => 'A',
            'É' => 'E',
            'È' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Í' => 'I',
            'Ì' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ó' => 'O',
            'Ò' => 'O',
            'Õ' => 'O',
            'Ô' => 'O',
            'Ö' => 'O',
            'Ú' => 'U',
            'Ù' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'ñ' => 'n',
            'Ñ' => 'N',
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
