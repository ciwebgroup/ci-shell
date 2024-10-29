# CI Enhanced Shell

A powerful WordPress plugin that provides a shell interface to run server commands, WP-CLI commands, and SQL queries directly from the WordPress admin. CI Enhanced Shell is especially beneficial for developers and administrators looking to migrate "difficult-to-migrate" WordPress sites, enabling precise control over database operations, file transfers, and troubleshooting during the migration process.

## Features

- **Execute Shell Commands**: Run shell commands directly within the WordPress admin, ideal for fine-tuning configurations and running server commands without leaving the WordPress interface.
- **WP-CLI Integration**: Perform WP-CLI commands, making it easy to manage plugins, themes, and site configurations remotely.
- **SQL Command Execution**: Run SQL queries on the WordPress database, perfect for handling complex database migrations and custom table operations.
- **Database Dumps**: Generate, view, and download `.sql` dumps of the WordPress database, facilitating easy backup and restoration during migration.

## Benefits for Migrating Difficult Sites

### 1. **Enhanced Control Over Database Operations**
   - **Direct SQL Access**: Execute SQL queries to transform, migrate, or update tables without relying on external database management tools.
   - **Generate Database Dumps**: Create `.sql` dumps of the database on demand, ideal for backing up data during each phase of migration. Access and download these dumps directly from the WordPress admin panel.

### 2. **Effortless WP-CLI Management**
   - **Full WP-CLI Support**: Run WP-CLI commands from the WordPress admin, streamlining actions like disabling plugins, flushing caches, or exporting content without SSH access.
   - **Customized Site Adjustments**: Configure or update plugin and theme settings post-migration using WP-CLI, ensuring site settings are tailored to the new server environment.

### 3. **Seamless Command Execution for Server Configurations**
   - **Shell Command Execution**: Use shell commands to make necessary adjustments, such as file permission corrections, folder structure modifications, or file system checks. These capabilities reduce reliance on additional hosting support and make migrations smoother.
   - **Fallback Strategy**: The plugin employs a fallback strategy for command execution, meaning it works reliably across various server environments, including those with limited function availability.

### 4. **Integrated Shell Interface in WordPress Admin**
   - **Centralized Control**: Run commands, manage dumps, and perform database operations without switching between multiple tools or platforms, centralizing migration tasks within WordPress.
   - **Minimal Access Requirements**: Access the plugin’s shell functionality directly from the admin dashboard, even if SSH or database management tools are unavailable or restricted.

## Installation

1. Upload the `ci-enhanced-shell` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to **Settings > CI Shell Interface** to access the shell, WP-CLI, and SQL command interface.

## Usage

- **Select Command Type**: Choose `Shell`, `WP-CLI`, or `SQL` from the dropdown.
- **Run Commands**: Enter the desired command and click "Run Command."
- **Generate Database Dumps**: Use the "Generate New Dump" button to create a fresh database dump.
- **View Dumps**: All generated `.sql` dump files are listed with download links for quick access.

## Security

- **Admin-Only Access**: The plugin is restricted to administrators, ensuring only authorized users can access and run commands.
- **Nonces for REST Requests**: All REST requests require valid nonces, preventing unauthorized external requests.

---

By providing a streamlined interface for running WP-CLI, shell, and SQL commands, CI Enhanced Shell makes migrating complex WordPress sites significantly easier and faster. From accessing essential server configurations to handling database dumps directly from the WordPress admin, this plugin enables efficient, secure site migration for WordPress professionals.

