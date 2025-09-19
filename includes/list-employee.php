<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$message = '';

// Handle delete with nonce check
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['emp_del_id'] ) ) {
    // verify nonce
    if ( isset( $_POST['ems_delete_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['ems_delete_nonce'] ), 'ems_delete_employee' ) ) {
        $del_id = intval( $_POST['emp_del_id'] );
        $wpdb->delete( $wpdb->prefix . 'ems_form_data', array( 'id' => $del_id ), array( '%d' ) );
        $message = 'Employee deleted successfully';
    } else {
        $message = 'Security check failed. Unable to delete.';
    }
}

// Fetch employees
$employees = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ems_form_data", ARRAY_A );
?>

<div class="container">
    <div class="row">
        <div class="col-sm-10">
            <h2>List Employee</h2>

            <div class="panel panel-primary">
                <div class="panel-heading">List Employee</div>
                <div class="panel-body">

                    <?php if ( ! empty( $message ) ) : ?>
                        <div class="alert alert-success">
                            <?php echo esc_html( $message ); ?>
                        </div>
                    <?php endif; ?>

                    <table class="table" id="tbl-employee" width="100%">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>#Name</th>
                                <th>#Email</th>
                                <th>#Gender</th>
                                <th>#Designation</th>
                                <th>#Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ( ! empty( $employees ) ) : ?>
                            <?php foreach ( $employees as $employee ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $employee['id'] ); ?></td>
                                    <td><?php echo esc_html( $employee['name'] ); ?></td>
                                    <td><?php echo esc_html( $employee['email'] ); ?></td>
                                    <td><?php echo esc_html( ucfirst( $employee['gender'] ) ); ?></td>
                                    <td><?php echo esc_html( $employee['designation'] ); ?></td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="employee-actions">
                                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ems-add-new-employee&action=edit&empId=' . intval( $employee['id'] ) ) ); ?>" class="btn btn-warning btn-sm">
                                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Edit
                                            </a>

                                            <form id="frm-delete-employee-<?php echo intval( $employee['id'] ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=ems-employee-list' ) ); ?>" style="display:inline-block;">
                                                <?php wp_nonce_field( 'ems_delete_employee', 'ems_delete_nonce' ); ?>
                                                <input type="hidden" name="emp_del_id" value="<?php echo intval( $employee['id'] ); ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure want to delete?');">
                                                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Delete
                                                </button>
                                            </form>

                                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ems-add-new-employee&action=view&empId=' . intval( $employee['id'] ) ) ); ?>" class="btn btn-info btn-sm">
                                                <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="6">No Employee found</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
