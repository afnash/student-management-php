<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

$log_file = __DIR__ . "/activity.log";
$log_content = "Log file not found or empty.";

try {
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        if ($log_content === false) {
            throw new Exception("Could not read log file.");
        }
        if (empty($log_content)) {
            $log_content = "Log file is empty.";
        }
    } else {
        file_put_contents($log_file, "Log file created on " . date("Y-m-d H:i:s") . PHP_EOL);
        $log_content = "Log file was just created and is now empty.";
    }
} catch (Exception $e) {
    $log_content = "Error accessing log file: " . $e->getMessage();
}

// Parse log entries for better display
$log_entries = [];
if ($log_content && $log_content !== "Log file is empty." && $log_content !== "Log file was just created and is now empty.") {
    $lines = explode(PHP_EOL, $log_content);
    foreach ($lines as $line) {
        if (trim($line)) {
            $log_entries[] = trim($line);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Student Management System</title>
    <link rel="stylesheet" href="modern-styles.css">
    <style>
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .page-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }
        
        .logs-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1000px;
            box-shadow: var(--shadow-medium);
            animation: slideIn 0.8s ease;
        }
        
        .logs-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .logs-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .logs-count {
            background: var(--gradient-secondary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .log-entries {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 1rem;
        }
        
        .log-entry {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .log-entry:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .log-entry::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }
        
        .log-entry.error::before {
            background: var(--gradient-secondary);
        }
        
        .log-entry.success::before {
            background: linear-gradient(135deg, var(--success-color) 0%, #22c55e 100%);
        }
        
        .log-timestamp {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .log-message {
            color: white;
            font-size: 1rem;
            line-height: 1.5;
        }
        
        .empty-logs {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .empty-logs i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-logs h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: white;
        }
        
        .empty-logs p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .log-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--glass-border);
        }
        
        .btn-clear-logs {
            background: var(--gradient-secondary);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-clear-logs:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }
        
        .btn-refresh {
            background: var(--gradient-primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-refresh:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }
        
        @media (max-width: 768px) {
            .logs-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .log-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="add_student.php"><i class="fas fa-user-plus"></i> Add Student</a>
        <a href="view_students.php"><i class="fas fa-users"></i> View Students</a>
        <a href="search_student.php"><i class="fas fa-search"></i> Search Student</a>
        <a href="logs.php"><i class="fas fa-list"></i> View Logs</a>
        <a href="index.php?logout=true" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="glass-container">
        <div class="page-header">
            <h1><i class="fas fa-list"></i> System Activity Logs</h1>
            <p>Monitor system activities and track user actions</p>
        </div>
        
        <div class="logs-container">
            <div class="logs-header">
                <h3 class="logs-title">
                    <i class="fas fa-clipboard-list"></i> Activity Logs
                </h3>
                <span class="logs-count">
                    <?php echo count($log_entries); ?> entr<?php echo count($log_entries) !== 1 ? 'ies' : 'y'; ?>
                </span>
            </div>
            
            <?php if (empty($log_entries)): ?>
                <div class="empty-logs">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Log Entries</h3>
                    <p>There are currently no activity logs to display.</p>
                    <p>Logs will appear here as you perform actions in the system.</p>
                </div>
            <?php else: ?>
                <div class="log-entries">
                    <?php foreach (array_reverse($log_entries) as $entry): ?>
                        <?php
                        $isError = stripos($entry, 'error') !== false;
                        $isSuccess = stripos($entry, 'success') !== false || stripos($entry, 'added') !== false;
                        $entryClass = $isError ? 'error' : ($isSuccess ? 'success' : '');
                        
                        // Extract timestamp if present
                        $timestamp = '';
                        $message = $entry;
                        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $entry, $matches)) {
                            $timestamp = $matches[1];
                            $message = preg_replace('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', '', $entry);
                        }
                        ?>
                        <div class="log-entry <?php echo $entryClass; ?>">
                            <?php if ($timestamp): ?>
                                <div class="log-timestamp">
                                    <i class="fas fa-clock"></i> <?php echo $timestamp; ?>
                                </div>
                            <?php endif; ?>
                            <div class="log-message">
                                <?php echo htmlspecialchars(trim($message)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="log-actions">
                    <a href="logs.php" class="btn-refresh">
                        <i class="fas fa-sync-alt"></i> Refresh Logs
                    </a>
                    <a href="logs.php?clear=1" class="btn-clear-logs" onclick="return confirm('Are you sure you want to clear all logs?');">
                        <i class="fas fa-trash"></i> Clear Logs
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
