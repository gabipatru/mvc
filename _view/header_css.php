<?php
if (!$_SKIP_CSS) {
	foreach($_aCSS as $value) {
		?><link rel="stylesheet" type="text/css" href="<?php echo ($https ? HTTPS_CSS : HTTP_CSS).$value;?>" />
<?php }
}
?>