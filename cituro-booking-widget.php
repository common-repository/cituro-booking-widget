<?php
/*
* Plugin Name: cituro Booking Widget
* Author: cituro GmbH
* Text Domain: cituro-booking-widget
* Domain Path: /languages
* Description: Integrate your cituro Booking Widget in your WordPress site.
* Author URI: https://www.cituro.com/
* Version: 1.1
* License: GPL v2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Requires at least: 5.0
* Requires PHP: 7.0
*/

if (!defined('ABSPATH')) {
    exit;
}

function cituro_register_settings() {
    add_option('cituro_account_number', '');
    add_option('cituro_preset_service', '');
    add_option('cituro_preset_category', ''); 
    add_option('cituro_preset_resource', ''); 
    add_option('cituro_preset_location', ''); 
    add_option('cituro_enable_custom_script', ''); 
    add_option('cituro_custom_script','');
    add_option('cituro_booking_widget_opened', false);

    register_setting('default', 'cituro_account_number', ['type' => 'string', 'sanitize_callback' => 'cituro_validate_account_number']);
    register_setting('default', 'cituro_preset_service', ['type' => 'string']);
    register_setting('default', 'cituro_preset_category', ['type' => 'string']); 
    register_setting('default', 'cituro_preset_resource', ['type' => 'string']); 
    register_setting('default', 'cituro_preset_location', ['type' => 'string']); 
    register_setting('default', 'cituro_enable_custom_script', ['type' => 'boolean']);
    register_setting('default', 'cituro_custom_script', ['type' => 'string']);
    register_setting('default', 'cituro_booking_widget_opened', ['type' => 'boolean']);
}
add_action('admin_init', 'cituro_register_settings');

function cituro_admin_menu() {
    add_menu_page('cituro Booking Widget Settings', esc_html__('cituro.page.name', 'cituro-booking-widget'), 'manage_options', 'cituro-widget', 'cituro_settings_page', 
    'data:image/svg+xml;base64,PG5zMDpzdmcgeG1sbnM6bnMwPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmVyc2lvbj0iMS4wIiB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCA1MC4wMDAwMDAgNTAuMDAwMDAwIiBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJ4TWlkWU1pZCBtZWV0Ij4KCjxuczA6ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwLjAwMDAwMCw1MC4wMDAwMDApIHNjYWxlKDAuMTAwMDAwLC0wLjEwMDAwMCkiIGZpbGw9IiNmMGYwZjEiIHN0cm9rZT0ibm9uZSI+CjxuczA6cGF0aCBkPSJNMTc2IDQ4NSBjLTQ5IC0xNyAtMTEyIC03OSAtMTI3IC0xMjMgLTE4IC01NCAtNyAtMTM0IDI1IC0xODMgbDI4IC00MiAzMyAzOSBjMzEgMzYgMzIgNDEgMTkgNjEgLTE3IDI3IC0xOCA5MiAtMSAxMTMgNTUgNzAgMTY0IDYzIDIwMCAtMTIgbDEzIC0yOCA0MCA0MSAzOSA0MCAtNDYgNDQgYy02NiA2MiAtMTQxIDc5IC0yMjMgNTB6IiAvPgo8bnMwOnBhdGggZD0iTTM1MiAyNDQgbC0xMDIgLTEwOCAtMjggMjcgLTI4IDI3IC0zOCAtMzkgLTM3IC0zOCA2MyAtNTcgYzM1IC0zMSA2NiAtNTYgNjkgLTU2IDMgMCA0NyA0MiA5NyA5NCA3NiA3OSA5MyAxMDMgMTA2IDE0NyAxMCAzNCAxMyA2NSA4IDgyIC03IDI4IC04IDI3IC0xMTAgLTc5eiIgLz4KPC9uczA6Zz4KPC9uczA6c3ZnPg==');
}
add_action('admin_menu', 'cituro_admin_menu');

function cituro_validate_account_number($input) {
    $input = trim($input);
    if (preg_match('/^[0-9]{7}$/', $input)) {
        return $input;
    } else {
        add_settings_error(
            'cituro_account_number',
            'invalid_account_number',
            esc_html__('settings.error.invalid.customernumber', 'cituro-booking-widget'),
            'error'
        );
        return '';
    }
}

