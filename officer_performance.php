<?php
require_once 'Database.php';
require_once 'config.php';

// Start session
session_start();

// Mock session data for demonstration
$staff = [
    'user_id' => 1,
    'full_name' => 'John Doe',
    'department' => 'Management'
];

class OfficerPerformanceAnalyzer {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get officer performance summary
     */
    public function getOfficerPerformance($month = null, $year = null) {
        $date_condition = "";
        $params = [];
        
        if ($month && $year) {
            $date_condition = "AND MONTH(t.date_of_payment) = :month AND YEAR(t.date_of_payment) = :year";
            $params[':month'] = $month;
            $params[':year'] = $year;
        } else {
            // Current month by default
            $date_condition = "AND MONTH(t.date_of_payment) = MONTH(NOW()) AND YEAR(t.date_of_payment) = YEAR(NOW())";
        }
        
        $this->db->query("
            SELECT 
                s.user_id,
                s.full_name,
                s.department,
                COUNT(t.id) as total_transactions,
                COALESCE(SUM(t.amount_paid), 0) as total_amount,
                AVG(t.amount_paid) as avg_transaction_amount,
                MIN(t.date_of_payment) as first_transaction_date,
                MAX(t.date_of_payment) as last_transaction_date,
                COUNT(DISTINCT t.date_of_payment) as active_days,
                COUNT(CASE WHEN t.approval_status = 'Approved' THEN 1 END) as approved_transactions,
                COUNT(CASE WHEN t.approval_status = 'Declined' THEN 1 END) as declined_transactions,
                COUNT(CASE WHEN t.approval_status = 'Pending' THEN 1 END) as pending_transactions
            FROM staffs s
            LEFT JOIN account_general_transaction_new t ON s.user_id = t.posting_officer_id
                {$date_condition}
            WHERE s.department IN ('Wealth Creation', 'Accounts')
            GROUP BY s.user_id, s.full_name, s.department
            ORDER BY total_amount DESC
        ");
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultSet();
    }
    
    /**
     * Get officer daily performance
     */
    public function getOfficerDailyPerformance($officer_id, $month = null, $year = null) {
        $date_condition = "";
        $params = [':officer_id' => $officer_id];
        
        if ($month && $year) {
            $date_condition = "AND MONTH(date_of_payment) = :month AND YEAR(date_of_payment) = :year";
            $params[':month'] = $month;
            $params[':year'] = $year;
        } else {
            $date_condition = "AND MONTH(date_of_payment) = MONTH(NOW()) AND YEAR(date_of_payment) = YEAR(NOW())";
        }
        
        $this->db->query("
            SELECT 
                DATE(date_of_payment) as payment_date,
                COUNT(*) as daily_transactions,
                SUM(amount_paid) as daily_amount,
                COUNT(CASE WHEN approval_status = 'Approved' THEN 1 END) as approved_count
            FROM account_general_transaction_new 
            WHERE posting_officer_id = :officer_id
            {$date_condition}
            GROUP BY DATE(date_of_payment)
            ORDER BY payment_date DESC
        ");
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultSet();
    }
    
    /**
     * Get performance rankings
     */
    public function getPerformanceRankings($month = null, $year = null) {
        $performance_data = $this->getOfficerPerformance($month, $year);
        
        // Calculate performance scores
        foreach ($performance_data as &$officer) {
            $efficiency_rate = $officer['total_transactions'] > 0 ? 
                ($officer['approved_transactions'] / $officer['total_transactions']) * 100 : 0;
            
            $daily_avg = $officer['active_days'] > 0 ? 
                $officer['total_amount'] / $officer['active_days'] : 0;
            
            // Performance score (weighted)
            $performance_score = ($efficiency_rate * 0.4) + 
                                (min($daily_avg / 10000, 100) * 0.4) + 
                                (min($officer['total_transactions'] / 50, 100) * 0.2);
            
            $officer['efficiency_rate'] = $efficiency_rate;
            $officer['daily_avg'] = $daily_avg;
            $officer['performance_score'] = $performance_score;
        }
        
        // Sort by performance score
        usort($performance_data, function($a, $b) {
            return $b['performance_score'] <=> $a['performance_score'];
        });
        
        return $performance_data;
    }
    
    /**
     * Get department comparison
     */
    public function getDepartmentComparison($month = null, $year = null) {
        $date_condition = "";
        $params = [];
        
        if ($month && $year) {
            $date_condition = "AND MONTH(t.date_of_payment) = :month AND YEAR(t.date_of_payment) = :year";
            $params[':month'] = $month;
            $params[':year'] = $year;
        } else {
            $date_condition = "AND MONTH(t.date_of_payment) = MONTH(NOW()) AND YEAR(t.date_of_payment) = YEAR(NOW())";
        }
        
        $this->db->query("
            SELECT 
                s.department,
                COUNT(DISTINCT s.user_id) as officer_count,
                COUNT(t.id) as total_transactions,
                COALESCE(SUM(t.amount_paid), 0) as total_amount,
                AVG(t.amount_paid) as avg_transaction_amount
            FROM staffs s
            LEFT JOIN account_general_transaction_new t ON s.user_id = t.posting_officer_id
                {$date_condition}
            WHERE s.department IN ('Wealth Creation', 'Accounts')
            GROUP BY s.department
            ORDER BY total_amount DESC
        ");
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultSet();
    }
}

$analyzer = new OfficerPerformanceAnalyzer();

// Get current date info
$selected_month = $_GET['month'] ?? date('n');
$selected_year = $_GET['year'] ?? date('Y');
$selected_month_name = date('F', mktime(0, 0, 0, $selected_month, 1));

// Get data
$officer_performance = $analyzer->getOfficerPerformance($selected_month, $selected_year);
$performance_rankings = $analyzer->getPerformanceRankings($selected_month, $selected_year);
$department_comparison = $analyzer->getDepartmentComparison($selected_month, $selected_year);

// Get selected officer details if requested
$selected_officer_id = $_GET['officer_id'] ?? null;
$officer_daily_data = [];
if ($selected_officer_id) {
    $officer_daily_data = $analyzer->getOfficerDailyPerformance($selected_officer_id, $selected_month, $selected_year);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Officer Performance Analysis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="mpr_dashboard.php" class="text-blue-600 hover:text-blue-800 mr-4">← Back to Dashboard</a>
                    <h1 class="text-xl font-bold text-gray-900">Officer Performance Analysis</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700">Welcome, <?php echo $staff['full_name']; ?></span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                        <?php echo $staff['department']; ?>
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-4 lg:mb-0">
                        <h2 class="text-2xl font-bold text-gray-900">Officer Performance Dashboard</h2>
                        <p class="text-gray-600"><?php echo $selected_month_name . ' ' . $selected_year; ?> Performance Analysis</p>
                    </div>
                    
                    <!-- Period Selection Form -->
                    <form method="GET" class="flex flex-col sm:flex-row gap-4">
                        <select name="month" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Month</option>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo $m == $selected_month ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        
                        <select name="year" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Year</option>
                            <?php for ($y = date('Y') - 3; $y <= date('Y'); $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y == $selected_year ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Load Analysis
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Department Comparison -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <?php foreach ($department_comparison as $dept): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900"><?php echo $dept['department']; ?> Department</h3>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                        <?php echo $dept['officer_count']; ?> Officers
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">₦<?php echo number_format($dept['total_amount']); ?></p>
                        <p class="text-sm text-gray-500">Total Collections</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600"><?php echo number_format($dept['total_transactions']); ?></p>
                        <p class="text-sm text-gray-500">Total Transactions</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <p class="text-sm text-gray-600">
                        Average per transaction: <span class="font-medium">₦<?php echo number_format($dept['avg_transaction_amount']); ?></span>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Performance Rankings -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Officer Performance Rankings</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Officer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Collections</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Efficiency</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Performance Score</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($performance_rankings as $index => $officer): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($index < 3): ?>
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold
                                            <?php echo $index === 0 ? 'bg-yellow-500' : ($index === 1 ? 'bg-gray-400' : 'bg-orange-600'); ?>">
                                            <?php echo $index + 1; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold">
                                            <?php echo $index + 1; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $officer['full_name']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php echo $officer['department'] === 'Wealth Creation' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo $officer['department']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-bold">
                                ₦<?php echo number_format($officer['total_amount']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                <div class="font-medium"><?php echo number_format($officer['total_transactions']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo $officer['active_days']; ?> active days</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-medium text-gray-900"><?php echo number_format($officer['efficiency_rate'], 1); ?>%</div>
                                <div class="text-xs text-gray-500">
                                    <?php echo $officer['approved_transactions']; ?>/<?php echo $officer['total_transactions']; ?> approved
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm font-bold 
                                    <?php echo $officer['performance_score'] >= 80 ? 'text-green-600' : 
                                              ($officer['performance_score'] >= 60 ? 'text-yellow-600' : 'text-red-600'); ?>">
                                    <?php echo number_format($officer['performance_score'], 1); ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?php echo $officer['performance_score'] >= 80 ? 'Excellent' : 
                                              ($officer['performance_score'] >= 60 ? 'Good' : 'Needs Improvement'); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="?officer_id=<?php echo $officer['user_id']; ?>&month=<?php echo $selected_month; ?>&year=<?php echo $selected_year; ?>" 
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Officer Daily Performance (if selected) -->
        <?php if ($selected_officer_id && !empty($officer_daily_data)): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                Daily Performance - 
                <?php 
                $selected_officer = array_filter($officer_performance, function($o) use ($selected_officer_id) {
                    return $o['user_id'] == $selected_officer_id;
                });
                $selected_officer = reset($selected_officer);
                echo $selected_officer['full_name'];
                ?>
            </h3>
            <canvas id="dailyPerformanceChart" width="400" height="200"></canvas>
        </div>
        <?php endif; ?>

        <!-- Performance Insights -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Insights & Recommendations</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Top Performers</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <?php foreach (array_slice($performance_rankings, 0, 3) as $index => $officer): ?>
                            <li>• <?php echo $officer['full_name']; ?> - ₦<?php echo number_format($officer['total_amount']); ?> 
                                (<?php echo number_format($officer['performance_score'], 1); ?> score)</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Areas for Improvement</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <?php 
                        $low_performers = array_filter($performance_rankings, function($o) {
                            return $o['performance_score'] < 60;
                        });
                        if (!empty($low_performers)): ?>
                            <?php foreach (array_slice($low_performers, 0, 3) as $officer): ?>
                                <li>• <?php echo $officer['full_name']; ?> - Focus on transaction efficiency 
                                    (<?php echo number_format($officer['efficiency_rate'], 1); ?>% approval rate)</li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>• All officers performing within acceptable ranges</li>
                            <li>• Continue monitoring for consistency</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php if ($selected_officer_id && !empty($officer_daily_data)): ?>
    <script>
        // Daily Performance Chart
        const dailyCtx = document.getElementById('dailyPerformanceChart').getContext('2d');
        const dailyData = <?php echo json_encode(array_reverse($officer_daily_data)); ?>;
        
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyData.map(item => new Date(item.payment_date).toLocaleDateString()),
                datasets: [{
                    label: 'Daily Collections (₦)',
                    data: dailyData.map(item => item.daily_amount),
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Transaction Count',
                    data: dailyData.map(item => item.daily_transactions),
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₦' + value.toLocaleString();
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    return 'Collections: ₦' + context.parsed.y.toLocaleString();
                                } else {
                                    return 'Transactions: ' + context.parsed.y;
                                }
                            }
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>