import { formatQuantity as _formatQuantity } from "format-quantity";
import { formatQuantity, parseQuantity } from './quantities';

(function () {
	class recipeGlobal {
		constructor() { }
		static initLightGallery(content) {
			var _videos = content.querySelectorAll(".dr-instruction-videopop");
			if (_videos) {
				_videos.forEach(function (_video) {
					lightGallery(_video, {
						selector: "this",
					});
				});
			}
		}

		static initRatings(content) {
			jQuery(content)
				.find(".dr-comment-form-rating")
				.each(function () {
					var ratingInputField = jQuery(this)
						.parent()
						.find('input[name="rating"]');

					jQuery(this).rateYo({
						halfStar: true,
						rating: 5,
						onInit: function (rating, rateYoInstance) {
							ratingInputField.val(rating);
						},
						onChange: function (rating, rateYoInstance) {
							// jQuery('.comment-rating-field-message').hide();
							ratingInputField.val(rating);
						},
					});
				});
		}
	}

	window["recipe_global"] = recipeGlobal;

})();

(function ($) {
	var nonce;
	var rtl;
	if ($("body").hasClass("rtl")) {
		rtl = true;
	} else {
		rtl = false;
	}
	$(".dr-post-carousel").owlCarousel({
		items: 3,
		autoplay: false,
		loop: false,
		nav: true,
		dots: false,
		rewind: true,
		margin: 30,
		autoplaySpeed: 800,
		autoplayTimeout: 3000,
		rtl: rtl,
		navText: [
			'<svg xmlns="http://www.w3.org/2000/svg" width="18.479" height="12.689" viewBox="0 0 18.479 12.689"><g transform="translate(17.729 11.628) rotate(180)"><path d="M7820.11-1126.021l5.284,5.284-5.284,5.284" transform="translate(-7808.726 1126.021)" fill="none" stroke="#232323" stroke-linecap="round" stroke-width="1.5"/><path d="M6558.865-354.415H6542.66" transform="translate(-6542.66 359.699)" fill="none" stroke="#232323" stroke-linecap="round" stroke-width="1.5"/></g></svg>',
			'<svg xmlns="http://www.w3.org/2000/svg" width="18.479" height="12.689" viewBox="0 0 18.479 12.689"><g transform="translate(0.75 1.061)"><path d="M7820.11-1126.021l5.284,5.284-5.284,5.284" transform="translate(-7808.726 1126.021)" fill="none" stroke="#232323" stroke-linecap="round" stroke-width="1.5"/><path d="M6558.865-354.415H6542.66" transform="translate(-6542.66 359.699)" fill="none" stroke="#232323" stroke-linecap="round" stroke-width="1.5"/></g></svg>',
		],
		responsive: {
			0: {
				items: 1,
			},
			768: {
				items: 2,
			},
			1025: {
				items: 3,
			},
		},
	});

	// Search Filters
	$(".js-select2").select2({
		closeOnSelect: false,
		placeholder: delicious_recipes.search_placeholder,
		allowClear: true,
	});

	var searchAjaxRunning = null;;
	$("body").on("change", ".dr-search-field select", function () {
		var choices = {};
		$(".dr-search-field select option").each(function () {
			if ($(this).is(":selected")) {
				if (!choices.hasOwnProperty($(this).attr("name"))) {
					choices[$(this).attr("name")] = [];
				}
				var idx = $.inArray(this.value, choices[$(this).attr("name")]);
				if (idx == -1) {
					choices[$(this).attr("name")].push(this.value);
				}
			}
		});

		var nonce = $("#dr-search-nonce").val();

		// Disable previous AJAX call if new AJAX call is to be made.
		// This will prevent calling multiple AJAX call response.
		if ( null !== searchAjaxRunning ) {
			searchAjaxRunning.abort();
		}

		searchAjaxRunning = jQuery.ajax({
			url: delicious_recipes.ajax_url,
			data: {
				action: "recipe_search_results",
				search: choices,
				nonce: nonce,
			},
			dataType: "json",
			type: "post",
			beforeSend: function () {
				$(".dr-search-item-wrap").addClass("dr-loading");
			},
			success: function (response) {
				if (response.success) {
					var template = wp.template("search-block-tmp");
					$(".dr-search-item-wrap").html(
						template(response.data.results)
					);
					$(".navigation.pagination .nav-links")
						.addClass("dr-ajax-paginate")
						.html(response.data.pagination);

					if ("AND" === response.data?.logic) {
						$(
							'.advance-search-options select.js-select2:not([name="sorting"]) option'
						).each(function (i, e) {
							$(this).text($(e).data("title") + " (0)");
						});

						if (response.data?.terms) {
							var terms = response.data.terms;
							if ("object" === typeof terms) {
								for (var tax in terms) {
									if ("object" === typeof terms[tax]) {
										for (var _tax in terms[tax]) {
											var tax_term = terms[tax][_tax];
											$(
												`select[name="${tax}"] option[value="${tax_term.term_id}"]`
											).text(
												`${tax_term.name} (${tax_term.count})`
											);
										}
									}
								}
							}
						}

						if (response.data?.metas) {
							var metas = response.data.metas;
							if ("object" === typeof metas) {
								for (var meta_name in metas) {
									for (var meta_value in metas[meta_name]) {
										var meta = metas[meta_name][meta_value];
										var $option = $(
											`select[name="${meta_name}"] option[value="${meta_value}"]`
										);
										var title = $option.data("title");

										$option.text(`${title} (${meta})`);
									}
								}
							}
						}

						$(".advance-search-options select.js-select2").select2({
							closeOnSelect: false,
							placeholder: delicious_recipes.search_placeholder,
							allowClear: true,
						});
					}

					searchAjaxRunning = null;
				}
			},
			complete: function () {
				$(".dr-search-item-wrap").removeClass("dr-loading");
				searchAjaxRunning = null;
			},
		});
	});

	$(document).on("click", ".dr-ajax-paginate a.page-numbers", function (e) {
		e.preventDefault();
		var choices = {};
		$(".dr-search-field select option").each(function () {
			if ($(this).is(":selected")) {
				if (!choices.hasOwnProperty($(this).attr("name"))) {
					choices[$(this).attr("name")] = [];
				}
				var idx = $.inArray(this.value, choices[$(this).attr("name")]);
				if (idx == -1) {
					choices[$(this).attr("name")].push(this.value);
				}
			}
		});

		var nonce = $("#dr-search-nonce").val();

		jQuery.ajax({
			url: delicious_recipes.ajax_url,
			data: {
				action: "recipe_search_results",
				search: choices,
				nonce: nonce,
				paged: $(this).attr("href").split("=")[1],
			},
			dataType: "json",
			type: "post",
			beforeSend: function () {
				$(".dr-search-item-wrap").addClass("dr-loading");
			},
			success: function (response) {
				if (response.success) {
					var template = wp.template("search-block-tmp");
					$(".dr-search-item-wrap").html(
						template(response.data.results)
					);
					$(".navigation.pagination .nav-links")
						.addClass("dr-ajax-paginate")
						.html(response.data.pagination);
				}
			},
			complete: function () {
				$(".dr-search-item-wrap").removeClass("dr-loading");
			},
		});
	});

	//show/hide social share
	$(".post-share a.meta-title").on("click", function (e) {
		e.stopPropagation();
		$(this).siblings(".social-networks").slideToggle();
	});

	$(".post-share").on("click", function (e) {
		e.stopPropagation();
	});

	$("body, html").on("click", function () {
		$(".post-share .social-networks").slideUp();
	});

	//pull recipe category title left
	$(".dr-category a, .post-navigation article .dr-category > span").each(
		function () {
			var recipeCatWidth = $(this).width();
			var recipeCatTitleWidth = $(this)
				.children(".cat-name")
				.outerWidth();
			var catPullValue =
				(parseInt(recipeCatTitleWidth) - parseInt(recipeCatWidth)) / 2;
			$(this).children(".cat-name").css("left", -catPullValue);
			if ($("body").hasClass("rtl")) {
				$(this).children(".cat-name").css({
					left: "auto",
					right: -catPullValue,
				});
			} else {
				$(this).children(".cat-name").css("left", -catPullValue);
			}
		}
	);

	/** AJAX call to fetch recipe like */
	$(document).on('ready', function() {
		var $likeElement = $('[data-liked_recipe_id]');
		if ( !$likeElement.length ) {
			return;
		}

		var recipeIds = [];
		$likeElement.each(function(index, element) {
			recipeIds = [...recipeIds, $(element).data('liked_recipe_id')];
		});

		recipeIds = [...new Set(recipeIds.filter(element => !!element))];

		if ( ! recipeIds.length ) {
			return;
		}

		$.ajax({
			type: 'POST',
			url: delicious_recipes.ajax_url,
			data: {
				action: 'recipe_get_likes',
				ids: [...recipeIds]
			},
			success: function(response) {
				if ( true !== response.success ) {
					$likeElement.find('.dr_like__recipe').removeClass('loading');

					return;
				}

				if (response.data?.recipes && 'object' === typeof response.data.recipes) {
					for( var recipe_id in response.data.recipes ) {
						$(`[data-liked_recipe_id="${recipe_id}"] .dr-likes-total`)
						.text(response.data.recipes[recipe_id].likes_count)
						.closest('.dr_like__recipe')
						.attr('title', response.data.recipes[recipe_id].likes)
						.removeClass('loading');
					}
				}
			},
			error: function() {
				$likeElement.find('.dr_like__recipe').removeClass('loading');
			}
		});
	});

	/** Ajax call for recipe like */
	$(document).on("click", ".dr_like__recipe", function (e) {
		e.preventDefault();
		var container = $(this);

		var id = container.attr("id").split("-").pop();

		if (container.hasClass("recipe-liked")) {
			container.removeClass("recipe-liked");
			container.addClass("like-recipe");
			var addRemove = "remove";
		} else {
			container.removeClass("like-recipe");
			container.addClass("recipe-liked");
			var addRemove = "add";
		}

		$.ajax({
			type: "post",
			url: delicious_recipes.ajax_url,
			data: { action: "recipe_likes", add_remove: addRemove, id: id },
			beforeSend: function () {
				container.addClass("loading");
			},
			success: function (data) {
				container.attr("title", data.data.likes);
				container.find(".dr-likes-total").html(data.data.likes_count);
			},
		}).done(function () {
			container.removeClass("loading");
			// update likes in floatingBarData
			if (typeof recipeProGlobal !== "undefined") {
				let likesParent = container.parent(".dr-floating-box .post-like .single-like");
				if (likesParent.length > 0) {
					let path = window.location.href;
					let data = recipeProGlobal.filter((item) => item.path === path);
					if (data[0]) {
						data[0].likes = container.parent(".single-like").html();
					}
				}
			}
		});
	});

	/****   Wishlist a Recipe   ****/
	$(document).on("click", ".dr-recipe-wishlist span.dr-bookmark-wishlist", function (e) {
		e.preventDefault();
		var thisHeart = $(this),
			recipeID = thisHeart.data("recipe-id");

		if (thisHeart.hasClass("dr-wishlist-is-bookmarked")) {
			thisHeart.removeClass("dr-wishlist-is-bookmarked");
			var addRemove = "remove";
		} else {
			thisHeart.addClass("dr-wishlist-is-bookmarked");
			var addRemove = "add";
		}

		$.ajax({
			type: "post",
			url: delicious_recipes.ajax_url,
			data: {
				action: "delicious_recipes_wishlist",
				add_remove: addRemove,
				recipe_id: recipeID,
			},
			beforeSend: function () {
				thisHeart.addClass("loading");
			},
			success: function (data) {
				thisHeart.find(".dr-wishlist-total").html(data.data.wishlists);
				thisHeart.find(".dr-wishlist-info").html(data.data.message);
			},
		}).done(function () {
			thisHeart.removeClass("loading");
			// update wishilist in floatingBarData
			if (typeof recipeProGlobal !== "undefined") {
				let wishlistParent = thisHeart.parent(".dr-floating-box .dr-add-to-wishlist-single .dr-recipe-wishlist");
				if (wishlistParent.length > 0) {
					let path = window.location.href;
					let data = recipeProGlobal.filter((item) => item.path === path);
					if (data[0]) {
						data[0].wishlist = thisHeart.parent(".dr-recipe-wishlist").html();
					}
				}
			}
		});
	});
	if ($(".dr-recipe-wishlist span.dr-popup-user__registration").length) {
		// Get the modal
		var modal = document.getElementById(
			"dr-user__registration-login-popup"
		);

		// Get the button that opens the modal
		var _popupBtns = document.querySelectorAll(
			".dr-popup-user__registration"
		);

		if (_popupBtns.length) {
			_popupBtns.forEach(function (_popupBtn) {
				_popupBtn.addEventListener("click", function (e) {
					e.preventDefault();
					modal.style.display = "block";
				});
			});
		}

		// Get the <span> element that closes the modal
		var span = document.getElementsByClassName(
			"dr-user__registration-login-popup-close"
		)[0];

		// When the user clicks on <span> (x), close the modal
		span.onclick = function () {
			modal.style.display = "none";
		};

		// When the user clicks anywhere outside of the modal, close it
		window.onclick = function (event) {
			if (event.target == modal) {
				modal.style.display = "none";
			}
		};

		$(document).on("submit", "form[name='dr-form__log-in']", function (e) {
			e.preventDefault();

			var loginform = $(this);
			var username = loginform.find('input[name="username"]').val();
			var password = loginform.find('input[name="password"]').val();
			var rememberme = loginform.find('input[name="rememberme"]').val();
			var login = loginform.find('input[name="login"]').val();
			nonce = loginform
				.find('input[name="delicious_recipes_user_login_nonce"]')
				.val();

			$.ajax({
				url: delicious_recipes.ajax_url,
				data: {
					action: "delicious_recipes_process_login",
					username: username,
					password: password,
					rememberme: rememberme,
					login: login,
					delicious_recipes_user_login_nonce: nonce,
					calling_action: "delicious_recipes_modal_login",
				},
				dataType: "json",
				type: "post",
				beforeSend: function () {
					loginform.addClass("dr-loading");
					$(".delicious-recipes-success-msg").hide();
					$(".delicious-recipes-error-msg").hide();
				},
				success: function (response) {
					if (response.success) {
						$(".dr-recipe-wishlist > span").removeClass(
							"dr-popup-user__registration"
						);
						$(".dr-recipe-wishlist > span").addClass(
							"dr-bookmark-wishlist"
						);
						$(".delicious-recipes-success-msg")
							.html(response.data.success)
							.show();
						location.reload();
					} else {
						console.log(response.data.error);
						$(".delicious-recipes-error-msg")
							.html(response.data.error)
							.show();
					}
				},
				complete: function () {
					loginform.removeClass("dr-loading");
				},
			});
		});
	}

	$("#dr-recipes-clear-filters").on("click", function (e) {
		e.preventDefault();
		$(".dr-advance-search .advance-search-options select").each(
			function () {
				$(this).val(null).trigger("change");
			}
		);
	});

	// recipe instruction
	$('.dr-inst-mark-read input[type="checkbox"]').each(function () {
		$(this).on("click", function () {
			if ($(this).prop("checked") == true) {
				$(this).parents("li").addClass("dr-instruction-checked");
			} else {
				$(this).parents("li").removeClass("dr-instruction-checked");
			}
		});
	});

	$("form[name='dr-form__log-in']").parsley();

	$(document).on("click", ".dr-ud_tab", function () {
		$(".dr-ud__sidebar").removeClass("collapsed");
	});
})(jQuery);

