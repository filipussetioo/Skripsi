<!-- {#
 # SharIF Judge
 # file: submissions.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?><?= $view == 'all' ? 'fa-bars' : 'fa-map-marker' ?><?= $this->endSection()?>
<?= $this->section('title') ?><?= ucfirst($view) ?> Submissions<?= $this->endSection() ?>
<?= $this->section('head_title') ?><?= ucfirst($view) ?> Submissions<?= $this->endSection() ?>



<?= $this->section('other_assets') ?>
	<link rel='stylesheet' type='text/css' href='<?= base_url("assets/snippet/jquery.snippet.css") ?>'/>
	<link rel='stylesheet' type='text/css' href='<?= base_url("assets/snippet/themes/github.css") ?>'/>
	<script type='text/javascript' src="<?= base_url("assets/snippet/jquery.snippet.js") ?>"></script>

	<link rel='stylesheet' type='text/css' href='<?= base_url("assets/reveal/reveal.css") ?>'/>
	<script type='text/javascript' src="<?= base_url("assets/reveal/jquery.reveal.js") ?>"></script>
	<script type='text/javascript' src="<?= base_url("assets/js/shj_submissions.js") ?>"></script>
<?= $this->endSection() ?>



<?= $this->section('title_menu') ?>
	<span class="title_menu_item">
		<a href="<?= $excel_link ?>"><i class="fa fa-download color2"></i> Excel</a>
	</span>
	<?php if ($filter_user): ?>
	<span class="title_menu_item">
		<a href="<?= site_url('submissions/'.$view.($filter_problem ? '/problem/'.$filter_problem : '')) ?>">
		<i class="fa fa-filter color1"></i> Remove Username Filter</a>
	</span>
	<?php endif ?>
	<?php if ($filter_problem): ?>
	<span class="title_menu_item">
		<a href="<?= site_url('submissions/'.$view.($filter_user?'/user/'.$filter_user : '')) ?>">
		<i class="fa fa-filter color4"></i> Remove Problem Filter</a>
	</span>
	<?php endif ?>
<?= $this->endSection() ?>




<?= $this->section('main_content') ?>

<p><?= ucfirst($view) ?> Submissions of <span dir="auto"><?= $user->selected_assignment['name'] ?></span></p>
<?php  if ($view == 'all'): ?>
<p><i class="fa fa-warning color3"></i> You cannot change your final submissions after assignment finishes.</p>
<?php endif ?>
<table class="sharif_table">
	<thead>
		<tr>
		<?php if ($view == 'all'): ?>
			<th width="1%" rowspan="2">Final</th>
		<?php endif ?>
		<?php if ($user->level > 0): ?>
				<?php if ($view == 'all'): ?>
				<th width="3%" rowspan="2">ID</th>
				<?php else: ?>
				<th width="2%" rowspan="2">#</th>
				<th width="3%" rowspan="2">ID</th>
				<?php endif ?>
				<th width="6%" rowspan="2">Username</th>
				<th width="14%" rowspan="2">Name</th>
				<th width="4%" rowspan="2">Problem</th>
				<th width="14%" rowspan="2">Submit Time</th>
				<th colspan="3">Score</th>
				<th width="1%" rowspan="2">Language</th>
				<th width="6%" rowspan="2">Status</th>
				<th width="6%" rowspan="2">Code</th>
				<th width="6%" rowspan="2">Log</th>
				<?php if ($user->level >= 2): ?>
				<th width="1%" rowspan="2">Actions</th>
				<?php endif ?>
			</tr>
			<tr>
				<th width="5%" class="score">Score</th>
				<th width="5%" class="score">Delay<br>%</th>
				<th width="5%" class="score">Final Score</th>
			</tr>
		<?php else: ?>
				<th width="10%" rowspan="2">Problem</th>
				<th width="30%" rowspan="2">Submit Time</th>
				<th width="7%" colspan="3">Score</th>
				<th width="1%" rowspan="2">Language</th>
				<th width="30%" rowspan="2">Status</th>
				<th width="15%" rowspan="2">Code</th>
			</tr>
			<tr>
				<th width="7%" class="score">Score</th>
				<th width="7%" class="score">Delay<br>%</th>
				<th width="7%" class="score">Final Score</th>
			</tr>
		<?php endif ?>
	</thead>
	<?php $i=0 ?>
	<?php $j=0 ?>
	<?php $un='' ?>
	<?php foreach ($submissions as $submission): ?>
		<?php $i = $i+1 ?>
		<?php if ($submission['username'] != $un ):?>
			<?php $j = $j+1 ?>
		<?php endif ?>
		<?php $un = $submission['username'] ?>
		<tr data-u="<?= $submission['username'] ?>" data-a="<?= $submission['assignment'] ?>" data-p="<?= $submission['problem'] ?>" data-s="<?= $submission['submit_id'] ?>" <?php if ($view == 'final' && ($j % 2 == 0)):?>class="hl"<?php endif ?>>
		<?php if ($view == 'all'): ?>
			<td>
				<i tabindex="0" aria-label="Set Final" role="checkbox" aria-checked="<?= $submission['is_final'] ? 'true' : 'false' ?>" class="pointer set_final fa <?= $submission['is_final'] ? 'fa-check-circle-o color11' : 'fa-circle-o' ?> fa-2x"></i>
			</td>
		<?php endif ?>
		<?php if ($user->level > 0): ?>
			<?php if ($view == 'all'): ?>
				<td><?= ($submission['submit_id']) ?></td>
			<?php else: ?>
				<td><?= ($page_number-1)*$per_page+$i ?></td>
				<td><?= $submission['submit_id'] ?></td>
			<?php endif ?>

			<td><a href="<?= site_url('submissions/'.$view.'/user/'.$submission['username'].($filter_problem?'/problem/'.$filter_problem :'')) ?>"><?= $submission['username'] ?></a></td>
			<td dir="auto"><?= $submission['name'] ?></td>
		<?php endif ?>
			<td><a href="<?= site_url('submissions/'.$view.($filter_user?'/user/'.$filter_user:'').'/problem/'.$submission['problem']) ?>" title="<?= $problems[$submission['problem']]['name'] ?>"><?= $submission['problem'] ?></a></td>
			<td><?= $submission['time'] ?></td>
			<td><?= $submission['pre_score'] ?></td>
			<td>
				<span class="tiny_text" <?= $submission['delay'] > 0 ? 'style="color:red;"' : '' ?>>
				<?php if ($submission['delay'] <= 0): ?>
					No Delay
				<?php else: ?>
					<span title="HH:MM"><?= time_hhmm($submission['delay']) ?></span>
				<?php endif ?>
				</span><br>
				<?= $submission['coefficient'] ?>%
			</td>
			<td style="font-weight: bold;"><?= $submission['final_score'] ?> </td>
			<td><?= $submission['language'] ?></td>
			<td class="status">
				<?php if ($submission['status'] == 'Uploaded'): ?>
					Uploaded
				<?php else: ?>
					<?php if (strtolower($submission['status']) == 'pending'): ?>
						<?php $submission_class = 'btn' ?>
					<?php elseif (strtolower($submission['status']) == 'score'): ?>
						<?php $submission_class = ($submission['fullmark'] ? 'btn shj-green' : 'btn shj-red') ?>
					<?php else: ?>
						<?php $submission_class = 'btn shj-blue' ?>
					<?php endif ?>
					<div tabindex="0" class="<?= $submission_class ?>" data-type="result" >
						<?php if ($submission['status'] == 'SCORE'): ?>
							<?= $submission['final_score'] ?>
						<?php else: ?>
							<?= $submission['status'] ?>
						<?php endif ?>
					</div>
				<?php endif ?>
			</td>
			<td>
				<?php if ($submission['file_type'] == 'zip' || $submission['file_type'] == 'pdf' || $submission['file_type'] == 'txt'): ?>
					<div tabindex="0" class="btn shj-orange" data-type="download">Download</div>
				<?php else: ?>
					<div tabindex="0" class="btn shj-orange" data-type="code" >Code</div>
				<?php endif ?>
			</td>
			<?php if ($user->level > 0): ?>
			<td>
				<?php if ($submission['status'] == 'Uploaded'): ?>
					---
				<?php else: ?>
					<div tabindex="0" class="btn" data-type="log" >Log</div>
				<?php endif ?>
			</td>
			<?php endif ?>
			<?php if ($user->level >= 2): ?>
				<td>
					<div tabindex="0" class="shj_rejudge pointer"><i class="fa fa-refresh fa-lg color10"></i></div>
				</td>
			<?php endif ?>
		</tr>
	<?php endforeach ?>
</table>

<p>
<?= $pagination ?>
</p>

<?= $this->endSection() ?>



<?= $this->section('body_end') ?>
<div id="shj_modal" class="reveal-modal xlarge">
	<div class="modal_inside">
		<div style="text-align: center;">Loading<br><img src="<?= base_url('assets/images/loading.gif') ?>"/></div>
	</div>
	<a class="close-reveal-modal">&#215;</a>
</div>
<?= $this->endSection() ?>