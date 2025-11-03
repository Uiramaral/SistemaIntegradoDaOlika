<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ToolsController extends Controller
{
    // GET /dashboard/tools/import-ingredients?url=...&create=1
    public function importIngredients(Request $request)
    {
        $this->middleware('auth');
        $url = $request->query('url');
        $create = $request->boolean('create', true);

        // Garante existência de categoria padrão antes do import
        $defaultCatName = 'Sem categoria';
        $defaultCatSlug = Str::slug($defaultCatName);
        $catId = DB::table('categories')->where('slug', $defaultCatSlug)->value('id');
        if (!$catId) {
            $existsByName = DB::table('categories')->whereRaw('LOWER(name)=?', [Str::lower($defaultCatName)])->value('id');
            if ($existsByName) {
                $catId = $existsByName;
            } else {
                $catId = DB::table('categories')->insertGetId([
                    'name' => $defaultCatName,
                    'slug' => $defaultCatSlug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $params = [];
        if ($url) { $params['url'] = $url; }
        $params['--create-products'] = $create ? '1' : '0';

        Artisan::call('ingredients:import-gist', $params);
        $output = Artisan::output();

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'output' => $output,
            ]);
        }
        return response('<pre>'.e($output).'</pre>', 200)->header('Content-Type','text/html');
    }

    // GET /dashboard/tools/flush
    public function flushCaches(Request $request)
    {
        $this->middleware('auth');

        $commands = [
            'view:clear',
            'route:clear',
            'cache:clear',
            'config:clear',
            'optimize:clear',
        ];

        $results = [];
        foreach ($commands as $cmd) {
            try {
                Artisan::call($cmd);
                $results[$cmd] = 'ok';
            } catch (\Throwable $e) {
                $results[$cmd] = 'error: '.$e->getMessage();
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'results' => $results]);
        }

        $lines = [];
        foreach ($results as $k => $v) { $lines[] = $k.' => '.$v; }
        return response("<pre>".e(implode("\n", $lines))."</pre>", 200)->header('Content-Type','text/html');
    }
}
