<?php

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("You cannot access this file directly.");
}

add_hook('AfterModuleCreate', 1, function($vars) {
    logActivity("Hook AfterModuleCreate déclenché (service ID: {$vars['serviceid']})");
    updateStockBasedOnPtero();
});
add_hook('AfterModuleTerminate', 1, function($vars) {
    logActivity("Hook AfterModuleTerminate déclenché (service ID: {$vars['serviceid']})");
    updateStockBasedOnPtero();
});
add_hook('AfterModuleSuspend', 1, function($vars) {
    updateStockBasedOnPtero();
});
add_hook('AfterModuleUnsuspend', 1, function($vars) {
    updateStockBasedOnPtero();
});
add_hook('DailyCronJob', 1, function($vars) {
    updateStockBasedOnPtero();
});

function updateStockBasedOnPtero() {
    try {
        $pteroApiKey = 'YOUR_PTERO_TOKEN';
        $pteroBaseUrl = 'YOUR_PTERO_API_URL';

        $nodes = httpRequestPtero("$pteroBaseUrl/nodes", $pteroApiKey);
        if (!$nodes || !isset($nodes['data'])) {
            logActivity("Erreur lors de la récupération des nœuds Pterodactyl.");
            return;
        }

        $totalFreeRam = 0;

        foreach ($nodes['data'] as $node) {
            $attrs = $node['attributes'];
            $freeMemory = $attrs['memory'] - $attrs['allocated_resources']['memory'];
            $totalFreeRam += $freeMemory;
        }

        logActivity("RAM totale disponible : {$totalFreeRam} MB");

        $products = Capsule::table('tblproducts')
            ->where('stockcontrol', 1)
            ->get();

        foreach ($products as $product) {
            $pid = $product->id;
            $desc = $product->description;
            $ramRequired = extractRamFromDescription($desc);

            if ($ramRequired !== null && $ramRequired > 0) {
                $stock = floor($totalFreeRam / $ramRequired);
                $stock = max(0, $stock);

                Capsule::table('tblproducts')
                    ->where('id', $pid)
                    ->update([
                        'qty' => $stock,
                        'stockcontrol' => 1,
                    ]);

                logActivity("Stock mis à jour pour PID {$pid} → {$stock} unités (RAM requise : {$ramRequired} MB)");
            }
        }

    } catch (Exception $e) {
        logActivity("Erreur dans updateStockBasedOnPtero: " . $e->getMessage());
    }
}

function extractRamFromDescription($description) {
    $patterns = [
        '/(\d+)\s*(Go|GB|G)\b/i',
        '/(\d+)\s*(Mo|MB|M)\b/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $description, $matches)) {
            $value = (int)$matches[1];
            $unit = strtolower($matches[2]);

            if (in_array($unit, ['go', 'gb', 'g'])) {
                return $value * 1024;
            } else {
                return $value;
            }
        }
    }

    return null;
}

function httpRequestPtero($url, $apiKey) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $apiKey",
            "Accept: application/json"
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        logActivity("Erreur cURL Pterodactyl : $error");
        return null;
    }

    return json_decode($response, true);
}
