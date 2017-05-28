<?php if (!$_SKIP_HEADER) {?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php require_once(VIEW_DIR.'/_core/header_meta.php');?>
        <!--[if lte IE 8]><script src="assets/css/ie/html5shiv.js"></script><![endif]-->
	<?php require_once(VIEW_DIR.'/_core/header_css.php');?>
        <!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie/v8.css" /><![endif]-->
        <!--[if lte IE 8]><script src="assets/css/ie/respond.min.js"></script><![endif]-->
        <script type="text/javascript" nonce="29af2i">
        	// this special function can only be declared here
        	var ValidateSubmit = {
		        isSubmitted: false,
		        submit: function() {
	                if (ValidateSubmit.isSubmitted) {
			            return false;
		            }
		            ValidateSubmit.isSubmitted = true;
		            $("input[type=submit]").attr("disabled", "disabled");
		            form.submit();
	            },
		        END: null
	        };
        </script>
	</head>
<?php }?>