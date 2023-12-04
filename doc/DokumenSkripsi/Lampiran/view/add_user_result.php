<!-- {#
 # SharIF Judge
 # file: add_user_result.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?php if($ok): ?>
<p class="shj_ok">These users added successfully:</p>
<ol>
	<?php foreach ($ok as $item): ?>
	<li>Usename: <?= $item[0] ?> Email: <?= $item[1] ?> Diplay Name: <?= $item[2] ?> Password: <code><?= $item[3] ?></code> Role: <?= $item[4] ?> </li>
	<?php endforeach ?>
</ol>
<?php endif ?>
<?php if ($error): ?>
<p class="shj_error">Error adding these users:</p>
<ol>
	<?php foreach($error as $item): ?>
	<li>Username: <?= $item[0] ?> Email: <?= $item[1] ?> Diplay Name: <?= $item[2] ?> Password: <code><?= $item[3] ?></code> Role: <?= $item[4] ?> (<?= $item[5] ?>)</li>
	<?php endforeach ?>
</ol>
<?php endif ?>
