<!-- {#
 # SharIF Judge
 # file: profile.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->extend('templates/base') ?>
<?= $this->section('icon') ?>fa-user<?= $this->endSection()?>
<?= $this->section('title') ?>Profile<?= $this->endSection() ?>
<?= $this->section('head_title') ?>Profile<?= $this->endSection() ?>

<?= $this->section('main_content')?>
<p class="input_p">
<?php if ($form_status == 'ok'): ?>
<div class="shj_ok">Profile updated successfully.</div>
<?php elseif ($form_status == 'error'): ?>
	<div class="shj_error">Error updating profile.</div>
<?php endif ?>
</p>
<?= form_open('profile/'. $id) ?>
<p class="input_p clear">
	<label for="form_username" class="short2">Username:<br>
		<span class="form_comment">You cannot change username.</span>
	</label>
	<input id="form_username" type="text" name="username" class="sharif_input medium" value="<?= $edit_username ?>"  disabled/>
</p>
<p class="input_p clear">
	<label for="form_name" class="short2">Name:</label>
  	<?php if ($lock_student_display_name == 1): ?>
  		<input id="form_name" type="text" name="display_name" class="sharif_input medium" value="<?= $display_name ?>" disabled/>
  	<?php else: ?>
  		<input id="form_name" type="text" name="display_name" class="sharif_input medium" value="<?= $display_name ?>"/>
  	<?php endif ?>
  <div class="shj_error"><?= $validationError->hasError('display_name') ? $validationError->getError('display_name') : '' ?></div>
</p>
<p class="input_p clear">
	<label for="form_email" class="short2">Email:</label>
	<input id="form_email" type="text" name="email" class="sharif_input medium" value="<?= $email ?>"/>
	<div class="shj_error"><?= $validationError->hasError('email') ? $validationError->getError('email') : '' ?></div>
</p>
<p class="input_p clear">
	<label for="form_password" class="short2">Password:<br>
		<span class="form_comment">If you don't want to change password, leave this blank.</span>
	</label>
	<input id="form_password" type="password" name="password" class="sharif_input medium"/>
	<div class="shj_error"><?= $validationError->hasError('password') ? $validationError->getError('password') : '' ?></div>
</p>
<p class="input_p clear">
	<label for="form_password_2" class="short2">Password, Again:</label>
	<input id="form_password_2" type="password" name="password_again" class="sharif_input medium"/>
	<div class="shj_error"><?= $validationError->hasError('password_again') ? $validationError->getError('password_again') : '' ?></div>
</p>
<?php if ($level == 3): ?>
<p class="input_p clear">
	<label for="form_role" class="short2">User Role:</label>
	<select id="form_role" name="role" class="sharif_input">
		<option value="admin" <?= $role == 'admin' ? 'selected="selected"': '' ?> >admin</option>
		<option value="head_instructor" <?= $role == 'head_instructor' ? 'selected="selected"': '' ?>>head_instructor</option>
		<option value="instructor" <?= $role == 'instructor' ? 'selected="selected"': '' ?>>instructor</option>
		<option value="student" <?= $role == 'student' ? 'selected="selected"': '' ?> >student</option>
	</select>
	<div class="shj_error"><?= $validationError->hasError('role') ? $validationError->getError('role') : '' ?></div>
</p>
<?php endif ?>
<p class="input_p">
	<input type="submit" value="Save" class="sharif_input"/>
</p>
</form>
<?= $this->endSection() ?>
