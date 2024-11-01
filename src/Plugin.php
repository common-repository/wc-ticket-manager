<?php

namespace WooCommerceTicketManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin.
 *
 * @since 1.0.0
 * @package WooCommerceTicketManager
 */
class Plugin extends Lib\Plugin {

	/**
	 * Plugin constructor.
	 *
	 * @param array $data The plugin data.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $data ) {
		parent::__construct( $data );
		$this->constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function constants() {
		define( 'WC_TICKET_MANAGER_VERSION', $this->get_version() );
		define( 'WC_TICKET_MANAGER_FILE', $this->get_file() );
		define( 'WC_TICKET_MANAGER_BASENAME', $this->get_basename() );
		define( 'WC_TICKET_MANAGER_PATH', $this->get_dir_path() );
		define( 'WC_TICKET_MANAGER_URL', $this->get_dir_url() );
	}

	/**
	 * Include required files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function includes() {
		require_once __DIR__ . '/Functions.php';
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		register_activation_hook( $this->get_file(), array( Installer::class, 'install' ) );
		add_action( 'admin_notices', array( $this, 'output_admin_notices' ) );
		add_action( 'init', array( $this, 'init' ), 0 );
	}

	/**
	 * Output admin notices.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_admin_notices() {
		$notices = array();

		$discount_percentage = esc_html__( '30%', 'wc-ticket-manager' );
		$notices[]           = array(
			'type'        => 'info',
			'classes'     => 'wctm-halloween',
			'dismissible' => true,
			'id'          => 'wctm_halloween_promotion',
			'message'     => sprintf(
			/* translators: %1$s: link to the plugin page, %2$s: Offer content, %3$s: link to the plugin page, %4$s: end link to the plugin page */
				__( '%1$s%2$s%3$s Claim your discount! %4$s', 'wc-ticket-manager' ),
				'<div class="wctm-halloween__header"><div class="wctm-halloween__icon"><img src="' . wc_ticket_manager()->get_dir_url( 'assets/dist/images/halloween-icon.svg' ) . '" alt="WC Ticket Manager Halloween offer"></div><div class="wctm-halloween__content"><strong class="wctm-halloween__title">',
				'👻 Halloween Sale: ' . $discount_percentage . ' OFF on All Plugins</strong><p>Get ' . $discount_percentage . ' OFF on all premium plugins with code <strong>‘BIGTREAT30’</strong>. Hurry, this deal won’t last long!</p>',
				'<a class="button button-primary" href="' . esc_url( 'https://pluginever.com/plugins/?utm_source=plugin&utm_medium=notice&utm_campaign=halloween-2024&discount=bigtreat30' ) . '" target="_blank">',
				'</a></div></div>',
			),
		);

		foreach ( $notices as $notice ) {
			$notice = wp_parse_args(
				$notice,
				array(
					'id'          => wp_generate_password( 12, false ),
					'type'        => 'info',
					'classes'     => '',
					'message'     => '',
					'dismissible' => false,
				)
			);

			$notice_classes = array( 'notice', 'notice-' . $notice['type'] );
			if ( $notice['dismissible'] ) {
				$notice_classes[] = 'is-dismissible';
			}
			if ( $notice['classes'] ) {
				$notice_classes[] = $notice['classes'];
			}
			?>
			<div class="notice wctm-notice <?php echo esc_attr( implode( ' ', $notice_classes ) ); ?>" data-notice-id="<?php echo esc_attr( $notice['id'] ); ?>">
				<p><?php echo wp_kses_post( $notice['message'] ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Init the plugin after plugins_loaded so environment variables are set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		$this->services['installer']  = new Installer();
		$this->services['actions']    = new Actions();
		$this->services['cart']       = new Cart();
		$this->services['orders']     = new Orders();
		$this->services['post_types'] = new PostTypes();
		$this->services['frontend']   = new Frontend\Frontend();

		if ( self::is_request( 'admin' ) ) {
			$this->services['admin'] = new Admin\Admin();
		}

		// Init action.
		do_action( 'wc_ticket_manager_init' );
	}
}
