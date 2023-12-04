<!-- {#
 # SharIF Judge
 # file: install.twig
 # author: Mohammad Javad Naderi <mjnaderi@gmail.com>
 #} -->
<?= $this->include('templates/simple_header')?>
<?php if($installed): ?>
	<div id="install_success">
		<h2>SharIF Judge Installed</h2>
		<?php if(!$key_changed): ?>
			<p class="shj_error">
				<code>app/Config/Encryption.php</code> is not writable by PHP.
			</p>
			<p>
				For security reasons, you should change the encryption key manually.<br>
				Open <code>app/Config/Encryption.php</code> and change the encryption key in this line:
			</p>
			<pre>$encryption_key = '<?= $enc_key ?>';</pre>
			<p>
				The key should be a 32-characters string as random as possible, with numbers and uppercase and lowercase letters.<br>
				You can use this random string: <code><?= $random_key ?></code>
			</p>
			<br>
		<?php endif ?>
		<p class="shj_ok">SharIF Judge installed! Now you can <a href="<?= base_url('login') ?>">login</a>.</p>
	</div>

<?php else: ?>
	<div id="install_form">
		<h2>SharIF Judge Installation</h2>
		<?= form_open('install') ?>
		<p class="input_p">
			<label for="form_username">Admin username:</label>
			<input id="form_username" class="sharif_input" type="text" name="username" required="required" pattern="[0-9A-Za-z]{3,20}" title="The Username field must be between 3 and 20 characters in length, and contain only alpha-numeric characters" value="<?= set_value('username') ?>" autofocus/>
			<div class="shj_error"><?= $validationError->hasError('username') ? $validationError->getError('username') : '' ?></div>
		</p>
		<p class="input_p">
			<label for="form_email">Admin email:</label>
			<input id="form_email" type="email" autocomplete="off" name="email" required="required" class="sharif_input" value="<?= set_value('email') ?>"/>
			<div class="shj_error"><?= $validationError->hasError('email') ? $validationError->getError('email') : '' ?></div>
		</p>
		<p class="input_p">
			<label for="form_password">Admin password:</label>
			<input id="form_password" type="password" name="password" required="required" pattern="[0-9A-Za-z]{6,20}" title="The Password field must be between 6 and 30 characters in length, and contain only alpha-numeric characters" class="sharif_input"/>
			<div class="shj_error"><?= $validationError->hasError('password') ? $validationError->getError('password') : '' ?></div>
		</p>
		<p class="input_p">
			<label for="form_password_2">Password, again:</label>
			<input id="form_password_2" type="password" name="password_again" required="required" class="sharif_input"/>
			<div class="shj_error"><?= $validationError->hasError('password_again') ? $validationError->getError('password_again') : '' ?></div>
		</p>
		<p class="input_p">
			<input class="sharif_input" type="submit" value="Continue"/>
		</p>
		<?= form_close() ?>
	</div>
<?php endif ?>
</body>
</html>