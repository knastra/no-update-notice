<?php
/*
Plugin Name: No Update Notice
Description: Disable update notifications for selected plugins.
Author: Ara-Soft
Author URI: https://ara-soft.com
Version: 1.0
*/

function remove_selected_plugin_updates($value) {
    $plugins_to_disable = get_option('plugins_to_disable', array());

    if (isset($value->response) && is_array($value->response)) {
        foreach ($value->response as $plugin => $plugin_info) {
            if (in_array($plugin, $plugins_to_disable)) {
                unset($value->response[$plugin]);
            }
        }
    }

    return $value;
}

add_filter('site_transient_update_plugins', 'remove_selected_plugin_updates');

function no_update_selective_menu() {
    add_menu_page('No Update Notice', 'No Update Notice', 'manage_options', 'no-update-selective', 'no_update_selective_options');
}

add_action('admin_menu', 'no_update_selective_menu');

function no_update_selective_options() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Salva as opções se o formulário foi enviado
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['plugins'])) {
            update_option('plugins_to_disable', $_POST['plugins']);
        } else {
            update_option('plugins_to_disable', array());
        }
    }

    // Obtém a lista de plugins e atualizações disponíveis
    $all_plugins = get_plugins();
    $plugins_to_disable = get_option('plugins_to_disable', array());
    $updates = get_site_transient('update_plugins');

    echo '<div class="wrap">';
    echo '<h2>No Update Notice</h2>';
    echo '<form method="post">';
    echo '<table class="wp-list-table widefat fixed striped">';

    echo '<thead><tr><th scope="col" class="check-column"></th><th scope="col" style="width: 1%; text-align: left;"></th><th scope="col">Plugin Name</th></tr></thead>';

    foreach ($all_plugins as $plugin_file => $plugin_data) {
        $has_update = isset($updates->response[$plugin_file]);
        echo '<tr>';
        echo '<td class="check-column" style="padding-left: 20px;"><input type="checkbox" name="plugins[]" value="' . $plugin_file . '"' . (in_array($plugin_file, $plugins_to_disable) ? ' checked="checked"' : '') . ' /></td>';
        echo '<td style="width: 1%; text-align: left;">' . ($has_update ? '🔔' : '') . '</td>';
        echo '<td>' . $plugin_data['Name'] . '</td>';
        echo '</tr>';
    }

    echo '</table>';
    echo '<p class="submit"><input type="submit" class="button-primary" value="Save Changes" /></p>';
    echo '</form>';
    
    // Adicione o link e o QR Code para doações aqui
    echo '<div style="margin-top: 20px;">';
    echo '<h3>Donations</h3>';
    echo '<p>If you find this plugin useful and would like to support the development, please consider making a donation:</p>';
    echo '<p><a href="https://www.paypal.com/donate/?hosted_button_id=MZ34RBA8G8TMU">Click here to donate</a></p>';
    echo '<p>Or scan the QR Code below:</p>';
    echo '<p><img src="https://ara-soft.com/wp-content/uploads/2024/02/QR-Code.png" alt="QR Code" /></p>';
    echo '</div>';

    echo '</div>';
}

// Adiciona algum estilo CSS para a página de opções
function no_update_selective_css() {
    echo '
    <style type="text/css">
        .wp-list-table {
            margin-top: 20px;
        }
        .wp-list-table td {
            padding: 10px;
        }
    </style>
    ';
}

add_action('admin_head', 'no_update_selective_css');
?>
