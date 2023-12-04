<!-- {#
 # SharIF Judge
 # file: logs.twig
 # author: Stillmen Vallian <stillmen.v@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?>fa-book<?= $this->endSection()?>
<?= $this->section('title') ?>24-hour Log<?= $this->endSection() ?>
<?= $this->section('head_title') ?>24-hour Log<?= $this->endSection() ?>

<?= $this->section('main_content') ?>

<p style="text-align:justify">Use this table to detect account lendings between students in a pre-seated exam environment. In a pre-seated environment, student should have logged in from only one IP address within the exam time period. Whenever a user logs in from different IP address in the past 24 hours, last column of this table will show link to the previous login that happened. In such case, proctor can analyse from those two logins, whether the same login has been used in different devices (meaning the account had been lent).</p>

<table class="sharif_table">
	<thead>
		<tr>
			<th>#</th>
			<th>Login ID</th>
			<th>Username</th>
      <th>IP Address</th>
      <th>Login Time</th>
      <th>Log from different IP (< 24 hours)</th>
		</tr>
	</thead>
	<?php foreach ($logs as $index => $log): ?>
		<tr>
			<td><?= $index + 1 ?></td>
			<td id="<? $log['login_id'] ?>"><?= $log['login_id'] ?></td>
			<td><?= $log['username'] ?></td>
      <td><?= $log['ip_address'] ?></td>
      <td><?= $log['timestamp'] ?></td>
      <td><a href="#<?= $log['last_24h_login_id'] ?>"><?= $log['last_24h_login_id'] ?></a></td>
		</tr>
	<?php endforeach ?>
</table>
<?= $this->endSection() ?>
