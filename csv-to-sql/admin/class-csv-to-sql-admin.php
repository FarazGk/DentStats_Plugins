<?php

if ( ! class_exists( 'CSV_To_SQL_Admin' ) ) {

    class CSV_To_SQL_Admin {

        public function add_admin_menu() {
            add_menu_page(
                'TSV to SQL',
                'TSV to SQL',
                'manage_options',
                'csv-to-sql',
                array( $this, 'create_admin_page' ),
                'dashicons-upload',
                20
            );
        }

        public function create_admin_page() {
            require_once CSV_TO_SQL_PLUGIN_DIR . 'includes/class-tsv-processor.php';
            $processor = new TSV_Processor();
            $tables = $processor->get_created_tables();
            ?>
            <div class="wrap">
                <h1>Upload TSV File</h1>
                <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                    <input type="hidden" name="action" value="upload_tsv">
                    <?php wp_nonce_field( 'tsv_upload', 'tsv_upload_nonce' ); ?>
                    <input type="file" name="tsv_file" id="tsv_file">
                    <?php submit_button( 'Upload TSV' ); ?>
                </form>
                <h2>Created Tables</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $tables as $table ) : ?>
                        <tr>
                            <td><?php echo esc_html( $table ); ?></td>
                            <td>
                                <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" onsubmit="return confirm('Are you sure you want to delete this table?');">
                                    <input type="hidden" name="action" value="delete_table">
                                    <input type="hidden" name="table_name" value="<?php echo esc_attr( $table ); ?>">
                                    <?php wp_nonce_field( 'delete_table', 'delete_table_nonce' ); ?>
                                    <?php submit_button( 'Delete', 'delete', 'submit', false ); ?>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
    }
}
