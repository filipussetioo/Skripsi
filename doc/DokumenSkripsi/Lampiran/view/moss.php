<!-- {#
 # SharIF Judge
 # file: moss.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?>fa-shield<?= $this->endSection()?>
<?= $this->section('title') ?>Detect Similar Codes<?= $this->endSection() ?>
<?= $this->section('head_title') ?>Detect Similar Codes<?= $this->endSection() ?>



<?= $this->section('other_assets') ?>
	<script type='text/javascript' src="<?= base_url('assets/js/taboverride.min.js') ?>"></script>
	<script>
		$(document).ready(function(){
			tabOverride.set(document.getElementById('md_editor'));
		});
	</script>
<?= $this->endSection() ?>




<?= $this->section('main_content') ?>
<h3>What is Moss?</h3>
<p>
	<a href="http://theory.stanford.edu/~aiken/moss/" target="_blank">Moss</a> (for a Measure Of Software Similarity)
	is an automatic system for determining the similarity of programs.
	To date, the main application of Moss has been in detecting plagiarism in programming classes. Since its
	development in 1994, Moss has been very effective in this role. The algorithm behind moss is a significant
	improvement over other cheating detection algorithms.
</p>

<br>

<h3>Moss user id</h3>
<?php if (!$moss_userid): ?>
	<p class="shj_error">You have not entered your Moss user id.</p>
<?php endif ?>
<p>
	Read <a href="http://theory.stanford.edu/~aiken/moss/" target="_blank">this page</a> and register for Moss,
	then find your user id in the script sent to your email by Moss and enter your user id here.
</p>
<?= form_open('moss/update/'.$moss_assignment['id']) ?>
<p class="input_p">
	<label for="moss_uid">Your Moss user id is:</label>
	<input id="moss_uid" type="text" name="moss_userid" class="sharif_input" value="<?= $moss_userid ?>"/>
</p>
<input type="submit" class="sharif_input" value="Save"/>
</form>

<br>

<h3>Detect similar submissions of assignment "<span dir="auto"><?= $moss_assignment['name'] ?></span>":</h3>
<p>
<?= form_open('moss/'.$moss_assignment['id']) ?>
<input type="hidden" name="detect" value="detect" />
You can send final submissions of assignment "<span dir="auto"><?= $moss_assignment['name'] ?></span>" to Moss
by clicking on this button.<br>
Zip and PDF files will not be sent.<br>
It may take a minute. Please be patient.<br>
<input type="submit" class="sharif_input" value="Detect similar codes"/>
</form>
</p>

<br>

<h3>Moss results for assignment "<span dir="auto"><?= $moss_assignment['name'] ?></span>":</h3>
<p>
	Links will expire after some time. (last update: <?= $update_time ?>) <br>
	<ul>
	<?php foreach ($moss_problems as $i => $moss_problem): ?>
		<li>Problem <?= $i ?>:
			<?php if ($moss_problem == null): ?>
				Link Not Found.
			<?php elseif (!$moss_problem): ?>
				Link Not Found. Maybe you have entered wrong user id.
			<?php else: ?>
				<a href="<?= $moss_problem ?>" target="_black"><?= $moss_problem ?></a>
			<?php endif ?>
		</li>
	<?php endforeach ?>
	</ul>
</p>
<?= $this->endSection() ?>