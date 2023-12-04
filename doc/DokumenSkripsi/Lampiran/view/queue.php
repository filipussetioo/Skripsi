<!-- {#
 # SharIF Judge
 # file: queue.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?>fa-play<?= $this->endSection() ?>
<?= $this->section('title') ?>Submission Queue<?= $this->endSection() ?>
<?= $this->section('head_title') ?>Submission Queue<?= $this->endSection() ?>



<?= $this->section('other_assets') ?>
<script>
	$(document).ready(function(){
		$(".shj_act").click(function(){
			var action=$(this).attr('id');
			$.post(
					'<?= site_url('queue') ?>/'+action,
					{shj_csrf_token: shj.csrf_token},
					function(data){
						if (data=='success')
							location.reload();
					}
			);
		});
	});
</script>
<?= $this->endSection() ?>



<?= $this->section('main_content') ?>
<p>
	<?php if ($working): ?>
		<i class="fa fa-play fa-lg color6"></i> Queue is working
	<?php else: ?>
		<i class="fa fa-pause fa-lg color10"></i> Queue is not working
	<?php endif ?>
	| Total submissions in queue: <?= count($queue) ?>
</p>
<p>
	<a href="#" class="shj_act" id="pause"><i class="fa fa-pause"></i> Pause</a> |
	<a href="#" class="shj_act" id="resume"><i class="fa fa-play"></i> Resume</a> |
	<a href="#" class="shj_act" id="empty_queue"><i class="fa fa-times-circle"></i> Empty Queue</a>
</p>
<table class="sharif_table">
	<thead>
	<tr>
		<th>#</th>
		<th>Submit ID</th>
		<th>Usename</th>
		<th>Assignment</th>
		<th>Problem</th>
		<th>Type (judge/rejudge)</th>
	</tr>
	</thead>
	<?php foreach ($queue as $index => $item): ?>
		<tr>
			<td><?= $index+1 ?></td>
			<td><?= $item['submit_id'] ?></td>
			<td><?= $item['username'] ?></td>
			<td><?= $item['assignment'] ?> (<span dir="auto"><?= $all_assignments[$item['assignment']]['name'] ?></span>)</td>
			<td><?= $item['problem'] ?></td>
			<td><?= $item['type'] ?></td>
		</tr>
	<?php endforeach ?>
</table>
<?= $this->endSection() ?>