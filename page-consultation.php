<?php
/**
 * Template Name: Consultation
 */

get_header();

$page_data = get_field('consult-page');
?>
<main id="main">
	<div class="page__wrap block-visible">
		<div class="side__wrap">
			<svg class="bg-svg">
				<use xlink:href="#bg-svg"></use>
			</svg>

			<?php
			if (!empty($page_data['photo-big'])) {
				echo wp_get_attachment_image($page_data['photo-big'], 'photo-small', false, ['alt' => '', 'class' => 'side__photo-big']);
			}

			if (!empty($page_data['photo-small'])) {
				echo wp_get_attachment_image($page_data['photo-small'], 'photo-small', false, ['alt' => '', 'class' => 'side__photo-small']);
			}
			?>

			<div class="side__slogan">
				<?php echo $page_data['slogan'] ?>
			</div>
		</div>

		<div class="form-wrap">
			<div class="form-wrap__title">
				<?php echo $page_data['form-title'] ?>
			</div>

			<form class="custom-form" id="lead-form" novalidate="novalidate" data-status="">
				<label>
					<?php _e("Ваше ім'я", THEME_SLUG); ?>
					<span class="form-control-wrap" data-name="user-name">
						<input class="form-control text-input required" aria-required="true"
							placeholder="<?php _e("Вкажіть Ваше ім'я", 'lead-form') ?>" value="" type="text"
							name="user-name">
					</span>
				</label>

				<label>
					<?php _e('Ваш e-mail', THEME_SLUG); ?>
					<span class="form-control-wrap" data-name="user-email">
						<input class="form-control email-input"
							placeholder="<?php _e('email@gmail.com', 'lead-form') ?>" value="" type="email"
							name="user-email">
					</span>
				</label>

				<label>
					<?php _e('Ваш телефон', THEME_SLUG); ?>
					<span class="form-control-wrap" data-name="user-phone">
						<input class="form-control tel-input required" aria-required="true"
							placeholder="<?php _e('Enter phone number', 'lead-form') ?>" value="" type="tel"
							name="user-phone" id="phone-field">
					</span>
				</label>

				<label style="min-height: 150px;">
					<span class="form-control-wrap" data-name="user-message">
						<textarea class="form-control textarea-input" name="user-message"
							placeholder="<?php _e('email@gmail.com', 'lead-form') ?>"></textarea>
					</span>
				</label>

				<input type="hidden" name="utm_source" value="" id="utm_source">
				<input type="hidden" name="utm_medium" value="" id="utm_medium">
				<input type="hidden" name="utm_campaign" value="" id="utm_campaign">
				<input type="hidden" name="utm_term" value="" id="utm_term">
				<input type="hidden" name="utm_content" value="" id="utm_content">

				<!-- <input class="form-control submit-btn has-spinner" type="submit"
					value="<?php // _e('Надіслати', 'lead-form') ?>"> -->

				<button class="form-control submit-btn" type="submit">
					<div class="spinner"></div>

					<div class="dot"></div>
					
					<?php _e('Надіслати', 'lead-form') ?>
				</button>
			</form>

			<div class="form-wrap__disclamer">
				<?php echo $page_data['disclamer-text'] . ' <a class="base-link" href="' . $page_data['disclamer-url'] . '">' . $page_data['disclamer-link-text'] . '</a>' ?>
			</div>
		</div>
	</div>

	<div class="popup__wrap block-hidden">
		<div class="popup__close-wrap">
			<div class="popup__close-text">
				<?php echo $page_data["success-close"] ?>
			</div>

			<svg class="popup__close-ico">
				<use xlink:href="#close-ico"></use>
			</svg>
		</div>

		<div class="popup__ico">
			<?php if (!empty($page_data["success-icon"])) { ?>
				<input type="image" src="<?php echo $page_data["success-icon"] ?>" alt="ico" class="popup__success-ico">
			<?php } ?>
		</div>

		<div class="popup__status">
			<?php echo $page_data["success-status-text"] ?>
		</div>

		<div class="popup__thanks">
			<?php echo $page_data["success-thanks"] ?>
		</div>
	</div>
</main>

<?php
get_footer();