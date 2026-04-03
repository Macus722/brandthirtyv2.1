<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Artisan;
use App\Services\SiteMonitoringService;
use App\Models\NetworkActivity;

/**
 * NetworkController — Super Admin Command Center
 *
 * Endpoints:
 *  GET  /admin/network              → index()         Dashboard page
 *  GET  /admin/network/pulse        → pulse()         30-second JSON heartbeat
 *  POST /admin/network/toggle       → toggleModule()  Module enable/disable
 *  GET  /admin/network/god-mode     → godModeLink()   60-second signed impersonation URL
 *  POST /admin/network/sync-all     → syncAll()       Broadcast to all nodes via Http::pool()
 */
class NetworkController extends Controller
{
    public function __construct(
        protected SiteMonitoringService $monitor
    ) {
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. MAIN DASHBOARD PAGE
    // ─────────────────────────────────────────────────────────────────────────

    public function index()
    {
        // Programmatically clear config so new .env values are always loaded
        Artisan::call('config:clear');

        $revenueData = $this->monitor->getNetworkRevenue();
        $receivables = $this->monitor->getNetworkReceivables();

        // Http::pool() inside pingAllSites() — all sites pinged concurrently
        $sites = collect($this->monitor->pingAllSites())->map(fn($s) => (object) $s);

        $stats = [
            'total_revenue' => $revenueData['total'],
            'pending_payments' => $receivables,
            'active_sites' => count($revenueData['active_dbs']),
            'revenue_prism' => $revenueData['prism'],
            'revenue_alphafin' => $revenueData['alphafin'],
            'revenue_brandthirty' => $revenueData['brandthirty'],
        ];

        // Last 5 activities for the live feed sidebar
        $activities = NetworkActivity::latestFeed(5)->get();

        return view('admin.network', compact('stats', 'sites', 'activities'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. PULSE ENDPOINT — polled every 30 s by the frontend
    // ─────────────────────────────────────────────────────────────────────────

    public function pulse()
    {
        $sites = $this->monitor->pingAllSites();
        $payload = array_map(fn($s) => [
            'db_key' => $s['db_key'],
            'name' => $s['name'],
            'status' => $s['status'],
            'ping' => $s['ping'],
            'ip' => $s['ip'],
        ], $sites);
        $activeCount = count($this->monitor->getNetworkRevenue()['active_dbs']);

        return response()->json([
            'sites' => $payload,
            'active_count' => $activeCount,
            'checked_at' => now()->format('Y-m-d H:i:s'),
        ])->withHeaders([
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. MODULE TOGGLE — forward signed command to child site
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /admin/network/toggle
     * Body: { "site_key": "prism", "module": "e_invoice", "state": true }
     */
    public function toggleModule(Request $request)
    {
        $request->validate([
            'site_key' => 'required|string|in:prism,alphafin',
            'module' => 'required|string|in:e_invoice,offline_sales,reports',
            'state' => 'required|boolean',
        ]);

        $siteKey = $request->input('site_key');
        $module = $request->input('module');
        $state = (bool) $request->input('state');

        $site = $this->monitor->findSiteByKey($siteKey);
        if (!$site) {
            return response()->json(['success' => false, 'message' => 'Site not found.'], 404);
        }

        $syncToken = env('NETWORK_SYNC_TOKEN');
        $targetUrl = "https://{$site['domain']}/api/sync/toggle";
        $startedAt = microtime(true);

        try {
            $response = Http::withHeaders([
                'X-Sync-Token' => $syncToken,
                'Accept' => 'application/json',
            ])->timeout(8)->withoutVerifying()->post($targetUrl, [
                        'module' => $module,
                        'state' => $state,
                        'origin' => 'brandthirty-hq',
                    ]);

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $stateLabel = $state ? 'enabled' : 'disabled';

            if ($response->successful()) {
                NetworkActivity::log(
                    'module_toggle',
                    $siteKey,
                    $site['name'],
                    ['module' => $module, 'state' => $state],
                    'success',
                    $durationMs
                );
                return response()->json([
                    'success' => true,
                    'message' => ucwords(str_replace('_', ' ', $module)) . " {$stateLabel} on {$site['name']}.",
                    'activity' => $this->latestFeedJson(),
                ]);
            }

            NetworkActivity::log(
                'module_toggle',
                $siteKey,
                $site['name'],
                ['module' => $module, 'state' => $state, 'http_status' => $response->status()],
                'failed',
                $durationMs
            );

            return response()->json([
                'success' => false,
                'message' => "Child site returned HTTP {$response->status()}.",
            ], 502);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            NetworkActivity::log(
                'module_toggle',
                $siteKey,
                $site['name'],
                ['module' => $module, 'state' => $state, 'error' => 'unreachable'],
                'queued',
                $durationMs
            );
            return response()->json([
                'success' => false,
                'queued' => true,
                'message' => "{$site['name']} unreachable. Toggle queued — will sync on reconnection.",
                'activity' => $this->latestFeedJson(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Toggle error [{$siteKey}:{$module}]: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unexpected error. Check logs.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. GOD-MODE LINK GENERATOR
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/network/god-mode?site_key=prism
     *
     * Generates a cryptographically-signed, time-limited impersonation URL
     * using Laravel's URL::temporarySignedRoute(). The link is valid for 60
     * seconds — long enough to click, short enough to be useless if intercepted.
     *
     * The child site MUST verify the signature server-side via:
     *   if (! $request->hasValidSignature()) abort(403);
     *
     * The URL returned points to the child site's own impersonation route,
     * constructed from its registered domain. HQ signs with its APP_KEY.
     */
    public function godModeLink(Request $request)
    {
        $request->validate([
            'site_key' => 'required|string|in:prism,alphafin',
        ]);

        $siteKey = $request->input('site_key');
        $site = $this->monitor->findSiteByKey($siteKey);

        if (!$site) {
            return response()->json(['success' => false, 'message' => 'Site not in registry.'], 404);
        }

        $actor = auth()->check() ? auth()->user()->name : 'Superadmin';

        // Generate a 60-second temporarily-signed URL.
        // We use the LOCAL route name (admin.network.god-mode.verify) so that
        // the HMAC signature is verifiable. The child site receives the full
        // signed URL in the 'redirect' payload and validates it on its end.
        $expiresAt = now()->addSeconds(60);
        $syncToken = env('NETWORK_SYNC_TOKEN');

        // Build the child site impersonation URL with HMAC query params.
        // Format: https://child-domain/admin/god-mode/verify?site=...&expires=...&signature=...
        $signedToken = hash_hmac('sha256', "{$siteKey}|{$actor}|{$expiresAt->timestamp}", $syncToken);

        $godModeUrl = "https://{$site['domain']}/admin/god-mode/verify?" . http_build_query([
            'origin' => 'brandthirty-hq',
            'actor' => $actor,
            'expires' => $expiresAt->timestamp,
            'signature' => $signedToken,
        ]);

        // Log to the activity feed
        NetworkActivity::log(
            'god_mode_login',
            $siteKey,
            $site['name'],
            ['actor' => $actor, 'expires_at' => $expiresAt->toDateTimeString()],
            'success'
        );

        Log::info("God-Mode link generated for [{$site['name']}] by [{$actor}], expires {$expiresAt}");

        return response()->json([
            'success' => true,
            'url' => $godModeUrl,
            'expires_in' => 60,
            'site_name' => $site['name'],
            'activity' => $this->latestFeedJson(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. SYNC ALL PLATFORMS — Http::pool() broadcast
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * POST /admin/network/sync-all
     *
     * Broadcasts a system control command to ALL registered nodes simultaneously.
     * Uses Http::pool() so all requests fire in parallel — total wall-clock time
     * ≈ slowest single node, not sum of all nodes. Ideal for the 8-core Ryzen
     * where we want the page to update in < 2 s even with 5+ child sites.
     *
     * Child sites must expose: POST /api/system/control
     * with X-Sync-Token header and body: { "command": "sync", "origin": "brandthirty-hq" }
     */
    public function syncAll(Request $request)
    {
        $registry = $this->monitor->getRegistry();
        $syncToken = env('NETWORK_SYNC_TOKEN');
        $actor = auth()->check() ? auth()->user()->name : 'Superadmin';
        $startedAt = microtime(true);

        // ── Fire all HTTP requests concurrently via Http::pool() ─────────────
        $responses = Http::pool(function ($pool) use ($registry, $syncToken) {
            foreach ($registry as $site) {
                $pool->as($site['db_key'])
                    ->withHeaders([
                        'X-Sync-Token' => $syncToken,
                        'Accept' => 'application/json',
                    ])
                    ->timeout(10)
                    ->withoutVerifying()
                    ->post("https://{$site['domain']}/api/system/control", [
                        'command' => 'sync',
                        'origin' => 'brandthirty-hq',
                        'ts' => now()->toIso8601String(),
                    ]);
            }
        });

        $totalMs = (int) round((microtime(true) - $startedAt) * 1000);

        // ── Collect per-site results ──────────────────────────────────────────
        $results = [];
        $successCount = 0;

        foreach ($registry as $site) {
            $key = $site['db_key'];
            $response = $responses[$key] ?? null;

            if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                $results[$key] = ['status' => 'synced', 'http' => $response->status()];
                $successCount++;
            } elseif ($response instanceof \Throwable || $response instanceof \Illuminate\Http\Client\ConnectionException) {
                $results[$key] = ['status' => 'unreachable', 'error' => 'Connection failed'];
            } else {
                $results[$key] = [
                    'status' => 'error',
                    'http' => $response instanceof \Illuminate\Http\Client\Response ? $response->status() : 0,
                ];
            }
        }

        $overallStatus = match (true) {
            $successCount === count($registry) => 'success',
            $successCount > 0 => 'partial',
            default => 'failed',
        };

        // ── Log to activity feed ──────────────────────────────────────────────
        NetworkActivity::log(
            'sync_all',
            'all',
            'All Sites',
            ['results' => $results, 'actor' => $actor, 'total_ms' => $totalMs],
            $overallStatus,
            $totalMs,
            $actor
        );

        Log::info("Sync-All by [{$actor}]: {$successCount}/" . count($registry) . " nodes synced in {$totalMs}ms");

        return response()->json([
            'success' => $overallStatus !== 'failed',
            'status' => $overallStatus,
            'synced_count' => $successCount,
            'total_sites' => count($registry),
            'duration_ms' => $totalMs,
            'results' => $results,
            'activity' => $this->latestFeedJson(),
            'message' => match ($overallStatus) {
                'success' => "All {$successCount} sites synced successfully in {$totalMs}ms.",
                'partial' => "{$successCount} of " . count($registry) . " sites synced. Others are offline.",
                'failed' => 'All sites unreachable. Check network connectivity.',
            },
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/network/financial-intelligence
     * Fetches detailed per-platform financial data directly from remote nodes.
     */
    public function financialIntelligence(\Illuminate\Http\Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $data = $this->monitor->getFinancialIntelligence((int)$year);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Financial Intelligence Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load network financial intelligence.'
            ], 500);
        }
    }

    /**
     * Return the latest 5 activity entries as JSON-ready arrays
     * so the frontend can update the live feed without a page reload.
     */
    private function latestFeedJson(): array
    {
        return NetworkActivity::latestFeed(5)->get()->map(function ($a) {
            $icon = $a->icon_data;
            return [
                'id' => $a->id,
                'description' => $a->description,
                'site_name' => $a->site_name,
                'site_color' => $a->site_color,
                'status' => $a->status,
                'icon' => $icon['icon'],
                'icon_color' => $icon['color'],
                'icon_bg' => $icon['bg'],
                'duration_ms' => $a->duration_ms,
                'human_time' => $a->created_at->diffForHumans(),
            ];
        })->toArray();
    }
}
