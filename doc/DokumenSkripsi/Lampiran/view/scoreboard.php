<!-- {#
 # SharIF Judge
 # file: scoreboard.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?>fa-star<?= $this->endSection()?>
<?= $this->section('title') ?>Scoreboard<?= $this->endSection() ?>
<?= $this->section('head_title') ?>Scoreboard<?= $this->endSection() ?>

<?= $this->section('main_content') ?>
	<?php if ($user->selected_assignment['id'] == 0): ?>
<p>No assignment is selected.</p>
<?php elseif (!$user->selected_assignment['scoreboard']): ?>
<p>Scoreboard is disabled.</p>
<?php else: ?>
	<p>Scoreboard of <span dir="auto"><?= $user->selected_assignment['name'] ?></span></p>
	<?= $scoreboard ?>
<?php endif ?>
<?= $this->endSection() ?>