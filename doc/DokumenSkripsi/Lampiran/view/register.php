<!-- {#
 # SharIF Judge
 # file: register.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->section('title') ?>Register<?= $this->endSection() ?>
<?= $this->include('templates/simple_header') ?>

<?= form_open('login/register') ?>
<div class="box register">

	<div class="judge_logo">
		<a href="<?= site_url() ?>"><img src="<?= base_url('assets/images/banner.png') ?>"/></a>
	</div>

	<div class="login_form">
		<div class="login1">
			<?php if ($registration_code_required): ?>
			<p>
				<label for="form_reg_code">Registration Code</label><br/>
				<input id="form_reg_code" type="text" name="registration_code" required="required" autofocus="autofocus" class="sharif_input" value="<?= set_value('registration_code') ?>"/>
				<div class="shj_error"><?= $validationError->hasError('registration_code') ? $validationError->getError('registration_code') : '' ?></div>
			</p>
			<?php endif ?>
			<p>
				<label for="form_username">Username</label><br/>
				<input id="form_username" type="text" name="username" required="required" pattern="[0-9A-Za-z]{3,20}" title="The Username field must be between 3 and 20 characters in length, and contain only alpha-numeric characters" class="sharif_input" value="<?= set_value('username') ?>"/>
				<div class="shj_error"><?= $validationError->hasError('username') ? $validationError->getError('username') : '' ?></div>
			</p>
			<p>
				<label for="form_email">Email</label><br/>
				<input id="form_email" type="email" autocomplete="off" name="email" required="required" class="sharif_input" value="<?= set_value('email') ?>"/>
				<div class="shj_error"><?= $validationError->hasError('email') ? $validationError->getError('email') : '' ?></div>
			</p>
      <p>
				<label for="form_displayname">Display Name</label><br/>
				<input id="form_displayname" type="text" name="displayname" required="required" pattern="[A-Za-z\s]+" title="The Display Name field must be contain only alphabetical letters" class="sharif_input" value="<?= set_value('displayname') ?>"/>
				<div class="shj_error"><?= $validationError->hasError('form_displayname') ? $validationError->getError('form_displayname') : ''?></div>
			</p>
			<p>
				<label for="form_password">Password</label><br/>
				<input id="form_password" type="password" name="password" required="required" pattern=".{6,200}" title="The Password field must be at least 6 characters in length" class="sharif_input"/>
				<div class="shj_error"><?= $validationError->hasError('password') ?  $validationError->getError('password') : ''?></div>
			</p>
			<p>
				<label for="form_password_2">Password, Again</label><br/>
				<input id="form_password_2" type="password" name="password_again" required="required" pattern=".{6,200}" title="The Password Confirmation field must be at least 6 characters in length" class="sharif_input"/>
				<div class="shj_error"><?= $validationError->hasError('password_again') ? $validationError->getError('password_again') : ''?></div>
			</p>
		</div>
		<div class="login2">
			<p style="margin:0;">
				<a href="<?= site_url('login') ?>">Login</a>
				<input type="submit" value="Register" id="sharif_submit"/>
			</p>
		</div>
	</div>

</div>
</form>
</body>
</html>
