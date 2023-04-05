"use strict";

jQuery(function($) {
	function settingsInPageNavigations($) {
		if ($(".sln-inpage_navbar_wrapper").length) {
			var initialOffset =
				$(".sln-inpage_navbar_wrapper").offset().top - 40;
			$(".sln-inpage_navbar_wrapper").affix({
				offset: {
					top: initialOffset,
				},
			});
			$(".sln-nav-tab-wrapper").affix({
				offset: {
					top: initialOffset,
				},
			});
		}
		$(".sln-inpage_navbar_wrapper .sln-inpage_navbarlink").each(function() {
			var initialOffset =
				$(this).offset().left -
				$(".sln-inpage_navbar_inner").offset().left;
			$(this).attr("data-initialOffset", initialOffset);
			$(this).on("click", function(e) {
				var thisTarget = $(this).attr("href");
				$("html,body").animate(
					{
						scrollTop: $("" + thisTarget + "").offset().top - 0,
					},
					"fast"
				);
				$(".sln-inpage_navbar_wrapper").removeClass(
					"sln-inpage_navbar_wrapper--pusheddown"
				);
				$(".sln-nav-tab-wrapper").removeClass(
					"sln-nav-tab-wrapper--inview"
				);
				$("" + thisTarget + "").addClass("sln-box--appeared--delayed");
				setTimeout(function() {
					$("" + thisTarget + "").removeClass(
						"sln-box--appeared--delayed"
					);
				}, 2000);
				if ($(window).width() < 768) {
					$("body").removeClass("sln-noscroll");
					$(".sln-inpage_navbar_wrapper").css("top", "0");
				}
				if ($(".sln-box--haspanel").length) {
					$(".sln-box--haspanel .sln-box__panelcollapse.in").collapse(
						"hide"
					);
					setTimeout(function() {
						$("" + thisTarget + " .collapse").collapse("show");
					}, 200);
				}
				e.preventDefault();
			});
		});
		$("body .sln-salon--settings").on(
			"activate.bs.scrollspy",
			function() {
				var x = $(
					".sln-inpage_navbaritem.active .sln-inpage_navbarlink"
				).attr("data-initialOffset");
				$(".sln-inpage_navbar_inner").scrollLeft(x - 10);
			}
		);
		$(".sln-inpage_navbar__currenttab").on("click", function(e) {
			var navHeight = $(".sln-nav-tab-wrapper").outerHeight();
			$(".sln-inpage_navbar_wrapper").addClass(
				"sln-inpage_navbar_wrapper--pusheddown"
			);
			$(".sln-nav-tab-wrapper").addClass("sln-nav-tab-wrapper--inview");
			if ($(window).width() < 768) {
				$("body").addClass("sln-noscroll");
				$(".sln-inpage_navbar_wrapper").css("top", navHeight);
			}
			e.preventDefault();
		});
		$(".sln-nav-tab--close, .sln-tab__curtain").on("click", function(e) {
			$(".sln-inpage_navbar_wrapper").removeClass(
				"sln-inpage_navbar_wrapper--pusheddown"
			);
			$(".sln-nav-tab-wrapper").removeClass(
				"sln-nav-tab-wrapper--inview"
			);
			if ($(window).width() < 768) {
				$("body").removeClass("sln-noscroll");
				$(".sln-inpage_navbar_wrapper").css("top", "0");
			}
			e.preventDefault();
		});
		$(".sln-inpage_navbar__scroller--right").on("click", function(e) {
			var distance = $(".sln-inpage_navbar_inner").outerWidth();
			$(".sln-inpage_navbar_inner").animate(
				{
					scrollLeft: "+=" + distance,
				},
				500
			);
			e.preventDefault();
		});
		$(".sln-inpage_navbar__scroller--left").on("click", function(e) {
			var distance = $(".sln-inpage_navbar_inner").outerWidth();
			$(".sln-inpage_navbar_inner").animate(
				{
					scrollLeft: "+=" + distance * -1,
				},
				500
			);
			e.preventDefault();
		});
	}

	if ($("body .sln-salon--settings").length) {
		settingsInPageNavigations($);
	}
	Beacon("once", "ready", () => {
		console.log(
			"This will only get called the first time the open event is triggered"
		);
		$("#beacon-container .BeaconContainer").prepend(
			'<a href="#nogo" class="sln-helpchat__close"><span class="sr-only">Close help chat</span></a>'
		);
	});
	$(document).on("click", ".sln-helpchat__close", function() {
		Beacon("close");
	});
});
