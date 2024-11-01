<?php

namespace WooCommerceTicketManager\Lib;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract class for data.
 *
 * @since 1.0.0
 * @version 1.0.5
 */
abstract class Data {
	/**
	 * ID for this object.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Post type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $post_type = 'post';

	/**
	 * All data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.0.0
	 * @var array All data.
	 */
	protected $data = array();

	/**
	 * Model changes.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $changes = array();

	/**
	 * Set to data on construct, so we can track and reset data if needed.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $default_data = array();

	/**
	 * This is false until the object is read from the DB.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $object_read = false;

	/**
	 * Post data to property map.
	 *
	 * Post data key => property key.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $postdata_map = array();

	/**
	 * Constructor.
	 *
	 * @param int|object|array $data Object ID, post object, or array of data.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $data = 0 ) {
		$this->data         = array_merge( array_fill_keys( array_keys( $this->postdata_map ), null ), $this->data );
		$this->default_data = $this->data;
		if ( ! empty( $data ) ) {
			$this->populate_data( $data );
		}
	}

	/**
	 * Magic method to get properties.
	 *
	 * @param string $key Property key.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function __get( $key ) {
		$key    = strtolower( $key );
		$getter = 'get_' . $key;
		if ( method_exists( $this, $getter ) ) {
			return $this->$getter();
		} else {
			return $this->get_prop( $key );
		}
	}

	/**
	 * Magic method to set properties.
	 *
	 * @param string $key Property key.
	 * @param mixed  $value Property value.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __set( $key, $value ) {
		$key    = strtolower( $key );
		$setter = 'set_' . $key;
		if ( method_exists( $this, $setter ) ) {
			$this->$setter( $value );
		} else {
			$this->set_prop( $key, $value );
		}
	}

	/**
	 * Magic method to check if property is set.
	 *
	 * @param string $key Property key.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function __isset( $key ) {
		return $this->__get( $key ) !== null;
	}

	/**
	 * Handle static method calls.
	 *
	 * @param string $method Method name.
	 * @param array  $args Method arguments.
	 *
	 * @throws \BadMethodCallException When method does not exist.
	 * @since 1.0.0
	 * @return mixed
	 */
	public static function __callStatic( $method, $args ) {
		// check if method is exists without initiating the class.
		if ( method_exists( get_called_class(), $method ) ) {
			// initiate the class and call the method.
			return call_user_func_array( array( new static(), $method ), $args );
		}

		throw new \BadMethodCallException( sprintf( 'Method %s::%s does not exist.', esc_html( get_called_class() ), esc_html( $method ) ) );
	}

	/**
	 * Get property.
	 *
	 * @param string $prop Property key.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	protected function get_prop( $prop ) {
		$value = null;
		if ( array_key_exists( $prop, $this->data ) ) {
			$value = isset( $this->changes[ $prop ] ) ? $this->changes[ $prop ] : $this->data[ $prop ];
		}

		return $value;
	}

	/**
	 * Sets a prop for a setter method.
	 *
	 * Sets the value to the changes array, and if the model is read, also sets the value to the data array.
	 *
	 * @param string $prop Name of prop to set.
	 * @param mixed  $value Value to set.
	 *
	 * @since 1.0.0
	 */
	protected function set_prop( $prop, $value ) {
		if ( array_key_exists( $prop, $this->data ) ) {
			if ( is_numeric( $value ) ) {
				if ( str_contains( $value, '.' ) ) {
					$value = (float) $value;
				} else {
					$value = (int) $value;
				}
			}

			if ( true === $this->object_read ) {
				if ( $value !== $this->data[ $prop ] || array_key_exists( $prop, $this->changes ) ) {
					$this->changes[ $prop ] = $value;
				}
			} else {
				$this->data[ $prop ] = $value;
			}
		}
	}