window.addEventListener("load", recipeScripts());

function recipeScripts() {
	// creating utils
	var Util = function () { };
	Util.on = function (eventName, selector, callback) {
		document.addEventListener(
			eventName,
			function (event) {
				for (
					var elTarget = event.target;
					elTarget && elTarget != this;
					elTarget = elTarget.parentNode
				) {
					if (elTarget.matches(selector)) {
						callback.call(elTarget, event);
						break;
					}
				}
			},
			false
		);
	};

	window["delicious_recipes"]["utilities"] = Util;

	// gallary modal
	Util.on("click", ".view-gallery-btn", function (event) {
		var targetEl = this;
		var images = JSON.parse(targetEl.dataset.lgSettings);
		var allimages = images.map(function (img) {
			return { src: img.previewURL };
		});
		var viewGallery = window.lightGallery(event.target, {
			dynamic: true,
			dynamicEl: allimages,
		});
		viewGallery.openGallery(0);
	});

	// video hide toggle
	Util.on("click", ".dr-video-toggle", function (e) {
		if (e.target.matches(".dr-video-toggle")) {
			let videoTargets = document.querySelectorAll(
				e.target.getAttribute("data-target")
			);
			videoTargets.forEach(function (video) {
				if (video.style.display === "none") {
					video.style.display = "block";
				} else {
					video.style.display = "none";
				}
			});
		}
	});

	if ('scale' === delicious_recipes.global_settings.adjustableServingType) {

		/**
		 * @since 1.4.7
		 */

		const _useFraction = delicious_recipes.global_settings.useFraction;
		const useFraction = 'yes' === _useFraction[_useFraction.length - 1];
		const scaleBtns = document.querySelectorAll('.dr-ingredients-list .scale-btn-wrapper button');

		if (scaleBtns.length > 0) {

			const servingValue = document.querySelector('.dr-ingredients-scale').getAttribute('data-serving-value');
			const ingredientLists = document.querySelectorAll('.dr-ingredients-list .dr-unordered-list li');

			function handleFractionBtnClick(event) {

				event.preventDefault();

				scaleBtns.forEach((item) => item.classList.remove('active'));

				event.target.classList.add('active');

				const scaleValue = parseQuantity(event.target.getAttribute('data-scale'));
				const recipeID = event.target.getAttribute('data-recipe');

				const printBtns = document.querySelectorAll(".dr-single-recipe-print-" + recipeID);

				printBtns.forEach(function (printBtn) {
					const defaultPrintLink = printBtn.getAttribute("href");
					const defaultPrintAttrs = defaultPrintLink.split("?");
					const newPrintAttrs = "print_recipe=true&recipe_servings=" + (servingValue * scaleValue);

					printBtn.setAttribute("href", defaultPrintAttrs[0] + "?" + newPrintAttrs);
				});

				ingredientLists.forEach(function (ingredientList) {

					const ingredient = ingredientList.querySelector('.ingredient_quantity');

					const quantity = parseQuantity(ingredient.getAttribute("data-original"));

					const _calculatedQty = (scaleValue * quantity);

					const calculatedQty = (_calculatedQty % 1 === 0) ? _calculatedQty : _calculatedQty.toFixed(2);

					ingredient.innerHTML = useFraction ? _formatQuantity(_calculatedQty, true) : calculatedQty;

				});
			}

			scaleBtns.forEach(function (scaleBtn) {
				scaleBtn.addEventListener('click', handleFractionBtnClick);
			});

			document.querySelector('.dr-ingredients-list .scale-btn-wrapper .active').click();

		}
	} else {
		// ingredients
		["keyup", "mouseup"].forEach(function (evt) {
			document.addEventListener(evt, function (e) {
				if (e.target.classList.contains("dr-scale-ingredients")) {
					let ingredient = e.target.closest(".dr-ingredients-list");
					let newServe = e.target.value;
					let recipeID = e.target.getAttribute("data-recipe");
					newServe = parseQuantity(newServe);
					let originalServe = e.target.getAttribute("data-original");
					originalServe = parseQuantity(originalServe);
					let ingredientQuantities = ingredient.querySelectorAll(
						".ingredient_quantity"
					);
					let printBtns = document.querySelectorAll(
						".dr-single-recipe-print-" + recipeID
					);
					printBtns.forEach(function (printBtn) {
						let default_print_lnk = printBtn.getAttribute("href");
						var default_print_attrs = default_print_lnk.split("?");
						// This may need something more complex...
						var new_print_attrs =
							"print_recipe=true&recipe_servings=" + newServe;
						// This changes the href of the link to the new one.
						printBtn.setAttribute(
							"href",
							default_print_attrs[0] + "?" + new_print_attrs
						);
					});

					ingredientQuantities.forEach(function (qty) {
						let ingredientQty = qty.getAttribute("data-original");

						if (ingredientQty != "") {
							ingredientQty = parseQuantity(ingredientQty);
							let newIngredientQty = (ingredientQty / originalServe) * newServe;
							if (!isNaN(newIngredientQty)) {
								newIngredientQty = formatQuantity(newIngredientQty, 2, true);
							}
							qty.innerText = newIngredientQty;
						}
					});
				}
			});
		});
	}


	// smooth scroll intoview
	Util.on("click", "a", function (e) {
		let stringText = e.target.getAttribute("href");

		if (!stringText) {
			return;
		}

		let arr = stringText?.split('');
		if (arr.length > 1) {
			if (e.target.getAttribute("href")?.match(/^#.*$/)) {
				e.preventDefault();
				let targetID = document.querySelector(
					e.target.getAttribute("href")
				);
				targetID?.scrollIntoView({
					behavior: "smooth",
				});
			}
		}
	});

	// init ratings
	recipe_global.initRatings("body");

	// init light gallery
	recipe_global.initLightGallery(document.body);

	document.addEventListener("click", function (e) {
		// faq accordion
		handleFaqAccordion(e);
	});

}

function handleFaqAccordion(e) {
	if (e.target.matches(".dr-switch-btn")) {
		let switchButtons = e.target;
		if (switchButtons) {
			let switchStats = switchButtons.getAttribute("data-switch");
			let switchOnText = switchButtons.getAttribute("data-switch-on");
			let switchOffText = switchButtons.getAttribute("data-switch-off");
			let targetID = switchButtons.getAttribute("data-target");
			if (switchStats == "off") {
				switchButtons.setAttribute("data-switch", "on");
				switchButtons.innerText = switchOnText;
			} else {
				switchButtons.setAttribute("data-switch", "off");
				switchButtons.innerText = switchOffText;
			}
			let switchStatus = switchButtons.getAttribute("data-switch");
			let faqItems = document
				.querySelector(targetID)
				.querySelectorAll(".dr-faq-item");
			faqToggle(faqItems, switchStatus);
		}
	}

	// faq accordion
	["dr-faq-title-wrap", "dr-title"].forEach(function (cls) {
		if (e.target.classList.contains(cls)) {
			if (e.target.closest(".dr-faq-item").matches(".faq-active")) {
				e.target.closest(".dr-faq-item").classList.remove("faq-active");
			} else {
				e.target.closest(".dr-faq-item").classList.add("faq-active");
			}
		}
	});

	function faqToggle(toggleItem, switchStatus) {
		if (switchStatus == "on") {
			toggleItem.forEach(function (item) {
				item.classList.add("faq-active");
			});
		} else {
			toggleItem.forEach(function (item) {
				item.classList.remove("faq-active");
			});
		}
	}
}

window.onload = function () {

	// disable input number wheel scroll
	var inputsNumber = document.querySelectorAll('input[type="number"]');
	if (inputsNumber) {
		inputsNumber.forEach(function (input) {
			input.addEventListener('mousewheel', function (e) {
				e.preventDefault();
			})
		})
	}
}