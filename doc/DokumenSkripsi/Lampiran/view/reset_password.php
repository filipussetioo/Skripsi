<!-- {#
 # SharIF Judge
 # file: reset_password.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->include('templates/simple_header') ?>

<?= form_open('login/reset/'.$key) ?>
	<div class="box login">

		<div class="judge_logo">
			<a href="<?= site_url() ?>"><img src="<?= base_url('assets/images/banner.png') ?>"/></a>
		</div>

		<div class="login_form">
			<div class="login1">
				<p>
					<label for="form_password">New Password:</label><br/>
					<input id="form_password" type="password" name="password" required="required" pattern=".{6,200}" title="The Password field must be at least 6 characters in length" class="sharif_input"/>
					<div class="shj_error"><?= $validationError->hasError('password') ? $validationError->getError('password'): ''?></div>
				</p>

				<p>
					<label for="form_password_2">New Password, Again:</label><br/>
					<input id="form_password_2" type="password" name="password_again" required="required" pattern=".{6,200}" title="The Password Confirmation field must be at least 6 characters in length" class="sharif_input"/>
					<div class="shj_error"><?= $validationError->hasError('password_again') ? $validationError->getError('password'): ''?></div>
				</p>
				<?php if ($reset): ?>
					<div class="shj_ok">Login with your new password!</div>
				<?php endif ?>
			</div>
			<div class="login2">
				<p style="margin:0;">
					<a href="<?= site_url('login') ?>">Login</a>
					<input type="submit" value="Set Password" id="sharif_submit"/>
				</p>
			</div>
		</div>

	</div>
</form>
</body>
</html>