jQuery(document).ready(function( $ ) {

	function addToCart(p_id) {
      $.get('/?post_type=product&add-to-cart=' + p_id, function() {
        console.log("Added to cart: " + p_id);
      });
   	}

   	function removeItemFromCart(p_id) {
		$.ajax({
		    type: "POST",
		    url: my_ajax_object.ajax_url,
		    data: {action : 'remove_item_from_cart','product_id' : p_id},
		    success: function (res) {
		        if (res) {
		            console.log('Removed Successfully');
		        }
		    }
		});
   	}
   	//$(".step-two").css('display', 'none');
    $(".step-three").css('display', 'none');

	if($(".step-one .product_id").length) {
	    $(".step-one .product_id").on('click', function(e) {
	        e.preventDefault();
	        $('.step-one .et_pb_button').removeClass("selected").html('SELECT');
	        $(this).addClass("selected").html('SELECTED');

	        $('.step-two').css('display', 'block');
	        create_cgs3()
	    }).animate({ scrollTop: $(".step-two").offset().top }, 2000)
	

	    $(".step-three .et_pb_button").on('click', function(e) {
	        e.preventDefault();
	        $('.step-three .et_pb_button').removeClass("selected").html('SELECT');
	        $(this).addClass("selected").html('SELECTED');
	        create_cgs3()
	    });

	    $(".step-two .et_pb_button").on('click', function(e) {
	        e.preventDefault();
	        if($(this).hasClass('selected')) {
	            $(this).removeClass("selected").html('SELECT');
	        } else {
	            $(this).addClass("selected").html('SELECTED');
	        }
	        $('.step-three').css('display', 'block');
	        create_cgs3()
	    });

	    
	    function reg_rep(str) {
	    	return str.replace(/ *\([^)]*\) */g, "");
	    }


	    function create_cgs3() {
	    	//empty the html
	        $(".summary-gs").empty();
	        //step one
	        $(".summary-gs").append('<div>', {"class" : "primary-item item"});
	        $(".summary-gs").append('<h4>Cornerstone Package:</h4>');
	        var sone_selected = $('.step-one .selected').closest('.step-one').find('h3');
	        $(".summary-gs").append( reg_rep($(sone_selected).html()) );
	        $(".summary-gs").append('</div>');


	        if( $('.step-two .selected').length > 0 ) {
		    	//step two
		        $(".summary-gs").append('<div class="adons-item item">');
		        $(".summary-gs").append('<h4>Biz Booster:</h4>');
		    
		        $( ".step-two .selected" ).each(function( i, el ) {
					var adons = $(this).closest('.step-two').find('h3');
		        	$(".summary-gs").append('<p>' + reg_rep($(adons).html()) + '</p>');
		        });

		        $(".summary-gs").append('</div>');
		    }

		    if( $('.step-three .selected').length > 0 ) {
		        //step three
		        $(".summary-gs").append('<div class="support-item item">');
		        $(".summary-gs").append('<h4>Premium Support:</h4>');
		        var sone_selected = $('.step-three .selected').closest('.step-three').find('h3')
		        $(".summary-gs").append( reg_rep($(sone_selected).html()) );
		        $(".summary-gs").append('</div>');
		    }


		    var cgs_total = 0;

		    $( ".selected" ).each(function( i, el ) {
				var adons = $(this).closest('.et_pb_row_inner').find('h3');
				var num = parseInt($(adons).html().match(/\d+/));
				//console.log(i + ' : ' +num);
				if( $.isNumeric(num) ) {
					cgs_total = cgs_total + num;
				}
		    });

	        
	        //Fine Print
	        $(".summary-gs").append('<div class="total-item item">');
	        $(".summary-gs").append('<h4>Total:</h4>');
	        $(".summary-gs").append( '<p>$' + cgs_total.toFixed(2) + '</p>' );
	        $(".summary-gs").append('</div>');      

	        $(".summary-gs").append('<a class="et_pb_button et_gs_continue" href="https://webninjahq.com/cart/">Continue</a>')

	    }

	    create_cgs3();

	    $("#text-2").css("width", $("#text-2_clone").width());

	   	var url = $(".et_gs_continue").attr("href");
	   	

	   	$(".product_id").on("click", function() {
	   		newCheckOutURL();
	   	});

	   	function newCheckOutURL() {
	   		var pids = '';
	   		$( ".product_id.selected" ).each(function( index ) {
	   			var p_id = $(this).attr("id");
		 		if(!pids){
		 			pids = p_id;
		 		} else {
		 			pids = pids + ',' + p_id;
		 		}
			});
			$(".et_gs_continue").attr("href", url + '?add-to-cart=' + pids)
	   	}



	   	//perform first click on starter package
	   	if($("body").hasClass("page-id-31027")) {
	   		var hash = window.location.hash;
	   		console.log(hash);

			if(hash == "#starter") {
				$(".unique-pricing #31025").click();
				console.log("Starter");
			} else {
				$(".unique-pricing #31026").click();
				console.log("Elite");
			}
	   		
	   		newCheckOutURL();
	   	}
	}






}); //end of jquery