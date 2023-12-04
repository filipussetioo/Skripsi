<!-- {#
 # SharIF Judge
 # file: halloffame.twig
 # author: Stillmen Vallian <stillmen.v@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?>fa fa-list-alt fa-lg<?= $this->endSection()?>
<?= $this->section('title') ?>Hall of Fame<?= $this->endSection() ?>
<?= $this->section('head_title') ?>Hall of Fame<?= $this->endSection() ?>
<?= $this->section('other_assets')?>
	<link rel='stylesheet' type='text/css' href='<?= base_url("assets/snippet/jquery.snippet.css") ?>'/>
	<link rel='stylesheet' type='text/css' href='<?= base_url("assets/snippet/themes/github.css") ?>'/>
	<script type='text/javascript' src="<?= base_url("assets/snippet/jquery.snippet.js") ?>"></script>
	<link rel='stylesheet' type='text/css' href='<?= base_url("assets/reveal/reveal.css") ?>'/>
	<script type='text/javascript' src="<?= base_url("assets/reveal/jquery.reveal.js") ?>"></script>
<?= $this->endSection() ?>


<?= $this->section('main_content') ?>
<div style="height:15px"></div>
<table class="sharif_table">
	<thead>
		<tr>
      <th>Rank</th>
			<th>Username</th>
      <th>Display Name</th>
			<th>Total Score</th>
		</tr>
	</thead>
  <?php $tempTotalScore = 0 ?>
  <?php $tempLoop = 0 ?>
  <?php $count = 0 ?>
	<?php foreach($hofs as $index => $hof): ?>
    <?php $index += 1 ?>
		<tr class="hof_details" style="cursor: pointer;">
      <?php if ($index == 1): ?>
        <td>1</td>
        <?php $tempTotalScore = $hof['totalscore'] ?>
        <?php $tempLoop = $tempLoop+1 ?>
      <?php elseif ($tempTotalScore == $hof['totalscore']): ?>
        <td><?= $tempLoop ?></td>
        <?php $count = $count+1 ?>
      <?php else: ?>
        <?php $tempTotalScore = $hof['totalscore'] ?>
        <?php $tempLoop = $count+$tempLoop+1 ?>
        <td><?= $tempLoop ?></td>
        <?php $count = 0 ?>
      <?php endif ?>
			<td class="username"><?= $hof['username'] ?></td>
      <td class="display_name"><?= $hof['display_name'] ?></td>
      <td><?= $hof['totalscore'] ?></td>
		</tr>
	<?php endforeach ?>
</table>
<?= $this->endSection() ?>

<?= $this->section('body_end') ?>
<div id="shj_modal" class="reveal-modal xlarge">
	<div class="modal_inside">
		<div style="text-align: center;">Loading<br><img src="<?= base_url('assets/images/loading.gif') ?>"/></div>
	</div>
	<a class="close-reveal-modal">&#215;</a>
</div>
<?= $this->endSection() ?>