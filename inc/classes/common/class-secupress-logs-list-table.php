<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );


/**
 * List Table API: SecuPress_Logs_List_Table class
 *
 * @package SecuPress
 * @since 1.0
 */

/**
 * Core class used to implement displaying Logs in a list table.
 *
 * @since 1.0
 *
 * @see WP_List_Table
 */
class SecuPress_Logs_List_Table extends WP_List_Table {

	const VERSION = '1.0';
	/**
	 * @var (object) Current Log.
	 */
	protected $log = false;
	/**
	 * @var (string) Logs class name.
	 */
	protected $logs_classname;
	/**
	 * @var (string) Log class name.
	 */
	protected $log_classname;
	/**
	 * @var (array) All available Log types.
	 */
	protected $log_types;
	/**
	 * @var (string) Current Log type.
	 */
	protected $log_type;
	/**
	 * @var (string) Default Log type.
	 */
	protected $default_log_type;


	// Instance ====================================================================================

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 *
	 * @param (array) $args An associative array of arguments.
	 */
	public function __construct( $args = array() ) {
		parent::__construct( array(
			'plural' => $args['screen']->post_type,
			'screen' => $args['screen'],
		) );
	}


	/**
	 * Get the current Log.
	 *
	 * @since 1.0
	 *
	 * return (object)
	 */
	public function get_log() {
		return $this->log;
	}


