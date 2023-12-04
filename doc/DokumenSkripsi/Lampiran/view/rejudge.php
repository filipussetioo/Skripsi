<!-- {#
 # SharIF Judge
 # file: rejudge.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?>fa-refresh<?= $this->endSection() ?>
<?= $this->section('title') ?>Rejudge<?= $this->endSection() ?>
<?= $this->section('head_title') ?>Rejudge<?= $this->endSection() ?>


<?= $this->section('main_content') ?>
<p>
	Selected Assignment: <span dir="auto"><?= $user->selected_assignment['name'] ?></span>
</p>
<p>
	By clicking on rejudge, all submissions of selected problem will change to <code>PENDING</code> state. Then
	SharIF Judge rejudges them one by one.
</p>
<p>
	If you want to rejudge a single submission, you can click on rejudge button in <a href="<?= site_url('submissions/all') ?>">All Submissions</a> or <a href="<?= site_url('submissions/final') ?>">Final Submissions</a> page.
</p>
<?php foreach ($problems as $problem): ?>
	<?= form_open('rejudge') ?>
		<input type="hidden" name="problem_id" value="<?= $problem['id'] ?>"/>
		<input type="submit" class="sharif_input" value="Rejudge Problem <?= $problem['id'] ?> (<?= $problem['name'] ?>)"/>
	</form>
<?php endforeach ?>

<?php foreach($msg as $message): ?>
	<p class="shj_ok"><?= $message ?></p>
<?php endforeach ?>
<?= $this->endSection() ?>