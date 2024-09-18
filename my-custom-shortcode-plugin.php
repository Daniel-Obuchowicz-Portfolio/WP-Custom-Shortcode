<?php
/**
 * Plugin Name: Custom Shortcode Plugin
 * Description: Tworzy nowe menu w panelu admina z możliwością generowania shortcode. Edytować może tylko administrator.
 * Version: 1.0
 * Author: Daniel Obuchowicz
 * License: GPL2
 */

defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . 'includes/ShortcodeSettings.php';

class CustomShortcodePlugin {
    private $shortcode_name_option = 'custom_shortcode_name';
    private $shortcode_content_option = 'custom_shortcode_content';
    private $menu_position_option = 'custom_shortcode_menu_position';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('init', [$this, 'register_custom_shortcode']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
    }

    public function add_admin_menu() {
        // Sprawdzenie, czy użytkownik ma uprawnienia do zarządzania opcjami (czyli czy jest administratorem)
        if (!current_user_can('manage_options')) {
            return;
        }

        $menu_position = get_option($this->menu_position_option, 20); // Domyślna pozycja to 20

        add_menu_page(
            'Custom Shortcode',
            'Custom Shortcode',
            'manage_options', // Tylko użytkownicy z uprawnieniami 'manage_options' (zazwyczaj administratorzy)
            'custom-shortcode',
            [$this, 'render_settings_page'],
            'dashicons-editor-code',
            (int) $menu_position
        );
    }

    public function render_settings_page() {
        // Zabezpieczenie - upewniamy się, że tylko administratorzy mają dostęp do tej strony
        if (!current_user_can('manage_options')) {
            wp_die(__('Nie masz uprawnień do edytowania tej strony.'));
        }
        ?>
        <div class="wrap custom-shortcode-settings">
            <h1>Ustawienia Custom Shortcode</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('custom_shortcode_group');
                do_settings_sections('custom-shortcode');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        // Zabezpieczenie - upewniamy się, że tylko administratorzy mogą rejestrować ustawienia
        if (!current_user_can('manage_options')) {
            return;
        }

        register_setting('custom_shortcode_group', $this->shortcode_name_option);
        register_setting('custom_shortcode_group', $this->shortcode_content_option);
        register_setting('custom_shortcode_group', $this->menu_position_option);

        add_settings_section(
            'custom_shortcode_section',
            null,
            null,
            'custom-shortcode'
        );

        add_settings_field(
            $this->shortcode_name_option,
            'Nazwa Shortcode',
            [$this, 'render_shortcode_name_field'],
            'custom-shortcode',
            'custom_shortcode_section'
        );

        add_settings_field(
            $this->shortcode_content_option,
            'Zawartość Shortcode (HTML)',
            [$this, 'render_shortcode_content_field'],
            'custom-shortcode',
            'custom_shortcode_section'
        );

        add_settings_field(
            $this->menu_position_option,
            'Pozycja Menu',
            [$this, 'render_menu_position_field'],
            'custom-shortcode',
            'custom_shortcode_section'
        );
    }

    public function render_shortcode_name_field() {
        $shortcode_name = get_option($this->shortcode_name_option, 'custom_shortcode');
        ?>
        <input type="text" name="<?php echo esc_attr($this->shortcode_name_option); ?>"
               value="<?php echo esc_attr($shortcode_name); ?>" class="regular-text" />
        <?php
    }

    public function render_shortcode_content_field() {
        $shortcode_content = get_option($this->shortcode_content_option, '');
        $editor_settings = array(
            'textarea_name' => $this->shortcode_content_option,
            'media_buttons' => true, // Pokaż przycisk dodawania mediów
            'textarea_rows' => 10,
            'teeny' => false, // Pełny edytor
            'quicktags' => true // Umożliwia używanie Quicktags (HTML)
        );

        wp_editor($shortcode_content, 'custom_shortcode_editor', $editor_settings);
    }

    public function render_menu_position_field() {
        $menu_position = get_option($this->menu_position_option, 80);
        ?>
        <input type="number" name="<?php echo esc_attr($this->menu_position_option); ?>"
               value="<?php echo esc_attr($menu_position); ?>" class="small-text" min="1" max="100" />
        <p class="description">Podaj pozycję menu (np. 1 dla najwyższego, 20 dla domyślnego miejsca).</p>
        <?php
    }

    public function register_custom_shortcode() {
        $shortcode_name = get_option($this->shortcode_name_option, 'custom_shortcode');
        add_shortcode($shortcode_name, [$this, 'display_shortcode_content']);
    }

    public function display_shortcode_content() {
        return get_option($this->shortcode_content_option, '');
    }

    public function enqueue_admin_styles() {
        wp_enqueue_style(
            'custom-shortcode-admin-style',
            plugin_dir_url(__FILE__) . 'assets/admin-style.css'
        );
    }
}

new CustomShortcodePlugin();
