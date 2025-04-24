<?php
/**
 * Plugin Name: Google Reviews by Ilias
 * Description: Shows a badge with the review rating and a sidebar with 5 relevant reviews.
 * Version: 1.2
 * Author: Ilias Khatsiyev
 * Text Domain: google-reviews-widget
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;
add_action( 'plugins_loaded', 'ilias_load_plugin_textdomain' );

function ilias_load_plugin_textdomain() {
	load_plugin_textdomain( 'google-reviews-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_shortcode( 'google_reviews_widget', 'ilias_google_reviews_widget' );

add_action( 'wp_enqueue_scripts', 'ilias_enqueue_google_reviews_assets' );
require_once plugin_dir_path( __FILE__ ) . 'admin-page.php';

add_action( 'plugins_loaded', 'ilias_load_textdomain' );
function ilias_load_textdomain() {
	load_plugin_textdomain(
		'ilias-google-reviews',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

function ilias_enqueue_google_reviews_assets() {
	wp_enqueue_style(
		'google-reviews-widget-style',
		plugins_url( 'assets/css/style.css', __FILE__ ),
		[],
		'1.1'
	);

	wp_enqueue_script(
		'google-reviews-widget-script',
		plugins_url( 'assets/js/main.js', __FILE__ ),
		[],
		'1.1',
		true
	);
}

function ilias_google_reviews_widget() {
	$api_key = get_option( 'ilias_google_reviews_api_key' );
	$place_id = get_option( 'ilias_google_reviews_place_id' );
	$business_name = get_option( 'ilias_google_reviews_business_name' );
	$google_link = get_option( 'ilias_google_reviews_google_link' );
	$badge_position = get_option( 'ilias_google_reviews_badge_position', 'left' );

	$rating = null;
	$total_reviews = null;
	$reviews = [];
	$error_message = null;

	$cache_key = 'ilias_google_reviews_data';
	$cached_data = get_transient( $cache_key );

	if ( false === $cached_data ) {
		$response = wp_remote_get( "https://maps.googleapis.com/maps/api/place/details/json?place_id=$place_id&fields=reviews,rating,user_ratings_total&language=it&key=$api_key" );

		if ( is_wp_error( $response ) ) {
			$error_message = 'Error retrieving the reviews.';
		} else {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			// only cache the data if the API call was successful and contains the expected fields
			if ( isset( $body['result']['rating'] ) && isset( $body['result']['user_ratings_total'] ) ) {
				set_transient( $cache_key, $body, DAY_IN_SECONDS );
				$cached_data = $body;
			} else {
				$error_message = 'Unvalid information from the Google Places API.';
			}
		}
	}
	if ( $error_message ) {
		// If there was an error, we can return early
		return '';
	}
	if ( $cached_data ) {
		$rating = $cached_data['result']['rating'] ?? null;
		$total_reviews = $cached_data['result']['user_ratings_total'] ?? null;
		$reviews = $cached_data['result']['reviews'] ?? [];
	}

	ob_start();
	$percentage = $rating ? ( $rating / 5 ) * 100 : 0;
	?>

	<!-- Badge -->
	<div class="google-reviews-badge google-reviews-badge--<?php echo esc_attr( $badge_position ); ?>"
		id="google-reviews-badge">
		<div class="google-reviews-badge__logo">
			<img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/img/google-logo.png'; ?>" alt="Google" width="24"
				height="24" />
		</div>
		<div class="wrapper-badge">


			<div class="google-reviews-badge__rating">
				<div class="star-rating" style="--star-fill: <?php echo $percentage; ?>%;"></div>
			</div>
			<div class="wrapper-badge-bottom">
				<span class="google-reviews-badge__score">
					<?php echo esc_html( $rating ); ?>
				</span>
				<?php if ( $total_reviews !== 1 ) : ?>
					<button class="google-reviews-badge__count">
						<?php printf( __( 'Read our reviews', 'google-reviews-widget' ) ); ?>
					</button>
				<?php else : ?>
					<button class="google-reviews-badge__count">
						<?php printf( __( 'Read our review', 'google-reviews-widget' ) ); ?>
					</button>
				<?php endif; ?>
			</div>

		</div>



	</div>

	<!-- Sidebar -->
	<div class="google-reviews-sidebar google-reviews-sidebar--<?php echo esc_attr( $badge_position ); ?>"
		id="google-reviews-sidebar">
		<div class="google-reviews-sidebar__header">
			<div class="google-reviews-sidebar__branding">
				<img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/img/google-logo.png'; ?>" alt="Google" width="24"
					height="24">
				<strong class="google-reviews-sidebar__title"><?php echo esc_html( $business_name ); ?></strong>
				<button class="google-reviews-sidebar__close">×</button>
			</div>
			<div class="google-reviews-sidebar__summary">
				<p class="google-reviews-sidebar__score">
					<?php printf( __( '%s on 5', 'google-reviews-widget' ), esc_html( $rating ) ); ?>
					<?php printf( __( 'based on %s reviews', 'google-reviews-widget' ), esc_html( $total_reviews ) ); ?>
				</p>
				<div class="star-rating" style="--star-fill: <?php echo $percentage; ?>%;"></div>
			</div>
			<div class="google-reviews-sidebar__link">
				<a href="<?php echo esc_html( $google_link ) ?>" target="_blank"
					rel="noopener noreferrer"><?php printf( __( 'Read all our ratings', 'google-reviews-widget' ) ) ?></a>
			</div>
		</div>

		<div class="google-reviews-sidebar__list">
			<?php if ( $error_message ) : ?>
				<p class="google-reviews-sidebar__error"><?php echo esc_html( $error_message ); ?></p>
			<?php elseif ( ! empty( $reviews ) ) : ?>
				<?php foreach ( $reviews as $index => $review ) :
					$rating_value = floatval( $review['rating'] );
					$percentage = ( $rating_value / 5 ) * 100;
					$review_time = date( 'd/m/Y', $review['time'] );
					$review_text = esc_html( $review['text'] );
					$short_text = mb_substr( $review_text, 0, 100 );
					$delay = $index * 0.5;
					?>

					<div class="google-review">
						<div class="google-review__header">
							<img class="google-review__avatar" src="<?php echo esc_url( $review['profile_photo_url'] ); ?>"
								alt="Profile Photo" width="50" height="50">
							<div class="google-review__user">
								<h3 class="google-review__name"><?php echo esc_html( $review['author_name'] ); ?></h3>
								<time class="google-review__date"><?php echo esc_html( $review_time ); ?></time>
							</div>
						</div>
						<div class="google-review__rating star-rating" style="--star-fill: <?php echo $percentage; ?>%;"></div>
						<div class="google-review__body">
							<?php if ( mb_strlen( $review_text ) > 100 ) : ?>
								<p class="google-review__text">
									<span class="short-text"><?php echo $short_text; ?>...</span>
									<span class="full-text" style="display:none;"> <?php echo $review_text; ?></span>
									<a href="#"
										class="read-more"><strong><?php _e( 'Read more', 'google-reviews-widget' ); ?></strong></a>
								</p>
							<?php else : ?>
								<p class="google-review__text"><?php echo $review_text; ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="google-reviews-sidebar__no-reviews">
					<?php _e( 'No reviews available.', 'google-reviews-widget' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<?php
	return ob_get_clean();
}
