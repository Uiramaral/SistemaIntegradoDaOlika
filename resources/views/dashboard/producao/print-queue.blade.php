<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fila de Impressão - {{ $date }}</title>
    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 210mm;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header .date {
            margin-top: 5px;
            font-size: 14px;
        }
        .summary {
            margin-bottom: 20px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .recipes-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .recipe-block {
            margin-bottom: 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            page-break-inside: avoid;
            background: #fff;
        }
        .recipe-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .recipe-info {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
            font-size: 12px;
        }
        .recipe-info strong {
            margin-right: 5px;
        }
        .ingredients-list {
            margin-top: 10px;
        }
        .ingredient-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 12px;
            border-bottom: 1px dotted #eee;
        }
        .ingredient-item:last-child {
            border-bottom: none;
        }
        .checkbox-wrapper {
            margin-right: 8px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
        }
        .ingredient-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            margin: 0;
            -webkit-appearance: checkbox;
            -moz-appearance: checkbox;
            appearance: checkbox;
            border: 1px solid #333;
            background: #fff;
        }
        @supports (-webkit-appearance: none) or (-moz-appearance: none) {
            .ingredient-checkbox {
                -webkit-appearance: checkbox;
                -moz-appearance: checkbox;
                appearance: checkbox;
            }
        }
        .ingredient-name {
            flex: 1;
        }
        .ingredient-weight {
            font-weight: bold;
            min-width: 80px;
            text-align: right;
        }
        .observation {
            margin-top: 10px;
            padding: 5px;
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            font-size: 11px;
            font-style: italic;
        }
        .no-print {
            margin-bottom: 20px;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
        }
        .no-print button {
            margin-right: 10px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .no-print button:hover {
            background: #0056b3;
        }
        @media print {
            @page {
                margin: 15mm;
            }
            body {
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
            .recipes-container {
                grid-template-columns: 1fr 1fr !important;
                gap: 15px !important;
            }
            .recipe-block {
                border: 1px solid #000 !important;
                background: #fff !important;
                page-break-inside: avoid;
            }
            .checkbox-wrapper {
                display: inline-block !important;
                margin-right: 8px !important;
                width: 18px !important;
                height: 18px !important;
                vertical-align: middle !important;
            }
            .ingredient-checkbox {
                width: 18px !important;
                height: 18px !important;
                -webkit-appearance: checkbox !important;
                -moz-appearance: checkbox !important;
                appearance: checkbox !important;
                display: block !important;
                margin: 0 !important;
                border: 1px solid #000 !important;
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .summary {
                background: #f5f5f5 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
        @media (max-width: 768px) {
            .recipes-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Imprimir</button>
        <button onclick="window.close()">❌ Fechar</button>
    </div>

    <div class="header">
        <h1>Fila de Impressão</h1>
        <div class="date">Data: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
    </div>

    <div class="summary">
        <strong>Total:</strong> {{ $totalRecipes }} receitas | 
        <strong>Levain:</strong> {{ number_format($totalLevain, 1, ',', '.') }}g
    </div>

    <div class="recipes-container">
    @foreach($recipes as $recipeData)
    <div class="recipe-block">
        <div class="recipe-header">
            {{ $recipeData['recipe_name'] }}
        </div>
        
        <div class="recipe-info">
            <div>
                <strong>Qtd:</strong> {{ $recipeData['quantity'] }}
            </div>
            <div>
                <strong>Peso:</strong> {{ number_format($recipeData['weight'], 0, ',', '.') }}g por un.
            </div>
        </div>

        @if($recipeData['observation'])
        <div class="observation">
            <strong>Obs:</strong> {{ $recipeData['observation'] }}
        </div>
        @endif

        <div class="ingredients-list">
            @php
                $ingredientsList = $recipeData['ingredients'] ?? [];
                \Log::info('Exibindo ingredientes na view', [
                    'recipe_name' => $recipeData['recipe_name'] ?? 'N/A',
                    'ingredients_count' => count($ingredientsList),
                    'ingredients' => array_map(function($ing) {
                        return [
                            'name' => $ing['ingredient']->name ?? 'Sem nome',
                            'weight' => $ing['weight'] ?? 0
                        ];
                    }, $ingredientsList)
                ]);
            @endphp
            @forelse($ingredientsList as $ing)
            <div class="ingredient-item">
                <span class="checkbox-wrapper">
                    <input type="checkbox" class="ingredient-checkbox" />
                </span>
                <span class="ingredient-name">{{ $ing['ingredient']->name ?? 'Ingrediente sem nome' }}</span>
                <span class="ingredient-weight">{{ number_format($ing['weight'] ?? 0, 1, ',', '.') }}g</span>
            </div>
            @empty
            <div class="ingredient-item" style="color: #999; font-style: italic;">
                <span class="ingredient-name">Nenhum ingrediente encontrado</span>
                <span class="ingredient-weight">-</span>
            </div>
            @endforelse
        </div>
        
        @if($recipeData['recipe']->include_notes_in_print && $recipeData['recipe']->notes)
        <div class="observation" style="margin-top: 10px;">
            <strong>Notas da Receita:</strong> {{ $recipeData['recipe']->notes }}
        </div>
        @endif
    </div>
    @endforeach
    </div>

    @if(count($recipes) === 0)
    <div style="text-align: center; padding: 40px; color: #999;">
        <p>Nenhuma receita na fila de impressão</p>
    </div>
    @endif
</body>
</html>
