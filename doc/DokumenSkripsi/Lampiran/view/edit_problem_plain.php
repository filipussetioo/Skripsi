<!-- {#
 # SharIF Judge
 # file: edit_problem_plain.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->

<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?>fa-edit<?= $this->endSection()?>
<?= $this->section('title') ?>Edit Problem Description (Plain HTML)<?= $this->endSection() ?>
<?= $this->section('head_title') ?>Edit Problem Description (Plain HTML)<?= $this->endSection() ?>



<?= $this->section('other_assets') ?>
<script type='text/javascript' src="<?= base_url('assets/js/taboverride.min.js') ?>"></script>
<script>
	$(document).ready(function(){
		tabOverride.set(document.getElementById('md_editor'));
	});
</script>
<?= $this->endSection() ?>



<?= $this->section('main_content') ?> 
<p>
	Assignment <?= $description_assignment['id'] ?> (<span dir="auto"><?= $description_assignment['name'] ?></span>)<br>
	Problem <?= $problem['id'] ?>
</p>
<p>
	<i class="fa fa-warning color3"></i>
	When you edit as html, the markdown code will be removed.
</p>
<?= form_open("problems/edit/html/".$description_assignment['id'].'/'.$problem['id']) ?>
<p class="input_p">
	<textarea name="text" rows="30" cols="80" class="sharif_input" id="html_editor" aria-label="HTML Editor"><?= $problem['description'] ?></textarea>
</p>
<p class="input_p">
	<input type="submit" value="Save" class="sharif_input"/>
</p>
</form>
<?= $this->endSection() ?>