	/**
	 * Get data.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_data() {
		$data = array( 'id' => $this->get_id() );
		foreach ( $this->data as $key => $value ) {
			$getter = 'get_' . $key;
			if ( method_exists( $this, $getter ) ) {
				$data[ $key ] = $this->$getter();
			} else {
				$data[ $key ] = $this->get_prop( $key );
			}
		}

		return $data;
	}

	/**
	 * Set the model's data.
	 *
	 * @param array|object $props Array or object of properties to set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_data( $props ) {
		if ( is_object( $props ) ) {
			$props = get_object_vars( $props );
		}
		if ( ! is_array( $props ) ) {
			return;
		}

		foreach ( $props as $prop => $value ) {
			$prop = preg_replace( '/^[^a-zA-Z]+/', '', strtolower( $prop ) );
			if ( is_callable( array( $this, "set_$prop" ) ) ) {
				$this->{"set_$prop"}( $value );
			} else {
				$this->set_prop( $prop, $value );
			}
		}
	}

	/**
	 * Populate data.
	 *
	 * @param int|\WP_Post $data Post ID or object.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function populate_data( $data ) {
		$this->set_defaults();

		if ( is_numeric( $data ) && $data > 0 && get_post_type( $data ) === $this->post_type ) {
			$post_id = absint( $data );
		} elseif ( $data instanceof \WP_Post && $this->post_type === $data->post_type ) {
			$post_id = absint( $data->ID );
		} elseif ( is_array( $data ) && ! empty( $data['ID'] ) && $this->post_type === $data['post_type'] ) {
			$post_id = absint( $data['ID'] );
		} elseif ( is_object( $data ) && ! empty( $data->ID ) && $this->post_type === $data->post_type ) {
			$post_id = absint( $data->ID );
		} else {
			$post_id = 0;
		}

		if ( empty( $post_id ) ) {
			return array();
		}

		$post_data = array();
		foreach ( array_keys( $this->data ) as $prop ) {
			$prop  = preg_replace( '/^[^a-zA-Z]+/', '', $prop );
			$field = isset( $this->postdata_map[ $prop ] ) ? $this->postdata_map[ $prop ] : $prop;
			if ( in_array( $field, $this->get_post_fields(), true ) ) {
				$post_data[ $prop ] = get_post_field( $field, $post_id );
			} else {
				$post_data[ $prop ] = get_post_meta( $post_id, "_$field", true );
			}
		}
		$post_data = array_map( 'maybe_unserialize', $post_data );
		$this->set_data( $post_data );
		$this->set_id( $post_id );
		$this->set_object_read( true );

		return $post_data;
	}

	/**
	 * Save data.
	 *
	 * @since 1.0.0
	 * @return $this|\WP_Error Post object (or WP_Error on failure).
	 */
	public function save() {
		$post_id  = $this->get_id();
		$data     = $this->exists() ? $this->changes : $this->data;
		$postdata = array();
		$metadata = array();
		foreach ( $data as $prop => $value ) {
			$prop  = preg_replace( '/^[^a-zA-Z]+/', '', $prop );
			$field = isset( $this->postdata_map[ $prop ] ) ? $this->postdata_map[ $prop ] : $prop;
			if ( in_array( $field, $this->get_post_fields(), true ) ) {
				$postdata[ $field ] = $value;
			} else {
				$metadata[ $prop ] = $value;
			}
		}

		// both empty return early.
		if ( empty( $postdata ) && empty( $metadata ) ) {
			return $this;
		}

		$postdata = array_map( 'maybe_serialize', $postdata );
		if ( ! empty( $postdata ) ) {
			$postdata['ID']        = $this->get_id();
			$postdata['post_type'] = $this->post_type;
			if ( empty( $postdata['ID'] ) ) {
				$post_id = wp_insert_post( $postdata, true );
			} else {
				$post_id = wp_update_post( $postdata, true );
			}
			if ( is_wp_error( $post_id ) ) {
				return $post_id;
			}
			// Set the ID.
			$this->set_id( $post_id );
			$this->set_object_read( true );
		}

		if ( ! empty( $metadata ) && $post_id ) {
			foreach ( $metadata as $key => $value ) {
				if ( ! is_null( $value ) ) {
					update_post_meta( $post_id, "_$key", $value );
				} else {
					delete_post_meta( $post_id, "_$key" );
				}
			}
		}

		$this->apply_changes();

		return $this;
	}

	/**
	 * Delete data.
	 *
	 * @param bool $force_delete Whether to bypass trash and force deletion. Default true.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function delete( $force_delete = true ) {
		if ( ! $this->exists() ) {
			return false;
		}

		$result = wp_delete_post( $this->get_id(), $force_delete );
		if ( ! $result ) {
			return false;
		}

		$this->set_defaults();
		$this->set_id( 0 );

		return true;
	}

	/**
	 * Retrieve the object instance.
	 *
	 * @param int|array|static $data Object ID or array of arguments.
	 *
	 * @since 1.0.0
	 * @return static|false Object instance on success, false on failure.
	 */
	protected function get( $data ) {
		if ( empty( $data ) ) {
			return false;
		}

		if ( is_array( $data ) ) {
			$data = $this->query( $data );
			if ( empty( $data ) ) {
				return false;
			}
			$data = reset( $data );
		}
		$record = new static( $data );
		if ( $record->exists() ) {
			return $record;
		}

		return false;
	}

