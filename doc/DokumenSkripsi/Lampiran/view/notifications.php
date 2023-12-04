<!-- {#
 # SharIF Judge
 # file: notifications.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?>fa-bell<?= $this->endSection()?>
<?= $this->section('title') ?>Notifications<?= $this->endSection() ?>
<?= $this->section('head_title') ?>Notifications<?= $this->endSection() ?>

<?= $this->section('title_menu') ?>
	<?php if ($user->level >= 2): ?>
		<span class="title_menu_item"><a href="<?= site_url('notifications/add') ?>"><i class="fa fa-plus color10"></i> New</a></span>
	<?php endif ?>
<?= $this->endSection()?> 



<?= $this->section('main_content') ?>
<?php if (count($notifications) == 0): ?>
	<p style="text-align: center;">Nothing yet...</p>
<?php endif ?>
<?php foreach ($notifications as $notification): ?>
	<div class="notif" id="number<?= $notification['id'] ?>" data-id="<?= $notification['id'] ?>">
		<div class="notif_title" dir="auto">
			<span class="anchor ttl_n"><?= $notification['title'] ?></span>
			<div class="notif_meta">
				<?= $notification['time'] ?>
				<?php if ($user->level >= 2): ?>
					<span class="anchor edt_n">Edit</span>
					<span class="anchor del_n">Delete</span>
				<?php endif ?>
			</div>
		</div>
		<div class="notif_text" dir="auto">
			<?= $notification['text']?>
		</div>
	</div>
<?php endforeach ?>
<?= $this->endSection()?>
