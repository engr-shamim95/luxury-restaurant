<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SystemCommandController extends Controller
{
    public function index()
    {
        return view('admin.system');
    }

    public function runCommand(Request $request)
    {
        $command = $request->input('command');
        $allowed = [
            'cache:clear',
            'config:clear',
            'route:clear',
            'view:clear',
            'optimize:clear',
            'storage:link'
        ];

        if (in_array($command, $allowed)) {
            try {
                Artisan::call($command);
                return back()->with('success', "Command 'php artisan {$command}' executed successfully!");
            } catch (\Exception $e) {
                return back()->with('error', "Error executing command: " . $e->getMessage());
            }
        }

        return back()->with('error', 'Unauthorized command.');
    }
}
