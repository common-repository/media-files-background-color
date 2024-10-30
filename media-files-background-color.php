<?php

/**
 * Plugin Name: Media Files Background Color
 * Version: 1.0.2
 * Plugin URI: http://websiter.ro
 * Description: Pick a custom bg color for media files in Settings > Media. Useful when working with white files on transparent backgrounds.
 * Tags: color picker, media files, media, custom background, wp-admin styling
 * Author: Andrei Gheorghiu
 * Author URI: mailto:andrei.gheorghiu@evolution.ro?Subject=mfbgc%20plugin
 * License: GPL2
 * Text Domain: mfbgc
 * Domain Path: /languages
 */
class MFBgC
{
    private static $ins = null;
    public $slug = 'mfbgc';
    public $options = [
        'type' => 'color',
        'value' => '#c1ceaf'
    ];

    /**
     * @return mixed|void
     */
    public function background_color() {
        return get_option($this->slug . '_background_color', '#c1ceaf');
    }

    /**
     * @return mixed|void
     */
    public function use_pattern() {
        return get_option($this->slug . '_use_pattern', false);
    }

    /**
     *
     */
    public static function init() {
        add_action('plugins_loaded', array(self::instance(), '_setup'));
    }

    /**
     * @return MFBgC
     */
    public static function instance() {
        is_null(self::$ins) && self::$ins = new self;

        return self::$ins;
    }

    /**
     *
     */
    public function _setup() {
        $this->load_textdomain();
        add_action(
            'admin_head',
            array($this, 'admin_head')
        );
        add_action(
            'admin_init',
            array($this, 'admin_init')
        );
        add_action(
            'admin_enqueue_scripts',
            array($this, 'admin_enqueue_scripts')
        );
        add_filter(
            "plugin_action_links_".plugin_basename(__FILE__),
            array($this, 'add_settings_link')
        );
    }

    /**
     * @param $domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'mfbgc',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    /**
     *
     */
    public function admin_head() {
        echo '
<style type="text/css">
    div div .attachment-preview .thumbnail,
    .edit-attachment-frame .attachment-media-view .details-image,
    .imgedit-crop-wrap {
        '.($this->use_pattern() ?
                'background-image: url("'.plugins_url("/assets/pattern.png",__FILE__) . '");' :
                '').
            'background-color: ' . $this->background_color() . ';'.'
    }
</style>';
    }

    /**
     *
     */
    public function admin_init() {
        register_setting(
            'media',
            $this->slug . '_background_color',
            array($this, 'validate_options')
        );
        register_setting(
            'media',
            $this->slug . '_use_pattern'
        );
        add_settings_section(
            $this->slug . '_settings',
            '<span id="mfbgc"></span>'.__('Media files background color', 'mfbgc'),
            array($this, 'settings_text'),
            'media'
        );
        add_settings_field(
            $this->slug . '_background_color',
            __('Background color', 'mfbgc'),
            array($this, 'display_colorpicker'),
            'media',
            $this->slug . '_settings'
        );
        add_settings_field(
            $this->slug . '_use_pattern',
            false,
            array($this, 'display_pattern_check'),
            'media',
            $this->slug . '_settings'
        );
    }

    /**
     *
     */
    public function admin_enqueue_scripts() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script(
            $this->slug . '_js',
            plugins_url('assets/jquery.' . $this->slug . '.js', __FILE__),
            array('jquery', 'wp-color-picker'),
            '',
            true
        );
    }

    /**
     *
     */
    public function display_pattern_check() {
        echo "<label for='{$this->slug}_use_pattern'>" .
            "<input name='{$this->slug}_use_pattern' id='{$this->slug}_use_pattern' type='checkbox' value='1' ".checked(1, $this->use_pattern(), false)." />" .
            __("Use pattern", 'mfbgc') .
            "</label>";
    }

    /**
     *
     */
    public function display_colorpicker() {
        $val = $this->background_color();
        echo '<input type="text" name="' . $this->slug . '_background_color' . '" value="' . $val . '" class="' . $this->slug . '-color-picker" >';
    }

    public function settings_text() {
        echo __('Pick a background color for your media files.', 'mfbgc');
    }

    /**
     * @param $value
     * @return bool
     */
    public function validate_color($value) {
        if (preg_match('/^#[a-f0-9]{6}$/i', $value)) {
            return true;
        }

        return false;
    }

    /**
     * @param $input
     * @return mixed|string|void
     */
    public function validate_options($input) {
        $color = strip_tags(stripslashes($input));
        if ($this->validate_color($color)) {
            return $color;
        }
        add_settings_error(
            $this->slug . '_plugin',
            $this->slug . '_settings',
            __('The media background color you tried to insert is not valid.', 'mfbgc'),
            'error'
        );

        return $this->background_color();
    }

    /**
     * @param $links
     * @return array
     */
    public function add_settings_link( $links ) {
        return array_merge($links, ['<a href="options-media.php#mfbgc">' . __( 'Pick', 'mfbgc' ) . '</a>']);
    }
}

MFBgC::init();