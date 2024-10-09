<?php
/*
Plugin Name: Reescribe atributo ALT
Description: Reescribe el atributo ALT de las imágenes para mejorar la accesibilidad.
Version: 1.0
Author: A. Cambronero Blogpocket.com
Text Domain: alt-attribute-rewriter
*/

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

// Añadir menú al panel de administración
add_action('admin_menu', 'aar_add_admin_menu');
add_action('admin_init', 'aar_settings_init');

function aar_add_admin_menu() {
    add_options_page(
        'Alt Attribute Rewriter',
        'Alt Attribute Rewriter',
        'manage_options',
        'alt-attribute-rewriter',
        'aar_options_page'
    );
}

function aar_settings_init() {
    register_setting('aar_settings_group', 'aar_settings');

    add_settings_section(
        'aar_settings_section',
        __('Configuración del plugin', 'alt-attribute-rewriter'),
        'aar_settings_section_callback',
        'aar_settings_group'
    );

    add_settings_field(
        'include_title',
        __('Incluir título del post o página', 'alt-attribute-rewriter'),
        'aar_include_title_render',
        'aar_settings_group',
        'aar_settings_section'
    );

    add_settings_field(
        'include_filename',
        __('Incluir nombre del archivo de imagen', 'alt-attribute-rewriter'),
        'aar_include_filename_render',
        'aar_settings_group',
        'aar_settings_section'
    );

    add_settings_field(
        'default_text',
        __('Texto predeterminado', 'alt-attribute-rewriter'),
        'aar_default_text_render',
        'aar_settings_group',
        'aar_settings_section'
    );
}

function aar_include_title_render() {
    $options = get_option('aar_settings');
    ?>
    <input type='checkbox' name='aar_settings[include_title]' <?php checked(isset($options['include_title'])); ?> value='1'>
    <?php
}

function aar_include_filename_render() {
    $options = get_option('aar_settings');
    ?>
    <input type='checkbox' name='aar_settings[include_filename]' <?php checked(isset($options['include_filename'])); ?> value='1'>
    <?php
}

function aar_default_text_render() {
    $options = get_option('aar_settings');
    ?>
    <input type='text' name='aar_settings[default_text]' value='<?php echo isset($options['default_text']) ? esc_attr($options['default_text']) : ''; ?>' style='width: 300px;'>
    <?php
}

function aar_settings_section_callback() {
    echo __('Configura cómo se reescribirá el atributo ALT de las imágenes.', 'alt-attribute-rewriter');
}

function aar_options_page() {
    ?>
    <div class="wrap">
        <h1>Alt Attribute Rewriter</h1>
        <form action='options.php' method='post'>
            <?php
            settings_fields('aar_settings_group');
            do_settings_sections('aar_settings_group');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Reescribir el atributo ALT de las imágenes
add_filter('the_content', 'aar_modify_image_alt_tags');

function aar_modify_image_alt_tags($content) {
    if (!is_singular()) {
        return $content;
    }

    $options = get_option('aar_settings');
    $include_title = isset($options['include_title']);
    $include_filename = isset($options['include_filename']);
    $default_text = isset($options['default_text']) ? $options['default_text'] : '';

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

        if ($include_title) {
            $post_title = get_the_title();
            $new_alt_parts[] = $post_title;
        }

        if ($include_filename && $src) {
            // Obtener el nombre de archivo sin extensión
            $filename = pathinfo(parse_url($src, PHP_URL_PATH), PATHINFO_FILENAME);
            $new_alt_parts[] = $filename;
        }

        if (!empty($alt)) {
            $new_alt_parts[] = $alt;
        } else {
            $new_alt_parts[] = $default_text;
        }

        // Filtrar partes vacías y unir con guiones
        $new_alt = implode(' - ', array_filter($new_alt_parts));

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
