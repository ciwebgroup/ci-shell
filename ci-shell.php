<?php
/*
Plugin Name: CI Enhanced Shell
Description: A WordPress shell plugin that supports WP-CLI, SQL execution, and database dump management.
Version: 1.1
Author: Your Name
*/

function ci_register_remote_shell_routes() {
    register_rest_route('ci-shell/v1', '/execute', array(
        'methods' => 'POST',
        'callback' => 'ci_execute_command',
        'permission_callback' => function() { return current_user_can('manage_options'); }
    ));
    register_rest_route('ci-shell/v1', '/db-dumps', array(
        'methods' => 'POST',
        'callback' => 'ci_generate_db_dump',
        'permission_callback' => function() { return current_user_can('manage_options'); }
    ));
}
add_action('rest_api_init', 'ci_register_remote_shell_routes');

// Handle command execution
function ci_execute_command($request) {
    $command = sanitize_text_field($request->get_param('command'));
    $type = $request->get_param('type'); // shell, wpcli, or sql

    if (!$command) return new WP_REST_Response(array('error' => 'No command provided'), 400);

    switch ($type) {
        case 'wpcli':
            $output = ci_execute_wpcli($command);
            break;
        case 'sql':
            $output = ci_execute_sql($command);
            break;
        case 'shell':
        default:
            $output = ci_execute_with_fallback($command);
            break;
    }

    return new WP_REST_Response(array('output' => $output), 200);
}

// WP-CLI command execution
function ci_execute_wpcli($cmd) {
    $wpcli_path = 'wp'; // Adjust path if WP-CLI is in a custom location
    $full_command = escapeshellcmd("$wpcli_path $cmd");
    return ci_execute_with_fallback($full_command);
}

// Raw SQL command execution
function ci_execute_sql($sql) {
    global $wpdb;
    $results = $wpdb->get_results($sql, ARRAY_A);
    if ($wpdb->last_error) return "SQL Error: " . $wpdb->last_error;

    ob_start();
    print_r($results);
    return ob_get_clean();
}

// Shell command execution fallback
function ci_execute_with_fallback($cmd) {
    $output = '';
    if (function_exists('exec')) {
        exec($cmd, $outputArr);
        $output = implode("\n", $outputArr);
    } elseif (function_exists('shell_exec')) {
        $output = shell_exec($cmd);
    } elseif (function_exists('system')) {
        ob_start();
        system($cmd);
        $output = ob_get_clean();
    } elseif (function_exists('passthru')) {
        ob_start();
        passthru($cmd);
        $output = ob_get_clean();
    } else {
        $output = 'Command execution functions disabled.';
    }
    return esc_html($output);
}

// Generate and list database dump files
function ci_generate_db_dump() {
    global $wpdb;
    $filename = 'db-dump-' . date('YmdHis') . '.sql';
    $file_path = WP_CONTENT_DIR . "/uploads/ci-shell-dumps/$filename";

    if (!file_exists(dirname($file_path))) mkdir(dirname($file_path), 0755, true);
    $command = "mysqldump --user={$wpdb->dbuser} --password={$wpdb->dbpassword} --host={$wpdb->dbhost} {$wpdb->dbname} > $file_path";
    ci_execute_with_fallback($command);

    return new WP_REST_Response(array('message' => 'Database dump created', 'filename' => $filename), 200);
}

function ci_list_db_dumps() {
    $directory = WP_CONTENT_DIR . '/uploads/ci-shell-dumps/';
    $files = glob($directory . '*.sql');

    return array_map(function ($file) {
        return basename($file);
    }, $files);
}

// Add Settings Page for Shell UI
function ci_shell_options_page() {
    add_options_page(
        'CI Shell Interface',
        'CI Shell',
        'manage_options',
        'ci-shell',
        'ci_shell_options_page_html'
    );
}
add_action('admin_menu', 'ci_shell_options_page');

// Settings Page HTML with embedded shell UI
function ci_shell_options_page_html() {
    $dumps = ci_list_db_dumps();
    ?>
    <div class="wrap">
        <h1>CI Shell Interface</h1>
        <p>Run shell commands, WP-CLI, or SQL queries:</p>
        
        <label for="cmd-type">Command Type:</label>
        <select id="cmd-type">
            <option value="shell">Shell</option>
            <option value="wpcli">WP-CLI</option>
            <option value="sql">SQL</option>
        </select>

        <textarea id="command" rows="3" style="width:100%;" placeholder="Enter command..."></textarea>
        <button onclick="runCommand()">Run Command</button>

        <pre id="output" style="background:#333;color:#eee;padding:10px;margin-top:10px;"></pre>

        <h2>Database Dumps</h2>
        <button onclick="generateDbDump()">Generate New Dump</button>
        <ul id="db-dumps">
            <?php foreach ($dumps as $dump): ?>
                <li><a href="<?php echo content_url('uploads/ci-shell-dumps/' . $dump); ?>" download><?php echo esc_html($dump); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
    function runCommand() {
        const type = document.getElementById('cmd-type').value;
        const command = document.getElementById('command').value;
        
        fetch('<?php echo esc_url(rest_url('ci-shell/v1/execute')); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' },
            body: JSON.stringify({ type, command })
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('output').textContent = data.output || data.error;
        });
    }

    function generateDbDump() {
        fetch('<?php echo esc_url(rest_url('ci-shell/v1/db-dumps')); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            location.reload();
        });
    }
    </script>
    <?php
}