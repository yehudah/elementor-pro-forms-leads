<?php
/*
Plugin Name: Elementor Pro Forms Leads
Description: Save and view messages by Elementor Pro Forms 
Plugin URI: http://wpdev.co.il
Author: Yehuda Hassine
Author URI: http://wpdev.co.il
Version: 1.0
License: GPL3
Text Domain: epfl
*/

class Epfl {

	const META_PREFIX = 'epfl_';

	private $email_text;

	private $columns;

	public function __construct() {
		$this->hooks();
		$this->init();
	}

	public function hooks() {
		add_action( 'init', [ $this, 'add_cpt' ] );
		add_filter( 'manage_epfl_leads_posts_columns', [ $this, 'set_custom_edit_leads_columns' ] );
		add_action( 'manage_epfl_leads_posts_custom_column' , [ $this, 'custom_leads_column' ], 10, 2 );		
		add_filter( 'elementor_pro/forms/wp_mail_message', [ $this, 'save_email_text' ] );
		add_action( 'elementor_pro/forms/mail_sent', [ $this, 'save_lead' ], 10, 3 );		
	}

	public function init() {
		$this->columns  = array(
			'date' => __( 'Date', 'elementor-pro' ),
			'time' => __( 'Time', 'elementor-pro' ),
			'page_url' => __( 'Page URL', 'elementor-pro' ),
			'user_agent' => __( 'User Agent', 'elementor-pro' ),
			'remote_ip' => __( 'Remote IP', 'elementor-pro' ),
		);		
	}

	function add_cpt() {

		$labels = array(
			'name'                  => _x( 'Leads', 'Post Type General Name', 'epfl' ),
			'singular_name'         => _x( 'Lead', 'Post Type Singular Name', 'epfl' ),
			'menu_name'             => __( 'Leads', 'epfl' ),
			'name_admin_bar'        => __( 'Leads', 'epfl' ),
			'archives'              => __( 'Item Archives', 'epfl' ),
			'attributes'            => __( 'Item Attributes', 'epfl' ),
			'parent_item_colon'     => __( 'Parent Item:', 'epfl' ),
			'all_items'             => __( 'All Items', 'epfl' ),
			'add_new_item'          => __( 'Add New Item', 'epfl' ),
			'add_new'               => __( 'Add New', 'epfl' ),
			'new_item'              => __( 'New Item', 'epfl' ),
			'edit_item'             => __( 'Edit Item', 'epfl' ),
			'update_item'           => __( 'Update Item', 'epfl' ),
			'view_item'             => __( 'View Item', 'epfl' ),
			'view_items'            => __( 'View Items', 'epfl' ),
			'search_items'          => __( 'Search Item', 'epfl' ),
			'not_found'             => __( 'Not found', 'epfl' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'epfl' ),
			'featured_image'        => __( 'Featured Image', 'epfl' ),
			'set_featured_image'    => __( 'Set featured image', 'epfl' ),
			'remove_featured_image' => __( 'Remove featured image', 'epfl' ),
			'use_featured_image'    => __( 'Use as featured image', 'epfl' ),
			'insert_into_item'      => __( 'Insert into item', 'epfl' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'epfl' ),
			'items_list'            => __( 'Items list', 'epfl' ),
			'items_list_navigation' => __( 'Items list navigation', 'epfl' ),
			'filter_items_list'     => __( 'Filter items list', 'epfl' ),
		);
		$args = array(
			'label'                 => __( 'Lead', 'epfl' ),
			'description'           => __( 'Leads For Elementor Pro Forms', 'epfl' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-groups',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,		
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'rewrite'               => false,
			'capability_type'       => 'post',
		);
		register_post_type( 'epfl_leads', $args );

	}


	function set_custom_edit_leads_columns($columns) {
	    foreach ( $this->columns as $key => $value ) {
	    	$columns[ $key ] = $value;
	    }

	    return $columns;
	}

	function custom_leads_column( $column, $post_id ) {
		if ( isset( $this->columns[ $column ] ) ) {
			echo get_post_meta( $post_id, self::META_PREFIX . $column, true );
		}
	}	


	public function save_email_text( $email_text ) {
		$this->email_text = $email_text;

		return $email_text;
	}

	public function save_lead( $form_id, $settings, $record ) {

		foreach ($record['fields'] as $key => $field) {
			if ( 'email' === $field['type'] ) {
				$email_from = $field['value'];
			}
		}

		$args = array(
			'post_type' => 'epfl_leads',
			'post_status' => 'publish',
			'post_title' => $email_from,
			'post_content' => $this->email_text,
		);

		$lead_id = wp_insert_post( $args, true );

		if ( ! is_wp_error( $lead_id ) ) {
			foreach ( $record['meta'] as $key => $meta ) {
				update_post_meta( $lead_id, self::META_PREFIX . $meta['type'], $meta['value'] );
			}
		}
	}
}

new Epfl;
