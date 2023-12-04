<!-- {#
 # SharIF Judge
 # file: add_notification.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?><?= $notif_edit ? 'fa-edit' : 'fa-plus' ?><?= $this->endSection()?>
<?= $this->section('title') ?><?= $notif_edit ? 'Edit' : 'New' ?><?= $this->endSection() ?>
<?= $this->section('head_title') ?><?= $notif_edit ? 'Edit' : 'New' ?><?= $this->endSection() ?>

<?= $this->section('other_assets')?>
<script type='text/javascript' src="<?= base_url('assets/tinymce/tinymce.min.js') ?>"></script>
<script>
$(document).ready(function(){
	tinymce.init({
		selector: 'textarea#notif_text',
		toolbar_items_size: 'small',
		relative_urls: false,
		width: 700,
		height: 300,
		resize: false,
		plugins: 'directionality emoticons textcolor link code',
		toolbar1: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | ltr rtl",
		toolbar2: "forecolor backcolor | emoticons | link unlink anchor image media code | removeformat"
	});
});
</script>
<?= $this->endSection() ?>

<?= $this->section('main_content') ?>
<?= form_open('notifications/'.($notif_edit ? 'edit/'.$notif_edit['id'] : 'add')) ?>
<?php if ($notif_edit): ?>
	<input type="hidden" name="id" value="<?= $notif_edit['id'] ?>"/>
<?php endif ?>
<p class="input_p">
	<label for="form_title" class="tiny">Title:</label>
	<input id="form_title" name="title" type="text" class="sharif_input" value="<?= $notif_edit ? $notif_edit['title'] : '' ?>"/>
</p>
<p class="input_p">
	<label for="notif_text" class="tiny">Text:</label><br><br>
	<textarea id="notif_text" name="text"><?= $notif_edit ? $notif_edit['text'] : '' ?></textarea>
</p>
<p class="input_p">
	<input type="submit" value="<?= $notif_edit ? 'Save' : 'Add' ?>" class="sharif_input"/>
</p>
</form>
<?= $this->endSection() ?>