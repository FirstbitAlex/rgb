<?php
get_header();
?>

<main id="main">
	<div class="container">
		<h1 class="page-title">
			<?php the_title() ?>
		</h1>
	</div>

	<section>
		<div class="container">
			<div class="page-content content-wrap">
				<?php the_content() ?>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
