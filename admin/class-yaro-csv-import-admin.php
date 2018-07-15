<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Yaro_Csv_Import
 * @subpackage Yaro_Csv_Import/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Yaro_Csv_Import
 * @subpackage Yaro_Csv_Import/admin
 * @author     Daniil Vinokurov
 */

require_once plugin_dir_path(dirname(__FILE__)) . 'admin/libs/parsecsv.lib.php';

class Yaro_Csv_Import_Admin
{
    # include parseCSV class.

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_slug    The ID of this plugin.
     */
    private $plugin_slug;

    /**
     * The name of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The name of this plugin.
     */
    private $plugin_name;

    /**
     * The csv file.
     *
     * @since    1.0.0
     * @access   private
     * @var      string      Uploaded file's path or false
     */
    private $file;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_slug       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_slug, $version)
    {

        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        $this->plugin_name = 'Yaro csv import';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Yaro_Csv_Import_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Yaro_Csv_Import_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_slug, plugin_dir_url(__FILE__) . 'css/yaro-csv-import-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Yaro_Csv_Import_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Yaro_Csv_Import_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_slug, plugin_dir_url(__FILE__) . 'js/yaro-csv-import-admin.js', array( 'jquery' ), $this->version, true);
    }

    public function plugin_action_links($links)
    {
        $links[] = '<a href="'.
        esc_url(get_admin_url(null, 'options-general.php?page='. $this->plugin_slug)) .
        '">' . __('Setting', 'yaro-csv-import') . '</a>';
        return $links;
    }

    public function plugin_menu()
    {
        add_options_page(
            $this->plugin_name . __(' settings', 'yaro-csv-import'),
            $this->plugin_name,
            'manage_options',
            $this->plugin_slug,
            array($this, 'pluginPage')
        );
    }

    public function pluginPage()
    {
        if (!current_user_can('manage_options')) {
            echo 'You do not have sufficient permissions to access this page.';
            wp_die(__(_e('You do not have sufficient permissions to access this page.')));
        }
        $this->display();
        include_once 'partials/yaro-csv-import-admin-display.php';
    }
    private function fileNewUpload()
    {
        echo '<div class="narrow">';
        echo '<p>' . __('Choose a WXR (.csv) file to upload, then click Upload file and import.', 'yaro-csv-import') . '</p>';
        wp_import_upload_form('admin.php?page=' . $this->plugin_slug . '&amp;step=2');
        echo '</div>';
    }

    private function handleFileUpload()
    {
        $file = wp_import_handle_upload();
        if (isset($file['error']) && !empty($file['error'])) {
            return false;
        }
        return $file['file'];
    }

    public function display()
    {
        echo '<div class="wrap">';
        echo '<h2>' . __($this->plugin_name, 'yaro-csv-import') . '</h2>';
        $tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'import';
        $step = (!empty($_GET['step']))? esc_attr($_GET['step']) : '1';
        $this->pageTabs($tab);
        if ($tab == 'import') {
            echo '<div class="yaro-panel">';
            echo '<h3>' . __('Import csv file', 'yaro-csv-import') . '</h3>';
            switch ($step) {
                case 1:
                    $this->fileNewUpload();
                    break;
                case 2:
                    $this->file = $this->handleFileUpload();
                    if ($this->file) {
                        update_option('csv_file_location', $this->file);
                        $this->getCsvHeaders();
                    }
                    break;
                case 3:
                    $this->importCsvAllData();
                    break;
            }
            echo '</div>';
        } elseif ($tab == 'settings') {
            echo '<div class="yaro-panel">';
            echo '<h3>' . __('Settings title', 'yaro-csv-import') . '</h3>';
            echo '<form method="post" action="options.php">';
            settings_fields($this->plugin_slug . '-settings-group');
            do_settings_sections($this->plugin_slug . '-settings-group');
            submit_button();
            echo '</form>';
            echo '</div>';
        } else {
            echo '<div>';
            echo '<h3>' . __('Manual title', 'yaro-csv-import') . '</h3>';
            echo '<p>';
            _e('Manual text', 'yaro-csv-import');
            echo '</p>';
            echo '</div>';
        }
        return true;
    }

    private function getCsvHeaders()
    {
        $csv = new parseCSV();
        $csv->auto(get_option('csv_file_location'));
        echo '<h4>'. __('Titles from csv file', 'yaro-csv-import').'</h4>';
        foreach ($csv->titles as $value) {
            echo '<p>' . $value . '</p>';
        }
        echo '<a class="button button-primary" href="?page='. $this->plugin_slug .'&tab=import&step=3">' . __('Import', 'yaro-csv-import') . '</a>';
        echo '<br>';
        echo '<button class="button button-primary" id="import-btn">' . __('Ajax import', 'yaro-csv-import') . '</button>';
        echo '<div id="import-log"></div>';
    }
    private function getCustomMeta($post_data)
    {
        $custom_meta = array();
        foreach ($post_data as $key => $value) {
            if ($value!='' && stripos($key, 'custom') !== false) {
                $header = explode('_', $key);
                if (isset($header[2])) {
                    $custom_meta[$header[0] . '_' . $header[1]][$header[2]] = htmlentities($value);
                } else {
                    $custom_meta[$header[0] . '_' . $header[1]] = htmlentities($value);
                }
            }
        }
        return $custom_meta;
    }

    public function importCsvItemCallback()
    {
        $offset = $_POST['offset'];
        $csv = new parseCSV();
        $csv->auto(get_option('csv_file_location'));
        $response = new stdClass();
        if ($offset==0) {
            $this->deleteAllPostMeta($csv);
        }
        if (isset($csv->data[$offset])) {
            $csv_row->data[0] = $csv->data[$offset];
            $response->message =  $this->importCsvData($csv_row, $offset);
            $response->offset = ++$offset;
        } else {
            $response->message = 'eof';
            $response->error = true;
        }
        wp_send_json($response);
    }

    private function importCsvAllData()
    {
        $csv = new parseCSV();
        $csv->auto(get_option('csv_file_location'));
        echo '<h3>'. __('Import log', 'yaro-csv-import').'</h3>';
        $this->deleteAllPostMeta($csv);
        echo $this->importCsvData($csv);
    }
    private function deleteAllPostMeta($csv)
    {
        foreach ($csv->data as $key => $row) {
            if (isset($row['ID']) && $row['ID']!='') {
                $post_id = $row['ID'];
            } elseif (post_exists($row['post_title'])) {
                $post_id = post_exists($row['post_title']);
            }
            $this->deletePostMeta($post_id, 'yaro_');
        }
    }
    private function importCsvData($csv, $offset = null)
    {
        //ignore_user_abort(true);
        //set_time_limit(0);
        $i = 0;
        $import_log = '';
        foreach ($csv->data as $key => $row) {
            $i++;
            $post = array();
            if (isset($row['ID']) && $row['ID']!='') {
                $post['ID'] = $row['ID'];
            } elseif (post_exists($row['post_title'])) {
                $post['ID'] = post_exists($row['post_title']);
            }
            if (isset($row['menu_order']) && $row['menu_order']!='') {
                $post['menu_order'] = $row['menu_order'];
            }
            if (isset($row['comment_status']) && $row['comment_status']!='') {
                if ($row['comment_status']==='1') {
                    $post['comment_status'] = 'open';
                } else {
                    $post['comment_status'] = 'closed';
                }
            }
            if (isset($row['post_author']) && $row['post_author']!='') {
                $post['post_author'] = $row['post_author'];
            }
            if (isset($row['post_content']) && $row['post_content']!='') {
                $post['post_content'] = $row['post_content'];
            } else {
                $post_wp = get_post( $post['ID'], OBJECT);
                if (isset($post_wp->post_content)) {
                    $post['post_content'] = $post_wp->post_content;
                }
            }
            if (isset($row['post_date_gmt']) && $row['post_date_gmt']!='') {
                $post['post_date_gmt'] = $row['post_date_gmt'];
            }
            if (isset($row['post_excerpt']) && $row['post_excerpt']!='') {
                $post['post_excerpt'] = $row['post_excerpt'];
            }
            if (isset($row['post_name']) && $row['post_name']!='') {
                $post['post_name'] = $row['post_name'];
            }
            if (isset($row['post_parent']) && $row['post_parent']!='') {
                $post['post_parent'] = post_exists($row['post_parent']);
            }
            if (isset($row['post_status']) && $row['post_status']!='') {
                $post['post_status'] = $row['post_status'];
            }
            if (isset($row['post_title']) && $row['post_title']!='') {
                $post['post_title'] = $row['post_title'];
            }
            if (isset($row['post_type']) && $row['post_type']!='') {
                $post['post_type'] = $row['post_type'];
            } else {
                $post['post_type'] = 'page';
            }
            if (isset($row['post_category']) && $row['post_category']!='') {
                $post['post_category'] = $row['post_category'];
            }
            if (isset($row['tags_input']) && $row['tags_input']!='') {
                $post['tags_input'] = $row['tags_input'];
            }
            $meta_input = $this->getPostMeta($row);
            if ($meta_input) {
                $post['meta_input'] = $meta_input;
            }
            if (isset($post['post_status'])) {
                $post_id = wp_insert_post(wp_slash($post), true);
            } else {
                $post_id = post_exists($row['post_title']);
            }
            if (isset($offset)) {
                $step_row = $offset;
            } else {
                $step_row = $i;
            }
            if (is_wp_error($post_id)) {
                $import_log .='<p>'. $step_row . ': ' . $post_id->get_error_message() . '</p>';
            } else {
                $import_log .='<p>'. $step_row . ': ' . __('Added/updated page: ', 'yaro-csv-import') . $post_id . '</p>';
                if (isset($row['thumbnail_image']) && $row['thumbnail_image']!='') {
                    $this->addPostAttachment($row['thumbnail_image'], $post_id, $row['post_title']);
                }
                $this->setCustomMeta($post_id, $row);
            }
        }
        return $import_log;
    }

    private function getPostMeta($post_data)
    {
        $meta = array();
        if (isset($post_data['wp_page_template']) && $post_data['wp_page_template']!='') {
            $meta['_wp_page_template'] = $post_data['wp_page_template'];
        } else {
            return false;
        }
        return $meta;
    }

    private function deletePostMeta($post_id, $preffix)
    {
        $post_meta = get_post_meta($post_id);
        //delete all meta data with preffix yaro_
        if (is_array($post_meta)) {
            foreach ($post_meta as $key => $value) {
                if (count($value)>1 && stripos($key, $preffix) !== false) {
                    delete_post_meta($post_id, $key);
                }
            }
        }
    }

    private function setCustomMeta($post_id, $post_data)
    {

        $post_meta = $this->getCustomMeta($post_data);
        foreach ($post_meta as $key => $value) {
            if (is_array($value)) {
                $meta_value = json_encode($value, JSON_UNESCAPED_UNICODE);
            } else {
                $meta_value = $value;
            }
            $header = explode('_', $key);

            if (stripos($header[0], 'multiple') !== false) {
                add_post_meta($post_id, 'yaro_' . $header[1], $meta_value, false);
            } else {
                //meta is not array
                update_post_meta($post_id, 'yaro_' . $header[1], $meta_value);
                //add_post_meta($post_id, 'yaro_' . $header[1], $meta_value, true);
            }
        }
    }
    
    /**
     * retrieves the attachment ID from the file URL
     */
    private function checkAttachmentId($image_guid)
    {
        global $wpdb;
        $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_guid));
        if ($attachment) {
            return $attachment[0];
        } else {
            return false;
        }
    }

    private function addPostAttachment($image, $post_id, $post_title)
    {
        $upload_dir = wp_get_upload_dir();
        $filename = $upload_dir['basedir'] . str_replace('wp-content/uploads', '', ltrim($image, '/'));
        $guid = get_site_url() . '/' . ltrim($image, '/');
        $attach_id = $this->checkAttachmentId($guid);
        if (!$attach_id) {
            // Check the type of post that we will use in the 'post_mime_type' field.
            $filetype = wp_check_filetype(basename($filename), null);
            
            // Download folder
            $wp_upload_dir = wp_upload_dir();
            
            $attachment = array(
                'guid'           => $guid,
                'post_mime_type' => $filetype['type'],
                'post_title'     => $post_title,
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
        }
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
        wp_update_attachment_metadata($attach_id, $attach_data);

        set_post_thumbnail($post_id, $attach_id);
    }

    private function getPostByTitle($post_title)
    {
        global $wpdb;
        $table = $wpdb->prefix . '_posts';
        $data = $wpdb->get_row("SELECT ID FROM $table WHERE post_title=$post_title");
        if ($data) {
            return $data['ID'];
        } else {
            return false;
        }
    }

    public function pageTabs($current = 'import')
    {
        $tabs = array(
            'import'    =>  __('Import', 'yaro-csv-import'),
            'settings'  =>  __('Settings', 'yaro-csv-import'),
            'manual'    =>  __('Manual', 'yaro-csv-import')
        );
        $html =  '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ($tab == $current) ? 'nav-tab-active' : '';
            $html = $html . '<a class="nav-tab ' . $class . '" href="?page='. $this->plugin_slug .'&tab=' . $tab . '">' . $name . '</a>';
        }
        $html = $html . '</h2>';
        echo $html;
    }

    /**
     * Array with options for plugin
     * for checkbox type boolean
     * for input types string, number, integer
     * examples
     *'testCheck' => [
     *    'type' => 'boolean',
     *    'title' => 'Option title',
     *    'input' => 'checkbox',
     *    'default' => false
     *  ],
     *'testInput' => [
     *   'type' => 'string',
     *   'title' => 'Option title',
     *   'input' => 'input',
     *   'default ' => null
     *]
     **/
    private function getOptions()
    {
        $options = array();
        return $options;
    }

    public function register_plugin_settings()
    {
        $options = $this->getOptions();
        foreach ($options as $key => $args) {
            register_setting($this->plugin_slug . '-settings-group', $key, $args);
        }
    }
}
