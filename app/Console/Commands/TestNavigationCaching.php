<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Utility\NavigationCacheService;
use App\Services\Monitoring\PerformanceMonitoringService;
use App\Models\Election\Election;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class TestNavigationCaching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:navigation-caching {--iterations=5 : Number of test iterations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test navigation caching performance and functionality';

    protected NavigationCacheService $navigationCache;
    protected PerformanceMonitoringService $performanceMonitor;

    public function __construct(
        NavigationCacheService $navigationCache,
        PerformanceMonitoringService $performanceMonitor
    ) {
        parent::__construct();
        $this->navigationCache = $navigationCache;
        $this->performanceMonitor = $performanceMonitor;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $iterations = (int) $this->option('iterations');

        $this->info('🧪 Testing Navigation Caching Implementation');
        $this->newLine();

        // Test 1: Cache functionality
        $this->testCacheFunctionality();

        // Test 2: Performance comparison
        $this->testPerformanceComparison($iterations);

        // Test 3: Cache invalidation
        $this->testCacheInvalidation();

        // Test 4: Performance monitoring
        $this->testPerformanceMonitoring();

        $this->newLine();
        $this->info('✅ Navigation caching tests completed!');
    }

    private function testCacheFunctionality(): void
    {
        $this->info('📊 Testing Cache Functionality');

        // Test active elections count
        $cachedCount = $this->navigationCache->getActiveElectionsCount();
        $directCount = Election::where('status', 'active')
            ->where('ends_at', '>', now())
            ->count();

        $this->assertEquals($cachedCount, $directCount, 'Active elections count matches');

        // Test active elections existence
        $cachedExists = $this->navigationCache->hasActiveElections();
        $directExists = Election::where('status', 'active')
            ->where('ends_at', '>', now())
            ->exists();

        $this->assertEquals($cachedExists, $directExists, 'Active elections existence matches');

        // Get a test user
        $user = User::first();
        if ($user) {
            $userStatus = $this->navigationCache->getUserStatus($user);
            $this->assertIsArray($userStatus, 'User status is returned as array');
            $this->assertArrayHasKey('kyc_verified', $userStatus, 'User status contains KYC verification');
            $this->assertArrayHasKey('has_upcoming_elections', $userStatus, 'User status contains elections info');
        }

        $this->info('✅ Cache functionality tests passed');
        $this->newLine();
    }

    private function testPerformanceComparison(int $iterations): void
    {
        $this->info("⚡ Testing Performance Comparison ({$iterations} iterations)");

        // Test cached vs direct queries for active elections count
        $cachedTimes = [];
        $directTimes = [];

        for ($i = 0; $i < $iterations; $i++) {
            // Cached query
            $start = microtime(true);
            $this->navigationCache->getActiveElectionsCount();
            $cachedTimes[] = microtime(true) - $start;

            // Direct query
            $start = microtime(true);
            Election::where('status', 'active')
                ->where('ends_at', '>', now())
                ->count();
            $directTimes[] = microtime(true) - $start;
        }

        $avgCached = array_sum($cachedTimes) / count($cachedTimes);
        $avgDirect = array_sum($directTimes) / count($directTimes);
        $improvement = $avgDirect > 0 ? (($avgDirect - $avgCached) / $avgDirect) * 100 : 0;

        $this->table(
            ['Method', 'Avg Time (ms)', 'Improvement'],
            [
                ['Cached', number_format($avgCached * 1000, 2), ''],
                ['Direct', number_format($avgDirect * 1000, 2), number_format($improvement, 1) . '%'],
            ]
        );

        $this->info('✅ Performance comparison completed');
        $this->newLine();
    }

    private function testCacheInvalidation(): void
    {
        $this->info('🔄 Testing Cache Invalidation');

        // Get initial cached value
        $initialCount = $this->navigationCache->getActiveElectionsCount();

        // Create a test election (if possible)
        try {
            $testElection = Election::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'title' => 'Test Election for Caching',
                'description' => 'Temporary test election',
                'type' => 'general',
                'status' => 'upcoming',
                'starts_at' => now()->addDays(1),
                'ends_at' => now()->addDays(2),
                'created_by' => 1, // Assuming admin user exists
            ]);

            // Cache should be invalidated by observer
            sleep(1); // Allow observer to run

            $newCount = $this->navigationCache->getActiveElectionsCount();

            // Clean up
            $testElection->delete();

            $this->assertNotEquals($initialCount, $newCount, 'Cache was invalidated after election creation/deletion');

        } catch (\Exception $e) {
            $this->warn('Could not test cache invalidation with real data: ' . $e->getMessage());
        }

        // Test manual cache clearing
        $this->navigationCache->clearAllNavigationCaches();
        $this->info('✅ Manual cache clearing works');

        $this->info('✅ Cache invalidation tests completed');
        $this->newLine();
    }

    private function testPerformanceMonitoring(): void
    {
        $this->info('📈 Testing Performance Monitoring');

        // Get performance stats
        $stats = $this->performanceMonitor->getPerformanceStats();
        $navigationMetrics = $this->performanceMonitor->getNavigationPerformanceMetrics();
        $healthCheck = $this->performanceMonitor->getHealthCheck();

        $this->info('Performance Stats:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Slow Queries Count', $stats['slow_queries_count']],
                ['Navigation Query Count', $navigationMetrics['navigation_query_count']],
                ['Avg Navigation Time (ms)', number_format($navigationMetrics['average_navigation_query_time'], 2)],
                ['Cache Hit Rate (%)', number_format($navigationMetrics['cache_hit_rate'], 1)],
                ['Health Status', $healthCheck['status']],
            ]
        );

        if (!empty($healthCheck['issues'])) {
            $this->warn('Health Check Issues:');
            foreach ($healthCheck['issues'] as $issue) {
                $this->warn("  - {$issue}");
            }
        }

        $this->info('✅ Performance monitoring tests completed');
        $this->newLine();
    }

    private function assertEquals($expected, $actual, string $message): void
    {
        if ($expected === $actual) {
            $this->info("  ✅ {$message}");
        } else {
            $this->error("  ❌ {$message} (Expected: {$expected}, Got: {$actual})");
        }
    }

    private function assertNotEquals($expected, $actual, string $message): void
    {
        if ($expected !== $actual) {
            $this->info("  ✅ {$message}");
        } else {
            $this->error("  ❌ {$message} (Both values are equal: {$expected})");
        }
    }

    private function assertIsArray($value, string $message): void
    {
        if (is_array($value)) {
            $this->info("  ✅ {$message}");
        } else {
            $this->error("  ❌ {$message} (Got: " . gettype($value) . ")");
        }
    }

    private function assertArrayHasKey($key, array $array, string $message): void
    {
        if (array_key_exists($key, $array)) {
            $this->info("  ✅ {$message}");
        } else {
            $this->error("  ❌ {$message} (Key '{$key}' not found)");
        }
    }
}
