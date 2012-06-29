<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6">
<![endif]-->
<!--[if IE 7]>
<html id="ie7">
<![endif]-->
<!--[if IE 8]>
<html id="ie8">
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html>
<!--<![endif]-->
<head>
	<title><?php the_title(); ?></title>
	<?php
	print_header_scripts();
	print_styles();
	?>
</head>
<body>
<header></header>
<section class="main">
	<article class="content">
		<?php the_content(); ?>
		<footer class="meta"><?php edit_link(); ?></footer>
	</article>
	<aside class="page-list">
		<?php list_pages(); ?>
	</aside>
</section>
<?php
print_footer_scripts();
?>
</body>
</html>