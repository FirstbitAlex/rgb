let iti;

// for email validation
const EMAIL_REGEX = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

document.addEventListener("DOMContentLoaded", function () {
	// set utm parameters in hidden fields of form
	var params = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
	params.forEach(function (p) {
		var el = document.getElementById(p);
		if (el) {
			var value = new URLSearchParams(window.location.search).get(p) || '';
			el.value = value;
		}
	});

	// set code for phone
	const phoneInput = document.querySelector("#phone-field");
	if (phoneInput) {
		iti = window.intlTelInput(phoneInput, {
			initialCountry: "ua",
			separateDialCode: true,
			autoInsertDialCode: true,
			utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.6/build/js/utils.js"
		});

		// set country code
		phoneInput.value = "+" + iti.getSelectedCountryData().dialCode;

		// update code by country
		phoneInput.addEventListener("countrychange", function () {
			phoneInput.value = "+" + iti.getSelectedCountryData().dialCode;
		});

		// restrict letters in phone field
		phoneInput.addEventListener('input', function () {
			this.value = this.value
				.replace(/[^\d+]/g, '') // leave only digitals and "+"
				.replace(/(?!^)\+/g, ''); // remove all "+" except first 
		});

		// set cursor after country code by tab
		phoneInput.addEventListener("focus", function () {
			const dialCode = "+" + iti.getSelectedCountryData().dialCode;
			if (this.value === dialCode) {
				this.setSelectionRange(this.value.length, this.value.length);
			}
		});
	}

	// name validation
	const nameField = document.querySelector('[name="user-name"]');
	if (nameField) {
		nameField.addEventListener('input', function () {
			this.value = this.value
				.replace(/[^a-zA-Zа-яА-ЯёЁіІїЇєЄґҐ\s]/g, '')  // only letters and spaces
				.replace(/\s{2,}/g, ' ')                       // only one space in a row
				.replace(/^\s/, '');                           // remove space at the beginning
		});
	}

	// email validation
	const emailField = document.querySelector('[name="user-email"]');
	if (emailField) {
		emailField.addEventListener('blur', function () {
			validateEmailField(this);
		});
	}
});

// email validation
function validateEmailField(emailField) {
	const email = emailField.value.trim();
	const fieldWrap = emailField.closest('.form-control-wrap');

	// remove previous error
	const existingError = fieldWrap.querySelector('.validation-error');
	if (existingError) {
		existingError.remove();
	}
	emailField.classList.remove('is-invalid');

	// email validate
	if (email && !EMAIL_REGEX.test(email)) {
		emailField.classList.add('is-invalid');
		fieldWrap.appendChild(createErrorElement('Неправильний формат email'));
		return false;
	}
	return true;
}

// create error element
function createErrorElement(message) {
	const errorElement = document.createElement('span');
	errorElement.className = 'validation-error';
	errorElement.textContent = message;
	return errorElement;
}

// front validation
function validateForm(formData) {
	const errors = {};

	// name validation
	const name = formData.get('user-name')?.trim();
	if (!name) {
		errors['user-name'] = 'Поле "Ім\'я" обов\'язкове';
	} else if (!/^[a-zA-Zа-яА-ЯёЁіІїЇєЄґҐ]+(?: [a-zA-Zа-яА-ЯёЁіІїЇєЄґҐ]+)*$/u.test(name)) {
		errors['user-name'] = 'Ім\'я повинно містити лише букви та один пробіл між словами';
	}

	// phone validation
	const phone = formData.get('user-phone')?.trim();
	if (!phone) {
		errors['user-phone'] = 'Поле "Телефон" обов\'язкове';
	} else if (!/^\+\d{10,15}$/.test(phone)) {
		errors['user-phone'] = 'Неправильний формат телефону';
	}

	// email validation
	const email = formData.get('user-email')?.trim();
	if (email && !EMAIL_REGEX.test(email)) {
		errors['user-email'] = 'Неправильний формат email';
	}

	return errors;
}

jQuery(document).ready(function ($) {
	var pageWrap = $('.page__wrap');
	var popupWrap = $('.popup__wrap');

	// form submit event
	$('#lead-form').on('submit', function (e) {
		e.preventDefault();

		var $form = $(this);
		var formData = new FormData(this);

		// front validation
		const validationErrors = validateForm(formData);

		// clear previous errors
		$form.find('.validation-error').remove();
		$form.find('.is-invalid').removeClass('is-invalid');

		// show validation errors (if have)
		if (Object.keys(validationErrors).length > 0) {
			Object.keys(validationErrors).forEach(function (fieldName) {
				var $fieldWrap = $form.find('[data-name="' + fieldName + '"]');
				var $input = $fieldWrap.find('[name="' + fieldName + '"]');

				if ($input.length) {
					$input.addClass('is-invalid').attr('aria-invalid', 'true');
					$fieldWrap.append('<span class="validation-error">' + validationErrors[fieldName] + '</span>');
				}
			});
			return;
		}

		// disabled submit button
		var $btn = $form.find('.submit-btn');
		$btn.prop('disabled', true).addClass('is-sending');

		// convert form data for AJAX
		const serializedData = new URLSearchParams(formData).toString();

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: serializedData + '&action=save_lead_to_cpt',
			dataType: 'json',
			success: function (response) {
				// enabled button
				$btn.prop('disabled', false).removeClass('is-sending');

				if (response.status === 'success') {
					// clear form
					$form[0].reset();

					// update phone
					const phoneInput = $form.find('#phone-field')[0];
					if (phoneInput && iti) {
						iti.setNumber('+' + iti.getSelectedCountryData().dialCode);
					}

					// show popup
					if (pageWrap.length && popupWrap.length) {
						pageWrap.removeClass('block-visible').addClass('block-hidden');
						popupWrap.removeClass('block-hidden').addClass('block-visible');
					}

				} else if (response.status === 'validation_failed') {

					// show errors for fields
					if (response.errors && typeof response.errors === 'object') {
						Object.keys(response.errors).forEach(function (fieldName) {
							var $fieldWrap = $form.find('[data-name="' + fieldName + '"]');
							var $input = $fieldWrap.find('[name="' + fieldName + '"]');

							if ($input.length) {
								$input.addClass('is-invalid').attr('aria-invalid', 'true');
								$fieldWrap.append('<span class="validation-error">' + response.errors[fieldName] + '</span>');
							}
						});
					}

				} else if (response.status === 'error') {
					alert(response.message);
				}
			},

			error: function (xhr, status, error) {
				$btn.prop('disabled', false).removeClass('is-sending');
				alert('Помилка з\'єднання. Перевірте підключення до інтернету і спробуйте ще раз.');
			}
		});
	});

	// Close popup
	$('.popup__close-wrap').on('click', function () {
		if (pageWrap.length && popupWrap.length) {
			pageWrap.removeClass('block-hidden').addClass('block-visible');
			popupWrap.removeClass('block-visible').addClass('block-hidden');
		}
	});
});
