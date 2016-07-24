<?php

namespace Tutbot;

class Core
{
    private static $instance = null; //instance store

    private function __construct()
    {
        add_action('plugins_loaded', [$this, 'init']);
        add_filter('gwptb_supported_commnds_list', [$this, 'customCommands'], 20);
    }

    public static function getInstance()
    {
        if (null === self :: $instance) {
            self::$instance = new self;
        }

        return self :: $instance;
    }

    public function init()
    {
        if (!class_exists('Gwptb_Core')) {
            return;
        }

        add_action('init', [$this, 'customPostTypes']);

        if (is_admin()) {
            add_action('admin_init', [$this, 'settingsInit'], 15);
        }
    }

    public function customPostTypes()
    {
        register_post_type(
            'quote',
            [
            'labels' => [
                'name' => 'Цитаты',
                'singular_name' => 'Цитата',
                'menu_name' => 'Цитаты',
                'name_admin_bar' => 'Добавить цитату',
                'add_new' => 'Добавить новую',
                'add_new_item' => 'Добавить новую',
                'new_item' => 'Новая цитат',
                'edit_item' => 'Редактировать цитату',
                'view_item' => 'Просмотр цитаты',
                'all_items' => 'Все цитаты',
                'search_items' => 'Искать цитаты',
                'parent_item_colon' => 'Родительская цитата:',
                'not_found' => 'Цитаты не найдены',
                'not_found_in_trash' => 'В Корзине цитаты не найдены'
            ],
            'public' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            //'query_var'           => true,
            'capability_type' => 'post',
            'has_archive' => false,
            'rewrite' => false,
            'hierarchical' => false,
            'menu_position' => 25,
            //'menu_icon'           => 'dashicons-calendar',
            'supports' => ['title', 'editor', 'author'],
            'taxonomies' => [],
            ]
        );

        register_post_type(
            'place',
            [
            'labels' => [
                'name' => 'Места',
                'singular_name' => 'Место',
                'menu_name' => 'Места',
                'name_admin_bar' => 'Добавить место',
                'add_new' => 'Добавить новое',
                'add_new_item' => 'Добавить новое',
                'new_item' => 'Новое место',
                'edit_item' => 'Редактировать место',
                'view_item' => 'Просмотр места',
                'all_items' => 'Все места',
                'search_items' => 'Искать места',
                'parent_item_colon' => 'Родительское место:',
                'not_found' => 'Места не найдены',
                'not_found_in_trash' => 'В Корзине места не найдены'
            ],
            'public' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            //'query_var'           => true,
            'capability_type' => 'post',
            'has_archive' => false,
            'rewrite' => false,
            'hierarchical' => false,
            'menu_position' => 25,
            //'menu_icon'           => 'dashicons-calendar',
            'supports' => ['title', 'editor', 'author', 'excerpt', 'custom-fields'],
            'taxonomies' => [],
            ]
        );
    }

    public function customCommands($commands)
    {
        $commands['q'] = 'tut_q_command_result';
        $commands['m'] = 'tut_m_command_result';

        return $commands;
    }

    public function settingsInit()
    {
        register_setting('gwptb_settings', 'tut_donation_url', ['GWPTB_Filters', 'sanitize_url']);

        add_settings_field('tut_donation_url', 'Ссылка "сделать пожертвование"', [$this, 'donationUrlRender'], 'gwptb_settings', 'gwptb_bot_section');
    }

    public function donationUrlRender()
    {
        $value = get_option('tut_donation_url');
        ?>
        <input type='text' name='tut_donation_url' value='<?php echo $value; ?>' class="large-text">
        <?php
    }

    public static function getDonationUrl()
    {
        $value = get_option('tut_donation_url');
        return (!empty($value)) ? esc_url($value) : '';
    }
}