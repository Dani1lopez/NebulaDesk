echo "--- Schema Validation ---\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('tickets'));

echo "\n--- Model Consistency ---\n";
$ticket = new App\Models\Ticket();
echo "Fillable: " . implode(', ', $ticket->getFillable()) . "\n";
echo "Casts: "; print_r($ticket->getCasts());

echo "\n--- Scheduler Validation ---\n";
// Hard to check schedule programmatically without parsing console.php, but we can check if the file exists and contains the job
echo "Checking routes/console.php...\n";
$console = file_get_contents(base_path('routes/console.php'));
if (strpos($console, 'SlaBreachCheckJob') !== false) {
    echo "PASS: SlaBreachCheckJob found in console.php\n";
} else {
    echo "FAIL: SlaBreachCheckJob NOT found in console.php\n";
}

echo "\n--- Redis Validation ---\n";
echo "Redis Client: " . config('database.redis.client') . "\n";

echo "\n--- Filesystem Validation ---\n";
echo "Default Filesystem: " . config('filesystems.default') . "\n";
