<?php
// load_test.php - Performance Testing
class LoadTester {
    private $concurrent_requests = 10;
    private $total_requests = 100;
    
    public function testAnnex4Submission() {
        echo "<h3>Annex 4 Load Test</h3>";
        
        $start_time = microtime(true);
        $successful = 0;
        $failed = 0;
        
        for ($i = 0; $i < $this->total_requests; $i++) {
            // Simulate form submission
            $data = [
                'region[]' => 'REGION II',
                'province[]' => 'CAGAYAN',
                'totally_damaged[]' => rand(1, 100),
                'partially_damaged[]' => rand(1, 50)
            ];
            
            // In real test, you'd make actual HTTP requests
            $success = $this->simulateRequest($data);
            
            if ($success) $successful++;
            else $failed++;
        }
        
        $end_time = microtime(true);
        $total_time = $end_time - $start_time;
        
        echo "Total Requests: {$this->total_requests}<br>";
        echo "Successful: $successful<br>";
        echo "Failed: $failed<br>";
        echo "Total Time: " . round($total_time, 2) . " seconds<br>";
        echo "Requests/Second: " . round($this->total_requests / $total_time, 2) . "<br>";
    }
    
    private function simulateRequest($data) {
        // Simulate database operation
        usleep(100000); // 100ms delay
        return true;
    }
}

$tester = new LoadTester();
$tester->testAnnex4Submission();
?>