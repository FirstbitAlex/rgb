<?php

add_action('wp_ajax_save_lead_to_cpt', 'save_lead_to_cpt');
add_action('wp_ajax_nopriv_save_lead_to_cpt', 'save_lead_to_cpt');

function save_lead_to_cpt()
{
	// =======================================
	// validations
	// =======================================
	$errors = [];
	$data = [];
	$data['user-name'] = sanitize_text_field($_POST['user-name'] ?? '');
	$data['user-email'] = sanitize_email($_POST['user-email'] ?? '');
	$data['user-phone'] = sanitize_text_field($_POST['user-phone'] ?? '');
	$data['user-message'] = sanitize_textarea_field($_POST['user-message'] ?? '');

	// name validation
	if (empty($data['user-name'])) {
		$errors['user-name'] = __("Поле \"Ім'я\" обов'язкове', 'lead-form");
	} elseif (!preg_match('/^[a-zA-Zа-яА-ЯёЁіІїЇєЄґҐ]+(?: [a-zA-Zа-яА-ЯёЁіІїЇєЄґҐ]+)*$/u', $data['user-name'])) {
		$errors['user-name'] = __('Ім\'я повинно містити лише букви та один пробіл між словами', 'lead-form');
	}

	// phone validation
	if (empty($data['user-phone'])) {
		$errors['user-phone'] = __('Поле "Телефон" обов\'язкове', 'lead-form');
	} elseif (!preg_match('/^\+\d{10,15}$/', $data['user-phone'])) {
		$errors['user-phone'] = __('Неправильний формат телефону', 'lead-form');
	}

	// email validation
	if (!empty($data['user-email'])) {
		if (!is_email($data['user-email'])) {
			$errors['user-email'] = __('Неправильний формат email', 'lead-form');
		} elseif (!filter_var($data['user-email'], FILTER_VALIDATE_EMAIL)) {
			$errors['user-email'] = __('Неправильний формат email', 'lead-form');
		} elseif (!validate_email_format($data['user-email'])) {
			$errors['user-email'] = __('Неправильний формат email', 'lead-form');
		}
	}

	// if have errors - return failed message
	if (!empty($errors)) {
		wp_send_json([
			'status' => 'validation_failed',
			'errors' => $errors,
			'' => __('Будь ласка, виправте помилки у формі.', 'lead-form'),
		]);
		return;
	}

	// =======================================
	// save to CPT
	// =======================================
	$post_id = wp_insert_post([
		'post_type' => 'lead',
		'post_status' => 'publish',
		'post_title' => $data['user-name'],
		'post_content' => $data['user-message'],
		'meta_input' => [
			'lead-email' => $data['user-email'],
			'lead-phone' => $data['user-phone'],
			'lead-submission-date' => current_time('mysql'),
			'lead-utm-source' => sanitize_text_field($_POST['utm_source'] ?? ''),
			'lead-utm-medium' => sanitize_text_field($_POST['utm_medium'] ?? ''),
			'lead-utm-campaign' => sanitize_text_field($_POST['utm_campaign'] ?? ''),
			'lead-utm-term' => sanitize_text_field($_POST['utm_term'] ?? ''),
			'lead-utm-content' => sanitize_text_field($_POST['utm_content'] ?? '')
		],
	]);

	if (is_wp_error($post_id)) {
		wp_send_json([
			'status' => 'error',
			'message' => __('Помилка при збереженні даних. Спробуйте ще раз.', 'lead-form'),
		]);
		return;
	}

	// =======================================
	// save to emails
	// =======================================
	$recipients = get_field('consult-page_email-list', 10);
	$to = array_map(function ($item) {
		return $item['recipient-email'];
	}, $recipients);

	$message = '';
	$message .= "Від кого: {$data['user-name']}\n";
	$message .= "E-mail: {$data['user-email']}\n";
	$message .= "Телефон: {$data['user-phone']}\n\n";
	$message .= "Текст повідомлення: \n";
	$message .= $data['user-message'];

	$subject = __('Новий запит на консультацію', 'lead-form');
	$headers = [];

	if (!wp_mail($to, $subject, $message, $headers)) {
		wp_send_json([
			'status' => 'error',
			'message' => __('Дані збережені, але відправка листа на пошту не вдалася!', 'lead-form'),
		]);
		return;
	}

	// =======================================
	// send to Telegram
	// =======================================
	$bot_token = get_field('consult-page_telegram-bot-token', 10);
	$chat_id = get_field('consult-page_telegram-channel-id', 10);

	if (!empty($bot_token) && !empty($chat_id)) {
		$tg_mess = $subject . "\n\n" . $message;
		send_telegram_message($tg_mess, $bot_token, $chat_id);
	}

	// =======================================
	// send to Google Sheets
	// =======================================
	$script_url = get_field('consult-page_google-sheets-url', 10);
	if (!empty($script_url)) {
		$user_data = [
			'user-name' => sanitize_text_field($_POST['user-name'] ?? ''),
			'user-email' => sanitize_email($_POST['user-email'] ?? ''),
			'user-phone' => sanitize_text_field($_POST['user-phone'] ?? ''),
			'user-message' => sanitize_textarea_field($_POST['user-message'] ?? ''),
			'utm-source' => sanitize_text_field($_POST['utm_source'] ?? ''),
			'utm-medium' => sanitize_text_field($_POST['utm_medium'] ?? ''),
			'utm-campaign' => sanitize_text_field($_POST['utm_campaign'] ?? ''),
			'utm-term' => sanitize_text_field($_POST['utm_term'] ?? ''),
			'utm-content' => sanitize_text_field($_POST['utm_content'] ?? '')
		];
		send_to_google_sheets($user_data, $script_url);
	}

	// finish
	wp_send_json([
		'status' => 'success',
		'message' => __('Дякуємо! Ваше повідомлення надіслано успішно.', 'lead-form'),
	]);
}

