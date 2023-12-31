<?php

class NEONCPT {
	protected $post_type_id;
	protected $slug;
	protected $args = [];

	public function __construct( $args ) {
		$this->args = $args;

		$this->post_type_id = $args['id'];
		$slug               = ! empty( $args['rewrite'] ) ? $args['rewrite'] : $args['id'];
		$this->slug         = apply_filters( "{$args['id']}_rewrite_slug", $slug );

		add_action( 'init', [ $this, '_register' ] );

		$this->customColumnsInit();
	}

	public function getSlug() {
		return $this->slug;
	}

	public function _register() {
		$labels = array(
			'name'                  => $this->args['title'],
			'singular_name'         => $this->args['title'],
			'menu_name'             => $this->args['title'],
			'name_admin_bar'        => $this->args['title'],
			'add_new'               => 'Add New',
			'add_new_item'          => 'Add New',
			'new_item'              => 'New ' . $this->args['title'],
			'edit_item'             => 'Edit ' . $this->args['title'],
			'view_item'             => 'View ' . $this->args['title'],
			'all_items'             => ! isset( $this->args['show_in_menu'] ) || $this->args['show_in_menu'] === true ? 'All ' . $this->args['title'] : $this->args['title'],
			'search_items'          => 'Search ' . $this->args['title'],
			'parent_item_colon'     => 'Parent ' . $this->args['title'],
			'not_found'             => 'No ' . $this->args['title'] . ' found.',
			'not_found_in_trash'    => 'No ' . $this->args['title'],
			'featured_image'        => $this->args['title'] . ' Image',
			'set_featured_image'    => 'Set image',
			'remove_featured_image' => 'Remove image',
			'use_featured_image'    => 'Use as image',
			'archives'              => $this->args['title'] . ' archives',
			'insert_into_item'      => 'Insert into ' . $this->args['title'],
			'uploaded_to_this_item' => 'Uploaded to this ' . $this->args['title'],
			'filter_items_list'     => 'Filter ' . $this->args['title'],
			'items_list_navigation' => $this->args['title'] . ' list navigation',
			'items_list'            => $this->args['title'] . ' list',
		);

		$publicly_queryable    = $this->args['publicly_queryable'] ?? true;
		$disable_in_front_page = apply_filters( "{$this->args['id']}_publicly_queryable", $publicly_queryable );

		$is_hierarchical = empty( $this->args['hierarchical'] ) ? false : $this->args['hierarchical'];
		if ( $is_hierarchical ) {
			$this->args['supports'][] = 'page-attributes';
		}

		register_post_type( $this->args['id'],
			array(
				'labels'              => $labels,
				'public'              => true,
				'publicly_queryable'  => $disable_in_front_page,
				'show_ui'             => true,
				'show_in_menu'        => ! isset( $this->args['show_in_menu'] ) ? true : $this->args['show_in_menu'],
				'show_in_admin_bar'   => $disable_in_front_page,
				'query_var'           => true,
				'rewrite'             => array( 'slug' => $this->slug ),
				'has_archive'         => ! isset( $this->args['has_archive'] ) ? true : $this->args['has_archive'],
				'exclude_from_search' => apply_filters( "{$this->args['id']}_exclude_from_search", ! $disable_in_front_page ),
				'hierarchical'        => $is_hierarchical,
				'menu_position'       => $this->args['menu_position'],
				'menu_icon'           => $this->args['menu_icon'],
				'supports'            => $this->args['supports'],
				'show_in_rest'        => ! empty( $this->args['show_in_rest'] ) ? $this->args['show_in_rest'] : false,
			)
		);

		if ( ! empty( $this->args['group'] ) ) {

			$this->taxonomies( $this->args['id'], $this->args['group'] );
		}
	}