function cituro_load_textdomain() {
    $user_locale = get_user_locale();
    if ($user_locale == 'de_DE' || $user_locale == 'de_AT' || $user_locale == 'de_CH' || $user_locale == 'de_DE_formal') {
        load_plugin_textdomain( 'cituro-booking-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    } 
    else {
        $mo_file_path = dirname( plugin_basename( __FILE__ ) ) . '/languages/cituro-booking-widget-en_US.mo';
        load_textdomain( 'cituro-booking-widget', WP_PLUGIN_DIR . '/' . $mo_file_path );
    }
}
add_action('plugins_loaded', 'cituro_load_textdomain');

function cituro_settings_page() {
    $account_number = esc_attr(get_option('cituro_account_number', ''));
    if (!empty($account_number)) {
        $booking_widget_design_link= "https://app.cituro.com/adminui/{$account_number}#online-booking/booking-widget-design";
        $subscription_link = "https://app.cituro.com/adminui/{$account_number}#subscription";
    } else {
        $booking_widget_design_link= "https://app.cituro.com/adminui/#online-booking/booking-widget-design";
        $subscription_link = "https://app.cituro.com/adminui/#subscription";
    }
    $allowed_tags = array(
        'code' => array(),
        'a' => array(
            'href' => array(),
            'title' => array(),
            'target' => array('_blank', '_self')
        ),
    );
    $section_general_info = wp_kses(__('section.general.info', 'cituro-booking-widget'), $allowed_tags);
    $section_general_description = wp_kses(__('section.general.description', 'cituro-booking-widget'),$allowed_tags);
    $section_general_customer = wp_kses(__('section.general.customer.description', 'cituro-booking-widget'),$allowed_tags);
    ?>
    <div class="wrap">

        <h1><?php esc_html_e('menuoverview', 'cituro-booking-widget'); ?></h1>
        <?php settings_errors(); ?>
        <form method="post" action="options.php">
            
            <?php
            settings_fields('default');
            $info_text = str_replace($search = '#', $replace = '<a href = ' . esc_url("https://www.cituro.com/") . ', target="_blank">cituro</a>', $subject = $section_general_info);
            $desc_text = str_replace($search = '#', $replace = "<a href={$booking_widget_design_link}, target='_blank'>cituro Manager</a>", $subject=$section_general_description);
            $table_desc_text = str_replace($search = '#', $replace = "<a href={$subscription_link}, target='_blank'>cituro Manager</a>", $subject = $section_general_customer);
            
            do_settings_sections('default');
            ?>
            <h3><?php esc_html_e('section.general.header', 'cituro-booking-widget');?></h3>
            <p class="info_text"><?php echo wp_kses($info_text, $allowed_tags);?></p>
            <p class="description"><?php echo wp_kses($desc_text, $allowed_tags);?></p>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('section.general.customer.number', 'cituro-booking-widget'); ?></th>
                    <td>
                        <input id="customer_number_input_id" type="text" name="cituro_account_number" value="<?php echo esc_attr(get_option('cituro_account_number')); ?>" class="customer_number_input" />
                        <strong id = "error_message_text" style = "display: none; color: #c70000;"><?php esc_html_e('section.general.customer.error', 'cituro-booking-widget'); ?></strong>
                        <p class="description"><?php echo wp_kses($table_desc_text, $allowed_tags); ?></p>
                    </td>
                </tr>
            </table>
            <h3><?php esc_html_e('section.customize.header', 'cituro-booking-widget'); ?></h3>
            <p class="info_text"><?php esc_html_e('section.customize.info', 'cituro-booking-widget'); ?></p>
            <p class="description"><?php esc_html_e('section.customize.description', 'cituro-booking-widget'); ?></p>
            <table class="form-table" id="settings_table">
                <tr>
                    <th scope="row"><?php esc_html_e('section.customize.preset.service.label', 'cituro-booking-widget'); ?></th>
                    <td>
                        <input type="text" name="cituro_preset_service" id="settings_textfield" value="<?php echo esc_attr(get_option('cituro_preset_service')); ?>" class="regular-text" />
                        <p class="description">
                        <?php esc_html_e('section.customize.preset.service.description', 'cituro-booking-widget'); ?>
                            <span class="tooltip">
                                <span class="tooltiptext"><?php esc_html_e('section.customize.preset.service.tooltip', 'cituro-booking-widget'); ?></span>
                                <span class="dashicons dashicons-editor-help"></span>
                            </span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('section.customize.preset.category.label', 'cituro-booking-widget'); ?></th>
                    <td>
                        <input type="text" name="cituro_preset_category" id="settings_textfield" value="<?php echo esc_attr(get_option('cituro_preset_category')); ?>" class="regular-text" />
                        <p class="description">
                        <?php esc_html_e('section.customize.preset.category.description', 'cituro-booking-widget'); ?>    
                        <span class="tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="tooltiptext"><?php esc_html_e('section.customize.preset.category.tooltip', 'cituro-booking-widget'); ?></span>
                        </span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('section.customize.preset.resource.label', 'cituro-booking-widget'); ?></th>
                    <td>
                        <input type="text" name="cituro_preset_resource" id="settings_textfield" value="<?php echo esc_attr(get_option('cituro_preset_resource')); ?>" class="regular-text" />
                        <p class="description">
                        <?php esc_html_e('section.customize.preset.resource.description', 'cituro-booking-widget'); ?>    
                        <span class="tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="tooltiptext"><?php esc_html_e('section.customize.preset.resource.tooltip', 'cituro-booking-widget'); ?></span>
                        </span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('section.customize.preset.location.label', 'cituro-booking-widget'); ?></th>
                    <td>
                        <input type="text" name="cituro_preset_location" id="settings_textfield" value="<?php echo esc_attr(get_option('cituro_preset_location')); ?>" class="regular-text" />
                        <p class="description">
                        <?php esc_html_e('section.customize.preset.location.description', 'cituro-booking-widget'); ?>
                        <span class="tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="tooltiptext"><?php esc_html_e('section.customize.preset.location.tooltip', 'cituro-booking-widget'); ?></span>
                         </span> 
                        </p>
                    </td>
                </tr>
            </table>
            <h3><?php esc_html_e('section.advanced.header', 'cituro-booking-widget'); ?></h3>
            <p class="info_text"><?php esc_html_e('section.advanced.info', 'cituro-booking-widget'); ?></p>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('section.advanced.checkbox.title', 'cituro-booking-widget'); ?></th>
                    <td>
                        <input type="checkbox" id="cituro_enable_custom_script" name="cituro_enable_custom_script" value="1" <?php checked(1, get_option('cituro_enable_custom_script'), true); ?> />
                        <label for="cituro_enable_custom_script"><?php esc_html_e('section.advanced.checkbox.text', 'cituro-booking-widget'); ?></label>
                    </td>
                </tr>
                <tr id="cituro_custom_script_row" style="display: none;">
                    <th scope="row"><?php esc_html_e('section.advanced.customscript', 'cituro-booking-widget'); ?></th>
                    <td>
                        <textarea id = "custom_script_text_id" name="cituro_custom_script" rows="4" class="large-text code"><?php echo esc_textarea(get_option('cituro_custom_script')); ?></textarea>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function cituro_enqueue_custom_inline_script($hook) {
    if ($hook != 'toplevel_page_cituro-widget') {
        return;
    }
    wp_register_script($handle = 'cituro-inline-script', $src='', $deps = array(), $ver = '1.0.0', $in_footer=false);
    $inline_script = '
        document.addEventListener("DOMContentLoaded", function() {
            const checkbox = document.getElementById("cituro_enable_custom_script");
            const customScriptRow = document.getElementById("cituro_custom_script_row");
            const customSettings = document.getElementById("settings_table");
            const customer_number_inputField = document.getElementById("customer_number_input_id");
            const customer_number_inputValue = customer_number_inputField.value;
            const error_message_advanced = document.getElementById("error_message_text");
            const customScriptTextarea = document.getElementById("custom_script_text_id");

            function toggleCustomScriptRow() {
                if (checkbox.checked) {
                    if(customer_number_inputValue === "") {
                        error_message_advanced.style.display = "";
                        checkbox.checked = false;
                        window.scrollTo(0, 0);
                        return;
                    }
                    customScriptRow.style.display = "";
                    customSettings.style.opacity = 0.30;
                    if (customScriptTextarea.value.trim() === "") { 
                        customScriptTextarea.value = "<" + "script id=\"cituro-widget-loader\" src=\"https://app.cituro.com/booking-widget\" data-account-number=\"" + customer_number_inputValue + "\" defer></script" + ">";
                    }
                } 
                else {
                    customScriptRow.style.display = "none";
                    customSettings.style.opacity = 1;
                }
            }

            checkbox.addEventListener("change", toggleCustomScriptRow);
            toggleCustomScriptRow();
        });
    ';    
    wp_add_inline_script('cituro-inline-script', $inline_script);  
    wp_enqueue_script('cituro-inline-script');
}
add_action('admin_enqueue_scripts', 'cituro_enqueue_custom_inline_script');

function cituro_register_and_enqueue_script() {
    wp_register_script(
        'cituro-widget-loader',
        'https://app.cituro.com/booking-widget',
        array(),
        null,
        false,
        
    );
    wp_enqueue_script('cituro-widget-loader');
} 
add_action('wp_enqueue_scripts', 'cituro_register_and_enqueue_script');

function cituro_custom_script_tag($tag, $handle) {
    if ('cituro-widget-loader' === $handle) {
        $account_number   = get_option('cituro_account_number');
        $preset_service   = get_option('cituro_preset_service');
        $preset_category  = get_option('cituro_preset_category');
        $preset_resource  = get_option('cituro_preset_resource');
        $preset_location  = get_option('cituro_preset_location');

        if (get_option('cituro_enable_custom_script')) {
            $custom_script = get_option('cituro_custom_script', '');
            if (!empty($custom_script)) {
                return $custom_script;
            }
        }

        $script_tag = explode('>', $tag, 2)[0];
        $script_tag = str_replace('cituro-widget-loader-js', 'cituro-widget-loader', $script_tag);
    
        if(!empty($account_number)) {
            $script_tag .= " data-account-number='{$account_number}'";
        }

        if (!empty($preset_service)) {
            $script_tag .= " data-preset-service='{$preset_service}'";
        }

        if (!empty($preset_category)) {
            if (str_contains($preset_category, ',')) {
                $script_tag .= " data-preset-categories='{$preset_category}'";
            } else {
                $script_tag .= " data-preset-category='{$preset_category}'";
            }
        }

        if (!empty($preset_resource)) {
            $script_tag .= " data-preset-resource='{$preset_resource}'";
        }

        if (!empty($preset_location)) {
            $script_tag .= " data-preset-location='{$preset_location}'";
        }

        $script_tag .= " defer></script>";

        return $script_tag;
    }
    return $tag;
}
add_filter('script_loader_tag', 'cituro_custom_script_tag', 10, 3);

function cituro_booking_widget_admin_notice() {
    if (get_option('cituro_booking_widget_opened') === false) {
        echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__('admin.notice.text', 'cituro-booking-widget') . '</p></div>';
        update_option('cituro_booking_widget_opened', true);
    }
}

function cituro_booking_widget_check_settings_page() {
    $current_screen = get_current_screen();
    if ($current_screen->id === 'toplevel_page_cituro-widget') {
        add_action('admin_notices', 'cituro_booking_widget_admin_notice');
    }
}
add_action('admin_head', 'cituro_booking_widget_check_settings_page');

function cituro_enqueue_admin_styles($hook) {
    if ($hook != 'toplevel_page_cituro-widget') {
        return;
    }
    wp_enqueue_style('cituro-widget-settings-css', plugins_url('settings_style.css', __FILE__), $deps = array(), $ver = '1.0.0');
}
add_action('admin_enqueue_scripts', 'cituro_enqueue_admin_styles');