	/**
	 * Query posts.
	 *
	 * @param array $args Query args.
	 * @param bool  $count Whether to return a count or posts.
	 *
	 * @since 1.0.0
	 * @return int|array|static[]
	 */
	protected function query( $args = array(), $count = false ) {
		$defaults = array(
			'post_status'    => 'any',
			'post_type'      => $this->post_type,
			'posts_per_page' => 20,
			'fields'         => 'all',
			'output'         => get_called_class(),
		);
		$args     = wp_parse_args( $args, $defaults );
		$output   = $args['output'];
		unset( $args['output'] );
		foreach ( $args as $key => $value ) {
			$key   = preg_replace( '/^[^a-zA-Z]+/', '', $key );
			$field = isset( $this->postdata_map[ $key ] ) ? $this->postdata_map[ $key ] : $key;
			if ( in_array( $value, array( '', array(), null ), true ) || 'meta_query' === $key ) {
				continue;
			}

			// For meta props, convert to meta query.
			if ( ! in_array( $field, $this->get_post_fields(), true ) && in_array( $field, array_keys( $this->data ), true ) ) {
				$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();
				if ( '*' === $value ) {
					$meta_query[] = array(
						array(
							'key'     => "_$field",
							'compare' => 'EXISTS',
						),
						array(
							'key'     => '_' . $key,
							'value'   => '',
							'compare' => '!=',
						),
					);
				} else {
					$meta_query[] = array(
						'key'     => "_$field",
						'value'   => $value,
						'compare' => is_array( $value ) ? 'IN' : '=',
					);
				}

				$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				unset( $args[ $key ] );
			} else {
				$key_mapping = array(
					'parent'         => 'post_parent',
					'parent_exclude' => 'post_parent__not_in',
					'exclude'        => 'post__not_in',
					'limit'          => 'posts_per_page',
					'type'           => 'post_type',
					'return'         => 'fields',
				);
				if ( isset( $key_mapping[ $key ] ) ) {
					$args[ $key_mapping[ $key ] ] = $value;
					unset( $args[ $key ] );
				}
				// Consider adding more mappings here.
				if ( isset( $this->postdata_map[ $key ] ) ) {
					$args[ $this->postdata_map[ $key ] ] = $value;
					unset( $args[ $key ] );
				}
			}

			if ( 'orderby' === $key ) {
				if ( isset( $this->postdata_map[ $value ] ) ) {
					$args['orderby'] = $this->postdata_map[ $value ];
				} elseif ( in_array( $value, $this->get_post_fields(), true ) ) {
					$args['orderby'] = $value;
				} elseif ( in_array( $value, array_keys( $this->data ), true ) ) {
					$args['meta_key'] = "_$value"; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					$args['orderby']  = 'meta_value'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				}
				$args['order'] = isset( $args['order'] ) ? $args['order'] : 'ASC';
			}
		}

		$query = new \WP_Query( $args );
		if ( $count ) {
			return $query->found_posts;
		}
		wp_reset_postdata();
		$items = array();
		foreach ( $query->posts as $post ) {
			$items[] = new static( $post );
		}

		if ( ARRAY_A === $output ) {
			$items = wp_list_pluck( $items, 'data' );
		} elseif ( ARRAY_N === $output ) {
			$items = wp_list_pluck( $items, 'data' );
			$items = array_map( 'array_values', $items );
		} elseif ( OBJECT === $output ) {
			$items = wp_list_pluck( $items, 'data' );
			foreach ( $items as $key => $data ) {
				$items[ $key ] = (object) $data;
			}
		}

		if ( 'ids' === $args['fields'] ) {
			$items = wp_list_pluck( $items, 'id' );
			$items = array_map(
				function ( $id ) {
					return is_numeric( $id ) ? (int) $id : $id;
				},
				$items
			);
		}

		return $items;
	}

	/**
	 * Insert or update an object in the database.
	 *
	 * @param array|object $data Model to insert or update.
	 * @param boolean      $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @return static|false|\WP_Error Object instance (success), false (failure), or WP_Error.
	 */
	protected function insert( $data, $wp_error = true ) {
		if ( is_object( $data ) ) {
			$data = get_object_vars( $data );
		}

		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}
		$id     = isset( $data['id'] ) ? $data['id'] : 0;
		$object = new static( $id );
		$object->set_data( $data );
		$result = $object->save();
		if ( is_wp_error( $result ) ) {
			if ( $wp_error ) {
				return $result;
			} else {
				return false;
			}
		}

		return $object->exists() ? $object : false;
	}

	/*
	|--------------------------------------------------------------------------
	| Getters and Setters.
	|--------------------------------------------------------------------------
	| Getters and setters for the data properties.
	*/
	/**
	 * Get id.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set id.
	 *
	 * @param int $id The id.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_id( $id ) {
		$this->id = absint( $id );
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers
	|--------------------------------------------------------------------------
	| Methods which do not modify class properties but are used by the class.
	*/
	/**
	 * Get post fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_post_fields() {
		return array( 'ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count' );
	}

	/**
	 * Set object read property.
	 *
	 * @param boolean $read Should read?.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_object_read( $read = true ) {
		$this->object_read = (bool) $read;
	}

	/**
	 * Merge changes with data and clear.
	 *
	 * @since 1.0.0
	 */
	protected function apply_changes() {
		$this->data    = array_replace_recursive( $this->data, $this->changes );
		$this->changes = array();
	}

	/**
	 * Reset data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function set_defaults() {
		$this->data        = $this->default_data;
		$this->changes     = array();
		$this->object_read = false;
	}

	/**
	 * If exists.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function exists() {
		return $this->get_id() > 0;
	}
}