	protected function taxonomies( $id, $taxonomy_args ) {
		foreach ( $taxonomy_args as $group ) {
			$labels = array(
				'name'              => $group['title'],
				'singular_name'     => $group['title'],
				'search_items'      => 'Search ' . $group['title'],
				'all_items'         => 'All ' . $group['title'],
				'parent_item'       => 'Parent ' . $group['title'],
				'parent_item_colon' => 'Parent ' . $group['title'] . ':',
				'edit_item'         => 'Edit ' . $group['title'],
				'update_item'       => 'Update ' . $group['title'],
				'add_new_item'      => 'Add New ' . $group['title'],
				'new_item_name'     => 'New ' . $group['title'] . ' Name',
				'menu_name'         => $group['title'],
			);

			$args_tax = array(
				'hierarchical'      => ! empty( $group['hierarchical'] ) ? $group['hierarchical'] : false,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => ! empty( $group['show_admin_column'] ) ? $group['show_admin_column'] : false,
				'show_in_rest'      => ! empty( $this->args['show_in_rest'] ) ? $this->args['show_in_rest'] : false,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => empty( $group['rewrite'] ) ? $group['id'] : $group['rewrite'] ),
			);

			register_taxonomy( $group['id'], array( $id ), $args_tax );
		}
	}

	protected function getId() {
		return $this->post_type_id;
	}

	public function customColumnsInit() {
		add_filter( "manage_{$this->post_type_id}_posts_columns", [ $this, 'setColumNames' ] );
		add_action( "manage_{$this->post_type_id}_posts_custom_column", [ $this, 'setColumnValue' ], 10, 2 );
		add_filter( "manage_edit-{$this->post_type_id}_sortable_columns", [ $this, 'setSortableColumns' ] );
		add_action( 'pre_get_posts', [ $this, 'setCustomSortableQuery' ] );
	}

	public function setColumNames( $columns ) {
		if ( ! empty( $this->args['unset_columns'] ) ) {
			foreach ( $this->args['unset_columns'] as $unset_column ) {
				unset( $columns[ $unset_column ] );
			}
		}

		if ( ! empty( $this->args['columns'] ) ) {

			foreach ( $this->args['columns'] as $column_id => $column ) {

				$next_column = '';

				if ( ! empty( $column['before'] ) && ! empty( $columns[ $column['before'] ] ) ) {
					$next_column = $columns[ $column['before'] ];
					unset( $columns[ $column['before'] ] );
				}

				$columns[ $column_id ] = $column['title'];

				if ( ! empty( $next_column ) ) {
					$columns[ $column['before'] ] = $next_column;
				}

			}
		}

		return $columns;
	}

	public function setColumnValue( $column, $post_id ) {
		if ( ! empty( $this->args['columns'] ) ) {
			foreach ( $this->args['columns'] as $column_id => $column_item ) {
				if ( $column_id == $column ) {
					if ( is_callable( $column_item['value'] ) ) {
						echo call_user_func( $column_item['value'], $post_id );
					} else {
						echo get_post_meta( $post_id, $column_item['value'], true );
					}
					break;
				}
			}
		}
	}

	public function setSortableColumns( $sortable_columns ) {
		if ( ! empty( $this->args['columns'] ) ) {
			foreach ( $this->args['columns'] as $column_id => $column ) {
				if ( $column['sort_by'] ) {
					$sortable_columns[ $column_id ] = $column['sort_by'];
				}
			}
		}

		if ( ! empty( $this->args['group'] ) ) {
			foreach ( $this->args['group'] as $group ) {
				if ( ! empty( $group['sortable'] ) ) {
					$sortable_columns["taxonomy-{$group['id']}"] = $group['id'];
				}
			}
		}

		return $sortable_columns;
	}

	public function setCustomSortableQuery( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->get( 'post_type' ) == $this->post_type_id ) {

			$orderby = $query->get( 'orderby' );

			if ( ! empty( $this->args['columns'][ $orderby ]['sort_type'] ) ) {
				$sort_type = $this->args['columns'][ $orderby ]['sort_type'];
				$query->set( 'meta_key', $orderby );
				$query->set( 'orderby', $sort_type !== 'number' ? 'meta_value' : 'meta_value_num' );
			}

		}
	}

}
