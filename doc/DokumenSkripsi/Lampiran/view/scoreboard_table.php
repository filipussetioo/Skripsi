<!-- {#
 # SharIF Judge
 # file: scoreboard_table.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<table class="sharif_table">

<thead>
	<tr>
		<th>#</th>
		<th>Username</th>
		<th>Name</th>
		<?php foreach($problems as $problem): ?>
			<th>
				<a dir="auto" href="<?= site_url("problems/$assignment_id/").$problem['id'] ?>"><?= $problem['name'] ?></a><br>
				<span class="tiny_text_b"><? $problem['score'] ?></span>
			</th>
		<?php endforeach ?>
		<th>
			Total<br>
			<span class="tiny_text_b"><?= $total_score ?></span>
		</th>
	</tr>
</thead>

<?php foreach($scoreboard['username'] as $index => $sc_username): ?>
	<tr>
	<td><?= $index + 1 ?></td>
	<td><?= $sc_username ?></td>
	<td dir="auto"><?= $names[$sc_username] ?></td>
	<?php foreach($problems as $problem): ?>
	<td>
		<?php if (isset($scores[$sc_username][$problem['id']]['score'])): ?>
			<?= $scores[$sc_username][$problem['id']]['score'] ?><br>
			<span class="tiny_text" title="Time"><?= time_hhmm($scores[$sc_username][$problem['id']]['time']) ?></span>
		<?php else: ?>
			-
		<?php endif ?>
	</td>
	<?php endforeach ?>
	<td>
	<span style="font-weight: bold;"><?= $scoreboard['score'][$index] ?></span>
	<br>
	<span class="tiny_text" title="Total Time + Submit Penalty"><?= time_hhmm($scoreboard['submit_penalty'][$index]) ?></span>
	</td>
	</tr>
<?php endforeach ?>

</table>