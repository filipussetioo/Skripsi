<!-- {#
 # SharIF Judge
 # file: problems.php
 # author: Filipus Setio Nugroho <filipussetio@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?>fa-puzzle-piece<?= $this->endSection()?>
<?= $this->section('title') ?><?= $all_problems[$problem['id']]['name'] ?><?= $this->endSection() ?>
<?= $this->section('head_title') ?>Problem <?= $problem['id'] ?><?= $this->endSection() ?>

<?= $this->section('other_assets') ?>
<link rel='stylesheet' type='text/css' href='<?= base_url("assets/snippet/jquery.snippet.css") ?>'/>
<link rel='stylesheet' type='text/css' href='<?= base_url("assets/snippet/themes/github.css") ?>'/>
<script type='text/javascript' src="<?= base_url("assets/snippet/jquery.snippet.js") ?>"></script>
<script>
$(document).ready(function(){
	// Syntax highlighting increases the page's height, and we need to update the scroll-bar
	$('.problem_description').resize(function(){
		$('.scroll-wrapper').nanoScroller();
	});
	// Fix text directions for rtl text
	$.each($('.problem_description [dir="auto"]'), function(i, element){
		if (getComputedStyle(element).direction == 'rtl')
		{
			$(element).css('direction', 'rtl');
			$(element).parent('ul, ol').css('direction', 'rtl');
		}
	});
	// Syntax highlighting
	$('pre code.language-c').parent().snippet('c', {style: shj.color_scheme});
	$('pre code.language-cpp').parent().snippet('cpp', {style: shj.color_scheme});
	$('pre code.language-python').parent().snippet('python', {style: shj.color_scheme});
	$('pre code.language-java').parent().snippet('java', {style: shj.color_scheme});
});
</script>
<?= $this->endSection() ?>



<?= $this->section('title_menu') ?>
<?php if ($user->level >= 2): ?>
<span class="title_menu_item"><a href="<?= site_url("problems/edit/md/".$description_assignment['id'].'/'.$problem['id']) ?>"><i class="fa fa-pencil color2"></i> Edit Markdown</a></span>
<span class="title_menu_item"><a href="<?= site_url("problems/edit/html/".$description_assignment['id'].'/'.$problem['id']) ?>"><i class="fa fa-pencil color10"></i> Edit HTML</a></span>
<span class="title_menu_item"><a href="<?= site_url("problems/edit/plain/".$description_assignment['id'].'/'.$problem['id']) ?>"><i class="fa fa-pencil color8"></i> Edit Plain HTML</a></span>
<?php if ($problem['has_pdf']): ?>
<span class="title_menu_item"><a href="<?= site_url("assignments/pdf/".$description_assignment['id'].'/'.$problem['id']) ?>"><i class="fa fa-download color1"></i> PDF</a></span>
<?php endif ?>
<?php endif ?>
<?= $this->endSection() ?>



<?= $this->section('main_content') ?>
<div class="problem_description">
	<?= $problem['description'] ?>
</div>

<div id="right_sidebar">

	<div class="problems_widget">
		<p dir="auto"><i class="fa fa-file-text fa-lg color9"></i> <?= $description_assignment['name'] ?></p>

		<?php if (count($all_problems) == 0): ?>
			<p style="text-align: center;">Nothing to show...</p>
		<?php endif ?>

		<table class="sharif_table">
			<thead>
			<tr>
				<th rowspan="2">#</th>
				<th rowspan="2">Problem</th>
				<th rowspan="2">Score</th>
				<th rowspan="2">Upload<br>Only</th>
			</tr>
			</thead>
			<?php foreach($all_problems as $one_problem): ?>
				<tr <?= $problem['id'] == $one_problem['id'] ? ' class="hl"' : ''  ?>>
					<td><?= $one_problem['id'] ?></td>
					<td>
						<a dir="auto" href="<?= site_url("problems/".$description_assignment['id'].'/'.$one_problem['id']) ?>"><?= $one_problem['name'] ?></a>
					</td>
					<td><?= $one_problem['score'] ?></td>
					<td><?= $one_problem['is_upload_only'] ? 'Yes' : 'No' ?></td>
				</tr>
			<?php endforeach ?>
		</table>
	</div>

	<?php if ($can_submit): ?>
	<div class="problems_widget">
		<p><i class="fa fa-upload fa-lg color11"></i> Submit</p>
		<?= form_open_multipart('submit') ?>
		<input type="hidden" name="problem" value="<?= $problem['id'] ?>"/>

		<p class="input_p">
			<select id="languages" name="language" class="sharif_input full-width" aria-label="Select Language">
				<option value="0" selected="selected">-- Select Language --</option>
				<?php foreach ($problem['allowed_languages'] as $l): ?>
					<option value="<?= $l ?>"><?= $l ?></option>
				<?php endforeach ?>
			</select>
		</p>
		<p class="input_p">
			<input type="file" id="file" class="sharif_input full-width" name="userfile" aria-label="Upload File"/>
		</p>
		<p class="input_p">
			<input type="submit" value="Submit" class="sharif_input"/>
		</p>
		</form>
	</div>
	<?php endif ?>

</div>
<?= $this->endSection() ?> {# main_content #}