	/**
	 * Prepare all the things.
	 *
	 * @since 1.0
	 */
	public function prepare_items() {
		global $avail_post_stati, $wp_query, $per_page, $mode;

		// Set the infos we need.
		$post_type              = $this->screen->post_type;
		$this->log_types        = SecuPress_Logs::_get_log_types();
		$this->default_log_type = key( $this->log_types );

		// Find the name of the class that handle this type of logs.
		foreach ( $this->log_types as $log_type => $atts ) {
			if ( $atts['post_type'] === $post_type ) {
				$this->log_type       = $log_type;
				$this->logs_classname = $atts['classname'];
				break;
			}
		}

		if ( empty( $this->logs_classname ) ) {
			return;
		}

		// Get the name of the class that handle this type of log.
		$logs_classname      = $this->logs_classname;
		$this->log_classname = $logs_classname::_maybe_include_log_class();

		// Set some globals.
		$mode = 'list';

		$per_page = $this->get_items_per_page( 'edit_' . $post_type . '_per_page' );

		/** This filter is documented in wp-admin/includes/post.php */
 		$per_page = apply_filters( 'edit_posts_per_page', $per_page, $post_type );

		$avail_post_stati = get_available_post_statuses( $post_type );

		// Get posts.
		$this->_query();

		if ( $wp_query->found_posts || $this->get_pagenum() === 1 ) {
			$total_items = $wp_query->found_posts;
		} else {
			$post_counts = (array) wp_count_posts( $post_type );

			if ( ! empty( $_REQUEST['critic'] ) && in_array( $_REQUEST['critic'], $avail_post_stati ) ) {
				$total_items = $post_counts[ $_REQUEST['critic'] ];
			} else {
				$total_items = array_sum( $post_counts );
			}
		}

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );
	}


	/**
	 * Query the Posts.
	 *
	 * @since 1.0
	 */
	protected function _query() {
		global $avail_post_stati;

		// Prepare the query args.
		$args = array( 'post_type' => $this->screen->post_type, );
		/**
		 * Filter the default query args used to display the logs.
		 *
		 * @since 1.0
		 *
		 * @param (array) $args An array containing at least the post type.
		 */
		$args = apply_filters( '_secupress.logs.logs_query_args', $args );

		// Criticity - Post Status.
		if ( ! empty( $_GET['critic'] ) && in_array( $_GET['critic'], $avail_post_stati ) ) {
			$args['post_status'] = $_GET['critic'];
		}

		// Order by.
		if ( ! empty( $_GET['orderby'] ) ) {
			switch ( $_GET['orderby'] ) {
				case 'date' :
					$args['orderby'] = 'date menu_order';
					break;
				case 'critic' :
					$args['orderby'] = 'post_status';
					break;
				default :
					$args['orderby'] = $_GET['orderby'];
			}
		}

		// Order
		$args['order'] = ! empty( $args['order'] ) ? $args['order'] : 'ASC';
		$args['order'] = ! empty( $_GET['order'] ) ? $_GET['order'] : $args['order'];

		// Posts per page.
		$args['posts_per_page'] = (int) get_user_option( 'edit_' . $args['post_type'] . '_per_page' );

		if ( empty( $posts_per_page ) || $args['posts_per_page'] < 1 ) {
			$args['posts_per_page'] = 20;
		}

		/** This filter is documented in wp-admin/includes/post.php */
		$args['posts_per_page'] = apply_filters( 'edit_' . $args['post_type'] . '_per_page', $args['posts_per_page'] );

		/** This filter is documented in wp-admin/includes/post.php */
		$args['posts_per_page'] = apply_filters( 'edit_posts_per_page', $args['posts_per_page'], $args['post_type'] );

		// Get posts.
		wp( $args );
	}


	/**
	 * Tell if we have Posts.
	 *
	 * @since 1.0
	 *
	 * @return (bool)
	 */
	public function has_items() {
		return have_posts();
	}


	/**
	 * Display a message telling no Posts are to be found.
	 *
	 * @since 1.0
	 */
	public function no_items() {
		echo get_post_type_object( $this->screen->post_type )->labels->not_found;
	}


	/**
	 * Determine if the current view is the "All" view.
	 *
	 * @since 1.0
	 *
	 * @return (bool) Whether the current view is the "All" view.
	 */
	protected function is_base_request() {
		$vars = $_GET;
		unset( $vars['paged'] );

		if ( empty( $vars ) ) {
			return true;
		} elseif ( 1 === count( $vars ) && ! empty( $vars['post_type'] ) ) {
			return $this->screen->post_type === $vars['post_type'];
		}

		return 1 === count( $vars );
	}


	/**
	 * Helper to create links to edit.php with params.
	 *
	 * @since 1.0
	 *
	 * @param (array)  $args  URL parameters for the link.
	 * @param (string) $label Link text.
	 * @param (string) $class Optional. Class attribute. Default empty string.
	 *
	 * @return (string) The formatted link string.
	 */
	protected function get_edit_link( $args, $label, $class = '' ) {
		$url = add_query_arg( $args, $this->_page_url() );

		$class_html = '';
		if ( ! empty( $class ) ) {
			 $class_html = sprintf(
				' class="%s"',
				esc_attr( $class )
			);
		}

		return sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( $url ),
			$class_html,
			$label
		);
	}


	/**
	 * Get links allowing to filter the Posts by post status.
	 *
	 * @since 1.0
	 *
	 * @return (array)
	 */
	protected function get_views() {
		global $avail_post_stati;

		$post_type       = $this->screen->post_type;
		$status_links    = array();
		$num_posts       = wp_count_posts( $post_type );
		$total_posts     = array_sum( (array) $num_posts );
		$class           = '';
		$current_user_id = get_current_user_id();

		if ( $this->is_base_request() || isset( $_REQUEST['all_posts'] ) ) {
			$class = 'current';
		}

		$all_inner_html = sprintf(
			_nx(
				'All <span class="count">(%s)</span>',
				'All <span class="count">(%s)</span>',
				$total_posts,
				'posts'
			),
			number_format_i18n( $total_posts )
		);

		$status_links['all'] = $this->get_edit_link( array(), $all_inner_html, $class );

		foreach ( get_post_stati( array(), 'objects' ) as $status ) {
			$class       = '';
			$status_name = $status->name;

			if ( ! in_array( $status_name, $avail_post_stati ) || empty( $num_posts->$status_name ) ) {
				continue;
			}

			if ( isset( $_REQUEST['critic'] ) && $status_name === $_REQUEST['critic'] ) {
				$class = 'current';
			}

			$status_args = array(
				'critic' => $status_name,
			);

			$status_label = sprintf(
				translate_nooped_plural( $status->label_count, $num_posts->$status_name ),
				number_format_i18n( $num_posts->$status_name )
			);

			$status_links[ $status_name ] = $this->get_edit_link( $status_args, $status_label, $class );
		}

		return $status_links;
	}


	/**
	 * Get bulk actions that will be displayed in the `<select>`.
	 *
	 * @since 1.0
	 *
	 * @return (array)
	 */
	protected function get_bulk_actions() {
		return array(
			'secupress_bulk_delete-' . $this->log_type . '-logs' => __( 'Delete Permanently' ),
		);
	}


	/**
	 * Display "Delete All" and "Downlad All" buttons.
	 *
	 * @since 1.0
	 *
	 * @param (string) $which The position: "top" or "bottom".
	 */
	protected function extra_tablenav( $which ) {
		?>
		<div class="alignleft actions">
			<?php
			if ( 'top' === $which && $this->has_items() ) {
				$logs_classname = $this->logs_classname;

				// "Delete All" button.
				$href = $logs_classname::get_instance()->delete_logs_url( $this->_paged_page_url() );

				echo '<a id="delete_all" class="button apply secupress-clear-logs" href="' . esc_url( $href ) . '">' . __( 'Delete All', 'secupress' ) . '</a> <span class="spinner secupress-inline-spinner"></span>';

				// "Downlad All" button.
				$href = $logs_classname::get_instance()->download_logs_url( $this->_paged_page_url() );

				echo '<a id="download_all" class="button apply secupress-download-logs" href="' . esc_url( $href ) . '">' . __( 'Download All', 'secupress' ) . '</a> <span class="spinner secupress-inline-spinner"></span>';
			}
			?>
		</div>
		<?php
		/** This action is documented in wp-admin/includes/class-wp-posts-list-table.php */
		do_action( 'manage_posts_extra_tablenav', $which );
	}


	/**
	 * Generate the table navigation above or below the table.
	 *
	 * @since 1.0
	 *
	 * @param (string) $which The position: "top" or "bottom".
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'secupress-bulk-' . $this->log_type . '-logs', '_wpnonce', false );

			// Use a custom referer input, we don't want superfuous paramaters in the URL.
			echo '<input type="hidden" name="_wp_http_referer" value="'. esc_attr( $this->_paged_page_url() ) . '" />';

			$args = parse_url( $this->_paged_page_url(), PHP_URL_QUERY );

			if ( $args ) {
				// Display all other parameters ("page" is the most important).
				$args = explode( '&', $args );

				foreach ( $args as $arg ) {
					$arg = explode( '=', $arg );

					if ( isset( $arg[1] ) ) {
						echo '<input type="hidden" name="' . $arg[0] . '" value="' . $arg[1] . "\"/>\n";
					}
				}
			}
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( 'top' === $which && $this->has_items() ): ?>
			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
			<?php endif;
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear" />
		</div>
	<?php
	}


	/**
	 * Get the classes to use on the `<table>`.
	 *
	 * @since 1.0
	 *
	 * @return (array)
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', 'posts' );
	}


	/**
	 * Get the columns we are going to display.
	 *
	 * @since 1.0
	 *
	 * @return (array)
	 */
	public function get_columns() {
		$post_type = $this->screen->post_type;

		$posts_columns = array();

		$posts_columns['cb'] = '<input type="checkbox" />';

		/* translators: manage posts column name */
		$posts_columns['title'] = _x( 'Title', 'column name' );

		if ( count( get_available_post_statuses( $post_type ) ) > 1 ) {
			$posts_columns['critic'] = __( 'Criticity', 'secupress' );
		}

		$posts_columns['date'] = __( 'Date' );

		/** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
		$posts_columns = apply_filters( 'manage_posts_columns', $posts_columns, $post_type );

		/** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
		return apply_filters( "manage_{$post_type}_posts_columns", $posts_columns );
	}


	/**
	 * Get the columns that can be sorted.
	 *
	 * @since 1.0
	 *
	 * @return (array)
	 */
	protected function get_sortable_columns() {
		return array(
			'title'    => 'title',
			'date'     => array( 'date', true )
		);
	}


	/**
	 * Display the rows.
	 *
	 * @since 1.0
	 *
	 * @param (array) $posts An array of posts.
	 * @param (int)   $level Level of the post (level as in parent/child relation).
	 */
	public function display_rows( $posts = array(), $level = 0 ) {
		global $wp_query, $per_page;

		if ( empty( $posts ) ) {
			$posts = $wp_query->posts;
		}

		$this->_display_rows( $posts, $level );
	}


	/**
	 * Display the rows.
	 * The current Log is set here.
	 *
	 * @since 1.0
	 *
	 * @param (array) $posts An array of posts.
	 * @param (int)   $level Level of the post (level as in parent/child relation).
	 */
	private function _display_rows( $posts, $level = 0 ) {
		$log_classname = $this->log_classname;

		foreach ( $posts as $post ) {
			$this->log = new $log_classname( $post );
			$this->single_row( $post, $level );
		}

		$this->log = false;
	}


	/**
	 * Handles the checkbox column output.
	 *
	 * @since 1.0
	 * @since WP 4.3.0
	 *
	 * @param (object) $post The current WP_Post object.
	 */
	public function column_cb( $post ) {
		?>
		<label class="screen-reader-text" for="cb-select-<?php the_ID(); ?>"><?php
			printf( __( 'Select &#8220;%s&#8221;', 'secupress' ), strip_tags( $this->log->get_title() ) );
		?></label>
		<input id="cb-select-<?php the_ID(); ?>" type="checkbox" name="post[]" value="<?php the_ID(); ?>" />
		<?php
	}

	/**
	 * Handles the title column output.
	 *
	 * @since 1.0
	 * @since WP 4.3.0
	 *
	 * @param (object) $post    The current WP_Post object.
	 * @param (string) $classes The cell classes.
	 * @param (string) $data    Cell data attributes.
	 * @param (string) $primary Name of the priramy column.
	 */
	protected function _column_title( $post, $classes, $data, $primary ) {
		echo '<td class="' . $classes . ' page-title" ', $data, '>';
			echo $this->column_title( $post );
			echo $this->handle_row_actions( $post, 'title', $primary );
		echo '</td>';
	}


	/**
	 * Handles the title column content.
	 *
	 * @since 1.0
	 * @since WP 4.3.0
	 *
	 * @param (object) $post The current WP_Post object.
	 */
	public function column_title( $post ) {
		$logs_classname = $this->logs_classname;
		$view_href      = add_query_arg( 'log', $post->ID, $this->_paged_page_url() );
		$title          = $this->log->get_title();

		echo '<a class="secupress-view-log" href="' . esc_url( $view_href ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), strip_tags( $title ) ) ) . '">'; // WP i18n
			echo $title;
		echo "</a>\n";

		if ( ! secupress_wp_version_is( '4.3.0' ) ) {
			echo $this->handle_row_actions( $post, 'title', $this->get_default_primary_column_name() );
		}
	}


	/**
	 * Handles the criticity column output.
	 *
	 * @since 1.0
	 *
	 * @param (object) $post The current WP_Post object.
	 */
	public function column_critic( $post ) {
		echo $this->log->get_criticity( 'icon' ) . ' <span aria-hidden="true">' . $this->log->get_criticity() . '</span>';
	}


	/**
	 * Handles the post date column output.
	 *
	 * @since 1.0
	 *
	 * @param (object) $post The current WP_Post object.
	 */
	public function column_date( $post ) {
		/** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
		echo apply_filters( 'post_date_column_time', $this->log->get_time(), $post, 'date', 'list' );
	}


	/**
	 * Handles the default column output.
	 *
	 * @since 1.0
	 *
	 * @param (object) $post        The current WP_Post object.
	 * @param (string) $column_name The current column name.
	 */
	public function column_default( $post, $column_name ) {

		/** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
		do_action( 'manage_posts_custom_column', $column_name, $post->ID );

		/** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
		do_action( "manage_{$post->post_type}_posts_custom_column", $column_name, $post->ID );
	}


	/**
	 * Display a row.
	 *
	 * @since 1.0
	 *
	 * @param (int|object) $post  The current post ID or WP_Post object.
	 * @param (int)        $level Level of the post (level as in parent/child relation).
	 */
	public function single_row( $post, $level = 0 ) {
		$global_post = get_post();
		$post        = get_post( $post );

		$GLOBALS['post'] = $post;
		setup_postdata( $post );
		?>
		<tr id="post-<?php echo $post->ID; ?>" class="<?php echo implode( ' ', get_post_class( 'level-0', $post->ID ) ); ?>">
			<?php $this->single_row_columns( $post ); ?>
		</tr>
		<?php
		$GLOBALS['post'] = $global_post;
	}


	/**
	 * Get the name of the default primary column.
	 *
	 * @since 1.0
	 *
	 * @return (string) Name of the default primary column, in this case, 'title'.
	 */
	protected function get_default_primary_column_name() {
		return 'title';
	}


	/**
	 * Generate and display row action links.
	 *
	 * @since 1.0
	 *
	 * @param (object) $post        Current WP_Post object.
	 * @param (string) $column_name Current column name.
	 * @param (string) $primary     Primary column name.
	 *
	 * @return (string) Row actions output for posts.
	 */
	protected function handle_row_actions( $post, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}

		$logs_classname = $this->logs_classname;
		$delete_href    = $logs_classname::get_instance()->delete_log_url( $post->ID, $this->_page_url() );
		$view_href      = add_query_arg( 'log', $post->ID, $this->_paged_page_url() );

		$actions = array(
			'delete' => '<a class="secupress-delete-log submitdelete" href="' . esc_url( $delete_href ) . '" title="' . esc_attr__( 'Delete this item permanently' ) . '">' . __( 'Delete Permanently' ) . '</a> <span class="spinner secupress-inline-spinner"></span>',
			'view'   => '<a class="secupress-view-log" href="' . esc_url( $view_href ) . '" title="' . esc_attr__( 'View this log details', 'secupress' ) . '" tabindex="-1">' . __( 'View' ) . '</a>',
		);

		/** This filter is documented in wp-admin/includes/class-wp-posts-list-table.php */
		$actions = apply_filters( 'post_row_actions', $actions, $post );

		return $this->row_actions( $actions );
	}


	/**
	 * The page URL.
	 *
	 * @since 1.0
	 *
	 * @return (string)
	 */
	public function _page_url( $log_type = false ) {
		$href = secupress_admin_url( 'logs' );

		if ( ! $log_type ) {
			$log_type = $this->log_type;
		}

		if ( $this->default_log_type !== $log_type ) {
			$href = add_query_arg( 'tab', $log_type, $href );
		}

		return $href;
	}


	/**
	 * The page URL, with the page number parameter.
	 *
	 * @since 1.0
	 *
	 * @return (string)
	 */
	public function _paged_page_url() {
		$page_url = $this->_page_url();
		$pagenum  = $this->get_pagenum();

		if ( $pagenum > 1 ) {
			$page_url = add_query_arg( 'paged', $pagenum, $page_url );
		}

		return $page_url;
	}
}
