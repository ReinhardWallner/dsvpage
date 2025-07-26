<?php
// Astra Child Theme functions

function astra_child_enqueue_styles() {
    error_log("astra_child_enqueue_styles start" . print_r(get_template_directory_uri(), true));
    // wp_enqueue_style( 'astra-parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'astra-parent-style', get_template_directory_uri() . '/assets/css/minified/style-css.min.css' );

}

add_action( 'wp_enqueue_scripts', 'astra_child_enqueue_styles' );


function my_custom_scripts() {
    error_log("my_custom_scripts");
    if (is_page('cpu-update')) { // Slug deiner Seite
        error_log("my_custom_scripts IS PAGE cpu-update");
        wp_register_script('custom-inline-js', '');
        wp_enqueue_script('custom-inline-js');

        // 2. Inline-JS anhängen
        wp_add_inline_script('custom-inline-js', "
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof onloadInternally === 'function') {
                    onloadInternally();
                }

                const form = document.getElementById('the-redirect-form');
                if (form) {
                    form.addEventListener('submit', function (e) {
                        setTimeout(() => {
                            const doExcel = document.getElementById('doExcelExport');
                            const zipFile = document.getElementById('createzipFile');
                            if (doExcel) doExcel.value = false;
                            if (zipFile) zipFile.value = false;
                        }, 100);
                    });
                }
            });
        ");
    }
}
add_action('wp_enqueue_scripts', 'my_custom_scripts');

function tempsharedfiles_enqueue_update_form_styles() {
    // Prüfe, ob eine bestimmte Seitenvorlage verwendet wird
    $istemp = is_page_template('template-editmeta-sharedfiles copy.php');
    if (is_page_template('template-editmeta-sharedfiles copy.php')) {
        wp_enqueue_style(
            'update-form-style',
            get_stylesheet_directory_uri() . '/css/template_sharedfiles.css',
            array(),
            '1.0',
            'all'
        );
    }
}
add_action('wp_enqueue_scripts', 'tempsharedfiles_enqueue_update_form_styles');
?>