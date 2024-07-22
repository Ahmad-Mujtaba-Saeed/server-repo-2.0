<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class EnvController extends Controller
{
    public function updateDatabaseName(Request $request)
    {
        // Validate the request
        $request->validate([
            'database_name' => 'required|string',
        ]);

        $newDatabaseName = $request->input('database_name');

        // Update the .env file
        $this->setEnvValue('DB_DATABASE', $newDatabaseName);

        // Clear the configuration cache
        Artisan::call('config:cache');

        return response()->json(['message' => 'Database name updated successfully']);
    }

    protected function setEnvValue($key, $value)
    {
        $path = base_path('.env');
        
        if (file_exists($path)) {
            file_put_contents($path, preg_replace(
                '/^' . preg_quote($key) . '=.*/m',
                $key . '=' . $value,
                file_get_contents($path)
            ));
        }
    }
}

