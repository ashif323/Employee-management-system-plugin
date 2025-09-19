<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$message = '';
$status = '';
$action = '';
$empId = 0;
$employee = array(
    'name' => '',
    'email' => '',
    'phoneNo' => '',
    'gender' => '',
    'designation' => '',
);

// If editing/viewing, load employee
if ( isset( $_GET['action'] ) && isset( $_GET['empId'] ) ) {
    $action = sanitize_text_field( wp_unslash( $_GET['action'] ) );
    $empId = intval( $_GET['empId'] );

    if ( in_array( $action, array( 'edit', 'view' ), true ) && $empId > 0 ) {
        $employee = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ems_form_data WHERE id = %d", $empId ), ARRAY_A );
        if ( ! $employee ) {
            $message = 'Employee not found';
            $status = 0;
        }
    }
}

// Save form data (add or edit)
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['btn_submit'] ) ) {
    // Verify nonce
    if ( ! isset( $_POST['ems_employee_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ems_employee_nonce'] ), 'ems_save_employee' ) ) {
        $message = 'Security check failed.';
        $status = 0;
    } else {
        $name = sanitize_text_field( wp_unslash( $_POST['name'] ) );
        $email = sanitize_email( wp_unslash( $_POST['email'] ) );
        $phoneNo = sanitize_text_field( wp_unslash( $_POST['phoneNo'] ) );
        $gender = sanitize_text_field( wp_unslash( $_POST['gender'] ) );
        $designation = sanitize_text_field( wp_unslash( $_POST['designation'] ) );

        if ( isset( $_GET['action'] ) && 'edit' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) && isset( $_GET['empId'] ) ) {
            $empId = intval( $_GET['empId'] );
            $wpdb->update(
                $wpdb->prefix . 'ems_form_data',
                array(
                    'name'        => $name,
                    'email'       => $email,
                    'phoneNo'     => $phoneNo,
                    'gender'      => $gender,
                    'designation' => $designation,
                ),
                array( 'id' => $empId ),
                array( '%s', '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );
            $message = 'Employee updated successfully';
            $status = 1;
            // reload employee for display
            $employee = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ems_form_data WHERE id = %d", $empId ), ARRAY_A );
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'ems_form_data',
                array(
                    'name'        => $name,
                    'email'       => $email,
                    'phoneNo'     => $phoneNo,
                    'gender'      => $gender,
                    'designation' => $designation,
                ),
                array( '%s', '%s', '%s', '%s', '%s' )
            );

            $last_inserted_id = $wpdb->insert_id;
            if ( $last_inserted_id > 0 ) {
                $message = 'Employee saved successfully';
                $status = 1;
                // clear form
                $employee = array( 'name' => '', 'email' => '', 'phoneNo' => '', 'gender' => '', 'designation' => '' );
            } else {
                $message = 'Failed to save employee';
                $status = 0;
            }
        }
    }
}
?>

<div class="container">
    <div class="row">
        <div class="col-sm-8">
            <h2>
                <?php
                if ( 'view' === $action ) {
                    echo 'View Employee';
                } elseif ( 'edit' === $action ) {
                    echo 'Update Employee';
                } else {
                    echo 'Add Employee';
                }
                ?>
            </h2>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <?php
                    if ( 'view' === $action ) {
                        echo 'View Employee';
                    } elseif ( 'edit' === $action ) {
                        echo 'Update Employee';
                    } else {
                        echo 'Add Employee';
                    }
                    ?>
                </div>
                <div class="panel-body">

                    <?php if ( ! empty( $message ) ) : ?>
                        <div class="alert <?php echo ( 1 === $status ) ? 'alert-success' : 'alert-danger'; ?>">
                            <?php echo esc_html( $message ); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php
                        if ( 'edit' === $action && $empId ) {
                            echo esc_url( admin_url( 'admin.php?page=ems-add-new-employee&action=edit&empId=' . intval( $empId ) ) );
                        } else {
                            echo esc_url( admin_url( 'admin.php?page=ems-add-new-employee' ) );
                        }
                    ?>" method="post" id="ems-frm-add-employee">

                        <?php wp_nonce_field( 'ems_save_employee', 'ems_employee_nonce' ); ?>

                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text"
                                value="<?php echo ( isset( $employee['name'] ) ) ? esc_attr( $employee['name'] ) : ''; ?>"
                                required <?php if ( 'view' === $action ) echo 'readonly'; ?>
                                class="form-control" id="name" placeholder="Enter name" name="name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email"
                                value="<?php echo ( isset( $employee['email'] ) ) ? esc_attr( $employee['email'] ) : ''; ?>"
                                required class="form-control" <?php if ( 'view' === $action ) echo 'readonly'; ?> id="email"
                                placeholder="Enter email" name="email">
                        </div>
                        <div class="form-group">
                            <label for="phoneNo">Phone No:</label>
                            <input type="text"
                                value="<?php echo ( isset( $employee['phoneNo'] ) ) ? esc_attr( $employee['phoneNo'] ) : ''; ?>"
                                class="form-control" id="phoneNo"
                                <?php if ( 'view' === $action ) echo 'readonly'; ?>
                                placeholder="Enter phone number" name="phoneNo">
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender:</label>
                            <select <?php if ( 'view' === $action ) echo 'disabled'; ?> name="gender" id="gender"
                                class="form-control">
                                <option value="">Select gender</option>
                                <option value="male" <?php selected( isset( $employee['gender'] ) ? $employee['gender'] : '', 'male' ); ?>>Male</option>
                                <option value="female" <?php selected( isset( $employee['gender'] ) ? $employee['gender'] : '', 'female' ); ?>>Female</option>
                                <option value="other" <?php selected( isset( $employee['gender'] ) ? $employee['gender'] : '', 'other' ); ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="designation">Designation:</label>
                            <input type="text" required
                                value="<?php echo ( isset( $employee['designation'] ) ) ? esc_attr( $employee['designation'] ) : ''; ?>"
                                class="form-control" id="designation"
                                <?php if ( 'view' === $action ) echo 'readonly'; ?>
                                placeholder="Enter designation" name="designation">
                        </div>

                        <?php
                        if ( 'view' === $action ) {
                            // no button
                        } elseif ( 'edit' === $action ) { ?>
                            <button type="submit" class="btn btn-success" name="btn_submit">Update</button>
                        <?php } else { ?>
                            <button type="submit" class="btn btn-success" name="btn_submit">Submit</button>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
