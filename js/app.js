jQuery(document).ready(function( $ ) {
	
	// $ Works! You can test it with next line if you like
	// console.log('test');

	var num = 1;

	function changePortfolio() {
		var activePortfolio = $(".fxp-slider .after.active");

  		var nextPortfolio = activePortfolio.next()

		if( nextPortfolio.length == 0 ) {
			nextPortfolio = $(".fxp-slider .after:first-child");
			num = 0;
		}


		nextPortfolio.addClass("active");
		activePortfolio.removeClass("active");
		$(".control").removeClass("active");
		$(".control").eq(num).addClass("active");	
		num++;	
		// console.log ("current rotation: " + num);
	}

	var afterNum = 0;
	$('.fxp-slider .laptop .after').each(function () {
		afterNum++;
	    var $class = $(this).attr("class");
	    $(".controls").append('<div class="control control-'+ afterNum +'" data-id="'+ afterNum +'"></div>');
	});
	$(".controls .control").eq(0).addClass("active");

	if( $(".fxp-slider").length != 0 ) {
		var startNextPortfolio;
		 var timer = function(){
		 startNextPortfolio = setInterval(function(){
		  changePortfolio()
		},5000);
		};
		timer();
	}

	$(".control").on("click", function() {
		clearInterval(startNextPortfolio);
		var cactive = $(this).data("id");
		$(".control").removeClass("active");
		

		$(".fxp-slider .active").removeClass("active");
		$(".fxp-slider .active").removeClass("active");
		$(".fxp-slider .laptop .screen").find(".after").eq(cactive-1).addClass("active");
		$(".fxp-slider .phone .screen").find(".after").eq(cactive-1).addClass("active");
		$(this).addClass("active");
		num = cactive;
		timer();
	});

	function getUrlVars() {
	    var vars = {};
	    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
	        vars[key] = value;
	    });
	    return vars;
	}
	


	var first = getUrlVars()["inquiry"] || '';
	if (first !== undefined)
	    $("#et_pb_contact_subject_1").find("option").eq(first).attr("selected", "selected")
	else alert('ooops');





	$(".service-btn").on('click', function() {
		var pid = $(this).data("pid");

		$.ajax({
		    type: "POST",
		    url: ajaxUrl,
		    data: {action : 'remove_item_from_cart','product_id' : pid},
		    success: function (res) {
		        if (res) {
		            console.log('Removed Successfully');
		        }
		    }
		});
	});



}); //end of jquery