function validate_email_format($email)
{
	// check for structure
	$pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
	if (!preg_match($pattern, $email)) {
		return false;
	}

	// check for dots
	if (strpos($email, '..') !== false) {
		return false;
	}

	// сheck dots (not in start or end of first part)
	$parts = explode('@', $email);
	if (isset($parts[0])) {
		if (strpos($parts[0], '.') === 0 || strrpos($parts[0], '.') === strlen($parts[0]) - 1) {
			return false;
		}
	}

	return true;
}

// add ajaxurl
add_action('wp_head', 'add_ajax_url_to_frontend');
function add_ajax_url_to_frontend()
{
	echo '<script>var ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
}


function send_telegram_message($message_text, $bot_token, $chat_id)
{
	$url = 'https://api.telegram.org/bot' . $bot_token . '/sendMessage';
	$args = array(
		'method' => 'GET',
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => [],
		'body' => [
			'chat_id' => $chat_id,
			'text' => $message_text,
			'parse_mode' => 'HTML',
		],
	);

	// send message
	$response = wp_remote_get($url, $args);

	// answer
	if (is_wp_error($response)) {
		$error_message = $response->get_error_message();
		error_log("Error send to Telegram: " . $error_message);
		return false;
	} else {
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if ($data['ok']) {
			return true;
		} else {
			error_log("API Telegram Error: " . $data['description']);
			return false;
		}
	}
}

function send_to_google_sheets($data, $script_url)
{
	$response = wp_remote_post(
		$script_url,
		array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => $data,
		)
	);

	if (is_wp_error($response)) {
		error_log("Error srnt to Google Sheets: " . $response->get_error_message());
		return false;
	} else {
		$body = wp_remote_retrieve_body($response);
		if ($body == "Success") {
			return true;
		} else {
			error_log("Error answer from Apps Script: " . $body);
			return false;
		}
	}
}