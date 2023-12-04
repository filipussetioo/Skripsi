<!-- {#
 # SharIF Judge
 # file: assignments.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?>fa-folder-open<?= $this->endSection()?>
<?= $this->section('title') ?>Assignments<?= $this->endSection() ?>
<?= $this->section('head_title') ?>Assignments<?= $this->endSection() ?>

<?= $this->section('title_menu') ?>
<?php if ($user->level >= 2): ?>
<span class="title_menu_item"><a href="<?= site_url('assignments/add') ?>"><i class="fa fa-plus color8"></i> Add</a></span>
<?php endif ?>
<?=  $this->endSection() ?>

<?php $this->section('main_content') ?>
<?php $msgclasses = ['success'=> 'shj_g', 'notice'=> 'shj_o', 'error'=> 'shj_r'] ?>
<?php foreach ($messages as $message): ?>
	<p class="<?= $msgclasses[$message['type']] ?>"><?= $message['text'] ?></p>
<?php endforeach ?>

<?php if (count($all_assignments)== 0): ?>
	<p style="text-align: center;">Nothing to show...</p>
<?php else: ?>
<br/>
<table class="sharif_table">
<thead>
<tr>
	<th>Select</th>
	<th>Name</th>
	<th>Problems</th>
	<th>Submissions</th>
	<th>Coefficient</th>
	<th>Start Time</th>
	<th>Finish Time</th>
	<th>Status</th>
	<th>PDF</th>
	<?php if ($user->level > 0): ?>
	<th>Actions</th>
	<?php endif ?>
</tr>
</thead>
<?php foreach (array_reverse($all_assignments) as $item): ?>
<tr>
	<td><i tabindex="0" class="pointer select_assignment fa <?= $item['id'] == $user->selected_assignment['id'] ? 'fa-check-square-o color6' : 'fa-square-o' ?> fa-2x" data-id="<?= $item['id'] ?>"></i></td>
	<td dir="auto"><?= $item['name'] ?></td>
	<td><a href="<?= site_url('problems/'.$item['id']) ?>"><? $item['problems'] ?> problem<?= $item['problems'] != 1 ? 's' : '' ?></a></td>
	<td><?= $item['total_submits'] ?> submission<?= $item['total_submits'] != 1 ? 's' : '' ?></td>
	<td>
		<?php if ($item['finished']): ?>
			<span style="color: red;">Finished</span>
		<?php else: ?>
			<?= $item['coefficient'] ?> %
		<?php endif ?>
	</td>
	<td><?= $item['start_time'] ?></td>
	<td><?= $item['finish_time'] ?></td>
	<td>
		<?php if ($item['open']): ?>
			<span style="color: green;">Open</span>
		<?php else: ?>
			<span style="color: red;">Close</span>
		<?php endif ?>
	</td>
	<td>
		<a href="<?= site_url('assignments/pdf/'.$item['id']) ?>"><img src="<?= base_url('assets/images/pdf.svg') ?>" aria-label="Download PDF For Assignment <?= $item['name'] ?>"/></a>
	</td>
	<?php if ($user->level > 0): ?>
	<td>
		<?php if ($user->level >= 2): ?>
			<a href="<?= site_url('assignments/downloadtestsdesc/'.$item['id']) ?>"><i title="Download Tests and Descriptions" class="fa fa-cloud-download fa-lg color11"></i></a>
		<?php endif ?>
		<?php if ($user->level >= 1): ?>
			<a href="<?= site_url("assignments/download_submissions/by_user/").$item['id'] ?>"><i title="Download Final Submissions (by user)" class="fa fa-download fa-lg color12"></i></a>
			<a href="<?= site_url("assignments/download_submissions/by_problem/").$item['id'] ?>"><i title="Download Final Submissions (by problem)" class="fa fa-download fa-lg color2"></i></a>
		<?php endif ?>
		<?php if ($user->level >= 2): ?>
			<a href="<?= site_url('moss/'.$item['id']) ?>"><i title="Detect Similar Codes" class="fa fa-shield fa-lg color7"></i></a>
		<?php endif ?>
		<?php if ($user->level >= 2): ?>
			<a href="<?= site_url('assignments/edit/'.$item['id']) ?>"><i title="Edit" class="fa fa-pencil fa-lg color3"></i></a>
		<?php endif ?>
		<?php if ($user->level >= 2): ?>
			<a href="<?= site_url('assignments/delete/'.$item['id']) ?>"><i title="Delete" class="fa fa-times fa-lg color1"></i></a>
		<?php endif ?>
	</td>
	<?php endif ?>
</tr>
<?php endforeach ?>
</table>
<?php endif ?>
<?php $this->endSection() ?>