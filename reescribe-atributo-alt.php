<?php
/*
Plugin Name: Reescribe atributo ALT
Description: Reescribe el atributo ALT de las imágenes para mejorar la accesibilidad.
Version: 1.2
Author: A. Cambronero Blogpocket.com
Text Domain: reescribe-atributo-alt
*/

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

// Añadir menú al panel de administración
add_action('admin_menu', 'raa_add_admin_menu');
add_action('admin_init', 'raa_settings_init');

function raa_add_admin_menu() {
    add_options_page(
        'Reescribe atributo ALT',
        'Reescribe atributo ALT',
        'manage_options',
        'reescribe-atributo-alt',
        'raa_options_page'
    );
}

function raa_settings_init() {
    register_setting('raa_settings_group', 'raa_settings', 'raa_sanitize_settings');

    add_settings_section(
        'raa_settings_section',
        __('Configuración del plugin', 'reescribe-atributo-alt'),
        'raa_settings_section_callback',
        'raa_settings_group'
    );

    add_settings_field(
        'include_title',
        __('Incluir título del post o página', 'reescribe-atributo-alt'),
        'raa_include_title_render',
        'raa_settings_group',
        'raa_settings_section'
    );

    add_settings_field(
        'include_filename',
        __('Incluir nombre del archivo de imagen', 'reescribe-atributo-alt'),
        'raa_include_filename_render',
        'raa_settings_group',
        'raa_settings_section'
    );

    add_settings_field(
        'default_text',
        __('Texto predeterminado', 'reescribe-atributo-alt'),
        'raa_default_text_render',
        'raa_settings_group',
        'raa_settings_section'
    );
}

// Función de sanitización de ajustes
function raa_sanitize_settings($input) {
    $sanitized_input = array();

    // Saneamiento del checkbox 'include_title'
    $sanitized_input['include_title'] = isset($input['include_title']) && $input['include_title'] == '1' ? '1' : '';

    // Saneamiento del checkbox 'include_filename'
    $sanitized_input['include_filename'] = isset($input['include_filename']) && $input['include_filename'] == '1' ? '1' : '';

    // Saneamiento del texto 'default_text'
    if (isset($input['default_text'])) {
        $sanitized_input['default_text'] = sanitize_text_field($input['default_text']);
    } else {
        $sanitized_input['default_text'] = '';
    }

    return $sanitized_input;
}

function raa_include_title_render() {
    $options = get_option('raa_settings');
    $include_title = isset($options['include_title']) ? esc_attr($options['include_title']) : '';
    ?>
    <input type='checkbox' name='raa_settings[include_title]' <?php checked($include_title, '1'); ?> value='1'>
    <?php
}

function raa_include_filename_render() {
    $options = get_option('raa_settings');
    $include_filename = isset($options['include_filename']) ? esc_attr($options['include_filename']) : '';
    ?>
    <input type='checkbox' name='raa_settings[include_filename]' <?php checked($include_filename, '1'); ?> value='1'>
    <?php
}

function raa_default_text_render() {
    $options = get_option('raa_settings');
    $default_text = isset($options['default_text']) ? esc_attr($options['default_text']) : '';
    ?>
    <input type='text' name='raa_settings[default_text]' value='<?php echo $default_text; ?>' style='width: 300px;'>
    <?php
}

function raa_settings_section_callback() {
    echo __('Configura cómo se reescribirá el atributo ALT de las imágenes.', 'reescribe-atributo-alt');
}

function raa_options_page() {
    ?>
    <div class="wrap">
        <h1>Reescribe atributo ALT</h1>
        <form action='options.php' method='post'>
            <?php
            settings_fields('raa_settings_group');
            do_settings_sections('raa_settings_group');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Reescribir el atributo ALT de las imágenes
add_filter('the_content', 'raa_modify_image_alt_tags');

function raa_modify_image_alt_tags($content) {
    $options = get_option('raa_settings');

    // Saneamiento de opciones
    $include_title = isset($options['include_title']) && $options['include_title'] == '1';
    $include_filename = isset($options['include_filename']) && $options['include_filename'] == '1';
    $default_text = isset($options['default_text']) ? sanitize_text_field($options['default_text']) : '';

    // Obtener el título de la página actual
    $title = '';
    if ($include_title) {
        if (is_singular()) {
            $title = get_the_title();
        } elseif (is_archive()) {
            $title = get_the_archive_title();
        } elseif (is_home()) {
            $title = get_bloginfo('name');
        }
        $title = sanitize_text_field($title);
    }

    // Cargar el contenido en DOMDocument
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    $images = $dom->getElementsByTagName('img');

    foreach ($images as $img) {
        $alt = $img->getAttribute('alt');
        $src = $img->getAttribute('src');

        $new_alt_parts = array();

        if ($include_title && !empty($title)) {
            $new_alt_parts[] = $title;
        }

        if ($include_filename && $src) {
            // Obtener el nombre de archivo sin extensión
            $filename = pathinfo(parse_url($src, PHP_URL_PATH), PATHINFO_FILENAME);
            $filename = sanitize_text_field($filename);
            $new_alt_parts[] = $filename;
        }

        if (!empty($alt)) {
            $alt = sanitize_text_field($alt);
            $new_alt_parts[] = $alt;
        } else {
            $new_alt_parts[] = $default_text;
        }

        // Filtrar partes vacías y unir con guiones
        $new_alt = implode(' - ', array_filter($new_alt_parts));

        // Saneamiento del atributo ALT
        $new_alt = esc_attr($new_alt);

        $img->setAttribute('alt', $new_alt);
    }

    // Guardar el contenido modificado
    $body = $dom->getElementsByTagName('body')->item(0);
    $new_content = '';
    foreach ($body->childNodes as $childNode) {
        $new_content .= $dom->saveHTML($childNode);
    }

    return $new_content;
}
