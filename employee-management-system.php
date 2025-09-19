<?php
/**
 * Plugin Name: Employee Management System
 * Description: A simple and powerful WordPress plugin to manage employee records. Allows admins to add, edit, delete, and view employee details including department, designation, salary, and joining date. Supports CSV import/export and optional frontend employee directory.
 * Version: 1.0.1
 * Author: Mohammad Ashif Iqbal
 * Author URI: https://ashifiqbal.com
 * Plugin URI: https://example.com/employee-management-system
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'EMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Register admin menu and subpages
 */
add_action( 'admin_menu', 'ems_register_admin_menu' );
function ems_register_admin_menu() {
    add_menu_page(
        'Employee Management System',
        'Employee Management',
        'manage_options',
        'ems-employee-list',            // main menu slug
        'ems_employee_list_page',
        'dashicons-groups',
        6
    );

    add_submenu_page(
        'ems-employee-list',
        'All Employees',
        'All Employees',
        'manage_options',
        'ems-employee-list',
        'ems_employee_list_page'
    );

    add_submenu_page(
        'ems-employee-list',
        'Add New Employee',
        'Add New',
        'manage_options',
        'ems-add-new-employee',
        'ems_add_new_employee_page'
    );
}

/**
 * Include callback pages
 */
function ems_add_new_employee_page() {
    include_once EMS_PLUGIN_DIR . 'includes/add-employee.php';
}
function ems_employee_list_page() {
    include_once EMS_PLUGIN_DIR . 'includes/list-employee.php';
}

/**
 * Create DB table on activation
 */
register_activation_hook( __FILE__, 'ems_create_table' );
function ems_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ems_form_data';
    $charset_collate = $wpdb->get_charset_collate();

    // Use dbDelta - needs exact format
    $sql = "CREATE TABLE {$table_name} (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(120) NOT NULL,
        email varchar(80) NOT NULL,
        phoneNo varchar(50) DEFAULT NULL,
        gender enum('male','female','other') DEFAULT NULL,
        designation varchar(50) DEFAULT NULL,
        PRIMARY KEY  (id)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    // Create wordpress pages for listing and adding employees

    $pageData = [
        "post_title" => "Employee Management System Page",
        "post_status" => "publish",
        "post_type" => "page",
        "post_content" => "This is Employee Management System Page",
        "post_name" => "employee-management-system-page",
    ];
    wp_insert_post($pageData);
}

/**
 * Optional: Drop table on deactivation (be careful - data will be lost).
 * You may prefer to leave the table and use uninstall.php for full removal.
 */
register_deactivation_hook( __FILE__, 'ems_drop_table' );
function ems_drop_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ems_form_data';
    $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

    // Drop the created page
    $pageSlug = 'employee-management-system-page';
    $pageInfo = get_page_by_path($pageSlug);

    if(!empty($pageInfo)){
        
        $pageId = $pageInfo->ID;
        wp_delete_post($pageId, true);
    }
}

/**
 * Enqueue assets only on plugin admin pages
 */
add_action( 'admin_enqueue_scripts', 'ems_add_plugin_assets' );
function ems_add_plugin_assets( $hook ) {
    // Quick check for pages we added
    $allowed_pages = array( 'toplevel_page_ems-employee-list', 'employee_page_ems-add-new-employee', 'ems-employee-list', 'ems-add-new-employee' );

    // Also check $_GET['page'] fallback
    $current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

    if ( ! in_array( $current_page, array( 'ems-employee-list', 'ems-add-new-employee' ), true ) ) {
        return;
    }

    // Styles
    wp_enqueue_style( 'ems-bootstrap-css', EMS_PLUGIN_URL . 'css/bootstrap.min.css', array(), '3.4.1' );
    wp_enqueue_style( 'ems-datatable-css', EMS_PLUGIN_URL . 'css/dataTables.dataTables.min.css', array(), '1.10.25' );
    wp_enqueue_style( 'ems-custom-css', EMS_PLUGIN_URL . 'css/custom.css', array(), '1.0.1' );

    // Scripts (jQuery dependency)
    wp_enqueue_script( 'ems-datatable-js', EMS_PLUGIN_URL . 'js/jquery.dataTables.min.js', array( 'jquery' ), '1.10.25', true );
    wp_enqueue_script( 'ems-validate-js', EMS_PLUGIN_URL . 'js/jquery.validate.min.js', array( 'jquery' ), '1.19.3', true );
    wp_enqueue_script( 'ems-bootstrap-js', EMS_PLUGIN_URL . 'js/bootstrap.min.js', array( 'jquery' ), '3.4.1', true );
    wp_enqueue_script( 'ems-custom-js', EMS_PLUGIN_URL . 'js/custom.js', array( 'jquery' ), '1.0.1', true );
}
